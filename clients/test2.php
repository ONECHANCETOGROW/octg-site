<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__);
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Services/AIParser/NLParser.php';

try {
    $db = \App\Core\Database::getInstance();
    $parser = new \App\Services\AIParser\NLParser();

    $clientId = 1;
    $moduleSlug = 'google_ads';
    $periodStart = '2026-07-01';

    $stmt = $db->prepare("INSERT INTO client_ai_import_jobs (client_id, module, provider, status, period_start, created_by) VALUES (?, ?, 'google_ads_advisor', 'pending_review', ?, ?)");
    $stmt->execute([$clientId, $moduleSlug, $periodStart, 1]);
    $jobId = $db->lastInsertId();

    $sec = 'kpis';
    $rawText = 'Total Spend: $1,000.00';

    $ins = $db->prepare("INSERT INTO client_ai_imports (job_id, section, raw_response) VALUES (?, ?, ?)");
    $ins->execute([$jobId, $sec, $rawText]);
    $importId = $db->lastInsertId();

    $parsed = $parser->parse($rawText, $sec);
    
    $upd = $db->prepare("UPDATE client_ai_imports SET parsed_json = ? WHERE id = ?");
    $upd->execute([json_encode($parsed), $importId]);

    echo "Success! Job ID: " . $jobId;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
