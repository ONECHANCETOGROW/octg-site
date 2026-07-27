<?php
require_once "config/config.php";
require_once "app/Core/Database.php";
$db = new Database();
$stmt = $db->query("DESCRIBE clients");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt2 = $db->query("DESCRIBE users");
print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));

