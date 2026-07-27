CREATE TABLE IF NOT EXISTS `client_ai_import_jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `provider` varchar(50) NOT NULL DEFAULT 'google_ads_advisor',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `period_start` date NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `client_ai_imports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) NOT NULL,
  `section` varchar(50) NOT NULL,
  `prompt_used` longtext,
  `raw_response` longtext,
  `parsed_json` longtext,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`job_id`) REFERENCES `client_ai_import_jobs`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
