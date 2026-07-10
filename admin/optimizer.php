<?php
require_once __DIR__ . '/includes/admin-auth.php';
$pdo = octg_db();

$reportId = isset($_GET['report_id']) ? (int) $_GET['report_id'] : null;
$report = null;
$pageScores = [];
$issues = [];
$history = [];
$dbConnected = (bool) $pdo;

if ($pdo) {
    try {
        if ($reportId) {
            $stmt = $pdo->prepare('SELECT * FROM audit_reports WHERE id = :id');
            $stmt->execute([':id' => $reportId]);
            $report = $stmt->fetch();
        } else {
            $report = $pdo->query('SELECT * FROM audit_reports ORDER BY started_at DESC LIMIT 1')->fetch();
            if ($report) $reportId = $report['id'];
        }

        if ($report) {
            $stmt = $pdo->prepare('SELECT * FROM audit_page_scores WHERE report_id = :id ORDER BY overall_score ASC');
            $stmt->execute([':id' => $reportId]);
            $pageScores = $stmt->fetchAll();

            $stmt = $pdo->prepare("SELECT * FROM audit_issues WHERE report_id = :id ORDER BY FIELD(severity,'critical','warning','info') LIMIT 100");
            $stmt->execute([':id' => $reportId]);
            $issues = $stmt->fetchAll();
        }

        $history = $pdo->query('SELECT id, started_at, overall_score, critical_issue_count, pages_crawled FROM audit_reports ORDER BY started_at DESC LIMIT 15')->fetchAll();
    } catch (Throwable $e) {
        $dbConnected = false;
    }
}

$adminPageTitle = 'AI Website Optimizer';
$adminActive = 'optimizer';
include __DIR__ . '/includes/admin-layout-start.php';
?>

<?php if (!$dbConnected): ?>
<div class="admin-panel" style="border-color:#8a6d1a;"><div class="admin-panel__body">Database not connected — import <code>sql/009_website_optimizer.sql</code> after 001–008, then run an audit.</div></div>
<?php elseif (!$report): ?>
<div class="admin-panel">
  <div class="admin-panel__body">
    <p style="margin-bottom:16px;">No audit has run yet. This crawls every real page over HTTP — it needs the site live and reachable at the configured base URL (see Optimizer Settings).</p>
    <a href="/admin/cron/run-audit.php?manual=1" class="admin-btn admin-btn-primary">Run Audit Now</a>
  </div>
</div>
<?php else: ?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
  <p style="color:var(--graphite); font-size:0.85rem;">Report #<?php echo $report['id']; ?> &middot; <?php echo htmlspecialchars(date('M j, Y g:ia', strtotime($report['started_at']))); ?> &middot; <?php echo $report['pages_crawled']; ?> pages crawled</p>
  <a href="/admin/cron/run-audit.php?manual=1" class="admin-btn admin-btn-small">Run New Audit</a>
</div>

<div class="admin-grid">
  <div class="admin-card" style="grid-column:span 2;">
    <span class="admin-card__label">Overall Website Health</span>
    <div class="admin-card__value" style="font-size:2.6rem;"><?php echo $report['overall_score'] ?? '—'; ?><span style="font-size:1.2rem;color:var(--graphite);">/100</span></div>
  </div>
  <?php
  $catLabels = ['seo'=>'SEO','aeo'=>'AEO','geo'=>'GEO / LLMO','eeat'=>'E-E-A-T','accessibility'=>'Accessibility','performance'=>'Performance','technical_seo'=>'Technical SEO','internal_linking'=>'Internal Linking'];
  foreach ($catLabels as $key => $label):
    $val = $report[$key . '_score'] ?? null;
  ?>
  <div class="admin-card">
    <span class="admin-card__label"><?php echo $label; ?></span>
    <div class="admin-card__value"><?php echo $val !== null ? $val : '—'; ?></div>
  </div>
  <?php endforeach; ?>
</div>

<div class="admin-panel">
  <div class="admin-panel__head">
    <h2>Issues Found (<?php echo count($issues); ?>) &mdash; <?php echo $report['critical_issue_count']; ?> critical, <?php echo $report['warning_count']; ?> warnings</h2>
    <a href="/admin/optimizer-drafts.php" class="admin-btn admin-btn-small">Review Draft Improvements</a>
  </div>
  <div class="admin-panel__body" style="padding:0;">
    <?php if (!$issues): ?>
      <p class="admin-empty">No issues found in this report.</p>
    <?php else: ?>
    <table class="admin-table">
      <tr><th>Severity</th><th>Category</th><th>Issue</th><th>URL</th><th>Reason</th></tr>
      <?php foreach ($issues as $iss): ?>
      <tr>
        <td><span class="admin-badge admin-badge--<?php echo $iss['severity']; ?>"><?php echo $iss['severity']; ?></span></td>
        <td><?php echo htmlspecialchars(str_replace('_',' ',$iss['category'])); ?></td>
        <td><strong><?php echo htmlspecialchars($iss['issue']); ?></strong><?php if ($iss['is_safe_auto_fix']): ?><br><span style="font-size:0.72rem;color:var(--green-deep);">Safe auto-fix available</span><?php endif; ?></td>
        <td style="font-size:0.78rem;"><?php echo htmlspecialchars($iss['url']); ?></td>
        <td style="font-size:0.8rem;color:var(--graphite);max-width:280px;"><?php echo htmlspecialchars($iss['reason']); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>
  </div>
</div>

<div class="admin-panel">
  <div class="admin-panel__head"><h2>Page Scores (Lowest First)</h2></div>
  <div class="admin-panel__body" style="padding:0;">
    <table class="admin-table">
      <tr><th>URL</th><th>Overall</th><th>SEO</th><th>AEO</th><th>GEO</th><th>E-E-A-T</th><th>A11y</th><th>Perf</th><th>Tech SEO</th><th>Int. Links</th></tr>
      <?php foreach ($pageScores as $ps): ?>
      <tr>
        <td style="font-size:0.8rem;"><?php echo htmlspecialchars($ps['url']); ?></td>
        <td><strong><?php echo $ps['overall_score']; ?></strong></td>
        <td><?php echo $ps['seo_score']; ?></td>
        <td><?php echo $ps['aeo_score']; ?></td>
        <td><?php echo $ps['geo_score']; ?></td>
        <td><?php echo $ps['eeat_score']; ?></td>
        <td><?php echo $ps['accessibility_score']; ?></td>
        <td><?php echo $ps['performance_score']; ?></td>
        <td><?php echo $ps['technical_seo_score']; ?></td>
        <td><?php echo $ps['internal_linking_score']; ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>

<div class="admin-panel">
  <div class="admin-panel__head"><h2>Report History</h2></div>
  <div class="admin-panel__body" style="padding:0;">
    <table class="admin-table">
      <tr><th>Date</th><th>Overall Score</th><th>Critical Issues</th><th>Pages</th><th></th></tr>
      <?php foreach ($history as $h): ?>
      <tr>
        <td><?php echo htmlspecialchars(date('M j, Y g:ia', strtotime($h['started_at']))); ?></td>
        <td><?php echo $h['overall_score'] ?? '—'; ?></td>
        <td><?php echo $h['critical_issue_count']; ?></td>
        <td><?php echo $h['pages_crawled']; ?></td>
        <td><a href="?report_id=<?php echo $h['id']; ?>" class="admin-btn admin-btn-small">View</a></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
