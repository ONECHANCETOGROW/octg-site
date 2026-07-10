<?php
/* ==========================================================================
   FRESHNESS.PHP — tracks content freshness using real filesystem
   modification timestamps (filemtime()) on the actual catalog files —
   genuinely measured data, not invented "last reviewed" dates. This is an
   honest proxy, not a perfect one: it reflects when the FILE last changed
   on disk, not necessarily when a human last read and confirmed the
   content is still accurate. A true "last reviewed by a person" field
   would need to be added as real content moves to database-backed editing
   (see the Admin Panel roadmap) — noted here rather than faked.
   ========================================================================== */

function octg_check_content_freshness(int $staleDaysThreshold = 90): array {
    $files = [
        'Services catalog' => __DIR__ . '/../../data/services-catalog.php',
        'Products catalog' => __DIR__ . '/../../data/products-catalog.php',
        'Resources catalog' => __DIR__ . '/../../data/resources-catalog.php',
        'Case studies catalog' => __DIR__ . '/../../data/case-studies-catalog.php',
        'Team catalog' => __DIR__ . '/../../data/team-catalog.php',
        'Industries page' => __DIR__ . '/../../industries.php',
        'Homepage' => __DIR__ . '/../../index.php',
        'About page' => __DIR__ . '/../../about.php',
        'Legal content' => __DIR__ . '/../../data/legal-content.php',
    ];

    $results = [];
    $now = time();
    foreach ($files as $label => $path) {
        if (!file_exists($path)) continue;
        $mtime = filemtime($path);
        $ageDays = (int) floor(($now - $mtime) / 86400);
        $results[] = [
            'label' => $label,
            'path' => str_replace(__DIR__ . '/../../', '', $path),
            'last_modified' => date('Y-m-d H:i:s', $mtime),
            'age_days' => $ageDays,
            'is_stale' => $ageDays > $staleDaysThreshold,
        ];
    }

    usort($results, fn($a, $b) => $b['age_days'] <=> $a['age_days']);
    return $results;
}
