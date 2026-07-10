-- Migration: 002_demo_requests
-- Purpose: stores multi-step submissions from book-demo.php
-- Import this yourself via Hostinger's phpMyAdmin.

CREATE TABLE IF NOT EXISTS demo_requests (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    business_name       VARCHAR(190)  NOT NULL,
    contact_name        VARCHAR(160)  NOT NULL,
    email               VARCHAR(190)  NOT NULL,
    phone               VARCHAR(40)   NOT NULL,
    business_type       VARCHAR(120)  NULL,
    services_interested TEXT          NULL,   -- JSON array of selected service slugs
    budget_range        VARCHAR(60)   NULL,
    goals               TEXT          NULL,
    status              VARCHAR(30)   NOT NULL DEFAULT 'new',
    source_page         VARCHAR(190)  NULL,
    created_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
