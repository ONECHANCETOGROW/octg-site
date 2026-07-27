<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/app/Core/Database.php';

try {
    $db = Database::getInstance();
    $clientId = 1;
    $periodStart = '2026-07-01'; // Assuming current period

    $stmt = $db->prepare("SELECT id FROM portal_modules WHERE slug = 'google_ads'");
    $stmt->execute();
    $mod = $stmt->fetch();
    
    if ($mod) {
        $stmt = $db->prepare("SELECT score, grade, health_status FROM client_portal_scores WHERE client_id = ? AND module_id = ? AND period_start = ? ORDER BY updated_at DESC LIMIT 1");
        $stmt->execute([$clientId, $mod['id'], $periodStart]);
        $scoreRow = $stmt->fetch();

        if ($scoreRow) {
            $stmt = $db->prepare("SELECT id FROM mi_audits WHERE client_id = ? ORDER BY id DESC");
            $stmt->execute([$clientId]);
            $audits = $stmt->fetchAll();
            $storagePath = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(BASE_PATH) . '/storage';
            
            $found = false;
            foreach ($audits as $audit) {
                $auditId = $audit['id'];
                $contractFile = $storagePath . "/clients/{$clientId}/{$auditId}/09-contract/intelligence.json";
                
                if (file_exists($contractFile)) {
                    $json = json_decode(file_get_contents($contractFile), true);
                    if (isset($json['scorecard'])) {
                        $json['scorecard']['overall_score'] = (int)$scoreRow['score'];
                        if ($scoreRow['grade']) $json['scorecard']['grade'] = $scoreRow['grade'];
                        if ($scoreRow['health_status']) $json['scorecard']['health_status'] = $scoreRow['health_status'];
                        
                        file_put_contents($contractFile, json_encode($json, JSON_PRETTY_PRINT));
                        echo "intelligence.json (Audit $auditId) successfully synced with manual score: " . $scoreRow['score'];
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                echo "intelligence.json not found in any recent audit.";
            }
        } else {
            echo "No manual score found for Google Ads.";
        }
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage();
}
