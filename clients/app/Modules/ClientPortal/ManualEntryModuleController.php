<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

use App\Core\Request;

require_once BASE_PATH . '/app/Core/ClientPortalBase.php';
require_once BASE_PATH . '/app/Models/ClientPortalMetric.php';
require_once BASE_PATH . '/app/Models/ClientPortalScore.php';
require_once BASE_PATH . '/app/Models/PortalModule.php';

/**
 * Shared implementation for every manual-entry dashboard module.
 */
abstract class ManualEntryModuleController extends \ClientPortalBase
{
    protected string $moduleKey = '';
    protected string $viewFolder = '';
    protected string $activeMenu = '';
    protected bool $supportsPlatforms = false;

    public function index(Request $request, array $params): void
    {
        $client = $this->resolveSlugOrRedirect($params['slug'] ?? null);
        $clientId = (int) $client['id'];
        
        $metricModel = new \ClientPortalMetric();
        $scoreModel = new \ClientPortalScore();
        $moduleModel = new \PortalModule();

        $mod = $moduleModel->getBySlug($this->moduleKey);
        if (!$mod) {
            die("Module not registered: " . $this->moduleKey);
        }

        $fields = ModuleFieldDefinitions::forModule($this->moduleKey);

        if ($this->supportsPlatforms) {
            $platforms = ModuleFieldDefinitions::socialPlatforms();
            $byPlatform = [];
            foreach ($platforms as $code => $label) {
                $latest = $metricModel->latest($clientId, $mod['id'], $code);
                $byPlatform[$code] = [
                    'label' => $label,
                    'latest' => $latest,
                    'data' => $latest ? json_decode($latest['data_json'], true) : null,
                ];
            }

            // Retrieve score row for the social module itself
            $scoreRow = $scoreModel->latest($clientId, $mod['id']);

            $this->viewPortal($this->viewFolder . '/index', [
                'title' => $this->pageTitle(),
                'active_menu' => $this->activeMenu,
                'fields' => $fields,
                'byPlatform' => $byPlatform,
                'scoreRow' => $scoreRow,
                'currentPeriod' => date('Y-m-01'),
            ]);
            return;
        }

        $latest = $metricModel->latest($clientId, $mod['id']);
        $previous = $latest ? $metricModel->previous($clientId, $mod['id'], '', $latest['period_start']) : null;
        $history = $metricModel->history($clientId, $mod['id'], '', 12);
        
        // Retrieve score row matching the latest metrics period
        $scoreRow = $latest ? $scoreModel->getForPeriod($clientId, $mod['id'], $latest['period_start']) : $scoreModel->latest($clientId, $mod['id']);

        $this->viewPortal($this->viewFolder . '/index', [
            'title' => $this->pageTitle(),
            'active_menu' => $this->activeMenu,
            'fields' => $fields,
            'latest' => $latest,
            'latestData' => $latest ? json_decode($latest['data_json'], true) : null,
            'previousData' => $previous ? json_decode($previous['data_json'], true) : null,
            'history' => $history,
            'scoreRow' => $scoreRow,
            'currentPeriod' => date('Y-m-01'),
        ]);
    }

    public function saveManualEntry(Request $request, array $params): void
    {
        // Gated: manual metrics saving on portal is only for admin-driven tools
        $client = $this->resolveSlugOrRedirect($params['slug'] ?? null);
        $this->verifyCSRF();

        $moduleModel = new \PortalModule();
        $mod = $moduleModel->getBySlug($this->moduleKey);
        if (!$mod) {
            die("Module not registered");
        }

        $fields = ModuleFieldDefinitions::forModule($this->moduleKey);
        $platform = $this->supportsPlatforms ? (string) ($_POST['platform'] ?? '') : '';
        $periodStart = $_POST['period_start'] ?? date('Y-m-01');
        $periodEnd = date('Y-m-t', strtotime($periodStart));

        $data = [];
        foreach ($fields as $field) {
            $raw = $_POST[$field['key']] ?? '';
            $data[$field['key']] = $field['type'] === 'textarea'
                ? array_values(array_filter(array_map('trim', explode("\n", (string) $raw))))
                : (is_numeric($raw) ? (float) $raw : $raw);
        }

        $metricModel = new \ClientPortalMetric();
        $metricModel->upsertForPeriod(
            (int) $client['id'],
            $mod['id'],
            $platform,
            $periodStart,
            $periodEnd,
            $data,
            $_SESSION['user_id'] ?? null
        );

        require_once BASE_PATH . '/app/Services/ClientActivity.php';
        \ClientActivity::log((int) $client['id'], null, ucfirst($this->moduleKey) . ' data entered for ' . $periodStart);

        header('Location: ' . $this->clientUrl($client['slug'], $this->activeMenu) . '?saved=1');
        exit;
    }

    abstract protected function pageTitle(): string;
}
