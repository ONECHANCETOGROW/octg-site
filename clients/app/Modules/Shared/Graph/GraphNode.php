<?php

declare(strict_types=1);

namespace App\Modules\Shared\Graph;

/**
 * One page as a node in the site graph — see SiteGraphBuilder. Immutable
 * snapshot built from one crawl's `pages` + `page_metrics` rows.
 *
 * Lives under Shared/ (not a single feature module) because it is used by
 * both the Crawler module (to build the rule engine's SiteIndex) and the
 * Intelligence module (structure/cluster/visualization analysis) — see
 * SYSTEM_ARCHITECTURE.md §9.
 */
final class GraphNode
{
    public function __construct(
        public readonly int $pageId,
        public readonly string $url,
        public readonly string $pageType,
        public readonly int $httpStatus,
        public readonly bool $isIndexable,
        public readonly ?string $contentHash,
        public readonly ?string $title,
        public readonly int $inboundInternalLinks,
        public readonly int $outboundInternalLinks,
        public readonly ?int $depth
    ) {
    }

    public function isOrphan(): bool
    {
        return $this->pageType !== 'home' && $this->inboundInternalLinks === 0;
    }

    public function isDeadEnd(): bool
    {
        return $this->outboundInternalLinks === 0;
    }

    public function isUnreachableFromHome(): bool
    {
        return $this->depth === null;
    }
}
