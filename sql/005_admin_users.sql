-- Migration: 005_admin_users
-- Purpose: admin panel authentication + role-based access.
-- Import this yourself via Hostinger's phpMyAdmin.
-- After importing, create your first Super Admin user by running the
-- one-time setup script at /admin/setup.php (it disables itself after
-- the first account is created — see DEPLOYMENT-NOTES.md).

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
