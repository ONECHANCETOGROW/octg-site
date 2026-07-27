CREATE TABLE IF NOT EXISTS `mi_requirement_dependencies` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `requirement_id` BIGINT UNSIGNED NOT NULL,
    `depends_on_requirement_id` BIGINT UNSIGNED NOT NULL,
    `edge_type` ENUM('blocks', 'enriches') NOT NULL DEFAULT 'enriches',
    `created_at` DATETIME NOT NULL,
    UNIQUE KEY `mi_dependency_edge_unique` (`requirement_id`, `depends_on_requirement_id`),
    KEY `mi_dependencies_depends_on_index` (`depends_on_requirement_id`),
    CONSTRAINT `mi_dependencies_requirement_id_foreign` FOREIGN KEY (`requirement_id`) REFERENCES `mi_intelligence_requirements` (`id`) ON DELETE CASCADE,
    CONSTRAINT `mi_dependencies_depends_on_id_foreign` FOREIGN KEY (`depends_on_requirement_id`) REFERENCES `mi_intelligence_requirements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
