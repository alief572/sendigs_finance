CREATE TABLE IF NOT EXISTS tr_jurnal_payment_reference_aliases (
    run_id CHAR(32) NOT NULL,
    journal_id BIGINT UNSIGNED NOT NULL,
    original_no_transaksi TEXT NOT NULL,
    voucher VARCHAR(191) NOT NULL,
    approval_ids TEXT NOT NULL,
    active TINYINT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (run_id, journal_id),
    KEY active_journal (journal_id, active),
    KEY active_voucher (voucher, active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
