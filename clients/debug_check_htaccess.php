<?php
ini_set('display_errors', 1); error_reporting(E_ALL);

echo "<h3>Checking .htaccess files</h3>";

$paths = [
    __DIR__ . '/.htaccess',
    dirname(__DIR__) . '/.htaccess',
    __DIR__ . '/public/.htaccess'
];

foreach ($paths as $p) {
    echo "<b>Path:</b> " . htmlspecialchars($p) . "<br>";
    if (file_exists($p)) {
        echo "<pre>" . htmlspecialchars(file_get_contents($p)) . "</pre><br>";
    } else {
        echo "Does not exist!<br>";
    }
}
