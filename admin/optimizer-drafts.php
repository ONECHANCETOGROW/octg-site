<?php
require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/../includes/optimizer/safe-fixes.php';
$pdo = octg_db();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    if (!empty($_POST['regenerate'])) {
        $target = $_POST['regenerate'];
        $result = null;
        if ($target === 'sitemap') $result = octg_regenerate_sitemap();
        elseif ($target === 'sitemap-images') $result = octg_regenerate_image_sitemap();
        elseif ($target === 'llms') $result = octg_regenerate_llms_txt();
        elseif ($target === 'robots') $result = octg_regenerate_robots_txt();
        if ($result) {
            $message = $result;
            octg_log_activity('Regenerated ' . $target, $result);
        }
    } elseif (!empty($_POST['draft_id']) && !empty($_POST['decision'])) {
        $draftId = (int) $_POST['draft_id'];
        $decision = $_POST['decision'] === 'approve' ? 'approved' : 'rejected';
        $pdo->prepare('UPDATE draft_improvements SET status = :s, reviewed_by = :by, reviewed_at = NOW() WHERE id = :id')
            ->execute([':s' => $decision, ':by' => octg_admin_user()['name'], ':id' => $draftId]);
        octg_log_activity('Draft improvement ' . $decision, "Draft #{$draftId}");
        /* Note: approving marks it reviewed and ready, but per "never
           automatically modify the live website," actually writing the
           approved value into a page/data file is a separate, explicit step
           an admin takes — this is intentionally not auto-applied here. */
    }
    header('Location: /admin/optimizer-drafts.php');
    exit;
}

$drafts = [];
$dbConnected = (bool) $pdo;
if ($pdo) {
    try {
        $drafts = $pdo->query("SELECT * FROM draft_improvements WHERE status = 'pending' ORDER BY created_at DESC LIMIT 100")->fetchAll();
    } catch (Throwable $e) {
        $dbConnected = false;
    }
}

$adminPageTitle = 'Draft Improvements';
$adminActive = 'optimizer';
include __DIR__ . '/includes/admin-layout-start.php';
?>

<?php if ($message): ?><div class="admin-success" style="margin-bottom:20px;"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>

<div class="admin-panel">
  <div class="admin-panel__head"><h2>Safe Auto-Fixes</h2></div>
  <div class="admin-panel__body">
    <p style="color:var(--graphite); font-size:0.86rem; margin-bottom:18px; line-height:1.6;">
      These regenerate derived metadata files from your real content — they can't introduce new facts or touch
      page copy, so they run immediately when you click, no review needed. Everything else the optimizer finds
      (missing alt text, weak meta descriptions, etc.) goes into the review queue below instead.
    </p>
    <div style="display:flex; flex-wrap:wrap; gap:12px;">
      <form method="POST"><input type="hidden" name="regenerate" value="sitemap"><button type="submit" class="admin-btn">Regenerate sitemap.xml</button></form>
      <form method="POST"><input type="hidden" name="regenerate" value="sitemap-images"><button type="submit" class="admin-btn">Regenerate sitemap-images.xml</button></form>
      <form method="POST"><input type="hidden" name="regenerate" value="llms"><button type="submit" class="admin-btn">Regenerate llms.txt</button></form>
      <form method="POST"><input type="hidden" name="regenerate" value="robots"><button type="submit" class="admin-btn">Regenerate robots.txt</button></form>
    </div>
  </div>
</div>

<div class="admin-panel">
  <div class="admin-panel__head"><h2>Pending Review (<?php echo count($drafts); ?>)</h2></div>
  <div class="admin-panel__body" style="padding:0;">
    <?php if (!$dbConnected): ?>
      <p class="admin-empty">Database not connected.</p>
    <?php elseif (!$drafts): ?>
      <p class="admin-empty">No drafts awaiting review. Run an audit to generate suggestions for fixable issues.</p>
    <?php else: ?>
    <table class="admin-table">
      <tr><th>URL</th><th>Field</th><th>Suggested Value</th><th>Reasoning</th><th></th></tr>
      <?php foreach ($drafts as $d): ?>
      <tr>
        <td style="font-size:0.8rem;"><?php echo htmlspecialchars($d['url']); ?></td>
        <td><?php echo htmlspecialchars($d['field']); ?></td>
        <td style="font-size:0.85rem;max-width:260px;"><?php echo htmlspecialchars($d['suggested_value']); ?></td>
        <td style="font-size:0.8rem;color:var(--graphite);max-width:240px;"><?php echo htmlspecialchars($d['reasoning']); ?></td>
        <td style="white-space:nowrap;">
          <form method="POST" style="display:inline;"><input type="hidden" name="draft_id" value="<?php echo $d['id']; ?>"><input type="hidden" name="decision" value="approve"><button type="submit" class="admin-btn admin-btn-small admin-btn-primary">Approve</button></form>
          <form method="POST" style="display:inline;"><input type="hidden" name="draft_id" value="<?php echo $d['id']; ?>"><input type="hidden" name="decision" value="reject"><button type="submit" class="admin-btn admin-btn-small">Reject</button></form>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
    <p style="padding:16px 22px; font-size:0.78rem; color:var(--graphite);">Approving marks a draft reviewed and ready — actually writing the value into the page's data file is a separate manual step for now, consistent with never auto-modifying marketing copy without you seeing it first.</p>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
