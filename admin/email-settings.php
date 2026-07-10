<?php
require_once __DIR__ . '/includes/admin-auth.php';
$pdo = octg_db();
$saved = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    if (!octg_admin_has_role('admin')) {
        die('Only Admin or Super Admin can change email settings.');
    }
    $emails = array_filter(array_map('trim', explode(',', $_POST['notification_emails'] ?? '')));
    $stmt = $pdo->prepare(
        'INSERT INTO email_settings (id, notifications_enabled, notification_emails, sender_name, sender_email, reply_to_email, company_name, email_footer, email_logo_url)
         VALUES (1, :enabled, :emails, :sname, :semail, :reply, :cname, :footer, :logo)
         ON DUPLICATE KEY UPDATE notifications_enabled=:enabled2, notification_emails=:emails2, sender_name=:sname2, sender_email=:semail2, reply_to_email=:reply2, company_name=:cname2, email_footer=:footer2, email_logo_url=:logo2'
    );
    $vals = [
        'enabled' => isset($_POST['notifications_enabled']) ? 1 : 0,
        'emails' => json_encode(array_values($emails)),
        'sname' => trim($_POST['sender_name']), 'semail' => trim($_POST['sender_email']),
        'reply' => trim($_POST['reply_to_email']), 'cname' => trim($_POST['company_name']),
        'footer' => trim($_POST['email_footer']), 'logo' => trim($_POST['email_logo_url']) ?: null,
    ];
    $stmt->execute([
        ':enabled' => $vals['enabled'], ':emails' => $vals['emails'], ':sname' => $vals['sname'], ':semail' => $vals['semail'],
        ':reply' => $vals['reply'], ':cname' => $vals['cname'], ':footer' => $vals['footer'], ':logo' => $vals['logo'],
        ':enabled2' => $vals['enabled'], ':emails2' => $vals['emails'], ':sname2' => $vals['sname'], ':semail2' => $vals['semail'],
        ':reply2' => $vals['reply'], ':cname2' => $vals['cname'], ':footer2' => $vals['footer'], ':logo2' => $vals['logo'],
    ]);
    octg_log_activity('Email settings updated');
    $saved = true;
}

require_once __DIR__ . '/../includes/notification-service.php';
$settings = octg_email_settings();

$adminPageTitle = 'Email Settings';
$adminActive = 'email-settings';
include __DIR__ . '/includes/admin-layout-start.php';
?>

<?php if ($saved): ?><div class="admin-success" style="margin-bottom:20px;">Settings saved.</div><?php endif; ?>
<?php if (!$pdo): ?>
<div class="admin-panel" style="border-color:#8a6d1a;"><div class="admin-panel__body">Database not connected — settings shown below are the built-in defaults and can't be saved until the database is set up.</div></div>
<?php endif; ?>

<div class="admin-panel">
  <div class="admin-panel__head"><h2>Notification Settings</h2></div>
  <div class="admin-panel__body">
    <form method="POST">
      <div class="admin-field">
        <label><input type="checkbox" name="notifications_enabled" <?php echo $settings['notifications_enabled'] ? 'checked' : ''; ?>> Enable email notifications for new leads</label>
      </div>
      <div class="admin-field">
        <label>Notification Email(s) — comma-separated for multiple recipients</label>
        <input type="text" name="notification_emails" value="<?php echo htmlspecialchars(implode(', ', $settings['notification_emails'])); ?>">
      </div>
      <div class="admin-field-row">
        <div class="admin-field"><label>Sender Name</label><input type="text" name="sender_name" value="<?php echo htmlspecialchars($settings['sender_name']); ?>"></div>
        <div class="admin-field"><label>Sender Email (No-Reply)</label><input type="email" name="sender_email" value="<?php echo htmlspecialchars($settings['sender_email']); ?>"></div>
      </div>
      <div class="admin-field-row">
        <div class="admin-field"><label>Reply-To Email</label><input type="email" name="reply_to_email" value="<?php echo htmlspecialchars($settings['reply_to_email'] ?? ''); ?>"></div>
        <div class="admin-field"><label>Company Name</label><input type="text" name="company_name" value="<?php echo htmlspecialchars($settings['company_name']); ?>"></div>
      </div>
      <div class="admin-field"><label>Email Logo URL (optional — falls back to text logo)</label><input type="text" name="email_logo_url" value="<?php echo htmlspecialchars($settings['email_logo_url'] ?? ''); ?>"></div>
      <div class="admin-field"><label>Email Footer</label><textarea name="email_footer"><?php echo htmlspecialchars($settings['email_footer'] ?? ''); ?></textarea></div>
      <button type="submit" class="admin-btn admin-btn-primary" <?php echo (!$pdo || !octg_admin_has_role('admin')) ? 'disabled' : ''; ?>>Save Settings</button>
      <?php if (!octg_admin_has_role('admin')): ?><p style="margin-top:10px;color:var(--graphite);font-size:0.82rem;">Your role can view but not change these settings.</p><?php endif; ?>
    </form>
  </div>
</div>

<div class="admin-panel">
  <div class="admin-panel__head"><h2>SMTP (Optional — for later)</h2></div>
  <div class="admin-panel__body">
    <p style="color:var(--graphite); font-size:0.88rem; line-height:1.6;">
      The site currently sends notification emails using PHP's built-in <code>mail()</code> function, which works
      out of the box on Hostinger. To switch to SMTP (PHPMailer, SendGrid, Mailgun, Amazon SES, etc.) later:
      install the client library on the server, then complete the single hook marked in
      <code>includes/notification-service.php</code> — no form or handler file needs to change.
      The <code>email_settings</code> table already has the SMTP host/port/username/password/encryption columns
      ready for that library to read from.
    </p>
  </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
