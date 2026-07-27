<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$_SESSION = [];
session_destroy();
header('Location: /admin8828051506/login.php');
exit;
