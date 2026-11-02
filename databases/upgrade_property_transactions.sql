-- Add rental/sale prices and rental cost details to an existing sora database.
-- Safe to import repeatedly; existing listings keep their current rent values.
USE `sora`;

SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='properties' AND column_name='transaction_type')=0, 'ALTER TABLE properties ADD COLUMN transaction_type varchar(10) NOT NULL DEFAULT ''rent'' AFTER school', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
ALTER TABLE properties MODIFY COLUMN rent int(11) DEFAULT NULL;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='properties' AND column_name='sale_price')=0, 'ALTER TABLE properties ADD COLUMN sale_price bigint(20) DEFAULT NULL AFTER rent', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='properties' AND column_name='management_fee')=0, 'ALTER TABLE properties ADD COLUMN management_fee int(11) DEFAULT NULL AFTER sale_price', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='properties' AND column_name='deposit_amount')=0, 'ALTER TABLE properties ADD COLUMN deposit_amount int(11) DEFAULT NULL AFTER management_fee', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
SET @sora_ddl = IF((SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='properties' AND column_name='key_money_amount')=0, 'ALTER TABLE properties ADD COLUMN key_money_amount int(11) DEFAULT NULL AFTER deposit_amount', 'SELECT 1');
PREPARE sora_stmt FROM @sora_ddl; EXECUTE sora_stmt; DEALLOCATE PREPARE sora_stmt;
