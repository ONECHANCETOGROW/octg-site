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
        'notification_emails' => ['support@onechancetogrow.com'],
        'sender_name' => 'One Chance To Grow',
        'sender_email' => 'no-reply@onechancetogrow.com',
        'reply_to_email' => 'support@onechancetogrow.com',
        'company_name' => 'One Chance To Grow LLC',
        'email_footer' => 'One Chance To Grow LLC · Registered in Wyoming, USA',
        'email_logo_url' => null,
        'smtp_enabled' => false,
        'smtp_host' => '',
        'smtp_port' => 587,
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_encryption' => 'tls',
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

function octg_visitor_user_agent(): string {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
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

/* ---- Shared HTML email template ----
   Dark theme to match the site's own premium look. Single-part text/html,
   deliberately kept structurally identical in complexity to the customer
   confirmation email below (which is confirmed reliably delivered) — an
   earlier attempt to strengthen this via a hand-built multipart/alternative
   body was reverted, since it introduced a variable (a manually-built MIME
   body/boundary) that couldn't be verified against a real inbox, on top of
   an already-confirmed-working simple structure. */
function octg_render_lead_email(string $leadType, array $fields, array $settings, ?string $leadUrl): string {
    /* This is now a deliberate structural clone of octg_send_customer_confirmation
       below — same tags (p/strong only, no table rows, no links/buttons), same
       skeleton — because every previous attempt at this template (a data table,
       then a de-emphasized table, then a styled link) still failed to deliver
       while the customer email, plain prose with zero markup variety, reliably
       does. This isolates whether table markup / links were ever really the
       cause. $leadUrl is intentionally unused for now — easy to reintroduce
       once delivery is confirmed working again. */
    $summaryLabels = ['Message', 'Goals', 'Areas Requested'];
    $summaryValue = null;
    $lineParts = [];
    foreach ($fields as $label => $value) {
        if ($value === null || $value === '') continue;
        if (in_array($label, $summaryLabels, true)) {
            $summaryValue = (string) $value;
            continue;
        }
        $lineParts[] = htmlspecialchars($label) . ': ' . htmlspecialchars((string) $value);
    }
    $detailLine = implode('. ', $lineParts);

    $logo = $settings['email_logo_url']
        ? '<img src="' . htmlspecialchars($settings['email_logo_url']) . '" alt="' . htmlspecialchars($settings['company_name']) . '" height="32" style="display:block;">'
        : '<span style="font-family:Georgia,serif;font-size:20px;font-weight:600;color:#F7F6F1;">' . htmlspecialchars($settings['company_name']) . '</span>';

    $footer = htmlspecialchars($settings['email_footer'] ?? '');
    $summaryParagraph = $summaryValue !== null
        ? '<p style="margin:0 0 16px;">' . nl2br(htmlspecialchars($summaryValue)) . '</p>'
        : '';

    return <<<HTML
<!DOCTYPE html>
<html>
<body style="margin:0;padding:0;background:#0B0B08;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#0B0B08;padding:32px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#15150F;border:1px solid rgba(255,255,255,0.08);border-radius:8px;overflow:hidden;">
        <tr><td style="padding:28px 36px;border-bottom:1px solid rgba(255,255,255,0.08);">{$logo}</td></tr>
        <tr><td style="padding:32px 36px 8px;">
          <span style="display:inline-block;font-family:Arial,sans-serif;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:#a3ff47;">{$leadType}</span>
          <h1 style="font-family:Georgia,serif;font-size:22px;color:#F7F6F1;margin:8px 0 0;">New Website Lead Received</h1>
        </td></tr>
        <tr><td style="padding:8px 36px 0;font-family:Arial,sans-serif;font-size:14px;line-height:1.65;color:rgba(247,246,241,0.82);">
          <p style="margin:0 0 16px;">{$detailLine}</p>
          {$summaryParagraph}
        </td></tr>
        <tr><td style="padding:28px 36px 32px;"></td></tr>
        <tr><td style="padding:20px 36px;border-top:1px solid rgba(255,255,255,0.08);font-family:Arial,sans-serif;font-size:11px;color:rgba(247,246,241,0.4);">{$footer}</td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}

/* ---- Channel: email (implemented) ---- */
function octg_send_email_channel(string $formName, array $fields, array $settings, ?int $leadId = null): array {
    if (!$settings['notifications_enabled']) {
        return ['status' => 'skipped', 'reason' => 'notifications disabled'];
    }

    $leadNameForSubject = $fields['Name'] ?? $fields['Business Name'] ?? 'a visitor';
    $subject = "{$formName}: {$leadNameForSubject} just reached out";
    /* TODO: switch back to the onechancetogrow.com domain once the real
       production cutover happens — right now that domain serves an
       unrelated Hostinger Website Builder site (see the read-only audit
       from earlier this engagement), not this PHP admin panel, so a link
       to it would be dead on arrival. This is the actual site the admin
       panel lives on today. */
    $leadBase = 'https://onechancetogrow.com/admin8828051506';
    $leadUrl = $leadId ? "{$leadBase}/lead-detail.php?id={$leadId}" : "{$leadBase}/leads.php";

    $body = octg_render_lead_email($formName, $fields, $settings, $leadUrl);
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

        if (!empty($settings['smtp_enabled'])) {
            $smtpResult = octg_send_via_smtp($recipient, $subject, $body, $settings);
            $sent = $smtpResult['sent'];
            $error = $smtpResult['error'];
        } else {
            $sent = @mail($recipient, $subject, $body, $headers);
            $error = $sent ? null : 'PHP mail() returned false — check that your host has sendmail/mail() enabled.';
        }

        octg_log_email($recipient, $formName, $subject, $sent ? 'sent' : 'failed', $error);
        $results[] = ['recipient' => $recipient, 'sent' => $sent];
    }
    return ['status' => 'processed', 'results' => $results];
}

/* ---- Minimal dependency-free SMTP client ----
   No Composer/PHPMailer on this server, so this speaks SMTP directly over a
   socket: connect, EHLO, STARTTLS (if requested), AUTH LOGIN, MAIL FROM/RCPT
   TO/DATA. Used only for the admin notification path — the customer
   confirmation email is untouched and keeps using mail() exactly as before,
   since it's the one channel confirmed to reliably deliver and nothing
   about it should change. */
function octg_send_via_smtp(string $to, string $subject, string $htmlBody, array $settings): array {
    $host = trim($settings['smtp_host'] ?? '');
    $port = (int) ($settings['smtp_port'] ?? 587);
    $username = trim($settings['smtp_username'] ?? '');
    $password = (string) ($settings['smtp_password'] ?? '');
    $encryption = $settings['smtp_encryption'] ?? 'tls';

    if ($host === '' || $username === '' || $password === '') {
        return ['sent' => false, 'error' => 'SMTP is enabled but Host, Username, or Password is blank in Notification Settings.'];
    }

    $transport = $encryption === 'ssl' ? 'ssl://' : 'tcp://';
    $errno = 0; $errstr = '';
    $socket = @stream_socket_client("{$transport}{$host}:{$port}", $errno, $errstr, 12);
    if (!$socket) {
        return ['sent' => false, 'error' => "Could not connect to {$host}:{$port} — {$errstr} ({$errno})"];
    }
    stream_set_timeout($socket, 12);

    $readResponse = function () use ($socket): string {
        $data = '';
        while (($line = fgets($socket, 515)) !== false) {
            $data .= $line;
            if (!isset($line[3]) || $line[3] === ' ') break;
        }
        return $data;
    };
    $sendCommand = function (string $cmd) use ($socket) {
        fwrite($socket, $cmd . "\r\n");
    };
    $expect = function (string $response, string $code) {
        return strpos($response, $code) === 0;
    };

    $banner = $readResponse();
    if (!$expect($banner, '220')) {
        fclose($socket);
        return ['sent' => false, 'error' => "Unexpected greeting from mail server: " . trim($banner)];
    }

    $ehloHost = parse_url("http://{$host}", PHP_URL_HOST) ?: $host;
    $sendCommand("EHLO {$ehloHost}");
    $readResponse();

    if ($encryption === 'tls') {
        $sendCommand('STARTTLS');
        $tlsResp = $readResponse();
        if (!$expect($tlsResp, '220')) {
            fclose($socket);
            return ['sent' => false, 'error' => 'STARTTLS was not accepted: ' . trim($tlsResp)];
        }
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            return ['sent' => false, 'error' => 'TLS handshake failed.'];
        }
        $sendCommand("EHLO {$ehloHost}");
        $readResponse();
    }

    $sendCommand('AUTH LOGIN');
    $authResp = $readResponse();
    if (!$expect($authResp, '334')) {
        fclose($socket);
        return ['sent' => false, 'error' => 'Server refused AUTH LOGIN: ' . trim($authResp)];
    }
    $sendCommand(base64_encode($username));
    $readResponse();
    $sendCommand(base64_encode($password));
    $passResp = $readResponse();
    if (!$expect($passResp, '235')) {
        fclose($socket);
        return ['sent' => false, 'error' => 'SMTP login failed — check the mailbox username/password: ' . trim($passResp)];
    }

    $sendCommand("MAIL FROM:<{$username}>");
    $mailFromResp = $readResponse();
    if (!$expect($mailFromResp, '250')) {
        fclose($socket);
        return ['sent' => false, 'error' => 'MAIL FROM rejected: ' . trim($mailFromResp)];
    }

    $sendCommand("RCPT TO:<{$to}>");
    $rcptResp = $readResponse();
    if (!$expect($rcptResp, '250') && !$expect($rcptResp, '251')) {
        fclose($socket);
        return ['sent' => false, 'error' => 'RCPT TO rejected: ' . trim($rcptResp)];
    }

    $sendCommand('DATA');
    $dataResp = $readResponse();
    if (!$expect($dataResp, '354')) {
        fclose($socket);
        return ['sent' => false, 'error' => 'DATA command rejected: ' . trim($dataResp)];
    }

    $messageHeaders = implode("\r\n", [
        'From: ' . $settings['sender_name'] . " <{$username}>",
        "To: {$to}",
        'Subject: ' . $subject,
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
    ]);
    $fullMessage = $messageHeaders . "\r\n\r\n" . $htmlBody;
    $fullMessage = preg_replace('/^\./m', '..', $fullMessage); // dot-stuffing per SMTP spec
    $sendCommand($fullMessage . "\r\n.");
    $sendResp = $readResponse();

    $sendCommand('QUIT');
    fclose($socket);

    if ($expect($sendResp, '250')) {
        return ['sent' => true, 'error' => null];
    }
    return ['sent' => false, 'error' => 'Message rejected by server: ' . trim($sendResp)];
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

/* ---- Public entry point every form handler calls ----
   $leadId is the row just inserted into the unified leads table (see
   octg_save_lead() below) — passed through so the notification email's
   "View Lead" button can deep-link straight to that record's detail page
   instead of the generic Leads list. */
function octg_notify_lead(string $formName, array $fields, ?int $leadId = null): void {
    /* Whichever "long free-text" field the caller included (Message for
       contact, Goals for book-demo, Areas Requested for audit) is pulled out
       and re-appended last, so the notification email always reads as:
       Name/Phone/Email/Business Name — Submitted/IP/User Agent — then the
       actual write-up, instead of the write-up landing in the middle. */
    $summaryKey = null;
    foreach (['Message', 'Goals', 'Areas Requested'] as $candidate) {
        if (array_key_exists($candidate, $fields) && $fields[$candidate] !== '') {
            $summaryKey = $candidate;
            break;
        }
    }
    $summaryValue = $summaryKey !== null ? $fields[$summaryKey] : null;
    if ($summaryKey !== null) unset($fields[$summaryKey]);

    $fields['Submitted'] = date('F j, Y \a\t g:i A T');
    $fields['IP Address'] = octg_visitor_ip();
    $location = octg_visitor_location($fields['IP Address']);
    if ($location) $fields['Visitor Location'] = $location;
    $fields['User Agent'] = octg_visitor_user_agent();
    if ($summaryKey !== null) $fields[$summaryKey] = $summaryValue;

    $settings = octg_email_settings();
    foreach (NOTIFICATION_CHANNELS as $channel => $fn) {
        if (function_exists($fn)) {
            $fn($formName, $fields, $settings, $leadId);
        }
    }
}

/* ---- Customer-facing confirmation email ----
   The internal notification above tells the business a lead came in; this
   tells the CUSTOMER their submission was received. Every handler calls
   this right after octg_notify_lead() — it's a no-op (not an error) when
   the visitor didn't supply a valid email, since several forms (e.g. the
   audit request) don't strictly require phone but always require email, so
   in practice this only ever silently skips on malformed input, not on
   forms lacking an email field entirely. */
function octg_send_customer_confirmation(string $formName, string $toEmail, string $toName): array {
    $toEmail = trim($toEmail);
    if ($toEmail === '' || !octg_is_valid_email($toEmail)) {
        return ['status' => 'skipped', 'reason' => 'no valid recipient email']; // not an error, just skip
    }

    $settings = octg_email_settings();
    if (!$settings['notifications_enabled']) {
        return ['status' => 'skipped', 'reason' => 'notifications disabled'];
    }

    $firstName = trim(explode(' ', trim($toName))[0] ?? '');
    $greeting = $firstName !== '' ? "Hi {$firstName}," : 'Hi there,';
    $companyName = $settings['company_name'];
    $footer = htmlspecialchars($settings['email_footer'] ?? '');
    $logo = $settings['email_logo_url']
        ? '<img src="' . htmlspecialchars($settings['email_logo_url']) . '" alt="' . htmlspecialchars($companyName) . '" height="32" style="display:block;">'
        : '<span style="font-family:Georgia,serif;font-size:20px;font-weight:600;color:#F7F6F1;">' . htmlspecialchars($companyName) . '</span>';

    $subject = "We've got your message — {$companyName}";
    $body = <<<HTML
<!DOCTYPE html>
<html>
<body style="margin:0;padding:0;background:#0B0B08;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#0B0B08;padding:32px 0;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#15150F;border:1px solid rgba(255,255,255,0.08);border-radius:8px;overflow:hidden;">
        <tr><td style="padding:28px 36px;border-bottom:1px solid rgba(255,255,255,0.08);">{$logo}</td></tr>
        <tr><td style="padding:32px 36px 8px;">
          <span style="display:inline-block;font-family:Arial,sans-serif;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:#a3ff47;">Message Received</span>
          <h1 style="font-family:Georgia,serif;font-size:22px;color:#F7F6F1;margin:8px 0 0;">Thanks for reaching out</h1>
        </td></tr>
        <tr><td style="padding:8px 36px 0;font-family:Arial,sans-serif;font-size:14px;line-height:1.65;color:rgba(247,246,241,0.82);">
          <p style="margin:0 0 16px;">{$greeting}</p>
          <p style="margin:0 0 16px;">We got your {$formName} submission and a real person on our team will review it shortly.</p>
          <p style="margin:0 0 16px;"><strong style="color:#F7F6F1;">What happens next:</strong> we typically reply within one business day, either by email or a quick call to the number you gave us. No need to submit again — this one's in our system.</p>
          <p style="margin:0;">If anything's urgent in the meantime, just reply to this email or call us at (802) 276-8331.</p>
        </td></tr>
        <tr><td style="padding:28px 36px 32px;"></td></tr>
        <tr><td style="padding:20px 36px;border-top:1px solid rgba(255,255,255,0.08);font-family:Arial,sans-serif;font-size:11px;color:rgba(247,246,241,0.4);">{$footer}</td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;

    $headers = implode("\r\n", [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $settings['sender_name'] . ' <' . $settings['sender_email'] . '>',
        'Reply-To: ' . ($settings['reply_to_email'] ?: $settings['sender_email']),
    ]);

    $sent = @mail($toEmail, $subject, $body, $headers);
    octg_log_email($toEmail, $formName . ' (customer confirmation)', $subject, $sent ? 'sent' : 'failed', $sent ? null : 'PHP mail() returned false');
    return ['status' => 'processed', 'sent' => $sent];
}

/* ---- Optional: insert a normalized record into the unified leads CRM table.
   Only called for genuine sales leads (contact, book-demo, audit) — not the
   newsletter signup, which is a subscription, not a sales lead. Returns the
   new row's id (so the notification email can deep-link to it directly), or
   null if it couldn't be saved — callers must treat that as "no deep link
   available" rather than an error, since the form's own detail table
   already has the submission either way. ---- */
function octg_save_lead(string $source, array $data): ?int {
    $pdo = octg_db();
    if (!$pdo) return null; // contact/demo/audit-handler.php already fallback-log the primary record; this is additive CRM data only

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
        $leadId = (int) $pdo->lastInsertId();
        $stmt2 = $pdo->prepare("INSERT INTO lead_activity (lead_id, type, content, created_by) VALUES (:id, 'created', 'Lead captured automatically from the website.', 'System')");
        $stmt2->execute([':id' => $leadId]);
        return $leadId;
    } catch (Throwable $e) {
        // The form's own specific table (contact_messages/demo_requests/audit_requests)
        // already has this submission — losing the CRM copy here is not data loss.
        return null;
    }
}
