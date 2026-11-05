-- Rollback 017_add_plan_bayar_nominal.sql
ALTER TABLE `tr_invoice_ar` DROP COLUMN `plan_bayar_nominal`;
