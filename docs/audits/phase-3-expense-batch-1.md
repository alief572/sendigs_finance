# Fase 3 — Expense batch 1

Tanggal: 14 Oktober 2026. Branch main. Status: **selesai dan terverifikasi pada cakupan batch 1**.
Modul **Expense 10/83 endpoint (Batch 1 selesai)**. Sisa 73 endpoint pending untuk Batch 2–9:
- Batch 1 (Kasbon Dasar): 10/10 endpoint selesai
- Batch 2–9 (Expense, Transport, Petty Cash, Approval, Reports, AJAX Lists): 73 endpoint pending

## Grounding dan Cakupan Batch 1

Batch 1 mencakup 10 endpoint kasbon dasar pada controller `Expense`:
1. `kasbon` (Page view) — halaman form input pengajuan kasbon.
2. `kasbon_list_all` (Page view) — halaman daftar seluruh kasbon.
3. `kasbon_create` (Page view / List PR) — form pembuatan kasbon dengan pemuatan daftar PR stok/departemen/aset.
4. `kasbon_save` (Write / Transaksi DB) — penyimpanan pengajuan kasbon baru atau revisi kasbon beserta attachment dan link PR.
5. `get_kasbon` (AJAX Read) — pembacaan detail kasbon dan rincian PR terkait untuk modal/form.
6. `kasbon_edit` (Page view form) — form edit pengajuan kasbon.
7. `kasbon_edit_manage` (Page view approval management) — form edit kasbon pada level approval management.
8. `kasbon_print` (Print document flow) — pencetakan dokumen kasbon (PR departemen, PR stok, PR aset, atau kasbon standar).
9. `periodik_print` (Print document flow) — pencetakan dokumen pengajuan periodik kasbon.
10. `kasbon_view` (Page view detail) — tampilan detail form kasbon readonly.

| Endpoint | HTTP / Method Type | Metadata Aman | Cabang Hasil Utama |
|---|---|---|---|
| `kasbon` | Page View (GET) | `page`, `outcome` | `rendered` (status 1) |
| `kasbon_list_all` | Page View (GET) | `page`, `filter_nama`, `outcome` | `rendered` (status 1) |
| `kasbon_create` | Page View (GET) | `page`, `item_count`, `outcome` | `rendered` (status 1), `query_error` (status 0) |
| `kasbon_save` | AJAX (POST Write) | `id`, `no_doc`, `tipe_pr`, `outcome` | `saved_new` (status 1), `updated` (status 1), `attachment_error` (status 0), `tx_failed` (status 0) |
| `get_kasbon` | AJAX (POST Read) | `id`, `tipe_pr`, `detail_count`, `outcome` | `fetched` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `kasbon_edit` | Page View (GET) | `id`, `no_doc`, `outcome` | `rendered` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `kasbon_edit_manage` | Page View (GET) | `id`, `no_doc`, `outcome` | `rendered` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `kasbon_print` | Page View / Print (GET) | `id`, `no_doc`, `tipe_pr`, `outcome` | `printed` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `periodik_print` | Page View / Print (GET) | `id`, `no_doc`, `outcome` | `printed` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `kasbon_view` | Page View (GET) | `id`, `no_doc`, `mod`, `outcome` | `rendered` (status 1), `not_found` (status 0), `query_error` (status 0) |

## Implementasi dan Keputusan Teknis

1. **Helper Audit Privat:**
   - Disediakan metode pembantu privat (`beginAudit`, `completeAudit`, `auditSafeData`, `auditObserve`) yang terintegrasi langsung dengan singleton `Module_audit_recorder`.
   - Dilengkapi guard defensif (`isset($this->load)`, `class_exists`) sehingga tetap aman jika dieksekusi di lingkungan mock test tanpa bootstrap CodeIgniter penuh.
2. **Sanitasi PII dan Payload Aman:**
   - Key PII seperti `nama`, `name`, password, token, dan data file biner dilarang masuk ke payload metadata audit.
   - Pada filter nama kasbon, key allowlist disesuaikan menjadi `filter_nama` untuk menghindari exception allowlist PII dari recorder.
   - Field `doc_file` dan `doc_file_2` tidak disertakan dalam metadata audit.
3. **Penanganan Evaluasi Transaksi DB (`trans_commit`):**
   - Evaluasi commit transaksi pada `kasbon_save` menggunakan strict check `$this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE`.
   - Hal ini memastikan kompatibilitas penuh dengan mock CodeIgniter lama (seperti pada suite `Kasbon_attachments_test.php` yang mengembalikan `void`/`null`) tanpa memicu rollback yang tidak semestinya, sekaligus tetap menangkap kegagalan commit nyata (`false`).
4. **Penanganan Error Lampiran (`attachment_error`):**
   - Kegagalan copy atau upload lampiran PR/kasbon yang melempar `RuntimeException` ditangkap dan dicatat dalam audit dengan status `0` (`attachment_error`) sebelum respon JSON dikirim ke client.
5. **Observasi Query pada Pemuatan Data dan View:**
   - Seluruh eksekusi query pada endpoint pembacaan, view, dan print dipantau dengan `$this->auditObserve(...)`. Kegagalan query dicatat dengan status `0` dan `outcome = 'query_error'`.
6. **Resiliensi Kegagalan Penulis Audit (Writer Failure Immunity):**
   - Kegagalan internal pada audit trail logger (misal database audit offline atau exception) tidak mengganggu alur bisnis maupun respon HTTP kasbon.
7. **Isolasi Batch:**
   - Sebanyak 73 endpoint publik `Expense` lainnya dibiarkan uninstrumented, mempertahankan batas maksimal 10 endpoint publik per batch.

## Hasil Pengujian dan Verifikasi

1. **Suite Batch 1 Expense (`application/modules/expense/tests/Batch1_audit_test.php`):**
   - **PASS**: 37 fixture scenarios + 1 shutdown subprocess runner.
   - Mencakup verifikasi seluruh cabang sukses dan gagal pada 10 endpoint, isolasi PII, observer query, penanganan attachment error, writer failure immunity, dan subprocess shutdown fallback.
2. **Suite Regresi Attachment Kasbon (`application/modules/expense/tests/Kasbon_attachments_test.php`):**
   - **PASS**: Seluruh pengujian normalisasi, copy, save, edit, fallback, dan rendering attachment kasbon lulus tanpa regresi.
3. **Regresi Audit Rollout Fase 1 dan Fase 2:**
   - `pengajuan_rutin` Batch 1 (58 scenarios + 1 subprocess) & Batch 2 (13 scenarios + 1 subprocess): **PASS**.
   - `request_mutasi` Batch 1 (391 scenarios + 2 subprocesses), Batch 2 (275 scenarios + 2 subprocesses), Batch 3 (10 scenarios + 1 subprocess): **PASS**.
   - `pembayaran_material` Batch 1 (246 scenarios), Batch 2 (551 scenarios): **PASS**.
   - `request_payment` Batch 1 (94 scenarios): **PASS**.
4. **Pemeriksaan Statis:**
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/controllers/Expense.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch1_audit_test.php` (0 errors).
   - `git diff --check`: PASS (0 whitespace error).

## SHA dan Metrik Controller

- Controller: `application/modules/expense/controllers/Expense.php`
- SHA-256 akhir: `5d38f96cb134e4b782d6129fea812be987edd90619d92b5032a739111c055d2a`
- Total baris: 5756
