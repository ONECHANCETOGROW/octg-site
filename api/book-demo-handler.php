<?php
/* ==========================================================================
   BOOK-DEMO-HANDLER.PHP — processes the multi-step form on book-demo.php.
   Same graceful-degradation pattern as contact-handler.php.
   ========================================================================== */
require __DIR__ . '/_lib.php';
require __DIR__ . '/../includes/notification-service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    octg_send_json(['ok' => false, 'error' => 'Invalid request method.'], 405);
}

if (octg_field('company_website') !== '') {
    octg_send_json(['ok' => true]);
}

$businessName = octg_field('business_name');
$contactName  = octg_field('contact_name');
$email        = octg_field('email');
$phone        = octg_field('phone');
$businessType = octg_field('business_type');
$budgetRange  = octg_field('budget_range');
$goals        = octg_field('goals');
$sourcePage   = octg_field('source_page', '/book-demo.php');

$servicesRaw = $_POST['services_interested'] ?? [];
if (!is_array($servicesRaw)) $servicesRaw = [$servicesRaw];
$servicesInterested = array_values(array_filter(array_map('trim', $servicesRaw)));

if ($businessName === '' || $contactName === '' || !octg_is_valid_email($email) || $phone === '') {
    octg_send_json(['ok' => false, 'error' => 'Please complete the business name, your name, a valid email, and phone number.'], 422);
}

$data = compact('businessName', 'contactName', 'email', 'phone', 'businessType', 'budgetRange', 'goals', 'servicesInterested', 'sourcePage');
$pdo = octg_db();

if ($pdo) {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO demo_requests
             (business_name, contact_name, email, phone, business_type, services_interested, budget_range, goals, source_page)
             VALUES (:business_name, :contact_name, :email, :phone, :business_type, :services_interested, :budget_range, :goals, :source_page)'
        );
        $stmt->execute([
            ':business_name' => $businessName, ':contact_name' => $contactName, ':email' => $email,
            ':phone' => $phone, ':business_type' => $businessType,
            ':services_interested' => json_encode($servicesInterested),
            ':budget_range' => $budgetRange, ':goals' => $goals, ':source_page' => $sourcePage,
        ]);
    } catch (Throwable $e) {
        octg_log_fallback('demo_requests', $data);
    }
} else {
    octg_log_fallback('demo_requests', $data);
}

try {
    octg_save_lead('book-demo', [
        'name' => $contactName, 'business_name' => $businessName, 'phone' => $phone,
        'email' => $email, 'interested_service' => implode(', ', $servicesInterested),
        'message' => $goals, 'source_page' => $sourcePage,
    ]);
    octg_notify_lead('Book a Growth Call', [
        'Contact Name' => $contactName, 'Business Name' => $businessName, 'Email' => $email,
        'Phone' => $phone, 'Business Type' => $businessType,
        'Services Interested' => implode(', ', $servicesInterested),
        'Budget Range' => $budgetRange, 'Goals' => $goals,
    ]);
} catch (Throwable $e) {
    // Demo request is already saved above regardless of notification outcome.
}

octg_send_json(['ok' => true]);
