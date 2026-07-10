<?php
require_once __DIR__ . '/api/_lib.php';
$pdo = octg_db();
if (!$pdo) {
    die("Database not connected.");
}
$sql = file_get_contents(__DIR__ . '/sql/master_install.sql');
// Strip BOM if present
if (substr($sql, 0, 3) === "\xEF\xBB\xBF") {
    $sql = substr($sql, 3);
}
try {
    $pdo->exec($sql);
    echo 'SUCCESS';
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
unlink(__FILE__); // self delete
?>
