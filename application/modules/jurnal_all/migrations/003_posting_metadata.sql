CREATE TABLE IF NOT EXISTS tr_jurnal_postings (
    group_key CHAR(64) NOT NULL,
    source VARCHAR(8) NOT NULL,
    no_transaksi VARCHAR(255) NOT NULL,
    jenis_transaksi VARCHAR(64) NOT NULL,
    id_company VARCHAR(64) DEFAULT NULL,
    posted_at DATETIME NOT NULL,
    posted_by VARCHAR(64) NOT NULL,
    posted_by_name VARCHAR(191) NOT NULL,
    accounting_numbers TEXT NOT NULL,
    PRIMARY KEY (group_key),
    KEY idx_jurnal_posted_at (posted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
