CREATE TABLE IF NOT EXISTS `mi_audit_channels` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `audit_id` BIGINT UNSIGNED NOT NULL,
    `channel_id` BIGINT UNSIGNED NOT NULL,
    `selected_at` DATETIME NOT NULL,
    UNIQUE KEY `mi_audit_channels_audit_channel_unique` (`audit_id`, `channel_id`),
    CONSTRAINT `mi_audit_channels_audit_id_foreign` FOREIGN KEY (`audit_id`) REFERENCES `mi_audits` (`id`) ON DELETE CASCADE,
    CONSTRAINT `mi_audit_channels_channel_id_foreign` FOREIGN KEY (`channel_id`) REFERENCES `mi_channels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
