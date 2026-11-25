# Audit regresi PHP 8.5

Pembanding PHP 7.4.33 memakai snapshot Git `ccd47e6e`. Hasil PHP 8.5.13 awal diambil dari `e6b218a6`. Kedua snapshot menjalankan 51 skrip yang sama dengan Node, `E_ALL`, source read-only, dan jaringan keluar dinonaktifkan. Kolom yang bertanda MySQL atau fixture memakai hasil uji ulang di kedua runtime pada kondisi lokal yang sesuai.

## Hasil Docker lokal

- Suite final menjalankan 191 skrip. Tanpa DB, 169 lulus, 20 gagal, dan 2 meminta fixture. Warning dan notice pada skrip yang lulus berjumlah nol.
- Tiga skrip yang gagal tanpa DB lulus di MySQL sementara. Penerimaan stok lolos termasuk jalur rollback, Debt Posting lulus 142 cek, dan WAPU lulus 119 cek. Hasil gabungan adalah **172 lulus, 15 gagal pada kasus yang juga gagal di PHP 7.4, dan 4 memerlukan fixture data**.
- Seluruh 18 skrip yang awalnya melaporkan warning lulus tanpa warning. Tes Expense multipart lulus 380 cek dalam 70 permintaan. Download Pengajuan Rutin lulus 34 cek dan penolakan izin upload lulus dengan user non-root.
- Tes konsultansi di MariaDB 10.11 mencapai assertion lama yang sama tentang isolasi deposit per company pada PHP 7.4 dan PHP 8.5. Tes Paid Status juga gagal pada test double loader yang sama. Hasil tersebut dipertahankan untuk tindak lanjut terpisah.
- Impor aset dan report penerimaan server-side membutuhkan konfigurasi dan skema data aplikasi lokal. Dua tes pembayaran membutuhkan data pembayaran yang dikonfigurasi lewat `MATERIAL_TEST_DB_*`. Status 0 pada tes pembayaran lama muncul ketika koneksi gagal dan tidak mewakili eksekusi assertion.

## Perbandingan 51 skrip awal

| Skrip di bawah `application/modules` | PHP 7.4 | PHP 8.5 awal | PHP 8.5 akhir |
| --- | --- | --- | --- |
| `alokasi/tests/Parser_test.php` | Lulus | Warning | Lulus |
| `approval_perubahan_budget/tests/Controller_test.php` | Gagal | Gagal | Gagal |
| `asset/tests/import_database_test.php` | Butuh fixture | Gagal | Butuh fixture |
| `audit_trail/tests/Auth_test.php` | Gagal | Gagal | Gagal |
| `audit_trail/tests/Controller_test.php` | Lulus | Warning | Lulus |
| `budget_periodik/tests/Controller_test.php` | Gagal | Gagal | Gagal |
| `deposit_customer/tests/Index_render_test.php` | Lulus | Gagal | Lulus |
| `expense/tests/Batch10_audit_test.php` | Lulus | Warning | Lulus |
| `expense/tests/Batch6_audit_test.php` | Lulus | Gagal | Lulus |
| `expense/tests/Batch8_audit_test.php` | Lulus | Warning | Lulus |
| `expense/tests/Batch9_audit_test.php` | Lulus | Warning | Lulus |
| `expense/tests/Company_flow_test.php` | Gagal | Gagal | Gagal |
| `expense/tests/Expense_report_updated_test.php` | Lulus | Gagal | Lulus |
| `expense/tests/Query_state_test.php` | Gagal | Gagal | Gagal |
| `expense/tests/Return_bank_view_test.php` | Notice | Gagal | Lulus |
| `expense/tests/Subtabs_expense_test.php` | Gagal | Gagal | Gagal |
| `expense/tests/Upload_permission_failure_test.php` | Lulus (fixture) | Gagal | Lulus (fixture) |
| `expense/tests/Upload_requests_test.php` | Lulus | Gagal | Lulus |
| `expense/tests/Visibility_test.php` | Gagal | Gagal | Gagal |
| `expense_petty_cash/tests/Batch1_audit_test.php` | Lulus | Warning | Lulus |
| `expense_petty_cash/tests/Batch2_audit_test.php` | Lulus | Warning | Lulus |
| `expense_petty_cash/tests/Batch3_audit_test.php` | Lulus | Warning | Lulus |
| `incoming_stok/tests/cash_receipt_test.php` | Lulus (MySQL) | Gagal | Lulus (MySQL) |
| `invoicing/tests/Consulting_integration_test.php` | Gagal | Gagal | Gagal |
| `invoicing/tests/List_filter_test.php` | Gagal | Gagal | Gagal |
| `invoicing/tests/Posting_coa_guard_test.php` | Notice | Warning | Lulus |
| `invoicing/tests/Wapu_repair_test.php` | Lulus (MySQL) | Gagal | Lulus (MySQL) |
| `invoicing/tests/integration_test.php` | Gagal | Gagal | Gagal |
| `jurnal_all/tests/Audit_test.php` | Lulus | Warning | Lulus |
| `jurnal_all/tests/Challenger_stress_test.php` | Gagal | Gagal | Gagal |
| `jurnal_all/tests/Controller_test.php` | Notice | Warning | Lulus |
| `jurnal_all/tests/Debt_posting_test.php` | Lulus (MySQL) | Gagal | Lulus (MySQL) |
| `jurnal_all/tests/Javh_fallback_test.php` | Notice | Warning | Lulus |
| `jurnal_all/tests/Legacy_posting_test.php` | Lulus | Warning | Lulus |
| `jurnal_all/tests/Metadata_test.php` | Lulus | Warning | Lulus |
| `jurnal_all/tests/Petty_posting_test.php` | Lulus | Warning | Lulus |
| `jurnal_all/tests/Preview_order_test.php` | Notice | Warning | Lulus |
| `jurnal_all/tests/Report_accounting_number_test.php` | Notice | Warning | Lulus |
| `jurnal_all/tests/Revision_test.php` | Lulus | Warning | Lulus |
| `kwitansi_deposit/tests/Index_render_test.php` | Lulus | Gagal | Lulus |
| `kwitansi_deposit/tests/Spk_display_test.php` | Lulus | Gagal | Lulus |
| `outgoing_stok_atk/tests/Export_test.php` | Lulus | Gagal | Lulus |
| `pembayaran_material/tests/Payment_list_grouping_test.php` | Butuh fixture | Gagal | Butuh fixture |
| `pembayaran_material/tests/View_payment_new_test.php` | Butuh fixture | Gagal | Butuh fixture |
| `penerimaan_uang/tests/Add_template_contract_test.php` | Lulus | Gagal | Lulus |
| `penerimaan_uang/tests/Lookup_bootstrap_test.php` | Gagal | Gagal | Gagal |
| `penerimaan_uang/tests/Report_penerimaan_serverside_test.php` | Butuh fixture | Gagal | Butuh fixture |
| `penerimaan_uang/tests/integration_test.php` | Gagal | Gagal | Gagal |
| `pengajuan_rutin/tests/Download_doc_test.php` | Lulus (fixture) | Gagal | Lulus (fixture) |
| `petty_cash_vuca_sustain/tests/paid_status_test.php` | Gagal | Gagal | Gagal |
| `request_mutasi/tests/JournalRoutingTest.php` | Gagal | Gagal | Gagal |

Log per skrip, daftar target, dan runner Docker lokal tersimpan di `scratch/php85-regression/` dalam worktree. Tidak ada perubahan pada database aplikasi.
