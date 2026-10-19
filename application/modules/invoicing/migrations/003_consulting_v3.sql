-- Run in the finance/ERP database before releasing consulting version 3.
DELIMITER $$
DROP PROCEDURE IF EXISTS migrate_consulting_v3$$
CREATE PROCEDURE migrate_consulting_v3()
BEGIN
 IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_invoicing' AND COLUMN_NAME='consulting_version') THEN
  ALTER TABLE `tr_invoicing` ADD COLUMN `consulting_version` TINYINT NULL;
 END IF;
 IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_invoicing' AND COLUMN_NAME='tax_treatment') THEN
  ALTER TABLE `tr_invoicing` ADD COLUMN `tax_treatment` VARCHAR(8) NULL;
 END IF;
 IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_invoicing' AND COLUMN_NAME='consulting_company_id') THEN
  ALTER TABLE `tr_invoicing` ADD COLUMN `consulting_company_id` VARCHAR(20) NULL;
 END IF;
 IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_invoicing' AND COLUMN_NAME='consulting_snapshot') THEN
  ALTER TABLE `tr_invoicing` ADD COLUMN `consulting_snapshot` LONGTEXT NULL;
 END IF;
 IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_invoicing' AND COLUMN_NAME='consulting_top_key') THEN
  ALTER TABLE `tr_invoicing` ADD COLUMN `consulting_top_key` VARCHAR(50) NULL;
 END IF;
 IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_penerimaan_piutang' AND COLUMN_NAME='consulting_batch') THEN
  ALTER TABLE `tr_penerimaan_piutang` ADD COLUMN `consulting_batch` TINYINT NULL;
 END IF;
 IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_penerimaan_piutang_detail' AND COLUMN_NAME='consulting_version') THEN
  ALTER TABLE `tr_penerimaan_piutang_detail` ADD COLUMN `consulting_version` TINYINT NULL;
 END IF;
 IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_penerimaan_piutang_detail' AND COLUMN_NAME='consulting_reduction') THEN
  ALTER TABLE `tr_penerimaan_piutang_detail` ADD COLUMN `consulting_reduction` DECIMAL(20,2) NULL;
 END IF;
 IF NOT EXISTS (SELECT 1 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_invoicing' AND INDEX_NAME='uq_consulting_top') THEN
  ALTER TABLE tr_invoicing ADD UNIQUE KEY uq_consulting_top (consulting_top_key);
 END IF;
 IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_penerimaan_piutang_detail' AND COLUMN_NAME='consulting_snapshot') THEN
  ALTER TABLE tr_penerimaan_piutang_detail ADD COLUMN consulting_snapshot LONGTEXT NULL;
 END IF;
END$$
CALL migrate_consulting_v3()$$
DROP PROCEDURE migrate_consulting_v3$$
DELIMITER ;
CREATE TABLE IF NOT EXISTS ms_invoice_coa_mapping (
 source_company_id VARCHAR(20) NOT NULL PRIMARY KEY,
 owner_company_id VARCHAR(20) NOT NULL,
 accounts LONGTEXT NOT NULL,
 updated_by VARCHAR(100) NOT NULL,
 updated_at DATETIME(6) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS ms_invoice_sequence (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 kode_entitas VARCHAR(10) NOT NULL,
 tahun INT NOT NULL,
 last_sequence INT NOT NULL,
 updated_at DATETIME NULL,
 updated_by VARCHAR(100) NULL,
 UNIQUE KEY uq_invoice_entity_year (kode_entitas,tahun)
) ENGINE=InnoDB;
START TRANSACTION;
SET @invoice_menu = (SELECT MIN(id) FROM menus WHERE link IN ('invoicing','invoicing/index'));
INSERT INTO permissions (nm_permission,ket,id_menu,nm_menu,created_on,created_by)
SELECT 'Invoicing.Configure','Configure',@invoice_menu,'Invoicing',NOW(),'7'
WHERE @invoice_menu IS NOT NULL AND NOT EXISTS (SELECT 1 FROM permissions WHERE nm_permission='Invoicing.Configure');
INSERT IGNORE INTO user_permissions (id_user,id_permission)
SELECT 7,id_permission FROM permissions WHERE nm_permission='Invoicing.Configure';
COMMIT;
