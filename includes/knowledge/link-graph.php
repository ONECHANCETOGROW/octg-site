<?php
/* ==========================================================================
   LINK-GRAPH.PHP — reads the real link graph persisted by the Website
   Optimizer's most recent crawl (admin/cron/run-audit.php now writes every
   internal link it discovers into page_links — see that file). This
   module does not crawl anything itself; it only reads what the optimizer
   already found, which is the correct integration point rather than
   re-crawling.
   ========================================================================== */

function octg_get_link_graph_stats(?PDO $pdo): array {
    if (!$pdo) {
        return ['available' => false, 'reason' => 'Database not connected.'];
    }

    try {
        $latestReport = $pdo->query('SELECT id, started_at FROM audit_reports ORDER BY started_at DESC LIMIT 1')->fetch();
        if (!$latestReport) {
            return ['available' => false, 'reason' => 'No audit has run yet — the link graph comes from the Website Optimizer\'s crawl.'];
        }
        $reportId = $latestReport['id'];

        $links = $pdo->prepare('SELECT source_url, target_url FROM page_links WHERE report_id = :rid');
        $links->execute([':rid' => $reportId]);
        $rows = $links->fetchAll();

        if (!$rows) {
            return ['available' => false, 'reason' => 'The most recent audit (report #' . $reportId . ') did not persist link data — re-run an audit after updating to this version.'];
        }

        $inboundCount = [];
        $outboundCount = [];
        foreach ($rows as $r) {
            $inboundCount[$r['target_url']] = ($inboundCount[$r['target_url']] ?? 0) + 1;
            $outboundCount[$r['source_url']] = ($outboundCount[$r['source_url']] ?? 0) + 1;
        }

        $allCrawledUrls = $pdo->prepare('SELECT url FROM audit_page_scores WHERE report_id = :rid');
        $allCrawledUrls->execute([':rid' => $reportId]);
        $allUrls = array_column($allCrawledUrls->fetchAll(), 'url');

        $orphans = [];
        foreach ($allUrls as $url) {
            if (empty($inboundCount[$url])) $orphans[] = $url;
        }

        arsort($inboundCount);
        $mostLinked = array_slice($inboundCount, 0, 10, true);
        asort($inboundCount);
        $leastLinked = array_slice(array_diff_key($inboundCount, array_flip($orphans)), 0, 10, true);

        return [
            'available' => true, 'report_id' => $reportId, 'crawled_at' => $latestReport['started_at'],
            'total_pages' => count($allUrls), 'total_links' => count($rows),
            'most_linked' => $mostLinked, 'least_linked' => $leastLinked, 'orphan_pages' => $orphans,
        ];
    } catch (Throwable $e) {
        return ['available' => false, 'reason' => 'page_links table not found — import sql/010_knowledge_engine.sql.'];
    }
}
