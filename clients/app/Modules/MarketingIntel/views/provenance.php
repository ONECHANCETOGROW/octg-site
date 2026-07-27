<?php
/** @var array<string,mixed> $audit */
/** @var array<int,array<string,mixed>> $timeline */
/** @var array<int,array<string,mixed>> $variances */
/** @var array<int,array<string,mixed>> $facts */
$pageTitle = 'Data Source Timeline';

?>
<p class="octg-muted"><a href="/audits/<?= (int) $audit['id'] ?>/cockpit">← Back to <?= htmlspecialchars((string)$audit['title']) ?></a></p>
<h1>Data Source Timeline</h1>
<p class="octg-muted">
  Every collection attempt for this audit, in order — the audit trail behind the Data
  Provenance appendix (RNS spec §14). Nothing here is ever overwritten; re-collecting a
  requirement adds a new row rather than erasing the last one.
</p>

<?php if ($variances !== []): ?>
  <div class="octg-card">
    <h3>Needs review — conflicting data</h3>
    <?php foreach ($variances as $variance): ?>
      <div class="mi-variance-flag">
        <?= htmlspecialchars((string)$variance['notes'] ?? 'Conflicting values were resolved by source-trust ranking.') ?>
        <div class="octg-muted" style="margin-top:4px;">Recorded <?= htmlspecialchars((string)$variance['created_at']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="octg-card">
  <h3>Timeline</h3>
  <table class="octg-table">
    <thead><tr><th>When</th><th>Requirement</th><th>Category</th><th>Method</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($timeline as $entry): ?>
      <tr>
        <td class="octg-muted"><?= htmlspecialchars((string)$entry['created_at']) ?></td>
        <td><?= htmlspecialchars((string)$entry['requirement_title']) ?></td>
        <td class="octg-muted"><?= htmlspecialchars((string)$entry['requirement_category']) ?></td>
        <td><?= htmlspecialchars((string)str_replace('_', ' ', $entry['method'])) ?></td>
        <td><span class="mi-status-pill <?= htmlspecialchars((string)$entry['status']) ?>"><?= htmlspecialchars((string)$entry['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    <?php if ($timeline === []): ?>
      <tr><td colspan="5" class="octg-muted">Nothing collected yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="octg-card">
  <h3>Knowledge facts (<?= count($facts) ?>)</h3>
  <table class="octg-table">
    <thead><tr><th>Entity</th><th>Field</th><th>Value</th><th>Confidence</th></tr></thead>
    <tbody>
    <?php foreach ($facts as $fact): ?>
      <tr>
        <td><?= htmlspecialchars((string)$fact['entity_type']) ?>: <?= htmlspecialchars((string)$fact['entity_key']) ?></td>
        <td><?= htmlspecialchars((string)$fact['field_name']) ?></td>
        <td><?= htmlspecialchars((string)$fact['value']) ?></td>
        <td><span class="mi-confidence-badge"><span class="dot"></span><?= (int) $fact['confidence'] ?>%</span></td>
      </tr>
    <?php endforeach; ?>
    <?php if ($facts === []): ?>
      <tr><td colspan="4" class="octg-muted">No knowledge facts yet.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>

<?php  ?>

