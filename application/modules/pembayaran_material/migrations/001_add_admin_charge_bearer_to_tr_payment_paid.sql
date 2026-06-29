-- Migration: Add admin_charge_bearer column to tr_payment_paid
-- Module: Pembayaran Material
-- Date: 2025-01-01
-- Description: Adds column to store who bears the admin charge (company or recipient)
-- Requirements: 5.1

ALTER TABLE `tr_payment_paid`
ADD COLUMN `admin_charge_bearer` VARCHAR(20) DEFAULT NULL
COMMENT 'Penanggung admin charge: company atau recipient';
