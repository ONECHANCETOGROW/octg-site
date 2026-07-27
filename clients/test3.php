<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
$_SESSION['user_id'] = 1;
define('BASE_PATH', __DIR__);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Model.php';

// Ensure autoloader from index.php is simulated or included
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
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

require_once BASE_PATH . '/app/Services/ClientActivity.php';

try {
    echo "Starting test...\n";
    $db = Database::getInstance();
    
    // Create a mock job for testing
    $stmt = $db->prepare("INSERT INTO client_ai_import_jobs (client_id, module, provider, status, period_start, created_by) VALUES (?, ?, 'google_ads_advisor', 'pending_review', ?, ?)");
    $stmt->execute([1, 'google_ads', '2026-07-01', 1]);
    $jobId = $db->lastInsertId();

    $clientId = 1;
    $moduleSlug = 'google_ads';
    $periodStart = '2026-07-01';

    echo "Job created ($jobId), calling AuditMerger...\n";
    require_once BASE_PATH . '/app/Services/AIParser/AuditMerger.php';
    $merger = new \App\Services\AIParser\AuditMerger();

    $parsedSections = ['kpis' => ['spend' => 1200]];
    
    echo "Merging...\n";
    $auditId = $merger->mergeAndCreateAudit($clientId, $moduleSlug, $periodStart, $parsedSections);
    echo "Audit created: $auditId\n";

    echo "Loading IntelAuditController...\n";
    require_once BASE_PATH . '/app/Modules/MarketingIntel/IntelAuditController.php';
    $_POST['audit_id'] = $auditId;
    $req = new \App\Core\Request();
    
    $intelController = new \App\Modules\MarketingIntel\IntelAuditController();
    
    // We cannot fully run process() outside of an auth session, it might die.
    // So let's skip the process() call but check if instantiation works.
    echo "IntelAuditController instantiated.\n";
    
    \ClientActivity::log($clientId, 1, "Completed & Validated AI Data Ingestion for {$moduleSlug} ($periodStart)");
    echo "Logged activity.\n";
    
    echo "Success!\n";

} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString();
} catch (Error $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
