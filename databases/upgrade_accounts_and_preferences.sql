-- Run once against an existing sora database.
USE `sora`;

ALTER TABLE `properties`
  ADD COLUMN `pet_allowed` tinyint(1) NOT NULL DEFAULT 0 AFTER `rent`,
  ADD COLUMN `layout` varchar(20) NOT NULL DEFAULT '1K' AFTER `pet_allowed`;

UPDATE `properties` SET `layout` = '1R' WHERE `id` IN (2, 8, 15, 21, 25, 31, 38);
UPDATE `properties` SET `layout` = '1DK' WHERE `id` IN (6, 10, 14, 23, 29, 35, 42);
UPDATE `properties` SET `layout` = '1LDK' WHERE `id` IN (5, 9, 13, 18, 22, 27, 32, 37, 43, 48);
UPDATE `properties` SET `layout` = '2DK' WHERE `id` IN (12, 19, 24, 30, 34, 40, 45);
UPDATE `properties` SET `layout` = '2LDK' WHERE `id` IN (20, 28, 36, 41, 46);
UPDATE `properties` SET `layout` = '3LDK' WHERE `id` IN (17, 33, 39, 44, 47);
UPDATE `properties` SET `pet_allowed` = 1 WHERE `id` IN (5, 8, 11, 14, 18, 21, 24, 27, 30, 33, 36, 39, 42, 45, 48);

CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
