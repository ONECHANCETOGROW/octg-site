<?php
/* ==========================================================================
   NOTIFICATION-SERVICE.PHP — the one place every lead-generating form routes
   through to notify the business. Forms never call mail() or build an email
   themselves; they call octg_notify_lead() and this file handles the rest.

   Channel architecture: NOTIFICATION_CHANNELS below is deliberately an array
   of channel handlers, even though only 'email' is implemented today. Adding
   SMS, WhatsApp, Slack, or Teams later means writing one new function and
   adding one line to this array — no changes to any form or api/*-handler.php
   file. That's the "future ready" requirement satisfied structurally, not
   just in a comment.
   ========================================================================== */

require_once __DIR__ . '/../api/_lib.php';

/* ---- Settings, with safe defaults if the table/DB isn't available yet ---- */
function octg_email_settings(): array {
    $defaults = [
        'notifications_enabled' => true,
        'notification_emails' => ['hello@onechancetogrow.com'],
        'sender_name' => 'One Chance To Grow',
        'sender_email' => 'no-reply@onechancetogrow.com',
        'reply_to_email' => 'hello@onechancetogrow.com',
        'company_name' => 'One Chance To Grow LLC',
        'email_footer' => 'One Chance To Grow LLC · Registered in Wyoming, USA',
        'email_logo_url' => null,
        'smtp_enabled' => false,
    ];

    $pdo = octg_db();
    if (!$pdo) return $defaults;

    try {
        $stmt = $pdo->query('SELECT * FROM email_settings WHERE id = 1 LIMIT 1');
        $row = $stmt->fetch();
        if (!$row) return $defaults;
        $row['notifications_enabled'] = (bool) $row['notifications_enabled'];
        $row['smtp_enabled'] = (bool) $row['smtp_enabled'];
        $row['notification_emails'] = json_decode($row['notification_emails'] ?? '[]', true) ?: $defaults['notification_emails'];
        return array_merge($defaults, $row);
    } catch (Throwable $e) {
        return $defaults;
    }
}

/* ---- Visitor context: IP is always available; location is best-effort and
   must never block or fail the form if the lookup is slow/unavailable. ---- */
function octg_visitor_ip(): string {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
}

function octg_visitor_location(string $ip): ?string {
    if ($ip === 'Unknown' || $ip === '127.0.0.1' || strpos($ip, '192.168.') === 0) return null;
    try {
        $context = stream_context_create(['http' => ['timeout' => 1.5]]); // never let this hang the form
        $result = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,city,regionName,country", false, $context);
        if (!$result) return null;
        $data = json_decode($result, true);
        if (($data['status'] ?? '') !== 'success') return null;
        return trim(implode(', ', array_filter([$data['city'] ?? '', $data['regionName'] ?? '', $data['country'] ?? ''])));
    } catch (Throwable $e) {
        return null;
    }
}

/* ---- Shared HTML email template ---- */
function octg_render_lead_email(string $formName, array $fields, array $settings): string {
    $submittedAt = date('F j, Y \a\t g:i A T');
    $rows = '';
    foreach ($fields as $label => $value) {
        if ($value === null || $value === '') continue;
        $rows .= '<tr><td style="padding:10px 0;border-top:1px solid #DEDACB;font-family:Arial,sans-serif;font-size:13px;color:#5F5D53;width:160px;vertical-align:top;">' . htmlspecialchars($label) . '</td>'
               . '<td style="padding:10px 0;border-top:1px solid #DEDACB;font-family:Arial,sans-serif;font-size:14px;color:#15150F;vertical-align:top;">' . nl2br(htmlspecialchars((string) $value)) . '</td></tr>';
    }

    $logo = $settings['email_logo_url']
        ? '<img src="' . htmlspecialchars($settings['email_logo_url']) . '" alt="' . htmlspecialchars($settings['company_name']) . '" height="32" style="display:block;">'
        : '<span style="font-family:Georgia,serif;font-size:20px;font-weight:600;color:#15150F;">' . htmlspecialchars($settings['company_name']) . '</span>';

    $footer = htmlspecialchars($settings['email_footer'] ?? '');

    return <<<HTML
<!DOCTYPE html>
<html>
<body style="margin:0;padding:0;background:#F7F6F1;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#F7F6F1;padding:32px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#FFFFFF;border:1px solid #DEDACB;">
        <tr><td style="padding:28px 36px;border-bottom:1px solid #DEDACB;">{$logo}</td></tr>
        <tr><td style="padding:28px 36px 8px;">
          <span style="display:inline-block;font-family:Arial,sans-serif;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:#33500F;">New {$formName} Submission</span>
          <h1 style="font-family:Georgia,serif;font-size:22px;color:#15150F;margin:8px 0 0;">Someone just reached out</h1>
          <p style="font-family:Arial,sans-serif;font-size:13px;color:#5F5D53;margin:8px 0 0;">{$formName} &middot; {$submittedAt}</p>
        </td></tr>
        <tr><td style="padding:8px 36px 28px;">
          <table width="100%" cellpadding="0" cellspacing="0">{$rows}</table>
        </td></tr>
        <tr><td style="padding:0 36px 32px;">
          <a href="https://onechancetogrow.com/admin/leads.php" style="display:inline-block;background:#15150F;color:#F7F6F1;font-family:Arial,sans-serif;font-size:13px;text-decoration:none;padding:14px 26px;">Open in Admin Panel</a>
        </td></tr>
        <tr><td style="padding:20px 36px;border-top:1px solid #DEDACB;font-family:Arial,sans-serif;font-size:11px;color:#5F5D53;">{$footer}</td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}

/* ---- Channel: email (implemented) ---- */
function octg_send_email_channel(string $formName, array $fields, array $settings): array {
    if (!$settings['notifications_enabled']) {
        return ['status' => 'skipped', 'reason' => 'notifications disabled'];
    }

    $subject = "New {$formName} Submission — " . ($fields['Name'] ?? $fields['Business Name'] ?? 'One Chance To Grow');
    $body = octg_render_lead_email($formName, $fields, $settings);
    $headers = implode("\r\n", [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $settings['sender_name'] . ' <' . $settings['sender_email'] . '>',
        'Reply-To: ' . ($settings['reply_to_email'] ?: $settings['sender_email']),
    ]);

    $results = [];
    foreach ($settings['notification_emails'] as $recipient) {
        $recipient = trim($recipient);
        if ($recipient === '') continue;

        /* SMTP hook: when $settings['smtp_enabled'] is true, this is the one
           place to swap in PHPMailer/SendGrid/Mailgun/SES instead of mail() —
           everything above this line (settings, template, recipients) stays
           identical, so enabling SMTP later never touches form logic. */
        if (!empty($settings['smtp_enabled'])) {
            $sent = false; // placeholder: no SMTP library is installed yet
            $error = 'SMTP is enabled in settings but no SMTP client is installed yet. Falling back is not automatic — install PHPMailer (or similar) and complete the hook in notification-service.php.';
        } else {
            $sent = @mail($recipient, $subject, $body, $headers);
            $error = $sent ? null : 'PHP mail() returned false — check that your host has sendmail/mail() enabled.';
        }

        octg_log_email($recipient, $formName, $subject, $sent ? 'sent' : 'failed', $error);
        $results[] = ['recipient' => $recipient, 'sent' => $sent];
    }
    return ['status' => 'processed', 'results' => $results];
}

/* ---- Future channel stubs (not implemented — extension points only) ----
function octg_send_sms_channel(string $formName, array $fields, array $settings): array { ... }
function octg_send_whatsapp_channel(string $formName, array $fields, array $settings): array { ... }
function octg_send_slack_channel(string $formName, array $fields, array $settings): array { ... }
function octg_send_teams_channel(string $formName, array $fields, array $settings): array { ... }
   To add one: write the function, then add its name to NOTIFICATION_CHANNELS
   below. octg_notify_lead() will call it automatically. */

const NOTIFICATION_CHANNELS = ['email' => 'octg_send_email_channel'];

function octg_log_email(string $recipient, string $formSource, string $subject, string $status, ?string $error): void {
    $pdo = octg_db();
    if (!$pdo) {
        octg_log_fallback('email_logs', compact('recipient', 'formSource', 'subject', 'status', 'error'));
        return;
    }
    try {
        $stmt = $pdo->prepare('INSERT INTO email_logs (recipient, form_source, subject, status, error_message) VALUES (:r, :f, :s, :st, :e)');
        $stmt->execute([':r' => $recipient, ':f' => $formSource, ':s' => $subject, ':st' => $status, ':e' => $error]);
    } catch (Throwable $e) {
        octg_log_fallback('email_logs', compact('recipient', 'formSource', 'subject', 'status', 'error'));
    }
}

/* ---- Public entry point every form handler calls ---- */
function octg_notify_lead(string $formName, array $fields): void {
    $ip = octg_visitor_ip();
    $location = octg_visitor_location($ip);

    $contextFields = ['IP Address' => $ip];
    if ($location) $contextFields['Visitor Location'] = $location;
    $fields = array_merge($contextFields, $fields);

    $settings = octg_email_settings();
    foreach (NOTIFICATION_CHANNELS as $channel => $fn) {
        if (function_exists($fn)) {
            $fn($formName, $fields, $settings);
        }
    }
}

/* ---- Optional: insert a normalized record into the unified leads CRM table.
   Only called for genuine sales leads (contact, book-demo, audit) — not the
   newsletter signup, which is a subscription, not a sales lead. ---- */
function octg_save_lead(string $source, array $data): void {
    $pdo = octg_db();
    if (!$pdo) return; // contact/demo/audit-handler.php already fallback-log the primary record; this is additive CRM data only

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO leads (name, business_name, phone, email, website, source, interested_service, message, source_page)
             VALUES (:name, :business_name, :phone, :email, :website, :source, :interested_service, :message, :source_page)'
        );
        $stmt->execute([
            ':name' => $data['name'] ?? '',
            ':business_name' => $data['business_name'] ?? null,
            ':phone' => $data['phone'] ?? null,
            ':email' => $data['email'] ?? '',
            ':website' => $data['website'] ?? null,
            ':source' => $source,
            ':interested_service' => $data['interested_service'] ?? null,
            ':message' => $data['message'] ?? null,
            ':source_page' => $data['source_page'] ?? null,
        ]);
        $leadId = $pdo->lastInsertId();
        $stmt2 = $pdo->prepare("INSERT INTO lead_activity (lead_id, type, content, created_by) VALUES (:id, 'created', 'Lead captured automatically from the website.', 'System')");
        $stmt2->execute([':id' => $leadId]);
    } catch (Throwable $e) {
        // The form's own specific table (contact_messages/demo_requests/audit_requests)
        // already has this submission — losing the CRM copy here is not data loss.
    }
}
