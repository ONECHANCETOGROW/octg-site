<?php
/** @var array<string,mixed> $audit */
/** @var array<string,mixed> $requirement */
/** @var array<int,array{template:array<string,mixed>,rendered_text:string}> $renderedPrompts */
/** @var array<int,array<string,mixed>> $uploadTemplates */
/** @var array<int,array<string,mixed>> $history */
/** @var array<string,mixed>|null $latestAttempt */
/** @var array<string,mixed>|null $latestExtraction */
/** @var array<int,array<string,mixed>> $latestIssues */
/** @var string|null $error */
$pageTitle = (string) $requirement['title'];

require_once __DIR__ . '/../../Shared/helpers.php';
?>
<p class="octg-muted"><a href="/audits/<?= (int) $audit['id'] ?>/cockpit">← Back to <?= htmlspecialchars((string)$audit['title']) ?></a></p>
<h1><?= htmlspecialchars((string)$requirement['title']) ?></h1>
<p class="octg-muted"><?= htmlspecialchars((string)$requirement['description'] ?? $requirement['purpose']) ?></p>

<?php if ($error): ?>
  <div class="octg-error"><?= htmlspecialchars((string)$error) ?></div>
<?php endif; ?>

<?php if ($latestExtraction !== null): ?>
  <div class="octg-card">
    <h3>Current status</h3>
    <span class="mi-confidence-badge"><span class="dot"></span>Confidence: <?= (int) $latestExtraction['overall_confidence'] ?>%</span>
    <span class="mi-source-tag" style="margin-left:8px;">via <?= htmlspecialchars((string)str_replace('_', ' ', (string) $latestAttempt['method'])) ?></span>

    <?php if ($latestIssues !== []): ?>
      <div style="margin-top:14px;">
        <?php foreach ($latestIssues as $issue): ?>
          <div class="octg-issue-impact" style="background:<?= $issue['severity'] === 'critical' ? 'var(--octg-critical-tint)' : 'var(--octg-warning-tint)' ?>; color:<?= $issue['severity'] === 'critical' ? 'var(--octg-critical)' : '#92400e' ?>; margin-bottom:8px;">
            <span class="octg-badge <?= htmlspecialchars((string)$issue['severity']) ?>"><?= htmlspecialchars((string)$issue['severity']) ?></span>
            <?= htmlspecialchars((string)$issue['message']) ?>
            <?php if (!(bool) $issue['is_resolved']): ?>
              <form method="post" action="/audits/<?= (int) $audit['id'] ?>/requirements/<?= (int) $requirement['id'] ?>/issues/<?= (int) $issue['id'] ?>/resolve" style="display:inline; margin-left:8px;">
                <?= $csrfField ?>
                <button type="submit" class="linklike" style="color:inherit; text-decoration:underline; background:none; border:none; cursor:pointer; font-size:12px;">Mark reviewed</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="octg-muted" style="margin-top:10px;">No validation issues on the latest collection.</p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php foreach ($renderedPrompts as $entry): ?>
  <div class="octg-card">
    <h3><?= htmlspecialchars((string)$entry['template']['title']) ?></h3>
    <p class="octg-muted"><?= htmlspecialchars((string)$entry['template']['purpose']) ?></p>
    <p class="octg-muted">Paste this into <strong><?= htmlspecialchars((string)$entry['template']['target_surface']) ?></strong>, then paste its reply below.</p>
    <pre class="mi-prompt-box" id="prompt-text-<?= (int) $entry['template']['id'] ?>"><?= htmlspecialchars((string)$entry['rendered_text']) ?></pre>
    <button type="button" class="octg-btn secondary" onclick="navigator.clipboard.writeText(document.getElementById('prompt-text-<?= (int) $entry['template']['id'] ?>').textContent)">Copy prompt</button>
  </div>
<?php endforeach; ?>

<div class="octg-card">
  <h3>Paste the AI's response</h3>
  <form method="post" action="/audits/<?= (int) $audit['id'] ?>/requirements/<?= (int) $requirement['id'] ?>/collect-text">
    <?= $csrfField ?>
    <input type="hidden" name="method" value="ai_assistant">
    <div class="octg-field">
      <textarea class="octg-input" name="response_text" rows="10" placeholder="Paste the assistant's reply here..."></textarea>
    </div>
    <button type="submit" class="octg-btn">Submit response</button>
  </form>
</div>

<?php if ($uploadTemplates !== []): ?>
  <div class="octg-card">
    <h3>Or upload a file instead</h3>
    <?php foreach ($uploadTemplates as $template): ?>
      <p class="octg-muted"><?= htmlspecialchars((string)$template['description'] ?? '') ?>
        (accepts: <?= htmlspecialchars((string)$template['accepted_formats']) ?>)</p>
    <?php endforeach; ?>
    <form method="post" action="/audits/<?= (int) $audit['id'] ?>/requirements/<?= (int) $requirement['id'] ?>/collect-file" enctype="multipart/form-data">
      <?= $csrfField ?>
      <div class="octg-field">
        <input type="file" name="upload" accept=".csv,.xlsx,.xls,.pdf">
      </div>
      <button type="submit" class="octg-btn secondary">Upload</button>
    </form>
  </div>
<?php endif; ?>

<?php if ($history !== []): ?>
  <div class="octg-card">
    <h3>Collection history</h3>
    <table class="octg-table">
      <thead><tr><th>When</th><th>Method</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach (array_reverse($history) as $attempt): ?>
        <tr>
          <td class="octg-muted"><?= htmlspecialchars((string)$attempt['created_at']) ?></td>
          <td><?= htmlspecialchars((string)str_replace('_', ' ', $attempt['method'])) ?></td>
          <td><span class="mi-status-pill <?= htmlspecialchars((string)$attempt['status']) ?>"><?= htmlspecialchars((string)$attempt['status']) ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php  ?>

