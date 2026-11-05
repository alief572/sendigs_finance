-- Migration: Add status_approval column to tr_alokasi_detail
-- Module: Alokasi
-- Date: 2026-06-11
-- Description: Adds status_approval column to tr_alokasi_detail for review & approval feature on uploaded rekening koran
-- NOTE: Uses IF NOT EXISTS pattern - safe to run multiple times

ALTER TABLE `tr_alokasi_detail`
ADD COLUMN IF NOT EXISTS `status_approval` ENUM('pending', 'approved') NOT NULL DEFAULT 'approved' COMMENT 'Status approval review rekening koran' AFTER `cheque_no`;
