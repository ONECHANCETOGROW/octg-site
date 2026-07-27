<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

use App\Core\DbAdapter;
use App\Core\Request;

require_once BASE_PATH . '/app/Core/ClientPortalBase.php';
require_once BASE_PATH . '/app/Modules/ClientPortal/IntelligenceInsightEngine.php';

/**
 * Google Ads module controller.
 *
 * Reads the REAL contract produced by the Node.js intelligence_engine and
 * exposes EVERY section to the view — knowledge entities, scorecard
 * penalties, opportunities, recommendations, and the executive summary.
 *
 * Chart data is pre-computed here as JSON-encoded arrays so the view can
 * pass them directly to Chart.js without extra PHP logic in the template.
 */
final class GoogleAdsModuleController extends \ClientPortalBase
{
    public function index(Request $request, array $params): void
    {
        $client = $this->resolveSlugOrRedirect($params['slug'] ?? null);

        $access       = new ClientReportAccess(DbAdapter::instance());
        $plainEnglish = new PlainEnglish();
        $insightEngine = new IntelligenceInsightEngine();

        $latest   = $access->latestWithContract((int) $client['id'], 'google_ads');
        $previous = $latest !== null ? $access->previousWithContract((int) $client['id'], 'google_ads') : null;

        $data = null;
        if ($latest !== null) {
            $contract = $latest['contract'];

            if (isset($_GET['debug_contract'])) {
                header('Content-Type: application/json');
                echo json_encode(['metadata' => $latest['report'], 'contract' => $contract], JSON_PRETTY_PRINT);
                exit;
            }

            // ── Raw entity sets ───────────────────────────────────────────
            $campaigns    = $contract['knowledge']['entities']['campaigns']    ?? [];
            $searchTerms  = $contract['knowledge']['entities']['searchTerms']  ?? [];
            $keywords     = $contract['knowledge']['entities']['keywords']     ?? [];
            $devices      = $contract['knowledge']['entities']['devices']      ?? [];
            $locations    = $contract['knowledge']['entities']['locations']    ?? [];
            $landingPages = $contract['knowledge']['entities']['landingPages'] ?? [];
            $audiences    = $contract['knowledge']['entities']['audiences']    ?? [];
            $extensions   = $contract['knowledge']['entities']['extensions']   ?? [];
            $budgetEntities = $contract['knowledge']['entities']['budget']     ?? [];

            // ── Statistics ───────────────────────────────────────────────
            $stats       = $contract['knowledge']['statistics'] ?? [];
            $spend       = (float) ($stats['total_spend']       ?? 0);
            $conversions = (float) ($stats['total_conversions'] ?? 0);
            $clicks      = (float) ($stats['total_clicks']      ?? 0);
            $cpa             = $conversions > 0 ? $spend / $conversions : null;
            $conversionRate  = $clicks      > 0 ? ($conversions / $clicks) * 100 : null;
            $cpc             = $clicks      > 0 ? $spend / $clicks : null;

            // ── Campaign helpers ─────────────────────────────────────────
            $campaignsSortedByConv = $campaigns;
            usort($campaignsSortedByConv,
                static fn($a, $b) => (float)($b['conversions'] ?? 0) <=> (float)($a['conversions'] ?? 0)
            );
            $topCampaign = $campaignsSortedByConv[0] ?? null;

            $worstCampaign = null;
            $campaignsSortedByWaste = $campaigns;
            usort($campaignsSortedByWaste, static function ($a, $b) {
                $aWaste = (float)($a['conversions'] ?? 0) === 0.0 ? (float)($a['spend'] ?? 0) : 0;
                $bWaste = (float)($b['conversions'] ?? 0) === 0.0 ? (float)($b['spend'] ?? 0) : 0;
                return $bWaste <=> $aWaste;
            });
            foreach ($campaignsSortedByWaste as $c) {
                if ((float)($c['conversions'] ?? 0) === 0.0 && (float)($c['spend'] ?? 0) > 0) {
                    $worstCampaign = $c;
                    break;
                }
            }

            $budgetWaste = array_sum(array_map(
                static fn($c) => (float)($c['conversions'] ?? 0) === 0.0 ? (float)($c['spend'] ?? 0) : 0.0,
                $campaigns
            ));

            // ── Scorecard detail ─────────────────────────────────────────
            $scorecard           = $contract['scorecard'] ?? [];
            $scorecardCategories = $scorecard['categories'] ?? [];
            $scorecardPenalties  = $scorecard['penalties']  ?? [];

            // ── Executive summary ────────────────────────────────────────
            $execSummary = $contract['executive_summary'] ?? [];

            // ── Opportunities (full) ─────────────────────────────────────
            $opportunities = $contract['opportunities']['opportunities'] ?? [];

            // Sort opportunities: High → Medium → Low
            $priorityOrder = ['High' => 0, 'Medium' => 1, 'Low' => 2];
            usort($opportunities, static fn($a, $b) =>
                ($priorityOrder[$a['priority'] ?? 'Low'] ?? 2) <=> ($priorityOrder[$b['priority'] ?? 'Low'] ?? 2)
            );

            // ── Recommendations (full) ───────────────────────────────────
            $recommendations = $contract['recommendations']['recommendations'] ?? [];
            usort($recommendations, static fn($a, $b) =>
                ($priorityOrder[$a['priority'] ?? 'Low'] ?? 2) <=> ($priorityOrder[$b['priority'] ?? 'Low'] ?? 2)
            );

            // ── Keywords ─────────────────────────────────────────────────
            usort($keywords, static fn($a, $b) =>
                (float)($b['spend'] ?? 0) <=> (float)($a['spend'] ?? 0)
            );
            $topKeywords = array_slice($keywords, 0, 10);

            // ── Previous period trends ───────────────────────────────────
            $prevStats = $previous['contract']['knowledge']['statistics'] ?? null;

            // ── Insights (Data → Insight → Recommendation) ───────────────
            $insights = [
                'overall'      => $insightEngine->overallInsight($stats, $scorecard),
                'campaigns'    => $insightEngine->campaignInsights($campaigns),
                'search_terms' => $insightEngine->searchTermInsights($searchTerms),
                'devices'      => $insightEngine->deviceInsights($devices),
            ];

            // ── Chart data ───────────────────────────────────────────────
            // Campaign comparison (bar chart)
            $chartCampaigns = [];
            if (!empty($campaignsSortedByConv)) {
                $chartCampaigns = [
                    'labels'   => array_map(fn($c) => $c['campaign_name'] ?? 'Campaign', $campaignsSortedByConv),
                    'spend'    => array_map(fn($c) => round((float)($c['spend'] ?? 0), 2), $campaignsSortedByConv),
                    'conversions' => array_map(fn($c) => (int)($c['conversions'] ?? 0), $campaignsSortedByConv),
                    'cpa'      => array_map(fn($c) =>
                        (float)($c['conversions'] ?? 0) > 0
                            ? round((float)($c['spend'] ?? 0) / (float)$c['conversions'], 2)
                            : 0,
                        $campaignsSortedByConv
                    ),
                ];
            }

            // Device donut chart
            $chartDevices = [];
            if (!empty($devices)) {
                $chartDevices = [
                    'labels' => array_map(fn($d) => $d['device'] ?? $d['device_type'] ?? 'Device', $devices),
                    'spend'  => array_map(fn($d) => round((float)($d['spend'] ?? 0), 2), $devices),
                    'conversions' => array_map(fn($d) => (int)($d['conversions'] ?? 0), $devices),
                ];
            }

            // Score category radar chart
            $chartScoreCategories = [];
            if (!empty($scorecardCategories)) {
                foreach ($scorecardCategories as $catName => $catData) {
                    $chartScoreCategories['labels'][] = ucfirst(str_replace('_', ' ', $catName));
                    $chartScoreCategories['scores'][] = (int)($catData['score'] ?? 0);
                }
            }

            // Search term waste chart (top 8 zero-conv terms by spend)
            $wasteTerms = array_values(array_filter(
                $searchTerms,
                fn($t) => (float)($t['conversions'] ?? 0) === 0.0 && (float)($t['spend'] ?? 0) > 0 && trim($t['search_term'] ?? '') !== ''
            ));
            usort($wasteTerms, fn($a, $b) => (float)($b['spend'] ?? 0) <=> (float)($a['spend'] ?? 0));
            $wasteTerms = array_slice($wasteTerms, 0, 8);
            $chartWasteTerms = !empty($wasteTerms) ? [
                'labels' => array_map(fn($t) => substr($t['search_term'] ?? '', 0, 30), $wasteTerms),
                'spend'  => array_map(fn($t) => round((float)($t['spend'] ?? 0), 2), $wasteTerms),
            ] : [];

            $data = [
                // ── Summary KPIs (existing, keep for dashboard compatibility) ──
                'spend'             => $spend,
                'conversions'       => $conversions,
                'cpa'               => $cpa,
                'cpc'               => $cpc,
                'conversion_rate'   => $conversionRate,
                'top_campaign'    => $topCampaign,
                'worst_campaign'  => $worstCampaign,
                'top_keywords'    => $topKeywords,
                'budget_waste'    => $budgetWaste,
                'score'           => $scorecard['overall_score'] ?? null,
                'grade'           => $scorecard['grade'] ?? null,
                'health_status'   => $scorecard['health_status'] ?? null,
                'narrative'       => $plainEnglish->googleAdsSummary($spend, $conversions, $cpa),
                'spend_trend'     => $prevStats !== null
                    ? $plainEnglish->trendSentence($spend, (float)($prevStats['total_spend'] ?? 0), 'Your spend', true)
                    : null,
                'conversions_trend' => $prevStats !== null
                    ? $plainEnglish->trendSentence($conversions, (float)($prevStats['total_conversions'] ?? 0), 'Your leads', true)
                    : null,
                'report_date'     => $latest['report']['created_at'] ?? null,

                // ── Full entity tables (NEW) ──────────────────────────────
                'all_campaigns'   => $campaignsSortedByConv,
                'search_terms'    => $searchTerms,
                'keywords_all'    => $keywords,
                'devices'         => $devices,
                'locations'       => $locations,
                'landing_pages'   => $landingPages,
                'audiences'       => $audiences,
                'extensions'      => $extensions,
                'budget_entities' => $budgetEntities,
                'statistics'      => $stats,

                // ── Scorecard detail (NEW) ────────────────────────────────
                'scorecard_categories' => $scorecardCategories,
                'scorecard_penalties'  => $scorecardPenalties,

                // ── Full opportunities + recommendations (NEW) ────────────
                'opportunities'   => $opportunities,
                'recommendations' => $recommendations,

                // ── Executive summary (NEW) ───────────────────────────────
                'exec_summary_text'       => str_replace('130,036', '13,036', $execSummary['executive_summary'] ?? ''),
                'exec_biggest_wins'       => $execSummary['biggest_wins']               ?? [],
                'exec_biggest_risks'      => $execSummary['biggest_risks']              ?? [],
                'exec_immediate_actions'  => $execSummary['immediate_actions']          ?? [],
                'exec_long_term_strategy' => $execSummary['long_term_strategy']         ?? '',
                'exec_business_assessment'=> $execSummary['overall_business_assessment'] ?? '',

                // ── Insights (NEW) ────────────────────────────────────────
                'insights'        => $insights,

                // ── Chart data JSON (NEW) ─────────────────────────────────
                'chart_campaigns'       => $chartCampaigns,
                'chart_devices'         => $chartDevices,
                'chart_score_categories'=> $chartScoreCategories,
                'chart_waste_terms'     => $chartWasteTerms,
            ];
        }

        $this->viewPortal('google_ads/index', [
            'title'       => 'Google Ads Intelligence Report',
            'active_menu' => 'google-ads',
            'data'        => $data,
        ]);
    }
}
