-- Migration: 007_email_notifications
-- Purpose: centralized email settings (singleton row) + a log of every
-- notification attempt, so a lead is never lost even if email delivery
-- fails, and every send is auditable/resendable from the admin panel.

CREATE TABLE IF NOT EXISTS email_settings (
    id                     TINYINT UNSIGNED PRIMARY KEY DEFAULT 1,
    notifications_enabled  TINYINT(1)   NOT NULL DEFAULT 1,
    notification_emails    TEXT         NOT NULL, -- JSON array of recipient addresses
    sender_name            VARCHAR(160) NOT NULL DEFAULT 'One Chance To Grow',
    sender_email           VARCHAR(190) NOT NULL DEFAULT 'no-reply@onechancetogrow.com',
    reply_to_email         VARCHAR(190) NULL,
    company_name           VARCHAR(160) NOT NULL DEFAULT 'One Chance To Grow LLC',
    email_footer           TEXT         NULL,
    email_logo_url         VARCHAR(255) NULL,
    smtp_enabled           TINYINT(1)   NOT NULL DEFAULT 0,
    smtp_host              VARCHAR(190) NULL,
    smtp_port              SMALLINT UNSIGNED NULL,
    smtp_username          VARCHAR(190) NULL,
    smtp_password           VARCHAR(255) NULL,
    smtp_encryption        ENUM('none','ssl','tls') NULL DEFAULT 'tls',
    updated_at             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_singleton CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed the one settings row with sane defaults (safe to import even if
-- a row already exists — INSERT IGNORE skips it rather than erroring).
INSERT IGNORE INTO email_settings (id, notification_emails, sender_email, reply_to_email)
VALUES (1, '["hello@onechancetogrow.com"]', 'no-reply@onechancetogrow.com', 'hello@onechancetogrow.com');

CREATE TABLE IF NOT EXISTS email_logs (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipient      VARCHAR(190) NOT NULL,
    form_source    VARCHAR(60)  NOT NULL, -- 'contact', 'book-demo', 'audit', 'newsletter'
    subject        VARCHAR(255) NULL,
    status         ENUM('sent','failed') NOT NULL,
    error_message  TEXT NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
