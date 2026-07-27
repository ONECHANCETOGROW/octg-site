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
$router->post('/audits/intel-store', 'App\Modules\MarketingIntel\IntelAuditController', 'store', ['AuthMiddleware']);
$router->post('/audits/process', 'App\Modules\MarketingIntel\IntelAuditController', 'process', ['AuthMiddleware']);
$router->get('/audits/(?<id>[0-9]+)/cockpit', 'App\Modules\MarketingIntel\IntelAuditController', 'cockpit', ['AuthMiddleware']);
$router->get('/audits/(?<id>[0-9]+)/provenance', 'App\Modules\MarketingIntel\IntelAuditController', 'provenance', ['AuthMiddleware']);
$router->get('/audits/(?<id>[0-9]+)/knowledge', 'App\Modules\MarketingIntel\IntelAuditController', 'knowledge', ['AuthMiddleware']);
$router->get('/audits/(?<auditId>[0-9]+)/requirements/(?<requirementId>[0-9]+)', 'App\Modules\MarketingIntel\CollectionController', 'show', ['AuthMiddleware']);
$router->post('/audits/(?<auditId>[0-9]+)/requirements/(?<requirementId>[0-9]+)/collect-text', 'App\Modules\MarketingIntel\CollectionController', 'collectText', ['AuthMiddleware']);
$router->post('/audits/(?<auditId>[0-9]+)/requirements/(?<requirementId>[0-9]+)/collect-file', 'App\Modules\MarketingIntel\CollectionController', 'collectFile', ['AuthMiddleware']);
$router->post('/audits/(?<auditId>[0-9]+)/requirements/(?<requirementId>[0-9]+)/issues/(?<issueId>[0-9]+)/resolve', 'App\Modules\MarketingIntel\CollectionController', 'resolveIssue', ['AuthMiddleware']);

// Reports (Protected)
$router->get('/reports', 'ReportController', 'index', ['AuthMiddleware']);
$router->get('/reports/view', 'ReportController', 'viewReport', ['AuthMiddleware']);

// Activity & Notifications
$router->get('/activity', 'ActivityController', 'index', ['AuthMiddleware']);
$router->post('/notifications/read', 'NotificationController', 'read', ['AuthMiddleware']);
$router->get('/notifications', 'NotificationController', 'index', ['AuthMiddleware']);

// Auth (Staff)
$router->get('/login', 'AuthController', 'showLogin');
$router->post('/login', 'AuthController', 'login');
$router->get('/logout', 'AuthController', 'logout');
$router->get('/change-password', 'AuthController', 'showChangePassword', ['AuthMiddleware']);
$router->post('/change-password', 'AuthController', 'changePassword', ['AuthMiddleware']);
$router->get('/forgot-password', 'AuthController', 'showForgotPassword');
$router->post('/forgot-password', 'AuthController', 'forgotPassword');
$router->get('/reset-password', 'AuthController', 'showResetPassword');
$router->post('/reset-password', 'AuthController', 'resetPassword');

// Admin: Client Portal login management (Protected, staff-only)
$router->post('/clients/portal-login/create', 'ClientController', 'createPortalLogin', ['AuthMiddleware']);
$router->post('/clients/portal-login/reset', 'ClientController', 'resetPortalPassword', ['AuthMiddleware']);
$router->post('/clients/portal-login/toggle', 'ClientController', 'togglePortalAccess', ['AuthMiddleware']);
$router->get('/clients/modules', 'ClientController', 'showModules', ['AuthMiddleware']);
$router->post('/clients/modules', 'ClientController', 'updateModules', ['AuthMiddleware']);

// Admin: Marketing Workspace
$router->get('/clients/portal-data', 'MarketingWorkspaceController', 'index', ['AuthMiddleware']);
$router->post('/clients/portal-data/save-metrics', 'MarketingWorkspaceController', 'saveMetrics', ['AuthMiddleware']);
$router->post('/clients/portal-data/save-scores', 'MarketingWorkspaceController', 'saveScores', ['AuthMiddleware']);
$router->post('/clients/portal-data/save-notes', 'MarketingWorkspaceController', 'saveNotes', ['AuthMiddleware']);
$router->post('/clients/portal-data/add-recommendation', 'MarketingWorkspaceController', 'addRecommendation', ['AuthMiddleware']);
$router->post('/clients/portal-data/delete-recommendation', 'MarketingWorkspaceController', 'deleteRecommendation', ['AuthMiddleware']);
$router->post('/clients/portal-data/add-timeline-event', 'MarketingWorkspaceController', 'addTimelineEvent', ['AuthMiddleware']);
$router->post('/clients/portal-data/delete-timeline-event', 'MarketingWorkspaceController', 'deleteTimelineEvent', ['AuthMiddleware']);
$router->post('/clients/portal-data/ingest-ai', 'MarketingWorkspaceController', 'ingestAI', ['AuthMiddleware']);
$router->get('/clients/portal-data/review-ai', 'MarketingWorkspaceController', 'reviewAI', ['AuthMiddleware']);
$router->post('/clients/portal-data/confirm-ai', 'MarketingWorkspaceController', 'confirmAI', ['AuthMiddleware']);

// ============================================================
// CLIENT SUCCESS PORTAL (clients.onechancetogrow.com)
// A completely separate identity space from the staff app above --
// see docs/CLIENT_PORTAL.md. Auth routes are unprotected (that's the
// point); every other /client/{slug}/... route requires
// ClientAuthMiddleware, which checks $_SESSION['client_user_id'], not
// the staff $_SESSION['user_id'].
// ============================================================

// Client Auth
$router->get('/portal/login', 'ClientAuthController', 'showLogin');
$router->post('/portal/login', 'ClientAuthController', 'login');
$router->get('/portal/logout', 'ClientAuthController', 'logout');
$router->get('/portal/change-password', 'ClientAuthController', 'showChangePassword', ['ClientAuthMiddleware']);
$router->post('/portal/change-password', 'ClientAuthController', 'changePassword', ['ClientAuthMiddleware']);
$router->get('/portal/forgot-password', 'ClientAuthController', 'showForgotPassword');
$router->post('/portal/forgot-password', 'ClientAuthController', 'forgotPassword');
$router->get('/portal/reset-password', 'ClientAuthController', 'showResetPassword');
$router->post('/portal/reset-password', 'ClientAuthController', 'resetPassword');

// Client Dashboard + Modules (slug-scoped URLs, session-scoped auth --
// see ClientPortalBase::resolveSlugOrRedirect())
$router->get('/client/(?<slug>[a-z0-9-]+)/dashboard', 'App\Modules\ClientPortal\ClientDashboardController', 'index', ['ClientAuthMiddleware']);
$router->get('/client/(?<slug>[a-z0-9-]+)/google-ads', 'App\Modules\ClientPortal\GoogleAdsModuleController', 'index', ['ClientAuthMiddleware']);
$router->get('/client/(?<slug>[a-z0-9-]+)/seo', 'App\Modules\ClientPortal\SeoModuleController', 'index', ['ClientAuthMiddleware']);
$router->post('/client/(?<slug>[a-z0-9-]+)/seo/manual-entry', 'App\Modules\ClientPortal\SeoModuleController', 'saveManualEntry', ['ClientAuthMiddleware']);
$router->get('/client/(?<slug>[a-z0-9-]+)/gbp', 'App\Modules\ClientPortal\GbpModuleController', 'index', ['ClientAuthMiddleware']);
$router->post('/client/(?<slug>[a-z0-9-]+)/gbp/manual-entry', 'App\Modules\ClientPortal\GbpModuleController', 'saveManualEntry', ['ClientAuthMiddleware']);
$router->get('/client/(?<slug>[a-z0-9-]+)/social', 'App\Modules\ClientPortal\SocialModuleController', 'index', ['ClientAuthMiddleware']);
$router->post('/client/(?<slug>[a-z0-9-]+)/social/manual-entry', 'App\Modules\ClientPortal\SocialModuleController', 'saveManualEntry', ['ClientAuthMiddleware']);
$router->get('/client/(?<slug>[a-z0-9-]+)/website-performance', 'App\Modules\ClientPortal\WebsitePerformanceModuleController', 'index', ['ClientAuthMiddleware']);
$router->post('/client/(?<slug>[a-z0-9-]+)/website-performance/manual-entry', 'App\Modules\ClientPortal\WebsitePerformanceModuleController', 'saveManualEntry', ['ClientAuthMiddleware']);
$router->get('/client/(?<slug>[a-z0-9-]+)/reports', 'App\Modules\ClientPortal\ClientReportsController', 'index', ['ClientAuthMiddleware']);
$router->get('/client/(?<slug>[a-z0-9-]+)/reports/(?<reportId>[0-9]+)', 'App\Modules\ClientPortal\ClientReportsController', 'view', ['ClientAuthMiddleware']);
$router->get('/client/(?<slug>[a-z0-9-]+)/recommendations', 'App\Modules\ClientPortal\RecommendationsController', 'index', ['ClientAuthMiddleware']);
$router->post('/client/(?<slug>[a-z0-9-]+)/recommendations/(?<recId>[0-9]+)/status', 'App\Modules\ClientPortal\RecommendationsController', 'updateStatus', ['ClientAuthMiddleware']);
$router->get('/client/(?<slug>[a-z0-9-]+)/timeline', 'App\Modules\ClientPortal\TimelineController', 'index', ['ClientAuthMiddleware']);

// Dispatch
$router->dispatch($_SERVER['REQUEST_URI']);
