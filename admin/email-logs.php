<?php
require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/../includes/notification-service.php';
$pdo = octg_db();

/* Resend */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['resend_id']) && $pdo) {
    $stmt = $pdo->prepare('SELECT * FROM email_logs WHERE id = :id');
    $stmt->execute([':id' => (int) $_POST['resend_id']]);
    $log = $stmt->fetch();
    if ($log) {
        $settings = octg_email_settings();
        $subject = $log['subject'] ?: 'Resent Notification';
        $body = '<p>This is a manual resend of a previous notification. Original subject: ' . htmlspecialchars($subject) . '</p>';
        $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: {$settings['sender_name']} <{$settings['sender_email']}>";
        $sent = @mail($log['recipient'], 'RESEND: ' . $subject, $body, $headers);
        octg_log_email($log['recipient'], $log['form_source'] . ' (resent)', $subject, $sent ? 'sent' : 'failed', $sent ? null : 'Resend failed via mail()');
        octg_log_activity('Email resent', "Log #{$log['id']} to {$log['recipient']}");
    }
    header('Location: /admin/email-logs.php');
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$logs = [];
$dbConnected = (bool) $pdo;

if ($pdo) {
    try {
        $where = [];
        $params = [];
        if ($statusFilter) { $where[] = 'status = ?'; $params[] = $statusFilter; }
        if ($search !== '') { $where[] = 'recipient LIKE ?'; $params[] = '%' . $search . '%'; }
        $sql = 'SELECT * FROM email_logs';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY created_at DESC LIMIT 200';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();
    } catch (Throwable $e) {
        $dbConnected = false;
    }
}

$adminPageTitle = 'Email Logs';
$adminActive = 'email-logs';
include __DIR__ . '/includes/admin-layout-start.php';
?>

<?php if (!$dbConnected): ?>
<div class="admin-panel" style="border-color:#8a6d1a;"><div class="admin-panel__body">Database not connected yet.</div></div>
<?php else: ?>
<div class="admin-panel">
  <div class="admin-panel__head"><h2>Email Delivery Log</h2></div>
  <div class="admin-panel__body">
    <form method="GET" class="admin-filters" style="margin-bottom:20px;">
      <input type="text" name="q" placeholder="Search recipient" value="<?php echo htmlspecialchars($search); ?>">
      <select name="status">
        <option value="">All Statuses</option>
        <option value="sent" <?php echo $statusFilter === 'sent' ? 'selected' : ''; ?>>Sent</option>
        <option value="failed" <?php echo $statusFilter === 'failed' ? 'selected' : ''; ?>>Failed</option>
      </select>
      <button type="submit" class="admin-btn admin-btn-small">Filter</button>
    </form>

    <?php if (!$logs): ?>
      <p class="admin-empty">No emails logged yet — this fills in automatically as forms are submitted.</p>
    <?php else: ?>
    <table class="admin-table">
      <tr><th>Date</th><th>Recipient</th><th>Source</th><th>Status</th><th>Error</th><th></th></tr>
      <?php foreach ($logs as $log): ?>
      <tr>
        <td><?php echo htmlspecialchars(date('M j, g:ia', strtotime($log['created_at']))); ?></td>
        <td><?php echo htmlspecialchars($log['recipient']); ?></td>
        <td><?php echo htmlspecialchars($log['form_source']); ?></td>
        <td><span class="admin-badge admin-badge--<?php echo $log['status']; ?>"><?php echo $log['status']; ?></span></td>
        <td style="color:var(--red);font-size:0.78rem;max-width:220px;"><?php echo htmlspecialchars($log['error_message'] ?? ''); ?></td>
        <td>
          <form method="POST" style="display:inline;">
            <input type="hidden" name="resend_id" value="<?php echo $log['id']; ?>">
            <button type="submit" class="admin-btn admin-btn-small">Resend</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
