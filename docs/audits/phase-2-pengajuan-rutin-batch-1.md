# Fase 2 — Pengajuan Rutin batch 1

Tanggal: 14 Oktober 2026. Branch main. Status: **selesai dan terverifikasi pada cakupan batch 1**.
Fase 2: **49/51 endpoint selesai** (Pembayaran Material 18/18, Request Mutasi 21/21, Pengajuan Rutin 10/12). 2 endpoint pending untuk Batch 2.

## Grounding dan Cakupan Batch 1

Sesuai aturan batas maksimal 10 endpoint publik per batch controller, modul `Pengajuan_rutin` (12 endpoint baseline) dibagi menjadi:
- **Batch 1 (10 endpoint aktif):** `get_user_department`, `index`, `app_list`, `create`, `edit`, `view`, `get_data`, `save_data`, `hapus_data`, `approve`.
- **Batch 2 (2 endpoint pending):** `reject`, `get_pengajuan_periodik`.

| Endpoint | HTTP / Method Type | Metadata Aman | Cabang Hasil Utama |
|---|---|---|---|
| `get_user_department` | Lookup helper & public | `department_id`, `outcome` | `resolved` (status 1), `unresolved` (status 1/0 pada query fail) |
| `index` | View (Render) | `department_id`, `outcome` | `rendered` (status 1), `query_error` (status 0) |
| `app_list` | View (Render) | `outcome` | `rendered` (status 1), `query_error` (status 0) |
| `create` | View (Form) | `department_id`, `outcome`, `reason` | `dept_unconfigured` (status 0), `rendered` (status 1), `query_error` (status 0) |
| `edit` | View (Form) | `id`, `no_doc`, `department_id`, `outcome`, `reason` | `not_found` (status 0), `unauthorized` (status 0), `rendered` (status 1), `query_error` (status 0) |
| `view` | View (Detail) | `id`, `no_doc`, `department_id`, `app`, `outcome`, `reason` | `not_found` (status 0), `unauthorized` (status 0), `rendered` (status 1), `query_error` (status 0) |
| `get_data` | AJAX (Read) | `department_id`, `no_doc`, `outcome` | `fetched` (status 1), `query_error` (status 0) |
| `save_data` | AJAX (Write) | `no_doc`, `department_id`, `amount`, `item_count`, `outcome`, `reason` | `already_submitted` (status 0), `commit_failed` (status 0), `exception` (status 0), `saved` (status 1) |
| `hapus_data` | AJAX (Delete) | `no_doc`, `outcome` | `empty_id` (status 0), `unauthorized` (status 0), `delete_failed` (status 0), `deleted` (status 1) |
| `approve` | AJAX (Write) | `id`, `outcome` | `empty_id` (status 0), `update_failed` (status 0), `approved` (status 1) |

## Implementasi dan Keputusan Teknis

1. **Pencegahan Nested Scope pada Helper Lookup (`get_user_department`):**
   - Method `get_user_department` dipanggil secara internal oleh hampir semua method (`index`, `create`, `edit`, `view`, `hapus_data`, `get_pengajuan_periodik`).
   - Menggunakan guard scope `$startedAudit = $this->beginAudit(...)`. Ketika dipanggil internal di dalam controller yang sedang memiliki scope audit aktif, `beginAudit` mengembalikan `false` dan `completeAudit` dilewati sehingga tidak menutup scope controller induk secara prematur.
2. **Kesesuaian Transaksi dan Komitmen Audit:**
   - Pada `save_data`, komitmen audit sukses (`status = 1`, `outcome = 'saved'`) hanya dipanggil setelah `$this->db->trans_commit()` berhasil dieksekusi.
   - Pada `hapus_data`, transaksi dievaluasi secara eksplisit: jika `trans_status() === FALSE`, dilakukan `trans_rollback()` dan dicatat `delete_failed` (status 0); jika berhasil, dilakukan `trans_commit()` dan dicatat `deleted` (status 1).
3. **Penyelarasan Legacy `simpan_aktifitas()`:**
   - Pemanggilan `simpan_aktifitas()` eksisting dipertahankan utuh pada `hapus_data` dan `approve`.
   - `Module_audit_recorder` secara otomatis menduplikasi / mengabaikan pemanggilan legacy ganda selama scope recorder sedang berjalan via metode `defer()`.
4. **Resiliensi Kegagalan Penulis Audit (Writer Failure Immunity):**
   - Kegagalan pencatatan audit (baik exception maupun storage failure) tidak membatalkan atau mengubah response bisnis (misal `save: true` atau `delete: 1`).
5. **Preservasi Pending Batch 2:**
   - Method `reject` dan `get_pengajuan_periodik` dibiarkan murni uninstrumented tanpa inisialisasi audit scope, terverifikasi dalam unit test batch 1.

## Hasil Pengujian dan Verifikasi

1. **Suite Batch 1 (`application/modules/pengajuan_rutin/tests/Batch1_audit_test.php`):**
   - **PASS**: 60 fixture scenarios + 1 shutdown subprocess.
   - Seluruh 10 endpoint teruji pada semua jalur sukses, validasi, otorisasi, gagal query, gagal transaksi, writer immunity, dan shutdown handler.
2. **Regresi Audit Rollout Seluruh Modul Sebelumnya:**
   - `request_mutasi/tests/Batch1_audit_test.php`: **PASS** (391 fixture scenarios + 2 shutdown subprocesses).
   - `request_mutasi/tests/Batch2_audit_test.php`: **PASS** (275 fixture scenarios + 2 shutdown subprocesses).
   - `request_mutasi/tests/Batch3_audit_test.php`: **PASS** (10 fixture scenarios + 1 shutdown subprocess).
   - `pembayaran_material/tests/Batch1_test.php`: **PASS** (246 fixture scenarios).
   - `pembayaran_material/tests/Batch2_test.php`: **PASS** (551 fixture scenarios).
   - `approval_request_payment/tests/Audit_test.php`: **PASS** (60 scenarios).
   - `record_request_payment/tests/Audit_test.php`: **PASS** (57 scenarios).
   - `request_payment/tests/Audit_test.php`: **PASS** (94 scenarios).
3. **Pemeriksaan Statis:**
   - PHP Lint: 0 syntax error pada `Pengajuan_rutin.php` dan `Batch1_audit_test.php`.
   - `git diff --check`: PASS (0 whitespace error).

## SHA dan Metrik Controller

- Controller: `application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php`
- SHA-256 akhir: `3c6155f73a53d5c5bc03761691728492b6cf0aa15b9e71704c9c86e090e8cc10`
- Total baris: 934
