<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Validation\Rules;

use App\Modules\MarketingIntel\Parsing\ParsedPayload;
use App\Modules\MarketingIntel\Validation\ValidationContext;
use App\Modules\MarketingIntel\Validation\ValidationFinding;
use App\Modules\MarketingIntel\Validation\ValidationRuleInterface;

/**
 * Layer 2 (RNS spec §9): are the fields the PromptTemplate/UploadTemplate
 * asked for actually present? Only runs when the payload has *some*
 * structure (StructuralFormatRule already covers the "nothing at all"
 * case) — this catches the partial case, e.g. a table with Campaign/Spend/
 * Clicks but no Conversions column at all.
 */
final class MissingMetricRule implements ValidationRuleInterface
{
    public function code(): string
    {
        return 'missing_metric';
    }

    public function evaluate(ParsedPayload $payload, ValidationContext $context): array
    {
        if ($payload->isEmpty() || $context->expectedColumns === []) {
            return [];
        }

        $detected = array_map(
            static fn (string $c): string => strtolower(trim($c)),
            $payload->columnsDetected
        );

        $findings = [];
        foreach ($context->expectedColumns as $expected) {
            $normalized = strtolower(trim($expected));
            if (!in_array($normalized, $detected, true)) {
                $findings[] = new ValidationFinding(
                    'warning',
                    'missing_metric',
                    $expected,
                    "Expected column \"{$expected}\" wasn't found in the response — ask the assistant to "
                        . "include it, or accept this collection with reduced confidence."
                );
            }
        }

        return $findings;
    }
}
