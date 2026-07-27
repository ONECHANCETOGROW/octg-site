<?php
/* ==========================================================================
   SETTINGS.PHP — Admin manager for Website Settings.
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
        if ($_POST['action'] === 'save_setting') {
            $group = trim($_POST['setting_group']);
            $key = trim($_POST['setting_key']);
            $val = trim($_POST['setting_value']);
            $desc = trim($_POST['description']);
            
            $stmt = $pdo->prepare('
                INSERT INTO website_settings (setting_group, setting_key, setting_value, description) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), description = VALUES(description)
            ');
            
            if ($stmt->execute([$group, $key, $val, $desc])) {
                $success = 'Setting updated successfully.';
            } else {
                $error = 'Failed to update setting.';
            }
        }
    }
}

// Fetch settings grouped by group
$stmt = $pdo->query('SELECT * FROM website_settings ORDER BY setting_group ASC, setting_key ASC');
$allSettings = $stmt->fetchAll();
$settingsByGroup = [];
foreach ($allSettings as $s) {
    $settingsByGroup[$s['setting_group']][] = $s;
}

$adminPageTitle = 'Website Settings';
$adminActive = 'settings';
require __DIR__ . '/includes/admin-layout-start.php';
?>

<div class="admin-panel">
  <div class="admin-panel__header">
    <h2>Website Settings</h2>
    <p style="color:#666; margin-top:5px; font-size:0.9rem;">Global configuration for the website and integrations.</p>
  </div>

  <?php if ($error): ?>
  <div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="admin-alert admin-alert--success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="admin-card">
    <div class="admin-card__head">
      <h3>Add / Update Setting</h3>
    </div>
    <div class="admin-card__body">
      <form method="post" action="settings.php" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="action" value="save_setting">
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="admin-form__row">
              <label>Setting Group</label>
              <select name="setting_group" class="admin-input" required>
                <option value="General">General</option>
                <option value="Brand">Brand</option>
                <option value="SEO">SEO</option>
                <option value="Social">Social</option>
                <option value="Analytics">Analytics</option>
                <option value="Contact">Contact</option>
                <option value="Security">Security</option>
              </select>
            </div>
            <div class="admin-form__row">
              <label>Setting Key <span class="required">*</span></label>
              <input type="text" name="setting_key" required class="admin-input" placeholder="e.g. contact_email">
            </div>
        </div>
        
        <div class="admin-form__row">
          <label>Setting Value</label>
          <textarea name="setting_value" class="admin-input" rows="3" placeholder="Value"></textarea>
        </div>
        
        <div class="admin-form__row">
          <label>Description (Optional)</label>
          <input type="text" name="description" class="admin-input" placeholder="What is this used for?">
        </div>
        
        <div class="admin-form__actions">
          <button type="submit" class="admin-btn admin-btn--primary">Save Setting</button>
        </div>
      </form>
    </div>
  </div>

  <?php foreach ($settingsByGroup as $group => $settings): ?>
  <div class="admin-card" style="margin-top: 30px;">
    <div class="admin-card__head">
      <h3><?php echo htmlspecialchars($group); ?> Settings</h3>
    </div>
    <div class="admin-card__body">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:25%">Key</th>
            <th style="width:40%">Value</th>
            <th style="width:35%">Description</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($settings as $s): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($s['setting_key']); ?></strong></td>
            <td>
              <div style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                <?php echo htmlspecialchars($s['setting_value']); ?>
              </div>
            </td>
            <td><span style="color:#6b6b6b; font-size:0.85rem;"><?php echo htmlspecialchars($s['description']); ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endforeach; ?>

</div>

<?php require __DIR__ . '/includes/admin-layout-end.php'; ?>
