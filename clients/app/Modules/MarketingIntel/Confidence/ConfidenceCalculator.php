<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Confidence;

use App\Modules\MarketingIntel\Parsing\ParsedPayload;
use App\Modules\MarketingIntel\Validation\ValidationEngine;
use App\Modules\MarketingIntel\Validation\ValidationFinding;

/**
 * The four-level confidence roll-up from RNS spec §16: field -> requirement
 * -> category -> audit. Deliberately kept separate from *completeness*
 * (how much has been collected) — this class only ever answers "how much do
 * we trust what we have," never "how much do we have." Completeness lives
 * in AuditProgressCalculator. Conflating the two was explicitly flagged as a
 * risk in the approved spec (§2, §16) and both this class and the UI layer
 * must keep them visually and semantically distinct.
 */
final class ConfidenceCalculator
{
    public function __construct(private readonly ValidationEngine $validationEngine)
    {
    }

    /**
     * Field-level roll-up into one "how well did this specific response
     * parse" number, before validation findings are applied.
     */
    public function extractionBaseConfidence(ParsedPayload $payload): int
    {
        if ($payload->fieldConfidence === []) {
            return 0;
        }

        $sum = array_sum($payload->fieldConfidence);
        $count = count($payload->fieldConfidence);

        return (int) round($sum / $count);
    }

    /**
     * Requirement-level confidence: the extraction's base confidence, minus
     * whatever the validation findings for this specific response earned as
     * a deduction. This is what gets stored on the ParsedExtraction row and
     * shown as the requirement's confidence badge in the cockpit.
     *
     * @param array<int,ValidationFinding> $findings
     */
    public function requirementConfidence(int $extractionBaseConfidence, array $findings): int
    {
        $deduction = $this->validationEngine->confidenceDeduction($findings);

        return max(0, min(100, $extractionBaseConfidence - $deduction));
    }

    /**
     * Category-level confidence: weighted average of the requirements
     * currently satisfied within one category, weighted by each
     * requirement's confidence_weight (not every requirement matters
     * equally to the eventual downstream analysis).
     *
     * @param array<int,array{confidence:int,weight:int}> $requirementConfidences
     */
    public function categoryConfidence(array $requirementConfidences): int
    {
        return $this->weightedAverage($requirementConfidences);
    }

    /**
     * Audit-level confidence: the same weighted-average roll-up, applied
     * across every satisfied requirement in the audit regardless of
     * category. Requirements that haven't been collected yet are excluded
     * here on purpose — they affect completeness, not confidence; an
     * audit with three collected, high-confidence requirements out of
     * twenty total should show high confidence and low completeness, two
     * different, independently-true statements.
     *
     * @param array<int,array{confidence:int,weight:int}> $allSatisfiedRequirementConfidences
     */
    public function auditConfidence(array $allSatisfiedRequirementConfidences): int
    {
        return $this->weightedAverage($allSatisfiedRequirementConfidences);
    }

    /**
     * @param array<int,array{confidence:int,weight:int}> $items
     */
    private function weightedAverage(array $items): int
    {
        if ($items === []) {
            return 0;
        }

        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($items as $item) {
            $weight = max(1, $item['weight']);
            $weightedSum += $item['confidence'] * $weight;
            $totalWeight += $weight;
        }

        if ($totalWeight === 0) {
            return 0;
        }

        return (int) round($weightedSum / $totalWeight);
    }
}
