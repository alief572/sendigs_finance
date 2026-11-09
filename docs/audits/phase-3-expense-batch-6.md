# Fase 3 — Expense batch 6

Tanggal: 15 Oktober 2026. Branch main. Status: **selesai dan terverifikasi pada cakupan batch 6**.
Modul **Expense 60/83 endpoint (Batch 1, 2, 3, 4, 5, & 6 selesai)**. **Fase 3 sedang berjalan: 60/121 endpoint selesai**:
- Batch 1 (Kasbon Dasar): 10/10 endpoint selesai
- Batch 2 (Approval/Delete Kasbon & Expense Views/Print): 10/10 endpoint selesai
- Batch 3 (Petty Cash Print, Approvals, Expense Save/Delete, Budget Check, Transport Mgmt): 10/10 endpoint selesai
- Batch 4 (Transport Request Finance, All, List, Create, Save, Edit, Print, View, Transport List, Create): 10/10 endpoint selesai
- Batch 5 (Transport Save, List Req Transport, Get Transport, Transport Edit, Transport View, Transport Req Approve, Transport Approve, Transport Delete, Transport Req Delete, Petty Cash): 10/10 endpoint selesai
- Batch 6 (Petty Cash Create/Edit/View, Get COA Budget, Reject, Reject Manage, Review, Return Confirm, Get PR Non-PO, Del Detail): 10/10 endpoint selesai
- Batch 7–9 (Petty Cash Kasbon PR, Ganti Expense, Refresh Lists, Approvals, Details, Reports, AJAX Lists): 23 endpoint pending

## Grounding dan Cakupan Batch 6

Batch 6 mencakup 10 endpoint lanjutan terkait form pembuatan, pengubahan, peninjauan petty cash, resolusi AJAX COA budget, penolakan (reject level finance & management), review expense report, konfirmasi pengembalian dana, resolusi rincian PR Non-PO, serta penghapusan detail expense pada controller `Expense`:
1. `create_pc` (Page view) — form pembuatan petty cash baru beserta pemuatan combo budget, petty cash, kasbon PR, dan daftar penggantian kasbon.
2. `edit_pc` (Page view) — form edit petty cash beserta data header, detail, dan budget terkait.
3. `view_pc` (Page view) — tampilan detail petty cash dalam mode read-only view (`stsview = view`).
4. `getcoabudget` (AJAX POST Read) — pengambilan combo options COA master untuk budget petty cash tanpa pemanggilan `die()`.
5. `reject` (AJAX POST Write) — penolakan pengajuan ke status 9 dalam transaksi database dengan strict commit check `$this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE`.
6. `reject_manage` (AJAX POST Write) — penolakan pengajuan level management ke status 9 dengan integrasi legacy bridge `simpan_aktifitas` dan transaksi database.
7. `review` (Page view) — halaman peninjauan (*review*) expense report beserta pemuatan data header, detail, departemen, user, dan COA bank.
8. `return_confirm` (AJAX POST Write) — konfirmasi pengembalian sisa expense dengan validasi batas pengembalian (`valid = 1` normal, `valid = 2` jika total melebihi sisa expense), insert ke `tr_pengembalian_expense`, dan strict transaction commit check.
9. `get_pr_non_po` (AJAX POST Read) — resolusi rincian PR non-PO untuk stok material, departemen, dan aset ke format baris tabel HTML JSON.
10. `del_detail` (AJAX POST Delete) — penghapusan baris item detail pada `tr_expense_detail` dalam transaksi database dengan strict commit check.

| Endpoint | HTTP / Method Type | Metadata Aman | Cabang Hasil Utama |
|---|---|---|---|
| `create_pc` | Page View (GET) | `page`, `outcome` | `rendered` (status 1) |
| `edit_pc` | Page View (GET) | `id`, `no_doc`, `outcome` | `rendered` (status 1), `not_found` (status 1) |
| `view_pc` | Page View (GET) | `id`, `no_doc`, `outcome` | `rendered` (status 1), `not_found` (status 1) |
| `getcoabudget` | AJAX (POST Read) | `coa`, `count`, `outcome` | `success` (status 1) |
| `reject` | AJAX (POST Write) | `id`, `table`, `reason`, `outcome` | `rejected` (status 1), `empty_id` (status 0), `update_failed` (status 0), `transaction_failed` (status 0) |
| `reject_manage` | AJAX (POST Write) | `id`, `table`, `reason`, `outcome` | `rejected` (status 1), `empty_id` (status 0), `update_failed` (status 0) |
| `review` | Page View (GET) | `id`, `no_doc`, `outcome` | `rendered` (status 1), `not_found` (status 1) |
| `return_confirm` | AJAX (POST Write) | `id`, `no_doc`, `valid`, `outcome` | `confirmed` (status 1), `over_limit` (status 0, valid 2), `empty_id` (status 0), `insert_failed` (status 0) |
| `get_pr_non_po` | AJAX (POST Read) | `no_pr`, `valid`, `outcome` | `found` (status 1, valid 1), `not_found` (status 1, valid 0) |
| `del_detail` | AJAX (POST Delete) | `id_detail`, `outcome` | `deleted` (status 1), `delete_failed` (status 0) |

## Implementasi dan Keputusan Teknis

1. **Integritas Transaksi dan Validasi Batas pada `return_confirm()`:**
   - Evaluasi nilai transfer balik pengembalian dihitung bersama riwayat `tr_pengembalian_expense` yang sudah ada. Jika akumulasi melebihi sisa expense, sistem menetapkan `valid = 2` dan mencatat outcome `over_limit` tanpa melakukan penulisan database.
   - Penulisan ke `tr_pengembalian_expense` dievaluasi secara strict dengan `$this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE` untuk menjamin konsistensi data finansial.
2. **Pembersihan dan Pengembalian Data Bersih pada AJAX Read (`getcoabudget`):**
   - Menghilangkan pemanggilan `die()` agar eksekusi controller dapat kembali secara teratur setelah menyelesaikan pencatatan audit trail dan mengirimkan string opsi HTML ke client.
3. **Penyelarasan Transaksi pada Operasi Reject (`reject` & `reject_manage`):**
   - Operasi `reject` membungkus pembaruan status 9 dalam `trans_begin()`, strict transaction commit check, dan penanganan exception dengan HTTP status 400 bila gagal.
   - Operasi `reject_manage` dilengkapi transaksi database dengan strict commit check serta mempertahankan pemanggilan legacy `simpan_aktifitas()` yang ditangani oleh mekanisme `defer()` recorder.
4. **Penanganan Missing Record pada Detail Form (`edit_pc`, `view_pc`, `review`):**
   - Header diperiksa ketersediaannya melalui `$this->auditObserve($data)`. Jika record tidak ditemukan, audit mencatat outcome `not_found` tanpa memicu fatal PHP error saat mengakses properti `$data->no_doc`.
5. **Sanitasi Metadata dan Proteksi PII:**
   - Seluruh payload kredensial (`password`, `credit_card`) dan data file biner (`doc_file`) disaring keluar dari metadata audit context.
   - Field `nama` tidak dimasukkan ke dalam allowlist recorder untuk mencegah kebocoran informasi identitas karyawan.
6. **Kekebalan terhadap Kegagalan Penulis Audit (Writer Failure Immunity):**
   - Kegagalan internal pada logger audit (baik kegagalan write maupun exception tak terduga) diisolasi sehingga tidak menggagalkan eksekusi bisnis maupun merusak respon JSON/view.
7. **Preservasi Batch 7–9:**
   - Sebanyak 23 endpoint publik sisa pada controller `Expense` dibiarkan murni tidak terinstrumentasi (*uninstrumented*) untuk menjaga batasan batch maksimal 10 endpoint publik.

## Hasil Pengujian dan Verifikasi

1. **Suite Batch 6 Expense (`application/modules/expense/tests/Batch6_audit_test.php`):**
   - **PASS**: 50 fixture scenarios + 1 shutdown subprocess runner.
   - Menguji semua cabang sukses dan gagal pada 10 endpoint, validasi limit pengembalian (`valid = 1` dan `valid = 2`), pembatalan/penolakan finance dan manajemen, penghapusan baris detail, observasi query, penanganan missing record, writer failure immunity, dan isolasi method pending Batch 7.
2. **Suite Batch 5 Expense (`application/modules/expense/tests/Batch5_audit_test.php`):**
   - **PASS**: 57 fixture scenarios + 1 shutdown subprocess runner.
3. **Suite Batch 4 Expense (`application/modules/expense/tests/Batch4_audit_test.php`):**
   - **PASS**: 51 fixture scenarios + 1 shutdown subprocess runner.
4. **Suite Batch 3 Expense (`application/modules/expense/tests/Batch3_audit_test.php`):**
   - **PASS**: 59 fixture scenarios + 1 shutdown subprocess runner.
5. **Suite Batch 2 Expense (`application/modules/expense/tests/Batch2_audit_test.php`):**
   - **PASS**: 54 fixture scenarios + 1 shutdown subprocess runner.
6. **Suite Batch 1 Expense (`application/modules/expense/tests/Batch1_audit_test.php`):**
   - **PASS**: 36 fixture scenarios + 1 shutdown subprocess runner.
7. **Regresi Audit Rollout Seluruh Modul Fase 1 dan Fase 2:**
   - `pengajuan_rutin` Batch 1 (58 scenarios + 1 subprocess) & Batch 2 (13 scenarios + 1 subprocess): **PASS**.
   - `request_mutasi` Batch 1 (391 scenarios + 2 subprocesses), Batch 2 (275 scenarios + 2 subprocesses), Batch 3 (10 scenarios + 1 subprocess): **PASS**.
   - `pembayaran_material` Batch 1 (246 scenarios), Batch 2 (551 scenarios): **PASS**.
   - `request_pr_stok` (24 endpoints regression suite): **PASS**.
   - `Kasbon_attachments_test.php` (kasbon attachments regression suite): **PASS**.
8. **Pemeriksaan Statis:**
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/controllers/Expense.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch6_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch5_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch4_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch3_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch2_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch1_audit_test.php` (0 errors).
   - `git diff --check`: PASS (0 whitespace error).

## SHA dan Metrik Controller

- Controller: `application/modules/expense/controllers/Expense.php`
- SHA-256 akhir: `21363FD42F42218EBD976F54F98FF9986FF3F17E82EA9E4F473EEAED7BEA3A26`
- Total baris: 6490

## File yang Diubah

1. `application/modules/expense/controllers/Expense.php` — Instrumentasi 10 endpoint Batch 6 dengan audit recorder dan strict commit checks.
2. `application/modules/expense/tests/Batch6_audit_test.php` — Test suite baru Batch 6 (50 skenario fixture + 1 subprocess).
3. `application/modules/expense/tests/Batch5_audit_test.php` — Penyelarasan uninstrumented method check ke Batch 7.
4. `application/modules/expense/tests/Batch4_audit_test.php` — Penyelarasan uninstrumented method check ke Batch 7.
5. `application/modules/expense/tests/Batch3_audit_test.php` — Penyelarasan uninstrumented method check ke Batch 7.
6. `application/modules/expense/tests/Batch2_audit_test.php` — Penyelarasan uninstrumented method check ke Batch 7.
7. `application/modules/expense/tests/Batch1_audit_test.php` — Penyelarasan uninstrumented method check ke Batch 7.
8. `docs/audits/active-menu-audit-2026-10-10.json` — Pembaruan manifest status rollout Batch 6, nomor baris, metrik dan hash controller.
9. `docs/audits/phase-3-expense-batch-6.md` — Laporan pembuktian implementasi dan pengujian Batch 6.
10. `docs/audits/audit-rollout-checkpoint.md` — Checkpoint pemulihan konteks rollout audit.
11. `docs/task-progress.md` — Checkpoint progres keseluruhan proyek.
12. `docs/implementation-plan.md` — Pembaruan rencana implementasi Fase 3 Batch 6.
