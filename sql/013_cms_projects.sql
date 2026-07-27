-- Migration: 013_cms_projects
-- Purpose: Schema for project showcase and case studies.

CREATE TABLE IF NOT EXISTS cms_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(150) NOT NULL UNIQUE,
    client_name VARCHAR(150) NOT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL, -- service slug (e.g. ai-automation)
    service_name VARCHAR(150) NOT NULL, -- e.g. AI Automation
    industry VARCHAR(100) NOT NULL,
    short_description TEXT NOT NULL,
    challenge TEXT NULL,
    solution TEXT NULL,
    results TEXT NULL, -- JSON array of results: [['metric' => '...', 'label' => '...']]
    timeline TEXT NULL, -- JSON array of timeline phases: [['phase' => '...', 'title' => '...', 'body' => '...']]
    services_used TEXT NULL, -- JSON array of service slugs (e.g. ['ai-automation', 'local-seo'])
    quote TEXT NULL,
    quote_role VARCHAR(255) NULL,
    cta_text VARCHAR(150) NULL,
    cta_link VARCHAR(255) NULL,
    featured_image VARCHAR(255) NULL, -- Path or key
    gallery_images TEXT NULL, -- JSON array of image paths
    display_order INT DEFAULT 0,
    status ENUM('published', 'draft') NOT NULL DEFAULT 'published',
    is_featured BOOLEAN DEFAULT 0,
    meta_title VARCHAR(255) NULL,
    meta_description VARCHAR(255) NULL,
    og_image VARCHAR(255) NULL,
    has_case_study BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
