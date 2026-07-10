-- Migration: 001_contact_messages
-- Purpose: stores submissions from contact.php
-- Import this yourself via Hostinger's phpMyAdmin — nothing in this project
-- imports SQL automatically.

CREATE TABLE IF NOT EXISTS contact_messages (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(160)  NOT NULL,
    email         VARCHAR(190)  NOT NULL,
    phone         VARCHAR(40)   NULL,
    business_name VARCHAR(190)  NULL,
    message       TEXT          NOT NULL,
    source_page   VARCHAR(190)  NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_read       TINYINT(1)    NOT NULL DEFAULT 0,
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
