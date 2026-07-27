<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/Database.php';
require_once __DIR__ . '/app/Core/DbAdapter.php';
try {
    $db = \App\Core\DbAdapter::instance();
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $stmt = $db->query('UPDATE users SET username = :user, password_hash = :pass, force_password_change = 0 WHERE username = :old OR id = 1', ['user' => 'admin', 'pass' => $hash, 'old' => 'admin']);
    echo 'Password updated for admin. Rows affected: ' . $stmt->rowCount();
} catch (\Throwable $e) {
    echo 'Error: ' . $e->getMessage();
}
