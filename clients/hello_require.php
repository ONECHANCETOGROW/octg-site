<?php
ini_set('display_errors', 1); error_reporting(E_ALL);
try {
    require_once __DIR__ . '/app/Database/MarketingIntelSeeder.php';
    echo 'Loaded successfully';
} catch (\Throwable \) {
    echo 'Caught error: ' . \->getMessage();
}

