# Fase 3 — Expense batch 7

Tanggal: 15 Oktober 2026. Branch main. Status: **selesai dan terverifikasi pada cakupan batch 7**.
Modul **Expense 70/83 endpoint (Batch 1, 2, 3, 4, 5, 6, & 7 selesai)**. **Fase 3 sedang berjalan: 70/121 endpoint selesai**:
- Batch 1 (Kasbon Dasar): 10/10 endpoint selesai
- Batch 2 (Approval/Delete Kasbon & Expense Views/Print): 10/10 endpoint selesai
- Batch 3 (Petty Cash Print, Approvals, Expense Save/Delete, Budget Check, Transport Mgmt): 10/10 endpoint selesai
- Batch 4 (Transport Request Finance, All, List, Create, Save, Edit, Print, View, Transport List, Create): 10/10 endpoint selesai
- Batch 5 (Transport Save, List Req Transport, Get Transport, Transport Edit, Transport View, Transport Req Approve, Transport Approve, Transport Delete, Transport Req Delete, Petty Cash): 10/10 endpoint selesai
- Batch 6 (Petty Cash Create/Edit/View, Get COA Budget, Reject, Reject Manage, Review, Return Confirm, Get PR Non-PO, Del Detail): 10/10 endpoint selesai
- Batch 7 (Kasbon Non-PO Detail Delete, Kasbon PR Fetch, Ganti Expense Detail Add, Refresh Kasbon Non-PR, Refresh Expense Kembalian, Kembalian Expense Detail Delete, Copy PR Doc, Transport Input List, Transport Req Finance List, Transport Req Approval List): 10/10 endpoint selesai
- Batch 8–9 (Transport Req All, COA Name, Kasbon Fin Manage, Kasbon Lists, Expense Approvals, Jurnal Expense): 13 endpoint pending

## Grounding dan Cakupan Batch 7

Batch 7 mencakup 10 endpoint lanjutan terkait transaksi kasbon non-PO, integrasi kasbon PR, penambahan detail penggantian sisa expense, refresh tampilan tabel kasbon dan kembalian, penghapusan detail kembalian expense, salin dokumen lampiran PR (rutin dan aset), serta endpoint DataTables daftar pengajuan transportasi pada controller `Expense`:
1. `del_detail_kasbon_non_po` (AJAX POST Delete) — penghapusan baris item detail pada `tr_expense_detail` dan reset `id_expense_pett_pr_non_po` pada `tr_kasbon` dalam transaksi database dengan strict commit check `$this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE`.
2. `add_kasbon_pr` (AJAX POST Read) — pembacaan data kasbon berdasarkan `no_doc` untuk pemetaan ke form kasbon PR.
3. `add_ganti_expense` (AJAX POST Write) — penambahan detail penggantian expense ke `tr_expense_detail` dalam transaksi database dengan strict commit check dan penanganan gracefully jika record expense sumber tidak ditemukan.
4. `refresh_list_kasbon_non_pr` (AJAX POST Read) — pembuatan ulang baris HTML tabel daftar kasbon non-PR yang belum dialokasikan ke expense.
5. `refresh_list_expense_kembalian` (AJAX POST Read) — pembuatan ulang baris HTML tabel daftar sisa expense yang siap dibayarkan/dikembalikan.
6. `del_detail_kembalian_expense` (AJAX POST Delete) — penghapusan baris item detail kembalian pada `tr_expense_detail` dan reset `expense_id_kembalian` pada `tr_expense` dalam transaksi database dengan strict commit check.
7. `copy_pr_doc` (AJAX POST Read) — pemetaan dan penyalinan referensi dokumen lampiran PR (baik dari `rutin_non_planning_header` maupun `tran_pr_header`) beserta resolusi path lampiran via `kasbon_attachment_names()`.
8. `get_data_transport_input` (DataTables AJAX Read) — pemuatan server-side data transport input milik user aktif via `Expense_model`.
9. `get_data_transport_req_fin_list` (DataTables AJAX Read) — pemuatan server-side daftar pengajuan transportasi level finance via `Expense_model`.
10. `get_data_transport_req` (DataTables AJAX Read) — pemuatan server-side daftar pengajuan transportasi yang diajukan oleh user aktif via `Expense_model`.

| Endpoint | HTTP / Method Type | Metadata Aman | Cabang Hasil Utama |
|---|---|---|---|
| `del_detail_kasbon_non_po` | AJAX (POST Delete) | `id_detail`, `id_kasbon_non_po`, `outcome` | `deleted` (status 1), `delete_failed` (status 0) |
| `add_kasbon_pr` | AJAX (POST Read) | `no_doc`, `outcome` | `found` (status 1), `not_found` (status 0) |
| `add_ganti_expense` | AJAX (POST Write) | `no_doc`, `id_detail`, `outcome` | `created` (status 1), `not_found` (status 0), `insert_failed` (status 0) |
| `refresh_list_kasbon_non_pr` | AJAX (POST Read) | `no_doc`, `row_count`, `outcome` | `rendered` (status 1) |
| `refresh_list_expense_kembalian` | AJAX (POST Read) | `no_doc`, `row_count`, `outcome` | `rendered` (status 1) |
| `del_detail_kembalian_expense` | AJAX (POST Delete) | `id_detail`, `id_expense_kembalian`, `outcome` | `deleted` (status 1), `delete_failed` (status 0) |
| `copy_pr_doc` | AJAX (POST Read) | `no_pr`, `source_type`, `attachment_count`, `outcome` | `found` (status 1), `not_found` (status 1) |
| `get_data_transport_input` | DataTables AJAX (POST Read) | `page`, `draw`, `outcome` | `rendered` (status 1) |
| `get_data_transport_req_fin_list` | DataTables AJAX (POST Read) | `page`, `draw`, `outcome` | `rendered` (status 1) |
| `get_data_transport_req` | DataTables AJAX (POST Read) | `page`, `draw`, `outcome` | `rendered` (status 1) |

## Implementasi dan Keputusan Teknis

1. **Integritas Transaksi dan Strict Commit Check:**
   - Diterapkan pada seluruh operasi write Batch 7: `del_detail_kasbon_non_po`, `add_ganti_expense`, dan `del_detail_kembalian_expense`.
   - Mengganti pemanggilan longgar `$this->db->trans_start()` pada `add_ganti_expense` dengan `$this->db->trans_begin()` dan strict check `$this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE` agar transaksi database atomik dan rollback konsisten saat terjadi gangguan jaringan atau database commit failure.
2. **Penanganan Missing Record pada Operasi Berelasi:**
   - Pada `add_kasbon_pr`, jika kasbon tidak ditemukan, respons JSON mengembalikan default kosong tanpa notice PHP, dan audit mencatat outcome `not_found` (status 0).
   - Pada `add_ganti_expense`, jika expense induk tidak ditemukan, sistem membatalkan operasi penulisan, mencatat outcome `not_found`, dan mengembalikan fallback JSON yang aman.
   - Pada `copy_pr_doc`, pencarian dilakukan berurutan ke `rutin_non_planning_header` kemudian `tran_pr_header`. Jika dokumen tidak ada di kedua tabel, dicatat outcome `not_found` dengan `source_type = 'none'`.
3. **Penyelarasan Siklus Eksekusi DataTables:**
   - Ketiga method DataTables (`get_data_transport_input`, `get_data_transport_req_fin_list`, `get_data_transport_req`) diinstrumentasi dengan audit recorder sebelum mendelegasikan query dan respon JSON ke `Expense_model`.
   - Menghindari pemutusan aliran eksekusi tanpa audit trail, dan memastikan pencatatan request audit selesai sebelum atau bersamaan dengan rendering tabel.
4. **Sanitasi Metadata dan Proteksi PII:**
   - Field `password`, `credit_card`, dan payload file biner `doc_file` disaring keluar dari audit safe data.
   - Field `nama` tidak disimpan ke dalam payload audit metadata.
5. **Kekebalan terhadap Kegagalan Penulis Audit (Writer Failure Immunity):**
   - Kegagalan internal audit logger diisolasi sepenuhnya; respons bisnis (baik berupa JSON maupun HTML tabel) tetap dikirimkan ke client tanpa terganggu.
6. **Preservasi Batch 8–9:**
   - Sebanyak 13 endpoint publik sisa pada controller `Expense` dibiarkan murni tidak terinstrumentasi (*uninstrumented*) untuk menjaga batasan batch maksimal 10 endpoint publik.

## Hasil Pengujian dan Verifikasi

1. **Suite Batch 7 Expense (`application/modules/expense/tests/Batch7_audit_test.php`):**
   - **PASS**: 48 fixture scenarios + 1 shutdown subprocess runner.
   - Menguji semua cabang sukses dan gagal pada 10 endpoint, kegagalan transaksi, commit failure, missing record, variasi dokumen PR rutin vs aset, rendering HTML baris tabel kasbon dan kembalian, pemuatan DataTables model, sanitasi PII, writer failure immunity, dan isolasi proteksi uninstrumented Batch 8.
2. **Suite Batch 1–6 Expense:**
   - `Batch6_audit_test.php`: **PASS** (50 fixture scenarios + 1 subprocess).
   - `Batch5_audit_test.php`: **PASS** (57 fixture scenarios + 1 subprocess).
   - `Batch4_audit_test.php`: **PASS** (51 fixture scenarios + 1 subprocess).
   - `Batch3_audit_test.php`: **PASS** (59 fixture scenarios + 1 subprocess).
   - `Batch2_audit_test.php`: **PASS** (54 fixture scenarios + 1 subprocess).
   - `Batch1_audit_test.php`: **PASS** (36 fixture scenarios + 1 subprocess).
   - Total pengujian modul Expense: 355 skenario fixtures + 7 subprocess shutdown runner PASS.
3. **Regresi Audit Rollout Seluruh Modul Fase 1 dan Fase 2:**
   - `pengajuan_rutin` Batch 1 (58 scenarios + 1 subprocess) & Batch 2 (13 scenarios + 1 subprocess): **PASS**.
   - `request_mutasi` Batch 1 (391 scenarios + 2 subprocesses), Batch 2 (275 scenarios + 2 subprocesses), Batch 3 (10 scenarios + 1 subprocess): **PASS**.
   - `pembayaran_material` Batch 1 (246 scenarios), Batch 2 (551 scenarios): **PASS**.
   - `request_pr_stok` (24 endpoints regression suite): **PASS**.
   - `Kasbon_attachments_test.php` (kasbon attachments regression suite): **PASS**.
4. **Pemeriksaan Statis:**
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/controllers/Expense.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch7_audit_test.php` (0 errors).
   - `git diff --check`: PASS (0 whitespace error).

## SHA dan Metrik Controller

- Controller: `application/modules/expense/controllers/Expense.php`
- SHA-256 akhir: `A30CAC4AB0398B5BA4541AC438E37B3036176131DCF1B7FD55D4689E12D06EA7`
- Total baris: 6625

## File yang Diubah

1. `application/modules/expense/controllers/Expense.php` — Instrumentasi 10 endpoint Batch 7 dengan audit recorder dan strict commit checks.
2. `application/modules/expense/tests/Batch7_audit_test.php` — Test suite baru Batch 7 (48 skenario fixture + 1 subprocess).
3. `application/modules/expense/tests/Batch6_audit_test.php` — Penyesuaian pending list ke batch 8.
4. `application/modules/expense/tests/Batch5_audit_test.php` — Penyesuaian pending list ke batch 8.
5. `application/modules/expense/tests/Batch4_audit_test.php` — Penyesuaian pending list ke batch 8.
6. `application/modules/expense/tests/Batch3_audit_test.php` — Penyesuaian pending list ke batch 8.
7. `application/modules/expense/tests/Batch2_audit_test.php` — Penyesuaian pending list ke batch 8.
8. `application/modules/expense/tests/Batch1_audit_test.php` — Penyesuaian pending list ke batch 8.
9. `docs/audits/active-menu-audit-2026-10-10.json` — Pembaruan manifest untuk 10 endpoint Batch 7 (sudah dikerjakan, nomor baris, status selesai, controller sha256).
10. `docs/audits/phase-3-expense-batch-7.md` — Laporan pembuktian implementasi dan pengujian Batch 7.
