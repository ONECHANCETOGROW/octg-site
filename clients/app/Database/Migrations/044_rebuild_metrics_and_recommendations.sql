-- Rebuild Metrics and Recommendations to link with Module Registry and support traceability.

-- 1. Modify client_portal_metrics table
ALTER TABLE `client_portal_metrics` ADD COLUMN `module_id` INT NULL AFTER `client_id`;

-- Populate module_id based on old module ENUM mapping
UPDATE `client_portal_metrics` m
JOIN `portal_modules` pm ON pm.slug = m.module
SET m.module_id = pm.id;

-- If there are any metrics with no module match, map to marketing_health or delete (usually empty in fresh migration run)
DELETE FROM `client_portal_metrics` WHERE `module_id` IS NULL;

ALTER TABLE `client_portal_metrics` MODIFY COLUMN `module_id` INT NOT NULL;
ALTER TABLE `client_portal_metrics` DROP KEY `client_portal_metrics_unique_period`;
ALTER TABLE `client_portal_metrics` DROP COLUMN `module`;

ALTER TABLE `client_portal_metrics` ADD UNIQUE KEY `client_portal_metrics_unique_period` (`client_id`, `module_id`, `platform`, `period_start`, `source`);
ALTER TABLE `client_portal_metrics` ADD CONSTRAINT `fk_client_portal_metrics_module_id` FOREIGN KEY (`module_id`) REFERENCES `portal_modules`(`id`) ON DELETE CASCADE;

-- 2. Modify client_portal_recommendations table
ALTER TABLE `client_portal_recommendations` ADD COLUMN `module_id` INT NULL AFTER `client_id`;

-- Populate module_id based on old module mapping (defaulting to google_ads if invalid)
UPDATE `client_portal_recommendations` r
JOIN `portal_modules` pm ON pm.slug = r.module
SET r.module_id = pm.id;

UPDATE `client_portal_recommendations` SET `module_id` = (SELECT id FROM `portal_modules` WHERE slug = 'google_ads' LIMIT 1) WHERE `module_id` IS NULL;

ALTER TABLE `client_portal_recommendations` MODIFY COLUMN `module_id` INT NOT NULL;
ALTER TABLE `client_portal_recommendations` DROP KEY `client_portal_recommendations_dedupe`;
ALTER TABLE `client_portal_recommendations` DROP COLUMN `module`;

-- Add traceability columns
ALTER TABLE `client_portal_recommendations` 
ADD COLUMN `audit_id` INT NULL AFTER `source_report_id`,
ADD COLUMN `report_version` VARCHAR(50) NULL AFTER `audit_id`,
ADD COLUMN `period_start` DATE NULL AFTER `report_version`,
ADD COLUMN `source` VARCHAR(50) NOT NULL DEFAULT 'intelligence_engine' AFTER `due_date`;

ALTER TABLE `client_portal_recommendations` ADD UNIQUE KEY `client_portal_recommendations_dedupe` (`client_id`, `module_id`, `source_recommendation_id`);
ALTER TABLE `client_portal_recommendations` ADD CONSTRAINT `fk_client_portal_recommendations_module_id` FOREIGN KEY (`module_id`) REFERENCES `portal_modules`(`id`) ON DELETE CASCADE;

-- 3. Create client_portal_timeline_events table
CREATE TABLE IF NOT EXISTS `client_portal_timeline_events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL,
    `event_date` DATE NOT NULL,
    `label` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `icon` VARCHAR(50) NOT NULL DEFAULT 'calendar',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
