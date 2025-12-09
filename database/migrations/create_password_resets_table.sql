-- Create password_resets table for forgot password feature
-- Note: user_id matches users.id data type (INT without UNSIGNED)
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `code` VARCHAR(6) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `attempts` INT(11) DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_code` (`code`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

