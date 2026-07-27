<?php
ini_set('display_errors', 1); error_reporting(E_ALL);

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
        \ = \ . str_replace('\\\\', '/', \) . '.php';
        if (file_exists(\)) require \;
    });

    require_once __DIR__ . '/app/Core/Database.php';
    require_once __DIR__ . '/app/Core/Model.php';
    require_once __DIR__ . '/app/Core/Controller.php';
    require_once __DIR__ . '/app/Core/Router.php';
    echo 'Core loaded.<br>';

    session_start();
    \['user_id'] = 1;
    \['email'] = 'admin@example.com';
    \['name'] = 'Admin';
    \['csrf_token'] = 'test-token';
    \['csrf_token'] = 'test-token';
    \['client_id'] = 1;
    \['title'] = 'Test Audit 123';
    \['channel_ids'] = [1];
    \['known_entity_names'] = 'Test, 123';

    echo 'Session & Post mocked.<br>';

    \ = new \App\Core\Request();
    \ = new \App\Modules\MarketingIntel\IntelAuditController();
    \->store(\);
    echo 'Store called without crashing!<br>';

} catch (\Throwable \) {
    echo '<br><b>EXCEPTION CAUGHT:</b> ' . \->getMessage() . '<br>in ' . \->getFile() . ' on line ' . \->getLine();
}

