# Fase 2 — Pengajuan Rutin batch 2

Tanggal: 14 Oktober 2026. Branch main. Status: **selesai dan terverifikasi pada cakupan batch 2**.
Modul **Pengajuan Rutin 12/12 endpoint (100% tuntas)**. **Fase 2 resmi 51/51 endpoint selesai (100% tuntas)**:
- Pembayaran Material: 18/18 endpoint
- Request Mutasi: 21/21 endpoint
- Pengajuan Rutin: 12/12 endpoint

## Grounding dan Cakupan Batch 2

Batch 2 mencakup 2 endpoint penutup modul `Pengajuan_rutin`:
1. `reject` (AJAX Write) — menolak pengajuan rutin dengan mencatat alasan penolakan (`reject_ket`), user penolak, dan tanggal reject.
2. `get_pengajuan_periodik` (DataTables Server-side Read) — membaca dan memformat daftar pengajuan periodik per departemen / seluruh departemen (admin) dengan pencarian, pengurutan, pagination, dan lookup status pembayaran serta keterangan detail.

| Endpoint | HTTP / Method Type | Metadata Aman | Cabang Hasil Utama |
|---|---|---|---|
| `reject` | AJAX (Write) | `id`, `outcome`, `reason` | `empty_id` (status 0), `update_failed` (status 0), `rejected` (status 1) |
| `get_pengajuan_periodik` | AJAX (Read) | `records_total`, `records_filtered`, `outcome` | `fetched` (status 1), `query_error` (status 0) |

## Implementasi dan Keputusan Teknis

1. **Preservasi Scope pada Panggilan Helper Internal:**
   - Method `get_pengajuan_periodik` memanggil `$this->get_user_department()` untuk menentukan filter departemen user non-admin.
   - Karena `get_pengajuan_periodik` telah membuka scope audit aktif (`beginAudit`), pemanggilan internal ke `get_user_department` tidak menduplikasi audit scope dan tidak menutup scope induk secara prematur.
2. **Pengamatan Query pada Pagination Server-Side:**
   - Seluruh tahapan query dalam `get_pengajuan_periodik` (`count_all`, `count_filter`, `get_data`, lookup departemen HRIS, dan lookup pembayaran) dipantau melalui `$this->auditObserve(...)`.
   - Jika terjadi kegagalan query database, status dicatat `0` dengan `outcome = 'query_error'`. Jika berhasil, status dicatat `1` dengan metadata `records_total` dan `records_filtered`.
3. **Pencatatan Penolakan Dokumen (`reject`):**
   - Validasi parameter `$id` kosong dicatat dengan status `0` (`empty_id`).
   - Eksekusi penolakan melalui model `update_batch` dievaluasi: jika sukses dicatat status `1` (`rejected`) beserta metadata `reason` yang telah disanitasi; jika gagal dicatat status `0` (`update_failed`).
   - Pemanggilan legacy `simpan_aktifitas()` tetap dipertahankan dan ditangani otomatis oleh bridge `Module_audit_recorder`.
4. **Resiliensi Kegagalan Penulis Audit (Writer Failure Immunity):**
   - Kegagalan pencatatan audit (storage error maupun exception) tidak mengubah respon bisnis JSON DataTables maupun respon JSON `save: true` pada `reject`.

## Hasil Pengujian dan Verifikasi

1. **Suite Batch 2 Pengajuan Rutin (`application/modules/pengajuan_rutin/tests/Batch2_audit_test.php`):**
   - **PASS**: 13 fixture scenarios + 1 shutdown subprocess.
   - Menguji semua cabang `reject` (empty id, model fail, success with reason, writer immunity) dan `get_pengajuan_periodik` (admin fetch, non-admin dept filter, search filter, db query error, hris query error, writer immunity, delegated scope preservation, shutdown interruption).
2. **Suite Batch 1 Pengajuan Rutin (`application/modules/pengajuan_rutin/tests/Batch1_audit_test.php`):**
   - **PASS**: 58 fixture scenarios + 1 shutdown subprocess.
3. **Regresi Audit Rollout Seluruh Modul Fase 1 dan Fase 2:**
   - `request_mutasi` Batch 1 (391 scenarios + 2 subprocesses), Batch 2 (275 scenarios + 2 subprocesses), Batch 3 (10 scenarios + 1 subprocess): **PASS**.
   - `pembayaran_material` Batch 1 (246 scenarios), Batch 2 (551 scenarios): **PASS**.
   - `request_payment` Batch 1 (94 scenarios), `approval_request_payment` (60 scenarios), `record_request_payment` (57 scenarios): **PASS**.
4. **Pemeriksaan Statis:**
   - PHP Lint: 0 syntax error pada `Pengajuan_rutin.php`, `Batch1_audit_test.php`, dan `Batch2_audit_test.php`.
   - `git diff --check`: PASS (0 whitespace error).

## SHA dan Metrik Controller

- Controller: `application/modules/pengajuan_rutin/controllers/Pengajuan_rutin.php`
- SHA-256 akhir: `cde04ae6a8f0275905593560a6dfc7da8a14fb073cebf9766a661ace7d72c04c`
- Total baris: 977
