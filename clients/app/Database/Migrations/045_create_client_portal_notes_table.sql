-- Migration 045: Create client_portal_notes table
-- Stores monthly notes and goals entered by staff in the Marketing Workspace

CREATE TABLE IF NOT EXISTS `client_portal_notes` (
    `id`                INT(11)       NOT NULL AUTO_INCREMENT,
    `client_id`         INT(11)       NOT NULL,
    `period_start`      DATE          NOT NULL,
    `note_type`         ENUM('note','goal') NOT NULL DEFAULT 'note',
    `body`              TEXT          NOT NULL,
    `entered_by_user_id` INT(11)      NULL DEFAULT NULL,
    `created_at`        TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        TIMESTAMP     NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cpn_client_period` (`client_id`, `period_start`),
    KEY `idx_cpn_type` (`note_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
