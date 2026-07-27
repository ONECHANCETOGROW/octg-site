<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

/**
 * Deterministic sentence templates -- no AI call, ever (matches this
 * platform's "no paid AI APIs" posture and the brief's own requirement
 * that translation be repeatable/auditable, not generated fresh each
 * time). Given the exact fields a Google Ads audit already produces
 * (spend, conversions, cpa), builds the narrative the brief's own example
 * specifies:
 *   "You spent $2,478 this month. You generated 216 leads. Your average
 *   cost per lead was $11.47. This is significantly better than the
 *   industry average."
 *
 * The industry-average comparison band is intentionally a plain constant
 * array here, not hardcoded inline per-sentence -- see
 * $industryBenchmarks -- so it can be edited without touching the
 * sentence-building logic, same rationale as MarketingIntel's
 * data-driven Prompt Library (edit data, not code).
 */
final class PlainEnglish
{
    /**
     * Rough CPA/CTR benchmarks used only to phrase a qualitative
     * comparison ("better than average" / "in line with" / "higher than
     * average") -- never shown as a citation to a specific source, since
     * these are directional constants, not looked-up industry data.
     */
    private const INDUSTRY_AVG_CPA = 48.0;

    /** @var array<string,string> */
    private const METRIC_LABELS = [
        'spend' => 'Amount Spent',
        'conversions' => 'Leads Generated',
        'cpa' => 'Cost Per Lead',
        'clicks' => 'Clicks',
        'impressions' => 'Times Your Ads Were Shown',
        'ctr' => 'Click Rate',
        'campaign_name' => 'Campaign',
        'search_term' => 'Search Phrase',
        'keyword' => 'Keyword',
    ];

    public function label(string $field): string
    {
        return self::METRIC_LABELS[$field] ?? ucwords(str_replace('_', ' ', $field));
    }

    /**
     * The brief's exact narrative pattern for the Google Ads module.
     */
    public function googleAdsSummary(float $spend, float $conversions, ?float $cpa): string
    {
        $cpa = $cpa ?? ($conversions > 0 ? $spend / $conversions : null);

        $sentences = [];
        $sentences[] = sprintf('You spent %s this month.', $this->money($spend));

        if ($conversions > 0) {
            $sentences[] = sprintf(
                'You generated %d %s.',
                (int) $conversions,
                $conversions === 1.0 ? 'lead' : 'leads'
            );
        } else {
            $sentences[] = 'You did not generate any tracked leads this month -- worth checking that conversion tracking is set up correctly.';
        }

        if ($cpa !== null && $conversions > 0) {
            $sentences[] = sprintf('Your average cost per lead was %s.', $this->money($cpa));
            $sentences[] = $this->cpaComparison($cpa);
        }

        return implode(' ', $sentences);
    }

    private function cpaComparison(float $cpa): string
    {
        $diffPercent = (($cpa - self::INDUSTRY_AVG_CPA) / self::INDUSTRY_AVG_CPA) * 100;

        if ($diffPercent <= -15) {
            return 'This is significantly better than the industry average.';
        }
        if ($diffPercent < 0) {
            return 'This is a bit better than the industry average.';
        }
        if ($diffPercent <= 15) {
            return 'This is roughly in line with the industry average.';
        }
        return 'This is higher than the industry average, which is worth addressing.';
    }

    public function money(float $value): string
    {
        return '$' . number_format($value, 2);
    }

    public function trendSentence(?float $current, ?float $previous, string $metricLabel, bool $higherIsBetter = true): string
    {
        if ($current === null || $previous === null || $previous == 0.0) {
            return "No prior month's data to compare {$metricLabel} against yet.";
        }

        $deltaPercent = (($current - $previous) / abs($previous)) * 100;
        $direction = $deltaPercent >= 0 ? 'up' : 'down';
        $isGoodDirection = $higherIsBetter ? $deltaPercent >= 0 : $deltaPercent < 0;

        return sprintf(
            '%s is %s %s%% versus last month, which is %s.',
            $metricLabel,
            $direction,
            number_format(abs($deltaPercent), 1),
            $isGoodDirection ? 'a positive sign' : 'worth keeping an eye on'
        );
    }
}
