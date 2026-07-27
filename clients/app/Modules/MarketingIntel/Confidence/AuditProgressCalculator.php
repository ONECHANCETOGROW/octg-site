<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Confidence;

/**
 * Completeness + reachable report tier — the other half of RNS spec §16's
 * "never conflate confidence with completeness" guardrail. This class only
 * counts what has and hasn't been collected; it never looks at how
 * trustworthy the collected data is (that's ConfidenceCalculator's job).
 *
 * Tiers, per RNS spec §14 (replacing the original brief's block/don't-block
 * binary):
 *   - none:        nothing collected yet.
 *   - preliminary: at least one requirement collected, but required ones
 *                  are still missing.
 *   - standard:    every required requirement satisfied.
 *   - complete:    every required AND optional requirement satisfied, at a
 *                  healthy overall confidence.
 */
final class AuditProgressCalculator
{
    private const COMPLETE_TIER_MIN_CONFIDENCE = 75;

    /**
     * @param array<int,array{is_required:bool,is_satisfied:bool}> $requirements
     */
    public function completenessPercent(array $requirements): int
    {
        if ($requirements === []) {
            return 0;
        }

        $required = array_filter($requirements, static fn (array $r): bool => $r['is_required']);
        $optional = array_filter($requirements, static fn (array $r): bool => !$r['is_required']);

        $requiredScore = $this->satisfiedFraction($required);
        $optionalScore = $this->satisfiedFraction($optional);

        // Required completion is weighted more heavily (70/30) — an audit
        // that's finished every required item but skipped every optional
        // one should read as "mostly done," not "half done."
        if ($required === [] && $optional === []) {
            return 0;
        }

        if ($required === []) {
            return (int) round($optionalScore * 100);
        }

        if ($optional === []) {
            return (int) round($requiredScore * 100);
        }

        return (int) round((($requiredScore * 0.7) + ($optionalScore * 0.3)) * 100);
    }

    /**
     * @param array<int,array{is_required:bool,is_satisfied:bool}> $requirements
     */
    public function reachableTier(array $requirements, int $overallConfidence): string
    {
        if ($requirements === []) {
            return 'none';
        }

        $anySatisfied = false;
        $allRequiredSatisfied = true;
        $allSatisfied = true;

        foreach ($requirements as $requirement) {
            if ($requirement['is_satisfied']) {
                $anySatisfied = true;
            } else {
                $allSatisfied = false;
                if ($requirement['is_required']) {
                    $allRequiredSatisfied = false;
                }
            }
        }

        if ($allSatisfied && $overallConfidence >= self::COMPLETE_TIER_MIN_CONFIDENCE) {
            return 'complete';
        }

        if ($allRequiredSatisfied) {
            return 'standard';
        }

        if ($anySatisfied) {
            return 'preliminary';
        }

        return 'none';
    }

    /**
     * @param array<int,array{is_required:bool,is_satisfied:bool}> $subset
     */
    private function satisfiedFraction(array $subset): float
    {
        if ($subset === []) {
            return 1.0;
        }

        $satisfied = count(array_filter($subset, static fn (array $r): bool => $r['is_satisfied']));

        return $satisfied / count($subset);
    }
}
