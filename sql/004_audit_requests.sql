-- Migration: 004_audit_requests
-- Purpose: stores submissions from audit.php's free audit request form.
-- Import this yourself via Hostinger's phpMyAdmin.

CREATE TABLE IF NOT EXISTS audit_requests (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_name VARCHAR(190) NOT NULL,
    website_url   VARCHAR(255) NULL,
    email         VARCHAR(190) NOT NULL,
    phone         VARCHAR(40)  NULL,
    audit_areas   TEXT         NULL,  -- JSON array
    status        VARCHAR(30)  NOT NULL DEFAULT 'new',
    source_page   VARCHAR(190) NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
