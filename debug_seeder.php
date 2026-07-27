<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
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
require_once __DIR__ . '/app/Core/DbAdapter.php';
require_once __DIR__ . '/app/Database/MarketingIntelSeeder.php';
try {
    \App\Database\Seeds\MarketingIntelSeeder::run(\App\Core\DbAdapter::instance(), __DIR__ . '/app/Database/marketing_intel_catalog.json');
    echo 'Seeder completed successfully';
} catch (\Throwable \) {
    echo 'Exception: ' . \->getMessage() . ' in ' . \->getFile() . ' on line ' . \->getLine();
}

