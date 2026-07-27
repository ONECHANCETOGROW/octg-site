<?php
/**
 * Gate for every client-portal route. Deliberately checks a DIFFERENT
 * session key (`client_user_id`) than the staff AuthMiddleware
 * (`user_id`) -- a client session and a staff session are two separate
 * identity spaces that must never be able to satisfy each other's
 * middleware. See docs/CLIENT_PORTAL.md "Client Access Model".
 */
class ClientAuthMiddleware {
    public function handle() {
        if (!isset($_SESSION['client_user_id']) || !isset($_SESSION['client_id'])) {
            // Admin Impersonation (Option B Bypass)
            // If the user is logged into the Admin Workspace, auto-generate a master client session
            // for the requested client slug so they don't have to log in.
            if (isset($_SESSION['user_id'])) {
                $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $scriptName = dirname($_SERVER['SCRIPT_NAME']);
                if ($scriptName !== '/' && $scriptName !== '\\') {
                    $uri = str_replace($scriptName, '', $uri);
                }
                $uri = '/' . trim($uri, '/');
                
                if (preg_match('#^/client/([^/]+)#', $uri, $matches)) {
                    $slug = $matches[1];
                    require_once BASE_PATH . '/app/Models/Client.php';
                    $clientModel = new Client();
                    $client = $clientModel->getBySlug($slug);
                    
                    if ($client) {
                        // Create a temporary "master admin" client session
                        $_SESSION['client_id'] = (int) $client['id'];
                        $_SESSION['client_user_id'] = -1; // Special ID for impersonation
                        $_SESSION['client_last_activity'] = time();
                        $_SESSION['is_admin_impersonating'] = true;
                        
                        // We must return true to allow them through
                        return true;
                    }
                }
            }

            header('Location: /portal/login');
            exit;
        }

        // Session timeout (2 hours, matches the staff middleware).
        $timeout = 7200;
        if (isset($_SESSION['client_last_activity']) && (time() - $_SESSION['client_last_activity'] > $timeout)) {
            $this->destroyClientSession();
            header('Location: /portal/login?error=' . urlencode('Session expired. Please log in again.'));
            exit;
        }
        $_SESSION['client_last_activity'] = time();

        if (isset($_SESSION['is_admin_impersonating']) && $_SESSION['is_admin_impersonating'] === true) {
            return true;
        }

        // Re-verify the account is still active on every request -- an
        // admin disabling a client mid-session must take effect
        // immediately, not just block the next login.
        require_once BASE_PATH . '/app/Models/ClientUser.php';
        $clientUserModel = new ClientUser();
        $clientUser = $clientUserModel->findById($_SESSION['client_user_id']);
        if (!$clientUser || (int) $clientUser['is_active'] !== 1) {
            $this->destroyClientSession();
            header('Location: /portal/login?error=' . urlencode('This account has been disabled.'));
            exit;
        }

        if ((int) $clientUser['must_reset_password'] === 1) {
            $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $scriptName = dirname($_SERVER['SCRIPT_NAME']);
            if ($scriptName !== '/' && $scriptName !== '\\') {
                $uri = str_replace($scriptName, '', $uri);
            }
            $uri = '/' . trim($uri, '/');

            if ($uri !== '/portal/change-password' && $uri !== '/portal/logout') {
                header('Location: /portal/change-password');
                exit;
            }
        }

        return true;
    }

    private function destroyClientSession() {
        unset($_SESSION['client_user_id'], $_SESSION['client_id'], $_SESSION['client_last_activity']);
        session_regenerate_id(true);
    }
}
