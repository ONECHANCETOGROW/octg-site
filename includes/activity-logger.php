<?php
/* ==========================================================================
   ACTIVITY-LOGGER.PHP — split out from admin/includes/admin-auth.php so it
   can be used by CLI/cron contexts (like admin/cron/run-audit.php) that
   deliberately don't load the full auth guard (there's no browser session
   to check in a cron job). admin-auth.php still includes this too, so
   nothing that already worked changes for regular logged-in admin pages.
   ========================================================================== */

if (!function_exists('octg_log_activity')) {
    function octg_log_activity(string $action, string $description = ''): void {
        $pdo = octg_db();
        if (!$pdo) return;
        $actor = 'System';
        if (function_exists('octg_admin_user')) {
            $user = octg_admin_user();
            if ($user) $actor = $user['name'];
        }
        try {
            $stmt = $pdo->prepare('INSERT INTO activity_log (action, description, actor) VALUES (:a, :d, :actor)');
            $stmt->execute([':a' => $action, ':d' => $description, ':actor' => $actor]);
        } catch (Throwable $e) {
            // Activity logging is diagnostic, not critical — never break the request over it.
        }
    }
}
