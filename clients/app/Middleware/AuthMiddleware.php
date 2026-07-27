<?php
class AuthMiddleware {
    public function handle() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Session timeout check (2 hours)
        $timeout = 7200;
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            session_unset();
            session_destroy();
            header('Location: /login?error=' . urlencode('Session expired. Please log in again.'));
            exit;
        }
        $_SESSION['last_activity'] = time();
        
        // Force password change check
        if (isset($_SESSION['force_password_change']) && $_SESSION['force_password_change'] == 1) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            // Clean up subfolder path if any
            $scriptName = dirname($_SERVER['SCRIPT_NAME']);
            if ($scriptName !== '/' && $scriptName !== '\\') {
                $uri = str_replace($scriptName, '', $uri);
            }
            $uri = '/' . trim($uri, '/');

            if ($uri !== '/change-password' && $uri !== '/logout' && $uri !== '/login') {
                header('Location: /change-password');
                exit;
            }
        }
        return true;
    }
}
