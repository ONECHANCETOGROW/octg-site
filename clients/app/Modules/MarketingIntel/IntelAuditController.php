<?php

declare(strict_types=1);

namespace App\Modules\MarketingIntel;

use App\Core\IntelController;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Modules\MarketingIntel\Confidence\AuditProgressCalculator;
use App\Modules\MarketingIntel\Knowledge\KnowledgeBuilderAdapter;
use Client;
require_once BASE_PATH . '/app/Models/Client.php';

/**
 * Entry point for the AI Data Collection System: creating an audit, picking
 * channels, and the Data Collection Cockpit dashboard (RNS spec §4). The
 * per-requirement collect/paste/upload actions live in
 * CollectionController — this controller is the "big picture" view.
 */
final class IntelAuditController extends IntelController
{
    public function create(Request $request, array $params = []): void
    {
        $this->requireAuth();

        $clientModel = new \Client();
        $clients = $clientModel->getAll();
        $channelRepo = new ChannelRepository($this->db);

        $this->render('MarketingIntel/views/audit_create', [
            'clients' => $clients,
            'channels' => $channelRepo->allActive(),
            'error' => Session::getFlash('error'),
        ]);
    }

    public function store(Request $request, array $params = []): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail($request);

        $projectId = (int) $request->post('client_id');
        $projectRepo = new \Client();
        $project = $projectRepo->getById($projectId);

        if ($project === null) {
            Session::flash('error', 'Client not found.');
            Response::redirect("/audits/create");
            return;
        }

        $title = trim((string) $request->post('title', ''));
        if ($title === '') {
            Session::flash('error', 'Audit title is required.');
            Response::redirect("/audits/create");

            return;
        }

        $channelIds = array_map('intval', (array) $request->post('channel_ids', []));
        if ($channelIds === []) {
            Session::flash('error', 'Select at least one channel for this audit.');
            Response::redirect("/audits/create");

            return;
        }

        $knownNames = array_filter(array_map(
            'trim',
            explode(',', (string) $request->post('known_entity_names', ''))
        ));

        $auditRepo = new AuditRepository($this->db);
        $audit = $auditRepo->create($projectId, (int) $this->userId(), $title);
        $auditRepo->attachChannels((int) $audit['id'], $channelIds);
        $auditRepo->setKnownEntityNames((int) $audit['id'], array_values($knownNames));

        Response::redirect('/audits/' . $audit['id'] . '/cockpit');
    }

    public function cockpit(Request $request, array $params): void
    {
        $this->requireAuth();

        $audit = $this->authorizedAuditOrNotFound((int) $params['id']);
        if ($audit === null) {
            return;
        }

        $auditId = (int) $audit['id'];
        $auditRepo = new AuditRepository($this->db);
        $requirementRepo = new RequirementRepository($this->db);
        $collectionRepo = new CollectionAttemptRepository($this->db);

        $channels = $auditRepo->channelsForAudit($auditId);
        $channelIds = array_map(static fn (array $c): int => (int) $c['id'], $channels);
        $requirements = $requirementRepo->forChannels($channelIds);

        $latestAttempts = $collectionRepo->latestPerRequirement($auditId);
        $attemptsByRequirement = [];
        foreach ($latestAttempts as $attempt) {
            $attemptsByRequirement[(int) $attempt['requirement_id']] = $attempt;
        }

        $requirementIds = array_map(static fn (array $r): int => (int) $r['id'], $requirements);
        $dependencyEdges = $requirementRepo->dependenciesFor($requirementIds);

        $satisfiedByRequirementId = [];
        foreach ($attemptsByRequirement as $reqId => $attempt) {
            $satisfiedByRequirementId[$reqId] = $attempt['status'] === 'parsed';
        }

        $graph = new DependencyGraph($dependencyEdges, $satisfiedByRequirementId);

        // Group requirements by category for the cockpit's category
        // sections (RNS spec §4).
        $byCategory = [];
        foreach ($requirements as $requirement) {
            $byCategory[$requirement['category']][] = $requirement;
        }

        $progressCalc = new AuditProgressCalculator();
        $progressInput = [];
        foreach ($requirements as $requirement) {
            $progressInput[] = [
                'is_required' => (bool) $requirement['is_required'],
                'is_satisfied' => $satisfiedByRequirementId[(int) $requirement['id']] ?? false,
            ];
        }

        $completeness = $progressCalc->completenessPercent($progressInput);
        $reachableTier = $progressCalc->reachableTier($progressInput, (int) $audit['overall_confidence']);
        $auditRepo->updateProgress($auditId, $completeness, (int) $audit['overall_confidence'], $reachableTier);

        $recommendedNextId = $graph->recommendNext($requirementIds);

        $this->render('MarketingIntel/views/cockpit', [
            'audit' => $audit,
            'channels' => $channels,
            'requirementsByCategory' => $byCategory,
            'attemptsByRequirement' => $attemptsByRequirement,
            'satisfiedByRequirementId' => $satisfiedByRequirementId,
            'graph' => $graph,
            'completeness' => $completeness,
            'reachableTier' => $reachableTier,
            'recommendedNextId' => $recommendedNextId,
        ]);
    }

    public function provenance(Request $request, array $params): void
    {
        $this->requireAuth();

        $audit = $this->authorizedAuditOrNotFound((int) $params['id']);
        if ($audit === null) {
            return;
        }

        $collectionRepo = new CollectionAttemptRepository($this->db);
        $mergeRepo = new MergeDecisionRepository($this->db);
        $factRepo = new KnowledgeFactRepository($this->db);

        $this->render('MarketingIntel/views/provenance', [
            'audit' => $audit,
            'timeline' => $collectionRepo->timelineForAudit((int) $audit['id']),
            'variances' => $mergeRepo->unresolvedVarianceForAudit((int) $audit['id']),
            'facts' => $factRepo->allForAudit((int) $audit['id']),
        ]);
    }

    public function knowledge(Request $request, array $params): void
    {
        $this->requireAuth();

        $audit = $this->authorizedAuditOrNotFound((int) $params['id']);
        if ($audit === null) {
            return;
        }

        $factRepo = new KnowledgeFactRepository($this->db);
        $requirementRepo = new RequirementRepository($this->db);
        $auditRepo = new AuditRepository($this->db);

        $adapter = new KnowledgeBuilderAdapter($factRepo, $requirementRepo, $auditRepo);

        $this->render('MarketingIntel/views/knowledge', [
            'audit' => $audit,
            'facts' => $adapter->factsForAudit((int) $audit['id']),
            'coverage' => $adapter->coverageMap((int) $audit['id']),
        ]);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function authorizedAuditOrNotFound(int $auditId): ?array
    {
        $auditRepo = new AuditRepository($this->db);
        $audit = $auditRepo->find($auditId);

        if ($audit === null) {
            Response::notFound('Audit not found.');

            return null;
        }

        if ((int) $audit['user_id'] !== (int) $this->userId()) {
            Response::notFound('Audit not found.');

            return null;
        }

        return $audit;
    }

    public function process(Request $request, array $params = []): void
    {
        $this->requireAuth();
        $this->verifyCsrfOrFail($request);

        $auditId = (int) $request->post('audit_id');
        $audit = $this->authorizedAuditOrNotFound($auditId);
        if ($audit === null) {
            return;
        }

        // Export Knowledge
        $factRepo = new KnowledgeFactRepository($this->db);
        $requirementRepo = new RequirementRepository($this->db);
        $auditRepo = new AuditRepository($this->db);

        $adapter = new KnowledgeBuilderAdapter($factRepo, $requirementRepo, $auditRepo);
        $facts = $adapter->factsForAudit($auditId);

        // Get channel for plugin (just take the first one or default to google_ads)
        $channels = $auditRepo->channelsForAudit($auditId);
        $plugin = !empty($channels) ? strtolower(str_replace(' ', '_', $channels[0]['name'])) : 'google_ads';

        // Build knowledge.json in the EXACT shape
        // intelligence_engine/schemas/knowledge.schema.json requires (see
        // Bridge\KnowledgeContractBuilder's docblock for why this matters
        // -- the previous {"knowledge": $facts} dump made the Rule Engine
        // silently evaluate zero rules for every AI-collected audit).
        $manifestPath = BASE_PATH . "/intelligence_engine/plugins/{$plugin}/plugin.json";
        $pluginManifest = file_exists($manifestPath)
            ? (json_decode((string) file_get_contents($manifestPath), true) ?? [])
            : [];

        $contractBuilder = new \App\Modules\MarketingIntel\Bridge\KnowledgeContractBuilder();
        $knowledgeData = $contractBuilder->build($facts, $pluginManifest, (int) $audit['client_id'], $auditId);

        // Create workspace path
        $storagePath = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(BASE_PATH) . '/storage';
        $workspaceRoot = $storagePath . "/clients/{$audit['client_id']}/{$auditId}";
        $workspaceRoot = str_replace('\\', '/', $workspaceRoot);

        $knowledgeDir = $workspaceRoot . '/03-knowledge';
        if (!is_dir($knowledgeDir)) {
            mkdir($knowledgeDir, 0777, true);
        }

        file_put_contents($knowledgeDir . '/knowledge.json', json_encode($knowledgeData, JSON_PRETTY_PRINT));

        $auditRepo->updateStatus($auditId, 'ready');

        // Execute Node.js Engine with --skip-extraction
        $enginePath = BASE_PATH . '/intelligence_engine/index.js';
        $nodeBin = file_exists('/opt/alt/alt-nodejs18/root/bin/node') ? '/opt/alt/alt-nodejs18/root/bin/node' : 'node';
        $cmd = "$nodeBin \"$enginePath\" --workspace=\"$workspaceRoot\" --plugin=\"$plugin\" --skip-extraction 2>&1";
        
        exec($cmd, $output, $returnVar);

        $logPath = $workspaceRoot . "/05-logs/processing.log";
        if (!is_dir(dirname($logPath))) {
            mkdir(dirname($logPath), 0777, true);
        }
        $logEntry = "[" . date('Y-m-d H:i:s') . "] Pipeline Execution Log:\n" . implode("\n", $output) . "\n";
        file_put_contents($logPath, $logEntry, FILE_APPEND);

        if ($returnVar === 0) {
            $auditRepo->updateStatus($auditId, 'completed');
            
            // Sync recommendations and scores automatically to the client portal
            $contractPath = $workspaceRoot . '/09-contract/intelligence.json';
            if (file_exists($contractPath)) {
                $contract = json_decode((string)file_get_contents($contractPath), true);
                require_once BASE_PATH . '/app/Models/PortalModule.php';
                $modModel = new \PortalModule();
                $googleAdsMod = $modModel->getBySlug('google_ads');
                
                if ($googleAdsMod) {
                    $clientId = (int)$audit['client_id'];
                    $periodStart = date('Y-m-01', strtotime((string)$audit['created_at']));
                    $periodEnd = date('Y-m-t', strtotime((string)$audit['created_at']));
                    
                    // Save calculated Google Ads score to client_portal_scores
                    $scorecard = $contract['scorecard'] ?? [];
                    $scoreVal = isset($scorecard['overall_score']) ? (int)$scorecard['overall_score'] : 0;
                    
                    require_once BASE_PATH . '/app/Models/ClientPortalScore.php';
                    $scoreModel = new \ClientPortalScore();
                    $scoreModel->upsertForPeriod(
                        $clientId,
                        (int)$googleAdsMod['id'],
                        $periodStart,
                        $periodEnd,
                        $scoreVal,
                        $scorecard['grade'] ?? null,
                        $scorecard['health_status'] ?? null,
                        "Calculated by Marketing Intelligence Engine",
                        $contract['executive_summary']['biggest_wins'][0] ?? null,
                        $contract['executive_summary']['biggest_risks'][0] ?? null,
                        $contract['executive_summary']['immediate_actions'][0] ?? null,
                        (int)$_SESSION['user_id']
                    );
                    
                    // Sync Recommendations
                    $recommendations = $contract['recommendations']['recommendations'] ?? [];
                    require_once BASE_PATH . '/app/Models/ClientPortalRecommendation.php';
                    $recModel = new \ClientPortalRecommendation();
                    foreach ($recommendations as $rec) {
                        $recModel->syncOne(
                            $clientId,
                            (int)$googleAdsMod['id'],
                            (string)($rec['recommendation_id'] ?? md5(json_encode($rec))),
                            $auditId,
                            (string)($rec['what_to_change'] ?? ''),
                            (string)($rec['why_it_matters'] ?? ''),
                            in_array($rec['priority'] ?? 'Medium', ['High', 'Medium', 'Low'], true) ? $rec['priority'] : 'Medium',
                            $auditId,
                            '1.0',
                            $periodStart,
                            'intelligence_engine'
                        );
                    }
                }
            }
            
            // Log to Activity Center
            $this->db->query(
                "INSERT INTO activity_logs (user_id, client_id, action, created_at) VALUES (?, ?, ?, NOW())",
                [$_SESSION['user_id'], $audit['client_id'], "Generated Marketing Intelligence Report for " . $audit['title']]
            );
            
            Session::flash('success', 'Report generated successfully.');
        } else {
            Session::flash('error', 'Intelligence Engine failed. Check logs.');
        }
        
        // Redirect back to cockpit or to the report view if you have one
        Response::redirect("/audits/{$auditId}/cockpit");
    }
}

