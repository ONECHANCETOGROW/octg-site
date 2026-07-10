<?php
require_once __DIR__ . '/includes/admin-auth.php';
$pdo = octg_db();
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    if (!octg_admin_has_role('admin')) die('Only Admin or Super Admin can change optimizer settings.');
    $stmt = $pdo->prepare(
        'INSERT INTO optimizer_settings (id, base_url, score_threshold, auto_fix_enabled, email_reports_enabled, report_frequency)
         VALUES (1, :url, :threshold, :autofix, :email, :freq)
         ON DUPLICATE KEY UPDATE base_url=:url2, score_threshold=:threshold2, auto_fix_enabled=:autofix2, email_reports_enabled=:email2, report_frequency=:freq2'
    );
    $vals = [
        'url' => rtrim(trim($_POST['base_url']), '/'),
        'threshold' => (int) $_POST['score_threshold'],
        'autofix' => isset($_POST['auto_fix_enabled']) ? 1 : 0,
        'email' => isset($_POST['email_reports_enabled']) ? 1 : 0,
        'freq' => $_POST['report_frequency'],
    ];
    $stmt->execute([
        ':url' => $vals['url'], ':threshold' => $vals['threshold'], ':autofix' => $vals['autofix'], ':email' => $vals['email'], ':freq' => $vals['freq'],
        ':url2' => $vals['url'], ':threshold2' => $vals['threshold'], ':autofix2' => $vals['autofix'], ':email2' => $vals['email'], ':freq2' => $vals['freq'],
    ]);
    octg_log_activity('Optimizer settings updated');
    $saved = true;
}

$settings = ['base_url' => 'https://onechancetogrow.com', 'score_threshold' => 80, 'auto_fix_enabled' => 0, 'email_reports_enabled' => 1, 'report_frequency' => 'daily'];
if ($pdo) {
    try {
        $row = $pdo->query('SELECT * FROM optimizer_settings WHERE id = 1')->fetch();
        if ($row) $settings = array_merge($settings, $row);
    } catch (Throwable $e) {}
}

$adminPageTitle = 'Optimizer Settings';
$adminActive = 'optimizer';
include __DIR__ . '/includes/admin-layout-start.php';
?>

<?php if ($saved): ?><div class="admin-success" style="margin-bottom:20px;">Settings saved.</div><?php endif; ?>

<div class="admin-panel">
  <div class="admin-panel__head"><h2>Crawl &amp; Scoring Settings</h2></div>
  <div class="admin-panel__body">
    <form method="POST">
      <div class="admin-field">
        <label>Site Base URL (what the crawler fetches — change if testing on a staging domain)</label>
        <input type="text" name="base_url" value="<?php echo htmlspecialchars($settings['base_url']); ?>">
      </div>
      <div class="admin-field">
        <label>Score Threshold (pages below this trigger "needs attention" flagging)</label>
        <input type="number" name="score_threshold" min="0" max="100" value="<?php echo (int) $settings['score_threshold']; ?>">
      </div>
      <div class="admin-field">
        <label>Report Frequency (for your own reference — the actual schedule is set by your cron job, see below)</label>
        <select name="report_frequency">
          <?php foreach (['daily','weekly','monthly'] as $f): ?>
          <option value="<?php echo $f; ?>" <?php echo $settings['report_frequency'] === $f ? 'selected' : ''; ?>><?php echo ucfirst($f); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="admin-field"><label><input type="checkbox" name="email_reports_enabled" <?php echo $settings['email_reports_enabled'] ? 'checked' : ''; ?>> Email the report after each audit</label></div>
      <div class="admin-field"><label><input type="checkbox" name="auto_fix_enabled" <?php echo $settings['auto_fix_enabled'] ? 'checked' : ''; ?>> Enable safe auto-fixes (sitemap/llms.txt/robots.txt regeneration) to run automatically after each scheduled audit</label></div>
      <button type="submit" class="admin-btn admin-btn-primary" <?php echo !octg_admin_has_role('admin') ? 'disabled' : ''; ?>>Save Settings</button>
    </form>
  </div>
</div>

<div class="admin-panel">
  <div class="admin-panel__head"><h2>Scheduling This Every 24 Hours</h2></div>
  <div class="admin-panel__body">
    <p style="color:var(--graphite); font-size:0.86rem; line-height:1.7;">
      This system cannot register its own cron job — that has to happen on your actual hosting account.
      In Hostinger's hPanel, open <strong>Advanced &rarr; Cron Jobs</strong>, add a new job set to run
      <strong>once every 24 hours</strong>, and point it at:
    </p>
    <p style="font-family:monospace; background:var(--paper-deep); padding:12px 16px; font-size:0.82rem; margin:12px 0;">
      php /home/YOUR_USERNAME/public_html/admin/cron/run-audit.php --scheduled
    </p>
    <p style="color:var(--graphite); font-size:0.86rem;">Hostinger's cron interface fills in your actual username and lets you pick the exact time. Once that's set up, audits run automatically and email you a report — nothing else needs to change.</p>
  </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
