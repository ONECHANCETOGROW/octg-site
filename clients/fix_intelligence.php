<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Services/Storage/StorageInterface.php';
require_once __DIR__ . '/app/Services/Storage/LocalStorageDriver.php';

use App\Core\DbAdapter;

// Ensure Autoloader works
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

$db = DbAdapter::instance();
try {
    $driver = new LocalStorageDriver();
    $prop = (new ReflectionClass($driver))->getProperty('basePath');
    $prop->setAccessible(true);
$path = $prop->getValue($driver);

$files = glob($path . '/clients/*/*/09-contract/intelligence.json');

foreach ($files as $file) {
    echo "Processing $file\n";
    preg_match('/clients\/(\d+)\/(\d+)\/09-contract/', $file, $matches);
    if (!$matches) continue;
    $clientId = (int)$matches[1];
    $auditId = (int)$matches[2];
    
    // Regenerate the knowledge block!
    $factRepo = new \App\Modules\MarketingIntel\KnowledgeFactRepository($db);
    $requirementRepo = new \App\Modules\MarketingIntel\RequirementRepository($db);
    $auditRepo = new \App\Modules\MarketingIntel\AuditRepository($db);
    $adapter = new \App\Modules\MarketingIntel\Knowledge\KnowledgeBuilderAdapter($factRepo, $requirementRepo, $auditRepo);
    $facts = $adapter->factsForAudit($auditId);
    
    // Call the builder
    $builder = new \App\Modules\MarketingIntel\Bridge\KnowledgeContractBuilder();
    
    // Get plugin
    $channels = $auditRepo->channelsForAudit($auditId);
    $plugin = !empty($channels) ? strtolower(str_replace(' ', '_', $channels[0]['name'])) : 'google_ads';
    
    $manifestPath = BASE_PATH . "/intelligence_engine/plugins/{$plugin}/plugin.json";
    $pluginManifest = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
    
    $knowledgeJson = $builder->build($facts, $pluginManifest, $clientId, $auditId);
    
    // Inject it back into intelligence.json
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    
    $data['knowledge'] = $knowledgeJson;
    
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    echo "Updated $file\n";
}
echo "Done!\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
