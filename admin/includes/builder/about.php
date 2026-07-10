<?php
/* ==========================================================================
   ABOUT PAGE BUILDER MODULE
   Handles the logic and UI for building the About Us page sections.
   ========================================================================== */
if (!defined('ABSPATH')) { 
    // Just a safety check if we ever define ABSPATH. For now, rely on being included from page-builder.php
}

$error = '';
$success = '';

// Process Form Submissions for the About Page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!octg_verify_csrf()) {
        $error = 'Invalid CSRF token.';
    } else {
        if ($_POST['action'] === 'save_about') {
            
            // 1. Save Hero & Carousel Settings
            $hero_settings = [
                'eyebrow' => trim($_POST['hero_eyebrow'] ?? ''),
                'heading' => trim($_POST['hero_heading'] ?? ''),
                'description' => trim($_POST['hero_description'] ?? ''),
                'rotation_speed' => (int)($_POST['rotation_speed'] ?? 5000),
                'auto_rotate' => isset($_POST['auto_rotate']) ? 1 : 0,
                'particle_density' => trim($_POST['particle_density'] ?? 'medium')
            ];
            pb_save_json('about_hero_settings', $hero_settings);
            
            // 2. Save Story Section
            $story_settings = [
                'eyebrow' => trim($_POST['story_eyebrow'] ?? ''),
                'heading' => trim($_POST['story_heading'] ?? ''),
                'paragraph_1' => trim($_POST['story_p1'] ?? ''),
                'paragraph_2' => trim($_POST['story_p2'] ?? ''),
                'image_id' => trim($_POST['story_image_id'] ?? '') // Handled separately or as ID
            ];
            pb_save_json('about_story_settings', $story_settings);
            
            // 3. Save Timeline Nodes
            $timeline_data = [];
            if (isset($_POST['timeline']) && is_array($_POST['timeline'])) {
                foreach ($_POST['timeline'] as $node) {
                    if (!empty($node['title'])) {
                        $timeline_data[] = [
                            'phase' => trim($node['phase'] ?? ''),
                            'title' => trim($node['title'] ?? ''),
                            'body' => trim($node['body'] ?? ''),
                            'image_id' => trim($node['image_id'] ?? '')
                        ];
                    }
                }
            }
            pb_save_json('about_timeline_data', $timeline_data);
            
            // 4. Save Values
            $values_data = [];
            if (isset($_POST['values']) && is_array($_POST['values'])) {
                foreach ($_POST['values'] as $val) {
                    if (!empty($val['title'])) {
                        $values_data[] = [
                            'title' => trim($val['title'] ?? ''),
                            'icon_svg' => trim($val['icon_svg'] ?? '')
                        ];
                    }
                }
            }
            pb_save_json('about_values_data', $values_data);
            
            $success = "About Page components saved successfully!";
        }
    }
}

// Fetch Current Data
$hero = pb_get_json('about_hero_settings', [
    'eyebrow' => 'Leadership',
    'heading' => 'The Team Building Your Growth System',
    'description' => 'One team across marketing, software, and automation, accountable for how the whole system performs, not just their own piece of it.',
    'rotation_speed' => 5000,
    'auto_rotate' => 1,
    'particle_density' => 'medium'
]);

$story = pb_get_json('about_story_settings', [
    'eyebrow' => 'Why We Exist',
    'heading' => '100 business problems. One brand. One solution.',
    'paragraph_1' => "Most businesses don't have a marketing problem, or a software problem, or an automation problem in isolation, they have a growth problem, and it's usually spread across all three. We built One Chance To Grow because that shouldn't take five separate companies to fix.",
    'paragraph_2' => "We're registered in Wyoming and work with businesses across the United States and Canada, from single-location local businesses to growing, multi-location companies. Different industries, different sizes, one consistent approach: understand the business first, then build the system around it.",
    'image_id' => ''
]);

$timeline = pb_get_json('about_timeline_data', []);
$values = pb_get_json('about_values_data', []);

// Fetch Team Members for the sub-panel
$stmt = $pdo->query('SELECT id, full_name, position, display_order FROM team_members ORDER BY display_order ASC');
$team_members = $stmt->fetchAll();

// Fetch Media for dropdowns
$stmt = $pdo->query('SELECT id, file_path, title FROM cms_media ORDER BY created_at DESC');
$allMedia = $stmt->fetchAll();
?>

<?php if ($error): ?>
<div class="admin-alert admin-alert--error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if ($success): ?>
<div class="admin-alert admin-alert--success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form method="post" action="page-builder.php?page=about" class="pb-form">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
    <input type="hidden" name="action" value="save_about">

    <!-- SECTION: HERO & LEADERSHIP -->
    <div class="admin-card" style="margin-bottom:30px;">
        <div class="admin-card__head" style="display:flex; justify-content:space-between; align-items:center;">
            <h3>Hero & Leadership Carousel</h3>
            <span class="admin-badge admin-badge--info">Header Section</span>
        </div>
        <div class="admin-card__body">
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="admin-form__row">
                    <label>Eyebrow Text</label>
                    <input type="text" name="hero_eyebrow" class="admin-input" value="<?php echo htmlspecialchars($hero['eyebrow'] ?? ''); ?>">
                </div>
                <div class="admin-form__row">
                    <label>Main Heading</label>
                    <input type="text" name="hero_heading" class="admin-input" value="<?php echo htmlspecialchars($hero['heading'] ?? ''); ?>">
                </div>
            </div>
            <div class="admin-form__row">
                <label>Hero Description</label>
                <textarea name="hero_description" class="admin-input" rows="2"><?php echo htmlspecialchars($hero['description'] ?? ''); ?></textarea>
            </div>
            
            <hr style="border:0; border-top:1px solid var(--paper-line); margin:25px 0;">
            <h4>Carousel Settings</h4>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px;">
                <div class="admin-form__row">
                    <label>Rotation Speed (ms)</label>
                    <input type="number" name="rotation_speed" class="admin-input" value="<?php echo (int)($hero['rotation_speed'] ?? 5000); ?>">
                </div>
                <div class="admin-form__row" style="display:flex; align-items:center; gap:10px; flex-direction:row; margin-top:28px;">
                    <input type="checkbox" name="auto_rotate" id="auto_rotate" value="1" <?php if(!empty($hero['auto_rotate'])) echo 'checked'; ?>>
                    <label for="auto_rotate" style="margin:0;">Auto Rotate</label>
                </div>
                <div class="admin-form__row">
                    <label>3D Particle Density</label>
                    <select name="particle_density" class="admin-input">
                        <option value="low" <?php if(($hero['particle_density']??'')==='low') echo 'selected'; ?>>Low</option>
                        <option value="medium" <?php if(($hero['particle_density']??'medium')==='medium') echo 'selected'; ?>>Medium (Default)</option>
                        <option value="high" <?php if(($hero['particle_density']??'')==='high') echo 'selected'; ?>>High</option>
                    </select>
                </div>
            </div>
            
            <hr style="border:0; border-top:1px solid var(--paper-line); margin:25px 0;">
            <h4>Team Management <a href="team.php" style="font-size:0.8rem; font-weight:normal; float:right;">Full Team Manager &rarr;</a></h4>
            <table class="admin-table" style="margin-top:10px;">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($team_members)): ?>
                    <tr><td colspan="4" class="admin-empty">No team members added yet.</td></tr>
                    <?php else: ?>
                        <?php foreach($team_members as $m): ?>
                        <tr>
                            <td><?php echo $m['display_order']; ?></td>
                            <td><strong><?php echo htmlspecialchars($m['full_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($m['position']); ?></td>
                            <td><a href="team.php?edit=<?php echo $m['id']; ?>" class="admin-btn admin-btn-small">Edit</a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>

    <!-- SECTION: OUR STORY -->
    <div class="admin-card" style="margin-bottom:30px;">
        <div class="admin-card__head">
            <h3>Mission / Our Story</h3>
        </div>
        <div class="admin-card__body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
                <div class="admin-form__row">
                    <label>Eyebrow Text</label>
                    <input type="text" name="story_eyebrow" class="admin-input" value="<?php echo htmlspecialchars($story['eyebrow'] ?? ''); ?>">
                </div>
                <div class="admin-form__row">
                    <label>Heading</label>
                    <input type="text" name="story_heading" class="admin-input" value="<?php echo htmlspecialchars($story['heading'] ?? ''); ?>">
                </div>
            </div>
            <div class="admin-form__row">
                <label>Paragraph 1</label>
                <textarea name="story_p1" class="admin-input" rows="4"><?php echo htmlspecialchars($story['paragraph_1'] ?? ''); ?></textarea>
            </div>
            <div class="admin-form__row">
                <label>Paragraph 2</label>
                <textarea name="story_p2" class="admin-input" rows="4"><?php echo htmlspecialchars($story['paragraph_2'] ?? ''); ?></textarea>
            </div>
            <div class="admin-form__row">
                <label>Story Image (Select from Media)</label>
                <select name="story_image_id" class="admin-input">
                    <option value="">-- Choose Existing Image --</option>
                    <?php foreach($allMedia as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php if(($story['image_id']??'') == $m['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars(basename($m['file_path']) . ' - ' . $m['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- SECTION: TIMELINE -->
    <div class="admin-card" style="margin-bottom:30px;">
        <div class="admin-card__head">
            <h3>Timeline (How We Got Here)</h3>
        </div>
        <div class="admin-card__body">
            <div id="timeline_container">
                <?php foreach($timeline as $index => $node): ?>
                <div class="pb-repeater-row" style="background:#f9f9f9; padding:15px; border:1px solid #ddd; margin-bottom:15px; border-radius:6px; position:relative;">
                    <button type="button" onclick="pbRemoveRepeaterRow(this)" style="position:absolute; top:10px; right:10px; background:#ffebee; color:#c62828; border:1px solid #ffcdd2; border-radius:4px; padding:4px 8px; cursor:pointer;">&times; Remove</button>
                    
                    <div style="display:grid; grid-template-columns:100px 1fr; gap:15px; margin-bottom:10px;">
                        <div class="admin-form__row">
                            <label>Year/Phase</label>
                            <input type="text" name="timeline[<?php echo $index; ?>][phase]" class="admin-input" value="<?php echo htmlspecialchars($node['phase'] ?? ''); ?>">
                        </div>
                        <div class="admin-form__row">
                            <label>Title</label>
                            <input type="text" name="timeline[<?php echo $index; ?>][title]" class="admin-input" value="<?php echo htmlspecialchars($node['title'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="admin-form__row">
                        <label>Body Text</label>
                        <textarea name="timeline[<?php echo $index; ?>][body]" class="admin-input" rows="2"><?php echo htmlspecialchars($node['body'] ?? ''); ?></textarea>
                    </div>
                    <div class="admin-form__row">
                        <label>Illustration (Media ID)</label>
                        <select name="timeline[<?php echo $index; ?>][image_id]" class="admin-input">
                            <option value="">-- Choose Existing Image --</option>
                            <?php foreach($allMedia as $m): ?>
                                <option value="<?php echo $m['id']; ?>" <?php if(($node['image_id']??'') == $m['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars(basename($m['file_path'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="admin-btn" onclick="pbAddRepeaterRow('timeline_container', 'timeline_template')">+ Add Timeline Node</button>
        </div>
    </div>

    <!-- SECTION: VALUES -->
    <div class="admin-card" style="margin-bottom:30px;">
        <div class="admin-card__head">
            <h3>Values (What We Believe)</h3>
        </div>
        <div class="admin-card__body">
            <div id="values_container" style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                <?php foreach($values as $index => $val): ?>
                <div class="pb-repeater-row" style="background:#f9f9f9; padding:15px; border:1px solid #ddd; border-radius:6px; position:relative;">
                    <button type="button" onclick="pbRemoveRepeaterRow(this)" style="position:absolute; top:10px; right:10px; background:none; border:none; cursor:pointer; color:#c62828;">&times;</button>
                    <div class="admin-form__row">
                        <label>Title</label>
                        <input type="text" name="values[<?php echo $index; ?>][title]" class="admin-input" value="<?php echo htmlspecialchars($val['title'] ?? ''); ?>">
                    </div>
                    <div class="admin-form__row">
                        <label>SVG Icon Code</label>
                        <textarea name="values[<?php echo $index; ?>][icon_svg]" class="admin-input" rows="3" style="font-family:monospace; font-size:0.8rem;"><?php echo htmlspecialchars($val['icon_svg'] ?? ''); ?></textarea>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div style="margin-top:20px;">
                <button type="button" class="admin-btn" onclick="pbAddRepeaterRow('values_container', 'value_template')">+ Add Value Item</button>
            </div>
        </div>
    </div>
    
    <div style="position:sticky; bottom:20px; background:var(--white); padding:15px; border:1px solid var(--ink); border-radius:8px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 10px 30px rgba(0,0,0,0.1);">
        <div>
            <strong style="color:var(--ink);">About Page</strong>
            <span style="color:#666; font-size:0.85rem; margin-left:10px;">Unsaved changes will be lost if you leave.</span>
        </div>
        <button type="submit" class="admin-btn admin-btn--primary">Save About Page Components</button>
    </div>
</form>

<!-- TEMPLATES FOR REPEATERS -->
<template id="timeline_template">
    <div class="pb-repeater-row" style="background:#f9f9f9; padding:15px; border:1px solid #ddd; margin-bottom:15px; border-radius:6px; position:relative;">
        <button type="button" onclick="pbRemoveRepeaterRow(this)" style="position:absolute; top:10px; right:10px; background:#ffebee; color:#c62828; border:1px solid #ffcdd2; border-radius:4px; padding:4px 8px; cursor:pointer;">&times; Remove</button>
        <div style="display:grid; grid-template-columns:100px 1fr; gap:15px; margin-bottom:10px;">
            <div class="admin-form__row">
                <label>Year/Phase</label>
                <input type="text" name="timeline[__INDEX__][phase]" class="admin-input">
            </div>
            <div class="admin-form__row">
                <label>Title</label>
                <input type="text" name="timeline[__INDEX__][title]" class="admin-input">
            </div>
        </div>
        <div class="admin-form__row">
            <label>Body Text</label>
            <textarea name="timeline[__INDEX__][body]" class="admin-input" rows="2"></textarea>
        </div>
        <div class="admin-form__row">
            <label>Illustration (Media ID)</label>
            <select name="timeline[__INDEX__][image_id]" class="admin-input">
                <option value="">-- Choose Existing Image --</option>
                <?php foreach($allMedia as $m): ?>
                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars(basename($m['file_path'])); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</template>

<template id="value_template">
    <div class="pb-repeater-row" style="background:#f9f9f9; padding:15px; border:1px solid #ddd; border-radius:6px; position:relative;">
        <button type="button" onclick="pbRemoveRepeaterRow(this)" style="position:absolute; top:10px; right:10px; background:none; border:none; cursor:pointer; color:#c62828;">&times;</button>
        <div class="admin-form__row">
            <label>Title</label>
            <input type="text" name="values[__INDEX__][title]" class="admin-input">
        </div>
        <div class="admin-form__row">
            <label>SVG Icon Code</label>
            <textarea name="values[__INDEX__][icon_svg]" class="admin-input" rows="3" style="font-family:monospace; font-size:0.8rem;"></textarea>
        </div>
    </div>
</template>
