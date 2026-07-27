CREATE TABLE IF NOT EXISTS `mi_prompt_templates` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `requirement_id` BIGINT UNSIGNED NOT NULL,
    `title` VARCHAR(190) NOT NULL,
    `target_surface` VARCHAR(120) NOT NULL DEFAULT 'Google Ads Advisor',
    `purpose` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `prompt_text` TEXT NOT NULL,
    `response_format_contract` TEXT NOT NULL,
    `expected_columns` TEXT NULL COMMENT 'JSON array of expected column names, used by the parser as a schema hint',
    `version` VARCHAR(16) NOT NULL DEFAULT '1.0.0',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    KEY `mi_prompt_templates_requirement_id_index` (`requirement_id`),
    CONSTRAINT `mi_prompt_templates_requirement_id_foreign` FOREIGN KEY (`requirement_id`) REFERENCES `mi_intelligence_requirements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
