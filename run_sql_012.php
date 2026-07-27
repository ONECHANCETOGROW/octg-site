<?php
require_once __DIR__ . '/api/_lib.php';

$pdo = octg_db();
if (!$pdo) {
    die("Database connection failed.");
}

try {
    $sql = file_get_contents(__DIR__ . '/sql/012_cms_reviews.sql');
    $pdo->exec($sql);
    echo "Successfully created cms_reviews table.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
