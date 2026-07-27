<?php
/* ==========================================================================
   PRODUCTS PAGE BUILDER MODULE
   Manages the screenshot/media assigned to each of the 5 OCTG products on
   products.php. The catalog itself (name, description, features, pricing
   copy) stays in data/products-catalog.php per the existing "edit via build
   script, not by hand" convention — this module only wires up the one thing
   that template already reads from the CMS (octg_media('product_{slug}_image',
   ...) in products.php) but never had an admin screen to actually set.
   ========================================================================== */

/* Local copies of home.php's helpers — page-builder.php only `require`s one
   builder module per request (whichever tab is active), so pb_save_text_local()/
   pb_get_text_local() defined inside home.php are NOT available here. Safe to
   redeclare locally since only one of these builder files ever loads at once. */
function pb_save_text_local($key, $val) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT id FROM cms_content WHERE content_key = ?');
    $stmt->execute([$key]);
    if ($stmt->fetchColumn()) {
        $stmt = $pdo->prepare('UPDATE cms_content SET content_value = ?, type = "text" WHERE content_key = ?');
        return $stmt->execute([$val, $key]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO cms_content (content_key, content_value, type, status) VALUES (?, ?, "text", "published")');
        return $stmt->execute([$key, $val]);
    }
}

function pb_get_text_local($key, $default = '') {
    global $pdo;
    $stmt = $pdo->prepare('SELECT content_value FROM cms_content WHERE content_key = ?');
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    return ($val !== false && trim($val) !== '') ? $val : $default;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_products') {
    if (!octg_verify_csrf()) {
        $error = 'Invalid CSRF token.';
    } else {
        $PRODUCTS_FOR_SAVE = require __DIR__ . '/../../../data/products-catalog.php';
        foreach ($PRODUCTS_FOR_SAVE as $p) {
            pb_save_text_local('product_' . $p['slug'] . '_image', trim($_POST['product_' . $p['slug'] . '_image'] ?? ''));
        }
        $success = 'Product images saved successfully!';
    }
}

$PRODUCTS = require __DIR__ . '/../../../data/products-catalog.php';
$stmt = $pdo->query('SELECT id, file_path, title, mime_type FROM cms_media ORDER BY created_at DESC');
$allMedia = $stmt->fetchAll();
?>

<?php if ($error): ?>
<div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
<div class="admin-alert admin-alert--success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="post" action="page-builder.php?page=products" class="pb-form">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
    <input type="hidden" name="action" value="save_products">

    <p style="font-size:0.85rem; color:#666; margin-bottom:20px;">
      Upload each product's screenshot in the Media Library first, then select it here. Any image orientation works —
      the page automatically shows it in full without cropping, whether it's a wide dashboard shot or a taller mobile view.
    </p>

    <?php foreach ($PRODUCTS as $p):
        $imgVal = pb_get_text_local('product_' . $p['slug'] . '_image');
    ?>
    <div class="admin-card" style="margin-bottom:24px;">
        <div class="admin-card__head">
            <h3><?php echo htmlspecialchars($p['name']); ?></h3>
        </div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <label>Screenshot</label>
                <select name="product_<?php echo htmlspecialchars($p['slug']); ?>_image" class="admin-input">
                    <option value="">-- No Image (shows placeholder) --</option>
                    <?php foreach ($allMedia as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php if ($imgVal == $m['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars(basename($m['file_path']) . ' - ' . $m['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div style="position:sticky; bottom:20px; background:var(--white); padding:15px; border:1px solid var(--ink); border-radius:8px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 10px 30px rgba(0,0,0,0.1);">
        <div>
            <strong style="color:var(--ink);">Products Page</strong>
            <span style="color:#666; font-size:0.85rem; margin-left:10px;">Unsaved changes will be lost if you leave.</span>
        </div>
        <button type="submit" class="admin-btn admin-btn--primary">Save Product Images</button>
    </div>
</form>
