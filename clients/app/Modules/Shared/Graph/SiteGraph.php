<?php

declare(strict_types=1);

namespace App\Modules\Shared\Graph;

/**
 * The whole-site link graph for one crawl. Built once by SiteGraphBuilder
 * and shared by both the crawler's own SiteIndex (rule-engine cross-page
 * checks) and every Website Intelligence Engine analyzer — see
 * SYSTEM_ARCHITECTURE.md §9 for why this exists as one shared component
 * instead of being computed twice.
 */
final class SiteGraph
{
    /**
     * @param array<int,GraphNode> $nodes pageId => node
     * @param array<int,array<int,int>> $outbound pageId => [targetPageId, ...]
     * @param array<int,array<int,int>> $inbound pageId => [sourcePageId, ...]
     */
    public function __construct(
        private readonly array $nodes,
        private readonly array $outbound,
        private readonly array $inbound,
        private readonly ?int $homePageId
    ) {
    }

    /**
     * @return array<int,GraphNode>
     */
    public function nodes(): array
    {
        return $this->nodes;
    }

    public function node(int $pageId): ?GraphNode
    {
        return $this->nodes[$pageId] ?? null;
    }

    public function homePageId(): ?int
    {
        return $this->homePageId;
    }

    /**
     * @return array<int,int>
     */
    public function outboundOf(int $pageId): array
    {
        return $this->outbound[$pageId] ?? [];
    }

    /**
     * @return array<int,int>
     */
    public function inboundOf(int $pageId): array
    {
        return $this->inbound[$pageId] ?? [];
    }

    public function count(): int
    {
        return count($this->nodes);
    }

    /**
     * Nodes with zero inbound internal links (excluding the homepage,
     * which by definition needs none).
     *
     * @return array<int,GraphNode>
     */
    public function orphanNodes(): array
    {
        return array_values(array_filter($this->nodes, static fn (GraphNode $n): bool => $n->isOrphan()));
    }

    /**
     * Nodes with zero outbound internal links — a visitor who lands here
     * has nowhere else on the site to go.
     *
     * @return array<int,GraphNode>
     */
    public function deadEndNodes(): array
    {
        return array_values(array_filter($this->nodes, static fn (GraphNode $n): bool => $n->isDeadEnd()));
    }

    /**
     * Nodes with no path from the homepage through internal links at all
     * (only ever discovered via sitemap/robots — a stronger signal than
     * a plain orphan, which just means "not linked *to*" but might still
     * be reachable via some other path).
     *
     * @return array<int,GraphNode>
     */
    public function unreachableNodes(): array
    {
        return array_values(array_filter($this->nodes, static fn (GraphNode $n): bool => $n->isUnreachableFromHome()));
    }

    public function maxDepth(): int
    {
        $max = 0;
        foreach ($this->nodes as $node) {
            if ($node->depth !== null && $node->depth > $max) {
                $max = $node->depth;
            }
        }

        return $max;
    }
}
