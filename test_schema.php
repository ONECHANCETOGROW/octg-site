<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);
require_once "config/config.php";
require_once "app/Core/Database.php";
$pdo = Database::getInstance();
$stmt = $pdo->query("DESCRIBE clients");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt2 = $pdo->query("DESCRIBE users");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));

