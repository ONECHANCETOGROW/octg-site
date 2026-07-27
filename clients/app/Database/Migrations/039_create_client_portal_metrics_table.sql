-- Generic, historical (never-overwritten) metrics store for every
-- manual-entry dashboard module (SEO/GBP/Social/Website Performance).
-- One table, `module` + `platform` distinguish which screen a row
-- belongs to -- see docs/CLIENT_PORTAL.md "Historical Data Pattern" for
-- why this is one table instead of four near-identical ones, and why
-- period_start is part of the uniqueness key instead of overwriting the
-- prior month's row (spec: "Everything should save historically. Never
-- overwrite previous months.").
CREATE TABLE IF NOT EXISTS `client_portal_metrics` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL,
    `module` ENUM('seo', 'gbp', 'social', 'website_performance') NOT NULL,
    `platform` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'e.g. facebook/instagram for social; empty string for single-platform modules',
    `period_start` DATE NOT NULL,
    `period_end` DATE NOT NULL,
    `source` ENUM('manual', 'api') NOT NULL DEFAULT 'manual',
    `data_json` TEXT NOT NULL,
    `entered_by_user_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `client_portal_metrics_unique_period` (`client_id`, `module`, `platform`, `period_start`, `source`),
    KEY `client_portal_metrics_lookup_index` (`client_id`, `module`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `client_portal_notes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL,
    `period_start` DATE NOT NULL,
    `note_type` ENUM('note', 'goal') NOT NULL DEFAULT 'note',
    `body` TEXT NOT NULL,
    `entered_by_user_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `client_portal_notes_client_id_index` (`client_id`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
