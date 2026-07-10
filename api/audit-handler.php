<?php
/* ==========================================================================
   AUDIT-HANDLER.PHP — processes the free audit request form.
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
$websiteUrl   = octg_field('website_url');
$email        = octg_field('email');
$phone        = octg_field('phone');
$sourcePage   = octg_field('source_page', '/audit.php');

$areasRaw = $_POST['audit_areas'] ?? [];
if (!is_array($areasRaw)) $areasRaw = [$areasRaw];
$auditAreas = array_values(array_filter(array_map('trim', $areasRaw)));

if ($businessName === '' || !octg_is_valid_email($email)) {
    octg_send_json(['ok' => false, 'error' => 'Please fill in your business name and a valid email.'], 422);
}

$data = compact('businessName', 'websiteUrl', 'email', 'phone', 'auditAreas', 'sourcePage');
$pdo = octg_db();

if ($pdo) {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO audit_requests (business_name, website_url, email, phone, audit_areas, source_page)
             VALUES (:business_name, :website_url, :email, :phone, :audit_areas, :source_page)'
        );
        $stmt->execute([
            ':business_name' => $businessName, ':website_url' => $websiteUrl, ':email' => $email,
            ':phone' => $phone, ':audit_areas' => json_encode($auditAreas), ':source_page' => $sourcePage,
        ]);
    } catch (Throwable $e) {
        octg_log_fallback('audit_requests', $data);
    }
} else {
    octg_log_fallback('audit_requests', $data);
}

try {
    octg_save_lead('audit', [
        'name' => $businessName, 'business_name' => $businessName, 'phone' => $phone,
        'email' => $email, 'website' => $websiteUrl,
        'interested_service' => implode(', ', $auditAreas), 'source_page' => $sourcePage,
    ]);
    octg_notify_lead('Free Audit Request', [
        'Business Name' => $businessName, 'Website' => $websiteUrl, 'Email' => $email,
        'Phone' => $phone, 'Areas Requested' => implode(', ', $auditAreas),
    ]);
} catch (Throwable $e) {
    // Audit request is already saved above regardless of notification outcome.
}

octg_send_json(['ok' => true]);
