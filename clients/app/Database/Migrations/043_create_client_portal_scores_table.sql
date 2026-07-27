-- Create Client Portal Scores table to separate raw metrics from scores.
CREATE TABLE IF NOT EXISTS `client_portal_scores` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL,
    `module_id` INT NOT NULL,
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `score` INT NOT NULL DEFAULT 0,
    `grade` VARCHAR(10) NULL,
    `health_status` VARCHAR(50) NULL,
    `trend` TEXT NULL,
    `biggest_win` TEXT NULL,
    `biggest_risk` TEXT NULL,
    `priority_this_month` TEXT NULL,
    `entered_by_user_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `client_portal_scores_unique_period` (`client_id`, `module_id`, `period_start`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`module_id`) REFERENCES `portal_modules`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
