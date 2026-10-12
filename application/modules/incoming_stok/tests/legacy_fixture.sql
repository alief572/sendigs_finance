-- Minimal legacy dependencies for regression; never loaded into an application database.
CREATE TABLE tr_purchase_order (no_po VARCHAR(50) PRIMARY KEY, no_surat VARCHAR(50), status VARCHAR(5), id_suplier VARCHAR(20), close_po VARCHAR(5), persen_disc DOUBLE DEFAULT 0) ENGINE=InnoDB;
CREATE TABLE dt_trans_po (id INT PRIMARY KEY, no_po VARCHAR(50), idpr INT, tipe VARCHAR(20), idmaterial VARCHAR(20), namamaterial VARCHAR(100), qty DOUBLE, qty_in DOUBLE DEFAULT 0, hargasatuan DOUBLE, persen_disc DOUBLE DEFAULT 0) ENGINE=InnoDB;
CREATE TABLE tr_kasbon (no_doc VARCHAR(50) PRIMARY KEY, id_pr VARCHAR(50), tipe_pr VARCHAR(30), status VARCHAR(5), sts_incoming VARCHAR(5)) ENGINE=InnoDB;
CREATE TABLE tr_pr_detail_kasbon (id INT PRIMARY KEY, id_kasbon VARCHAR(50), id_material VARCHAR(20), nm_material VARCHAR(100), qty DOUBLE, qty_in DOUBLE DEFAULT 0, harga DOUBLE, unit VARCHAR(20), type_pr VARCHAR(20)) ENGINE=InnoDB;
CREATE TABLE accessories_category (id INT PRIMARY KEY, category VARCHAR(50)) ENGINE=InnoDB;
CREATE TABLE rutin_non_planning_header (no_pengajuan VARCHAR(50), no_pr VARCHAR(50)) ENGINE=InnoDB;
CREATE TABLE rutin_non_planning_detail (id INT, no_pengajuan VARCHAR(50)) ENGINE=InnoDB;
CREATE TABLE users (id_user INT, username VARCHAR(50), nm_lengkap VARCHAR(100)) ENGINE=InnoDB;
CREATE TABLE so_internal_spk_material (kode_det VARCHAR(50), weight DOUBLE) ENGINE=InnoDB;
CREATE TABLE tr_jurnal (id INT AUTO_INCREMENT PRIMARY KEY, no_jurnal VARCHAR(50), tgl_jurnal DATE, coa VARCHAR(50), id_company VARCHAR(50), nm_company VARCHAR(100), nm_coa VARCHAR(100), debit DECIMAL(20,2), kredit DECIMAL(20,2), keterangan TEXT, no_transaksi VARCHAR(50), jenis_transaksi VARCHAR(50), id_divisi VARCHAR(50), nm_divisi VARCHAR(100), created_by VARCHAR(50), created_date DATETIME) ENGINE=InnoDB;
