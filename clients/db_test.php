<?php
define('BASE_PATH', __DIR__);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/index.php'; // gets autoloader

use App\Core\DbAdapter;
$db = DbAdapter::instance();
$audits = $db->query("SELECT id, created_at, status FROM mi_audits ORDER BY id DESC LIMIT 5");
$latestId = $audits[0]['id'] ?? 0;
$facts = $db->query("SELECT * FROM mi_knowledge_facts WHERE audit_id = ?", [$latestId]);
header('Content-Type: application/json');
echo json_encode([
    'audits' => $audits,
    'facts' => $facts
]);
