<?php
ini_set('display_errors', 1); error_reporting(E_ALL);

echo "<h3>Searching for files on the server</h3>";

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $file) {
    if ($file->isFile()) {
        $name = $file->getFilename();
        if (in_array($name, ['ClientController.php', 'Client.php', 'IntelAuditController.php', 'MarketingIntelReportGenerator.php'])) {
            $path = $file->getPathname();
            echo "<b>Found:</b> " . htmlspecialchars($path) . " (" . $file->getSize() . " bytes)<br>";
        }
    }
}
