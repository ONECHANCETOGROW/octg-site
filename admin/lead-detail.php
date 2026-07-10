<?php
require_once __DIR__ . '/includes/admin-auth.php';
$pdo = octg_db();

$id = (int) ($_GET['id'] ?? 0);
if (!$pdo || !$id) { header('Location: /admin/leads.php'); exit; }

$stmt = $pdo->prepare('SELECT * FROM leads WHERE id = :id');
$stmt->execute([':id' => $id]);
$lead = $stmt->fetch();
if (!$lead) { header('Location: /admin/leads.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_status') {
        $newStatus = $_POST['status'];
        $followUp = $_POST['follow_up_date'] ?: null;
        $pdo->prepare('UPDATE leads SET status = :s, follow_up_date = :f WHERE id = :id')
            ->execute([':s' => $newStatus, ':f' => $followUp, ':id' => $id]);
        $pdo->prepare("INSERT INTO lead_activity (lead_id, type, content, created_by) VALUES (:id, 'status_change', :c, :actor)")
            ->execute([':id' => $id, ':c' => "Status changed to {$newStatus}", ':actor' => octg_admin_user()['name']]);
        octg_log_activity('Lead status updated', "Lead #{$id} set to {$newStatus}");
    } elseif ($action === 'add_note' && trim($_POST['note'] ?? '') !== '') {
        $pdo->prepare("INSERT INTO lead_activity (lead_id, type, content, created_by) VALUES (:id, 'note', :c, :actor)")
            ->execute([':id' => $id, ':c' => trim($_POST['note']), ':actor' => octg_admin_user()['name']]);
        $pdo->prepare('UPDATE leads SET last_contacted_at = NOW() WHERE id = :id')->execute([':id' => $id]);
    }
    header('Location: /admin/lead-detail.php?id=' . $id);
    exit;
}

$activity = $pdo->prepare('SELECT * FROM lead_activity WHERE lead_id = :id ORDER BY created_at DESC');
$activity->execute([':id' => $id]);
$timeline = $activity->fetchAll();

$adminPageTitle = 'Lead: ' . $lead['name'];
$adminActive = 'leads';
include __DIR__ . '/includes/admin-layout-start.php';
?>

<a href="/admin/leads.php" class="admin-btn admin-btn-small" style="margin-bottom:20px;">&larr; All Leads</a>

<div class="admin-grid" style="grid-template-columns:1.3fr 1fr;">
  <div class="admin-panel">
    <div class="admin-panel__head"><h2>Lead Details</h2><span class="admin-badge admin-badge--<?php echo $lead['status']; ?>"><?php echo str_replace('_',' ',$lead['status']); ?></span></div>
    <div class="admin-panel__body">
      <table class="admin-table">
        <tr><td style="width:160px;"><strong>Name</strong></td><td><?php echo htmlspecialchars($lead['name']); ?></td></tr>
        <tr><td><strong>Business</strong></td><td><?php echo htmlspecialchars($lead['business_name'] ?? '—'); ?></td></tr>
        <tr><td><strong>Email</strong></td><td><a href="mailto:<?php echo htmlspecialchars($lead['email']); ?>"><?php echo htmlspecialchars($lead['email']); ?></a></td></tr>
        <tr><td><strong>Phone</strong></td><td><?php echo htmlspecialchars($lead['phone'] ?? '—'); ?></td></tr>
        <tr><td><strong>Website</strong></td><td><?php echo htmlspecialchars($lead['website'] ?? '—'); ?></td></tr>
        <tr><td><strong>Source</strong></td><td><?php echo htmlspecialchars(ucwords(str_replace('-',' ',$lead['source']))); ?></td></tr>
        <tr><td><strong>Interested In</strong></td><td><?php echo htmlspecialchars($lead['interested_service'] ?? '—'); ?></td></tr>
        <tr><td><strong>Message</strong></td><td><?php echo nl2br(htmlspecialchars($lead['message'] ?? '—')); ?></td></tr>
        <tr><td><strong>Received</strong></td><td><?php echo htmlspecialchars(date('M j, Y g:ia', strtotime($lead['created_at']))); ?></td></tr>
      </table>
    </div>
  </div>

  <div>
    <div class="admin-panel">
      <div class="admin-panel__head"><h2>Update Status</h2></div>
      <div class="admin-panel__body">
        <form method="POST">
          <input type="hidden" name="action" value="update_status">
          <div class="admin-field">
            <label>Status</label>
            <select name="status">
              <?php foreach (['new','contacted','qualified','proposal_sent','won','lost','archived'] as $s): ?>
              <option value="<?php echo $s; ?>" <?php echo $lead['status'] === $s ? 'selected' : ''; ?>><?php echo ucwords(str_replace('_',' ',$s)); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="admin-field">
            <label>Follow-up Date</label>
            <input type="date" name="follow_up_date" value="<?php echo htmlspecialchars($lead['follow_up_date'] ?? ''); ?>">
          </div>
          <button type="submit" class="admin-btn admin-btn-primary">Save</button>
        </form>
      </div>
    </div>

    <div class="admin-panel">
      <div class="admin-panel__head"><h2>Add Note</h2></div>
      <div class="admin-panel__body">
        <form method="POST">
          <input type="hidden" name="action" value="add_note">
          <div class="admin-field"><textarea name="note" placeholder="Log a call, email, or internal note…" required></textarea></div>
          <button type="submit" class="admin-btn admin-btn-primary">Add Note</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="admin-panel">
  <div class="admin-panel__head"><h2>Timeline</h2></div>
  <div class="admin-panel__body" style="padding:0;">
    <?php if (!$timeline): ?>
      <p class="admin-empty">No activity logged for this lead yet.</p>
    <?php else: ?>
      <table class="admin-table">
        <?php foreach ($timeline as $t): ?>
        <tr>
          <td style="width:140px;"><span class="admin-badge admin-badge--new" style="text-transform:capitalize;"><?php echo str_replace('_',' ',$t['type']); ?></span></td>
          <td><?php echo nl2br(htmlspecialchars($t['content'])); ?></td>
          <td style="width:160px;color:var(--graphite);font-size:0.8rem;"><?php echo htmlspecialchars($t['created_by']); ?><br><?php echo htmlspecialchars(date('M j, g:ia', strtotime($t['created_at']))); ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
