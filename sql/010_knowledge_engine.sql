-- Migration: 010_knowledge_engine
-- Purpose: the Website Knowledge Engine's data layer. The knowledge graph
-- itself (entities + relationships) is NOT duplicated into tables here —
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
