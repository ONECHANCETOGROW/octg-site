<?php
require_once __DIR__ . '/includes/admin-auth.php';
$pdo = octg_db();

/* Bulk status update */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo && !empty($_POST['bulk_ids'])) {
    $ids = array_map('intval', $_POST['bulk_ids']);
    $newStatus = $_POST['bulk_status'] ?? '';
    $validStatuses = ['new','contacted','qualified','proposal_sent','won','lost','archived'];
    if (in_array($newStatus, $validStatuses, true) && $ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("UPDATE leads SET status = ? WHERE id IN ($placeholders)");
        $stmt->execute(array_merge([$newStatus], $ids));
        octg_log_activity('Bulk lead status update', count($ids) . ' lead(s) set to ' . $newStatus);
    }
    header('Location: /admin/leads.php');
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
    <form method="POST">
      <div style="margin-bottom:12px; display:flex; gap:10px; align-items:center;">
        <select name="bulk_status">
          <option value="">Bulk set status to…</option>
          <?php foreach (['new','contacted','qualified','proposal_sent','won','lost','archived'] as $s): ?>
          <option value="<?php echo $s; ?>"><?php echo ucwords(str_replace('_',' ',$s)); ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="admin-btn admin-btn-small">Apply to Selected</button>
      </div>
      <table class="admin-table">
        <tr><th></th><th>Name</th><th>Business</th><th>Source</th><th>Status</th><th>Created</th><th></th></tr>
        <?php foreach ($leads as $l): ?>
        <tr>
          <td><input type="checkbox" name="bulk_ids[]" value="<?php echo $l['id']; ?>"></td>
          <td><a href="/admin/lead-detail.php?id=<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['name']); ?></a><br><span style="color:var(--graphite);font-size:0.78rem;"><?php echo htmlspecialchars($l['email']); ?></span></td>
          <td><?php echo htmlspecialchars($l['business_name'] ?? '—'); ?></td>
          <td><?php echo htmlspecialchars(ucwords(str_replace('-',' ',$l['source']))); ?></td>
          <td><span class="admin-badge admin-badge--<?php echo $l['status']; ?>"><?php echo str_replace('_',' ',$l['status']); ?></span></td>
          <td><?php echo htmlspecialchars(date('M j, Y', strtotime($l['created_at']))); ?></td>
          <td><a href="/admin/lead-detail.php?id=<?php echo $l['id']; ?>" class="admin-btn admin-btn-small">Open</a></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
