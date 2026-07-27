<?php
// Layout wrapper for all protected SaaS views
require_once BASE_PATH . '/app/Models/Notification.php';
$notifModel = new Notification();
$unreadNotifs = isset($_SESSION['user_id']) ? $notifModel->getUnreadForUser($_SESSION['user_id']) : [];
$unreadCount = count($unreadNotifs);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'OCTG Intelligence'); ?></title>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="/css/marketing_intel.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <div class="app-layout">
        
        <!-- SIDEBAR -->
        <aside class="app-sidebar">
            <div class="sidebar-header">
                OCTG Platform
            </div>
            <nav class="sidebar-nav">
                <a href="/dashboard" class="nav-item <?php echo ($active_menu ?? '') === 'dashboard' ? 'active' : ''; ?>">
                    <i data-lucide="layout-dashboard"></i> Dashboard
                </a>
                <a href="/clients" class="nav-item <?php echo ($active_menu ?? '') === 'clients' ? 'active' : ''; ?>">
                    <i data-lucide="users"></i> Clients
                </a>
                <a href="/reports" class="nav-item <?php echo ($active_menu ?? '') === 'reports' ? 'active' : ''; ?>">
                    <i data-lucide="file-text"></i> Reports
                </a>
                <a href="/activity" class="nav-item <?php echo ($active_menu ?? '') === 'activity' ? 'active' : ''; ?>">
                    <i data-lucide="activity"></i> Activity
                </a>
                <a href="/settings" class="nav-item <?php echo ($active_menu ?? '') === 'settings' ? 'active' : ''; ?>">
                    <i data-lucide="settings"></i> Settings
                </a>
            </nav>
            <div class="sidebar-footer">
                <a href="/audits/create" class="btn btn-primary" style="width: 100%; justify-content: center; margin-bottom: 16px;">
                    <i data-lucide="plus" width="16" style="margin-right: 8px;"></i> New Audit
                </a>
                <div class="user-profile">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <a href="/logout" style="color: var(--text-muted);" title="Logout">
                        <i data-lucide="log-out" width="18"></i>
                    </a>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="app-main">
            <!-- TOP NAVIGATION -->
            <header class="app-header">
                <div class="breadcrumbs">
                    <a href="/dashboard">Home</a>
                    <?php if (isset($breadcrumbs)): ?>
                        <?php foreach($breadcrumbs as $bc): ?>
                            <span class="separator">/</span>
                            <?php if (isset($bc['url'])): ?>
                                <a href="<?php echo htmlspecialchars($bc['url']); ?>"><?php echo htmlspecialchars($bc['label']); ?></a>
                            <?php else: ?>
                                <span class="current"><?php echo htmlspecialchars($bc['label']); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="header-actions">
                    <div style="position: relative; display: inline-block;">
                        <button class="btn btn-secondary" style="padding: 6px; border-radius: 50%; position: relative;" onclick="document.getElementById('notif-dropdown').classList.toggle('show')">
                            <i data-lucide="bell" width="18"></i>
                            <?php if ($unreadCount > 0): ?>
                            <span style="position: absolute; top: -2px; right: -2px; width: 10px; height: 10px; background: var(--danger); border-radius: 50%; border: 2px solid var(--bg-surface);"></span>
                            <?php endif; ?>
                        </button>
                        <div id="notif-dropdown" style="display: none; position: absolute; right: 0; top: 100%; margin-top: 8px; width: 300px; background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-md); box-shadow: 0 4px 12px rgba(0,0,0,0.1); z-index: 100;">
                            <div style="padding: 12px; border-bottom: 1px solid var(--border); font-weight: 600; display: flex; justify-content: space-between; align-items: center;">
                                Notifications
                                <?php if($unreadCount > 0): ?><span class="badge badge-primary"><?php echo $unreadCount; ?> New</span><?php endif; ?>
                            </div>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <?php if ($unreadCount === 0): ?>
                                <div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 13px;">No new notifications</div>
                                <?php else: ?>
                                    <?php foreach ($unreadNotifs as $n): ?>
                                    <div style="padding: 12px; border-bottom: 1px solid var(--border); font-size: 13px;">
                                        <div style="font-weight: 500; margin-bottom: 4px;"><?php echo htmlspecialchars($n['title']); ?></div>
                                        <div style="color: var(--text-muted); margin-bottom: 8px;"><?php echo htmlspecialchars($n['message']); ?></div>
                                        <button class="btn btn-secondary" style="padding: 2px 8px; font-size: 11px;" onclick="markLayoutRead(<?php echo $n['id']; ?>)">Mark Read</button>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div style="padding: 8px; text-align: center; border-top: 1px solid var(--border);">
                                <a href="/notifications" style="font-size: 13px; color: var(--primary); text-decoration: none; font-weight: 500;">View All Notifications</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- SCROLLABLE CONTENT AREA -->
            <div class="app-content">
                <?php require BASE_PATH . "/app/Views/{$content_view}.php"; ?>
            </div>
        </main>

    </div>
    
    <script>
      lucide.createIcons();
      document.addEventListener('click', function(e) {
          const dropdown = document.getElementById('notif-dropdown');
          if (dropdown && dropdown.classList.contains('show') && !e.target.closest('.header-actions')) {
              dropdown.classList.remove('show');
          }
      });
      function markLayoutRead(id) {
          let fd = new FormData();
          fd.append('id', id);
          fetch('/notifications/read', { method: 'POST', body: fd })
          .then(r => r.json())
          .then(d => { if(d.success) location.reload(); });
      }
    </script>
    <style>
    #notif-dropdown.show { display: block !important; }
    </style>
</body>
</html>
