<?php

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/app/Core/Database.php';
require_once BASE_PATH . '/app/Core/Controller.php'; // Needed for IntelAuditController inheritance
require_once BASE_PATH . '/app/Core/IntelController.php';
require_once BASE_PATH . '/app/Services/AIParser/NLParser.php';
require_once BASE_PATH . '/app/Services/AIParser/AuditMerger.php';
require_once BASE_PATH . '/app/Modules/MarketingIntel/IntelAuditController.php';
require_once BASE_PATH . '/app/Models/Client.php';
require_once BASE_PATH . '/app/Models/PortalModule.php';
require_once BASE_PATH . '/app/Models/ClientPortalMetric.php';
require_once BASE_PATH . '/app/Models/ClientPortalScore.php';

session_start();
$_SESSION['user_id'] = 1;

try {
    $db = Database::getInstance();

    // 1. Find Client
    $stmt = $db->prepare("SELECT id, business_name FROM clients WHERE business_name LIKE '%Independent RV%' LIMIT 1");
    $stmt->execute();
    $client = $stmt->fetch();
    if (!$client) die("Client not found\n");
    $clientId = $client['id'];

    echo "Client: {$client['business_name']} (ID: $clientId)\n";

    // 2. Find latest job
    $stmt = $db->prepare("SELECT * FROM client_ai_import_jobs WHERE client_id = ? AND module = 'google_ads' ORDER BY id DESC LIMIT 1");
    $stmt->execute([$clientId]);
    $job = $stmt->fetch();
    if (!$job) die("No AI import job found\n");
    $jobId = $job['id'];
    $periodStart = $job['period_start'];

    echo "Job ID: $jobId, Period: $periodStart\n";

    // 3. Fetch raw imports
    $stmt = $db->prepare("SELECT section, raw_response FROM client_ai_imports WHERE job_id = ?");
    $stmt->execute([$jobId]);
    $imports = $stmt->fetchAll();

    $parser = new \App\Services\AIParser\NLParser();
    $parsedSections = [];
    $sectionsCount = 0;

    foreach ($imports as $import) {
        $sec = $import['section'];
        $raw = $import['raw_response'];
        if (trim($raw) === '') continue;

        $parsed = $parser->parse($raw, $sec);
        if (!empty($parsed)) {
            $parsedSections[$sec] = $parsed;
            $sectionsCount++;
        }
    }

    echo "Parsed $sectionsCount sections successfully.\n";

    // 4. Merge and Create Audit
    $merger = new \App\Services\AIParser\AuditMerger();
    $auditId = $merger->mergeAndCreateAudit($clientId, 'google_ads', $periodStart, $parsedSections);

    echo "Created Audit ID: $auditId\n";

    // 5. Process Audit (builds JSON, calls Node engine)
    $_POST['audit_id'] = $auditId;
    // Mock the Request class
    class MockRequest extends \App\Core\Request {
        public function post($key, $default = null) {
            return $_POST[$key] ?? $default;
        }
    }
    
    $_POST['csrf_token'] = 'mock';
    $_SESSION['csrf_token'] = 'mock';

    $req = new MockRequest();
    $intel = new \App\Modules\MarketingIntel\IntelAuditController();
    
    // Catch redirects
    ob_start();
    try {
        $intel->process($req, []);
    } catch (Exception $e) {
        echo "Error in process: " . $e->getMessage() . "\n";
    }
    ob_end_clean();

    echo "IntelAuditController::process() finished.\n";

    // 6. Verify Results
    $modModel = new PortalModule();
    $mod = $modModel->getBySlug('google_ads');
    $modId = $mod['id'];

    $metricModel = new ClientPortalMetric();
    $metrics = $metricModel->getForPeriod($clientId, $modId, '', $periodStart);
    echo "\n--- METRICS ---\n";
    if ($metrics) {
        $data = json_decode($metrics['data_json'], true);
        echo "Spend: " . ($data['spend'] ?? 'N/A') . "\n";
        echo "Conversions: " . ($data['conversions'] ?? 'N/A') . "\n";
        echo "Clicks: " . ($data['clicks'] ?? 'N/A') . "\n";
        echo "Impressions: " . ($data['impressions'] ?? 'N/A') . "\n";
    } else {
        echo "No metrics found.\n";
    }

    $scoreModel = new ClientPortalScore();
    $score = $scoreModel->getForPeriod($clientId, $modId, $periodStart);
    echo "\n--- SCORE ---\n";
    if ($score) {
        echo "Score: {$score['score']}\n";
        echo "Grade: {$score['grade']}\n";
        echo "Status: {$score['health_status']}\n";
        echo "Win: {$score['biggest_win']}\n";
        echo "Risk: {$score['biggest_risk']}\n";
        echo "Action: {$score['priority_this_month']}\n";
    } else {
        echo "No score found.\n";
    }

    // Check Recommendations
    $stmt = $db->prepare("SELECT * FROM client_portal_recommendations WHERE client_id = ? AND module_id = ? AND period_start = ?");
    $stmt->execute([$clientId, $modId, $periodStart]);
    $recs = $stmt->fetchAll();
    echo "\n--- RECOMMENDATIONS ---\n";
    echo "Count: " . count($recs) . "\n";
    foreach ($recs as $r) {
        echo "- [{$r['priority']}] {$r['what_to_change']} (Why: {$r['why_it_matters']})\n";
    }

    // Verify raw tables (Campaigns, Keywords, Search Terms) were populated in KnowledgeFacts
    $stmt = $db->prepare("SELECT entity_type, COUNT(*) as count FROM mi_knowledge_facts WHERE audit_id = ? GROUP BY entity_type");
    $stmt->execute([$auditId]);
    $facts = $stmt->fetchAll();
    echo "\n--- KNOWLEDGE FACTS ---\n";
    foreach ($facts as $f) {
        echo "{$f['entity_type']}: {$f['count']}\n";
    }

    // Executive summary details check
    $stmt = $db->prepare("SELECT * FROM mi_knowledge_facts WHERE audit_id = ? AND entity_type = 'executive_summary'");
    $stmt->execute([$auditId]);
    $es = $stmt->fetchAll();
    echo "\n--- EXECUTIVE SUMMARY TEXT ---\n";
    foreach ($es as $f) {
        echo "{$f['field_name']}: " . substr(json_decode($f['field_value'], true)['value'] ?? '', 0, 100) . "...\n";
    }

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
