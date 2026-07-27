<?php
require_once BASE_PATH . '/app/Models/Notification.php';

class NotificationController extends Controller {
    public function read() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if ($id && isset($_SESSION['user_id'])) {
                $model = new Notification();
                $model->markAsRead($id, $_SESSION['user_id']);
                echo json_encode(['success' => true]);
                return;
            }
        }
        echo json_encode(['success' => false]);
    }
    
    public function index() {
        $model = new Notification();
        $notifications = $model->getAllForUser($_SESSION['user_id']);
        
        $this->view('layouts/main', [
            'title' => 'Notifications - OCTG Intelligence',
            'content_view' => 'notifications/index',
            'active_menu' => 'dashboard',
            'breadcrumbs' => [
                ['label' => 'Notifications']
            ],
            'notifications' => $notifications
        ]);
    }
}
