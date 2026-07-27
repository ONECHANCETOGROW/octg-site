<?php
/** @var array<string,mixed> $audit */
/** @var array<string,array<string,array<string,array{value:string,unit:?string,confidence:int}>>> $facts */
/** @var array<int,array{requirement_id:int,code:string,title:string,category:string,is_required:bool,is_satisfied:bool}> $coverage */
$pageTitle = 'Knowledge View';

?>
<p class="octg-muted"><a href="/audits/<?= (int) $audit['id'] ?>/cockpit">← Back to <?= htmlspecialchars((string)$audit['title']) ?></a></p>
<h1>Knowledge View</h1>
<p class="octg-muted">
  The normalized Marketing Intelligence Schema (MIS) for this audit — this is exactly what
  a future Rule/Scoring/Opportunity/Recommendation engine would read (RNS spec §12): source
  method and provenance are intentionally not shown here, only in the
  <a href="/audits/<?= (int) $audit['id'] ?>/provenance">Data Source Timeline</a>.
</p>

<div class="octg-card">
  <h3>Coverage</h3>
  <table class="octg-table">
    <thead><tr><th>Requirement</th><th>Category</th><th>Required</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($coverage as $item): ?>
      <tr>
        <td><?= htmlspecialchars((string)$item['title']) ?></td>
        <td class="octg-muted"><?= htmlspecialchars((string)$item['category']) ?></td>
        <td><?= $item['is_required'] ? 'Required' : 'Optional' ?></td>
        <td>
          <?php if ($item['is_satisfied']): ?>
            <span class="octg-badge opportunity">Satisfied</span>
          <?php else: ?>
            <span class="octg-badge notice">Missing</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php foreach ($facts as $entityType => $entities): ?>
  <div class="octg-card">
    <h3><?= htmlspecialchars((string)$entityType) ?></h3>
    <?php foreach ($entities as $entityKey => $fields): ?>
      <h4 style="margin-bottom:6px;"><?= htmlspecialchars((string)$entityKey) ?></h4>
      <table class="octg-table" style="margin-bottom:16px;">
        <thead><tr><th>Field</th><th>Value</th><th>Confidence</th></tr></thead>
        <tbody>
        <?php foreach ($fields as $fieldName => $fieldData): ?>
          <tr>
            <td><?= htmlspecialchars((string)$fieldName) ?></td>
            <td><?= htmlspecialchars((string)$fieldData['value']) ?><?= $fieldData['unit'] ? ' ' . htmlspecialchars((string)$fieldData['unit']) : '' ?></td>
            <td><span class="mi-confidence-badge"><span class="dot"></span><?= (int) $fieldData['confidence'] ?>%</span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>

<?php if ($facts === []): ?>
  <div class="octg-card"><p class="octg-muted">No knowledge collected yet — go back and start with a requirement.</p></div>
<?php endif; ?>

<?php  ?>

