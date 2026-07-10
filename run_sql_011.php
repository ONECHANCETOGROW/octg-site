<?php
require_once __DIR__ . '/api/_lib.php';
$pdo = octg_db();
$sql = file_get_contents(__DIR__ . '/sql/011_cms_architecture.sql');
$pdo->exec($sql);
echo 'SUCCESS';
unlink(__FILE__); // self delete
