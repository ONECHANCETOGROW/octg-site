<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/app/Core/Database.php';

try {
    $db = Database::getInstance();
    $clientId = 1;
    $stmt = $db->prepare("SELECT id FROM mi_audits WHERE client_id = ? ORDER BY id DESC");
    $stmt->execute([$clientId]);
    $audits = $stmt->fetchAll();
    $storagePath = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(BASE_PATH) . '/storage';
    foreach ($audits as $audit) {
        $auditId = $audit['id'];
        $contractFile = $storagePath . "/clients/{$clientId}/{$auditId}/09-contract/intelligence.json";
        if (file_exists($contractFile)) {
            $json = json_decode(file_get_contents($contractFile), true);
            // Fix the structure!
            if (isset($json['opportunities'][0])) {
                $json['opportunities'] = ['opportunities' => $json['opportunities']];
                file_put_contents($contractFile, json_encode($json, JSON_PRETTY_PRINT));
                echo "Fixed Audit ID $auditId\n";
            } else {
                echo "Audit ID $auditId already correct or empty.\n";
            }
            break;
        }
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
