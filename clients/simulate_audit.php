<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
require_once __DIR__ . '/config/config.php';
spl_autoload_register(function ($class) {
    $prefix = 'App\\'; $base_dir = __DIR__ . '/app/';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
    $file = $base_dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) require $file;
});
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/Model.php';
require_once __DIR__ . '/app/Core/Controller.php';

session_start();
$_SESSION['user_id'] = 1; $_SESSION['email'] = 'admin@example.com';
$_SESSION['name'] = 'Admin'; $_SESSION['csrf_token'] = 'test-token';

$db = \App\Core\DbAdapter::instance();
$auditId = 2;

// Get requirements for audit 2
$reqs = $db->all('
    SELECT r.id, r.code, pt.expected_columns 
    FROM mi_intelligence_requirements r
    JOIN mi_prompt_templates pt ON pt.requirement_id = r.id
    JOIN mi_audit_channels ac ON ac.channel_id = r.channel_id
    WHERE ac.audit_id = :auditId
', ['auditId' => $auditId]);

echo 'Found ' . count($reqs) . ' requirements for Audit ' . $auditId . '<br>';

$controller = new \App\Modules\MarketingIntel\CollectionController();

foreach ($reqs as $req) {
    echo 'Processing ' . $req['code'] . ' (ID: ' . $req['id'] . ')<br>';
    $cols = json_decode($req['expected_columns'], true) ?? [];
    
    // Generate Markdown table
    $md = '| ' . implode(' | ', $cols) . ' |' . "\n";
    $md .= '| ' . implode(' | ', array_fill(0, count($cols), '---')) . ' |' . "\n";
    
    // Generate 2 rows of dummy data
    for ($i = 1; $i <= 2; $i++) {
        $row = [];
        foreach ($cols as $col) {
            if (stripos($col, 'Campaign') !== false) $row[] = 'Campaign ' . $i;
            elseif (stripos($col, 'Spend') !== false || stripos($col, 'Cost') !== false) $row[] = '$100.00';
            elseif (stripos($col, 'Click') !== false || stripos($col, 'Impression') !== false || stripos($col, 'Conversion') !== false) $row[] = (100 * $i);
            elseif (stripos($col, 'CTR') !== false || stripos($col, 'Share') !== false) $row[] = '5.00%';
            else $row[] = 'Value ' . $i;
        }
        $md .= '| ' . implode(' | ', $row) . ' |' . "\n";
    }
    
    $_POST = [
        'csrf_token' => 'test-token',
        'method' => 'ai_assistant',
        'response_text' => "Here is the data:\n\n" . $md
    ];
    
    $request = new \App\Core\Request();
    try {
        // We must suppress redirects because CollectionController redirects on success
        ob_start();
        $controller->collectText($request, ['auditId' => $auditId, 'requirementId' => $req['id']]);
        ob_end_clean();
        echo '<span style="color:green">Success</span><br>';
    } catch (\Throwable $e) {
        ob_end_clean();
        echo '<span style="color:red">Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . '</span><br>';
    }
}

// Call IntelAuditController::process just in case it's needed to finalize
try {
    $auditCtrl = new \App\Modules\MarketingIntel\IntelAuditController();
    $request = new \App\Core\Request();
    $_POST = ['csrf_token' => 'test-token', 'audit_id' => $auditId];
    ob_start();
    $auditCtrl->process($request);
    ob_end_clean();
    echo '<span style="color:green">Process (finalize) called.</span><br>';
} catch (\Throwable $e) {
    ob_end_clean();
    echo '<span style="color:red">Process error: ' . $e->getMessage() . '</span><br>';
}

echo 'Finished.';
