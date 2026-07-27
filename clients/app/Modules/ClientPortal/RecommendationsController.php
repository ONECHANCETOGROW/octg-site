<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

use App\Core\DbAdapter;
use App\Core\Request;

require_once BASE_PATH . '/app/Core/ClientPortalBase.php';
require_once BASE_PATH . '/app/Models/ClientPortalRecommendation.php';

final class RecommendationsController extends \ClientPortalBase
{
    public function index(Request $request, array $params): void
    {
        $client = $this->resolveSlugOrRedirect($params['slug'] ?? null);
        $clientId = (int) $client['id'];

        $this->syncLatestGoogleAdsRecommendations($clientId);

        $model = new \ClientPortalRecommendation();
        $all = $model->forClient($clientId);

        $grouped = ['High' => [], 'Medium' => [], 'Low' => []];
        foreach ($all as $rec) {
            $grouped[$rec['priority']][] = $rec;
        }

        $this->viewPortal('recommendations/index', [
            'title' => 'Recommendations',
            'active_menu' => 'recommendations',
            'grouped' => $grouped,
            'csrf_token' => $this->generateCSRF(),
        ]);
    }

    public function updateStatus(Request $request, array $params): void
    {
        $client = $this->resolveSlugOrRedirect($params['slug'] ?? null);
        $this->verifyCSRF();

        $recId = (int) ($params['recId'] ?? 0);
        $status = $_POST['status'] ?? 'open';
        $dueDate = $_POST['due_date'] ?? null;

        if (!in_array($status, ['open', 'in_progress', 'completed', 'ignored'], true)) {
            $status = 'open';
        }

        $model = new \ClientPortalRecommendation();
        $model->updateStatus($recId, (int) $client['id'], $status, $dueDate ?: null);

        require_once BASE_PATH . '/app/Services/ClientActivity.php';
        \ClientActivity::log((int) $client['id'], $this->clientUserId(), 'Marked recommendation #' . $recId . ' as ' . $status);

        header('Location: ' . $this->clientUrl($client['slug'], 'recommendations'));
        exit;
    }

    private function syncLatestGoogleAdsRecommendations(int $clientId): void
    {
        $access = new ClientReportAccess(DbAdapter::instance());
        $latest = $access->latestWithContract($clientId, 'google_ads');
        if ($latest === null) {
            return;
        }

        $model = new \ClientPortalRecommendation();
        $recommendations = $latest['contract']['recommendations']['recommendations'] ?? [];
        $reportVersion = $latest['contract']['metadata']['contract_version'] ?? null;
        $periodStart = date('Y-m-01', strtotime((string) ($latest['report']['created_at'] ?? 'now')));
        foreach ($recommendations as $rec) {
            $model->syncOne(
                $clientId,
                'google_ads',
                (string) ($rec['recommendation_id'] ?? md5(json_encode($rec))),
                (int) $latest['report']['id'],
                (string) ($rec['what_to_change'] ?? ''),
                (string) ($rec['why_it_matters'] ?? ''),
                in_array($rec['priority'] ?? 'Medium', ['High', 'Medium', 'Low'], true) ? $rec['priority'] : 'Medium',
                (int) $latest['report']['id'],
                $reportVersion !== null ? (string) $reportVersion : null,
                $periodStart,
                'intelligence_engine'
            );
        }
    }
}
