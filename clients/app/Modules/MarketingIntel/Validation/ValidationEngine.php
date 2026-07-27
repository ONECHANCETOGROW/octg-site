<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Validation;

use App\Modules\MarketingIntel\Parsing\ParsedPayload;
use App\Modules\MarketingIntel\Validation\Rules\ConflictingDataRule;
use App\Modules\MarketingIntel\Validation\Rules\HedgingLanguageRule;
use App\Modules\MarketingIntel\Validation\Rules\MissingMetricRule;
use App\Modules\MarketingIntel\Validation\Rules\NameGroundingRule;
use App\Modules\MarketingIntel\Validation\Rules\OutOfRangeRule;
use App\Modules\MarketingIntel\Validation\Rules\StructuralFormatRule;

/**
 * Runs every ValidationRule against a ParsedPayload, in the order specified
 * by RNS spec §9 (structural first, hallucination heuristics last, since
 * later layers assume earlier ones already passed some minimum bar).
 * Persistence of the resulting findings is the caller's job (see
 * CollectionController) — this class is a pure function over its inputs,
 * same separation SeoEngine\RuleEngine already uses.
 */
final class ValidationEngine
{
    /**
     * @return array<int,ValidationRuleInterface>
     */
    public function rules(): array
    {
        return [
            new StructuralFormatRule(),
            new MissingMetricRule(),
            new OutOfRangeRule(),
            new ConflictingDataRule(),
            new HedgingLanguageRule(),
            new NameGroundingRule(),
        ];
    }

    /**
     * @return array<int,ValidationFinding>
     */
    public function evaluate(ParsedPayload $payload, ValidationContext $context): array
    {
        $findings = [];

        foreach ($this->rules() as $rule) {
            foreach ($rule->evaluate($payload, $context) as $finding) {
                $findings[] = $finding;
            }
        }

        return $findings;
    }

    /**
     * Confidence deduction implied by a set of findings — feeds
     * ConfidenceCalculator (RNS spec §16) rather than each rule reasoning
     * about confidence itself, keeping "what's wrong" (rules) separate from
     * "how much should this move the confidence number" (this calculation).
     *
     * @param array<int,ValidationFinding> $findings
     */
    public function confidenceDeduction(array $findings): int
    {
        $deduction = 0;

        foreach ($findings as $finding) {
            $deduction += match ($finding->severity) {
                'critical' => 25,
                'warning' => 10,
                'notice' => 3,
                default => 0,
            };
        }

        return min(100, $deduction);
    }
}
