-- Run on the selected ERP schema. See 012_check_rp_transfer.sql before and after.
-- MySQL DDL commits implicitly. Each statement can be rerun after interruption.
CREATE TABLE IF NOT EXISTS tr_rp_transfer (
    id INT NOT NULL AUTO_INCREMENT,
    id_pengajuan INT NOT NULL,
    urutan INT NOT NULL,
    bank VARCHAR(100) NOT NULL,
    no_rek VARCHAR(80) NOT NULL,
    nama VARCHAR(160) NOT NULL,
    admin DECIMAL(20,2) NOT NULL DEFAULT 0,
    bearer ENUM('company','recipient') NOT NULL DEFAULT 'company',
    status ENUM('pending','approved','cancelled','paid') NOT NULL DEFAULT 'pending',
    id_payment VARCHAR(40) DEFAULT NULL,
    actual_bearer ENUM('company','recipient','free') DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_rp_transfer_sequence (id_pengajuan, urutan),
    UNIQUE KEY uq_rp_transfer_voucher (id_payment),
    CONSTRAINT fk_rp_transfer_header FOREIGN KEY (id_pengajuan) REFERENCES tr_rp_pengajuan_h (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @rp_ddl = IF(EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_rp_pengajuan_d' AND COLUMN_NAME='id_transfer'),
    'DO 0', 'ALTER TABLE tr_rp_pengajuan_d ADD COLUMN id_transfer INT DEFAULT NULL, ADD CONSTRAINT fk_rp_detail_transfer FOREIGN KEY (id_transfer) REFERENCES tr_rp_transfer (id)');
PREPARE rp_statement FROM @rp_ddl;
EXECUTE rp_statement;
DEALLOCATE PREPARE rp_statement;

SET @rp_ddl = IF(EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_rp_pengajuan_d' AND COLUMN_NAME='keterangan_finance'),
    'DO 0', 'ALTER TABLE tr_rp_pengajuan_d ADD COLUMN keterangan_finance TEXT CHARACTER SET utf8mb4 DEFAULT NULL');
PREPARE rp_statement FROM @rp_ddl;
EXECUTE rp_statement;
DEALLOCATE PREPARE rp_statement;

SET @rp_ddl = IF(EXISTS(SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tr_rp_pengajuan_d' AND COLUMN_NAME='payment_approval_id'),
    'DO 0', 'ALTER TABLE tr_rp_pengajuan_d ADD COLUMN payment_approval_id VARCHAR(40) CHARACTER SET utf8mb4 DEFAULT NULL, ADD UNIQUE KEY uq_rp_detail_payment_approval (payment_approval_id)');
PREPARE rp_statement FROM @rp_ddl;
EXECUTE rp_statement;
DEALLOCATE PREPARE rp_statement;

CREATE TABLE IF NOT EXISTS tr_rp_transfer_file (
    id INT NOT NULL AUTO_INCREMENT,
    id_transfer INT NOT NULL,
    id_detail INT DEFAULT NULL COMMENT 'NULL = proof of payment for the transfer',
    nama VARCHAR(255) NOT NULL,
    mime VARCHAR(80) NOT NULL,
    content MEDIUMBLOB NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_rp_file_transfer FOREIGN KEY (id_transfer) REFERENCES tr_rp_transfer (id),
    CONSTRAINT fk_rp_file_detail FOREIGN KEY (id_detail) REFERENCES tr_rp_pengajuan_d (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
