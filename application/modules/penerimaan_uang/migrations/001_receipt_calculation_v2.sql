-- Apply before deploying receipt v2. Historical rows deliberately remain NULL.
ALTER TABLE tr_penerimaan_piutang_detail
    ADD COLUMN penyesuaian_pph DECIMAL(20,2) NULL,
    ADD COLUMN saldo_piutang_sebelum DECIMAL(20,2) NULL,
    ADD COLUMN versi_perhitungan TINYINT UNSIGNED NULL,
    ADD INDEX idx_penerimaan_invoice_header (id_inv, id_header);
ALTER TABLE tr_jurnal
    ADD INDEX idx_penerimaan_transaction_coa (no_transaksi, jenis_transaksi, coa);
