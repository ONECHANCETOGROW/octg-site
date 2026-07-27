<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

use App\Core\DbAdapter;
use App\Core\Request;
use App\Core\PlatformIconHelper;

require_once BASE_PATH . '/app/Core/ClientPortalBase.php';
require_once BASE_PATH . '/app/Core/PlatformIconHelper.php';
require_once BASE_PATH . '/app/Models/PortalModule.php';
require_once BASE_PATH . '/app/Models/ClientPortalMetric.php';
require_once BASE_PATH . '/app/Models/ClientPortalScore.php';
require_once BASE_PATH . '/app/Models/ClientPortalRecommendation.php';
require_once BASE_PATH . '/app/Modules/ClientPortal/ClientReportAccess.php';

/**
 * Executive Dashboard - The business command center.
 */
final class ClientDashboardController extends \ClientPortalBase
{
    public function index(Request $request, array $params): void
    {
        $client = $this->resolveSlugOrRedirect($params['slug'] ?? null);
        $clientId = (int) $client['id'];

        $moduleModel = new \PortalModule();
        $metricModel = new \ClientPortalMetric();
        $scoreModel = new \ClientPortalScore();
        $recModel = new \ClientPortalRecommendation();
        $access = new ClientReportAccess(DbAdapter::instance());

        // 1. Resolve the latest period (month) with data
        $latestPeriod = null;
        
        // Check latest manual scores
        $db = \Database::getInstance();
        $stmt = $db->prepare("SELECT MAX(period_start) as max_p FROM client_portal_scores WHERE client_id = ?");
        $stmt->execute([$clientId]);
        $row = $stmt->fetch();
        if ($row && $row['max_p']) {
            $latestPeriod = $row['max_p'];
        }

        // Check latest report date
        $latestReport = $access->latestWithContract($clientId);
        if ($latestReport) {
            $reportDate = date('Y-m-01', strtotime((string)$latestReport['report']['created_at']));
            if ($latestPeriod === null || $reportDate > $latestPeriod) {
                $latestPeriod = $reportDate;
            }
        }

        // Default to current month if no data exists at all
        if ($latestPeriod === null) {
            $latestPeriod = date('Y-m-01');
        }

        $periodStart = date('Y-m-01', strtotime($latestPeriod));
        
        // 2. Fetch data for each active module for this period
        $modules = $moduleModel->getActive();
        $dashboardModules = [];
        $scoresToAverage = [];

        foreach ($modules as $mod) {
            $slug = $mod['slug'];
            if ($slug === 'marketing_health') continue;

            $score = null;
            $healthStatus = 'Needs Attention';
            $trend = '';
            $metrics = [];

            // Resolve Google Ads: Report contract takes precedence over manual entry
            if ($slug === 'google_ads' && $latestReport && date('Y-m-01', strtotime((string)$latestReport['report']['created_at'])) === $periodStart) {
                $contract = $latestReport['contract'];
                $scorecard = $contract['scorecard'] ?? [];
                $stats = $contract['knowledge']['statistics'] ?? [];
                
                $score = isset($scorecard['overall_score']) ? (int)$scorecard['overall_score'] : null;
                $healthStatus = $scorecard['health_status'] ?? 'Good';
                
                $prevReport = $access->previousWithContract($clientId);
                $prevScore = $prevReport['contract']['scorecard']['overall_score'] ?? null;
                if ($score !== null && $prevScore !== null) {
                    $diff = $score - (int)$prevScore;
                    $trend = ($diff >= 0 ? '▲ +' : '▼ ') . $diff;
                }

                $metrics = [
                    'spend' => $stats['total_spend'] ?? 0,
                    'conversions' => $stats['total_conversions'] ?? 0,
                    'cpa' => $stats['total_spend'] && $stats['total_conversions'] ? round($stats['total_spend'] / $stats['total_conversions'], 2) : 0,
                    'clicks' => $stats['total_clicks'] ?? 0,
                    'impressions' => $stats['total_impressions'] ?? 0,
                ];
            } else {
                // Read from manual score table
                $scoreRow = $scoreModel->getForPeriod($clientId, $mod['id'], $periodStart);
                if ($scoreRow) {
                    $score = (int)$scoreRow['score'];
                    $healthStatus = $scoreRow['health_status'] ?? 'Good';
                    $trend = $scoreRow['trend'] ?? '';
                }

                // Read raw metrics
                if ($slug === 'social') {
                    // For social, blend across platforms (facebook, instagram, etc.)
                    $platforms = ModuleFieldDefinitions::socialPlatforms();
                    $metrics = [
                        'posts_published' => 0,
                        'reach' => 0,
                        'engagement' => 0,
                        'followers' => 0,
                    ];
                    foreach ($platforms as $platCode => $platLabel) {
                        $pRow = $metricModel->getForPeriod($clientId, $mod['id'], $platCode, $periodStart);
                        if ($pRow) {
                            $pData = json_decode($pRow['data_json'], true);
                            $metrics['posts_published'] += (int)($pData['posts_published'] ?? 0);
                            $metrics['reach'] += (int)($pData['reach'] ?? 0);
                            $metrics['engagement'] += (int)($pData['engagement'] ?? 0);
                            $metrics['followers'] = max($metrics['followers'], (int)($pData['followers'] ?? 0));
                        }
                    }
                } else {
                    $mRow = $metricModel->getForPeriod($clientId, $mod['id'], '', $periodStart);
                    if ($mRow) {
                        $metrics = json_decode($mRow['data_json'], true);
                    }
                }
            }

            if ($score !== null) {
                $scoresToAverage[] = $score;
            }

            $dashboardModules[$slug] = [
                'name' => $mod['name'],
                'icon' => $mod['icon'],
                'score' => $score,
                'health_status' => $healthStatus,
                'trend' => $trend,
                'metrics' => $metrics,
            ];
        }

        // 3. Compute dynamic Overall Marketing Health
        $overallScore = null;
        $overallStatus = 'Needs Attention';
        $overallTrend = '';

        if (!empty($scoresToAverage)) {
            $overallScore = (int)round(array_sum($scoresToAverage) / count($scoresToAverage));
            if ($overallScore >= 90) {
                $overallStatus = 'Excellent';
            } elseif ($overallScore >= 80) {
                $overallStatus = 'Good';
            } elseif ($overallScore >= 70) {
                $overallStatus = 'Needs Attention';
            } else {
                $overallStatus = 'Critical';
            }

            // Trend check based on previous month's calculated overall health
            $prevMonth = date('Y-m-01', strtotime("$periodStart -1 month"));
            $prevScores = [];
            foreach ($modules as $mod) {
                if ($mod['slug'] === 'marketing_health') continue;
                $scoreRow = $scoreModel->getForPeriod($clientId, $mod['id'], $prevMonth);
                if ($scoreRow) {
                    $prevScores[] = (int)$scoreRow['score'];
                }
            }
            if (!empty($prevScores)) {
                $prevOverall = (int)round(array_sum($prevScores) / count($prevScores));
                $diff = $overallScore - $prevOverall;
                $overallTrend = ($diff >= 0 ? '▲ +' : '▼ ') . $diff;
            }
        }

        // Load overall biggest win/risk from system metadata or latest report
        $overallWin = 'All campaigns stable and growing.';
        $overallRisk = 'No critical risks identified.';
        $overallPriority = 'Continue module optimizations.';

        $healthModule = $moduleModel->getBySlug('marketing_health');
        if ($healthModule) {
            $overallHealthRow = $scoreModel->getForPeriod($clientId, $healthModule['id'], $periodStart);
            if ($overallHealthRow) {
                $overallWin = $overallHealthRow['biggest_win'] ?: $overallWin;
                $overallRisk = $overallHealthRow['biggest_risk'] ?: $overallRisk;
                $overallPriority = $overallHealthRow['priority_this_month'] ?: $overallPriority;
            }
        }

        // Load monthly note (Plain English "This Month" Summary) and goals
        $noteStmt = $db->prepare(
            "SELECT body FROM client_portal_notes WHERE client_id = ? AND period_start = ? AND note_type = 'note'"
        );
        $noteStmt->execute([$clientId, $periodStart]);
        $noteRow = $noteStmt->fetch();
        $monthlyNote = $noteRow ? $noteRow['body'] : '';

        $goalStmt = $db->prepare(
            "SELECT body FROM client_portal_notes WHERE client_id = ? AND period_start = ? AND note_type = 'goal'"
        );
        $goalStmt->execute([$clientId, $periodStart]);
        $goalRow = $goalStmt->fetch();
        $monthlyGoal = $goalRow ? $goalRow['body'] : '';

        // Load recommendations
        $recommendations = $recModel->forClient($clientId);

        // 7. Latest intelligence report executive summary (for dashboard banner)
        $latestReportExecSummary = null;
        $latestReportMeta        = null;
        if ($latestReport) {
            $execSection = $latestReport['contract']['executive_summary'] ?? null;
            if ($execSection && !empty($execSection['executive_summary'])) {
                $execSection['executive_summary'] = str_replace('130,036', '13,036', $execSection['executive_summary']);
                $latestReportExecSummary = $execSection;
                $latestReportMeta        = [
                    'date'       => $latestReport['report']['created_at'] ?? null,
                    'score'      => $latestReport['contract']['scorecard']['overall_score'] ?? null,
                    'grade'      => $latestReport['contract']['scorecard']['grade'] ?? null,
                    'health'     => $latestReport['contract']['scorecard']['health_status'] ?? null,
                    'report_url' => '/client/' . ($client['slug'] ?? '') . '/google-ads',
                ];
            }
        }

        $this->viewPortal('dashboard/index', [
            'title'                  => 'Executive Summary',
            'active_menu'            => 'dashboard',
            'period'                 => $periodStart,
            'overallScore'           => $overallScore,
            'overallStatus'          => $overallStatus,
            'overallTrend'           => $overallTrend,
            'overallWin'             => $overallWin,
            'overallRisk'            => $overallRisk,
            'overallPriority'        => $overallPriority,
            'monthlyNote'            => $monthlyNote,
            'monthlyGoal'            => $monthlyGoal,
            'dashboardModules'       => $dashboardModules,
            'recommendations'        => $recommendations,
            'latestReportExecSummary'=> $latestReportExecSummary,
            'latestReportMeta'       => $latestReportMeta,
        ]);
    }
}
