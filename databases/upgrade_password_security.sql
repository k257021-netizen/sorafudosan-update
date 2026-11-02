-- Safe to import repeatedly after upgrade_full_features.sql.
USE `sora`;

SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='customers' AND column_name='password_version')=0, 'ALTER TABLE customers ADD COLUMN password_version int(10) unsigned NOT NULL DEFAULT 0 AFTER password_hash', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;

CREATE TABLE IF NOT EXISTS `auth_security_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `event_type` varchar(40) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_event_created` (`customer_id`, `event_type`, `created_at`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `auth_security_events_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
