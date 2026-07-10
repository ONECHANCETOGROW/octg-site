<?php
require_once __DIR__ . '/includes/admin-auth.php';
require_once __DIR__ . '/../includes/knowledge/graph-builder.php';
require_once __DIR__ . '/../includes/knowledge/gap-detector.php';
require_once __DIR__ . '/../includes/knowledge/entity-consistency.php';
require_once __DIR__ . '/../includes/knowledge/topical-authority.php';
require_once __DIR__ . '/../includes/knowledge/freshness.php';
require_once __DIR__ . '/../includes/knowledge/link-graph.php';

$pdo = octg_db();
$graph = octg_build_knowledge_graph();
$tab = $_GET['tab'] ?? 'overview';

$adminPageTitle = 'Website Intelligence';
$adminActive = 'intelligence';
include __DIR__ . '/includes/admin-layout-start.php';
?>

<div class="admin-filters" style="margin-bottom:24px;">
  <?php foreach (['overview'=>'Overview','gaps'=>'Content Gaps','consistency'=>'Entity Consistency','topics'=>'Topical Authority','links'=>'Internal Link Graph','freshness'=>'Content Freshness'] as $key => $label): ?>
  <a href="?tab=<?php echo $key; ?>" class="admin-btn admin-btn-small <?php echo $tab === $key ? 'admin-btn-primary' : ''; ?>"><?php echo $label; ?></a>
  <?php endforeach; ?>
</div>

<?php if ($tab === 'overview'): ?>
  <div class="admin-grid">
    <div class="admin-card"><span class="admin-card__label">Services</span><div class="admin-card__value"><?php echo count($graph['raw']['services']); ?></div></div>
    <div class="admin-card"><span class="admin-card__label">Products</span><div class="admin-card__value"><?php echo count($graph['raw']['products']); ?></div></div>
    <div class="admin-card"><span class="admin-card__label">Industries</span><div class="admin-card__value"><?php echo count($graph['raw']['industries']); ?></div></div>
    <div class="admin-card"><span class="admin-card__label">Case Studies</span><div class="admin-card__value"><?php echo count($graph['raw']['cases']); ?></div></div>
    <div class="admin-card"><span class="admin-card__label">Articles</span><div class="admin-card__value"><?php echo count($graph['raw']['articles']); ?></div></div>
    <div class="admin-card"><span class="admin-card__label">Team Members</span><div class="admin-card__value"><?php echo count($graph['raw']['team']); ?></div></div>
    <div class="admin-card"><span class="admin-card__label">Total Relationships</span><div class="admin-card__value"><?php echo count($graph['edges']); ?></div></div>
    <?php $directCount = count(array_filter($graph['edges'], fn($e) => $e[5] === 'direct')); ?>
    <div class="admin-card"><span class="admin-card__label">Direct / Derived</span><div class="admin-card__value" style="font-size:1.3rem;"><?php echo $directCount; ?> / <?php echo count($graph['edges']) - $directCount; ?></div></div>
  </div>

  <div class="admin-panel">
    <div class="admin-panel__head"><h2>How This Graph Is Built</h2></div>
    <div class="admin-panel__body">
      <p style="color:var(--graphite); font-size:0.86rem; line-height:1.7;">
        Every node and edge above is read live from the real catalog files (<code>data/services-catalog.php</code>,
        <code>products-catalog.php</code>, <code>case-studies-catalog.php</code>, <code>resources-catalog.php</code>,
        <code>team-catalog.php</code>, and <code>industries.php</code>) — nothing is duplicated into a database table
        that could drift out of sync. "Direct" relationships are explicit fields in that data (a service's
        <code>related_product</code>, a product's <code>pairs_with</code> list). "Derived" relationships are computed
        by following a chain of direct ones — for example, a Product only connects to a Case Study by way of a
        Service both reference, never assumed directly.
      </p>
    </div>
  </div>

  <div class="admin-panel">
    <div class="admin-panel__head"><h2>Sample Relationships</h2></div>
    <div class="admin-panel__body" style="padding:0;">
      <table class="admin-table">
        <tr><th>From</th><th>Relationship</th><th>To</th><th>Type</th><th>Source</th></tr>
        <?php foreach (array_slice($graph['edges'], 0, 20) as $e): ?>
        <tr>
          <td><?php echo htmlspecialchars($e[0] . ':' . $e[1]); ?></td>
          <td><?php echo htmlspecialchars($e[4]); ?></td>
          <td><?php echo htmlspecialchars($e[2] . ':' . $e[3]); ?></td>
          <td><span class="admin-badge admin-badge--<?php echo $e[5] === 'direct' ? 'sent' : 'archived'; ?>"><?php echo $e[5]; ?></span></td>
          <td style="font-size:0.76rem; color:var(--graphite);"><?php echo htmlspecialchars($e[6]); ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <p style="padding:14px 22px; font-size:0.78rem; color:var(--graphite);">Showing 20 of <?php echo count($graph['edges']); ?> total relationships.</p>
    </div>
  </div>

<?php elseif ($tab === 'gaps'): ?>
  <?php $gaps = octg_detect_content_gaps($graph, $pdo); ?>
  <div class="admin-panel">
    <div class="admin-panel__head"><h2>Content Gaps (<?php echo count($gaps); ?>)</h2></div>
    <div class="admin-panel__body" style="padding:0;">
      <?php if (!$gaps): ?>
        <p class="admin-empty">No gaps detected against the current rubric.</p>
      <?php else: ?>
      <table class="admin-table">
        <tr><th>Severity</th><th>Category</th><th>Finding</th><th>Detail</th></tr>
        <?php foreach ($gaps as $g): ?>
        <tr>
          <td><span class="admin-badge admin-badge--<?php echo $g['severity']; ?>"><?php echo $g['severity']; ?></span></td>
          <td><?php echo htmlspecialchars(str_replace('_',' ',$g['category'])); ?></td>
          <td><strong><?php echo htmlspecialchars($g['title']); ?></strong></td>
          <td style="font-size:0.82rem; color:var(--graphite); max-width:360px;"><?php echo htmlspecialchars($g['description']); ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <?php endif; ?>
    </div>
  </div>

<?php elseif ($tab === 'consistency'): ?>
  <?php $consistency = octg_check_entity_consistency(); ?>
  <div class="admin-panel">
    <div class="admin-panel__head"><h2>Entity Consistency</h2></div>
    <div class="admin-panel__body" style="padding:0;">
      <table class="admin-table">
        <tr><th>Entity</th><th>Status</th><th>Detail</th></tr>
        <?php foreach ($consistency as $c): ?>
        <tr>
          <td><?php echo htmlspecialchars(str_replace('_',' ',$c['type'])); ?></td>
          <td><span class="admin-badge admin-badge--<?php echo $c['status'] === 'consistent' ? 'sent' : ($c['status'] === 'inconsistent' ? 'critical' : 'warning'); ?>"><?php echo str_replace('_',' ',$c['status']); ?></span></td>
          <td style="font-size:0.85rem;"><?php echo htmlspecialchars($c['detail']); ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>

<?php elseif ($tab === 'topics'): ?>
  <?php $topics = octg_measure_topical_authority($graph); ?>
  <div class="admin-panel">
    <div class="admin-panel__head"><h2>Topic Cluster Coverage</h2></div>
    <div class="admin-panel__body" style="padding:0;">
      <table class="admin-table">
        <tr><th>Topic</th><th>Services</th><th>Articles</th><th>Case Studies</th><th>Industries</th><th>FAQs</th></tr>
        <?php foreach ($topics['coverage'] as $key => $data): ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($data['label']); ?></strong></td>
          <td><?php echo $data['services']; ?></td>
          <td><?php echo $data['articles']; ?></td>
          <td><?php echo $data['case_studies']; ?></td>
          <td><?php echo $data['industries']; ?></td>
          <td><?php echo $data['faqs']; ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
  <?php if ($topics['recommendations']): ?>
  <div class="admin-panel">
    <div class="admin-panel__head"><h2>Recommendations</h2></div>
    <div class="admin-panel__body">
      <?php foreach ($topics['recommendations'] as $r): ?>
        <p style="margin-bottom:12px; font-size:0.88rem;"><?php echo htmlspecialchars($r['detail']); ?></p>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

<?php elseif ($tab === 'links'): ?>
  <?php $linkStats = octg_get_link_graph_stats($pdo); ?>
  <?php if (!$linkStats['available']): ?>
    <div class="admin-panel" style="border-color:#8a6d1a;"><div class="admin-panel__body"><?php echo htmlspecialchars($linkStats['reason']); ?> <a href="/admin/cron/run-audit.php?manual=1">Run an audit</a>.</div></div>
  <?php else: ?>
    <div class="admin-grid">
      <div class="admin-card"><span class="admin-card__label">Pages Crawled</span><div class="admin-card__value"><?php echo $linkStats['total_pages']; ?></div></div>
      <div class="admin-card"><span class="admin-card__label">Total Internal Links</span><div class="admin-card__value"><?php echo $linkStats['total_links']; ?></div></div>
      <div class="admin-card"><span class="admin-card__label">Orphan Pages</span><div class="admin-card__value"><?php echo count($linkStats['orphan_pages']); ?></div></div>
    </div>
    <div class="admin-grid" style="grid-template-columns:1fr 1fr;">
      <div class="admin-panel">
        <div class="admin-panel__head"><h2>Most Linked Pages</h2></div>
        <div class="admin-panel__body" style="padding:0;">
          <table class="admin-table"><tr><th>URL</th><th>Inbound Links</th></tr>
          <?php foreach ($linkStats['most_linked'] as $url => $count): ?><tr><td style="font-size:0.8rem;"><?php echo htmlspecialchars($url); ?></td><td><?php echo $count; ?></td></tr><?php endforeach; ?>
          </table>
        </div>
      </div>
      <div class="admin-panel">
        <div class="admin-panel__head"><h2>Least Linked Pages</h2></div>
        <div class="admin-panel__body" style="padding:0;">
          <table class="admin-table"><tr><th>URL</th><th>Inbound Links</th></tr>
          <?php foreach ($linkStats['least_linked'] as $url => $count): ?><tr><td style="font-size:0.8rem;"><?php echo htmlspecialchars($url); ?></td><td><?php echo $count; ?></td></tr><?php endforeach; ?>
          </table>
        </div>
      </div>
    </div>
    <?php if ($linkStats['orphan_pages']): ?>
    <div class="admin-panel">
      <div class="admin-panel__head"><h2>Orphan Pages (Zero Inbound Links)</h2></div>
      <div class="admin-panel__body">
        <ul style="font-size:0.85rem; line-height:1.9;"><?php foreach ($linkStats['orphan_pages'] as $o): ?><li><?php echo htmlspecialchars($o); ?></li><?php endforeach; ?></ul>
      </div>
    </div>
    <?php endif; ?>
  <?php endif; ?>

<?php elseif ($tab === 'freshness'): ?>
  <?php $freshness = octg_check_content_freshness(); ?>
  <div class="admin-panel">
    <div class="admin-panel__head"><h2>Content Freshness</h2></div>
    <div class="admin-panel__body">
      <p style="color:var(--graphite); font-size:0.82rem; margin-bottom:16px;">Based on real file modification timestamps — not invented review dates. See the file header comment in <code>includes/knowledge/freshness.php</code> for the honest caveat on what this does and doesn't tell you.</p>
    </div>
    <div class="admin-panel__body" style="padding:0;">
      <table class="admin-table">
        <tr><th>Content</th><th>Last Modified</th><th>Age</th><th>Status</th></tr>
        <?php foreach ($freshness as $f): ?>
        <tr>
          <td><?php echo htmlspecialchars($f['label']); ?></td>
          <td><?php echo htmlspecialchars($f['last_modified']); ?></td>
          <td><?php echo $f['age_days']; ?> days</td>
          <td><span class="admin-badge admin-badge--<?php echo $f['is_stale'] ? 'warning' : 'sent'; ?>"><?php echo $f['is_stale'] ? 'Review recommended' : 'Recent'; ?></span></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin-layout-end.php'; ?>
