-- Persistent, trackable recommendations (spec section 4.8). Synced in
-- from a contract's recommendations[] the first time a client views them
-- (see RecommendationsController::syncFromContract) -- status/due_date
-- then live independently of the report that originally produced them,
-- so marking something "Completed" doesn't get wiped out next audit.
CREATE TABLE IF NOT EXISTS `client_portal_recommendations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL,
    `module` VARCHAR(40) NOT NULL DEFAULT 'google_ads',
    `source_recommendation_id` VARCHAR(80) NULL COMMENT 'recommendation_id from the contract, for de-duplication on re-sync',
    `source_report_id` INT NULL,
    `what_to_change` TEXT NOT NULL,
    `why_it_matters` TEXT NULL,
    `priority` ENUM('High', 'Medium', 'Low') NOT NULL DEFAULT 'Medium',
    `status` ENUM('open', 'in_progress', 'completed', 'ignored') NOT NULL DEFAULT 'open',
    `due_date` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY `client_portal_recommendations_client_id_index` (`client_id`),
    UNIQUE KEY `client_portal_recommendations_dedupe` (`client_id`, `module`, `source_recommendation_id`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
