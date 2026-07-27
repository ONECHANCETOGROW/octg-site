<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/app/Core/Database.php';
require_once BASE_PATH . '/app/Core/Model.php';
require_once BASE_PATH . '/app/Models/User.php';

try {
    $userModel = new User();
    $user = $userModel->findByEmailOrUsername('admin');
    echo "DB Connected Successfully. Found user: " . ($user ? $user['username'] : 'none');
} catch (Exception $e) {
    http_response_code(500);
    echo "DB Error: " . $e->getMessage();
}
