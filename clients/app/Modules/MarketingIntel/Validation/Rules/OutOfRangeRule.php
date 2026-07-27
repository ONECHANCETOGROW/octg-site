<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel\Validation\Rules;

use App\Modules\MarketingIntel\Parsing\ParsedPayload;
use App\Modules\MarketingIntel\Validation\ValidationContext;
use App\Modules\MarketingIntel\Validation\ValidationFinding;
use App\Modules\MarketingIntel\Validation\ValidationRuleInterface;

/**
 * Layer 3 (RNS spec §9): deterministic range/sanity checks, independent of
 * *why* a value might be wrong. Field names are matched by pattern rather
 * than an exact whitelist, since AI-relayed and uploaded data can label the
 * same metric slightly differently ("CTR", "Click-Through Rate", "Clickthru %").
 */
final class OutOfRangeRule implements ValidationRuleInterface
{
    private const NON_NEGATIVE_PATTERN = '/spend|cost|clicks?|conversions?|impressions?|budget/i';

    private const PERCENTAGE_PATTERN = '/ctr|click.?through|conversion rate|impr(ession)?\.?\s*share/i';

    public function code(): string
    {
        return 'out_of_range';
    }

    public function evaluate(ParsedPayload $payload, ValidationContext $context): array
    {
        $findings = [];

        foreach ($payload->rows as $rowIndex => $row) {
            foreach ($row as $column => $rawValue) {
                $number = $this->toNumber($rawValue);
                if ($number === null) {
                    continue;
                }

                if (preg_match(self::PERCENTAGE_PATTERN, $column) === 1 && ($number < 0 || $number > 100)) {
                    $findings[] = new ValidationFinding(
                        'warning',
                        'out_of_range',
                        $column,
                        "Row " . ($rowIndex + 1) . ": \"{$column}\" is {$number}%, which is outside the "
                            . "plausible 0-100% range for this kind of metric."
                    );
                    continue;
                }

                if (preg_match(self::NON_NEGATIVE_PATTERN, $column) === 1 && $number < 0) {
                    $findings[] = new ValidationFinding(
                        'critical',
                        'out_of_range',
                        $column,
                        "Row " . ($rowIndex + 1) . ": \"{$column}\" is negative ({$number}), which isn't "
                            . "possible for this metric."
                    );
                }
            }

            $conversionsAndClicks = $this->conversionsExceedClicks($row);
            if ($conversionsAndClicks !== null) {
                $findings[] = new ValidationFinding(
                    'critical',
                    'conflicting_data',
                    'conversions',
                    "Row " . ($rowIndex + 1) . ": conversions ({$conversionsAndClicks['conversions']}) "
                        . "exceed clicks ({$conversionsAndClicks['clicks']}), which isn't possible."
                );
            }
        }

        return $findings;
    }

    /**
     * @param array<string,string> $row
     * @return array{clicks:float,conversions:float}|null
     */
    private function conversionsExceedClicks(array $row): ?array
    {
        $clicks = null;
        $conversions = null;

        foreach ($row as $column => $value) {
            if ($clicks === null && preg_match('/^clicks?$/i', trim($column)) === 1) {
                $clicks = $this->toNumber($value);
            }

            if ($conversions === null && preg_match('/^conversions?$/i', trim($column)) === 1) {
                $conversions = $this->toNumber($value);
            }
        }

        if ($clicks === null || $conversions === null) {
            return null;
        }

        return $conversions > $clicks ? ['clicks' => $clicks, 'conversions' => $conversions] : null;
    }

    private function toNumber(string $value): ?float
    {
        $cleaned = trim(str_replace(['$', ',', '%', '€', '£'], '', $value));
        if ($cleaned === '' || !is_numeric($cleaned)) {
            return null;
        }

        return (float) $cleaned;
    }
}
