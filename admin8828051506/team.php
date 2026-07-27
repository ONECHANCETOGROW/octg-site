<?php
/* ==========================================================================
   TEAM.PHP — Admin manager for the About Hero: globe/rotation settings plus
   full CRUD for team members (photo, bio, socials, order, active state).
   ========================================================================== */
require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/../api/_lib.php';

$pdo = octg_db();
if (!$pdo) {
    die("Database connection failed.");
}

$SOCIAL_PLATFORMS = ['linkedin' => 'LinkedIn', 'facebook' => 'Facebook', 'instagram' => 'Instagram', 'x' => 'X / Twitter', 'website' => 'Website'];

$error = '';
$success = '';

function team_handle_photo_upload(PDO $pdo, string $label): ?int {
    if (isset($_FILES['photo_upload']) && $_FILES['photo_upload']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '', basename($_FILES['photo_upload']['name']));
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['photo_upload']['tmp_name'], $targetFile)) {
            $stmt = $pdo->prepare('INSERT INTO cms_media (file_path, title) VALUES (?, ?)');
            $stmt->execute(['/assets/uploads/' . $fileName, 'Team photo: ' . $label]);
            return (int)$pdo->lastInsertId();
        }
    }
    if (!empty($_POST['existing_photo_id'])) {
        return (int)$_POST['existing_photo_id'];
    }
    return null;
}

function team_save_json_content(PDO $pdo, string $key, array $value): void {
    $json = json_encode($value);
    $stmt = $pdo->prepare('SELECT id FROM cms_content WHERE content_key = ?');
    $stmt->execute([$key]);
    if ($stmt->fetchColumn()) {
        $pdo->prepare('UPDATE cms_content SET content_value = ?, type = "json" WHERE content_key = ?')->execute([$json, $key]);
    } else {
        $pdo->prepare('INSERT INTO cms_content (content_key, content_value, type, status) VALUES (?, ?, "json", "published")')->execute([$key, $json]);
    }
}

function team_save_socials(PDO $pdo, int $teamId, array $platforms): void {
    $pdo->prepare('DELETE FROM team_socials WHERE team_id = ?')->execute([$teamId]);
    $stmt = $pdo->prepare('INSERT INTO team_socials (team_id, platform, url, display_order) VALUES (?, ?, ?, ?)');
    $order = 0;
    foreach ($platforms as $platform => $label) {
        $url = trim($_POST['social_' . $platform] ?? '');
        if ($url === '') continue;
        if ($platform === 'website' && !preg_match('~^https?://~i', $url)) $url = 'https://' . $url;
        $stmt->execute([$teamId, $platform, $url, $order++]);
    }
}

// Handle Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!octg_verify_csrf()) {
        $error = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'];

        if ($action === 'save_hero_settings') {
            $settings = [
                'eyebrow' => trim($_POST['eyebrow'] ?? 'Leadership'),
                'heading' => trim($_POST['heading'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'rotation_speed' => max(1000, (int)($_POST['rotation_speed'] ?? 5000)),
                'auto_rotate' => isset($_POST['auto_rotate']) ? 1 : 0,
                'particle_density' => in_array($_POST['particle_density'] ?? 'medium', ['low', 'medium', 'high'], true) ? $_POST['particle_density'] : 'medium',
                'transition_time' => max(200, (int)($_POST['transition_time'] ?? 800)),
                'animation_speed' => max(0.1, (float)($_POST['animation_speed'] ?? 1)),
                'glow_intensity' => max(0, min(1, (float)($_POST['glow_intensity'] ?? 0.55))),
            ];
            team_save_json_content($pdo, 'about_hero_settings', $settings);
            $success = 'Hero & globe settings saved.';
        }

        elseif ($action === 'save_founder_hero') {
            $photoId = team_handle_photo_upload($pdo, trim($_POST['name'] ?? 'Founder'));
            $existingFounder = octg_get_json('about_founder_hero', []);
            $founderData = [
                'label' => trim($_POST['label'] ?? 'Founder & CEO'),
                'name' => trim($_POST['name'] ?? ''),
                'title' => trim($_POST['title'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'photo_id' => $photoId !== null ? $photoId : ($existingFounder['photo_id'] ?? null),
                'linkedin' => trim($_POST['social_linkedin'] ?? ''),
                'instagram' => trim($_POST['social_instagram'] ?? ''),
                'facebook' => trim($_POST['social_facebook'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
            ];
            team_save_json_content($pdo, 'about_founder_hero', $founderData);
            $success = 'Founder hero saved.';
        }

        elseif ($action === 'save_team_stats') {
            $statsData = [
                'members_value' => trim($_POST['members_value'] ?? '15+'),
                'members_label' => trim($_POST['members_label'] ?? 'Team Members'),
                'projects_value' => trim($_POST['projects_value'] ?? '100+'),
                'projects_label' => trim($_POST['projects_label'] ?? 'Projects Delivered'),
                'industries_value' => trim($_POST['industries_value'] ?? '8+'),
                'industries_label' => trim($_POST['industries_label'] ?? 'Industries Served'),
                'years_value' => trim($_POST['years_value'] ?? '5+'),
                'years_label' => trim($_POST['years_label'] ?? 'Years of Experience'),
            ];
            team_save_json_content($pdo, 'about_team_stats', $statsData);
            $success = 'Team stats saved.';
        }

        elseif ($action === 'add_team_member') {
            $slug = trim($_POST['slug']);
            $full_name = trim($_POST['full_name']);
            $position = trim($_POST['position']);
            $bio = trim($_POST['bio']);
            $email = trim($_POST['email'] ?? '');
            $display_order = (int)$_POST['display_order'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $photo_id = team_handle_photo_upload($pdo, $full_name);

            $stmt = $pdo->prepare('
                INSERT INTO team_members (slug, full_name, position, bio, email, display_order, is_active, photo_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            if ($stmt->execute([$slug, $full_name, $position, $bio, $email ?: null, $display_order, $is_active, $photo_id])) {
                team_save_socials($pdo, (int)$pdo->lastInsertId(), $SOCIAL_PLATFORMS);
                $success = 'Team member added successfully.';
            } else {
                $error = 'Failed to add team member. Does the slug already exist?';
            }
        }

        elseif ($action === 'update_team_member') {
            $id = (int)$_POST['id'];
            $slug = trim($_POST['slug']);
            $full_name = trim($_POST['full_name']);
            $position = trim($_POST['position']);
            $bio = trim($_POST['bio']);
            $email = trim($_POST['email'] ?? '');
            $display_order = (int)$_POST['display_order'];
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $photo_id = team_handle_photo_upload($pdo, $full_name);

            if ($photo_id !== null) {
                $stmt = $pdo->prepare('UPDATE team_members SET slug=?, full_name=?, position=?, bio=?, email=?, display_order=?, is_active=?, photo_id=? WHERE id=?');
                $ok = $stmt->execute([$slug, $full_name, $position, $bio, $email ?: null, $display_order, $is_active, $photo_id, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE team_members SET slug=?, full_name=?, position=?, bio=?, email=?, display_order=?, is_active=? WHERE id=?');
                $ok = $stmt->execute([$slug, $full_name, $position, $bio, $email ?: null, $display_order, $is_active, $id]);
            }
            if ($ok) {
                team_save_socials($pdo, $id, $SOCIAL_PLATFORMS);
                $success = 'Team member updated successfully.';
            } else {
                $error = 'Failed to update team member.';
            }
        }

        elseif ($action === 'delete_team_member') {
            $id = (int)$_POST['id'];
            $pdo->prepare('DELETE FROM team_members WHERE id = ?')->execute([$id]);
            $success = 'Team member deleted.';
        }

        elseif ($action === 'toggle_active') {
            $id = (int)$_POST['id'];
            $pdo->prepare('UPDATE team_members SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
            $success = 'Status updated.';
        }
    }
}

// Fetch founder hero
$founderHero = octg_get_json('about_founder_hero', [
    'label' => 'Founder & CEO',
    'name' => 'Paul Raja',
    'title' => 'Founder & Chief Executive Officer',
    'description' => 'Building technology, automation and digital marketing systems that help businesses grow smarter, scale faster and compete with confidence.',
    'photo_id' => null,
    'linkedin' => '',
    'instagram' => '',
    'facebook' => '',
    'email' => '',
]);
$founderPhotoPath = null;
if (!empty($founderHero['photo_id'])) {
    $stmt = $pdo->prepare('SELECT file_path FROM cms_media WHERE id = ?');
    $stmt->execute([$founderHero['photo_id']]);
    $founderPhotoPath = $stmt->fetchColumn() ?: null;
}

// Fetch team stats
$teamStats = octg_get_json('about_team_stats', [
    'members_value' => '15+', 'members_label' => 'Team Members',
    'projects_value' => '100+', 'projects_label' => 'Projects Delivered',
    'industries_value' => '8+', 'industries_label' => 'Industries Served',
    'years_value' => '5+', 'years_label' => 'Years of Experience',
]);

// Fetch hero settings
$hero = octg_get_json('about_hero_settings', [
    'eyebrow' => 'Leadership',
    'heading' => 'The Team Building Your Growth System',
    'description' => 'One team across marketing, software, and automation, accountable for how the whole system performs, not just their own piece of it.',
    'rotation_speed' => 5000,
    'auto_rotate' => 1,
    'particle_density' => 'medium',
    'transition_time' => 800,
    'animation_speed' => 1,
    'glow_intensity' => 0.55,
]);

// Fetch team members + their socials
$stmt = $pdo->query('
    SELECT t.*, m.file_path
    FROM team_members t
    LEFT JOIN cms_media m ON t.photo_id = m.id
    ORDER BY t.display_order ASC
');
$teamMembers = $stmt->fetchAll();

$socialStmt = $pdo->prepare('SELECT platform, url FROM team_socials WHERE team_id = ?');
foreach ($teamMembers as &$tm) {
    $socialStmt->execute([$tm['id']]);
    $tm['socials'] = [];
    foreach ($socialStmt->fetchAll() as $s) { $tm['socials'][$s['platform']] = $s['url']; }
}
unset($tm);

$stmt = $pdo->query('SELECT id, file_path, title FROM cms_media ORDER BY created_at DESC');
$allMedia = $stmt->fetchAll();

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editMember = null;
if ($editId) {
    foreach ($teamMembers as $tm) { if ((int)$tm['id'] === $editId) { $editMember = $tm; break; } }
}

$adminPageTitle = 'Team Management';
$adminActive = 'team';
require __DIR__ . '/includes/admin-layout-start.php';
?>

<div class="admin-panel">
  <div class="admin-panel__header">
    <h2>Team Management</h2>
    <p style="color:#666; margin-top:5px; font-size:0.9rem;">Manage the About page hero: the rotating globe, its settings, and the leadership team it showcases.</p>
  </div>

  <?php if ($error): ?>
  <div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="admin-alert admin-alert--success"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>

  <div class="admin-card">
    <div class="admin-card__head">
      <h3>Founder Hero</h3>
    </div>
    <div class="admin-card__body">
      <p style="color:#666; margin:-6px 0 18px; font-size:0.85rem;">The first thing visitors see on the About page — everything here is live on the site immediately after saving.</p>
      <form method="post" action="team.php" class="admin-form" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="action" value="save_founder_hero">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
          <div class="admin-form__row">
            <label>Label</label>
            <input type="text" name="label" class="admin-input" value="<?php echo htmlspecialchars($founderHero['label']); ?>">
          </div>
          <div class="admin-form__row">
            <label>Name</label>
            <input type="text" name="name" class="admin-input" value="<?php echo htmlspecialchars($founderHero['name']); ?>">
          </div>
        </div>
        <div class="admin-form__row">
          <label>Title</label>
          <input type="text" name="title" class="admin-input" value="<?php echo htmlspecialchars($founderHero['title']); ?>">
        </div>
        <div class="admin-form__row">
          <label>Description</label>
          <textarea name="description" class="admin-input" rows="3"><?php echo htmlspecialchars($founderHero['description']); ?></textarea>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
          <div class="admin-form__row">
            <label>Photo — Upload New</label>
            <input type="file" name="photo_upload" class="admin-input" accept="image/*">
          </div>
          <div class="admin-form__row">
            <label>Photo — Or Select Existing</label>
            <select name="existing_photo_id" class="admin-input">
              <option value="">-- Keep Current --</option>
              <?php foreach ($allMedia as $m): ?>
              <option value="<?php echo $m['id']; ?>" <?php echo (!empty($founderHero['photo_id']) && (int)$founderHero['photo_id'] === (int)$m['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars(basename($m['file_path']) . ' — ' . $m['title']); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <?php if ($founderPhotoPath): ?>
        <div style="margin:-10px 0 16px;">
          <img src="<?php echo htmlspecialchars($founderPhotoPath); ?>" alt="Current founder photo" style="width:70px; height:70px; border-radius:8px; object-fit:cover; border:1px solid var(--paper-line);">
        </div>
        <?php else: ?>
        <p style="margin:-10px 0 16px; font-size:0.8rem; color:#999;">No photo chosen yet — the site falls back to /assets/img/founder-hero.jpg.</p>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:20px;">
          <div class="admin-form__row">
            <label>LinkedIn</label>
            <input type="text" name="social_linkedin" class="admin-input" placeholder="https://…" value="<?php echo htmlspecialchars($founderHero['linkedin']); ?>">
          </div>
          <div class="admin-form__row">
            <label>Instagram</label>
            <input type="text" name="social_instagram" class="admin-input" placeholder="https://…" value="<?php echo htmlspecialchars($founderHero['instagram']); ?>">
          </div>
          <div class="admin-form__row">
            <label>Facebook</label>
            <input type="text" name="social_facebook" class="admin-input" placeholder="https://…" value="<?php echo htmlspecialchars($founderHero['facebook']); ?>">
          </div>
          <div class="admin-form__row">
            <label>Email (shown as an icon)</label>
            <input type="email" name="email" class="admin-input" placeholder="paul@onechancetogrow.in" value="<?php echo htmlspecialchars($founderHero['email']); ?>">
          </div>
        </div>

        <div class="admin-form__actions">
          <button type="submit" class="admin-btn admin-btn--primary">Save Founder Hero</button>
        </div>
      </form>
    </div>
  </div>

  <div class="admin-card">
    <div class="admin-card__head">
      <h3>Hero & Globe Settings</h3>
    </div>
    <div class="admin-card__body">
      <form method="post" action="team.php" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="action" value="save_hero_settings">

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
          <div class="admin-form__row">
            <label>Eyebrow</label>
            <input type="text" name="eyebrow" class="admin-input" value="<?php echo htmlspecialchars($hero['eyebrow']); ?>">
          </div>
          <div class="admin-form__row">
            <label>Heading</label>
            <input type="text" name="heading" class="admin-input" value="<?php echo htmlspecialchars($hero['heading']); ?>">
          </div>
        </div>
        <div class="admin-form__row">
          <label>Description</label>
          <textarea name="description" class="admin-input" rows="2"><?php echo htmlspecialchars($hero['description']); ?></textarea>
        </div>

        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-top:10px;">
          <div class="admin-form__row">
            <label>Rotation Speed (ms per member)</label>
            <input type="number" name="rotation_speed" class="admin-input" min="1000" step="500" value="<?php echo (int)$hero['rotation_speed']; ?>">
          </div>
          <div class="admin-form__row">
            <label>Transition Time (ms)</label>
            <input type="number" name="transition_time" class="admin-input" min="200" step="100" value="<?php echo (int)$hero['transition_time']; ?>">
          </div>
          <div class="admin-form__row">
            <label>Globe Rotation Animation Speed (×)</label>
            <input type="number" name="animation_speed" class="admin-input" min="0.1" step="0.1" value="<?php echo htmlspecialchars((string)$hero['animation_speed']); ?>">
          </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px;">
          <div class="admin-form__row">
            <label>Particle Intensity</label>
            <select name="particle_density" class="admin-input">
              <option value="low" <?php echo $hero['particle_density'] === 'low' ? 'selected' : ''; ?>>Low</option>
              <option value="medium" <?php echo $hero['particle_density'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
              <option value="high" <?php echo $hero['particle_density'] === 'high' ? 'selected' : ''; ?>>High</option>
            </select>
          </div>
          <div class="admin-form__row">
            <label>Glow Intensity (0 – 1)</label>
            <input type="number" name="glow_intensity" class="admin-input" min="0" max="1" step="0.05" value="<?php echo htmlspecialchars((string)$hero['glow_intensity']); ?>">
          </div>
          <div class="admin-form__row">
            <label style="display:flex; align-items:center; gap:8px; margin-top:26px;">
              <input type="checkbox" name="auto_rotate" <?php echo empty($hero['auto_rotate']) ? '' : 'checked'; ?>> Auto-Rotate Team Members
            </label>
          </div>
        </div>

        <div class="admin-form__actions">
          <button type="submit" class="admin-btn admin-btn--primary">Save Hero Settings</button>
        </div>
      </form>
    </div>
  </div>

  <div class="admin-card" style="margin-top: 30px;">
    <div class="admin-card__head">
      <h3>Team Stats Bar</h3>
    </div>
    <div class="admin-card__body">
      <p style="color:#666; margin:-6px 0 18px; font-size:0.85rem;">The four stat tiles below the team carousel. Edit the number and label for each — no code changes needed.</p>
      <form method="post" action="team.php" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="action" value="save_team_stats">

        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px;">
          <div class="admin-form__row">
            <label>Team Members — Value</label>
            <input type="text" name="members_value" class="admin-input" value="<?php echo htmlspecialchars($teamStats['members_value']); ?>">
          </div>
          <div class="admin-form__row">
            <label>Projects Delivered — Value</label>
            <input type="text" name="projects_value" class="admin-input" value="<?php echo htmlspecialchars($teamStats['projects_value']); ?>">
          </div>
          <div class="admin-form__row">
            <label>Industries Served — Value</label>
            <input type="text" name="industries_value" class="admin-input" value="<?php echo htmlspecialchars($teamStats['industries_value']); ?>">
          </div>
          <div class="admin-form__row">
            <label>Years of Experience — Value</label>
            <input type="text" name="years_value" class="admin-input" value="<?php echo htmlspecialchars($teamStats['years_value']); ?>">
          </div>
        </div>
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-top:10px;">
          <div class="admin-form__row">
            <label>Team Members — Label</label>
            <input type="text" name="members_label" class="admin-input" value="<?php echo htmlspecialchars($teamStats['members_label']); ?>">
          </div>
          <div class="admin-form__row">
            <label>Projects Delivered — Label</label>
            <input type="text" name="projects_label" class="admin-input" value="<?php echo htmlspecialchars($teamStats['projects_label']); ?>">
          </div>
          <div class="admin-form__row">
            <label>Industries Served — Label</label>
            <input type="text" name="industries_label" class="admin-input" value="<?php echo htmlspecialchars($teamStats['industries_label']); ?>">
          </div>
          <div class="admin-form__row">
            <label>Years of Experience — Label</label>
            <input type="text" name="years_label" class="admin-input" value="<?php echo htmlspecialchars($teamStats['years_label']); ?>">
          </div>
        </div>

        <div class="admin-form__actions">
          <button type="submit" class="admin-btn admin-btn--primary">Save Team Stats</button>
        </div>
      </form>
    </div>
  </div>

  <div class="admin-card" style="margin-top: 30px;">
    <div class="admin-card__head">
      <h3><?php echo $editMember ? 'Edit Team Member' : 'Add Team Member'; ?></h3>
    </div>
    <div class="admin-card__body">
      <form method="post" action="team.php" class="admin-form" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="action" value="<?php echo $editMember ? 'update_team_member' : 'add_team_member'; ?>">
        <?php if ($editMember): ?><input type="hidden" name="id" value="<?php echo (int)$editMember['id']; ?>"><?php endif; ?>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="admin-form__row">
              <label>Full Name <span class="required">*</span></label>
              <input type="text" name="full_name" required class="admin-input" placeholder="e.g. Jane Doe" value="<?php echo htmlspecialchars($editMember['full_name'] ?? ''); ?>">
            </div>
            <div class="admin-form__row">
              <label>Slug (Unique ID) <span class="required">*</span></label>
              <input type="text" name="slug" required class="admin-input" placeholder="e.g. jane-doe" value="<?php echo htmlspecialchars($editMember['slug'] ?? ''); ?>">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
            <div class="admin-form__row">
              <label>Job Title <span class="required">*</span></label>
              <input type="text" name="position" required class="admin-input" placeholder="e.g. Chief Executive Officer" value="<?php echo htmlspecialchars($editMember['position'] ?? ''); ?>">
            </div>
            <div class="admin-form__row">
              <label>Email (optional, shown as an icon)</label>
              <input type="email" name="email" class="admin-input" placeholder="jane@onechancetogrow.in" value="<?php echo htmlspecialchars($editMember['email'] ?? ''); ?>">
            </div>
        </div>

        <div class="admin-form__row">
          <label>Short Description</label>
          <textarea name="bio" class="admin-input" rows="3" placeholder="One or two sentences."><?php echo htmlspecialchars($editMember['bio'] ?? ''); ?></textarea>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
          <div class="admin-form__row">
            <label>Photo — Upload New</label>
            <input type="file" name="photo_upload" class="admin-input" accept="image/*">
          </div>
          <div class="admin-form__row">
            <label>Photo — Or Select Existing</label>
            <select name="existing_photo_id" class="admin-input">
              <option value="">-- Keep Current / None --</option>
              <?php foreach ($allMedia as $m): ?>
              <option value="<?php echo $m['id']; ?>" <?php echo (!empty($editMember['photo_id']) && (int)$editMember['photo_id'] === (int)$m['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars(basename($m['file_path']) . ' — ' . $m['title']); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <?php if (!empty($editMember['file_path'])): ?>
        <div style="margin:-10px 0 16px;">
          <img src="<?php echo htmlspecialchars($editMember['file_path']); ?>" alt="Current photo" style="width:60px; height:60px; border-radius:50%; object-fit:cover; border:1px solid var(--paper-line);">
        </div>
        <?php endif; ?>

        <div class="admin-card__head" style="padding:0; margin:20px 0 12px; border:none;">
          <h4 style="font-size:0.95rem; color:var(--ink);">Social Links (leave blank to hide the icon)</h4>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
          <?php foreach ($SOCIAL_PLATFORMS as $platform => $label): ?>
          <div class="admin-form__row">
            <label><?php echo htmlspecialchars($label); ?></label>
            <input type="text" name="social_<?php echo $platform; ?>" class="admin-input" placeholder="https://…" value="<?php echo htmlspecialchars($editMember['socials'][$platform] ?? ''); ?>">
          </div>
          <?php endforeach; ?>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:10px;">
          <div class="admin-form__row">
            <label>Display Order</label>
            <input type="number" name="display_order" class="admin-input" value="<?php echo htmlspecialchars((string)($editMember['display_order'] ?? 0)); ?>">
          </div>
          <div class="admin-form__row">
            <label style="display:flex; align-items:center; gap:8px; margin-top:26px;">
              <input type="checkbox" name="is_active" <?php echo (!$editMember || !empty($editMember['is_active'])) ? 'checked' : ''; ?>> Active (shown in the hero rotation)
            </label>
          </div>
        </div>

        <div class="admin-form__actions">
          <button type="submit" class="admin-btn admin-btn--primary"><?php echo $editMember ? 'Save Changes' : 'Add Team Member'; ?></button>
          <?php if ($editMember): ?><a href="team.php" class="admin-btn">Cancel</a><?php endif; ?>
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
            <th>Status</th>
            <th style="width:220px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($teamMembers)): ?>
          <tr><td colspan="6" class="admin-empty">No team members added yet. The hero will show the globe only until you add one.</td></tr>
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
              <td><?php echo $t['is_active'] ? '<span style="color:#2f7a2f;">Active</span>' : '<span style="color:#999;">Inactive</span>'; ?></td>
              <td style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="team.php?edit=<?php echo (int)$t['id']; ?>" class="admin-btn" style="padding:6px 12px; font-size:0.82rem;">Edit</a>
                <form method="post" action="team.php" onsubmit="return confirm('Deactivate this member instead of the site showing them? This just toggles visibility.');" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                  <input type="hidden" name="action" value="toggle_active">
                  <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
                  <button type="submit" class="admin-btn" style="padding:6px 12px; font-size:0.82rem;"><?php echo $t['is_active'] ? 'Deactivate' : 'Activate'; ?></button>
                </form>
                <form method="post" action="team.php" onsubmit="return confirm('Permanently delete this team member? This cannot be undone.');" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                  <input type="hidden" name="action" value="delete_team_member">
                  <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
                  <button type="submit" class="admin-btn" style="padding:6px 12px; font-size:0.82rem; color:#b33;">Delete</button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require __DIR__ . '/includes/admin-layout-end.php'; ?>
