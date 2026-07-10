<?php
/* ==========================================================================
   CONTENT.PHP — Admin manager for CMS Content blocks.
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
        if ($_POST['action'] === 'save_content') {
            $key = trim($_POST['content_key']);
            $val = trim($_POST['content_value']);
            $title = trim($_POST['title']);
            $type = $_POST['type'];
            
            // Upsert content
            $stmt = $pdo->prepare('
                INSERT INTO cms_content (content_key, title, content_value, type, status, updated_by) 
                VALUES (?, ?, ?, ?, "published", ?)
                ON DUPLICATE KEY UPDATE title = VALUES(title), content_value = VALUES(content_value), type = VALUES(type), updated_by = VALUES(updated_by)
            ');
            
            if ($stmt->execute([$key, $title, $val, $type, octg_admin_user()['id'] ?? null])) {
                $success = 'Content updated successfully.';
            } else {
                $error = 'Failed to update content.';
            }
        }
    }
}

// Fetch all content slots
$stmt = $pdo->query('SELECT * FROM cms_content ORDER BY content_key ASC');
$contents = $stmt->fetchAll();

$adminPageTitle = 'Content Manager';
$adminActive = 'content'; // Add to NAV_ITEMS later
require __DIR__ . '/includes/admin-layout-start.php';
?>

<div class="admin-panel">
  <div class="admin-panel__header">
    <h2>Content Manager</h2>
    <p style="color:#666; margin-top:5px; font-size:0.9rem;">Map page content and media slots to their values.</p>
  </div>

  <?php if ($error): ?>
  <div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="admin-alert admin-alert--success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="admin-card">
    <div class="admin-card__head">
      <h3>Add / Update Content Slot</h3>
    </div>
    <div class="admin-card__body">
      <form method="post" action="content.php" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="action" value="save_content">
        
        <div class="admin-form__row">
          <label>Content Key <span class="required">*</span></label>
          <input type="text" name="content_key" required class="admin-input" placeholder="e.g. homepage_hero_title or hero_image">
          <small>Must match the key used in the frontend PHP template.</small>
        </div>
        
        <div class="admin-form__row">
          <label>Title (Internal reference)</label>
          <input type="text" name="title" class="admin-input" placeholder="e.g. Homepage Hero Title">
        </div>
        
        <div class="admin-form__row">
          <label>Data Type</label>
          <select name="type" class="admin-input">
            <option value="text">Plain Text</option>
            <option value="richtext">Rich Text (HTML)</option>
            <option value="url">Media ID or URL</option>
          </select>
        </div>
        
        <div class="admin-form__row">
          <label>Content Value <span class="required">*</span></label>
          <textarea name="content_value" required class="admin-input" rows="4" placeholder="Enter the text or Media ID number"></textarea>
        </div>
        
        <div class="admin-form__actions">
          <button type="submit" class="admin-btn admin-btn--primary">Save Content</button>
        </div>
      </form>
    </div>
  </div>

  <div class="admin-card" style="margin-top: 30px;">
    <div class="admin-card__head">
      <h3>Managed Content</h3>
    </div>
    <div class="admin-card__body">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Key</th>
            <th>Title</th>
            <th>Type</th>
            <th>Value Preview</th>
            <th>Status</th>
            <th>Last Updated</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($contents)): ?>
          <tr><td colspan="6" class="admin-empty">No content defined.</td></tr>
          <?php else: ?>
            <?php foreach($contents as $c): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($c['content_key']); ?></strong></td>
              <td><?php echo htmlspecialchars($c['title'] ?? '-'); ?></td>
              <td><span class="admin-badge"><?php echo htmlspecialchars($c['type']); ?></span></td>
              <td>
                <div style="max-width:250px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                  <?php echo htmlspecialchars($c['content_value']); ?>
                </div>
              </td>
              <td><?php echo htmlspecialchars($c['status']); ?></td>
              <td><?php echo date('M j, Y H:i', strtotime($c['updated_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require __DIR__ . '/includes/admin-layout-end.php'; ?>
