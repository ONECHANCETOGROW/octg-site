-- Migration: 003_newsletter_subscribers
-- Purpose: stores email signups from resources.php
-- Import this yourself via Hostinger's phpMyAdmin.

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(190) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
