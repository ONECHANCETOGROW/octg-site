<?php
require_once BASE_PATH . '/app/Models/ActivityLog.php';

class ActivityController extends Controller {
    public function index() {
        $model = new ActivityLog();
        $activities = $model->getAll();
        
        $this->view('layouts/main', [
            'title' => 'Activity Center - OCTG Intelligence',
            'content_view' => 'activity/index',
            'active_menu' => 'activity',
            'breadcrumbs' => [
                ['label' => 'Activity Center']
            ],
            'activities' => $activities
        ]);
    }
}
