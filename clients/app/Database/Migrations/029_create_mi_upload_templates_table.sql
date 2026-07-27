CREATE TABLE IF NOT EXISTS `mi_upload_templates` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `requirement_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(190) NOT NULL,
    `accepted_formats` VARCHAR(120) NOT NULL COMMENT 'Comma list, e.g. csv,xlsx,pdf',
    `expected_columns` TEXT NULL COMMENT 'JSON array of expected column names',
    `description` TEXT NULL,
    `version` VARCHAR(16) NOT NULL DEFAULT '1.0.0',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    KEY `mi_upload_templates_requirement_id_index` (`requirement_id`),
    CONSTRAINT `mi_upload_templates_requirement_id_foreign` FOREIGN KEY (`requirement_id`) REFERENCES `mi_intelligence_requirements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
