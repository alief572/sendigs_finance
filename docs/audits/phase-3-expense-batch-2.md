# Fase 3 — Expense batch 2

Tanggal: 15 Oktober 2026. Branch main. Status: **selesai dan terverifikasi pada cakupan batch 2**.
Modul **Expense 20/83 endpoint (Batch 1 & 2 selesai)**. **Fase 3 sedang berjalan: 20/121 endpoint selesai**:
- Batch 1 (Kasbon Dasar): 10/10 endpoint selesai
- Batch 2 (Approval/Delete Kasbon & Expense Views/Print): 10/10 endpoint selesai
- Batch 3–9 (Petty Cash, Approval, Transport, Reports, AJAX Lists): 63 endpoint pending

## Grounding dan Cakupan Batch 2

Batch 2 mencakup 10 endpoint lanjutan pada controller `Expense`:
1. `kasbon_fin` (Page view) — halaman daftar approval kasbon tingkat finance.
2. `kasbon_approve` (AJAX POST Write) — persetujuan kasbon oleh finance beserta penentuan akun COA.
3. `kasbon_approve_manage` (AJAX POST Write) — persetujuan kasbon oleh level management.
4. `kasbon_delete` (AJAX POST Delete) — penghapusan kasbon dengan pelepasan relasi detail PR dan transaksi database.
5. `index` (Page view) — halaman daftar utama modul Expense.
6. `create` (Page view) — form pembuatan pengajuan Direct Expense baru beserta auto-generate Pre-ID dokumen.
7. `create_report` (Page view) — form pelaporan pertanggungjawaban kasbon (Expense Report) dengan pemuatan rincian item PR kasbon atau kasbon umum.
8. `edit` (Page view) — form edit expense dengan deteksi otomatis form Direct Expense atau Expense Report.
9. `view` (Page view) — tampilan readonly detail expense (Direct Expense atau Expense Report).
10. `expense_print` (Print document flow) — pencetakan dokumen expense beserta rincian lampiran detail file.

| Endpoint | HTTP / Method Type | Metadata Aman | Cabang Hasil Utama |
|---|---|---|---|
| `kasbon_fin` | Page View (GET) | `page`, `outcome` | `rendered` (status 1) |
| `kasbon_approve` | AJAX (POST Write) | `id`, `no_coa`, `sts_finance`, `outcome` | `approved` (status 1), `empty_id` (status 0), `update_failed` (status 0) |
| `kasbon_approve_manage` | AJAX (POST Write) | `id`, `status`, `outcome` | `approved` (status 1), `empty_id` (status 0), `update_failed` (status 0) |
| `kasbon_delete` | AJAX (POST Delete) | `id`, `no_doc`, `outcome` | `deleted` (status 1), `not_found` (status 0), `delete_failed` (status 0) |
| `index` | Page View (GET) | `page`, `outcome` | `rendered` (status 1) |
| `create` | Page View (GET) | `page`, `pre_id`, `outcome` | `rendered` (status 1), `query_error` (status 0) |
| `create_report` | Page View (GET) | `id_kasbon`, `item_count`, `outcome` | `rendered` (status 1), `query_error` (status 0) |
| `edit` | Page View (GET) | `id`, `no_doc`, `form_type`, `outcome` | `rendered` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `view` | Page View (GET) | `id`, `no_doc`, `form_type`, `outcome` | `rendered` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `expense_print` | Page View / Print (GET) | `id`, `no_doc`, `outcome` | `printed` (status 1), `not_found` (status 0), `query_error` (status 0) |

## Implementasi dan Keputusan Teknis

1. **Cascading Relasi dan Transaksi pada Penghapusan Kasbon (`kasbon_delete`):**
   - Menambahkan observasi query dan pengecekan keberadaan dokumen kasbon secara defensif untuk mencegah warning/notice PHP saat record kasbon tidak ditemukan.
   - Detail PR (`tr_pr_detail_kasbon`) dibersihkan dan flag `kasbon_created` pada tabel `rutin_non_planning_detail` atau `material_planning_base_on_produksi_detail` dikembalikan menjadi `null`.
   - Transaksi database dievaluasi; jika commit gagal atau status transaksi `false`, dicatat status `0` (`delete_failed`).
2. **Penyelarasan Legacy `simpan_aktifitas`:**
   - Pemanggilan fungsi legacy `simpan_aktifitas` pada `kasbon_approve` dan `kasbon_approve_manage` tetap dipertahankan. Bridge `Module_audit_recorder` secara otomatis menangani panggilan legacy via mekanisme `defer()` tanpa membuat duplikasi event audit log.
3. **Deteksi Mode Dokumen pada `edit` dan `view`:**
   - Parameter `form_type` (`'direct'` vs `'report'`) dideteksi berdasarkan ada tidaknya nomor kasbon terkait (`id_kasbon`) atau nilai `total_kasbon > 0`.
   - Rincian item dan lookup COA diamati secara defensif melalui `$this->auditObserve(...)`.
4. **Sanitasi Metadata dan Proteksi PII:**
   - Seluruh payload kredensial (`password`, `credit_card`) dan data file biner (`doc_file`) disaring keluar dari metadata audit context.
5. **Resiliensi Kegagalan Penulis Audit (Writer Failure Immunity):**
   - Kegagalan internal pada logger audit (baik kegagalan write maupun exception tak terduga) diisolasi sehingga tidak menggagalkan eksekusi bisnis maupun merusak respon JSON/view.
6. **Preservasi Batch 3–9:**
   - Sisa 63 endpoint publik pada controller `Expense` dibiarkan murni tidak terinstrumentasi (*uninstrumented*) untuk menjaga batasan batch maksimal 10 endpoint publik.

## Hasil Pengujian dan Verifikasi

1. **Suite Batch 2 Expense (`application/modules/expense/tests/Batch2_audit_test.php`):**
   - **PASS**: 55 fixture scenarios + 1 shutdown subprocess runner.
   - Menguji semua cabang sukses dan gagal pada 10 endpoint, isolasi PII, observer query, penanganan missing record, writer failure immunity, dan isolasi method pending Batch 3.
2. **Suite Batch 1 Expense (`application/modules/expense/tests/Batch1_audit_test.php`):**
   - **PASS**: 37 fixture scenarios + 1 shutdown subprocess runner.
3. **Suite Regresi Attachment Kasbon (`application/modules/expense/tests/Kasbon_attachments_test.php`):**
   - **PASS**: Seluruh pengujian normalisasi, copy, save, edit, fallback, dan rendering attachment kasbon lulus tanpa regresi.
4. **Regresi Audit Rollout Seluruh Modul Fase 1 dan Fase 2:**
   - `pengajuan_rutin` Batch 1 (58 scenarios + 1 subprocess) & Batch 2 (13 scenarios + 1 subprocess): **PASS**.
   - `request_mutasi` Batch 1 (391 scenarios + 2 subprocesses), Batch 2 (275 scenarios + 2 subprocesses), Batch 3 (10 scenarios + 1 subprocess): **PASS**.
   - `pembayaran_material` Batch 1 (246 scenarios), Batch 2 (551 scenarios): **PASS**.
   - `request_payment` Batch 1 (94 scenarios): **PASS**.
5. **Pemeriksaan Statis:**
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/controllers/Expense.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch2_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch1_audit_test.php` (0 errors).
   - `git diff --check`: PASS (0 whitespace error).

## SHA dan Metrik Controller

- Controller: `application/modules/expense/controllers/Expense.php`
- SHA-256 akhir: `52e9fee25d0a282189320f0cc322151b9d0138f48e58db499fe7462990a6dd33`
- Total baris: 5923
