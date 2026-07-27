<?php
class ActivityLog extends Model {
    public function getAll() {
        $stmt = $this->db->query("
            SELECT a.*, u.username, c.business_name 
            FROM activity_logs a 
            LEFT JOIN users u ON a.user_id = u.id 
            LEFT JOIN clients c ON a.client_id = c.id 
            ORDER BY a.created_at DESC
        ");
        return $stmt->fetchAll();
    }
}
