<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/Core/Database.php';

try {
    $pdo = Database::getInstance();
    echo "Connected successfully to Database.\n\n";

    // 1. Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables present:\n";
    foreach ($tables as $table) {
        echo " - $table\n";
    }
    echo "\n";

    // 2. Describe portal_modules
    if (in_array('portal_modules', $tables)) {
        echo "Describe portal_modules:\n";
        $stmt = $pdo->query("DESCRIBE portal_modules");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 3. Describe client_portal_metrics
    if (in_array('client_portal_metrics', $tables)) {
        echo "\nDescribe client_portal_metrics:\n";
        $stmt = $pdo->query("DESCRIBE client_portal_metrics");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 4. Describe client_portal_scores
    if (in_array('client_portal_scores', $tables)) {
        echo "\nDescribe client_portal_scores:\n";
        $stmt = $pdo->query("DESCRIBE client_portal_scores");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 5. Describe client_portal_recommendations
    if (in_array('client_portal_recommendations', $tables)) {
        echo "\nDescribe client_portal_recommendations:\n";
        $stmt = $pdo->query("DESCRIBE client_portal_recommendations");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // 6. Describe client_portal_timeline_events
    if (in_array('client_portal_timeline_events', $tables)) {
        echo "\nDescribe client_portal_timeline_events:\n";
        $stmt = $pdo->query("DESCRIBE client_portal_timeline_events");
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
