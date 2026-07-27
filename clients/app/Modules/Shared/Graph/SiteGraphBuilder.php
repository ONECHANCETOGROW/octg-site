<?php

declare(strict_types=1);

namespace App\Modules\Shared\Graph;

/**
 * Builds the whole-site link graph for one crawl from already-fetched
 * `pages` rows + `page_metrics` data — never touches the database or the
 * network itself (see WEBSITE_INTELLIGENCE.md §1: "never crawl directly,
 * only analyze existing crawl data").
 *
 * This was originally inline logic in CrawlManager::buildSiteIndex(). It
 * was extracted here in Session 3 so the same graph-construction code
 * serves both the crawler's SiteIndex (used by the rule engine) and every
 * Website Intelligence Engine analyzer, instead of two copies drifting
 * apart — see CrawlManager::buildSiteIndex(), which now delegates here.
 *
 * O(n) in page count plus O(e) in internal-link count: one pass to index
 * URLs, one pass to build adjacency + in/out-degree, one BFS for depth.
 * No N+1 queries — the caller already has all rows in memory.
 */
final class SiteGraphBuilder
{
    /**
     * @param array<int,array<string,mixed>> $pageRows one crawl's `pages` rows
     * @param array<int,array<string,mixed>> $metricsByPage page_id => decoded page_metrics data
     */
    public static function build(array $pageRows, array $metricsByPage): SiteGraph
    {
        $urlToPageId = [];
        foreach ($pageRows as $pageRow) {
            $urlToPageId[(string) $pageRow['url']] = (int) $pageRow['id'];
        }

        $outbound = [];
        $inbound = [];
        foreach ($pageRows as $pageRow) {
            $pageId = (int) $pageRow['id'];
            $outbound[$pageId] = [];
            $inbound[$pageId] = [];
        }

        foreach ($pageRows as $pageRow) {
            $pageId = (int) $pageRow['id'];
            $metrics = $metricsByPage[$pageId] ?? [];

            foreach ($metrics['links'] ?? [] as $link) {
                if (!($link['is_internal'] ?? false)) {
                    continue;
                }

                $targetId = $urlToPageId[$link['href']] ?? null;
                if ($targetId === null || $targetId === $pageId) {
                    continue;
                }

                // A page can link to the same target more than once (nav +
                // body copy, for example) — de-duplicate the edge so
                // in/out-degree reflects distinct linked pages, not raw
                // anchor count.
                if (!in_array($targetId, $outbound[$pageId], true)) {
                    $outbound[$pageId][] = $targetId;
                }
                if (!in_array($pageId, $inbound[$targetId], true)) {
                    $inbound[$targetId][] = $pageId;
                }
            }
        }

        $homePageId = null;
        foreach ($pageRows as $pageRow) {
            if ($pageRow['page_type'] === 'home') {
                $homePageId = (int) $pageRow['id'];
                break;
            }
        }

        $depths = self::breadthFirstDepths($homePageId, $outbound);

        $nodes = [];
        foreach ($pageRows as $pageRow) {
            $pageId = (int) $pageRow['id'];
            $nodes[$pageId] = new GraphNode(
                pageId: $pageId,
                url: (string) $pageRow['url'],
                pageType: (string) $pageRow['page_type'],
                httpStatus: (int) $pageRow['http_status'],
                isIndexable: (bool) $pageRow['is_indexable'],
                contentHash: $pageRow['content_hash'] ?? null,
                title: $pageRow['title'] ?? null,
                inboundInternalLinks: count($inbound[$pageId] ?? []),
                outboundInternalLinks: count($outbound[$pageId] ?? []),
                depth: $depths[$pageId] ?? null
            );
        }

        return new SiteGraph($nodes, $outbound, $inbound, $homePageId);
    }

    /**
     * @param array<int,array<int,int>> $outbound
     * @return array<int,int> pageId => shortest hop count from the homepage
     */
    private static function breadthFirstDepths(?int $homePageId, array $outbound): array
    {
        if ($homePageId === null) {
            return [];
        }

        $depths = [$homePageId => 0];
        $queue = [$homePageId];

        while ($queue !== []) {
            $current = array_shift($queue);
            $currentDepth = $depths[$current];

            foreach ($outbound[$current] ?? [] as $neighbor) {
                if (isset($depths[$neighbor])) {
                    continue;
                }
                $depths[$neighbor] = $currentDepth + 1;
                $queue[] = $neighbor;
            }
        }

        return $depths;
    }
}
