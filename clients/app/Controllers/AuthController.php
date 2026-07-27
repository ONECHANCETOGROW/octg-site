<?php
require_once BASE_PATH . '/app/Models/User.php';

class AuthController extends Controller {

    public function showLogin($error = '', $success = '') {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }
        
        if (empty($error) && isset($_GET['error'])) {
            $error = $_GET['error'];
        }
        
        $this->view('layouts/auth', [
            'title' => 'Login - OCTG',
            'subtitle' => 'Sign in to your account',
            'content_view' => 'auth/login',
            'csrf_token' => $this->generateCSRF(),
            'error' => $error,
            'success' => $success
        ]);
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCSRF();
            
            $identifier = trim($_POST['identifier'] ?? '');
            $password = $_POST['password'] ?? '';
            
            $userModel = new User();
            $user = $userModel->findByEmailOrUsername($identifier);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Prevent session fixation
                session_regenerate_id(true);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role_id'] = $user['role_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['force_password_change'] = $user['force_password_change'];
                
                $this->redirect('/dashboard');
            } else {
                $this->showLogin('Invalid username or password.');
            }
        }
    }
    
    public function showChangePassword($error = '', $success = '') {
        $this->view('layouts/auth', [
            'title' => 'Change Password - OCTG',
            'subtitle' => 'Please update your password to continue',
            'content_view' => 'auth/change_password',
            'csrf_token' => $this->generateCSRF(),
            'error' => $error,
            'success' => $success
        ]);
    }

    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

            $userModel = new User();
            // We need to fetch current user to verify old password
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if ($user && password_verify($current, $user['password_hash'])) {
                $hash = password_hash($new, PASSWORD_DEFAULT);
                $userModel->updatePassword($user['id'], $hash);
                $_SESSION['force_password_change'] = 0;
                $this->redirect('/dashboard');
            } else {
                $this->showChangePassword('Current password is incorrect.');
            }
        }
    }

    public function showForgotPassword($error = '', $success = '') {
        $this->view('layouts/auth', [
            'title' => 'Forgot Password - OCTG',
            'subtitle' => 'Enter your email to receive a reset link',
            'content_view' => 'auth/forgot_password',
            'csrf_token' => $this->generateCSRF(),
            'error' => $error,
            'success' => $success
        ]);
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCSRF();
            $email = trim($_POST['email'] ?? '');
            
            // In a real app, generate token, save to DB, send email
            // For now, mock success to prevent email enumeration
            $this->showForgotPassword('', 'If that email exists, a reset link has been sent.');
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: /login');
        exit;
    }
}
