<?php
/* ==========================================================================
   NEWSLETTER-HANDLER.PHP — processes the email signup on resources.php.
   ========================================================================== */
require __DIR__ . '/_lib.php';
require __DIR__ . '/../includes/notification-service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    octg_send_json(['ok' => false, 'error' => 'Invalid request method.'], 405);
}
if (octg_field('company_website') !== '') {
    octg_send_json(['ok' => true]);
}

$email = octg_field('email');
if (!octg_is_valid_email($email)) {
    octg_send_json(['ok' => false, 'error' => 'Please enter a valid email address.'], 422);
}

$pdo = octg_db();
if ($pdo) {
    try {
        $stmt = $pdo->prepare('INSERT IGNORE INTO newsletter_subscribers (email) VALUES (:email)');
        $stmt->execute([':email' => $email]);
    } catch (Throwable $e) {
        octg_log_fallback('newsletter_subscribers', ['email' => $email]);
    }
} else {
    octg_log_fallback('newsletter_subscribers', ['email' => $email]);
}

try {
    octg_notify_lead('Newsletter Signup', ['Email' => $email]);
} catch (Throwable $e) {
    // Subscriber is already saved above regardless of notification outcome.
}

octg_send_json(['ok' => true]);
