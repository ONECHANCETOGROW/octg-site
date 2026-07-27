<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/api/_lib.php';

$pdo = octg_db();
if (!$pdo) {
    die("Database connection failed.");
}

try {
    $pdo->exec("DROP TABLE IF EXISTS `cms_reviews`;");
    $sql = "CREATE TABLE IF NOT EXISTS `cms_reviews` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `customer_name` VARCHAR(150) NOT NULL,
        `company_name` VARCHAR(150),
        `job_title` VARCHAR(150),
        `review_text` TEXT NOT NULL,
        `star_rating` INT DEFAULT 5,
        `google_review_url` VARCHAR(255),
        `customer_avatar` VARCHAR(255),
        `company_logo` VARCHAR(255),
        `industry` VARCHAR(100),
        `is_featured` BOOLEAN DEFAULT 0,
        `status` ENUM('published', 'draft') DEFAULT 'published',
        `display_order` INT DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );";
    $pdo->exec($sql);
    echo "Successfully dropped and re-created cms_reviews table.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
