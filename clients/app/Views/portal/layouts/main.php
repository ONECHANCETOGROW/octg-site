<?php
/**
 * Layout wrapper for every client-portal page.
 */
require_once BASE_PATH . '/app/Core/PlatformIconHelper.php';
use App\Core\PlatformIconHelper;

$slug = $client['slug'] ?? '';
$disabledModules = $disabledModules ?? [];

// Helper check for active modules
$isEnabled = static fn (string $code): bool =>
    !in_array($code, $disabledModules, true) &&
    !in_array(str_replace('-', '_', $code), $disabledModules, true) &&
    !in_array(str_replace('_', '-', $code), $disabledModules, true);

// FOCUS MODE: temporarily hides every nav item except Executive Summary and
// Google Ads while that module is being built out. Nothing behind these
// links is removed -- routes, controllers, and data all still work, they're
// just not linked from the sidebar. Set this back to false to restore the
// full nav once the other modules are ready to show clients again.
$portalFocusModeGoogleAdsOnly = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(($title ?? 'Dashboard') . ' - ' . ($client['business_name'] ?? 'Client Portal')); ?></title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
    <link rel="stylesheet" href="/assets/css/portal-premium.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="app-sidebar">
            <div class="sidebar-header">
                <div class="header-logo">
                    <span style="background: linear-gradient(135deg, var(--primary), #2563eb); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">OCTG</span>
                    <span style="font-weight: 400; color: var(--text-muted); font-size:12px; margin-left:6px;">Portal</span>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="/client/<?php echo $slug; ?>/dashboard" class="nav-item <?php echo ($active_menu ?? '') === 'dashboard' ? 'active' : ''; ?>">
                    <i data-lucide="layout-dashboard" class="nav-icon"></i> Executive Summary
                </a>
                
                <?php if ($isEnabled('google-ads')): ?>
                <a href="/client/<?php echo $slug; ?>/google-ads" class="nav-item <?php echo ($active_menu ?? '') === 'google-ads' ? 'active' : ''; ?>">
                    <?php echo PlatformIconHelper::getSvgSized('google-ads', 20); ?> Google Ads
                </a>
                <?php endif; ?>
                
                <?php if (!$portalFocusModeGoogleAdsOnly && $isEnabled('seo')): ?>
                <a href="/client/<?php echo $slug; ?>/seo" class="nav-item <?php echo ($active_menu ?? '') === 'seo' ? 'active' : ''; ?>">
                    <?php echo PlatformIconHelper::getSvgSized('seo', 20); ?> Website SEO
                </a>
                <?php endif; ?>

                <?php if (!$portalFocusModeGoogleAdsOnly && $isEnabled('gbp')): ?>
                <a href="/client/<?php echo $slug; ?>/gbp" class="nav-item <?php echo ($active_menu ?? '') === 'gbp' ? 'active' : ''; ?>">
                    <?php echo PlatformIconHelper::getSvgSized('google-business-profile', 20); ?> Google Business Profile
                </a>
                <?php endif; ?>

                <?php if (!$portalFocusModeGoogleAdsOnly && $isEnabled('social')): ?>
                <a href="/client/<?php echo $slug; ?>/social" class="nav-item <?php echo ($active_menu ?? '') === 'social' ? 'active' : ''; ?>">
                    <?php echo PlatformIconHelper::getSvgSized('facebook', 20); ?> Social Media
                </a>
                <?php endif; ?>

                <?php if (!$portalFocusModeGoogleAdsOnly && $isEnabled('website-performance')): ?>
                <a href="/client/<?php echo $slug; ?>/website-performance" class="nav-item <?php echo ($active_menu ?? '') === 'website-performance' ? 'active' : ''; ?>">
                    <?php echo PlatformIconHelper::getSvgSized('website', 20); ?> Website Performance
                </a>
                <?php endif; ?>

                <?php if (!$portalFocusModeGoogleAdsOnly): ?>
                <a href="/client/<?php echo $slug; ?>/reports" class="nav-item <?php echo ($active_menu ?? '') === 'reports' ? 'active' : ''; ?>">
                    <i data-lucide="file-text" class="nav-icon"></i> Reports
                </a>

                <a href="/client/<?php echo $slug; ?>/recommendations" class="nav-item <?php echo ($active_menu ?? '') === 'recommendations' ? 'active' : ''; ?>">
                    <i data-lucide="list-checks" class="nav-icon"></i> Recommendations
                </a>

                <a href="/client/<?php echo $slug; ?>/timeline" class="nav-item <?php echo ($active_menu ?? '') === 'timeline' ? 'active' : ''; ?>">
                    <i data-lucide="history" class="nav-icon"></i> Business Timeline
                </a>
                <?php endif; ?>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar"><?php echo strtoupper(substr($client['business_name'] ?? 'C', 0, 1)); ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($client['business_name'] ?? 'Client'); ?></div>
                        <div class="user-role"><?php echo htmlspecialchars($_SESSION['client_email'] ?? ''); ?></div>
                    </div>
                    <a href="/portal/logout" style="color: var(--text-muted); display:flex; align-items:center;" title="Logout">
                        <i data-lucide="log-out" width="18"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="app-main">
            <header class="app-header">
                <div class="breadcrumbs">
                    <span class="current"><?php echo htmlspecialchars($title ?? ''); ?></span>
                </div>
                <div class="header-actions">
                    <a href="/portal/change-password" class="btn btn-secondary" style="padding: 8px 16px; font-size: 13px; display:inline-flex; align-items:center; gap:6px;">
                        <i data-lucide="settings" width="14"></i> Settings
                    </a>
                </div>
            </header>
            <div class="app-content">
                <?php require $viewFile; ?>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
