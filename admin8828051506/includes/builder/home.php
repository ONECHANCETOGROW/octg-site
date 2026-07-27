<?php
/* ==========================================================================
   HOME PAGE BUILDER MODULE
   Handles the logic and UI for building the Home page sections.
   ========================================================================== */
if (!defined('ABSPATH')) { 
    // Safety check
}

$error = '';
$success = '';

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

// Process Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_home') {
    if (!octg_verify_csrf()) {
        $error = 'Invalid CSRF token.';
    } else {
        // Save Hero Video
        pb_save_text_local('hero_video', trim($_POST['hero_video'] ?? ''));

        // Save Testimonials
        for ($i=1; $i<=3; $i++) {
            pb_save_text_local("testimonial_{$i}_quote", trim($_POST["testimonial_{$i}_quote"] ?? ''));
            pb_save_text_local("testimonial_{$i}_name", trim($_POST["testimonial_{$i}_name"] ?? ''));
            pb_save_text_local("testimonial_{$i}_role", trim($_POST["testimonial_{$i}_role"] ?? ''));
            pb_save_text_local("testimonial_{$i}_photo", trim($_POST["testimonial_{$i}_photo"] ?? ''));
        }

        // Save Trust Bar Logos
        $logos = array_filter($_POST['client_logos'] ?? []);
        pb_save_json('client_logos', array_values($logos));
        
        $success = "Home Page components saved successfully!";
    }
}

// Fetch Current Data
$hero_video = pb_get_text_local('hero_video');
$client_logos = pb_get_json('client_logos', []);
if (empty($client_logos)) {
    for ($i = 1; $i <= 5; $i++) {
        $old = pb_get_text_local('client_logo_' . $i);
        if ($old) $client_logos[] = $old;
    }
}
if (empty($client_logos)) {
    $client_logos = [''];
}

// Fetch Media for dropdowns
$stmt = $pdo->query('SELECT id, file_path, title, mime_type FROM cms_media ORDER BY created_at DESC');
$allMedia = $stmt->fetchAll();
?>

<?php if ($error): ?>
<div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
<div class="admin-alert admin-alert--success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="post" action="page-builder.php?page=home" class="pb-form">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
    <input type="hidden" name="action" value="save_home">

    <!-- SECTION: HERO -->
    <div class="admin-card" style="margin-bottom:30px;">
        <div class="admin-card__head">
            <h3>Hero Section</h3>
        </div>
        <div class="admin-card__body">
            <div class="admin-form__row">
                <label>Hero Background Video (Select from Media)</label>
                <select name="hero_video" class="admin-input">
                    <option value="">-- No Video (Fallback to text) --</option>
                    <?php foreach($allMedia as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php if($hero_video == $m['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars(basename($m['file_path']) . ' - ' . $m['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p style="font-size:0.8rem; color:#666; margin-top:5px;">Upload a .mp4 or .webm file in the Media Library first, then select it here.</p>
            </div>
        </div>
    </div>

    <!-- SECTION: TESTIMONIALS -->
    <div class="admin-card" style="margin-bottom:30px;">
        <div class="admin-card__head">
            <h3>Testimonials (Homepage)</h3>
        </div>
        <div class="admin-card__body">
            <p style="font-size:0.85rem; color:#666; margin-bottom:20px;">Edit the text and photos for the 3 homepage quote cards here.</p>
            
            <?php 
            $defaults = [
                1 => ['q' => 'One Chance To Grow rebuilt our lead process end to end — we stopped losing calls and started closing them.', 'n' => 'Client Name', 'r' => 'Owner, Home Services Co.'],
                2 => ['q' => 'The first agency we\'ve used that actually understood our CRM better than we did.', 'n' => 'Client Name', 'r' => 'Founder, Retail Brand'],
                3 => ['q' => 'Our AI receptionist alone paid for the whole engagement in the first month.', 'n' => 'Client Name', 'r' => 'Managing Partner, Professional Services'],
            ];
            for ($i=1; $i<=3; $i++): 
                $photo_val = pb_get_text_local("testimonial_{$i}_photo");
            ?>
            <div style="background:#f9f9f9; padding:20px; border:1px solid #ddd; border-radius:6px; margin-bottom:15px;">
                <h4 style="margin:0 0 15px 0; font-size:1.05rem; color:var(--ink);">Testimonial <?php echo $i; ?></h4>
                <div class="admin-form__row">
                    <label>Quote Text</label>
                    <textarea name="testimonial_<?php echo $i; ?>_quote" class="admin-input" rows="3" placeholder="Enter the review..."><?php echo htmlspecialchars(pb_get_text_local("testimonial_{$i}_quote", $defaults[$i]['q'])); ?></textarea>
                </div>
                <div style="display:flex; gap:15px; margin-bottom: 15px;">
                    <div class="admin-form__row" style="flex:1; margin-bottom:0;">
                        <label>Client Name</label>
                        <input type="text" name="testimonial_<?php echo $i; ?>_name" class="admin-input" value="<?php echo htmlspecialchars(pb_get_text_local("testimonial_{$i}_name", $defaults[$i]['n'])); ?>">
                    </div>
                    <div class="admin-form__row" style="flex:1; margin-bottom:0;">
                        <label>Client Role / Company</label>
                        <input type="text" name="testimonial_<?php echo $i; ?>_role" class="admin-input" value="<?php echo htmlspecialchars(pb_get_text_local("testimonial_{$i}_role", $defaults[$i]['r'])); ?>">
                    </div>
                </div>
                <div class="admin-form__row">
                    <label>Client Photo</label>
                    <select name="testimonial_<?php echo $i; ?>_photo" class="admin-input">
                        <option value="">-- No Photo --</option>
                        <?php foreach($allMedia as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php if($photo_val == $m['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars(basename($m['file_path']) . ' - ' . $m['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>

    <!-- SECTION: TRUST BAR -->
    <div class="admin-card" style="margin-bottom:30px;">
        <div class="admin-card__head">
            <h3>Trust Bar Logos</h3>
        </div>
        <div class="admin-card__body">
            <p style="font-size:0.85rem; color:#666; margin-bottom:15px;">These logos will automatically scroll in a continuous marquee slider on the homepage. Add as many as you need.</p>
            <div id="trust-bar-repeater" style="display:grid; grid-template-columns:1fr; gap:15px; margin-bottom:15px;">
                <?php foreach ($client_logos as $index => $logoId): ?>
                <div class="pb-repeater-row" style="background:#f9f9f9; padding:15px; border:1px solid #ddd; border-radius:6px; position:relative;">
                    <button type="button" onclick="pbRemoveRepeaterRow(this)" style="position:absolute; top:15px; right:15px; background:none; border:none; color:var(--red); cursor:pointer; font-size:0.85rem;">Remove</button>
                    <label>Client Logo</label>
                    <select name="client_logos[]" class="admin-input" style="width:calc(100% - 70px);">
                        <option value="">-- Choose Logo --</option>
                        <?php foreach($allMedia as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php if($logoId == $m['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars(basename($m['file_path']) . ' - ' . $m['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="admin-btn admin-btn--outline" onclick="pbAddRepeaterRow('trust-bar-repeater', 'tmpl-trust-bar')">+ Add Another Logo</button>
        </div>
    </div>

    <template id="tmpl-trust-bar">
        <div class="pb-repeater-row" style="background:#f9f9f9; padding:15px; border:1px solid #ddd; border-radius:6px; position:relative;">
            <button type="button" onclick="pbRemoveRepeaterRow(this)" style="position:absolute; top:15px; right:15px; background:none; border:none; color:var(--red); cursor:pointer; font-size:0.85rem;">Remove</button>
            <label>Client Logo</label>
            <select name="client_logos[]" class="admin-input" style="width:calc(100% - 70px);">
                <option value="">-- Choose Logo --</option>
                <?php foreach($allMedia as $m): ?>
                    <option value="<?php echo $m['id']; ?>">
                        <?php echo htmlspecialchars(basename($m['file_path']) . ' - ' . $m['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </template>

    <div style="position:sticky; bottom:20px; background:var(--white); padding:15px; border:1px solid var(--ink); border-radius:8px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 10px 30px rgba(0,0,0,0.1);">
        <div>
            <strong style="color:var(--ink);">Home Page</strong>
            <span style="color:#666; font-size:0.85rem; margin-left:10px;">Unsaved changes will be lost if you leave.</span>
        </div>
        <button type="submit" class="admin-btn admin-btn--primary">Save Home Page Components</button>
    </div>
</form>
