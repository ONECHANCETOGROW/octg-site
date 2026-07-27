<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

/**
 * Deterministic insight engine.
 *
 * Converts raw intelligence contract data into plain-English insights
 * following the Data → Insight → Recommendation pattern.
 *
 * No AI API calls — all analysis is rule-based using actual numbers from
 * the intelligence contract. Every figure shown in an insight is derived
 * directly from the data.
 *
 * Each public method returns:
 *   ['insight' => string, 'recommendation' => string, 'severity' => string]
 * where severity is: 'positive' | 'warning' | 'critical' | 'neutral'
 */
final class IntelligenceInsightEngine
{
    // ----------------------------------------------------------------
    // Campaign Analysis
    // ----------------------------------------------------------------

    /**
     * @param array<int,array<string,mixed>> $campaigns
     * @return array<string,mixed>
     */
    public function campaignInsights(array $campaigns): array
    {
        if (empty($campaigns)) {
            return $this->neutral('No campaign data available for this report period.');
        }

        $totalSpend       = array_sum(array_column($campaigns, 'spend'));
        $totalConversions = array_sum(array_column($campaigns, 'conversions'));
        $accountCpa       = $totalConversions > 0 ? $totalSpend / $totalConversions : null;

        // Sort by conversions desc for analysis
        usort($campaigns, fn($a, $b) => (float)($b['conversions'] ?? 0) <=> (float)($a['conversions'] ?? 0));

        $topCampaign  = $campaigns[0] ?? null;
        $wasteCampaigns = array_filter($campaigns, fn($c) =>
            (float)($c['conversions'] ?? 0) === 0.0 && (float)($c['spend'] ?? 0) > 0
        );
        $budgetWaste  = array_sum(array_map(fn($c) => (float)($c['spend'] ?? 0), $wasteCampaigns));

        $insights  = [];
        $recs      = [];
        $severity  = 'neutral';

        if ($topCampaign) {
            $topName  = $topCampaign['campaign_name'] ?? 'Top Campaign';
            $topConv  = (int)($topCampaign['conversions'] ?? 0);
            $topSpend = (float)($topCampaign['spend'] ?? 0);
            $topCpa   = $topConv > 0 ? $topSpend / $topConv : null;
            $topShare = $totalConversions > 0 ? round(($topConv / $totalConversions) * 100) : 0;
            $spendShare = $totalSpend > 0 ? round(($topSpend / $totalSpend) * 100) : 0;

            $insights[] = "\"$topName\" generated $topConv conversions ({$topShare}% of total) while consuming {$spendShare}% of budget.";

            if ($topCpa !== null && $accountCpa !== null && $topCpa < $accountCpa * 0.85) {
                $insights[] = "Its cost per conversion (\$" . number_format($topCpa, 2) . ") is significantly below the account average (\$" . number_format($accountCpa, 2) . ").";
                $recs[]     = "Increase budget allocation to \"$topName\" — it is your most efficient converter.";
                $severity   = 'positive';
            }
        }

        if ($budgetWaste > 0) {
            $wasteCount = count($wasteCampaigns);
            $insights[] = "\$" . number_format($budgetWaste, 2) . " was spent across $wasteCount campaign(s) that generated zero tracked conversions this period.";
            $recs[]     = "Pause or restructure zero-conversion campaigns immediately to reclaim \$" . number_format($budgetWaste, 2) . " of budget waste.";
            $severity   = $budgetWaste > 500 ? 'critical' : 'warning';
        }

        if (empty($insights)) {
            $insights[] = count($campaigns) . " campaigns active with \$" . number_format($totalSpend, 2) . " total spend and $totalConversions total conversions.";
        }

        return [
            'insight'        => implode(' ', $insights),
            'recommendation' => implode(' ', $recs) ?: 'Monitor campaign performance weekly and adjust bids based on CPA targets.',
            'severity'       => $severity,
            'stats'          => [
                'total_spend'       => $totalSpend,
                'total_conversions' => $totalConversions,
                'account_cpa'       => $accountCpa,
                'budget_waste'      => $budgetWaste,
            ],
        ];
    }

    // ----------------------------------------------------------------
    // Search Terms Analysis
    // ----------------------------------------------------------------

    /**
     * @param array<int,array<string,mixed>> $searchTerms
     * @return array<string,mixed>
     */
    public function searchTermInsights(array $searchTerms): array
    {
        $nonEmpty = array_values(array_filter(
            $searchTerms,
            fn($t) => trim((string)($t['search_term'] ?? '')) !== ''
        ));

        if (empty($nonEmpty)) {
            return $this->neutral('No search term data available. Upload a Search Terms report to unlock this analysis.');
        }

        $wasteTerms = array_filter(
            $nonEmpty,
            fn($t) => (float)($t['conversions'] ?? 0) === 0.0 && (float)($t['spend'] ?? 0) > 0
        );
        $wasteSpend = array_sum(array_map(fn($t) => (float)($t['spend'] ?? 0), $wasteTerms));

        usort($nonEmpty, fn($a, $b) => (float)($b['conversions'] ?? 0) <=> (float)($a['conversions'] ?? 0));
        $bestTerm = $nonEmpty[0] ?? null;

        $insights = [];
        $recs     = [];
        $severity = 'neutral';

        if ($bestTerm && (float)($bestTerm['conversions'] ?? 0) > 0) {
            $term = $bestTerm['search_term'];
            $conv = (int)$bestTerm['conversions'];
            $spend = (float)$bestTerm['spend'];
            $insights[] = "\"$term\" is your best-converting search phrase — $conv conversion(s) at \$" . number_format($spend, 2) . " spend.";
            $recs[] = "Consider adding \"$term\" as an exact-match keyword to protect and scale this traffic.";
            $severity = 'positive';
        }

        if ($wasteSpend > 0) {
            $wasteCount = count($wasteTerms);
            $insights[] = "$wasteCount search phrase(s) spent \$" . number_format($wasteSpend, 2) . " with zero conversions — these are negative keyword opportunities.";
            $recs[] = "Add zero-conversion search terms as negative keywords to eliminate wasted spend of \$" . number_format($wasteSpend, 2) . ".";
            $severity = $wasteSpend > 100 ? 'critical' : 'warning';
        }

        if (empty($insights)) {
            $insights[] = count($nonEmpty) . " search terms tracked this period.";
        }

        return [
            'insight'        => implode(' ', $insights),
            'recommendation' => implode(' ', $recs) ?: 'Review search terms monthly and add irrelevant phrases as negative keywords.',
            'severity'       => $severity,
            'waste_spend'    => $wasteSpend,
            'waste_count'    => count($wasteTerms),
        ];
    }

    // ----------------------------------------------------------------
    // Device Analysis
    // ----------------------------------------------------------------

    /**
     * @param array<int,array<string,mixed>> $devices
     * @return array<string,mixed>
     */
    public function deviceInsights(array $devices): array
    {
        if (empty($devices)) {
            return $this->neutral('No device breakdown data in this report.');
        }

        usort($devices, fn($a, $b) => (float)($b['conversions'] ?? 0) <=> (float)($a['conversions'] ?? 0));
        $best  = $devices[0];
        $worst = end($devices);

        $bestDevice  = $best['device'] ?? $best['device_type'] ?? 'Top Device';
        $worstDevice = $worst['device'] ?? $worst['device_type'] ?? 'Lowest Device';
        $bestConv    = (int)($best['conversions'] ?? 0);
        $worstConv   = (int)($worst['conversions'] ?? 0);
        $worstSpend  = (float)($worst['spend'] ?? 0);

        $insight = "$bestDevice drives the most conversions ($bestConv).";
        $rec     = 'Review device bid adjustments to push more budget toward your best-performing device type.';

        if ($worstConv === 0 && $worstSpend > 50) {
            $insight .= " $worstDevice spent \$" . number_format($worstSpend, 2) . " with zero conversions.";
            $rec      = "Apply a negative bid adjustment (-30% or more) on $worstDevice to redirect wasted spend.";
        }

        return [
            'insight'        => $insight,
            'recommendation' => $rec,
            'severity'       => ($worstConv === 0 && $worstSpend > 50) ? 'warning' : 'neutral',
        ];
    }

    // ----------------------------------------------------------------
    // Overall Account Insight
    // ----------------------------------------------------------------

    /**
     * @param array<string,mixed> $stats
     * @param array<string,mixed> $scorecard
     * @return array<string,mixed>
     */
    public function overallInsight(array $stats, array $scorecard): array
    {
        $score  = (int)($scorecard['overall_score'] ?? 0);
        $grade  = $scorecard['grade'] ?? '?';
        $status = $scorecard['health_status'] ?? 'Unknown';

        $penalties    = $scorecard['penalties'] ?? [];
        $penaltyCount = count($penalties);
        $totalPenalty = array_sum(array_column($penalties, 'penalty'));

        $spend       = (float)($stats['total_spend'] ?? 0);
        $conversions = (float)($stats['total_conversions'] ?? 0);
        $cpa         = $conversions > 0 ? $spend / $conversions : null;

        $lines = [];
        $lines[] = "This account scored $score/100 (Grade $grade — $status) based on " . ($penaltyCount > 0 ? "$penaltyCount rule violation(s) totalling -$totalPenalty penalty points." : "all evaluated rules.");

        if ($cpa !== null) {
            $lines[] = "\$" . number_format($spend, 2) . " spent generated " . (int)$conversions . " conversion(s) at an average cost of \$" . number_format($cpa, 2) . " each.";
        }

        $rec = match (true) {
            $score >= 90 => 'Excellent performance. Focus on scaling what is working and exploring new audience segments.',
            $score >= 80 => 'Good performance. Address the flagged rule violations to push into the 90+ range.',
            $score >= 70 => 'Needs attention. Prioritise fixing the high-severity issues before increasing spend.',
            default      => 'Critical issues detected. Pause spend increases and resolve all flagged violations immediately.',
        };

        return [
            'insight'        => implode(' ', $lines),
            'recommendation' => $rec,
            'severity'       => match (true) {
                $score >= 90 => 'positive',
                $score >= 70 => 'warning',
                default      => 'critical',
            },
        ];
    }

    // ----------------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------------

    /** @return array<string,string> */
    private function neutral(string $message): array
    {
        return [
            'insight'        => $message,
            'recommendation' => '',
            'severity'       => 'neutral',
        ];
    }
}
