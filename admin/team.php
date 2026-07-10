<?php
/* ==========================================================================
   TEAM.PHP — Admin manager for Team Members.
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
        if ($_POST['action'] === 'add_team_member') {
            $slug = trim($_POST['slug']);
            $full_name = trim($_POST['full_name']);
            $position = trim($_POST['position']);
            $bio = trim($_POST['bio']);
            $display_order = (int)$_POST['display_order'];
            $photo_id = !empty($_POST['photo_id']) ? (int)$_POST['photo_id'] : null;
            
            $stmt = $pdo->prepare('
                INSERT INTO team_members (slug, full_name, position, bio, display_order, photo_id) 
                VALUES (?, ?, ?, ?, ?, ?)
            ');
            
            if ($stmt->execute([$slug, $full_name, $position, $bio, $display_order, $photo_id])) {
                $success = 'Team member added successfully.';
            } else {
                $error = 'Failed to add team member. Does the slug already exist?';
            }
        }
    }
}

// Fetch team members
$stmt = $pdo->query('
    SELECT t.*, m.file_path 
    FROM team_members t 
    LEFT JOIN cms_media m ON t.photo_id = m.id 
    ORDER BY t.display_order ASC
');
$teamMembers = $stmt->fetchAll();

$adminPageTitle = 'Team Management';
$adminActive = 'team';
require __DIR__ . '/includes/admin-layout-start.php';
?>

<div class="admin-panel">
  <div class="admin-panel__header">
    <h2>Team Management</h2>
    <p style="color:#666; margin-top:5px; font-size:0.9rem;">Manage the leadership and core team members.</p>
  </div>

  <?php if ($error): ?>
  <div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="admin-alert admin-alert--success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="admin-card">
    <div class="admin-card__head">
      <h3>Add Team Member</h3>
    </div>
    <div class="admin-card__body">
      <form method="post" action="team.php" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="action" value="add_team_member">
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="admin-form__row">
              <label>Full Name <span class="required">*</span></label>
              <input type="text" name="full_name" required class="admin-input" placeholder="e.g. Jane Doe">
            </div>
            <div class="admin-form__row">
              <label>Slug (Unique ID) <span class="required">*</span></label>
              <input type="text" name="slug" required class="admin-input" placeholder="e.g. jane-doe">
            </div>
        </div>
        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="admin-form__row">
              <label>Position <span class="required">*</span></label>
              <input type="text" name="position" required class="admin-input" placeholder="e.g. Chief Executive Officer">
            </div>
            <div class="admin-form__row">
              <label>Media ID (Photo)</label>
              <input type="number" name="photo_id" class="admin-input" placeholder="Enter Media ID from Library">
            </div>
        </div>
        
        <div class="admin-form__row">
          <label>Bio</label>
          <textarea name="bio" class="admin-input" rows="4" placeholder="Short biography..."></textarea>
        </div>
        
        <div class="admin-form__row">
          <label>Display Order</label>
          <input type="number" name="display_order" class="admin-input" value="0">
        </div>
        
        <div class="admin-form__actions">
          <button type="submit" class="admin-btn admin-btn--primary">Add Team Member</button>
        </div>
      </form>
    </div>
  </div>

  <div class="admin-card" style="margin-top: 30px;">
    <div class="admin-card__head">
      <h3>Current Team</h3>
    </div>
    <div class="admin-card__body">
      <table class="admin-table">
        <thead>
          <tr>
            <th style="width:60px;">Photo</th>
            <th>Name</th>
            <th>Position</th>
            <th>Order</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($teamMembers)): ?>
          <tr><td colspan="4" class="admin-empty">No team members added yet.</td></tr>
          <?php else: ?>
            <?php foreach($teamMembers as $t): ?>
            <tr>
              <td>
                  <?php if ($t['file_path']): ?>
                    <img src="<?php echo htmlspecialchars($t['file_path']); ?>" alt="Photo" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                  <?php else: ?>
                    <div style="width:40px; height:40px; border-radius:50%; background:#e2e1d9;"></div>
                  <?php endif; ?>
              </td>
              <td><strong><?php echo htmlspecialchars($t['full_name']); ?></strong><br><small style="color:#666;"><?php echo htmlspecialchars($t['slug']); ?></small></td>
              <td><?php echo htmlspecialchars($t['position']); ?></td>
              <td><?php echo $t['display_order']; ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require __DIR__ . '/includes/admin-layout-end.php'; ?>
