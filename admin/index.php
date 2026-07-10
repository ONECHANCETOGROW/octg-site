<?php
require_once __DIR__ . '/includes/admin-auth.php';

$pdo = octg_db();

/* Real counts from actual content catalogs — these are true regardless of DB status */
$servicesCount = count(require __DIR__ . '/../data/services-catalog.php');
$articlesCount = count(require __DIR__ . '/../data/resources-catalog.php');
$caseStudiesCount = count(require __DIR__ . '/../data/case-studies-catalog.php');

/* Real counts from the database, where available */
$totalLeads = $newLeadsToday = $wonLeads = 0;
$recentActivity = [];
$dbConnected = (bool) $pdo;

if ($pdo) {
    try {
        $totalLeads = (int) $pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn();
        $newLeadsToday = (int) $pdo->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()")->fetchColumn();
        $wonLeads = (int) $pdo->query("SELECT COUNT(*) FROM leads WHERE status = 'won'")->fetchColumn();
        $recentActivity = $pdo->query('SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 10')->fetchAll();
    } catch (Throwable $e) {
        $dbConnected = false; // tables likely not migrated yet
    }
}

$conversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : null;

$adminPageTitle = 'Dashboard';
$adminActive = 'dashboard';
include __DIR__ . '/includes/admin-layout-start.php';
?>

<?php if (!$dbConnected): ?>
<div class="admin-panel" style="border-color:#8a6d1a;">
  <div class="admin-panel__body">
    <strong>Database not connected yet.</strong> Copy <code>includes/db-config.example.php</code> to
    <code>includes/db-config.php</code> with your real Hostinger credentials, then import
    <code>sql/001</code> through <code>sql/008</code> via phpMyAdmin. Lead counts, activity, and email
    logs below will populate automatically once that's done — nothing else needs to change.
  </div>
</div>
<?php endif; ?>

<div class="admin-grid">
  <div class="admin-card">
    <span class="admin-card__label">Total Leads</span>
    <div class="admin-card__value"><?php echo $dbConnected ? $totalLeads : '—'; ?></div>
  </div>
  <div class="admin-card">
    <span class="admin-card__label">New Leads Today</span>
    <div class="admin-card__value"><?php echo $dbConnected ? $newLeadsToday : '—'; ?></div>
  </div>
  <div class="admin-card">
    <span class="admin-card__label">Conversion Rate</span>
    <div class="admin-card__value"><?php echo $conversionRate !== null ? $conversionRate . '%' : '—'; ?></div>
    <span class="admin-card__note">Won ÷ total leads</span>
  </div>
  <div class="admin-card">
    <span class="admin-card__label">Published Services</span>
    <div class="admin-card__value"><?php echo $servicesCount; ?></div>
  </div>
  <div class="admin-card">
    <span class="admin-card__label">Published Resources</span>
    <div class="admin-card__value"><?php echo $articlesCount; ?></div>
  </div>
  <div class="admin-card">
    <span class="admin-card__label">Published Case Studies</span>
    <div class="admin-card__value"><?php echo $caseStudiesCount; ?></div>
  </div>
  <div class="admin-card">
    <span class="admin-card__label">Active Visitors (Live)</span>
    <div class="admin-card__value">—</div>
    <span class="admin-card__note is-pending">Not yet built — needs a visitor-tracking system (see notes)</span>
  </div>
  <div class="admin-card">
    <span class="admin-card__label">Visitors Today / Week / Month</span>
    <div class="admin-card__value">—</div>
    <span class="admin-card__note is-pending">Not yet built — same as above</span>
  </div>
</div>

<div class="admin-panel">
  <div class="admin-panel__head">
    <h2>Recent Activity</h2>
    <a href="/admin/activity-log.php" class="admin-btn admin-btn-small">View All</a>
  </div>
  <div class="admin-panel__body" style="padding:0;">
    <?php if (!$recentActivity): ?>
      <p class="admin-empty"><?php echo $dbConnected ? 'No activity logged yet.' : 'Connect the database to see activity.'; ?></p>
    <?php else: ?>
      <table class="admin-table">
        <?php foreach ($recentActivity as $row): ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($row['action']); ?></strong><br><span style="color:var(--graphite);font-size:0.8rem;"><?php echo htmlspecialchars($row['description']); ?></span></td>
          <td><?php echo htmlspecialchars($row['actor']); ?></td>
          <td><?php echo htmlspecialchars(date('M j, g:ia', strtotime($row['created_at']))); ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
