DROP TABLE IF EXISTS `clients`;
CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `business_name` varchar(255) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `monthly_budget` decimal(10,2) DEFAULT NULL,
  `target_cpa` decimal(10,2) DEFAULT NULL,
  `target_locations` text DEFAULT NULL,
  `status` enum('active','inactive','onboarding') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `clients` (`id`, `business_name`, `website`, `industry`, `contact_person`, `email`, `phone`, `monthly_budget`, `target_cpa`, `target_locations`, `status`, `created_at`) VALUES ('1', 'INDEPENDENT RV, LLC', 'https://www.independentrvca.com/', 'RV dealer in California', 'Bobby', 'bobby@independentrvca.com', '+1-209-814-6891', NULL, NULL, NULL, 'active', '2026-07-16 21:36:11');


DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activity_logs` (`id`, `client_id`, `user_id`, `action`, `details`, `created_at`) VALUES ('1', '1', '1', 'Generated Marketing Intelligence Report: INDEPENDENT RV, LLC 2026 JULY', NULL, '2026-07-17 04:08:13');
INSERT INTO `activity_logs` (`id`, `client_id`, `user_id`, `action`, `details`, `created_at`) VALUES ('2', '1', '1', 'Generated Marketing Intelligence Report for Independent Rv', NULL, '2026-07-17 19:45:04');
INSERT INTO `activity_logs` (`id`, `client_id`, `user_id`, `action`, `details`, `created_at`) VALUES ('3', '1', '1', 'Generated Marketing Intelligence Report for Independent Rv', NULL, '2026-07-17 19:46:01');
INSERT INTO `activity_logs` (`id`, `client_id`, `user_id`, `action`, `details`, `created_at`) VALUES ('4', '1', '1', 'Generated Marketing Intelligence Report for Independent Rv', NULL, '2026-07-17 19:46:08');
INSERT INTO `activity_logs` (`id`, `client_id`, `user_id`, `action`, `details`, `created_at`) VALUES ('5', '1', '1', 'Generated Marketing Intelligence Report for Independent Rv', NULL, '2026-07-17 19:46:25');


