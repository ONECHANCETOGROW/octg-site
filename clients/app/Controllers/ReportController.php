<?php
require_once BASE_PATH . '/app/Models/Audit.php';

class ReportController extends Controller {
    public function index() {
        // Fetch all audits that have reached 'completed' or are 'processing'
        $auditModel = new Audit();
        // Fetch all audits from old system
        $db = Database::getInstance();
        $stmt = $db->query("SELECT a.id, a.name, a.channel, a.audit_month, a.status, a.client_id, c.business_name, a.created_at, 'old' as audit_type 
                            FROM audits a 
                            JOIN clients c ON a.client_id = c.id 
                            WHERE a.status IN ('queued', 'processing', 'completed')");
        $oldReports = $stmt->fetchAll();

        // Fetch all audits from new MI system
        $stmt2 = $db->query("SELECT a.id, a.title as name, 'Google Ads' as channel, 'Current' as audit_month, a.status, a.client_id, c.business_name, a.created_at, 'mi' as audit_type 
                             FROM mi_audits a 
                             JOIN clients c ON a.client_id = c.id 
                             WHERE a.status IN ('ready', 'completed')");
        $miReports = $stmt2->fetchAll();

        $reports = array_merge($oldReports, $miReports);
        usort($reports, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        $this->view('layouts/main', [
            'title' => 'Report Library - OCTG Intelligence',
            'content_view' => 'reports/index',
            'active_menu' => 'reports',
            'breadcrumbs' => [
                ['label' => 'Reports']
            ],
            'reports' => $reports
        ]);
    }
    
    public function viewReport() {
        $id = $_GET['id'] ?? null;
        $type = $_GET['type'] ?? 'old';
        
        if (!$id) $this->redirect('/reports');
        
        $db = Database::getInstance();
        if ($type === 'mi') {
            $stmt = $db->prepare("SELECT a.id, a.title as name, a.client_id, c.business_name 
                                FROM mi_audits a 
                                JOIN clients c ON a.client_id = c.id 
                                WHERE a.id = ?");
            $stmt->execute([$id]);
            $audit = $stmt->fetch();
        } else {
            $auditModel = new Audit();
            $audit = $auditModel->getById($id);
            // ensure business_name is set for old audits if missing
            if ($audit && !isset($audit['business_name'])) {
                $stmt = $db->query("SELECT business_name FROM clients WHERE id = ?", [$audit['client_id']]);
                $c = $stmt->fetch();
                if ($c) $audit['business_name'] = $c['business_name'];
            }
        }
        
        if (!$audit) $this->redirect('/reports');
        
        require_once BASE_PATH . '/app/Services/Storage/Storage.php';
        $contractPath = "clients/{$audit['client_id']}/{$id}/09-contract/intelligence.json";
        
        $intelligence = null;
        if (Storage::exists($contractPath)) {
            $intelligence = json_decode(Storage::read($contractPath), true);
        }
        
        $this->view('layouts/main', [
            'title' => 'Intelligence Report - ' . $audit['name'],
            'content_view' => 'reports/view',
            'active_menu' => 'reports',
            'breadcrumbs' => [
                ['label' => 'Reports', 'url' => '/reports'],
                ['label' => $audit['name']]
            ],
            'audit' => $audit,
            'intelligence' => $intelligence
        ]);
    }
}
