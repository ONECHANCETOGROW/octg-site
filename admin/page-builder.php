<?php
/* ==========================================================================
   PAGE-BUILDER.PHP — Component-driven builder mirroring frontend logic.
   ========================================================================== */
require_once __DIR__ . '/includes/admin-auth.php';

$pdo = octg_db();
if (!$pdo) {
    die("Database connection failed.");
}

// Ensure the builder includes directory exists
$builderDir = __DIR__ . '/includes/builder';
if (!is_dir($builderDir)) {
    mkdir($builderDir, 0755, true);
}

// Supported pages
$PAGES = [
    'home' => 'Home',
    'about' => 'About Us',
    'services' => 'Services',
    'projects' => 'Projects',
    'contact' => 'Contact'
];

$activePage = isset($_GET['page']) && array_key_exists($_GET['page'], $PAGES) ? $_GET['page'] : 'home';
$pageFile = $builderDir . '/' . $activePage . '.php';

// Helper function to get JSON content
function pb_get_json($key, $default = []) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT content_value FROM cms_content WHERE content_key = ? AND type = "json"');
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    if ($val) {
        $decoded = json_decode($val, true);
        return is_array($decoded) ? $decoded : $default;
    }
    return $default;
}

// Helper function to save JSON content
function pb_save_json($key, $data) {
    global $pdo;
    $json = json_encode($data);
    $stmt = $pdo->prepare('SELECT id FROM cms_content WHERE content_key = ?');
    $stmt->execute([$key]);
    if ($stmt->fetchColumn()) {
        $stmt = $pdo->prepare('UPDATE cms_content SET content_value = ?, type = "json" WHERE content_key = ?');
        return $stmt->execute([$json, $key]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO cms_content (content_key, content_value, type, status) VALUES (?, ?, "json", "published")');
        return $stmt->execute([$key, $json]);
    }
}

// Include specific page logic if POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (file_exists($pageFile)) {
        require_once $pageFile;
    }
}

$adminPageTitle = 'Page Builder';
$adminActive = 'page-builder'; 
require __DIR__ . '/includes/admin-layout-start.php';
?>

<div class="admin-panel">
  <div class="admin-panel__header">
    <h2>Page Builder</h2>
    <p style="color:#666; margin-top:5px; font-size:0.9rem;">Manage actual page components, layouts, and data.</p>
  </div>

  <div class="editor-tabs" style="display:flex; border-bottom:2px solid var(--paper-line); margin-bottom:30px; gap:20px;">
      <?php foreach ($PAGES as $key => $label): ?>
          <a href="?page=<?php echo $key; ?>" style="padding:10px 15px; text-decoration:none; font-weight:600; font-size:0.95rem; border-bottom:2px solid <?php echo $activePage === $key ? 'var(--ink)' : 'transparent'; ?>; color:<?php echo $activePage === $key ? 'var(--ink)' : 'var(--graphite)'; ?>; transition:all 0.2s ease;">
              <?php echo htmlspecialchars($label); ?>
          </a>
      <?php endforeach; ?>
  </div>

  <div class="editor-content">
      <?php 
      if (file_exists($pageFile)) {
          // The included file handles its own UI rendering
          require $pageFile;
      } else {
          echo '<div class="admin-empty">The builder module for <strong>' . htmlspecialchars($PAGES[$activePage]) . '</strong> is currently under construction.</div>';
      }
      ?>
  </div>
</div>

<script>
// Generic helper for adding repeater rows in the UI
function pbAddRepeaterRow(containerId, templateId) {
    const container = document.getElementById(containerId);
    const template = document.getElementById(templateId).content.cloneNode(true);
    // Replace __INDEX__ with current timestamp to ensure unique array keys
    const tempHtml = document.createElement('div');
    tempHtml.appendChild(template);
    tempHtml.innerHTML = tempHtml.innerHTML.replace(/__INDEX__/g, Date.now());
    container.appendChild(tempHtml.firstElementChild);
}
function pbRemoveRepeaterRow(btn) {
    if(confirm('Remove this item?')) {
        btn.closest('.pb-repeater-row').remove();
    }
}
</script>

<?php require __DIR__ . '/includes/admin-layout-end.php'; ?>
