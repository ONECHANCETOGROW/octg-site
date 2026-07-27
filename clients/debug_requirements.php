<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/Database.php';
try {
    \ = \Database::getInstance();
    \ = \->query('SELECT * FROM mi_requirements');
    \ = \->fetchAll(PDO::FETCH_ASSOC);
    echo '<pre>';
    print_r(\);
    echo '</pre>';
} catch (Exception \) {
    echo \->getMessage();
}

