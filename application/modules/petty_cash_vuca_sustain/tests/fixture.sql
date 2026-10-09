CREATE TABLE payment_approve (
    id VARCHAR(40) PRIMARY KEY, no_doc VARCHAR(40), tipe VARCHAR(40), status INT DEFAULT 1,
    id_payment VARCHAR(40), tgl_bayar DATE, supplier VARCHAR(40), keterangan_pembayaran TEXT,
    coa_bank VARCHAR(40), nm_coa_bank VARCHAR(100), mata_uang VARCHAR(5), payment_bank DECIMAL(15,2),
    total_payment DECIMAL(15,2), selisih DECIMAL(15,2), link_doc TEXT, id_supplier VARCHAR(40),
    nm_supplier VARCHAR(100), kurs_payment DECIMAL(15,2)
) ENGINE=InnoDB;
CREATE TABLE tr_payment_paid (
    id VARCHAR(40) PRIMARY KEY, bank_charge DECIMAL(15,2), admin_charge_bearer VARCHAR(20),
    created_by INT, created_on DATETIME
) ENGINE=InnoDB;
CREATE TABLE tr_choosed_payment (id_user INT) ENGINE=InnoDB;
CREATE TABLE tr_jurnal (
    no_jurnal VARCHAR(40), tgl_jurnal DATE, coa VARCHAR(40), id_company INT, nm_company VARCHAR(40),
    nm_coa VARCHAR(100), debit DECIMAL(15,2), kredit DECIMAL(15,2), keterangan TEXT, sts VARCHAR(5),
    no_transaksi VARCHAR(100), jenis_transaksi VARCHAR(40), created_by INT, created_date DATETIME
) ENGINE=InnoDB;
CREATE TABLE coa_master (no_perkiraan VARCHAR(40) PRIMARY KEY, nama VARCHAR(100), kode_bank VARCHAR(20)) ENGINE=InnoDB;
CREATE TABLE master_oto_jurnal_detail (kode_master_jurnal VARCHAR(20), parameter_no VARCHAR(5), no_perkiraan VARCHAR(40)) ENGINE=InnoDB;
CREATE TABLE jurnaltras (
    nomor VARCHAR(100), tanggal DATE, tipe VARCHAR(10), no_perkiraan VARCHAR(40), keterangan TEXT,
    no_request VARCHAR(40), kredit DECIMAL(15,2), debet DECIMAL(15,2), no_reff VARCHAR(100),
    jenis_jurnal VARCHAR(20), nocust VARCHAR(40), stspos VARCHAR(5)
) ENGINE=InnoDB;
CREATE TABLE jurnal (
    tipe VARCHAR(10), nomor VARCHAR(100), tanggal DATE, no_perkiraan VARCHAR(40), keterangan TEXT,
    no_reff VARCHAR(100), debet DECIMAL(15,2), kredit DECIMAL(15,2)
) ENGINE=InnoDB;
CREATE TABLE japh (
    nomor VARCHAR(100), tgl DATE, jml DECIMAL(15,2), jenis_ap VARCHAR(5), bayar_kepada VARCHAR(100),
    kdcab VARCHAR(10), jenis_reff VARCHAR(10), no_reff VARCHAR(100), note TEXT, user_id INT, ho_valid VARCHAR(5)
) ENGINE=InnoDB;
CREATE TABLE pastibisa_tb_cabang (nocab VARCHAR(10), nobuk INT) ENGINE=InnoDB;
CREATE TABLE tr_kartu_hutang (
    tipe VARCHAR(10), nomor VARCHAR(100), tanggal DATE, no_perkiraan VARCHAR(40), keterangan TEXT,
    no_reff VARCHAR(100), debet DECIMAL(15,2), kredit DECIMAL(15,2), id_supplier VARCHAR(40),
    nama_supplier VARCHAR(100), no_request VARCHAR(100)
) ENGINE=InnoDB;
CREATE TABLE tr_pelaporan_petty_cash_detail (pelaporan_id INT, pencatatan_id INT) ENGINE=InnoDB;
CREATE TABLE tr_expense_petty_cash (
    id INT PRIMARY KEY, no_pencatatan VARCHAR(40), tanggal DATE, request_by VARCHAR(100), keterangan TEXT, grand_total DECIMAL(15,2)
) ENGINE=InnoDB;
INSERT INTO coa_master VALUES ('BANK', 'Bank Test', 'TEST');
INSERT INTO master_oto_jurnal_detail VALUES ('BUK001', '1', 'BANK'), ('BUK001', '3', 'AP');
INSERT INTO pastibisa_tb_cabang VALUES ('101', 0);
