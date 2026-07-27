<?php
class Logger {
    public static function log($action, $details = [], $clientId = null) {
        if (!isset($_SESSION['user_id'])) return;
        
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO activity_logs (client_id, user_id, action, details) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $clientId,
                $_SESSION['user_id'],
                $action,
                json_encode($details)
            ]);
        } catch (Exception $e) {
            // Silently fail logging rather than breaking the app
        }
    }
    
    public static function notify($userId, $title, $message) {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $title, $message]);
        } catch (Exception $e) {}
    }
    
    public static function getUnreadNotifications($userId) {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
