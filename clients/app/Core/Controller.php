<?php
class Controller {
    public function view($view, $data = []) {
        extract($data);
        $viewFile = BASE_PATH . "/app/Views/{$view}.php";
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("View does not exist: " . $viewFile);
        }
    }

    public function redirect($url) {
        header("Location: " . $url);
        exit();
    }

    protected function verifyCSRF() {
        $csrf = $_POST['csrf_token'] ?? '';
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            // For JSON requests (like upload), return JSON error instead of die
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'CSRF Token validation failed.']);
                exit;
            }
            die("CSRF Token validation failed.");
        }
    }

    protected function generateCSRF() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
