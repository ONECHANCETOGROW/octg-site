<?php
/** @var array<string,mixed> $audit */
/** @var array<int,array<string,mixed>> $channels */
/** @var array<string,array<int,array<string,mixed>>> $requirementsByCategory */
/** @var array<int,array<string,mixed>> $attemptsByRequirement */
/** @var array<int,bool> $satisfiedByRequirementId */
/** @var \App\Modules\MarketingIntel\DependencyGraph $graph */
/** @var int $completeness */
/** @var string $reachableTier */
/** @var int|null $recommendedNextId */
$pageTitle = (string) $audit['title'];


$statusLabels = [
    'not_started' => 'Not started',
    'pending' => 'Needs review',
    'parsed' => 'Verified',
    'failed' => 'Failed',
];
?>
<h1><?= htmlspecialchars((string)$audit['title']) ?></h1>
<p class="octg-muted">
  Channels: <?= htmlspecialchars((string)implode(', ', array_map(static fn (array $c): string => $c['name'], $channels))) ?>
  · <a href="/audits/<?= (int) $audit['id'] ?>/provenance">Data Source Timeline</a>
  · <a href="/audits/<?= (int) $audit['id'] ?>/knowledge">Knowledge View</a>
</p>

<div class="octg-card" style="display:flex; align-items:center; gap:28px; flex-wrap:wrap;">
  <div style="min-width:220px;">
    <div class="octg-muted" style="margin-bottom:6px;">Completeness</div>
    <div class="mi-progress-bar"><span style="width:<?= (int) $completeness ?>%"></span></div>
    <div style="margin-top:6px; font-weight:700;"><?= (int) $completeness ?>%</div>
  </div>
  <div>
    <div class="octg-muted" style="margin-bottom:6px;">Data Confidence</div>
    <span class="mi-confidence-badge"><span class="dot"></span><?= (int) $audit['overall_confidence'] ?>%</span>
    <p class="octg-muted" style="max-width:260px; margin-top:6px;">
      How much we trust the data collected so far — not a judgment of the client's marketing.
    </p>
  </div>
  <div>
    <div class="octg-muted" style="margin-bottom:6px;">Reachable Report Tier</div>
    <span class="mi-tier-pill <?= htmlspecialchars((string)$reachableTier) ?>"><?= htmlspecialchars((string)$reachableTier) ?></span>
  </div>
</div>

<?php foreach ($requirementsByCategory as $category => $requirements): ?>
  <div class="octg-card">
    <h3><?= htmlspecialchars((string)$category) ?></h3>
    <?php foreach ($requirements as $requirement):
        $reqId = (int) $requirement['id'];
        $attempt = $attemptsByRequirement[$reqId] ?? null;
        $status = $attempt !== null ? (string) $attempt['status'] : 'not_started';
        $blocked = $graph->isBlocked($reqId);
        $capped = $graph->isConfidenceCapped($reqId);
        $isRecommended = $recommendedNextId === $reqId;
        $cardClasses = 'mi-requirement-card' . ($blocked ? ' blocked' : '') . ($isRecommended ? ' recommended' : '');
    ?>
      <div class="<?= $cardClasses ?>">
        <div>
          <div style="font-weight:700;">
            <?= htmlspecialchars((string)$requirement['title']) ?>
            <?php if ((bool) $requirement['is_required']): ?>
              <span class="octg-muted" style="font-weight:400;">· required</span>
            <?php else: ?>
              <span class="octg-muted" style="font-weight:400;">· optional</span>
            <?php endif; ?>
            <?php if ($isRecommended): ?>
              <span class="octg-badge notice" style="margin-left:6px;">Recommended next</span>
            <?php endif; ?>
          </div>
          <div class="octg-muted" style="margin-top:2px;"><?= htmlspecialchars((string)$requirement['purpose']) ?></div>
          <?php if ($blocked): ?>
            <div class="octg-muted" style="margin-top:4px; color:var(--octg-warning);">
              Waiting on another requirement to be collected first.
            </div>
          <?php elseif ($capped && $status === 'parsed'): ?>
            <div class="octg-muted" style="margin-top:4px; color:var(--octg-warning);">
              Confidence capped until a related requirement is also collected.
            </div>
          <?php endif; ?>
          <?php if ($attempt !== null): ?>
            <span class="mi-source-tag" style="margin-top:6px; display:inline-block;">
              via <?= htmlspecialchars((string)str_replace('_', ' ', $attempt['method'])) ?>
            </span>
          <?php endif; ?>
        </div>
        <div style="text-align:right; white-space:nowrap;">
          <span class="mi-status-pill <?= htmlspecialchars((string)$status) ?>"><?= htmlspecialchars((string)$statusLabels[$status] ?? $status) ?></span>
          <div style="margin-top:8px;">
            <a class="octg-btn secondary" href="/audits/<?= (int) $audit['id'] ?>/requirements/<?= $reqId ?>">
              <?= $status === 'not_started' ? 'Start' : 'Open' ?>
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>

<div class="octg-card">
  <h3>Reports</h3>
  <p class="octg-muted">
    Currently reachable tier: <strong><?= htmlspecialchars((string)$reachableTier) ?></strong>.
    Report generation (Client Summary / Internal Detailed, per RNS spec §14) builds on the
    Knowledge View below and is the next phase of this build — see the Developer Handoff
    Package's "Known Limitations" for what's implemented today vs. planned next.
  </p>
  <div style="display:flex; gap:16px; margin-top:16px;">
      <a class="btn btn-secondary" href="/audits/<?= (int) $audit['id'] ?>/knowledge">View collected knowledge</a>
      <?php if ($completeness > 0): ?>
          <form method="post" action="/audits/process">
              <?= $csrfField ?>
              <input type="hidden" name="audit_id" value="<?= (int) $audit['id'] ?>">
              <button type="submit" class="btn btn-primary">Generate Final Report</button>
          </form>
      <?php endif; ?>
  </div>
</div>

<?php  ?>

