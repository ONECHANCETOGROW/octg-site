<?php
require_once __DIR__ . '/_lib.php';
$pdo = octg_db();
if ($pdo) {
    header('Content-Type: application/json');
    echo json_encode($pdo->query('SELECT * FROM cms_projects')->fetchAll(), JSON_PRETTY_PRINT);
} else {
    echo "[]";
}
