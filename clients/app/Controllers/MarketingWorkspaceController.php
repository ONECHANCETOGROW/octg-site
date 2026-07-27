<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Models/Client.php';
require_once BASE_PATH . '/app/Models/PortalModule.php';
require_once BASE_PATH . '/app/Models/ClientPortalMetric.php';
require_once BASE_PATH . '/app/Models/ClientPortalScore.php';
require_once BASE_PATH . '/app/Models/ClientPortalRecommendation.php';
require_once BASE_PATH . '/app/Modules/ClientPortal/ModuleFieldDefinitions.php';

class MarketingWorkspaceController extends Controller
{
    private function requireStaffAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    public function index()
    {
        $this->requireStaffAuth();

        $clientId = $_GET['id'] ?? null;
        if (!$clientId) {
            $this->redirect('/clients');
        }

        $clientModel = new Client();
        $client = $clientModel->getById($clientId);
        if (!$client) {
            $this->redirect('/clients');
        }

        // Resolve active period (month)
        $period = $_GET['period'] ?? date('Y-m-01');
        $periodStart = date('Y-m-01', strtotime($period));
        $periodEnd = date('Y-m-t', strtotime($period));

        $moduleModel = new PortalModule();
        $modules = $moduleModel->getActive(); // Only load active registry modules

        $metricModel = new ClientPortalMetric();
        $scoreModel = new ClientPortalScore();
        $recModel = new ClientPortalRecommendation();

        // Load metrics and scores for this period
        $metricsData = [];
        $scoresData = [];

        foreach ($modules as $mod) {
            $slug = $mod['slug'];
            if ($slug === 'social') {
                $platforms = \App\Modules\ClientPortal\ModuleFieldDefinitions::socialPlatforms();
                foreach ($platforms as $platSlug => $platLabel) {
                    $row = $metricModel->getForPeriod($clientId, $mod['id'], $platSlug, $periodStart);
                    $metricsData[$slug][$platSlug] = $row ? json_decode($row['data_json'], true) : [];
                }
            } else {
                $row = $metricModel->getForPeriod($clientId, $mod['id'], '', $periodStart);
                $metricsData[$slug] = $row ? json_decode($row['data_json'], true) : [];
            }

            // Scores are per-module
            $scoreRow = $scoreModel->getForPeriod($clientId, $mod['id'], $periodStart);
            $scoresData[$slug] = $scoreRow ?: null;
        }

        // Get overall health score for this period
        $healthModule = $moduleModel->getBySlug('marketing_health');
        $overallScoreRow = $healthModule ? $scoreModel->getForPeriod($clientId, $healthModule['id'], $periodStart) : null;

        // Load notes & goals for this period
        $db = Database::getInstance();
        $notesStmt = $db->prepare(
            "SELECT * FROM client_portal_notes WHERE client_id = ? AND period_start = ? AND note_type = 'note' LIMIT 1"
        );
        $notesStmt->execute([$clientId, $periodStart]);
        $note = $notesStmt->fetch();

        $goalsStmt = $db->prepare(
            "SELECT * FROM client_portal_notes WHERE client_id = ? AND period_start = ? AND note_type = 'goal' LIMIT 1"
        );
        $goalsStmt->execute([$clientId, $periodStart]);
        $goal = $goalsStmt->fetch();

        // Load recommendations
        $recommendations = $recModel->forClient($clientId);

        // Load custom timeline events
        $timelineStmt = $db->prepare(
            "SELECT * FROM client_portal_timeline_events WHERE client_id = ? ORDER BY event_date DESC"
        );
        $timelineStmt->execute([$clientId]);
        $timelineEvents = $timelineStmt->fetchAll();

        $prompts = [];
        $promptDir = BASE_PATH . '/app/Prompts/google_ads/';
        if (is_dir($promptDir)) {
            // Priming prompts run FIRST, before any of the real data
            // questions below -- they're pasted into the AI advisor at the
            // start of the conversation to set the account-analyst role,
            // lock in the exact JSON-only response format (with a worked
            // example), and confirm what data the advisor is actually
            // working from. This is what makes the 7 data-collection
            // prompts below get consistent, parseable JSON back instead of
            // prose the advisor free-styled on the first real question.
            // Like every other prompt here, these are plain files under
            // app/Prompts/google_ads/ -- not hardcoded PHP strings -- so
            // they stay editable without a code change.
            $primingFiles = [
                'priming_1' => 'priming_1_role_and_scope.md',
                'priming_2' => 'priming_2_format_contract.md',
                'priming_3' => 'priming_3_data_source_check.md',
            ];
            foreach ($primingFiles as $key => $file) {
                if (file_exists($promptDir . $file)) {
                    $prompts[$key] = trim(file_get_contents($promptDir . $file));
                }
            }

            $system = file_exists($promptDir . 'system.md') ? file_get_contents($promptDir . 'system.md') : '';
            $files = ['kpis' => 'performance.md', 'campaigns' => 'campaigns.md', 'keywords' => 'keywords.md', 'search_terms' => 'search_terms.md', 'recommendations' => 'recommendations.md', 'opportunities' => 'opportunities.md', 'executive_summary' => 'executive_summary.md'];
            foreach ($files as $key => $file) {
                if (file_exists($promptDir . $file)) {
                    $prompts[$key] = trim($system . "\n\n" . file_get_contents($promptDir . $file) . "\n\nData to analyze:\n[PASTE DATA HERE]");
                }
            }
        }

        $this->view('layouts/main', [
            'title' => 'Marketing Workspace - ' . $client['business_name'],
            'content_view' => 'clients/marketing_workspace',
            'active_menu' => 'clients',
            'breadcrumbs' => [
                ['label' => 'Clients', 'url' => '/clients'],
                ['label' => $client['business_name'], 'url' => '/clients/edit?id=' . $client['id']],
                ['label' => 'Marketing Workspace'],
            ],
            'client' => $client,
            'period' => $periodStart,
            'modules' => $modules,
            'metricsData' => $metricsData,
            'scoresData' => $scoresData,
            'overallScoreRow' => $overallScoreRow,
            'note' => $note,
            'goal' => $goal,
            'recommendations' => $recommendations,
            'timelineEvents' => $timelineEvents,
            'prompts' => $prompts,
            'csrf_token' => $this->generateCSRF(),
        ]);
    }

    public function saveMetrics()
    {
        $this->requireStaffAuth();
        $this->verifyCSRF();

        $clientId = (int)($_POST['client_id'] ?? 0);
        $period = $_POST['period'] ?? date('Y-m-01');
        $periodStart = date('Y-m-01', strtotime($period));
        $periodEnd = date('Y-m-t', strtotime($period));
        $moduleSlug = $_POST['module'] ?? '';

        $moduleModel = new PortalModule();
        $mod = $moduleModel->getBySlug($moduleSlug);
        if (!$mod) {
            $this->redirect("/clients");
        }

        $fields = \App\Modules\ClientPortal\ModuleFieldDefinitions::forModule($moduleSlug);
        $platform = $_POST['platform'] ?? '';

        $data = [];
        foreach ($fields as $field) {
            $raw = $_POST[$field['key']] ?? '';
            $data[$field['key']] = $field['type'] === 'textarea'
                ? array_values(array_filter(array_map('trim', explode("\n", (string) $raw))))
                : (is_numeric($raw) ? (float) $raw : $raw);
        }

        $metricModel = new ClientPortalMetric();
        $metricModel->upsertForPeriod(
            $clientId,
            $mod['id'],
            $platform,
            $periodStart,
            $periodEnd,
            $data,
            (int)$_SESSION['user_id']
        );

        // Log action
        require_once BASE_PATH . '/app/Services/ClientActivity.php';
        ClientActivity::log($clientId, null, "Updated raw metrics for {$mod['name']} ($periodStart) via Marketing Workspace");

        $this->redirect("/clients/portal-data?id={$clientId}&period={$periodStart}&success=1");
    }

    public function ingestAI()
    {
        $this->requireStaffAuth();
        $this->verifyCSRF();

        $clientId = (int)($_POST['client_id'] ?? 0);
        $period = $_POST['period'] ?? date('Y-m-01');
        $periodStart = date('Y-m-01', strtotime($period));
        $moduleSlug = $_POST['module'] ?? '';
        
        require_once BASE_PATH . '/app/Services/AIParser/NLParser.php';

        $db = Database::getInstance();
        $parser = new \App\Services\AIParser\NLParser();

        // 1. Create a job
        $stmt = $db->prepare("INSERT INTO client_ai_import_jobs (client_id, module, provider, status, period_start, created_by) VALUES (?, ?, 'google_ads_advisor', 'pending_review', ?, ?)");
        $stmt->execute([$clientId, $moduleSlug, $periodStart, $_SESSION['user_id'] ?? 1]);
        $jobId = $db->lastInsertId();

        $parsedSections = [];
        $sections = ['kpis', 'campaigns', 'keywords', 'search_terms', 'recommendations', 'opportunities', 'executive_summary'];

        // 2. Extract structured data using NLParser
        foreach ($sections as $sec) {
            if (!empty($_POST[$sec])) {
                $rawText = $_POST[$sec];
                // Save raw import
                $ins = $db->prepare("INSERT INTO client_ai_imports (job_id, section, raw_response) VALUES (?, ?, ?)");
                $ins->execute([$jobId, $sec, $rawText]);
                $importId = $db->lastInsertId();

                $parsed = $parser->parse($rawText, $sec);
                if (!empty($parsed)) {
                    $parsedSections[$sec] = $parsed;
                    // Update parsed json containing confidence metadata
                    $upd = $db->prepare("UPDATE client_ai_imports SET parsed_json = ? WHERE id = ?");
                    $upd->execute([json_encode($parsed), $importId]);
                }
            }
        }

        if (empty($parsedSections)) {
            $db->prepare("UPDATE client_ai_import_jobs SET status = 'failed' WHERE id = ?")->execute([$jobId]);
            $this->redirect("/clients/portal-data?id={$clientId}&period={$periodStart}&error=Parsing failed. Confidence too low.");
            return;
        }

        // Redirect to Validation Screen
        $this->redirect("/clients/portal-data/review-ai?job_id={$jobId}");
    }

    public function reviewAI()
    {
        $this->requireStaffAuth();
        $jobId = (int)($_GET['job_id'] ?? 0);
        
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM client_ai_import_jobs WHERE id = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();
        if (!$job || $job['status'] !== 'pending_review') {
            $this->redirect("/clients");
        }

        $stmt2 = $db->prepare("SELECT * FROM client_ai_imports WHERE job_id = ?");
        $stmt2->execute([$jobId]);
        $imports = $stmt2->fetchAll();
        
        // Fetch existing facts for comparison
        $stmt3 = $db->prepare("SELECT * FROM mi_knowledge_facts WHERE audit_id IN (SELECT id FROM mi_audits WHERE client_id = ? AND created_at >= ?)");
        $stmt3->execute([$job['client_id'], $job['period_start']]);
        $existingFacts = $stmt3->fetchAll();

        // Mock getting the client for header info
        require_once BASE_PATH . '/app/Models/Client.php';
        $clientModel = new Client();
        $client = $clientModel->getById($job['client_id']);

        $this->view('layouts/main', [
            'title' => 'Review AI Ingestion - ' . $client['business_name'],
            'content_view' => 'clients/review_ai',
            'active_menu' => 'clients',
            'client' => $client,
            'job' => $job,
            'imports' => $imports,
            'existingFacts' => $existingFacts,
            'periodStart' => $job['period_start'],
            'moduleSlug' => $job['module'],
            'csrf_token' => $this->generateCSRF(),
        ]);
    }

    public function confirmAI()
    {
        $this->requireStaffAuth();
        $this->verifyCSRF();

        $jobId = (int)($_POST['job_id'] ?? 0);
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM client_ai_import_jobs WHERE id = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();
        
        if (!$job || $job['status'] !== 'pending_review') {
            $this->redirect("/clients");
        }

        $clientId = $job['client_id'];
        $moduleSlug = $job['module'];
        $periodStart = $job['period_start'];

        require_once BASE_PATH . '/app/Services/AIParser/AuditMerger.php';
        $merger = new \App\Services\AIParser\AuditMerger();

        // Reconstruct parsedSections stripped of confidence, relying on what the admin POSTed
        $parsedSections = [];
        $sections = ['kpis', 'campaigns', 'keywords', 'search_terms', 'recommendations', 'opportunities', 'executive_summary'];
        
        foreach ($sections as $sec) {
            if (isset($_POST[$sec]) && is_array($_POST[$sec])) {
                $parsedSections[$sec] = $_POST[$sec]; // Raw validated values without confidence wrappers
            }
        }

        $auditId = $merger->mergeAndCreateAudit($clientId, $moduleSlug, $periodStart, $parsedSections);

        require_once BASE_PATH . '/app/Modules/MarketingIntel/IntelAuditController.php';
        $_POST['audit_id'] = $auditId;
        $req = new \App\Core\Request();
        
        $intelController = new \App\Modules\MarketingIntel\IntelAuditController();
        try {
            $intelController->process($req, []);
            $db->prepare("UPDATE client_ai_import_jobs SET status = 'completed', completed_at = NOW() WHERE id = ?")->execute([$jobId]);
        } catch (\Exception $e) {
            $db->prepare("UPDATE client_ai_import_jobs SET status = 'failed', completed_at = NOW() WHERE id = ?")->execute([$jobId]);
        }

        require_once BASE_PATH . '/app/Services/ClientActivity.php';
        \ClientActivity::log($clientId, null, "Completed & Validated AI Data Ingestion for {$moduleSlug} ($periodStart)");

        $this->redirect("/clients/portal-data?id={$clientId}&period={$periodStart}&success=1");
    }

    public function saveScores()
    {
        $this->requireStaffAuth();
        $this->verifyCSRF();

        $clientId = (int)($_POST['client_id'] ?? 0);
        $period = $_POST['period'] ?? date('Y-m-01');
        $periodStart = date('Y-m-01', strtotime($period));
        $periodEnd = date('Y-m-t', strtotime($period));

        $moduleModel = new PortalModule();
        $modules = $moduleModel->getActive();
        $scoreModel = new ClientPortalScore();

        foreach ($modules as $mod) {
            $slug = $mod['slug'];
            if (isset($_POST['scores'][$slug])) {
                $scoreVal = (int)($_POST['scores'][$slug]['score'] ?? 0);
                $grade = $_POST['scores'][$slug]['grade'] ?? '';
                $healthStatus = $_POST['scores'][$slug]['health_status'] ?? '';
                $trend = $_POST['scores'][$slug]['trend'] ?? '';
                $win = $_POST['scores'][$slug]['biggest_win'] ?? '';
                $risk = $_POST['scores'][$slug]['biggest_risk'] ?? '';
                $priority = $_POST['scores'][$slug]['priority_this_month'] ?? '';

                $scoreModel->upsertForPeriod(
                    $clientId,
                    $mod['id'],
                    $periodStart,
                    $periodEnd,
                    $scoreVal,
                    $grade ?: null,
                    $healthStatus ?: null,
                    $trend ?: null,
                    $win ?: null,
                    $risk ?: null,
                    $priority ?: null,
                    (int)$_SESSION['user_id']
                );

                // Sync manual score back to intelligence.json for Google Ads so the Client Dashboard reflects it
                if ($slug === 'google_ads') {
                    $stmt = $db->prepare("SELECT id FROM mi_audits WHERE client_id = ? ORDER BY id DESC LIMIT 1");
                    $stmt->execute([$clientId]);
                    $audit = $stmt->fetch();
                    if ($audit) {
                        $auditId = $audit['id'];
                        $storagePath = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(BASE_PATH) . '/storage';
                        $contractFile = $storagePath . "/clients/{$clientId}/{$auditId}/09-contract/intelligence.json";
                        if (file_exists($contractFile)) {
                            $json = json_decode(file_get_contents($contractFile), true);
                            if (isset($json['scorecard'])) {
                                $json['scorecard']['overall_score'] = $scoreVal;
                                $json['scorecard']['grade'] = $grade ?: $json['scorecard']['grade'];
                                $json['scorecard']['health_status'] = $healthStatus ?: $json['scorecard']['health_status'];
                                file_put_contents($contractFile, json_encode($json, JSON_PRETTY_PRINT));
                            }
                        }
                    }
                }
            }
        }

        // Dynamically compute Overall Marketing Health Score (average of all active and populated scores for this period)
        $scoresToAverage = [];
        foreach ($modules as $mod) {
            $scoreRow = $scoreModel->getForPeriod($clientId, $mod['id'], $periodStart);
            if ($scoreRow && $scoreRow['score'] > 0) {
                $scoresToAverage[] = (int)$scoreRow['score'];
            }
        }

        if (!empty($scoresToAverage)) {
            $overallScore = (int)round(array_sum($scoresToAverage) / count($scoresToAverage));
            
            // Map score to standard grades & status
            if ($overallScore >= 90) {
                $grade = 'A';
                $status = 'Excellent';
            } elseif ($overallScore >= 80) {
                $grade = 'B';
                $status = 'Good';
            } elseif ($overallScore >= 70) {
                $grade = 'C';
                $status = 'Needs Attention';
            } else {
                $grade = 'D';
                $status = 'Critical';
            }

            $healthModule = $moduleModel->getBySlug('marketing_health');
            if ($healthModule) {
                // Determine wins / risks based on individual modules
                $scoreModel->upsertForPeriod(
                    $clientId,
                    $healthModule['id'],
                    $periodStart,
                    $periodEnd,
                    $overallScore,
                    $grade,
                    $status,
                    "Dynamic score calculated from active modules",
                    $_POST['overall_biggest_win'] ?? "Everything stable.",
                    $_POST['overall_biggest_risk'] ?? "None identified.",
                    $_POST['overall_priority'] ?? "Continue optimization.",
                    (int)$_SESSION['user_id']
                );
            }
        }

        // Log action
        require_once BASE_PATH . '/app/Services/ClientActivity.php';
        ClientActivity::log($clientId, null, "Updated scores and calculated Overall Marketing Health ($periodStart) via Marketing Workspace");

        $this->redirect("/clients/portal-data?id={$clientId}&period={$periodStart}&success=1");
    }

    public function saveNotes()
    {
        $this->requireStaffAuth();
        $this->verifyCSRF();

        $clientId = (int)($_POST['client_id'] ?? 0);
        $period = $_POST['period'] ?? date('Y-m-01');
        $periodStart = date('Y-m-01', strtotime($period));

        $noteBody = trim($_POST['note'] ?? '');
        $goalBody = trim($_POST['goal'] ?? '');

        $db = Database::getInstance();

        // Note
        $db->prepare("DELETE FROM client_portal_notes WHERE client_id = ? AND period_start = ? AND note_type = 'note'")->execute([$clientId, $periodStart]);
        if ($noteBody !== '') {
            $ins = $db->prepare("INSERT INTO client_portal_notes (client_id, period_start, note_type, body, entered_by_user_id) VALUES (?, ?, 'note', ?, ?)");
            $ins->execute([$clientId, $periodStart, $noteBody, $_SESSION['user_id']]);
        }

        // Goal
        $db->prepare("DELETE FROM client_portal_notes WHERE client_id = ? AND period_start = ? AND note_type = 'goal'")->execute([$clientId, $periodStart]);
        if ($goalBody !== '') {
            $ins = $db->prepare("INSERT INTO client_portal_notes (client_id, period_start, note_type, body, entered_by_user_id) VALUES (?, ?, 'goal', ?, ?)");
            $ins->execute([$clientId, $periodStart, $goalBody, $_SESSION['user_id']]);
        }

        require_once BASE_PATH . '/app/Services/ClientActivity.php';
        ClientActivity::log($clientId, null, "Updated monthly notes and goals ($periodStart) via Marketing Workspace");

        $this->redirect("/clients/portal-data?id={$clientId}&period={$periodStart}&success=1");
    }

    public function addRecommendation()
    {
        $this->requireStaffAuth();
        $this->verifyCSRF();

        $clientId = (int)($_POST['client_id'] ?? 0);
        $period = $_POST['period'] ?? date('Y-m-01');
        $periodStart = date('Y-m-01', strtotime($period));

        $moduleSlug = $_POST['module'] ?? '';
        $what = trim($_POST['what_to_change'] ?? '');
        $why = trim($_POST['why_it_matters'] ?? '');
        $priority = $_POST['priority'] ?? 'Medium';

        if ($what !== '') {
            $recModel = new ClientPortalRecommendation();
            $recModel->addManual($clientId, $moduleSlug, $what, $why, $priority, $periodStart);
        }

        $this->redirect("/clients/portal-data?id={$clientId}&period={$periodStart}&success=1");
    }

    public function deleteRecommendation()
    {
        $this->requireStaffAuth();
        $this->verifyCSRF();

        $clientId = (int)($_POST['client_id'] ?? 0);
        $period = $_POST['period'] ?? date('Y-m-01');
        $periodStart = date('Y-m-01', strtotime($period));
        $id = (int)($_POST['recommendation_id'] ?? 0);

        if ($id > 0) {
            $recModel = new ClientPortalRecommendation();
            $recModel->delete($id, $clientId);
        }

        $this->redirect("/clients/portal-data?id={$clientId}&period={$periodStart}&success=1");
    }

    public function addTimelineEvent()
    {
        $this->requireStaffAuth();
        $this->verifyCSRF();

        $clientId = (int)($_POST['client_id'] ?? 0);
        $period = $_POST['period'] ?? date('Y-m-01');
        $periodStart = date('Y-m-01', strtotime($period));

        $date = $_POST['event_date'] ?? date('Y-m-d');
        $label = trim($_POST['label'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $icon = $_POST['icon'] ?? 'calendar';

        if ($label !== '') {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                "INSERT INTO client_portal_timeline_events (client_id, event_date, label, description, icon) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$clientId, $date, $label, $desc, $icon]);
        }

        $this->redirect("/clients/portal-data?id={$clientId}&period={$periodStart}&success=1");
    }

    public function deleteTimelineEvent()
    {
        $this->requireStaffAuth();
        $this->verifyCSRF();

        $clientId = (int)($_POST['client_id'] ?? 0);
        $period = $_POST['period'] ?? date('Y-m-01');
        $periodStart = date('Y-m-01', strtotime($period));
        $id = (int)($_POST['event_id'] ?? 0);

        if ($id > 0) {
            $db = Database::getInstance();
            $stmt = $db->prepare("DELETE FROM client_portal_timeline_events WHERE id = ? AND client_id = ?");
            $stmt->execute([$id, $clientId]);
        }

        $this->redirect("/clients/portal-data?id={$clientId}&period={$periodStart}&success=1");
    }
}
