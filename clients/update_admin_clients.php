<?php
// Script to reset admin password
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/app/Core/Database.php';

try {
    $db = Database::getInstance();
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // The user table likely has 'username' and 'password_hash' and 'email'
    // Let's just update where role_id = 1 or id = 1
    $stmt = $db->prepare("UPDATE users SET username = 'admin', password_hash = ? WHERE id = 1");
    $stmt->execute([$hash]);
    
    echo "Password updated successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
