<?php
ini_set('display_errors', 1); error_reporting(E_ALL);

echo "<h3>Scanning clients subdirectory</h3>";
$path = __DIR__ . '/clients';
if (is_dir($path)) {
    $files = scandir($path);
    foreach ($files as $f) {
        if (is_dir($path . '/' . $f)) {
            echo "<b>[DIR]</b> $f<br>";
        } else {
            echo "[FILE] $f<br>";
        }
    }
} else {
    echo "Directory does not exist!";
}
