-- Migration: 009_website_optimizer
-- Purpose: the AI Website Optimizer's data layer — audit runs, per-page
-- scores, detected issues, draft improvements awaiting approval, and the
-- history of fixes that were actually applied.
-- Import this yourself via Hostinger's phpMyAdmin, after 005-008.

CREATE TABLE IF NOT EXISTS audit_reports (
    id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    run_type          ENUM('manual','scheduled') NOT NULL DEFAULT 'manual',
    pages_crawled     INT UNSIGNED NOT NULL DEFAULT 0,
    overall_score     DECIMAL(5,2) NULL,
    seo_score         DECIMAL(5,2) NULL,
    aeo_score         DECIMAL(5,2) NULL,
    geo_score         DECIMAL(5,2) NULL,
    eeat_score        DECIMAL(5,2) NULL,
    accessibility_score DECIMAL(5,2) NULL,
    performance_score DECIMAL(5,2) NULL,
    technical_seo_score DECIMAL(5,2) NULL,
    internal_linking_score DECIMAL(5,2) NULL,
    critical_issue_count INT UNSIGNED NOT NULL DEFAULT 0,
    warning_count     INT UNSIGNED NOT NULL DEFAULT 0,
    started_at        DATETIME NOT NULL,
    finished_at        DATETIME NULL,
    email_sent        TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_started_at (started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_page_scores (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id       BIGINT UNSIGNED NOT NULL,
    url             VARCHAR(255) NOT NULL,
    file_path       VARCHAR(255) NULL,
    overall_score   DECIMAL(5,2) NOT NULL,
    seo_score       DECIMAL(5,2) NULL,
    aeo_score       DECIMAL(5,2) NULL,
    geo_score       DECIMAL(5,2) NULL,
    eeat_score      DECIMAL(5,2) NULL,
    accessibility_score DECIMAL(5,2) NULL,
    performance_score DECIMAL(5,2) NULL,
    technical_seo_score DECIMAL(5,2) NULL,
    internal_linking_score DECIMAL(5,2) NULL,
    INDEX idx_report_id (report_id),
    INDEX idx_url (url),
    CONSTRAINT fk_page_scores_report FOREIGN KEY (report_id) REFERENCES audit_reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_issues (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id     BIGINT UNSIGNED NOT NULL,
    url           VARCHAR(255) NOT NULL,
    file_path     VARCHAR(255) NULL,
    category      VARCHAR(40) NOT NULL, -- seo, aeo, geo, eeat, accessibility, performance, technical_seo, internal_linking
    severity      ENUM('critical','warning','info') NOT NULL,
    issue         VARCHAR(190) NOT NULL,       -- e.g. "Missing meta description"
    reason        TEXT NOT NULL,               -- why this was flagged, in plain language
    suggested_fix TEXT NULL,
    is_safe_auto_fix TINYINT(1) NOT NULL DEFAULT 0,
    status        ENUM('open','draft_created','resolved','ignored') NOT NULL DEFAULT 'open',
    INDEX idx_report_id (report_id),
    INDEX idx_category (category),
    INDEX idx_severity (severity),
    CONSTRAINT fk_issues_report FOREIGN KEY (report_id) REFERENCES audit_reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS draft_improvements (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    issue_id        BIGINT UNSIGNED NULL,
    url             VARCHAR(255) NOT NULL,
    file_path       VARCHAR(255) NULL,
    field           VARCHAR(120) NOT NULL,   -- e.g. "meta_description", "alt_text:hero_image"
    current_value   TEXT NULL,
    suggested_value TEXT NOT NULL,
    reasoning       TEXT NULL,
    status          ENUM('pending','approved','rejected','applied') NOT NULL DEFAULT 'pending',
    reviewed_by     VARCHAR(160) NULL,
    reviewed_at     DATETIME NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    CONSTRAINT fk_drafts_issue FOREIGN KEY (issue_id) REFERENCES audit_issues(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS optimization_history (
    id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    draft_id       BIGINT UNSIGNED NULL,
    url            VARCHAR(255) NOT NULL,
    issue          VARCHAR(190) NOT NULL,
    old_score      DECIMAL(5,2) NULL,
    new_score      DECIMAL(5,2) NULL,
    fix_applied    TEXT NOT NULL,
    approved_by    VARCHAR(160) NULL,
    deployed_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_url (url),
    CONSTRAINT fk_history_draft FOREIGN KEY (draft_id) REFERENCES draft_improvements(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS optimizer_settings (
    id                    TINYINT UNSIGNED PRIMARY KEY DEFAULT 1,
    base_url              VARCHAR(255) NOT NULL DEFAULT 'https://onechancetogrow.com',
    score_threshold       TINYINT UNSIGNED NOT NULL DEFAULT 80,
    auto_fix_enabled      TINYINT(1) NOT NULL DEFAULT 0,
    email_reports_enabled TINYINT(1) NOT NULL DEFAULT 1,
    report_frequency      ENUM('daily','weekly','monthly') NOT NULL DEFAULT 'daily',
    updated_at            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_optimizer_singleton CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO optimizer_settings (id) VALUES (1);
