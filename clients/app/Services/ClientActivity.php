<?php
/**
 * Activity-log writer for actions taken by a logged-in CLIENT (as
 * opposed to Services/Logger.php, which is staff-only and gates on
 * $_SESSION['user_id']). Shared by every client-portal controller that
 * needs to record something on the Activity Timeline.
 */
class ClientActivity {
    public static function log($clientId, $clientUserId, $action, $details = null) {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare(
                "INSERT INTO activity_logs (user_id, client_user_id, client_id, action, details, created_at)
                 VALUES (NULL, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $clientUserId,
                $clientId,
                $action,
                $details !== null ? json_encode($details) : null,
            ]);
        } catch (\PDOException $e) {
            // Never let activity logging break the action it's logging.
        }
    }
}
