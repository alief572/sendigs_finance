-- Migration: Add nilai_terpakai column to tr_alokasi_split
-- Module: Alokasi / Penerimaan Uang
-- Date: 2026-06-12
-- Description: Adds nilai_terpakai column to tr_alokasi_split so each split can track its own usage independently
-- NOTE: Uses IF NOT EXISTS pattern - safe to run multiple times

ALTER TABLE `tr_alokasi_split`
ADD COLUMN IF NOT EXISTS `nilai_terpakai` DECIMAL(15,2) DEFAULT NULL COMMENT 'Nilai yang sudah terpakai per split' AFTER `nominal`;
