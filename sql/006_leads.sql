-- Migration: 006_leads
-- Purpose: unified CRM lead record. Every real lead-generating form
-- (Contact, Book a Demo, Free Audit — NOT the newsletter signup, which
-- is a lower-intent subscription rather than a sales lead) inserts a
-- normalized record here IN ADDITION TO its own existing detail table
-- (contact_messages / demo_requests / audit_requests are untouched).
-- This table is what the Lead Management admin screen reads from.

CREATE TABLE IF NOT EXISTS leads (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name              VARCHAR(160)  NOT NULL,
    business_name     VARCHAR(190)  NULL,
    phone             VARCHAR(40)   NULL,
    email             VARCHAR(190)  NOT NULL,
    website           VARCHAR(255)  NULL,
    source            ENUM('contact','book-demo','audit') NOT NULL,
    interested_service VARCHAR(255) NULL,
    message           TEXT          NULL,
    status            ENUM('new','contacted','qualified','proposal_sent','won','lost','archived') NOT NULL DEFAULT 'new',
    assigned_to       BIGINT UNSIGNED NULL,
    follow_up_date    DATE          NULL,
    last_contacted_at DATETIME      NULL,
    tags              TEXT          NULL, -- JSON array
    source_page       VARCHAR(190)  NULL,
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_assigned_to (assigned_to),
    CONSTRAINT fk_leads_assigned_to FOREIGN KEY (assigned_to) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lead_activity (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id    BIGINT UNSIGNED NOT NULL,
    type       ENUM('note','status_change','email_sent','created') NOT NULL,
    content    TEXT NULL,
    created_by VARCHAR(160) NULL, -- admin user name, or 'System'
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead_id (lead_id),
    CONSTRAINT fk_lead_activity_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
