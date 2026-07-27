<?php
ini_set('display_errors', 1); error_reporting(E_ALL);

// Output buffering disabled
while (ob_get_level()) { ob_end_flush(); }

echo 'Starting...<br>';
try {
    require_once __DIR__ . '/config/config.php';
    echo 'Config loaded.<br>';
    
    spl_autoload_register(function (\) {
        \ = 'App\\';
        \ = __DIR__ . '/app/';
        \ = strlen(\);
        if (strncmp(\, \, \) !== 0) return;
        \ = substr(\, \);
        \ = \ . str_replace('\\', '/', \) . '.php';
        if (file_exists(\)) require \;
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
    
    \ = \App\Core\DbAdapter::instance();
    echo 'DB connected.<br>';
    
    \App\Database\Seeds\MarketingIntelSeeder::run(\, __DIR__ . '/app/Database/marketing_intel_catalog.json');
    echo 'SUCCESS';
} catch (\Throwable \) {
    echo 'EXCEPTION: ' . \->getMessage() . ' in ' . \->getFile() . ' on line ' . \->getLine();
}

