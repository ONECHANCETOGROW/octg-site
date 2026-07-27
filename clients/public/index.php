<?php
// One Chance To Grow Marketing Intelligence Platform
// Front Controller

session_start();

// Define base path (Assuming index.php is at root of subdomain in this structure)
define('BASE_PATH', __DIR__);

// Load configuration
require_once BASE_PATH . '/config/config.php';

// Load Core classes
require_once BASE_PATH . '/app/Core/Database.php';
require_once BASE_PATH . '/app/Core/Model.php';
require_once BASE_PATH . '/app/Core/Controller.php';
require_once BASE_PATH . '/app/Core/Router.php';

// PSR-4 Autoloader for App\ namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = BASE_PATH . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize router
$router = new Router();

// Define routes
// Dashboard (Protected)
$router->get('/', 'DashboardController', 'index', ['AuthMiddleware']);
$router->get('/dashboard', 'DashboardController', 'index', ['AuthMiddleware']);

// Clients (Protected)
$router->get('/clients', 'ClientController', 'index', ['AuthMiddleware']);
$router->get('/clients/create', 'ClientController', 'create', ['AuthMiddleware']);
$router->post('/clients/store', 'ClientController', 'store', ['AuthMiddleware']);
$router->get('/clients/edit', 'ClientController', 'edit', ['AuthMiddleware']);
$router->post('/clients/update', 'ClientController', 'update', ['AuthMiddleware']);
$router->post('/clients/delete', 'ClientController', 'delete', ['AuthMiddleware']);
$router->post('/clients/update-email', 'ClientController', 'updatePortalEmail', ['AuthMiddleware']);
$router->post('/clients/send-invite', 'ClientController', 'sendPortalInvite', ['AuthMiddleware']);

// Audits (Protected)
$router->get('/audits/wizard', 'AuditController', 'wizard', ['AuthMiddleware']);
$router->post('/audits/store', 'AuditController', 'store', ['AuthMiddleware']);
$router->get('/audits/upload', 'AuditController', 'upload', ['AuthMiddleware']);
$router->get('/audits/show', 'AuditController', 'show', ['AuthMiddleware']);
// Upload Pipeline
$router->post('/upload/process', 'UploadController', 'process', ['AuthMiddleware']);

// AI Data Collection System (MarketingIntel Module)
$router->get('/audits/create', 'App\Modules\MarketingIntel\IntelAuditController', 'create', ['AuthMiddleware']);
$router->post('/audits/store', 'App\Modules\MarketingIntel\IntelAuditController', 'store', ['AuthMiddleware']);
$router->post('/audits/process', 'App\Modules\MarketingIntel\IntelAuditController', 'process', ['AuthMiddleware']);
$router->get('/audits/([0-9]+)/cockpit', 'App\Modules\MarketingIntel\IntelAuditController', 'cockpit', ['AuthMiddleware']);
$router->get('/audits/([0-9]+)/provenance', 'App\Modules\MarketingIntel\IntelAuditController', 'provenance', ['AuthMiddleware']);
$router->get('/audits/([0-9]+)/knowledge', 'App\Modules\MarketingIntel\IntelAuditController', 'knowledge', ['AuthMiddleware']);
$router->get('/audits/([0-9]+)/collect/([0-9]+)', 'App\Modules\MarketingIntel\CollectionController', 'collect', ['AuthMiddleware']);
$router->post('/audits/([0-9]+)/collect/([0-9]+)', 'App\Modules\MarketingIntel\CollectionController', 'store', ['AuthMiddleware']);
$router->post('/audits/([0-9]+)/merge/([0-9]+)', 'App\Modules\MarketingIntel\CollectionController', 'merge', ['AuthMiddleware']);

// Reports (Protected)
$router->get('/reports', 'ReportController', 'index', ['AuthMiddleware']);
$router->get('/reports/view', 'ReportController', 'viewReport', ['AuthMiddleware']);

// Activity & Notifications
$router->get('/activity', 'ActivityController', 'index', ['AuthMiddleware']);
$router->post('/notifications/read', 'NotificationController', 'read', ['AuthMiddleware']);
$router->get('/notifications', 'NotificationController', 'index', ['AuthMiddleware']);

// Auth
$router->get('/login', 'AuthController', 'showLogin');
$router->post('/login', 'AuthController', 'login');
$router->get('/logout', 'AuthController', 'logout');
$router->get('/change-password', 'AuthController', 'showChangePassword', ['AuthMiddleware']);
$router->post('/change-password', 'AuthController', 'changePassword', ['AuthMiddleware']);
$router->get('/forgot-password', 'AuthController', 'showForgotPassword');
$router->post('/forgot-password', 'AuthController', 'forgotPassword');
$router->get('/reset-password', 'AuthController', 'showResetPassword');
$router->post('/reset-password', 'AuthController', 'resetPassword');

// Dispatch
$router->dispatch($_SERVER['REQUEST_URI']);
