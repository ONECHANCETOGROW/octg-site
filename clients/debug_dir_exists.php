<?php
ini_set('display_errors', 1); error_reporting(E_ALL);

echo "<h3>Scanning directories in " . __DIR__ . "</h3>";
$files = scandir(__DIR__);
foreach ($files as $f) {
    if (is_dir(__DIR__ . '/' . $f)) {
        echo "<b>[DIR]</b> $f<br>";
    } else {
        echo "[FILE] $f<br>";
    }
}
