<?php
/* ==========================================================================
   ADMIN-LAYOUT-START.PHP — shared chrome for every admin page.
   Requires admin-auth.php to already have run. Set $adminPageTitle and
   $adminActive (nav key) before including this.
   ========================================================================== */
$adminPageTitle = $adminPageTitle ?? 'Admin';
$adminActive = $adminActive ?? '';
$user = octg_admin_user();

$NAV_ITEMS = [
    'dashboard' => ['label' => 'Dashboard', 'href' => '/admin8828051506/index.php', 'icon' => 'grid'],
    'leads' => ['label' => 'Leads', 'href' => '/admin8828051506/leads.php', 'icon' => 'mail'],
    'bookings' => ['label' => 'Bookings', 'href' => '/admin8828051506/bookings.php', 'icon' => 'calendar'],
    'page-builder' => ['label' => 'Page Builder', 'href' => '/admin8828051506/page-builder.php', 'icon' => 'layout'],
    'media' => ['label' => 'Media Library', 'href' => '/admin8828051506/media.php', 'icon' => 'image'],
    'team' => ['label' => 'Team', 'href' => '/admin8828051506/team.php', 'icon' => 'users'],
    'reviews' => ['label' => 'Reviews', 'href' => '/admin8828051506/reviews.php', 'icon' => 'star'],
    'blog' => ['label' => 'Blog', 'href' => '/admin8828051506/blog.php', 'icon' => 'list'],
    'navigation' => ['label' => 'Navigation', 'href' => '/admin8828051506/navigation.php', 'icon' => 'list'],
    'settings' => ['label' => 'Website Settings', 'href' => '/admin8828051506/settings.php', 'icon' => 'settings'],
    'notifications' => ['label' => 'Notifications', 'href' => '/admin8828051506/email-settings.php', 'icon' => 'bell'],
    'email-logs' => ['label' => 'Email Logs', 'href' => '/admin8828051506/email-logs.php', 'icon' => 'clock'],
    'optimizer' => ['label' => 'AI Optimizer', 'href' => '/admin8828051506/optimizer.php', 'icon' => 'sparkle'],
    'intelligence' => ['label' => 'Website Intelligence', 'href' => '/admin8828051506/intelligence.php', 'icon' => 'graph'],
];
/* seo.php, system.php, and backups.php were linked here previously but none
   of the three files exist yet — that's three permanent 404s in the sidebar
   on every admin page. Removed rather than left dead; re-add once each page
   is actually built. */

$ICONS = [
    'grid' => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>',
    'image' => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>',
    'layout' => '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/>',
    'settings' => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
    'search' => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
    'users' => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="8" r="2.5"/><path d="M21 20c0-2.5-1.6-4.6-3.8-5.4"/>',
    'mail' => '<rect x="3" y="5" width="18" height="14" rx="1"/><path d="m4 6 8 6 8-6"/>',
    'list' => '<path d="M9 6h11M9 12h11M9 18h11M4 6h.01M4 12h.01M4 18h.01"/>',
    'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
    'sparkle' => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8Z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8Z"/>',
    'graph' => '<circle cx="6" cy="6" r="2.5"/><circle cx="18" cy="6" r="2.5"/><circle cx="6" cy="18" r="2.5"/><circle cx="18" cy="18" r="2.5"/><circle cx="12" cy="12" r="2.5"/><path d="M8.2 7.3 10 10M16 7.3 14 10M8.2 16.7 10 14M16 16.7 14 14"/>',
    'star' => '<path d="M12 2.5l2.9 6 6.6.7-4.9 4.5 1.3 6.5L12 16.9l-5.9 3.3 1.3-6.5-4.9-4.5 6.6-.7Z"/>',
    'bell' => '<path d="M6 8a6 6 0 0 1 12 0c0 4 1.5 5.5 2 6H4c.5-.5 2-2 2-6Z"/><path d="M10 21a2 2 0 0 0 4 0"/>',
    'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
];
/* 'star' was referenced by the Reviews nav item but was never defined here —
   PHP emitted an undefined-array-key warning and the sidebar rendered an
   empty icon for that link. Added it, plus 'bell' for the new Notifications
   item below, rather than reusing an unrelated icon. */
?>
<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($adminPageTitle); ?> | Admin | One Chance To Grow</title>
<meta name="robots" content="noindex, nofollow">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/admin8828051506/assets/admin.css">
</head>
<body class="admin-body">
<div class="admin-shell">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar__brand">
      <span>One Chance <em>To</em> Grow</span>
      <small>Admin Panel</small>
    </div>
    <nav class="admin-nav">
      <?php foreach ($NAV_ITEMS as $key => $item): ?>
      <a href="<?php echo $item['href']; ?>" class="admin-nav__link <?php echo $adminActive === $key ? 'is-active' : ''; ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><?php echo $ICONS[$item['icon']]; ?></svg>
        <?php echo htmlspecialchars($item['label']); ?>
      </a>
      <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar__user">
      <div class="admin-sidebar__user-info">
        <strong><?php echo htmlspecialchars($user['name'] ?? ''); ?></strong>
        <span><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $user['role'] ?? ''))); ?></span>
      </div>
      <a href="/admin8828051506/logout.php" class="admin-nav__link admin-nav__link--logout">Log Out</a>
    </div>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <button class="admin-menu-toggle" id="adminMenuToggle" aria-label="Toggle menu" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
      <h1><?php echo htmlspecialchars($adminPageTitle); ?></h1>
      <a href="/index.php" target="_blank" class="admin-topbar__view-site">View Site &rarr;</a>
    </header>
    <main class="admin-content">
