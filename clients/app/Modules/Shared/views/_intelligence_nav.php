<?php
/**
 * Shared tab nav for the Website Intelligence Engine pages — one place so
 * the five destinations stay in sync instead of five templates drifting.
 *
 * @var array<string,mixed> $website
 * @var string $activeTab one of: pages, action-center, overview, clusters, structure, visualization
 */

use App\Core\View;

$id = (int) $website['id'];
$tabs = [
    'pages' => ['label' => 'Pages', 'href' => "/websites/{$id}/pages"],
    'action-center' => ['label' => 'Action Center', 'href' => "/websites/{$id}/action-center"],
    'overview' => ['label' => 'Website Health', 'href' => "/websites/{$id}/intelligence/overview"],
    'clusters' => ['label' => 'Content Clusters', 'href' => "/websites/{$id}/intelligence/clusters"],
    'structure' => ['label' => 'Structure & Relationships', 'href' => "/websites/{$id}/intelligence/structure"],
    'visualization' => ['label' => 'Visualization', 'href' => "/websites/{$id}/intelligence/visualization"],
];
?>
<p>
  <a href="/websites/<?= $id ?>">← Back to website</a>
</p>
<div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom: 18px;">
  <?php foreach ($tabs as $key => $tab): ?>
    <a class="octg-btn <?= $key === $activeTab ? '' : 'secondary' ?>" href="<?= View::e($tab['href']) ?>"><?= View::e($tab['label']) ?></a>
  <?php endforeach; ?>
</div>
