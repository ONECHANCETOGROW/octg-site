<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

use App\Core\DbAdapter;
use App\Core\Request;

require_once BASE_PATH . '/app/Core/ClientPortalBase.php';

/**
 * Report Library (spec section 4.7). Lists every completed report for
 * THIS client only (ClientReportAccess is already client_id-scoped),
 * grouped by month, each entry versioned by its audit id.
 */
final class ClientReportsController extends \ClientPortalBase
{
    public function index(\App\Core\Request $request, array $params): void
    {
        $client = $this->resolveSlugOrRedirect($params['slug'] ?? null);
        $access = new ClientReportAccess(DbAdapter::instance());

        $reports = $access->completedReportsForClient((int) $client['id']);

        $byMonth = [];
        foreach ($reports as $report) {
            $month = date('F Y', strtotime((string) $report['created_at']));
            $byMonth[$month][] = $report;
        }

        $this->viewPortal('reports/index', [
            'title' => 'Reports',
            'active_menu' => 'reports',
            'byMonth' => $byMonth,
        ]);
    }

    public function view(\App\Core\Request $request, array $params): void
    {
        $client = $this->resolveSlugOrRedirect($params['slug'] ?? null);
        $access = new ClientReportAccess(DbAdapter::instance());

        $reportId = (int) ($params['reportId'] ?? 0);
        $contract = $access->contractFor((int) $client['id'], $reportId);

        if ($contract === null) {
            \App\Core\Response::notFound('Report not found.');
            return;
        }

        $this->viewPortal('reports/view', [
            'title' => 'Report',
            'active_menu' => 'reports',
            'contract' => $contract,
        ]);
    }
}
