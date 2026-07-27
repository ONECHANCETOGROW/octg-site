<?php
require_once BASE_PATH . '/app/Models/Audit.php';
require_once BASE_PATH . '/app/Models/Client.php';

class AuditController extends Controller {
    public function wizard() {
        $clientModel = new Client();
        $clients = $clientModel->getAll();

        $this->view('layouts/main', [
            'title' => 'New Audit - OCTG Intelligence',
            'content_view' => 'audits/wizard',
            'active_menu' => 'audits',
            'breadcrumbs' => [
                ['label' => 'Audits'],
                ['label' => 'New Audit']
            ],
            'clients' => $clients
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auditModel = new Audit();
            $data = [
                'client_id' => $_POST['client_id'],
                'name' => $_POST['name'],
                'audit_month' => $_POST['audit_month'],
                'channel' => $_POST['channel'],
                'notes' => $_POST['notes'] ?? '',
                'status' => 'uploading'
            ];
            $auditId = $auditModel->create($data);
            
            // Redirect to step 3: Upload Reports
            $this->redirect("/audits/upload?id=" . $auditId);
        }
    }
    
    public function upload() {
        $auditId = $_GET['id'] ?? null;
        if (!$auditId) $this->redirect('/dashboard');
        
        $auditModel = new Audit();
        $audit = $auditModel->getById($auditId);
        if (!$audit) $this->redirect('/dashboard');

        $this->view('layouts/main', [
            'title' => 'Upload Files - ' . $audit['name'],
            'content_view' => 'audits/upload',
            'active_menu' => 'audits',
            'breadcrumbs' => [
                ['label' => 'Audits'],
                ['label' => $audit['name'], 'url' => '/audits/show?id=' . $audit['id']],
                ['label' => 'Upload Files']
            ],
            'audit' => $audit
        ]);
    }

    public function show() {
        $auditId = $_GET['id'] ?? null;
        if (!$auditId) $this->redirect('/dashboard');
        
        $auditModel = new Audit();
        $audit = $auditModel->getById($auditId);
        if (!$audit) $this->redirect('/dashboard');

        // We will fetch uploads here later

        $this->view('layouts/main', [
            'title' => 'Audit Workspace - ' . $audit['name'],
            'content_view' => 'audits/show',
            'active_menu' => 'audits',
            'breadcrumbs' => [
                ['label' => 'Audits'],
                ['label' => $audit['name']]
            ],
            'audit' => $audit
        ]);
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auditId = $_POST['audit_id'] ?? null;
            if (!$auditId) {
                echo json_encode(['success' => false, 'error' => 'No audit id provided']);
                return;
            }

            $auditModel = new Audit();
            $audit = $auditModel->getById($auditId);
            if (!$audit) {
                echo json_encode(['success' => false, 'error' => 'Invalid audit id']);
                return;
            }

            // Set status to queued/processing
            $auditModel->update($auditId, ['status' => 'processing']);
            
            // Generate absolute workspace path
            $storagePath = defined('STORAGE_PATH') ? STORAGE_PATH : dirname(BASE_PATH) . '/storage';
            $workspaceRoot = $storagePath . "/clients/{$audit['client_id']}/{$auditId}";
            $workspaceRoot = str_replace('\\', '/', $workspaceRoot);
            
            // Execute Node.js Engine
            $enginePath = BASE_PATH . '/intelligence_engine/index.js';
            $plugin = $audit['channel'] ?? 'google_ads';
            
            $cmd = "node \"$enginePath\" --workspace=\"$workspaceRoot\" --plugin=\"$plugin\" 2>&1";
            
            // Execute synchronously for V1
            exec($cmd, $output, $returnVar);
            
            // Append output to processing.log
            $logPath = "clients/{$audit['client_id']}/{$auditId}/05-logs/processing.log";
            $existingLog = Storage::exists($logPath) ? Storage::read($logPath) : "";
            $logEntry = "[" . date('Y-m-d H:i:s') . "] Pipeline Execution Log:\n" . implode("\n", $output) . "\n";
            Storage::save($logPath, $existingLog . $logEntry);

            if ($returnVar === 0) {
                $auditModel->update($auditId, ['status' => 'completed']);
                echo json_encode(['success' => true]);
            } else {
                $auditModel->update($auditId, ['status' => 'failed']);
                echo json_encode(['success' => false, 'error' => 'Intelligence Engine failed. Check logs.', 'output' => $output]);
            }
        }
    }
}
