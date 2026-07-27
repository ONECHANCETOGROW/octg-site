<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel;

/**
 * In-memory dependency graph over a set of requirements for one audit —
 * built fresh per cockpit render from RequirementRepository::dependenciesFor().
 * Implements RNS spec §8: two edge types (`blocks` hard-sequencing,
 * `enriches` soft data-quality), plus the "recommend missing prompt" logic
 * (greedy: prioritize whichever unresolved requirement unblocks the most
 * other unresolved requirements).
 */
final class DependencyGraph
{
    /**
     * @param array<int,array{requirement_id:int,depends_on_requirement_id:int,edge_type:string}> $edges
     * @param array<int,bool> $satisfiedByRequirementId
     */
    public function __construct(
        private readonly array $edges,
        private readonly array $satisfiedByRequirementId
    ) {
    }

    /**
     * True if a requirement has at least one unsatisfied `blocks`
     * prerequisite — the cockpit uses this to de-prioritize/disable a card.
     */
    public function isBlocked(int $requirementId): bool
    {
        foreach ($this->edges as $edge) {
            if ($edge['requirement_id'] !== $requirementId || $edge['edge_type'] !== 'blocks') {
                continue;
            }

            $prerequisiteSatisfied = $this->satisfiedByRequirementId[$edge['depends_on_requirement_id']] ?? false;
            if (!$prerequisiteSatisfied) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int,int> requirement IDs this one is waiting on
     */
    public function blockingPrerequisites(int $requirementId): array
    {
        $prerequisites = [];

        foreach ($this->edges as $edge) {
            if ($edge['requirement_id'] !== $requirementId || $edge['edge_type'] !== 'blocks') {
                continue;
            }

            if (!($this->satisfiedByRequirementId[$edge['depends_on_requirement_id']] ?? false)) {
                $prerequisites[] = $edge['depends_on_requirement_id'];
            }
        }

        return $prerequisites;
    }

    /**
     * True if a requirement has at least one unsatisfied `enriches`
     * dependency — used to show "confidence capped pending X" messaging
     * rather than blocking collection outright.
     */
    public function isConfidenceCapped(int $requirementId): bool
    {
        foreach ($this->edges as $edge) {
            if ($edge['requirement_id'] !== $requirementId || $edge['edge_type'] !== 'enriches') {
                continue;
            }

            if (!($this->satisfiedByRequirementId[$edge['depends_on_requirement_id']] ?? false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Among all not-yet-satisfied, not-currently-blocked requirement IDs,
     * returns the one that is itself the prerequisite for the largest
     * number of other not-yet-satisfied requirements — the single action
     * that unblocks the most subsequent progress (RNS spec §8's greedy
     * graph-centrality recommendation).
     *
     * @param array<int,int> $candidateRequirementIds
     */
    public function recommendNext(array $candidateRequirementIds): ?int
    {
        $unblockedCandidates = array_filter(
            $candidateRequirementIds,
            fn (int $id): bool => !$this->isBlocked($id) && !($this->satisfiedByRequirementId[$id] ?? false)
        );

        if ($unblockedCandidates === []) {
            return null;
        }

        $downstreamCount = [];
        foreach ($unblockedCandidates as $candidateId) {
            $downstreamCount[$candidateId] = 0;
        }

        foreach ($this->edges as $edge) {
            $prerequisite = $edge['depends_on_requirement_id'];
            if (!isset($downstreamCount[$prerequisite])) {
                continue;
            }

            $dependentSatisfied = $this->satisfiedByRequirementId[$edge['requirement_id']] ?? false;
            if (!$dependentSatisfied) {
                $downstreamCount[$prerequisite]++;
            }
        }

        arsort($downstreamCount);
        $topId = array_key_first($downstreamCount);

        return $topId === null ? null : (int) $topId;
    }
}
