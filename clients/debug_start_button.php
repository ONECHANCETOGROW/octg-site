<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/Database.php';

$db = Database::getInstance();

echo "<h2>Diagnosing Start Button Issue</h2>";

// Check audit 3
$stmt = $db->prepare("SELECT * FROM mi_audits WHERE id = 3");
$stmt->execute();
$audit = $stmt->fetch();
echo "<b>Audit 3:</b> " . ($audit ? $audit['title'] . " (status: " . $audit['status'] . ")" : "NOT FOUND") . "<br>";

// Check all audits
$stmt = $db->query("SELECT id, title, status, client_id FROM mi_audits ORDER BY id DESC");
$audits = $stmt->fetchAll();
echo "<b>All MI Audits:</b><br>";
foreach ($audits as $a) {
    echo "  - ID {$a['id']}: {$a['title']} (status: {$a['status']}, client: {$a['client_id']})<br>";
}

// Check requirements
$stmt = $db->query("SELECT id, catalog_key, title, is_required FROM mi_requirements ORDER BY id");
$requirements = $stmt->fetchAll();
echo "<br><b>Requirements:</b> " . count($requirements) . " found<br>";
foreach ($requirements as $r) {
    echo "  - ID {$r['id']}: {$r['title']} (key: {$r['catalog_key']}, required: {$r['is_required']})<br>";
}

// Check prompt templates
$stmt = $db->query("SELECT id, requirement_id, title, target_surface FROM mi_prompt_templates ORDER BY id");
$prompts = $stmt->fetchAll();
echo "<br><b>Prompt Templates:</b> " . count($prompts) . " found<br>";
foreach ($prompts as $p) {
    echo "  - ID {$p['id']}: req={$p['requirement_id']} \"{$p['title']}\" (surface: {$p['target_surface']})<br>";
}

// Try to hit the start URL for audit 3 requirement 1
echo "<br><b>URL test:</b> /audits/3/requirements/1 → CollectionController::show()<br>";

// Check if IntelController autoloads correctly
echo "<br><b>Checking class files:</b><br>";
$files = [
    'app/Core/IntelController.php',
    'app/Modules/MarketingIntel/CollectionController.php',
    'app/Modules/MarketingIntel/PromptTemplateRepository.php',
    'app/Modules/MarketingIntel/RequirementRepository.php',
];
foreach ($files as $f) {
    echo "  - $f: " . (file_exists(BASE_PATH . '/' . $f) ? "EXISTS ✅" : "MISSING ❌") . "<br>";
}
