<?php
/* ==========================================================================
   NAVIGATION.PHP — Admin manager for Website Menus.
   ========================================================================== */
require_once __DIR__ . '/includes/admin-auth.php';

$pdo = octg_db();
if (!$pdo) {
    die("Database connection failed.");
}

$error = '';
$success = '';

// Handle Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!octg_verify_csrf()) {
        $error = 'Invalid CSRF token.';
    } else {
        if ($_POST['action'] === 'add_menu_item') {
            $menu_location = trim($_POST['menu_location']);
            $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            $label = trim($_POST['label']);
            $url = trim($_POST['url']);
            $is_external = isset($_POST['is_external']) ? 1 : 0;
            $display_order = (int)$_POST['display_order'];
            
            $stmt = $pdo->prepare('
                INSERT INTO navigation_menus (menu_location, parent_id, label, url, target, display_order) 
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            
            if ($stmt->execute([$menu_location, $parent_id, $label, $url, $is_external ? '_blank' : '_self', $display_order])) {
                $success = 'Menu item added successfully.';
            } else {
                $error = 'Failed to add menu item.';
            }
        }
    }
}

// Fetch all menu items
$stmt = $pdo->query('SELECT * FROM navigation_menus ORDER BY menu_location ASC, parent_id ASC, display_order ASC');
$navItems = $stmt->fetchAll();

$navTree = [];
foreach ($navItems as $item) {
    $navTree[$item['menu_location']][] = $item;
}

$adminPageTitle = 'Navigation Manager';
$adminActive = 'navigation'; // Add to NAV_ITEMS later
require __DIR__ . '/includes/admin-layout-start.php';
?>

<div class="admin-panel">
  <div class="admin-panel__header">
    <h2>Navigation Manager</h2>
    <p style="color:#666; margin-top:5px; font-size:0.9rem;">Manage header, footer, and mobile menus dynamically.</p>
  </div>

  <?php if ($error): ?>
  <div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="admin-alert admin-alert--success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="admin-card">
    <div class="admin-card__head">
      <h3>Add Menu Item</h3>
    </div>
    <div class="admin-card__body">
      <form method="post" action="navigation.php" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="action" value="add_menu_item">
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="admin-form__row">
              <label>Menu Location <span class="required">*</span></label>
              <select name="menu_location" class="admin-input" required>
                <option value="header_main">Header Main Nav</option>
                <option value="header_mega">Header Mega Menu</option>
                <option value="footer_services">Footer Services</option>
                <option value="footer_company">Footer Company</option>
                <option value="footer_resources">Footer Resources</option>
                <option value="mobile_nav">Mobile Menu</option>
              </select>
            </div>
            <div class="admin-form__row">
              <label>Parent ID (Submenu of)</label>
              <input type="number" name="parent_id" class="admin-input" placeholder="Optional">
            </div>
        </div>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="admin-form__row">
              <label>Label <span class="required">*</span></label>
              <input type="text" name="label" required class="admin-input" placeholder="e.g. About Us">
            </div>
            <div class="admin-form__row">
              <label>URL / Path <span class="required">*</span></label>
              <input type="text" name="url" required class="admin-input" placeholder="e.g. /about.php or https://...">
            </div>
        </div>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="admin-form__row" style="display:flex; align-items:center; gap:10px;">
              <input type="checkbox" name="is_external" id="is_external" value="1">
              <label for="is_external" style="margin:0;">Open in new tab (External link)</label>
            </div>
            <div class="admin-form__row">
              <label>Display Order</label>
              <input type="number" name="display_order" class="admin-input" value="0">
            </div>
        </div>
        
        <div class="admin-form__actions">
          <button type="submit" class="admin-btn admin-btn--primary">Add Menu Item</button>
        </div>
      </form>
    </div>
  </div>

  <?php foreach ($navTree as $location => $items): ?>
  <div class="admin-card" style="margin-top: 30px;">
    <div class="admin-card__head">
      <h3><?php echo htmlspecialchars($location); ?></h3>
    </div>
    <div class="admin-card__body">
      <table class="admin-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Label</th>
            <th>URL</th>
            <th>Parent ID</th>
            <th>Order</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($items as $s): ?>
          <tr>
            <td><?php echo $s['id']; ?></td>
            <td><strong><?php echo htmlspecialchars($s['label']); ?></strong> <?php if($s['target'] === '_blank') echo '<span style="color:#999; font-size:0.8em;">(Ext)</span>'; ?></td>
            <td><?php echo htmlspecialchars($s['url']); ?></td>
            <td><?php echo $s['parent_id'] ?: '-'; ?></td>
            <td><?php echo $s['display_order']; ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>

</div>

<?php require __DIR__ . '/includes/admin-layout-end.php'; ?>
