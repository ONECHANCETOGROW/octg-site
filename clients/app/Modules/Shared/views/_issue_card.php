<?php
/**
 * Shared issue explanation card — used by Pages/views/show.php and
 * Reporting/views/report.php so the "why it matters / how to fix / example
 * / expected impact" structure only exists in one place.
 *
 * Expected variables in scope before including this file:
 * @var array<string,mixed> $issue          one row from IssueRepository
 * @var array<string,mixed>|null $rule      matching row from RuleRepository::allKeyedByCode(), or null
 * @var bool $openByDefault                 whether the <details> starts expanded
 */

use App\Core\View;

$severity = (string) $issue['severity'];
$rule ??= null;
$openByDefault ??= false;
?>
<details class="octg-issue <?= View::e($severity) ?>" <?= $openByDefault ? 'open' : '' ?>>
  <summary>
    <span class="octg-badge <?= View::e($severity) ?>"><?= octg_severity_label($severity) ?></span>
    <span class="octg-issue-title"><?= View::e($issue['title']) ?></span>
  </summary>
  <div class="octg-issue-body">
    <div class="octg-issue-section">
      <h4>What we found</h4>
      <p><?= View::e($issue['description']) ?></p>
    </div>

    <?php if ($rule !== null && !empty($rule['why_it_matters'])): ?>
    <div class="octg-issue-section">
      <h4>Why it matters</h4>
      <p><?= View::e($rule['why_it_matters']) ?></p>
    </div>
    <?php endif; ?>

    <div class="octg-issue-section">
      <h4>How to fix it</h4>
      <p><?= View::e($issue['recommendation']) ?></p>
    </div>

    <?php if ($rule !== null && !empty($rule['example_fix'])): ?>
    <div class="octg-issue-section">
      <h4>Example of a correct implementation</h4>
      <pre class="octg-issue-example"><?= View::e($rule['example_fix']) ?></pre>
    </div>
    <?php endif; ?>

    <?php if ($rule !== null && !empty($rule['expected_impact'])): ?>
    <div class="octg-issue-section">
      <h4>Expected impact after fixing</h4>
      <p class="octg-issue-impact"><?= View::e($rule['expected_impact']) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($rule !== null && !empty($rule['reference_url'])): ?>
    <div class="octg-issue-section">
      <a href="<?= View::e($rule['reference_url']) ?>" target="_blank" rel="noopener">Google's guidance on this →</a>
    </div>
    <?php endif; ?>
  </div>
</details>
