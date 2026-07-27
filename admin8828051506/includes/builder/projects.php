<?php
/* ==========================================================================
   PROJECTS BUILDER MODULE
   Handles CRUD and ordering for Projects/Case Studies inside the Page Builder.
   ========================================================================== */
if (!defined('ABSPATH')) {
    // Rely on being included from page-builder.php
}

$error = '';
$success = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$projectId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Services Catalog for options
$servicesCatalog = file_exists(__DIR__ . '/../../../data/services-catalog.php') ? require __DIR__ . '/../../../data/services-catalog.php' : [];

// Fetch Media for dropdowns
$stmt = $pdo->query('SELECT id, file_path, title FROM cms_media ORDER BY created_at DESC');
$allMedia = $stmt->fetchAll();

// Image helper to find file_path by media_id
function pm_get_media_path(PDO $pdo, $mediaId) {
    if (empty($mediaId)) return null;
    $stmt = $pdo->prepare('SELECT file_path FROM cms_media WHERE id = ?');
    $stmt->execute([(int)$mediaId]);
    return $stmt->fetchColumn() ?: null;
}

// Upload handler
function pm_handle_upload(PDO $pdo, string $fieldName, string $label): ?string {
    if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../assets/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '', basename($_FILES[$fieldName]['name']));
        $targetFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $targetFile)) {
            $stmt = $pdo->prepare('INSERT INTO cms_media (file_path, title) VALUES (?, ?)');
            $stmt->execute(['/assets/uploads/' . $fileName, $label]);
            return '/assets/uploads/' . $fileName;
        }
    }
    return null;
}

// Handle Delete
if ($action === 'delete' && $projectId > 0) {
    if (!isset($_GET['csrf']) || $_GET['csrf'] !== ($_SESSION['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $stmt = $pdo->prepare('DELETE FROM cms_projects WHERE id = ?');
        if ($stmt->execute([$projectId])) {
            $success = 'Project deleted successfully.';
            $action = 'list';
        } else {
            $error = 'Failed to delete project.';
        }
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_project'])) {
    if (!octg_verify_csrf()) {
        $error = 'Invalid CSRF token.';
    } else {
        $slug = trim($_POST['slug'] ?? '');
        $client_name = trim($_POST['client_name'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $service_name = trim($_POST['service_name'] ?? '');
        $industry = trim($_POST['industry'] ?? '');
        $short_description = trim($_POST['short_description'] ?? '');
        $challenge = trim($_POST['challenge'] ?? '');
        $solution = trim($_POST['solution'] ?? '');
        $quote = trim($_POST['quote'] ?? '');
        $quote_role = trim($_POST['quote_role'] ?? '');
        $cta_text = trim($_POST['cta_text'] ?? '');
        $cta_link = trim($_POST['cta_link'] ?? '');
        $display_order = (int)($_POST['display_order'] ?? 0);
        $status = $_POST['status'] === 'draft' ? 'draft' : 'published';
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $has_case_study = isset($_POST['has_case_study']) ? 1 : 0;
        $meta_title = trim($_POST['meta_title'] ?? '');
        $meta_description = trim($_POST['meta_description'] ?? '');

        // Validation
        if (empty($slug) || empty($client_name) || empty($title) || empty($short_description)) {
            $error = 'Please fill in Slug, Client Name, Project Title, and Short Description.';
        } else {
            // Process images
            // Featured Image
            $featured_image = pm_handle_upload($pdo, 'featured_image_upload', 'Project Hero: ' . $title);
            if (!$featured_image && !empty($_POST['featured_image_media_id'])) {
                $featured_image = pm_get_media_path($pdo, $_POST['featured_image_media_id']);
            }
            if (!$featured_image) {
                $featured_image = $_POST['existing_featured_image'] ?? '';
            }

            // OG Image
            $og_image = pm_handle_upload($pdo, 'og_image_upload', 'Project SEO: ' . $title);
            if (!$og_image && !empty($_POST['og_image_media_id'])) {
                $og_image = pm_get_media_path($pdo, $_POST['og_image_media_id']);
            }
            if (!$og_image) {
                $og_image = $_POST['existing_og_image'] ?? '';
            }

            // Gallery Images
            $gallery_paths = [];
            // Parse existing gallery paths
            if (isset($_POST['existing_gallery_images']) && is_array($_POST['existing_gallery_images'])) {
                $gallery_paths = $_POST['existing_gallery_images'];
            }
            // Parse chosen media gallery
            if (isset($_POST['gallery_media_ids']) && is_array($_POST['gallery_media_ids'])) {
                foreach ($_POST['gallery_media_ids'] as $mId) {
                    $mPath = pm_get_media_path($pdo, $mId);
                    if ($mPath && !in_array($mPath, $gallery_paths)) {
                        $gallery_paths[] = $mPath;
                    }
                }
            }
            // Parse uploaded gallery files
            if (isset($_FILES['gallery_uploads'])) {
                $files = $_FILES['gallery_uploads'];
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $uploadDir = __DIR__ . '/../../../assets/uploads/';
                        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                        $fileName = time() . '_gal_' . $i . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '', basename($files['name'][$i]));
                        $targetFile = $uploadDir . $fileName;
                        if (move_uploaded_file($files['tmp_name'][$i], $targetFile)) {
                            $stmtM = $pdo->prepare('INSERT INTO cms_media (file_path, title) VALUES (?, ?)');
                            $stmtM->execute(['/assets/uploads/' . $fileName, 'Project Gallery: ' . $title]);
                            $gallery_paths[] = '/assets/uploads/' . $fileName;
                        }
                    }
                }
            }
            $gallery_images = json_encode(array_values(array_filter($gallery_paths)));

            // Results Repeater
            $results_data = [];
            if (isset($_POST['results']) && is_array($_POST['results'])) {
                foreach ($_POST['results'] as $res) {
                    if (!empty($res['metric'])) {
                        $results_data[] = [
                            'metric' => trim($res['metric']),
                            'label' => trim($res['label'] ?? '')
                        ];
                    }
                }
            }
            $results = json_encode($results_data);

            // Timeline Repeater
            $timeline_data = [];
            if (isset($_POST['timeline']) && is_array($_POST['timeline'])) {
                foreach ($_POST['timeline'] as $node) {
                    if (!empty($node['title'])) {
                        $timeline_data[] = [
                            'phase' => trim($node['phase'] ?? ''),
                            'title' => trim($node['title'] ?? ''),
                            'body' => trim($node['body'] ?? '')
                        ];
                    }
                }
            }
            $timeline = json_encode($timeline_data);

            // Services used checkboxes
            $services_used = json_encode(isset($_POST['services_used']) && is_array($_POST['services_used']) ? array_values($_POST['services_used']) : []);

            if ($projectId > 0) {
                // Update
                $stmt = $pdo->prepare("UPDATE cms_projects SET 
                    slug = ?, client_name = ?, title = ?, category = ?, service_name = ?, industry = ?, short_description = ?,
                    challenge = ?, solution = ?, results = ?, timeline = ?, services_used = ?, quote = ?, quote_role = ?,
                    cta_text = ?, cta_link = ?, featured_image = ?, gallery_images = ?, display_order = ?, status = ?,
                    is_featured = ?, meta_title = ?, meta_description = ?, og_image = ?, has_case_study = ?
                    WHERE id = ?");
                $ok = $stmt->execute([
                    $slug, $client_name, $title, $category, $service_name, $industry, $short_description,
                    $challenge, $solution, $results, $timeline, $services_used, $quote, $quote_role,
                    $cta_text, $cta_link, $featured_image, $gallery_images, $display_order, $status,
                    $is_featured, $meta_title, $meta_description, $og_image, $has_case_study, $projectId
                ]);
                if ($ok) {
                    $success = 'Project updated successfully.';
                    $action = 'list';
                } else {
                    $error = 'Failed to update project.';
                }
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO cms_projects (
                    slug, client_name, title, category, service_name, industry, short_description,
                    challenge, solution, results, timeline, services_used, quote, quote_role,
                    cta_text, cta_link, featured_image, gallery_images, display_order, status,
                    is_featured, meta_title, meta_description, og_image, has_case_study
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $ok = $stmt->execute([
                    $slug, $client_name, $title, $category, $service_name, $industry, $short_description,
                    $challenge, $solution, $results, $timeline, $services_used, $quote, $quote_role,
                    $cta_text, $cta_link, $featured_image, $gallery_images, $display_order, $status,
                    $is_featured, $meta_title, $meta_description, $og_image, $has_case_study
                ]);
                if ($ok) {
                    $success = 'Project added successfully.';
                    $action = 'list';
                } else {
                    $error = 'Failed to add project.';
                }
            }
        }
    }
}

// Fetch Current project if editing
$project = null;
if (($action === 'edit' || $action === 'add') && $projectId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM cms_projects WHERE id = ?');
    $stmt->execute([$projectId]);
    $project = $stmt->fetch();
}

// Fetch all projects for listing
$stmt = $pdo->query('SELECT * FROM cms_projects ORDER BY display_order ASC, id DESC');
$projectsList = $stmt->fetchAll();
?>

<?php if ($error): ?>
<div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
<div class="admin-alert admin-alert--success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 1.25rem;">Live Projects & Case Studies</h3>
        <a href="page-builder.php?page=projects&action=add" class="admin-btn admin-btn--primary">+ Add New Project</a>
    </div>

    <div class="admin-card">
        <div class="admin-card__body" style="padding: 0;">
            <table class="admin-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--paper-line); background: #fafafa;">
                        <th style="padding: 15px;">Order</th>
                        <th style="padding: 15px;">Image</th>
                        <th style="padding: 15px;">Project Title</th>
                        <th style="padding: 15px;">Client</th>
                        <th style="padding: 15px;">Category</th>
                        <th style="padding: 15px;">Featured</th>
                        <th style="padding: 15px;">Status</th>
                        <th style="padding: 15px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($projectsList)): ?>
                        <tr>
                            <td colspan="8" style="padding: 30px; text-align: center; color: #999;">No projects found. Add your first project above.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($projectsList as $p): ?>
                            <tr style="border-bottom: 1px solid var(--paper-line); transition: background 0.2s;" onmouseover="this.style.background='#fcfcfc'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 15px; font-weight: 600;"><?php echo htmlspecialchars($p['display_order']); ?></td>
                                <td style="padding: 15px; width: 80px;">
                                    <?php if ($p['featured_image']): ?>
                                        <img src="<?php echo htmlspecialchars($p['featured_image']); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid var(--paper-line);">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; color: #999; border-radius: 4px;">No image</div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 15px;">
                                    <strong style="display: block; color: var(--ink);"><?php echo htmlspecialchars($p['title']); ?></strong>
                                    <span style="font-size: 0.8rem; color: #666;">Slug: <?php echo htmlspecialchars($p['slug']); ?></span>
                                </td>
                                <td style="padding: 15px; color: var(--graphite);"><?php echo htmlspecialchars($p['client_name']); ?></td>
                                <td style="padding: 15px; font-size: 0.85rem; background: #f0f0f0; border-radius: 4px; display: inline-block; margin-top: 15px; font-weight: 500;"><?php echo htmlspecialchars($p['service_name']); ?></td>
                                <td style="padding: 15px;">
                                    <?php echo $p['is_featured'] ? '<span style="color:var(--green); font-weight:600;">Yes</span>' : '<span style="color:#999;">No</span>'; ?>
                                </td>
                                <td style="padding: 15px;">
                                    <?php echo $p['status'] === 'published' ? '<span style="color:#2f7a2f; font-weight:600;">Published</span>' : '<span style="color:#a61c1c; font-weight:600;">Draft</span>'; ?>
                                </td>
                                <td style="padding: 15px; text-align: right; white-space: nowrap;">
                                    <a href="page-builder.php?page=projects&action=edit&id=<?php echo $p['id']; ?>" class="admin-btn admin-btn--small" style="margin-right: 5px;">Edit</a>
                                    <a href="page-builder.php?page=projects&action=delete&id=<?php echo $p['id']; ?>&csrf=<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>" onclick="return confirm('Are you absolutely sure you want to delete this project? This action is permanent.');" class="admin-btn admin-btn--small" style="color: #c62828; border-color: #ffcdd2;">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
    <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 1.25rem;"><?php echo $action === 'edit' ? 'Edit Project' : 'Add New Project'; ?></h3>
        <a href="page-builder.php?page=projects" class="admin-btn">&larr; Back to List</a>
    </div>

    <form method="post" action="page-builder.php?page=projects&action=<?php echo $action; ?>&id=<?php echo $projectId; ?>" enctype="multipart/form-data" class="pb-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="save_project" value="1">
        <input type="hidden" name="existing_featured_image" value="<?php echo htmlspecialchars($project['featured_image'] ?? ''); ?>">
        <input type="hidden" name="existing_og_image" value="<?php echo htmlspecialchars($project['og_image'] ?? ''); ?>">

        <!-- CARD 1: BASIC DETAILS -->
        <div class="admin-card" style="margin-bottom: 30px;">
            <div class="admin-card__head">
                <h3>Core Project Information</h3>
            </div>
            <div class="admin-card__body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="admin-form__row">
                        <label>Client Label/Name *</label>
                        <input type="text" name="client_name" class="admin-input" required value="<?php echo htmlspecialchars($project['client_name'] ?? ''); ?>" placeholder="e.g. Home Services Client">
                    </div>
                    <div class="admin-form__row">
                        <label>Project Title *</label>
                        <input type="text" name="title" class="admin-input" required value="<?php echo htmlspecialchars($project['title'] ?? ''); ?>" placeholder="e.g. A Lead System That Stopped Losing Calls">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="admin-form__row">
                        <label>URL Slug *</label>
                        <input type="text" name="slug" class="admin-input" required value="<?php echo htmlspecialchars($project['slug'] ?? ''); ?>" placeholder="e.g. home-services-lead-system">
                    </div>
                    <div class="admin-form__row">
                        <label>Core Category Slug *</label>
                        <select name="category" class="admin-input" required>
                            <option value="">-- Choose Category --</option>
                            <?php foreach ($servicesCatalog as $s): ?>
                                <option value="<?php echo htmlspecialchars($s['slug']); ?>" <?php if (($project['category'] ?? '') === $s['slug']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($s['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="admin-form__row">
                        <label>Category Label *</label>
                        <input type="text" name="service_name" class="admin-input" required value="<?php echo htmlspecialchars($project['service_name'] ?? ''); ?>" placeholder="e.g. AI Automation">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="admin-form__row">
                        <label>Industry *</label>
                        <input type="text" name="industry" class="admin-input" required value="<?php echo htmlspecialchars($project['industry'] ?? ''); ?>" placeholder="e.g. Contractors & Trades">
                    </div>
                    <div class="admin-form__row">
                        <label>Display Order</label>
                        <input type="number" name="display_order" class="admin-input" value="<?php echo htmlspecialchars($project['display_order'] ?? '0'); ?>">
                    </div>
                </div>

                <div class="admin-form__row">
                    <label>Short Description (Shown on Card / Grid Page) *</label>
                    <textarea name="short_description" class="admin-input" rows="3" required placeholder="Describe the outcome or results of the project..."><?php echo htmlspecialchars($project['short_description'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- CARD 2: MEDIA & GALLERY -->
        <div class="admin-card" style="margin-bottom: 30px;">
            <div class="admin-card__head">
                <h3>Visual assets (Hero & Gallery)</h3>
            </div>
            <div class="admin-card__body">
                <!-- Featured Image -->
                <div class="admin-form__row" style="margin-bottom: 25px;">
                    <label>Featured Card Image (Hero Image)</label>
                    <?php if (!empty($project['featured_image'])): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?php echo htmlspecialchars($project['featured_image']); ?>" style="max-height: 150px; border-radius: 6px; border: 1px solid var(--paper-line);">
                        </div>
                    <?php endif; ?>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <span style="font-size: 0.85rem; display:block; margin-bottom: 5px; font-weight: 500;">Option A: Upload New File</span>
                            <input type="file" name="featured_image_upload" class="admin-input">
                        </div>
                        <div>
                            <span style="font-size: 0.85rem; display:block; margin-bottom: 5px; font-weight: 500;">Option B: Select from Library</span>
                            <select name="featured_image_media_id" class="admin-input">
                                <option value="">-- Choose Existing --</option>
                                <?php foreach ($allMedia as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars(basename($m['file_path']) . ' - ' . $m['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Gallery Images -->
                <div class="admin-form__row">
                    <label>Project Gallery Images</label>
                    <?php 
                    $gallery = [];
                    if (!empty($project['gallery_images'])) {
                        $gallery = json_decode($project['gallery_images'], true) ?: [];
                    }
                    ?>
                    <?php if (!empty($gallery)): ?>
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px;">
                            <?php foreach ($gallery as $gIdx => $gImg): ?>
                                <div style="position: relative; width: 100px; height: 100px; border: 1px solid var(--paper-line); border-radius: 6px; overflow: hidden;">
                                    <img src="<?php echo htmlspecialchars($gImg); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                    <label style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); color: #fff; font-size: 0.65rem; text-align: center; padding: 2px 0; margin: 0; display: block; cursor: pointer;">
                                        <input type="checkbox" name="existing_gallery_images[]" value="<?php echo htmlspecialchars($gImg); ?>" checked style="margin-right: 3px;"> Keep
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <span style="font-size: 0.85rem; display:block; margin-bottom: 5px; font-weight: 500;">Option A: Upload Gallery Files</span>
                            <input type="file" name="gallery_uploads[]" class="admin-input" multiple>
                            <p style="font-size:0.75rem; color:#666; margin-top:5px;">You can select multiple files at once.</p>
                        </div>
                        <div>
                            <span style="font-size: 0.85rem; display:block; margin-bottom: 5px; font-weight: 500;">Option B: Choose Library Images</span>
                            <div style="max-height: 150px; overflow-y: auto; border: 1px solid var(--paper-line); padding: 10px; border-radius: 6px; background: #fff;">
                                <?php foreach ($allMedia as $m): ?>
                                    <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-size: 0.85rem; cursor: pointer;">
                                        <input type="checkbox" name="gallery_media_ids[]" value="<?php echo $m['id']; ?>">
                                        <?php echo htmlspecialchars(basename($m['file_path'])); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 3: CASE STUDY DATA -->
        <div class="admin-card" style="margin-bottom: 30px;">
            <div class="admin-card__head" style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Full Case Study Details</h3>
                <label style="font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="has_case_study" value="1" <?php if (($project['has_case_study'] ?? 1) == 1) echo 'checked'; ?> id="toggle_case_study">
                    Enable Full Case Study Page
                </label>
            </div>
            <div class="admin-card__body" id="case_study_fields_container">
                <div class="admin-form__row">
                    <label>Challenge Description</label>
                    <textarea name="challenge" class="admin-input" rows="4" placeholder="What obstacle did the client face?"><?php echo htmlspecialchars($project['challenge'] ?? ''); ?></textarea>
                </div>
                <div class="admin-form__row">
                    <label>Solution Description</label>
                    <textarea name="solution" class="admin-input" rows="4" placeholder="How did we solve it? What systems did we build?"><?php echo htmlspecialchars($project['solution'] ?? ''); ?></textarea>
                </div>

                <!-- RESULTS / METRICS -->
                <div class="admin-form__row" style="margin-top: 25px; margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:10px; font-weight: 600;">Results & Metrics (Up to 3 cards)</label>
                    <div id="results_container">
                        <?php 
                        $results_list = [];
                        if (!empty($project['results'])) {
                            $results_list = json_decode($project['results'], true) ?: [];
                        }
                        ?>
                        <?php foreach ($results_list as $index => $r): ?>
                            <div class="pb-repeater-row" style="background:#fcfcfc; padding:15px; border:1px solid var(--paper-line); margin-bottom:10px; border-radius:6px; position:relative; display:grid; grid-template-columns:1fr 2fr; gap:15px;">
                                <button type="button" onclick="pbRemoveRepeaterRow(this)" style="position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:#c62828; font-size:1.1rem;">&times;</button>
                                <div>
                                    <label style="font-size:0.75rem;">Metric (e.g. 50% / Higher)</label>
                                    <input type="text" name="results[<?php echo $index; ?>][metric]" class="admin-input" value="<?php echo htmlspecialchars($r['metric']); ?>">
                                </div>
                                <div>
                                    <label style="font-size:0.75rem;">Label Details</label>
                                    <input type="text" name="results[<?php echo $index; ?>][label]" class="admin-input" value="<?php echo htmlspecialchars($r['label']); ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="admin-btn admin-btn--small" onclick="pbAddRepeaterRow('results_container', 'results_template')">+ Add Result Card</button>
                </div>

                <!-- TIMELINE REPEATER -->
                <div class="admin-form__row" style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:10px; font-weight: 600;">Project Implementation Timeline</label>
                    <div id="timeline_container">
                        <?php 
                        $timeline_list = [];
                        if (!empty($project['timeline'])) {
                            $timeline_list = json_decode($project['timeline'], true) ?: [];
                        }
                        ?>
                        <?php foreach ($timeline_list as $index => $node): ?>
                            <div class="pb-repeater-row" style="background:#fcfcfc; padding:15px; border:1px solid var(--paper-line); margin-bottom:10px; border-radius:6px; position:relative;">
                                <button type="button" onclick="pbRemoveRepeaterRow(this)" style="position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:#c62828; font-size:1.1rem;">&times;</button>
                                <div style="display:grid; grid-template-columns:150px 1fr; gap:15px; margin-bottom:10px;">
                                    <div>
                                        <label style="font-size:0.75rem;">Phase/Week</label>
                                        <input type="text" name="timeline[<?php echo $index; ?>][phase]" class="admin-input" value="<?php echo htmlspecialchars($node['phase']); ?>">
                                    </div>
                                    <div>
                                        <label style="font-size:0.75rem;">Stage Title</label>
                                        <input type="text" name="timeline[<?php echo $index; ?>][title]" class="admin-input" value="<?php echo htmlspecialchars($node['title']); ?>">
                                    </div>
                                </div>
                                <div>
                                    <label style="font-size:0.75rem;">Body Description</label>
                                    <textarea name="timeline[<?php echo $index; ?>][body]" class="admin-input" rows="2"><?php echo htmlspecialchars($node['body']); ?></textarea>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="admin-btn admin-btn--small" onclick="pbAddRepeaterRow('timeline_container', 'timeline_template')">+ Add Timeline Step</button>
                </div>

                <!-- SERVICES USED CHECKBOXES -->
                <div class="admin-form__row" style="margin-bottom: 25px;">
                    <label style="display:block; margin-bottom:10px; font-weight: 600;">Services Used / Built For This Project</label>
                    <?php 
                    $selected_services = [];
                    if (!empty($project['services_used'])) {
                        $selected_services = json_decode($project['services_used'], true) ?: [];
                    }
                    ?>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; max-height: 200px; overflow-y: auto; border:1px solid var(--paper-line); padding:10px; border-radius:6px; background:#fff;">
                        <?php foreach ($servicesCatalog as $s): ?>
                            <label style="display:flex; align-items:center; gap:8px; font-size:0.85rem; cursor:pointer;">
                                <input type="checkbox" name="services_used[]" value="<?php echo htmlspecialchars($s['slug']); ?>" <?php if (in_array($s['slug'], $selected_services)) echo 'checked'; ?>>
                                <?php echo htmlspecialchars($s['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- QUOTE -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="admin-form__row">
                        <label>Client Quote / Testimonial</label>
                        <textarea name="quote" class="admin-input" rows="3" placeholder="What the client said about the result..."><?php echo htmlspecialchars($project['quote'] ?? ''); ?></textarea>
                    </div>
                    <div class="admin-form__row">
                        <label>Quote Sign-off / Role</label>
                        <input type="text" name="quote_role" class="admin-input" value="<?php echo htmlspecialchars($project['quote_role'] ?? ''); ?>" placeholder="e.g. Owner, Home Services Client" style="margin-top:0;">
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 4: DISPLAY STATUS, CTA & SEO -->
        <div class="admin-card" style="margin-bottom: 30px;">
            <div class="admin-card__head">
                <h3>Visibility, CTA & SEO Settings</h3>
            </div>
            <div class="admin-card__body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="admin-form__row">
                        <label>Publish Status</label>
                        <select name="status" class="admin-input">
                            <option value="published" <?php if (($project['status'] ?? 'published') === 'published') echo 'selected'; ?>>Published (Live)</option>
                            <option value="draft" <?php if (($project['status'] ?? 'published') === 'draft') echo 'selected'; ?>>Draft (Hidden)</option>
                        </select>
                    </div>
                    <div style="display:flex; gap:30px; align-items:center; margin-top:20px;">
                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-weight:600;">
                            <input type="checkbox" name="is_featured" value="1" <?php if (($project['is_featured'] ?? 0) == 1) echo 'checked'; ?>>
                            Featured Project (Highlight)
                        </label>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="admin-form__row">
                        <label>CTA Button Text (Optional Override)</label>
                        <input type="text" name="cta_text" class="admin-input" value="<?php echo htmlspecialchars($project['cta_text'] ?? ''); ?>" placeholder="e.g. Book a Growth Call">
                    </div>
                    <div class="admin-form__row">
                        <label>CTA Link URL (Optional Override)</label>
                        <input type="text" name="cta_link" class="admin-input" value="<?php echo htmlspecialchars($project['cta_link'] ?? ''); ?>" placeholder="e.g. /book-demo.php">
                    </div>
                </div>

                <h4 style="margin-top:30px; margin-bottom:15px; font-size:1rem; border-bottom: 1px solid var(--paper-line); padding-bottom:5px; color: var(--ink);">SEO Meta Fields</h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div class="admin-form__row">
                        <label>SEO Meta Title</label>
                        <input type="text" name="meta_title" class="admin-input" value="<?php echo htmlspecialchars($project['meta_title'] ?? ''); ?>" placeholder="Defaults to Project Title | One Chance To Grow">
                    </div>
                    <div class="admin-form__row">
                        <label>SEO Meta Description</label>
                        <input type="text" name="meta_description" class="admin-input" value="<?php echo htmlspecialchars($project['meta_description'] ?? ''); ?>" placeholder="Defaults to Short Description">
                    </div>
                </div>

                <div class="admin-form__row">
                    <label>SEO OG Share Image</label>
                    <?php if (!empty($project['og_image'])): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?php echo htmlspecialchars($project['og_image']); ?>" style="max-height: 100px; border-radius: 4px; border: 1px solid var(--paper-line);">
                        </div>
                    <?php endif; ?>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <span style="font-size: 0.85rem; display:block; margin-bottom: 5px; font-weight: 500;">Option A: Upload SEO Image</span>
                            <input type="file" name="og_image_upload" class="admin-input">
                        </div>
                        <div>
                            <span style="font-size: 0.85rem; display:block; margin-bottom: 5px; font-weight: 500;">Option B: Select from Library</span>
                            <select name="og_image_media_id" class="admin-input">
                                <option value="">-- Choose Existing --</option>
                                <?php foreach ($allMedia as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars(basename($m['file_path']) . ' - ' . $m['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STICKY ACTION FOOTER -->
        <div style="position:sticky; bottom:20px; background:var(--white); padding:15px; border:1px solid var(--ink); border-radius:8px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 10px 30px rgba(0,0,0,0.1); z-index:10;">
            <div>
                <strong style="color:var(--ink);">Projects Module</strong>
                <span style="color:#666; font-size:0.85rem; margin-left:10px;">Ensure all required (*) fields are filled.</span>
            </div>
            <button type="submit" class="admin-btn admin-btn--primary">Save Portfolio Project</button>
        </div>
    </form>

    <!-- REPEATER TEMPLATES -->
    <template id="results_template">
        <div class="pb-repeater-row" style="background:#fcfcfc; padding:15px; border:1px solid var(--paper-line); margin-bottom:10px; border-radius:6px; position:relative; display:grid; grid-template-columns:1fr 2fr; gap:15px;">
            <button type="button" onclick="pbRemoveRepeaterRow(this)" style="position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:#c62828; font-size:1.1rem;">&times;</button>
            <div>
                <label style="font-size:0.75rem;">Metric (e.g. 50% / Higher)</label>
                <input type="text" name="results[__INDEX__][metric]" class="admin-input">
            </div>
            <div>
                <label style="font-size:0.75rem;">Label Details</label>
                <input type="text" name="results[__INDEX__][label]" class="admin-input">
            </div>
        </div>
    </template>

    <template id="timeline_template">
        <div class="pb-repeater-row" style="background:#fcfcfc; padding:15px; border:1px solid var(--paper-line); margin-bottom:10px; border-radius:6px; position:relative;">
            <button type="button" onclick="pbRemoveRepeaterRow(this)" style="position:absolute; top:8px; right:8px; background:none; border:none; cursor:pointer; color:#c62828; font-size:1.1rem;">&times;</button>
            <div style="display:grid; grid-template-columns:150px 1fr; gap:15px; margin-bottom:10px;">
                <div>
                    <label style="font-size:0.75rem;">Phase/Week</label>
                    <input type="text" name="timeline[__INDEX__][phase]" class="admin-input">
                </div>
                <div>
                    <label style="font-size:0.75rem;">Stage Title</label>
                    <input type="text" name="timeline[__INDEX__][title]" class="admin-input">
                </div>
            </div>
            <div>
                <label style="font-size:0.75rem;">Body Description</label>
                <textarea name="timeline[__INDEX__][body]" class="admin-input" rows="2"></textarea>
            </div>
        </div>
    </template>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggle = document.getElementById('toggle_case_study');
        const container = document.getElementById('case_study_fields_container');
        
        function updateCSFields() {
            if (toggle.checked) {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
                container.querySelectorAll('input, textarea, select, button').forEach(el => el.removeAttribute('disabled'));
            } else {
                container.style.opacity = '0.4';
                container.style.pointerEvents = 'none';
                container.querySelectorAll('input, textarea, select, button').forEach(el => el.setAttribute('disabled', 'disabled'));
            }
        }
        
        if (toggle && container) {
            toggle.addEventListener('change', updateCSFields);
            updateCSFields();
        }
    });
    </script>
<?php endif; ?>
