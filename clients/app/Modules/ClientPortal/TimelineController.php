<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

use App\Core\DbAdapter;
use App\Core\Request;

require_once BASE_PATH . '/app/Core/ClientPortalBase.php';
require_once BASE_PATH . '/app/Models/ClientPortalMetric.php';
require_once BASE_PATH . '/app/Models/PortalModule.php';

/**
 * Derived view only (spec section 4.9) -- built by querying the Reports,
 * manual-entry-metrics, and custom timeline events tables.
 */
final class TimelineController extends \ClientPortalBase
{
    public function index(Request $request, array $params): void
    {
        $client = $this->resolveSlugOrRedirect($params['slug'] ?? null);
        $clientId = (int) $client['id'];

        $access = new ClientReportAccess(DbAdapter::instance());
        $metricModel = new \ClientPortalMetric();
        $moduleModel = new \PortalModule();

        $events = [];

        // 1. Fetch system audit report completions
        foreach ($access->completedReportsForClient($clientId) as $report) {
            $events[] = [
                'month' => date('F Y', strtotime((string) $report['created_at'])),
                'sort' => strtotime((string) $report['created_at']),
                'label' => 'Monthly Report Generated',
                'description' => ucwords(str_replace('_', ' ', $report['channel'])) . ' Marketing Intelligence report completed successfully.',
                'icon' => 'file-text',
            ];
        }

        // 2. Fetch manual metrics snapshot completions
        $activeModules = $moduleModel->getActive();
        foreach ($activeModules as $mod) {
            if ($mod['slug'] === 'marketing_health' || $mod['slug'] === 'google_ads') continue;
            foreach ($metricModel->history($clientId, $mod['id'], '', 24) as $row) {
                $events[] = [
                    'month' => date('F Y', strtotime((string) $row['period_start'])),
                    'sort' => strtotime((string) $row['period_start']),
                    'label' => $mod['name'] . ' Snapshot Uploaded',
                    'description' => "Monthly metrics data updated for {$mod['name']}.",
                    'icon' => $mod['icon'] ?: 'clipboard-list',
                ];
            }
        }

        // 3. Fetch custom manual timeline events
        $db = \Database::getInstance();
        $manualStmt = $db->prepare(
            "SELECT * FROM client_portal_timeline_events WHERE client_id = ? ORDER BY event_date DESC"
        );
        $manualStmt->execute([$clientId]);
        $manualEvents = $manualStmt->fetchAll();

        foreach ($manualEvents as $event) {
            $events[] = [
                'month' => date('F Y', strtotime((string) $event['event_date'])),
                'sort' => strtotime((string) $event['event_date']),
                'label' => $event['label'],
                'description' => $event['description'],
                'icon' => $event['icon'] ?: 'calendar',
            ];
        }

        // 4. Sort and group by month
        usort($events, static fn ($a, $b) => $b['sort'] <=> $a['sort']);

        $byMonth = [];
        foreach ($events as $event) {
            $byMonth[$event['month']][] = $event;
        }

        $this->viewPortal('timeline/index', [
            'title' => 'Business Timeline',
            'active_menu' => 'timeline',
            'byMonth' => $byMonth,
        ]);
    }
}
