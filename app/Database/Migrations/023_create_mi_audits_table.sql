CREATE TABLE IF NOT EXISTS `mi_audits` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `title` VARCHAR(190) NOT NULL,
    `status` ENUM('collecting', 'ready', 'completed') NOT NULL DEFAULT 'collecting',
    `overall_completeness` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `overall_confidence` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `reachable_tier` ENUM('none', 'preliminary', 'standard', 'complete') NOT NULL DEFAULT 'none',
    `known_entity_names` TEXT NULL COMMENT 'JSON array of real campaign/account names confirmed by the strategist, used by NameGroundingRule',
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    KEY `mi_audits_client_id_index` (`client_id`),
    KEY `mi_audits_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
