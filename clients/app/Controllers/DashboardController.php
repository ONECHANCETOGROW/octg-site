<?php
require_once BASE_PATH . '/app/Models/Client.php';
require_once BASE_PATH . '/app/Models/Audit.php';
require_once BASE_PATH . '/app/Models/ActivityLog.php';

class DashboardController extends Controller {
    public function index() {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT COUNT(*) FROM clients");
        $clientCount = $stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) FROM audits");
        $auditCount = $stmt->fetchColumn();
        
        $stmt = $db->query("SELECT COUNT(*) FROM audits WHERE status = 'completed'");
        $reportsCount = $stmt->fetchColumn();
        
        $stmt = $db->query("SELECT a.*, u.username, c.business_name 
            FROM activity_logs a 
            LEFT JOIN users u ON a.user_id = u.id 
            LEFT JOIN clients c ON a.client_id = c.id 
            ORDER BY a.created_at DESC LIMIT 5");
        $recentActivity = $stmt->fetchAll();

        $this->view('layouts/main', [
            'title' => 'Dashboard - OCTG Intelligence',
            'content_view' => 'dashboard/index',
            'active_menu' => 'dashboard',
            'breadcrumbs' => [
                ['label' => 'Dashboard']
            ],
            'stats' => [
                'clients' => $clientCount,
                'audits' => $auditCount,
                'reports' => $reportsCount
            ],
            'activities' => $recentActivity
        ]);
    }
}
