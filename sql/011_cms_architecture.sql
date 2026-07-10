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
