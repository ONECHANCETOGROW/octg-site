<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = Database::getInstance();
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo implode(", ", $tables);
    unlink(__FILE__);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
