<?php
ini_set('display_errors', 1); error_reporting(E_ALL);

echo 'Starting...<br>';
try {
    require_once __DIR__ . '/config/config.php';
    echo 'Config loaded.<br>';
    
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $base_dir = __DIR__ . '/app/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) return;
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) require $file;
    });
    
    require_once __DIR__ . '/app/Core/Database.php';
    echo 'Database loaded.<br>';
    
    require_once __DIR__ . '/app/Core/DbAdapter.php';
    echo 'DbAdapter loaded.<br>';
    
    require_once __DIR__ . '/app/Database/MarketingIntelSeeder.php';
    echo 'Seeder loaded.<br>';
    
    $db = \App\Core\DbAdapter::instance();
    echo 'DB connected.<br>';
    
    $counts = \App\Database\Seeds\MarketingIntelSeeder::run($db, __DIR__ . '/app/Database/marketing_intel_catalog.json');
    echo 'SUCCESS! Counts: ' . json_encode($counts) . '<br>';
} catch (\Throwable $e) {
    echo '<br><b>EXCEPTION CAUGHT:</b> ' . $e->getMessage() . '<br>in ' . $e->getFile() . ' on line ' . $e->getLine();
}
