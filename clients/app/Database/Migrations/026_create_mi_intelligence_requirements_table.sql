CREATE TABLE IF NOT EXISTS `mi_intelligence_requirements` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `channel_id` BIGINT UNSIGNED NOT NULL,
    `code` VARCHAR(64) NOT NULL UNIQUE,
    `title` VARCHAR(190) NOT NULL,
    `category` VARCHAR(64) NOT NULL,
    `purpose` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `is_required` TINYINT(1) NOT NULL DEFAULT 0,
    `confidence_weight` TINYINT UNSIGNED NOT NULL DEFAULT 10,
    `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `version` VARCHAR(16) NOT NULL DEFAULT '1.0.0',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    KEY `mi_requirements_channel_id_index` (`channel_id`),
    KEY `mi_requirements_category_index` (`category`),
    CONSTRAINT `mi_requirements_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `mi_channels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
