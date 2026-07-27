<?php
/* ==========================================================================
   BOOKINGS.PHP — dedicated admin view for Book a Growth Call submissions.
   Reads directly from demo_requests (sql/002_demo_requests.sql), which
   already has the richer fields (budget_range, services_interested,
   business_type) that the generic unified leads table doesn't carry —
   no schema change needed, that table already has a status column too.

   Book a Demo submissions also still get a normalized row in the shared
   leads table (source='book-demo', see includes/notification-service.php
   octg_save_lead()) — that's intentional and untouched, it's what powers
   cross-source reporting later. This page is the source-specific, detailed
   view; "Notes" links out to that leads-table record's activity timeline
   via lead-detail.php, rather than duplicating note-taking here.
   ========================================================================== */
require_once __DIR__ . '/includes/admin-auth.php';
$pdo = octg_db();

$validStatuses = ['new', 'contacted', 'scheduled', 'completed', 'archived'];

/* Single-row quick actions: status change or delete */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo && !empty($_POST['row_id'])) {
    $rowId = (int) $_POST['row_id'];
    $rowAction = $_POST['row_action'] ?? '';
    if ($rowAction === 'archive') {
        $pdo->prepare('UPDATE demo_requests SET status = ? WHERE id = ?')->execute(['archived', $rowId]);
        octg_log_activity('Booking archived', "Booking #{$rowId}");
    } elseif ($rowAction === 'delete') {
        $pdo->prepare('DELETE FROM demo_requests WHERE id = ?')->execute([$rowId]);
        octg_log_activity('Booking deleted', "Booking #{$rowId}");
    } elseif ($rowAction === 'set_status' && in_array($_POST['new_status'] ?? '', $validStatuses, true)) {
        $pdo->prepare('UPDATE demo_requests SET status = ? WHERE id = ?')->execute([$_POST['new_status'], $rowId]);
        octg_log_activity('Booking status updated', "Booking #{$rowId} set to {$_POST['new_status']}");
    }
    header('Location: /admin8828051506/bookings.php');
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$bookings = [];
$dbConnected = (bool) $pdo;
if ($pdo) {
    try {
        $where = [];
        $params = [];
        if ($statusFilter) { $where[] = 'status = ?'; $params[] = $statusFilter; }
        if ($search !== '') {
            $where[] = '(business_name LIKE ? OR contact_name LIKE ? OR email LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        $sql = 'SELECT * FROM demo_requests';
        if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
        $sql .= ' ORDER BY created_at DESC LIMIT 200';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $bookings = $stmt->fetchAll();

        /* Cross-reference into the unified leads table for the "Notes" link —
           matched by email, since both rows are written in the same request
           by octg_save_lead('book-demo', ...) at submission time. */
        if ($bookings) {
            $emails = array_column($bookings, 'email');
            $placeholders = implode(',', array_fill(0, count($emails), '?'));
            $leadStmt = $pdo->prepare("SELECT id, email FROM leads WHERE source = 'book-demo' AND email IN ($placeholders) ORDER BY created_at DESC");
            $leadStmt->execute($emails);
            $leadIdByEmail = [];
            foreach ($leadStmt->fetchAll() as $row) {
                if (!isset($leadIdByEmail[$row['email']])) $leadIdByEmail[$row['email']] = $row['id'];
            }
        }
    } catch (Throwable $e) {
        $dbConnected = false;
    }
}

$adminPageTitle = 'Bookings';
$adminActive = 'bookings';
include __DIR__ . '/includes/admin-layout-start.php';
?>

<?php if (!$dbConnected): ?>
<div class="admin-panel" style="border-color:#8a6d1a;"><div class="admin-panel__body">Database not connected — see the Dashboard for setup steps.</div></div>
<?php else: ?>

<div class="admin-panel">
  <div class="admin-panel__head">
    <h2>All Bookings (<?php echo count($bookings); ?>)</h2>
  </div>
  <div class="admin-panel__body">
    <form method="GET" class="admin-filters" style="margin-bottom:20px;">
      <input type="text" name="q" placeholder="Search business, contact, email" value="<?php echo htmlspecialchars($search); ?>">
      <select name="status">
        <option value="">All Statuses</option>
        <?php foreach ($validStatuses as $s): ?>
        <option value="<?php echo $s; ?>" <?php echo $statusFilter === $s ? 'selected' : ''; ?>><?php echo ucwords($s); ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="admin-btn admin-btn-small">Filter</button>
    </form>

    <?php if (!$bookings): ?>
      <p class="admin-empty">No bookings match these filters yet.</p>
    <?php else: ?>
      <table class="admin-table">
        <tr><th>Business / Contact</th><th>Phone</th><th>Budget</th><th>Requested Services</th><th>Status</th><th>Submitted</th><th></th><th></th></tr>
        <?php foreach ($bookings as $b):
          $services = [];
          if (!empty($b['services_interested'])) {
              $decoded = json_decode($b['services_interested'], true);
              if (is_array($decoded)) $services = $decoded;
          }
          $leadId = $leadIdByEmail[$b['email']] ?? null;
        ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($b['business_name']); ?></strong><br>
            <span style="color:var(--graphite);font-size:0.78rem;"><?php echo htmlspecialchars($b['contact_name']); ?> &middot; <a href="mailto:<?php echo htmlspecialchars($b['email']); ?>"><?php echo htmlspecialchars($b['email']); ?></a></span>
          </td>
          <td><?php echo $b['phone'] ? '<a href="tel:' . htmlspecialchars($b['phone']) . '">' . htmlspecialchars($b['phone']) . '</a>' : '—'; ?></td>
          <td><?php echo htmlspecialchars($b['budget_range'] ?: '—'); ?></td>
          <td><?php echo $services ? htmlspecialchars(implode(', ', $services)) : '—'; ?></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="row_id" value="<?php echo $b['id']; ?>">
              <input type="hidden" name="row_action" value="set_status">
              <select name="new_status" onchange="this.form.submit()" class="admin-badge admin-badge--<?php echo $b['status']; ?>" style="border:none;cursor:pointer;">
                <?php foreach ($validStatuses as $s): ?>
                <option value="<?php echo $s; ?>" <?php echo $b['status'] === $s ? 'selected' : ''; ?>><?php echo ucwords($s); ?></option>
                <?php endforeach; ?>
              </select>
            </form>
          </td>
          <td><?php echo htmlspecialchars(date('M j, Y g:ia', strtotime($b['created_at']))); ?></td>
          <td>
            <?php if ($leadId): ?>
            <a href="/admin8828051506/lead-detail.php?id=<?php echo $leadId; ?>" class="admin-btn admin-btn-small">Notes &amp; Timeline</a>
            <?php else: ?>
            <span style="color:var(--graphite);font-size:0.78rem;">—</span>
            <?php endif; ?>
          </td>
          <td>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this booking? This cannot be undone.');">
              <input type="hidden" name="row_id" value="<?php echo $b['id']; ?>">
              <input type="hidden" name="row_action" value="delete">
              <button type="submit" class="admin-btn admin-btn-small admin-btn--danger">Delete</button>
            </form>
          </td>
        </tr>
        <?php if (!empty($b['goals'])): ?>
        <tr>
          <td colspan="8" style="color:var(--graphite);font-size:0.82rem;padding-top:0;"><strong>Goals:</strong> <?php echo nl2br(htmlspecialchars($b['goals'])); ?></td>
        </tr>
        <?php endif; ?>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
