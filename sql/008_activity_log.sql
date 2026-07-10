-- Migration: 008_activity_log
-- Purpose: a general audit trail across the admin panel.

CREATE TABLE IF NOT EXISTS activity_log (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    action      VARCHAR(190) NOT NULL,   -- e.g. "Lead status changed", "Admin login"
    description TEXT NULL,
    actor       VARCHAR(160) NOT NULL DEFAULT 'System',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
