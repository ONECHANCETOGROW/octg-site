<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});
ini_set('display_errors', 1); error_reporting(E_ALL);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/Database.php';

$pdo = Database::getInstance();
$migrationsDir = __DIR__ . '/app/Database/Migrations';

$files = glob($migrationsDir . '/*.sql');
sort($files);

foreach ($files as $file) {
    echo "Running migration: " . basename($file) . "\n";
    $sql = file_get_contents($file);
    try {
        $pdo->exec($sql);
        echo "Success.\n";
    } catch (PDOException $e) {
        // Ignore "Table already exists" errors
        if (strpos($e->getMessage(), '1050') === false) {
            echo "Error: " . $e->getMessage() . "\n";
        } else {
            echo "Already exists.\n";
        }
    }
}

// Run Seeder
echo "Running Seeder...\n";
require_once __DIR__ . '/app/Core/DbAdapter.php';
require_once __DIR__ . '/app/Database/MarketingIntelSeeder.php';

if (!class_exists('App\Core\Database')) {
    class_alias('App\Core\DbAdapter', 'App\Core\Database');
}

try {
    \App\Database\Seeds\MarketingIntelSeeder::run(\App\Core\DbAdapter::instance(), __DIR__ . '/app/Database/marketing_intel_catalog.json');
    echo "Done.\n";
} catch (\Throwable $e) {
    echo "Seeder Error: " . $e->getMessage() . " on line " . $e->getLine() . " in " . $e->getFile() . "\n";
}
