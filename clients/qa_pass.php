<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        echo 'Fatal error: ', $error['message'], ' in ', $error['file'], ' on line ', $error['line'];
    }
});

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/app/Core/Database.php';

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, $len)) . '.php';
    if (file_exists($file)) require $file;
});

require_once BASE_PATH . '/app/Core/DbAdapter.php';
$db = \App\Core\DbAdapter::instance();
$db->query("UPDATE mi_knowledge_facts SET value = REPLACE(value, '130,036', '13,036') WHERE value LIKE '%130,036%'");
$db->query("UPDATE mi_ai_imports SET raw_response = REPLACE(raw_response, '130,036', '13,036') WHERE raw_response LIKE '%130,036%'");

// We ALSO need to re-run the pipeline to generate intelligence.json
require_once BASE_PATH . '/app/Services/AIParser/NLParser.php';
require_once BASE_PATH . '/app/Services/AIParser/AuditMerger.php';
require_once BASE_PATH . '/app/Core/Model.php';
require_once BASE_PATH . '/app/Core/Controller.php';
require_once BASE_PATH . '/app/Core/IntelController.php';
require_once BASE_PATH . '/app/Modules/MarketingIntel/IntelAuditController.php';
require_once BASE_PATH . '/app/Models/Client.php';
require_once BASE_PATH . '/app/Models/PortalModule.php';

function flattenConfidence($data) {
    if (!is_array($data)) return $data;
    if (isset($data['value']) && isset($data['confidence'])) return flattenConfidence($data['value']);
    $result = [];
    foreach ($data as $k => $v) $result[$k] = flattenConfidence($v);
    return $result;
}

$clientId = 1;
$periodStart = '2026-07-01';
$parser = new \App\Services\AIParser\NLParser();
$parsedSections = [];
$imports = $db->all("SELECT section, raw_response FROM mi_ai_imports WHERE client_id = ? AND module_slug = 'google_ads' AND period_start = ?", [$clientId, $periodStart]);
foreach ($imports as $import) {
    if (trim($import['raw_response']) === '') continue;
    $parsed = $parser->parse($import['raw_response'], $import['section']);
    if (!empty($parsed)) $parsedSections[$import['section']] = flattenConfidence($parsed);
}
$merger = new \App\Services\AIParser\AuditMerger();
$auditId = $merger->mergeAndCreateAudit($clientId, 'google_ads', $periodStart, $parsedSections);

$intelController = new \App\Modules\MarketingIntel\IntelAuditController();
$intelController->processAudit($auditId);

echo 'Success fixing typo and regenerating pipeline';
