<?php
require_once __DIR__ . '/includes/admin-auth.php';
$pdo = octg_db();

$logs = [];
$dbConnected = (bool) $pdo;
if ($pdo) {
    try {
        $logs = $pdo->query('SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 300')->fetchAll();
    } catch (Throwable $e) {
        $dbConnected = false;
    }
}

$adminPageTitle = 'Activity Log';
$adminActive = 'activity';
include __DIR__ . '/includes/admin-layout-start.php';
?>

<?php if (!$dbConnected): ?>
<div class="admin-panel" style="border-color:#8a6d1a;"><div class="admin-panel__body">Database not connected yet.</div></div>
<?php else: ?>
<div class="admin-panel">
  <div class="admin-panel__head"><h2>Full Activity Log</h2></div>
  <div class="admin-panel__body" style="padding:0;">
    <?php if (!$logs): ?>
      <p class="admin-empty">No activity logged yet.</p>
    <?php else: ?>
    <table class="admin-table">
      <tr><th>Action</th><th>Description</th><th>By</th><th>When</th></tr>
      <?php foreach ($logs as $log): ?>
      <tr>
        <td><strong><?php echo htmlspecialchars($log['action']); ?></strong></td>
        <td><?php echo htmlspecialchars($log['description']); ?></td>
        <td><?php echo htmlspecialchars($log['actor']); ?></td>
        <td><?php echo htmlspecialchars(date('M j, Y g:ia', strtotime($log['created_at']))); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
