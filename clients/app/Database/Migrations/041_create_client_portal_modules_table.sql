-- Per-client module visibility (spec section 6, "Manage Dashboard
-- Modules"). Absence of a row means ENABLED (default-on) -- only
-- explicit disables are stored, so existing/new clients never need a
-- seed row per module to work correctly.
CREATE TABLE IF NOT EXISTS `client_portal_modules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_id` INT NOT NULL,
    `module_code` VARCHAR(40) NOT NULL,
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `client_portal_modules_unique` (`client_id`, `module_code`),
    FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
