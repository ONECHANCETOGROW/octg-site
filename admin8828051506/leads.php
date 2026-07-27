<?php
require_once __DIR__ . '/includes/admin-auth.php';
$pdo = octg_db();

/* Bulk status update / bulk delete */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo && !empty($_POST['bulk_ids'])) {
    $ids = array_map('intval', $_POST['bulk_ids']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    if (!empty($_POST['bulk_delete']) && $ids) {
        $stmt = $pdo->prepare("DELETE FROM leads WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        octg_log_activity('Bulk lead delete', count($ids) . ' lead(s) permanently deleted');
    } else {
        $newStatus = $_POST['bulk_status'] ?? '';
        $validStatuses = ['new','contacted','qualified','proposal_sent','won','lost','archived'];
        if (in_array($newStatus, $validStatuses, true) && $ids) {
            $stmt = $pdo->prepare("UPDATE leads SET status = ? WHERE id IN ($placeholders)");
            $stmt->execute(array_merge([$newStatus], $ids));
            octg_log_activity('Bulk lead status update', count($ids) . ' lead(s) set to ' . $newStatus);
        }
    }
    header('Location: /admin8828051506/leads.php');
    exit;
}

/* Single-row quick actions: archive or delete, straight from the list */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo && !empty($_POST['row_id'])) {
    $rowId = (int) $_POST['row_id'];
    if (($_POST['row_action'] ?? '') === 'archive') {
        $pdo->prepare('UPDATE leads SET status = ? WHERE id = ?')->execute(['archived', $rowId]);
        octg_log_activity('Lead archived', "Lead #{$rowId}");
    } elseif (($_POST['row_action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM leads WHERE id = ?')->execute([$rowId]);
        octg_log_activity('Lead deleted', "Lead #{$rowId}");
    }
    header('Location: /admin8828051506/leads.php');
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$sourceFilter = $_GET['source'] ?? '';
$search = trim($_GET['q'] ?? '');

$leads = [];
$dbConnected = (bool) $pdo;
if ($pdo) {
    try {
        $where = [];
        $params = [];
        if ($statusFilter) { $where[] = 'status = ?'; $params[] = $statusFilter; }
        if ($sourceFilter) { $where[] = 'source = ?'; $params[] = $sourceFilter; }
        if ($search !== '') {
            $where[] = '(name LIKE ? OR business_name LIKE ? OR email LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        $sql = 'SELECT * FROM leads';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY created_at DESC LIMIT 200';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $leads = $stmt->fetchAll();
    } catch (Throwable $e) {
        $dbConnected = false;
    }
}

/* Export to CSV */
if (isset($_GET['export']) && $pdo) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="leads-export-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Name', 'Business', 'Email', 'Phone', 'Source', 'Status', 'Interested Service', 'Created']);
    foreach ($leads as $l) {
        fputcsv($out, [$l['name'], $l['business_name'], $l['email'], $l['phone'], $l['source'], $l['status'], $l['interested_service'], $l['created_at']]);
    }
    fclose($out);
    exit;
}

$adminPageTitle = 'Leads';
$adminActive = 'leads';
include __DIR__ . '/includes/admin-layout-start.php';
?>

<?php if (!$dbConnected): ?>
<div class="admin-panel" style="border-color:#8a6d1a;"><div class="admin-panel__body">Database not connected — see the Dashboard for setup steps.</div></div>
<?php else: ?>

<div class="admin-panel">
  <div class="admin-panel__head">
    <h2>All Leads (<?php echo count($leads); ?>)</h2>
    <a href="?export=1<?php echo $statusFilter ? '&status=' . urlencode($statusFilter) : ''; ?>" class="admin-btn admin-btn-small">Export CSV</a>
  </div>
  <div class="admin-panel__body">
    <form method="GET" class="admin-filters" style="margin-bottom:20px;">
      <input type="text" name="q" placeholder="Search name, business, email" value="<?php echo htmlspecialchars($search); ?>">
      <select name="status">
        <option value="">All Statuses</option>
        <?php foreach (['new','contacted','qualified','proposal_sent','won','lost','archived'] as $s): ?>
        <option value="<?php echo $s; ?>" <?php echo $statusFilter === $s ? 'selected' : ''; ?>><?php echo ucwords(str_replace('_',' ',$s)); ?></option>
        <?php endforeach; ?>
      </select>
      <select name="source">
        <option value="">All Sources</option>
        <?php foreach (['contact','book-demo','audit'] as $s): ?>
        <option value="<?php echo $s; ?>" <?php echo $sourceFilter === $s ? 'selected' : ''; ?>><?php echo ucwords(str_replace('-',' ',$s)); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="admin-btn admin-btn-small">Filter</button>
    </form>

    <?php if (!$leads): ?>
      <p class="admin-empty">No leads match these filters yet.</p>
    <?php else: ?>
    <form method="POST" id="bulkLeadsForm">
      <div style="margin-bottom:12px; display:flex; gap:10px; align-items:center;">
        <select name="bulk_status">
          <option value="">Bulk set status to…</option>
          <?php foreach (['new','contacted','qualified','proposal_sent','won','lost','archived'] as $s): ?>
          <option value="<?php echo $s; ?>"><?php echo ucwords(str_replace('_',' ',$s)); ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="admin-btn admin-btn-small">Apply to Selected</button>
        <button type="submit" name="bulk_delete" value="1" class="admin-btn admin-btn-small admin-btn--danger" onclick="return confirm('Permanently delete the selected lead(s)? This cannot be undone.');">Delete Selected</button>
      </div>
      <table class="admin-table">
        <tr><th></th><th>Name</th><th>Phone</th><th>Business</th><th>Source</th><th>Status</th><th>Created</th><th colspan="3"></th></tr>
        <?php foreach ($leads as $l): ?>
        <tr>
          <td><input type="checkbox" name="bulk_ids[]" value="<?php echo $l['id']; ?>" form="bulkLeadsForm"></td>
          <td><a href="/admin8828051506/lead-detail.php?id=<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['name']); ?></a><br><span style="color:var(--graphite);font-size:0.78rem;"><?php echo htmlspecialchars($l['email']); ?></span></td>
          <td><?php echo $l['phone'] ? '<a href="tel:' . htmlspecialchars($l['phone']) . '">' . htmlspecialchars($l['phone']) . '</a>' : '—'; ?></td>
          <td><?php echo htmlspecialchars($l['business_name'] ?? '—'); ?></td>
          <td><?php echo htmlspecialchars(ucwords(str_replace('-',' ',$l['source']))); ?></td>
          <td><span class="admin-badge admin-badge--<?php echo $l['status']; ?>"><?php echo str_replace('_',' ',$l['status']); ?></span></td>
          <td><?php echo htmlspecialchars(date('M j, Y', strtotime($l['created_at']))); ?></td>
          <td><a href="/admin8828051506/lead-detail.php?id=<?php echo $l['id']; ?>" class="admin-btn admin-btn-small">Open</a></td>
          <td>
            <?php if ($l['status'] !== 'archived'): ?>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="row_id" value="<?php echo $l['id']; ?>">
              <input type="hidden" name="row_action" value="archive">
              <button type="submit" class="admin-btn admin-btn-small">Archive</button>
            </form>
            <?php endif; ?>
          </td>
          <td>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this lead? This cannot be undone.');">
              <input type="hidden" name="row_id" value="<?php echo $l['id']; ?>">
              <input type="hidden" name="row_action" value="delete">
              <button type="submit" class="admin-btn admin-btn-small admin-btn--danger">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
