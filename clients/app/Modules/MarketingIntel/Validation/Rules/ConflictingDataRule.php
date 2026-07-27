<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Validation\Rules;

use App\Modules\MarketingIntel\Parsing\ParsedPayload;
use App\Modules\MarketingIntel\Validation\ValidationContext;
use App\Modules\MarketingIntel\Validation\ValidationFinding;
use App\Modules\MarketingIntel\Validation\ValidationRuleInterface;

/**
 * Layer 4 (RNS spec §9): within one row, do the numbers imply each other
 * correctly? If Spend, Clicks, and CPC are all present, spend / clicks
 * should roughly equal the reported CPC — a mismatch beyond a reasonable
 * rounding tolerance is a strong hallucination signal, since it means the
 * AI's own numbers don't multiply out.
 */
final class ConflictingDataRule implements ValidationRuleInterface
{
    private const TOLERANCE_PERCENT = 15.0;

    public function code(): string
    {
        return 'conflicting_data';
    }

    public function evaluate(ParsedPayload $payload, ValidationContext $context): array
    {
        $findings = [];

        foreach ($payload->rows as $rowIndex => $row) {
            $spend = $this->findNumeric($row, '/^(spend|cost|ad spend)$/i');
            $clicks = $this->findNumeric($row, '/^clicks?$/i');
            $cpc = $this->findNumeric($row, '/^(cpc|cost.?per.?click|avg\.?\s*cpc)$/i');

            if ($spend === null || $clicks === null || $cpc === null || $clicks <= 0.0) {
                continue;
            }

            $impliedCpc = $spend / $clicks;
            $diffPercent = $cpc > 0
                ? abs($impliedCpc - $cpc) / $cpc * 100
                : 100.0;

            if ($diffPercent > self::TOLERANCE_PERCENT) {
                $findings[] = new ValidationFinding(
                    'warning',
                    'conflicting_data',
                    'cpc',
                    "Row " . ($rowIndex + 1) . ": reported CPC ({$cpc}) doesn't match Spend ÷ Clicks "
                        . "(" . round($impliedCpc, 2) . ") within a reasonable tolerance — the response's own "
                        . "figures are inconsistent with each other."
                );
            }
        }

        return $findings;
    }

    /**
     * @param array<string,string> $row
     */
    private function findNumeric(array $row, string $columnPattern): ?float
    {
        foreach ($row as $column => $value) {
            if (preg_match($columnPattern, trim($column)) === 1) {
                $cleaned = trim(str_replace(['$', ',', '%', '€', '£'], '', $value));
                if ($cleaned !== '' && is_numeric($cleaned)) {
                    return (float) $cleaned;
                }
            }
        }

        return null;
    }
}
