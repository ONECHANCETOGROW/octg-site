-- Migration: 001_contact_messages
-- Purpose: stores submissions from contact.php
-- Import this yourself via Hostinger's phpMyAdmin â€” nothing in this project
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
-- Migration: 003_newsletter_subscribers
-- Purpose: stores email signups from resources.php
-- Import this yourself via Hostinger's phpMyAdmin.

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(190) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
-- Migration: 005_admin_users
-- Purpose: admin panel authentication + role-based access.
-- Import this yourself via Hostinger's phpMyAdmin.
-- After importing, create your first Super Admin user by running the
-- one-time setup script at /admin/setup.php (it disables itself after
-- the first account is created â€” see DEPLOYMENT-NOTES.md).

CREATE TABLE IF NOT EXISTS admin_users (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(160)  NOT NULL,
    email         VARCHAR(190)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('super_admin','admin','editor','content_writer','marketing','viewer') NOT NULL DEFAULT 'viewer',
    is_active     TINYINT(1)    NOT NULL DEFAULT 1,
    last_login_at DATETIME      NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Migration: 006_leads
-- Purpose: unified CRM lead record. Every real lead-generating form
-- (Contact, Book a Demo, Free Audit â€” NOT the newsletter signup, which
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
-- a row already exists â€” INSERT IGNORE skips it rather than erroring).
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
-- Migration: 009_website_optimizer
-- Purpose: the AI Website Optimizer's data layer â€” audit runs, per-page
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
-- Migration: 010_knowledge_engine
-- Purpose: the Website Knowledge Engine's data layer. The knowledge graph
-- itself (entities + relationships) is NOT duplicated into tables here â€”
-- it's built live from the real catalog files each time it's viewed, the
-- same pattern as the optimizer's page-inventory.php, so it can never
-- drift out of sync with the actual site content. These tables only store
-- the OUTPUT of analysis: the link graph discovered during crawls, and
-- generated recommendations/findings.

CREATE TABLE IF NOT EXISTS page_links (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    report_id   BIGINT UNSIGNED NOT NULL,
    source_url  VARCHAR(255) NOT NULL,
    target_url  VARCHAR(255) NOT NULL,
    INDEX idx_report_id (report_id),
    INDEX idx_source (source_url),
    INDEX idx_target (target_url),
    CONSTRAINT fk_page_links_report FOREIGN KEY (report_id) REFERENCES audit_reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS knowledge_recommendations (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category      VARCHAR(60) NOT NULL,  -- content_gap, entity_consistency, topical_authority, internal_linking, freshness
    entity_type   VARCHAR(60) NULL,      -- service, product, industry, article, case_study
    entity_slug   VARCHAR(190) NULL,
    title         VARCHAR(255) NOT NULL,
    description   TEXT NOT NULL,
    severity      ENUM('critical','warning','info') NOT NULL DEFAULT 'info',
    status        ENUM('open','resolved','dismissed') NOT NULL DEFAULT 'open',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at   DATETIME NULL,
    INDEX idx_category (category),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Migration: 011_cms_architecture
-- Purpose: Enterprise-grade CMS foundation handling media, content, settings,
-- revisions, team members, navigation, blogs, and redirects.

-- 1. Media Library
CREATE TABLE IF NOT EXISTS cms_media (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_path     VARCHAR(255) NOT NULL,
    title         VARCHAR(190) NULL,
    alt_text      VARCHAR(255) NULL,
    caption       TEXT NULL,
    description   TEXT NULL,
    category      VARCHAR(60) NULL,
    tags          VARCHAR(255) NULL,
    width         INT UNSIGNED NULL,
    height        INT UNSIGNED NULL,
    file_size     INT UNSIGNED NULL,
    mime_type     VARCHAR(100) NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Content Manager
CREATE TABLE IF NOT EXISTS cms_content (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_key   VARCHAR(190) NOT NULL UNIQUE,
    title         VARCHAR(190) NULL,
    content_value LONGTEXT NULL,
    type          ENUM('text','richtext','html','markdown','code','json','url') NOT NULL DEFAULT 'text',
    category      VARCHAR(60) NULL,
    page_slug     VARCHAR(100) NULL,
    status        ENUM('draft','published') NOT NULL DEFAULT 'published',
    updated_by    BIGINT UNSIGNED NULL,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_page_slug (page_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Version History
CREATE TABLE IF NOT EXISTS cms_revisions (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_name    VARCHAR(60) NOT NULL,
    record_id     BIGINT UNSIGNED NOT NULL,
    action        ENUM('create','update','delete') NOT NULL,
    old_value     LONGTEXT NULL,
    new_value     LONGTEXT NULL,
    created_by    BIGINT UNSIGNED NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_table_record (table_name, record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Website Settings
CREATE TABLE IF NOT EXISTS website_settings (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_group VARCHAR(60) NOT NULL, -- General, Brand, SEO, Social, Analytics, Contact, Email, Performance, AI, Security
    setting_key   VARCHAR(190) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    description   VARCHAR(255) NULL,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_group (setting_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Team Management
CREATE TABLE IF NOT EXISTS team_members (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug          VARCHAR(190) NOT NULL UNIQUE,
    full_name     VARCHAR(160) NOT NULL,
    display_name  VARCHAR(100) NULL,
    position      VARCHAR(160) NOT NULL,
    department    VARCHAR(100) NULL,
    bio           TEXT NULL,
    email         VARCHAR(190) NULL,
    phone         VARCHAR(40) NULL,
    website       VARCHAR(255) NULL,
    photo_id      BIGINT UNSIGNED NULL, -- References cms_media
    display_order INT NOT NULL DEFAULT 0,
    featured      TINYINT(1) NOT NULL DEFAULT 0,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_team_photo FOREIGN KEY (photo_id) REFERENCES cms_media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS team_socials (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team_id       BIGINT UNSIGNED NOT NULL,
    platform      VARCHAR(60) NOT NULL, -- linkedin, facebook, etc, or custom
    custom_icon   VARCHAR(255) NULL, -- SVG code or URL
    url           VARCHAR(255) NOT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_team_socials_team FOREIGN KEY (team_id) REFERENCES team_members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Navigation Manager
CREATE TABLE IF NOT EXISTS navigation_menus (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    menu_location VARCHAR(60) NOT NULL, -- primary, mega, footer, mobile
    parent_id     BIGINT UNSIGNED NULL,
    label         VARCHAR(100) NOT NULL,
    url           VARCHAR(255) NOT NULL,
    target        VARCHAR(20) NOT NULL DEFAULT '_self', -- _blank
    icon_svg      TEXT NULL,
    display_order INT NOT NULL DEFAULT 0,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    INDEX idx_location (menu_location),
    CONSTRAINT fk_nav_parent FOREIGN KEY (parent_id) REFERENCES navigation_menus(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Blog Manager
CREATE TABLE IF NOT EXISTS blog_categories (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug          VARCHAR(190) NOT NULL UNIQUE,
    name          VARCHAR(160) NOT NULL,
    description   TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_tags (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug          VARCHAR(190) NOT NULL UNIQUE,
    name          VARCHAR(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_posts (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug          VARCHAR(190) NOT NULL UNIQUE,
    title         VARCHAR(255) NOT NULL,
    content       LONGTEXT NULL,
    format        ENUM('html','markdown') NOT NULL DEFAULT 'html',
    excerpt       TEXT NULL,
    author_id     BIGINT UNSIGNED NULL,
    featured_img  BIGINT UNSIGNED NULL,
    meta_title    VARCHAR(255) NULL,
    meta_desc     VARCHAR(255) NULL,
    canonical_url VARCHAR(255) NULL,
    schema_type   VARCHAR(60) NOT NULL DEFAULT 'Article',
    status        ENUM('draft','published','scheduled') NOT NULL DEFAULT 'draft',
    published_at  DATETIME NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_blog_img FOREIGN KEY (featured_img) REFERENCES cms_media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_post_categories (
    post_id       BIGINT UNSIGNED NOT NULL,
    category_id   BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (post_id, category_id),
    CONSTRAINT fk_bpc_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_bpc_cat FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS blog_post_tags (
    post_id       BIGINT UNSIGNED NOT NULL,
    tag_id        BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    CONSTRAINT fk_bpt_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_bpt_tag FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Redirects Manager
CREATE TABLE IF NOT EXISTS redirects (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    old_url       VARCHAR(255) NOT NULL UNIQUE,
    new_url       VARCHAR(255) NOT NULL,
    type          ENUM('301','302','410') NOT NULL DEFAULT '301',
    clicks        INT UNSIGNED NOT NULL DEFAULT 0,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Forms Manager (Definitions)
CREATE TABLE IF NOT EXISTS forms (
    id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    form_key      VARCHAR(100) NOT NULL UNIQUE,
    name          VARCHAR(160) NOT NULL,
    notification_emails TEXT NULL, -- JSON array
    success_message TEXT NULL,
    redirect_url  VARCHAR(255) NULL,
    spam_protection TINYINT(1) NOT NULL DEFAULT 1,
    is_active     TINYINT(1) NOT NULL DEFAULT 1,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
