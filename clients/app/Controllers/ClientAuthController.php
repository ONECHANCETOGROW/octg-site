<?php
require_once BASE_PATH . '/app/Models/ClientUser.php';
require_once BASE_PATH . '/app/Models/Client.php';

/**
 * Login/logout/password flows for CLIENTS -- entirely separate from
 * AuthController, which is staff-only. See docs/CLIENT_PORTAL.md.
 */
class ClientAuthController extends Controller {

    public function showLogin($error = '', $success = '') {
        if (isset($_SESSION['client_user_id'])) {
            $this->redirectToDashboard();
            return;
        }

        if (empty($error) && isset($_GET['error'])) {
            $error = $_GET['error'];
        }

        $this->view('portal/layouts/auth', [
            'title' => 'Client Login - One Chance To Grow',
            'subtitle' => 'Sign in to your Marketing Command Center',
            'content_view' => 'portal/auth/login',
            'csrf_token' => $this->generateCSRF(),
            'error' => $error,
            'success' => $success,
        ]);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $this->verifyCSRF();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $clientUserModel = new ClientUser();
        $clientUser = $clientUserModel->findByEmail($email);

        if (!$clientUser || !password_verify($password, $clientUser['password_hash'])) {
            $this->showLogin('Invalid email or password.');
            return;
        }

        if ((int) $clientUser['is_active'] !== 1) {
            $this->showLogin('This account has been disabled. Contact your account manager.');
            return;
        }

        session_regenerate_id(true);

        $_SESSION['client_user_id'] = (int) $clientUser['id'];
        $_SESSION['client_id'] = (int) $clientUser['client_id'];
        $_SESSION['client_email'] = $clientUser['email'];
        $_SESSION['client_last_activity'] = time();

        $clientUserModel->recordLogin($clientUser['id']);

        require_once BASE_PATH . '/app/Services/ClientActivity.php';
        ClientActivity::log((int) $clientUser['client_id'], (int) $clientUser['id'], 'Logged in');

        $this->redirectToDashboard();
    }

    public function logout() {
        unset(
            $_SESSION['client_user_id'],
            $_SESSION['client_id'],
            $_SESSION['client_email'],
            $_SESSION['client_last_activity']
        );
        session_regenerate_id(true);
        header('Location: /portal/login');
        exit;
    }

    public function showChangePassword($error = '', $success = '') {
        $this->view('portal/layouts/auth', [
            'title' => 'Change Password',
            'subtitle' => 'Please set a new password to continue',
            'content_view' => 'portal/auth/change_password',
            'csrf_token' => $this->generateCSRF(),
            'error' => $error,
            'success' => $success,
        ]);
    }

    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $this->verifyCSRF();

        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new !== $confirm) {
            $this->showChangePassword('New passwords do not match.');
            return;
        }
        if (strlen($new) < 8) {
            $this->showChangePassword('Password must be at least 8 characters.');
            return;
        }

        $clientUserModel = new ClientUser();
        $clientUser = $clientUserModel->findById($_SESSION['client_user_id']);

        // First-time setup (must_reset_password) has no old password worth
        // checking against a temp one the admin generated; otherwise
        // require the current password like the staff flow does.
        $skipCurrentCheck = (int) $clientUser['must_reset_password'] === 1;

        if (!$skipCurrentCheck && !password_verify($current, $clientUser['password_hash'])) {
            $this->showChangePassword('Current password is incorrect.');
            return;
        }

        $hash = password_hash($new, PASSWORD_DEFAULT);
        $clientUserModel->updatePassword($clientUser['id'], $hash, false);

        $this->redirectToDashboard();
    }

    public function showForgotPassword($error = '', $success = '') {
        $this->view('portal/layouts/auth', [
            'title' => 'Forgot Password',
            'subtitle' => 'Enter your email to receive a reset link',
            'content_view' => 'portal/auth/forgot_password',
            'csrf_token' => $this->generateCSRF(),
            'error' => $error,
            'success' => $success,
        ]);
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $this->verifyCSRF();

        $email = trim($_POST['email'] ?? '');
        $clientUserModel = new ClientUser();
        $clientUser = $clientUserModel->findByEmail($email);

        // Always show the same message whether or not the email exists,
        // to avoid leaking which emails are registered clients.
        if ($clientUser) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600);
            $clientUserModel->createPasswordReset($clientUser['id'], $tokenHash, $expiresAt);

            // No transactional email service is wired into OCTG_Platform
            // yet (see docs/CLIENT_PORTAL.md Known Limitations) -- the
            // reset link is written to the PHP error log for an admin to
            // relay manually rather than silently doing nothing. This is
            // an explicit, disclosed gap, not a finished email flow.
            error_log("[client_password_reset] Reset link for {$email}: /portal/reset-password?token={$token}");
        }

        $this->showForgotPassword('', 'If that email is registered, a reset link has been generated. Contact your account manager if you do not receive it.');
    }

    public function showResetPassword($error = '') {
        $token = $_GET['token'] ?? '';
        if (empty($error) && isset($_GET['error'])) {
            $error = $_GET['error'];
        }

        $this->view('portal/layouts/auth', [
            'title' => 'Reset Password',
            'subtitle' => 'Choose a new password',
            'content_view' => 'portal/auth/reset_password',
            'csrf_token' => $this->generateCSRF(),
            'token' => $token,
            'error' => $error,
        ]);
    }

    public function resetPassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $this->verifyCSRF();

        $token = $_POST['token'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new !== $confirm || strlen($new) < 8) {
            $this->redirect('/portal/reset-password?token=' . urlencode($token) . '&error=' . urlencode('Passwords must match and be at least 8 characters.'));
            return;
        }

        $clientUserModel = new ClientUser();
        $reset = $clientUserModel->findValidReset(hash('sha256', $token));

        if (!$reset) {
            $this->redirect('/portal/forgot-password?error=' . urlencode('That reset link is invalid or has expired.'));
            return;
        }

        $hash = password_hash($new, PASSWORD_DEFAULT);
        $clientUserModel->updatePassword($reset['client_user_id'], $hash, false);
        $clientUserModel->consumeReset($reset['id']);

        $this->showLogin('', 'Password updated. You can now log in.');
    }

    private function redirectToDashboard() {
        require_once BASE_PATH . '/app/Models/Client.php';
        $clientModel = new Client();
        $client = $clientModel->getById($_SESSION['client_id']);
        $slug = $client['slug'] ?? $_SESSION['client_id'];
        $this->redirect('/client/' . $slug . '/dashboard');
    }

}
