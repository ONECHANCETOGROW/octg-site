-- Create Portal Modules Registry table to support dynamic modules without migrations.
CREATE TABLE IF NOT EXISTS `portal_modules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `icon` VARCHAR(50) NOT NULL DEFAULT 'package',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed the initial standard modules (including system modules like marketing_health)
INSERT INTO `portal_modules` (`slug`, `name`, `description`, `icon`, `sort_order`, `is_enabled`) VALUES
('google_ads', 'Google Ads', 'Google PPC campaigns and metrics.', 'target', 10, 1),
('seo', 'Website SEO', 'Search engine optimization, rankings, and backlinks.', 'search', 20, 1),
('gbp', 'Google Business Profile', 'Local map listing interactions and reviews.', 'map-pin', 30, 1),
('social', 'Social Media', 'Facebook, Instagram, LinkedIn, and YouTube metrics.', 'share-2', 40, 1),
('website_performance', 'Website Performance', 'Leads, phone calls, form completions, and conversion rates.', 'mouse-pointer-click', 50, 1),
('marketing_health', 'Overall Marketing Health', 'Computed overall marketing score across all active modules.', 'activity', 0, 0)
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `icon`=VALUES(`icon`);
