-- Refuses destructive rollback after version 3 data has been created.
DELIMITER $$
DROP PROCEDURE IF EXISTS rollback_consulting_v3$$
CREATE PROCEDURE rollback_consulting_v3()
BEGIN
 IF EXISTS (SELECT 1 FROM tr_invoicing WHERE consulting_version=3) OR EXISTS (SELECT 1 FROM tr_penerimaan_piutang WHERE consulting_batch=3) THEN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Version 3 transactions exist; preserve schema and reconcile before rollback.';
 END IF;
 ALTER TABLE `tr_invoicing` DROP COLUMN `consulting_version`;
 ALTER TABLE `tr_invoicing` DROP COLUMN `tax_treatment`;
 ALTER TABLE `tr_invoicing` DROP COLUMN `consulting_company_id`;
 ALTER TABLE `tr_invoicing` DROP COLUMN `consulting_snapshot`;
 ALTER TABLE `tr_invoicing` DROP COLUMN `consulting_top_key`;
 ALTER TABLE `tr_penerimaan_piutang` DROP COLUMN `consulting_batch`;
 ALTER TABLE `tr_penerimaan_piutang_detail` DROP COLUMN `consulting_version`;
 ALTER TABLE `tr_penerimaan_piutang_detail` DROP COLUMN `consulting_reduction`;
 ALTER TABLE tr_penerimaan_piutang_detail DROP COLUMN consulting_snapshot;
 DROP TABLE IF EXISTS ms_invoice_coa_mapping;
 DELETE up FROM user_permissions up JOIN permissions p ON p.id_permission=up.id_permission WHERE p.nm_permission='Invoicing.Configure';
 DELETE FROM permissions WHERE nm_permission='Invoicing.Configure';
END$$
CALL rollback_consulting_v3()$$
DROP PROCEDURE rollback_consulting_v3$$
DELIMITER ;
