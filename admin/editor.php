<?php
/* ==========================================================================
   EDITOR.PHP — Visual Page Editor for managing page-specific content and images.
   ========================================================================== */
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/includes/admin-auth.php';

$pdo = octg_db();
if (!$pdo) {
    die("Database connection failed.");
}

$error = '';
$success = '';

// 1. Define the Page Schema
$PAGE_SCHEMA = [
    'home' => [
        'label' => 'Home',
        'sections' => [
            'hero' => [
                'label' => 'Homepage Hero Section',
                'fields' => [
                    ['key' => 'hero_image', 'label' => 'Hero Background Image', 'type' => 'image', 'hint' => 'Recommended size: 1920x1080 (Landscape)']
                ]
            ],
            'testimonials' => [
                'label' => 'Testimonial Client Photos',
                'fields' => [
                    ['key' => 'testimonial_1_photo', 'label' => 'Testimonial 1 Photo', 'type' => 'image', 'hint' => 'Recommended size: 400x400 (Square)'],
                    ['key' => 'testimonial_2_photo', 'label' => 'Testimonial 2 Photo', 'type' => 'image', 'hint' => 'Recommended size: 400x400 (Square)'],
                    ['key' => 'testimonial_3_photo', 'label' => 'Testimonial 3 Photo', 'type' => 'image', 'hint' => 'Recommended size: 400x400 (Square)'],
                ]
            ]
        ]
    ],
    'about' => [
        'label' => 'About Us',
        'sections' => [
            'intro' => [
                'label' => 'About Introduction',
                'fields' => [
                    ['key' => 'about_story_image', 'label' => 'Our Story Image', 'type' => 'image', 'hint' => 'Recommended size: 800x600 (Landscape)']
                ]
            ],
            'timeline' => [
                'label' => 'Timeline Images',
                'fields' => [
                    ['key' => 'timeline_about_1', 'label' => 'Timeline Phase 1', 'type' => 'image', 'hint' => 'Recommended size: 600x400'],
                    ['key' => 'timeline_about_2', 'label' => 'Timeline Phase 2', 'type' => 'image', 'hint' => 'Recommended size: 600x400'],
                    ['key' => 'timeline_about_3', 'label' => 'Timeline Phase 3', 'type' => 'image', 'hint' => 'Recommended size: 600x400'],
                    ['key' => 'timeline_about_4', 'label' => 'Timeline Phase 4 (Today)', 'type' => 'image', 'hint' => 'Recommended size: 600x400'],
                ]
            ]
        ]
    ]
];

// Handle Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!octg_verify_csrf()) {
        $error = 'Invalid CSRF token.';
    } else {
        if ($_POST['action'] === 'update_slot') {
            $content_key = trim($_POST['content_key']);
            $media_id = null;
            
            // Check if user is uploading a NEW file
            if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                
                $fileName = time() . '_' . basename($_FILES['new_image']['name']);
                $fileName = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $fileName);
                $targetFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES['new_image']['tmp_name'], $targetFile)) {
                    $mediaPath = '/assets/uploads/' . $fileName;
                    // Insert into media library
                    $stmt = $pdo->prepare('INSERT INTO cms_media (file_path, title) VALUES (?, ?)');
                    if ($stmt->execute([$mediaPath, 'Uploaded via Editor: ' . $content_key])) {
                        $media_id = $pdo->lastInsertId();
                    } else {
                        $error = 'Failed to save media to database.';
                    }
                } else {
                    $error = 'Failed to move uploaded file.';
                }
            } 
            // Check if user selected an EXISTING file
            elseif (!empty($_POST['existing_media_id'])) {
                $media_id = (int)$_POST['existing_media_id'];
            }
            
            // Update cms_content with the media ID (as string)
            if ($media_id && !$error) {
                // Check if content slot exists
                $stmt = $pdo->prepare('SELECT id FROM cms_content WHERE content_key = ?');
                $stmt->execute([$content_key]);
                $exists = $stmt->fetchColumn();
                
                if ($exists) {
                    $stmt = $pdo->prepare('UPDATE cms_content SET content_value = ? WHERE content_key = ?');
                    $stmt->execute([(string)$media_id, $content_key]);
                } else {
                    $stmt = $pdo->prepare('INSERT INTO cms_content (content_key, content_value, type, status) VALUES (?, ?, "url", "published")');
                    $stmt->execute([$content_key, (string)$media_id]);
                }
                $success = 'Image slot updated successfully.';
            } elseif (!$media_id && !$error) {
                $error = 'Please upload an image or select one from the library.';
            }
        }
    }
}

// Fetch all media for the dropdown
$stmt = $pdo->query('SELECT id, file_path, title FROM cms_media ORDER BY created_at DESC');
$allMedia = $stmt->fetchAll();

// Determine Active Tab
$activeTab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $PAGE_SCHEMA) ? $_GET['tab'] : 'home';

$adminPageTitle = 'Page Editor';
$adminActive = 'editor'; 
require __DIR__ . '/includes/admin-layout-start.php';
?>

<div class="admin-panel">
  <div class="admin-panel__header">
    <h2>Page Editor</h2>
    <p style="color:#666; margin-top:5px; font-size:0.9rem;">Visually manage content and images across your website pages.</p>
  </div>

  <?php if ($error): ?>
  <div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="admin-alert admin-alert--success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="editor-tabs" style="display:flex; border-bottom:2px solid var(--paper-line); margin-bottom:30px; gap:20px;">
      <?php foreach ($PAGE_SCHEMA as $key => $page): ?>
          <a href="?tab=<?php echo $key; ?>" style="padding:10px 15px; text-decoration:none; font-weight:600; font-size:0.95rem; border-bottom:2px solid <?php echo $activeTab === $key ? 'var(--ink)' : 'transparent'; ?>; color:<?php echo $activeTab === $key ? 'var(--ink)' : 'var(--graphite)'; ?>; transition:all 0.2s ease;">
              <?php echo htmlspecialchars($page['label']); ?>
          </a>
      <?php endforeach; ?>
  </div>

  <div class="editor-content">
      <?php 
      $currentSchema = $PAGE_SCHEMA[$activeTab];
      foreach ($currentSchema['sections'] as $secKey => $section):
      ?>
      <div class="admin-card" style="margin-bottom:30px;">
          <div class="admin-card__head">
              <h3><?php echo htmlspecialchars($section['label']); ?></h3>
          </div>
          <div class="admin-card__body">
              <?php foreach ($section['fields'] as $field): 
                  // Fetch current value
                  $stmt = $pdo->prepare('SELECT content_value FROM cms_content WHERE content_key = ?');
                  $stmt->execute([$field['key']]);
                  $val = $stmt->fetchColumn();
                  
                  $mediaPath = null;
                  if ($val && is_numeric($val)) {
                      $stmt2 = $pdo->prepare('SELECT file_path FROM cms_media WHERE id = ?');
                      $stmt2->execute([$val]);
                      $mediaPath = $stmt2->fetchColumn();
                  }
              ?>
              
              <div style="display:flex; gap:30px; padding:25px; background:var(--paper-deep); border:1px solid var(--paper-line); border-radius:8px; margin-bottom:20px;">
                  
                  <!-- Preview Area -->
                  <div style="width:250px; flex-shrink:0;">
                      <div style="background:#000; border-radius:4px; overflow:hidden; width:100%; aspect-ratio:16/9; display:flex; align-items:center; justify-content:center; margin-bottom:10px;">
                          <?php if ($mediaPath): ?>
                              <img src="<?php echo htmlspecialchars($mediaPath); ?>" style="width:100%; height:100%; object-fit:contain;" alt="Preview">
                          <?php else: ?>
                              <span style="color:#666; font-size:0.8rem;">No Image Assigned</span>
                          <?php endif; ?>
                      </div>
                      <span style="display:block; font-size:0.75rem; color:#666; text-align:center;">
                          Current: <?php echo $mediaPath ? basename($mediaPath) : 'None'; ?>
                      </span>
                  </div>
                  
                  <!-- Form Area -->
                  <div style="flex-grow:1;">
                      <h4 style="margin:0 0 5px 0; font-size:1.05rem; color:var(--ink);"><?php echo htmlspecialchars($field['label']); ?></h4>
                      <?php if(isset($field['hint'])): ?>
                          <p style="margin:0 0 20px 0; font-size:0.8rem; color:#f57f20; font-weight:600;"><?php echo htmlspecialchars($field['hint']); ?></p>
                      <?php endif; ?>
                      
                      <form method="post" action="editor.php?tab=<?php echo $activeTab; ?>" enctype="multipart/form-data" style="background:var(--white); padding:20px; border:1px solid var(--paper-line); border-radius:6px;">
                          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                          <input type="hidden" name="action" value="update_slot">
                          <input type="hidden" name="content_key" value="<?php echo htmlspecialchars($field['key']); ?>">
                          
                          <div class="admin-form__row">
                              <label>Select from Media Library:</label>
                              <select name="existing_media_id" class="admin-input">
                                  <option value="">-- Choose Existing Image --</option>
                                  <?php foreach($allMedia as $m): ?>
                                      <option value="<?php echo $m['id']; ?>" <?php if($val == $m['id']) echo 'selected'; ?>>
                                          <?php echo htmlspecialchars(basename($m['file_path']) . ' - ' . $m['title']); ?>
                                      </option>
                                  <?php endforeach; ?>
                              </select>
                          </div>
                          
                          <div style="text-align:center; margin:15px 0; color:#999; font-size:0.85rem; font-weight:600; text-transform:uppercase;">
                              - OR -
                          </div>
                          
                          <div class="admin-form__row">
                              <label>Upload New File:</label>
                              <input type="file" name="new_image" class="admin-input" accept="image/*">
                          </div>
                          
                          <div style="margin-top:20px;">
                              <button type="submit" class="admin-btn admin-btn--primary">Update Image Slot</button>
                          </div>
                      </form>
                  </div>
              </div>
              
              <?php endforeach; ?>
          </div>
      </div>
      <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-layout-end.php'; ?>
