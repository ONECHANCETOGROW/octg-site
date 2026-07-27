<?php
// Simple .env parser for PHP
// Define BASE_PATH
define('BASE_PATH', dirname(__DIR__));

// Define Storage Path (outside public root)
// Assuming public is the document root, we go one level up.
define('STORAGE_PATH', dirname(BASE_PATH) . '/storage');

$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }
}

// Define defaults if not set in .env
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'u263949463_Clients');
if (!defined('DB_USER')) define('DB_USER', 'u263949463_Clients');
if (!defined('DB_PASS')) define('DB_PASS', 'G6CXmz37@123');
