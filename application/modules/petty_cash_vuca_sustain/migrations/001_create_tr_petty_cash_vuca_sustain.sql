-- Migration: Create table tr_petty_cash_vuca_sustain
-- Module: petty_cash_vuca_sustain
-- Description: Payment hutang inter-company VUCA/SUSTAIN terhadap STM

CREATE TABLE IF NOT EXISTS `tr_petty_cash_vuca_sustain` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `no_payment_hutang` VARCHAR(20) NOT NULL COMMENT 'Format: PHP-YYYY-NNNN',
    `no_pelaporan` VARCHAR(20) NOT NULL COMMENT 'No pelaporan asal (RPC-YYYY-NNNN)',
    `pelaporan_id` INT(11) UNSIGNED NOT NULL COMMENT 'FK ke tr_pelaporan_petty_cash.id',
    `company` ENUM('VUCA', 'SUSTAIN') NOT NULL,
    `periode_start` DATE NOT NULL,
    `periode_end` DATE NOT NULL,
    `jumlah_pencatatan` INT(11) NOT NULL DEFAULT 0,
    `grand_total` DECIMAL(15,0) NOT NULL DEFAULT 0,
    `status` VARCHAR(20) NOT NULL DEFAULT 'draft' COMMENT 'draft|waiting payment|done payment',
    `nama_pembuat` VARCHAR(150) NULL COMMENT 'Nama pembuat pelaporan asal',
    `created_on` DATETIME NULL,
    `created_by` INT(11) UNSIGNED NULL,
    `modified_on` DATETIME NULL,
    `modified_by` INT(11) UNSIGNED NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_no_payment_hutang` (`no_payment_hutang`),
    KEY `idx_pelaporan_id` (`pelaporan_id`),
    KEY `idx_status` (`status`),
    KEY `idx_company` (`company`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Payment hutang inter-company VUCA/SUSTAIN';
