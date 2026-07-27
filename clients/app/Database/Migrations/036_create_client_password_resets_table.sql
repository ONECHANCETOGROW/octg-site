CREATE TABLE IF NOT EXISTS `client_password_resets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_user_id` INT NOT NULL,
    `token_hash` VARCHAR(255) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `used_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `client_password_resets_client_user_id_index` (`client_user_id`),
    FOREIGN KEY (`client_user_id`) REFERENCES `client_users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
