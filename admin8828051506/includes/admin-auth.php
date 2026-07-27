<?php
/* ==========================================================================
   ADMIN-AUTH.PHP — session guard. Every protected admin page requires this
   at the very top, before any output. Redirects to login.php if not
   authenticated. Also exposes octg_admin_user() and octg_admin_has_role().
   ========================================================================== */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../../api/_lib.php';

function octg_admin_user(): ?array {
    return $_SESSION['admin_user'] ?? null;
}

function octg_admin_require_login(): void {
    if (empty($_SESSION['admin_user'])) {
        header('Location: /admin8828051506/login.php');
        exit;
    }
}

/* Role hierarchy for simple "at least this level" checks. Full per-feature
   permission matrices aren't built yet — see DEPLOYMENT-NOTES.md — but every
   page can already gate itself by minimum role with this. */
const OCTG_ROLE_RANK = [
    'viewer' => 1, 'content_writer' => 2, 'marketing' => 2,
    'editor' => 3, 'admin' => 4, 'super_admin' => 5,
];

function octg_admin_has_role(string $minRole): bool {
    $user = octg_admin_user();
    if (!$user) return false;
    $userRank = OCTG_ROLE_RANK[$user['role']] ?? 0;
    $minRank = OCTG_ROLE_RANK[$minRole] ?? 999;
    return $userRank >= $minRank;
}

require_once __DIR__ . '/../../includes/activity-logger.php';

function octg_generate_csrf(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function octg_verify_csrf(): bool {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return true;
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

// Generate it so forms can use it
octg_generate_csrf();

octg_admin_require_login();
