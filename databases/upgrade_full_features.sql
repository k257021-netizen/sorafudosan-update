-- Safe to import repeatedly in phpMyAdmin. Existing accounts are kept.
USE `sora`;

SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='properties' AND column_name='pet_allowed')=0, 'ALTER TABLE properties ADD COLUMN pet_allowed tinyint(1) NOT NULL DEFAULT 0 AFTER rent', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='properties' AND column_name='layout')=0, 'ALTER TABLE properties ADD COLUMN layout varchar(20) NOT NULL DEFAULT ''1K'' AFTER pet_allowed', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='properties' AND column_name='area_sqm')=0, 'ALTER TABLE properties ADD COLUMN area_sqm decimal(6,2) DEFAULT NULL AFTER layout', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='properties' AND column_name='floor_number')=0, 'ALTER TABLE properties ADD COLUMN floor_number smallint DEFAULT NULL AFTER area_sqm', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='properties' AND column_name='initial_cost')=0, 'ALTER TABLE properties ADD COLUMN initial_cost int(11) DEFAULT NULL AFTER floor_number', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='properties' AND column_name='status')=0, 'ALTER TABLE properties ADD COLUMN status varchar(20) NOT NULL DEFAULT ''available'' AFTER layout', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='properties' AND column_name='updated_at')=0, 'ALTER TABLE properties ADD COLUMN updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;

SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='customers' AND column_name='email_verified_at')=0, 'ALTER TABLE customers ADD COLUMN email_verified_at datetime DEFAULT NULL', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='customers' AND column_name='terms_accepted_at')=0, 'ALTER TABLE customers ADD COLUMN terms_accepted_at datetime DEFAULT NULL', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='customers' AND column_name='privacy_accepted_at')=0, 'ALTER TABLE customers ADD COLUMN privacy_accepted_at datetime DEFAULT NULL', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;

SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='inquiries' AND column_name='status')=0, 'ALTER TABLE inquiries ADD COLUMN status varchar(20) NOT NULL DEFAULT ''new''', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='inquiries' AND column_name='admin_note')=0, 'ALTER TABLE inquiries ADD COLUMN admin_note text DEFAULT NULL', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='inquiries' AND column_name='privacy_accepted_at')=0, 'ALTER TABLE inquiries ADD COLUMN privacy_accepted_at datetime DEFAULT NULL', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='inquiries' AND column_name='updated_at')=0, 'ALTER TABLE inquiries ADD COLUMN updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;

CREATE TABLE IF NOT EXISTS `customer_favorites` (
  `customer_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`customer_id`, `property_id`),
  KEY `property_id` (`property_id`),
  CONSTRAINT `customer_favorites_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_favorites_property_fk` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `customer_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `purpose` varchar(20) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `customer_purpose` (`customer_id`, `purpose`),
  CONSTRAINT `customer_tokens_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `auth_login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rate_key` char(64) NOT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `rate_key_attempted_at` (`rate_key`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
