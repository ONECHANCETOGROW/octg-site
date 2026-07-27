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
        if (file_exists($file)) require $file;
    });

    require_once __DIR__ . '/app/Core/Database.php';
    require_once __DIR__ . '/app/Core/Model.php';
    require_once __DIR__ . '/app/Core/Controller.php';
    require_once __DIR__ . '/app/Core/Router.php';
    echo 'Core loaded.<br>';

    // Mock Session
    session_start();
    $_SESSION['user_id'] = 1;
    $_SESSION['email'] = 'admin@example.com';
    $_SESSION['name'] = 'Admin';
    echo 'Session mocked.<br>';

    $request = new \App\Core\Request();
    echo 'Request created.<br>';

    $controller = new \App\Modules\MarketingIntel\IntelAuditController();
    echo 'Controller created.<br>';

    $controller->create($request);
    echo 'Create called.<br>';

} catch (\Throwable $e) {
    echo '<br><b>EXCEPTION CAUGHT:</b> ' . $e->getMessage() . '<br>in ' . $e->getFile() . ' on line ' . $e->getLine();
}
