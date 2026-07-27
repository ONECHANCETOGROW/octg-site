<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
try {
    require_once __DIR__ . '/config/config.php';
    spl_autoload_register(function (\) {
        \ = 'App\\';
        \ = __DIR__ . '/app/';
        \ = strlen(\);
        if (strncmp(\, \, \) !== 0) return;
        \ = substr(\, \);
        \ = \ . str_replace('\\', '/', \) . '.php';
        if (file_exists(\)) require \;
    });
    require_once __DIR__ . '/app/Core/Database.php';
    require_once __DIR__ . '/app/Core/DbAdapter.php';
    require_once __DIR__ . '/app/Database/MarketingIntelSeeder.php';
    if (!class_exists('App\Core\Database')) {
        class_alias('App\Core\DbAdapter', 'App\Core\Database');
    }
    \App\Database\Seeds\MarketingIntelSeeder::run(\App\Core\DbAdapter::instance(), __DIR__ . '/app/Database/marketing_intel_catalog.json');
    echo 'SUCCESS';
} catch (\Throwable \) {
    echo 'EXCEPTION: ' . \->getMessage() . ' in ' . \->getFile() . ' on line ' . \->getLine();
}

