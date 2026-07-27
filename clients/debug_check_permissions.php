<?php
ini_set('display_errors', 1); error_reporting(E_ALL);

$viewFile = __DIR__ . '/app/Views/clients/index.php';
echo "<h3>Checking index.php permissions</h3>";
if (file_exists($viewFile)) {
    echo "Writable: " . (is_writable($viewFile) ? 'Yes' : 'No') . "<br>";
    echo "Owner: " . fileowner($viewFile) . "<br>";
    echo "Permissions: " . decoct(fileperms($viewFile)) . "<br>";
    
    // Let's try to overwrite it using a dummy string
    $testContent = "<!-- test overwrite -->\n" . file_get_contents($viewFile);
    if (@file_put_contents($viewFile, $testContent)) {
        echo "<span style='color:green;'>PHP Overwrite successful!</span><br>";
        // Revert it
        file_put_contents($viewFile, substr($testContent, 24));
    } else {
        echo "<span style='color:red;'>PHP Overwrite failed!</span><br>";
    }
} else {
    echo "File does not exist!";
}
