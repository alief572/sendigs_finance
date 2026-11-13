# Publikasi invoicing dan penerimaan_uang ke main - 2026-11-13

- Branch tujuan: main. Dasar: origin/main 68836263 (fast-forward, tanpa force).
- Sumber: development f63a425e (commit) ditambah `Penerimaan_uang.php` dan tes `Batch1/Batch2_audit_test.php` penerimaan_uang dari working copy (audit trail, belum ter-commit di development).
- Isi: modul invoicing dan penerimaan_uang (controller, model, library, view, tes, README), `assets/js/invoicing/*`, `assets/css/invoicing.css`, `application/libraries/Xlsx_export.php`, dokumen invoicing/penerimaan terkait.
- Fitur utama: UMKM Non-PKP (PPN 0, PPh Final 0,5%), tab Invoiced (card + ekspor .xlsx), filter tab Konsultasi, audit trail endpoint invoicing dan penerimaan_uang, guard COA kosong pada posting penerimaan.
- Tidak ikut: print_invoice_non_konsultasi.php (versi main lebih baru), jurnal_invoicing, jurnal_penerimaan, penerimaan_pph_23, report_piutang_per_invoice, approval_invoice_so, scratch, perubahan modul lain.

## Validasi (PHP 7.4 di container sendigs_finance-web, dari kandidat bersih)
- Lint 29 file PHP dan 5 file JS lulus.
- PASS penerimaan_uang: calculation, Umkm_view (12), Umkm_receipt_journal (212), Consulting_receipt_tobe, Batch1 (36), Batch2 (61).
- PASS invoicing: Consulting_calculation (128), Ui_render (385), List_filter (62), Invoiced_export (41), Posting_coa_guard (3), Print_invoice (142), Batch1 (161), Ui_client_test.js (51), Ui_list_client_test.js (77).
- Consulting_calculation_test.js: PASS 110 cek di workspace development; di kandidat tidak bisa spawn php host (tidak ada di PATH), pasangan PHP lulus di Docker.

## Batas
Tidak menjalankan browser, migrasi, atau Consulting_integration_test (butuh DB uji terisolasi). Tidak ada migrasi SQL baru di paket ini.
