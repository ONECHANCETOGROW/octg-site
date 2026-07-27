<?php
ini_set('display_errors', 1); error_reporting(E_ALL);

while (ob_get_level()) { ob_end_flush(); }

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
        if (file_exists($file)) {
            require $file;
            echo "Autoloaded: $file<br>";
        } else {
            echo "Failed to autoload: $file<br>";
        }
    });
    
    require_once __DIR__ . '/app/Core/DbAdapter.php';
    echo 'DbAdapter loaded.<br>';
    
    require_once __DIR__ . '/app/Core/Database.php';
    echo 'Database loaded.<br>';
    
    require_once __DIR__ . '/app/Database/MarketingIntelSeeder.php';
    echo 'Seeder loaded.<br>';
    
    if (!class_exists('App\Core\Database')) {
        class_alias('App\Core\DbAdapter', 'App\Core\Database');
    }
    
    $db = \App\Core\DbAdapter::instance();
    echo 'DB connected.<br>';
    
    \App\Database\Seeds\MarketingIntelSeeder::run($db, __DIR__ . '/app/Database/marketing_intel_catalog.json');
    echo 'SUCCESS: Seeder finished without crashing!<br>';
} catch (\Throwable $e) {
    echo 'EXCEPTION: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
}
