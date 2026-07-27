<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\DbAdapter;

abstract class IntelController
{
    protected DbAdapter $db;

    public function __construct()
    {
        $this->db = DbAdapter::instance();
    }

    protected function requireAuth(): void
    {
        if (empty($_SESSION['user_id'])) {
            header("Location: /login");
            exit;
        }
    }

    protected function userId(): int
    {
        return (int) $_SESSION['user_id'];
    }

    protected function render(string $template, array $data = []): void
    {
        extract($data);
        $currentUser = ['id' => $_SESSION['user_id'] ?? null, 'email' => $_SESSION['email'] ?? null, 'name' => $_SESSION['name'] ?? null];
        
        $globalController = new \Controller();
        // Since Controller method is protected we need to call it via reflection or write it here
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $csrfField = '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
        
        $viewFile = BASE_PATH . "/app/Modules/" . $template . ".php";
        if (file_exists($viewFile)) {
            // Need to wrap in the main layout?
            $content_view = "../Modules/" . $template;
            $title = $data['title'] ?? 'Marketing Intelligence';
            $active_menu = 'audits';
            require BASE_PATH . "/app/Views/layouts/main.php";
        } else {
            die("Module view does not exist: " . $viewFile);
        }
    }

    protected function verifyCsrfOrFail(Request $request): void
    {
        $csrf = $_POST['csrf_token'] ?? '';
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) {
            die("CSRF Token validation failed.");
        }
    }
}
