CREATE TABLE IF NOT EXISTS `mi_merge_decisions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `knowledge_fact_id` BIGINT UNSIGNED NOT NULL,
    `competing_values_json` TEXT NOT NULL COMMENT 'JSON array of {collection_attempt_id, value, source_trust_tier} for every competing value considered',
    `resolution_method` ENUM('single_source', 'corroborated', 'trust_ranking', 'manual') NOT NULL,
    `variance_detected` TINYINT(1) NOT NULL DEFAULT 0,
    `resolved_by_user_id` INT NULL,
    `notes` VARCHAR(500) NULL,
    `created_at` DATETIME NOT NULL,
    KEY `mi_merge_decisions_knowledge_fact_id_index` (`knowledge_fact_id`),
    CONSTRAINT `mi_merge_decisions_knowledge_fact_id_foreign` FOREIGN KEY (`knowledge_fact_id`) REFERENCES `mi_knowledge_facts` (`id`) ON DELETE CASCADE,
    CONSTRAINT `mi_merge_decisions_resolved_by_user_id_foreign` FOREIGN KEY (`resolved_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
