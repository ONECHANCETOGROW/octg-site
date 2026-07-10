<?php
/* ==========================================================================
   MEDIA.PHP — Admin manager for the CMS Media Library.
   ========================================================================== */
require_once __DIR__ . '/includes/admin-auth.php';

$pdo = octg_db();
if (!$pdo) {
    die("Database connection failed.");
}

$error = '';
$success = '';

// Handle Image Uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_upload'])) {
    if (!octg_verify_csrf()) {
        $error = 'Invalid CSRF token.';
    } else {
        $file = $_FILES['media_upload'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'svg', 'webp', 'gif'];
            if (in_array($ext, $allowed)) {
                $uploadDir = __DIR__ . '/../assets/img/cms/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename = uniqid('img_') . '.' . $ext;
                $targetPath = $uploadDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $webPath = '/assets/img/cms/' . $filename;
                    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
                    $alt_text = isset($_POST['alt_text']) ? trim($_POST['alt_text']) : '';
                    $category = isset($_POST['category']) ? trim($_POST['category']) : '';
                    
                    // Note: In a full app we'd get dimensions using getimagesize()
                    $stmt = $pdo->prepare('INSERT INTO cms_media (file_path, title, alt_text, category, mime_type, file_size) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([
                        $webPath,
                        $title,
                        $alt_text,
                        $category,
                        $file['type'],
                        $file['size']
                    ]);
                    $success = 'Image uploaded successfully!';
                } else {
                    $error = 'Failed to move uploaded file.';
                }
            } else {
                $error = 'Invalid file type. Only JPG, PNG, SVG, WEBP, and GIF are allowed.';
            }
        } else {
            $error = 'Upload error code: ' . $file['error'];
        }
    }
}

// Fetch all media
$stmt = $pdo->query('SELECT * FROM cms_media ORDER BY created_at DESC');
$mediaItems = $stmt->fetchAll();

$adminPageTitle = 'Media Library';
$adminActive = 'media';
require __DIR__ . '/includes/admin-layout-start.php';
?>

<div class="admin-panel">
  <div class="admin-panel__header">
    <h2>Media Library</h2>
  </div>

  <?php if ($error): ?>
  <div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="admin-alert admin-alert--success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="admin-card">
    <div class="admin-card__head">
      <h3>Upload New Media</h3>
    </div>
    <div class="admin-card__body">
      <form method="post" action="media.php" enctype="multipart/form-data" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        
        <div class="admin-form__row">
          <label>File to Upload <span class="required">*</span></label>
          <input type="file" name="media_upload" required accept="image/*">
        </div>
        
        <div class="admin-form__row">
          <label>Title</label>
          <input type="text" name="title" class="admin-input" placeholder="e.g. Hero Background">
        </div>
        
        <div class="admin-form__row">
          <label>Alt Text (for SEO and Accessibility)</label>
          <input type="text" name="alt_text" class="admin-input" placeholder="Describe the image">
        </div>
        
        <div class="admin-form__row">
          <label>Category</label>
          <select name="category" class="admin-input">
            <option value="">(None)</option>
            <option value="hero">Hero Images</option>
            <option value="team">Team Photos</option>
            <option value="blog">Blog Imagery</option>
            <option value="icons">Icons & Assets</option>
          </select>
        </div>
        
        <div class="admin-form__actions">
          <button type="submit" class="admin-btn admin-btn--primary">Upload Image</button>
        </div>
      </form>
    </div>
  </div>

  <div class="admin-card" style="margin-top: 30px;">
    <div class="admin-card__head">
      <h3>Uploaded Media</h3>
    </div>
    <div class="admin-card__body">
      <?php if (empty($mediaItems)): ?>
        <p class="admin-empty">No media uploaded yet.</p>
      <?php else: ?>
        <div class="media-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:20px;">
          <?php foreach ($mediaItems as $item): ?>
          <div class="media-item" style="border:1px solid #E2E1D9; border-radius:4px; padding:10px; background:#fff;">
            <div style="height:140px; background:#F7F6F1; display:flex; align-items:center; justify-content:center; margin-bottom:10px; overflow:hidden; border-radius:3px;">
                <img src="<?php echo htmlspecialchars($item['file_path']); ?>" alt="<?php echo htmlspecialchars($item['alt_text'] ?? ''); ?>" style="max-width:100%; max-height:100%; object-fit:contain;">
            </div>
            <div style="font-size:0.85rem; font-weight:600; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;" title="<?php echo htmlspecialchars($item['title'] ?: basename($item['file_path'])); ?>">
                <?php echo htmlspecialchars($item['title'] ?: basename($item['file_path'])); ?>
            </div>
            <div style="font-size:0.75rem; color:#6b6b6b; margin-top:4px;">ID: <?php echo $item['id']; ?> &middot; <?php echo htmlspecialchars($item['category'] ?? 'Uncategorized'); ?></div>
            <div style="font-size:0.7rem; color:#999; margin-top:4px;"><?php echo date('M j, Y', strtotime($item['created_at'])); ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php require __DIR__ . '/includes/admin-layout-end.php'; ?>
