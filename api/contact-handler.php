<?php
/* ==========================================================================
   CONTACT-HANDLER.PHP — processes submissions from contact.php's form.
   Requires includes/db-config.php to write to the database; falls back to
   api/logs/contact_messages.log (blocked from public access) if that
   doesn't exist yet, so no submission is ever lost.
   Also notifies the admin by email and saves a normalized CRM record via
   the shared notification service — see includes/notification-service.php.
   ========================================================================== */
require __DIR__ . '/_lib.php';
require __DIR__ . '/../includes/notification-service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    octg_send_json(['ok' => false, 'error' => 'Invalid request method.'], 405);
}

/* Honeypot: real users never fill this hidden field */
if (octg_field('company_website') !== '') {
    octg_send_json(['ok' => true]); // pretend success, drop silently
}

$name        = octg_field('name');
$email       = octg_field('email');
$phone       = octg_field('phone');
$businessName = octg_field('business_name');
$message     = octg_field('message');
$sourcePage  = octg_field('source_page', '/contact.php');

if ($name === '' || $message === '' || !octg_is_valid_email($email)) {
    octg_send_json(['ok' => false, 'error' => 'Please fill in your name, a valid email, and a message.'], 422);
}

$data = compact('name', 'email', 'phone', 'businessName', 'message', 'sourcePage');
$pdo = octg_db();

if ($pdo) {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO contact_messages (name, email, phone, business_name, message, source_page)
             VALUES (:name, :email, :phone, :business_name, :message, :source_page)'
        );
        $stmt->execute([
            ':name' => $name, ':email' => $email, ':phone' => $phone,
            ':business_name' => $businessName, ':message' => $message, ':source_page' => $sourcePage,
        ]);
    } catch (Throwable $e) {
        octg_log_fallback('contact_messages', $data);
    }
} else {
    octg_log_fallback('contact_messages', $data);
}

/* CRM record + email notification — never allowed to break the form's
   success response, even if something in here throws. */
try {
    octg_save_lead('contact', [
        'name' => $name, 'business_name' => $businessName, 'phone' => $phone,
        'email' => $email, 'message' => $message, 'source_page' => $sourcePage,
    ]);
    octg_notify_lead('Contact Form', [
        'Name' => $name, 'Business Name' => $businessName, 'Email' => $email,
        'Phone' => $phone, 'Message' => $message,
    ]);
} catch (Throwable $e) {
    // Contact message is already saved above — a notification failure here
    // must never surface as a failed submission to the visitor.
}

octg_send_json(['ok' => true]);
