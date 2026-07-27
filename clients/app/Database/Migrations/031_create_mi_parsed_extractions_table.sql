CREATE TABLE IF NOT EXISTS `mi_parsed_extractions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `collection_attempt_id` BIGINT UNSIGNED NOT NULL,
    `structured_json` LONGTEXT NOT NULL COMMENT 'Normalized rows/fields extracted from the raw input, MIS-shaped',
    `field_confidence_json` LONGTEXT NOT NULL COMMENT 'Per-field confidence 0-100, keyed the same as structured_json',
    `overall_confidence` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `parser_used` VARCHAR(64) NOT NULL COMMENT 'e.g. ai_table_parser, csv_parser, excel_parser, pdf_parser',
    `parser_version` VARCHAR(16) NOT NULL DEFAULT '1.0.0',
    `created_at` DATETIME NOT NULL,
    KEY `mi_parsed_extractions_collection_attempt_id_index` (`collection_attempt_id`),
    CONSTRAINT `mi_parsed_extractions_collection_attempt_id_foreign` FOREIGN KEY (`collection_attempt_id`) REFERENCES `mi_collection_attempts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
