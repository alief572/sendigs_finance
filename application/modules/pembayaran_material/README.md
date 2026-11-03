# Peralatan koreksi voucher BK--1026-0002

CLI ini khusus kasus development lokal BK--1026-0002, bukan endpoint pembayaran umum. Konfigurasi development menyediakan kredensial; koneksi lokal menggunakan port3307. Tidak menjalankan posting accounting atau membuat pembayaran baru.

- cli/reconcile_bk_1026_0002.php: pemeriksaan READ ONLY dan rollback terhadap snapshot historis sebelas baris. Tanpa mode apply. Setelah koreksi, perbedaan baseline memang diharapkan.
- cli/complete_bk_1026_0002_coa.php: default dry-run; --apply hanya melengkapi dua kode serta dua parent dari master global ke master STM. ID source, saldo dan mutasi tidak disalin; kode/nama/level dipertahankan. Master MyISAM memakai table lock, backup sebelum write dan pelaporan hasil parsial tanpa klaim rollback.
- cli/repair_bk_1026_0002.php: default dry-run; --review=docs/bk-1026-0002-repair-review.json --apply memerlukan review serta preflight lengkap. Mengisi company tiga Cash dan satu kredit bank dengan snapshot/lock/transaksi/backup. Marker deterministik mencegah kredit kedua. Tidak mengubah accounting/counter/jurnaltras/approval/status pembayaran.

Pada development lokal koreksi sudah diterapkan: Payment8713202 dan refill207000 masing-masing balance, seluruh staging tetap pending. Backup dan bukti sebelum/sesudah hanya disimpan lokal di scratch, tidak dipublikasikan. Laporan: docs/bk-1026-0002-source-repair-2026-10-21.md. Script bukan migrasi otomatis; push kode tidak menerapkan koreksi pada environment lain.

Dependensi library Payment_journal_validator ikut dipublikasikan agar runner koreksi dapat memvalidasi cents/balance. Integrasi validator pada save_payment dan perubahan audit Material lokal berada di luar paket publikasi ini.

Pengujian PHP: tests/Journal_validator_test.php, Reconciliation_test.php, Voucher_repair_test.php, Coa_completion_test.php. Suite Voucher_repair_mysql_test.php dan Coa_completion_mysql_test.php membuat schema acak disposable pada localhost3307; set JOURNAL_TEST_PASSWORD untuk instance fixture. Keduanya hanya membaca struktur/master aplikasi sebagai acuan dan menulis pada schema fixture, lalu membersihkannya. Jangan menjalankan CLI --apply untuk pengujian; tidak perlu browser.
