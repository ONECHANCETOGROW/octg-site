CREATE TABLE IF NOT EXISTS `mi_validation_issues` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `parsed_extraction_id` BIGINT UNSIGNED NOT NULL,
    `severity` ENUM('critical', 'warning', 'notice') NOT NULL,
    `issue_type` ENUM(
        'incomplete_response',
        'hallucination_suspected',
        'missing_metric',
        'invalid_format',
        'conflicting_data',
        'out_of_range',
        'structural_format_failure'
    ) NOT NULL,
    `field_name` VARCHAR(120) NULL,
    `message` VARCHAR(500) NOT NULL,
    `is_resolved` TINYINT(1) NOT NULL DEFAULT 0,
    `resolved_by_user_id` INT NULL,
    `resolved_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL,
    KEY `mi_validation_issues_parsed_extraction_id_index` (`parsed_extraction_id`),
    CONSTRAINT `mi_validation_issues_parsed_extraction_id_foreign` FOREIGN KEY (`parsed_extraction_id`) REFERENCES `mi_parsed_extractions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `mi_validation_issues_resolved_by_user_id_foreign` FOREIGN KEY (`resolved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
