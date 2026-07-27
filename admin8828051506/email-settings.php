<?php
require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/../includes/notification-service.php';
$pdo = octg_db();
$saved = false;
$testResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'send_test') {
    if (!octg_admin_has_role('admin')) {
        die('Only Admin or Super Admin can send test emails.');
    }
    $testTo = trim($_POST['test_email'] ?? '');
    $testKind = $_POST['test_kind'] ?? 'notification';

    if ($testTo === '' || !octg_is_valid_email($testTo)) {
        $testResult = ['ok' => false, 'message' => 'Enter a valid email address to send the test to.'];
    } else {
        $sampleFields = [
            'Name' => 'Test Lead',
            'Business Name' => 'Sample Business Co.',
            'Email' => 'sample.customer@example.com',
            'Phone' => '(555) 123-4567',
            'Message' => "This is a test email sent from the Notifications page — it uses the exact same template a real lead notification uses, so you can confirm formatting and delivery without submitting a real form.",
        ];
        if ($testKind === 'confirmation') {
            $result = octg_send_customer_confirmation('Contact Form (Test)', $testTo, 'Test Lead');
            if ($result['status'] === 'skipped') {
                $reason = $result['reason'] === 'notifications disabled'
                    ? 'Notifications are turned off (the checkbox below) — nothing was sent.'
                    : 'That email address failed validation — nothing was sent.';
                $testResult = ['ok' => false, 'message' => $reason];
            } elseif ($result['sent']) {
                $testResult = ['ok' => true, 'message' => "PHP's mail() reported success sending the test confirmation to {$testTo}. Check spam/junk if it doesn't show up in the inbox within a few minutes."];
            } else {
                $testResult = ['ok' => false, 'message' => "PHP's mail() function reported failure sending to {$testTo} — this is a server-level mail problem (not a settings problem). Check with your hosting provider about the mail()/sendmail configuration for this account."];
            }
        } else {
            // Sends only to the address typed above via a one-off settings
            // copy — calling octg_send_email_channel() directly rather than
            // going through octg_notify_lead() (which always reads the real
            // configured list) so a test send never also emails every real
            // notification address by accident.
            $sampleFields = array_merge([
                'Submitted' => date('F j, Y \a\t g:i A T'),
                'IP Address' => octg_visitor_ip(),
                'User Agent' => octg_visitor_user_agent(),
            ], $sampleFields);
            $testSettings = octg_email_settings();
            $testSettings['notification_emails'] = [$testTo];
            $result = octg_send_email_channel('Contact Form (Test)', $sampleFields, $testSettings, null);
            if ($result['status'] === 'skipped') {
                $testResult = ['ok' => false, 'message' => 'Notifications are turned off (the checkbox below) — nothing was sent.'];
            } elseif (!empty($result['results'][0]['sent'])) {
                $testResult = ['ok' => true, 'message' => "PHP's mail() reported success sending the test notification to {$testTo}. Check spam/junk if it doesn't show up in the inbox within a few minutes."];
            } else {
                $testResult = ['ok' => false, 'message' => "PHP's mail() function reported failure sending to {$testTo} — this is a server-level mail problem (not a settings problem). Check with your hosting provider about the mail()/sendmail configuration for this account."];
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo && ($_POST['action'] ?? '') !== 'send_test') {
    if (!octg_admin_has_role('admin')) {
        die('Only Admin or Super Admin can change email settings.');
    }
    $emails = array_filter(array_map('trim', $_POST['notification_emails'] ?? []));
    $currentSettings = octg_email_settings(); // read before overwriting, so a blank password field below doesn't wipe out one already saved
    $stmt = $pdo->prepare(
        'INSERT INTO email_settings (id, notifications_enabled, notification_emails, sender_name, sender_email, reply_to_email, company_name, email_footer, email_logo_url, smtp_enabled, smtp_host, smtp_port, smtp_username, smtp_password, smtp_encryption)
         VALUES (1, :enabled, :emails, :sname, :semail, :reply, :cname, :footer, :logo, :smtpenabled, :smtphost, :smtpport, :smtpuser, :smtppass, :smtpenc)
         ON DUPLICATE KEY UPDATE notifications_enabled=:enabled2, notification_emails=:emails2, sender_name=:sname2, sender_email=:semail2, reply_to_email=:reply2, company_name=:cname2, email_footer=:footer2, email_logo_url=:logo2, smtp_enabled=:smtpenabled2, smtp_host=:smtphost2, smtp_port=:smtpport2, smtp_username=:smtpuser2, smtp_password=:smtppass2, smtp_encryption=:smtpenc2'
    );
    $vals = [
        'enabled' => isset($_POST['notifications_enabled']) ? 1 : 0,
        'emails' => json_encode(array_values($emails)),
        'sname' => trim($_POST['sender_name']), 'semail' => trim($_POST['sender_email']),
        'reply' => trim($_POST['reply_to_email']), 'cname' => trim($_POST['company_name']),
        'footer' => trim($_POST['email_footer']), 'logo' => trim($_POST['email_logo_url']) ?: null,
        'smtpenabled' => isset($_POST['smtp_enabled']) ? 1 : 0,
        'smtphost' => trim($_POST['smtp_host'] ?? ''),
        'smtpport' => (int) ($_POST['smtp_port'] ?? 587),
        'smtpuser' => trim($_POST['smtp_username'] ?? ''),
        /* Only overwrite the stored password if a new one was actually typed
           — the field is intentionally rendered blank on every page load
           (see below), so leaving it blank and saving must not wipe out a
           password that was already set. */
        'smtppass' => trim($_POST['smtp_password'] ?? '') !== '' ? trim($_POST['smtp_password']) : ($currentSettings['smtp_password'] ?? ''),
        'smtpenc' => in_array($_POST['smtp_encryption'] ?? '', ['none', 'ssl', 'tls'], true) ? $_POST['smtp_encryption'] : 'tls',
    ];
    $stmt->execute([
        ':enabled' => $vals['enabled'], ':emails' => $vals['emails'], ':sname' => $vals['sname'], ':semail' => $vals['semail'],
        ':reply' => $vals['reply'], ':cname' => $vals['cname'], ':footer' => $vals['footer'], ':logo' => $vals['logo'],
        ':smtpenabled' => $vals['smtpenabled'], ':smtphost' => $vals['smtphost'], ':smtpport' => $vals['smtpport'], ':smtpuser' => $vals['smtpuser'], ':smtppass' => $vals['smtppass'], ':smtpenc' => $vals['smtpenc'],
        ':enabled2' => $vals['enabled'], ':emails2' => $vals['emails'], ':sname2' => $vals['sname'], ':semail2' => $vals['semail'],
        ':reply2' => $vals['reply'], ':cname2' => $vals['cname'], ':footer2' => $vals['footer'], ':logo2' => $vals['logo'],
        ':smtpenabled2' => $vals['smtpenabled'], ':smtphost2' => $vals['smtphost'], ':smtpport2' => $vals['smtpport'], ':smtpuser2' => $vals['smtpuser'], ':smtppass2' => $vals['smtppass'], ':smtpenc2' => $vals['smtpenc'],
    ]);
    octg_log_activity('Email settings updated');
    $saved = true;
}

$settings = octg_email_settings();

$adminPageTitle = 'Notifications';
$adminActive = 'notifications';
include __DIR__ . '/includes/admin-layout-start.php';
?>

<?php if ($saved): ?><div class="admin-success" style="margin-bottom:20px;">Settings saved.</div><?php endif; ?>
<?php if ($testResult): ?><div class="<?php echo $testResult['ok'] ? 'admin-success' : 'admin-alert admin-alert--error'; ?>" style="margin-bottom:20px;"><?php echo htmlspecialchars($testResult['message']); ?></div><?php endif; ?>
<?php if (!$pdo): ?>
<div class="admin-panel" style="border-color:#8a6d1a;"><div class="admin-panel__body">Database not connected — settings shown below are the built-in defaults and can't be saved until the database is set up.</div></div>
<?php endif; ?>

<div class="admin-panel">
  <div class="admin-panel__head"><h2>Send Test Email</h2></div>
  <div class="admin-panel__body">
    <p style="color:var(--graphite); font-size:0.88rem; line-height:1.6; margin-bottom:16px;">
      Sends a real email using the exact same template and code path as a live lead — the fastest way to confirm
      delivery without submitting a real form. Check the destination inbox's spam/junk folder too, not just the inbox.
    </p>
    <form method="POST">
      <input type="hidden" name="action" value="send_test">
      <div class="admin-field-row" style="align-items:flex-end;">
        <div class="admin-field" style="flex:1;">
          <label>Send test to</label>
          <input type="email" name="test_email" placeholder="you@example.com" required value="<?php echo htmlspecialchars($_POST['test_email'] ?? ($settings['notification_emails'][0] ?? '')); ?>">
        </div>
        <div class="admin-field">
          <button type="submit" name="test_kind" value="notification" class="admin-btn admin-btn-primary" <?php echo !octg_admin_has_role('admin') ? 'disabled' : ''; ?>>Send Test Admin Notification</button>
        </div>
        <div class="admin-field">
          <button type="submit" name="test_kind" value="confirmation" class="admin-btn" <?php echo !octg_admin_has_role('admin') ? 'disabled' : ''; ?>>Send Test Customer Confirmation</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="admin-panel">
  <div class="admin-panel__head"><h2>Notification Settings</h2></div>
  <div class="admin-panel__body">
    <form method="POST">
      <div class="admin-field">
        <label><input type="checkbox" name="notifications_enabled" <?php echo $settings['notifications_enabled'] ? 'checked' : ''; ?>> Enable email notifications for new leads</label>
      </div>
      <div class="admin-field">
        <label>Notification Emails — every lead notification goes to all of these</label>
        <div id="notifyEmailRows">
          <?php $notifyEmails = $settings['notification_emails'] ?: ['']; foreach ($notifyEmails as $i => $addr): ?>
          <div class="admin-field-row" style="margin-bottom:8px; align-items:center;">
            <input type="email" name="notification_emails[]" placeholder="<?php echo $i === 0 ? 'Primary notification email' : 'Additional notification email'; ?>" value="<?php echo htmlspecialchars($addr); ?>" style="flex:1;">
            <button type="button" class="admin-btn admin-btn-small" onclick="this.parentElement.remove()">Remove</button>
          </div>
          <?php endforeach; ?>
        </div>
        <button type="button" class="admin-btn admin-btn-small" onclick="octgAddNotifyEmailRow()">+ Add Another Email</button>
      </div>
      <script>
      function octgAddNotifyEmailRow(){
        var wrap = document.getElementById('notifyEmailRows');
        var row = document.createElement('div');
        row.className = 'admin-field-row';
        row.style.marginBottom = '8px';
        row.style.alignItems = 'center';
        row.innerHTML = '<input type="email" name="notification_emails[]" placeholder="Additional notification email" style="flex:1;">'
          + '<button type="button" class="admin-btn admin-btn-small" onclick="this.parentElement.remove()">Remove</button>';
        wrap.appendChild(row);
        row.querySelector('input').focus();
      }
      </script>
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

      <div style="margin-top:30px; padding-top:24px; border-top:1px solid var(--paper-line);">
        <h3 style="margin:0 0 6px;">SMTP (recommended)</h3>
        <p style="color:var(--graphite); font-size:0.85rem; line-height:1.6; margin-bottom:16px;">
          The admin notification email now supports real authenticated SMTP — this is the fix for "mail() reports success but nothing arrives," a known limitation of plain PHP <code>mail()</code> on shared hosting. Enable this and point it at a real mailbox on this hosting account (e.g. <code>support@onechancetogrow.com</code>) for reliable delivery. The customer confirmation email is untouched and keeps using <code>mail()</code> as-is.
        </p>
        <div class="admin-field">
          <label><input type="checkbox" name="smtp_enabled" <?php echo !empty($settings['smtp_enabled']) ? 'checked' : ''; ?>> Send admin notifications via SMTP instead of mail()</label>
        </div>
        <div class="admin-field-row">
          <div class="admin-field" style="flex:2;"><label>SMTP Host</label><input type="text" name="smtp_host" placeholder="smtp.hostinger.com" value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>"></div>
          <div class="admin-field"><label>Port</label><input type="number" name="smtp_port" value="<?php echo htmlspecialchars((string) ($settings['smtp_port'] ?? 587)); ?>"></div>
          <div class="admin-field">
            <label>Encryption</label>
            <select name="smtp_encryption">
              <?php foreach (['tls' => 'TLS (STARTTLS, port 587)', 'ssl' => 'SSL (port 465)', 'none' => 'None'] as $val => $labelText): ?>
              <option value="<?php echo $val; ?>" <?php echo ($settings['smtp_encryption'] ?? 'tls') === $val ? 'selected' : ''; ?>><?php echo $labelText; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="admin-field-row">
          <div class="admin-field"><label>Mailbox Username</label><input type="text" name="smtp_username" placeholder="support@onechancetogrow.com" value="<?php echo htmlspecialchars($settings['smtp_username'] ?? ''); ?>"></div>
          <div class="admin-field">
            <label>Mailbox Password</label>
            <input type="password" name="smtp_password" placeholder="<?php echo !empty($settings['smtp_password']) ? '••••••••  (already saved — leave blank to keep it)' : 'Enter mailbox password'; ?>" autocomplete="new-password">
          </div>
        </div>
      </div>

      <button type="submit" class="admin-btn admin-btn-primary" style="margin-top:16px;" <?php echo (!$pdo || !octg_admin_has_role('admin')) ? 'disabled' : ''; ?>>Save Settings</button>
      <?php if (!octg_admin_has_role('admin')): ?><p style="margin-top:10px;color:var(--graphite);font-size:0.82rem;">Your role can view but not change these settings.</p><?php endif; ?>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
