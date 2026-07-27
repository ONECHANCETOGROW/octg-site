<?php
ini_set('display_errors', 1); error_reporting(E_ALL);

$viewFile = __DIR__ . '/app/Views/clients/index.php';
echo "<h3>Checking view file: $viewFile</h3>";
if (file_exists($viewFile)) {
    $content = file_get_contents($viewFile);
    echo "Size: " . strlen($content) . " bytes<br>";
    echo "Contains 'Portal Access': " . (str_contains($content, 'Portal Access') ? 'Yes' : 'No') . "<br>";
    echo "First 10 lines:<br><pre>" . htmlspecialchars(implode("\n", array_slice(explode("\n", $content), 0, 10))) . "</pre>";
} else {
    echo "File does not exist!";
}
