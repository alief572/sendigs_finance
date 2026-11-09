# Fase 1 — Penyelesaian Rollout (Batch 5 `Request_payment`, `Approval_request_payment`, dan `Record_request_payment`)

Selesai: 2026-10-11 02:27 WIB, branch `main`. Cakupan: **15 endpoint terakhir Fase 1** — terdiri dari 6 endpoint batch 5 `Request_payment`, 4 endpoint `Approval_request_payment`, dan 5 endpoint `Record_request_payment`. Fase 1 kini **55/55 endpoint selesai (100%)**. Checkpoint utama: `docs/task-progress.md`.

## Grounding

`AGENTS.md`, `docs/implementation-plan.md`, `docs/task-progress.md`, controller `Request_payment.php`, `Approval_request_payment.php`, `Record_request_payment.php`, serta model `Request_payment_model.php`, `Approval_request_payment_model.php`, dan `Record_request_payment_model.php` diperiksa sebelum perubahan.

Hash SHA-256 sebelum dan sesudah implementasi:

| File | SHA-256 sebelum | SHA-256 sesudah | Jumlah `$this->beginAudit(` |
| --- | --- | --- | ---: |
| `application/modules/request_payment/controllers/Request_payment.php` | `f1e2b140976bab1bf052808fa94d6814d339d27847e6ae73df99f3716b714ccf` | `956b4a03c006af1d2076f395da9e118ad3112770c3b0f8c24e2b4df72fed5409` | 46 |
| `application/modules/request_payment/models/Request_payment_model.php` | `ee5783f459bfd59c2cc445c54129fd85994291ccdd0d698536b183bd8dd9dbcc` | `ae5aa79469322d92fd5240b8b3555ad8a349c68069587271f971bc642ffcb928` | 0 |
| `application/modules/approval_request_payment/controllers/Approval_request_payment.php` | `cff2cda810deaf0138467dc04f7f33b058d4c70c66c0daf16a92cb358b4be23d` | `20512fa49e8037846b3cb7525804074452c83d0736066af36f09f9d8c57a849f` | 4 |
| `application/modules/approval_request_payment/models/Approval_request_payment_model.php` | `84df25e424497fc733a3346caee25ee6019f6ff16ce1a6f7f5cb66418b10f642` | `881d97deff4be5578121428e701f953aea9853aed3956d41e17068ac44090593` | 0 |
| `application/modules/record_request_payment/controllers/Record_request_payment.php` | `7afde857c9ef6c15710a4c021e05f8416ffb70ad318fb0d0baf3a4e71658b986` | `3e7d9e5ae5e6cdd4cba142710f1433b346863b766e8889a043643b51094922c8` | 5 |
| `application/modules/record_request_payment/models/Record_request_payment_model.php` | `c87d1a5e186bc1a86d7a0a871b27a8cf23b7a3869fb1d5879663d0ff73923eb0` | `9828b4888f25c5ab3e6b86cc5f3635f4340bd1a35edae2fa196374eff84302b5` | 0 |

Tidak ada inisialisasi recorder di constructor pada ketiga controller; sifat opt-in per endpoint, permission check, dan format respons bisnis dipertahankan.

## Checklist 15 endpoint

### 1. `Request_payment` — Batch 5 (6 endpoint)

| Endpoint | Tipe | Metadata allowlist | Cabang verifikasi |
| --- | --- | --- | --- |
| `copy_to_payment` | Write | `document_ids`, `document_count`, `payment_ids`, `processed_count` | 9 cabang tipe (`kasbon`, `kasbon_consultant`, `transportasi`, `expense`, `expense_consultant`, `nonrutin`, `periodik`, `petty_cash`/`petty_cash_hutang`/`refill_pettycash`, `direct_payment`), seleksi kosong (`empty_selection`), sumber hilang (`missing_source`), lebih dari 100 dokumen (dibatasi 100 ID), setiap query/insert_batch/update_batch/begin/status/commit gagal, writer |
| `reject_req_payment` | Write | `no_dokumen`, `tipe`, `document_ids`, `document_count` | 9 cabang tipe (`transportasi`, `kasbon`, `kasbon_consultant`, `expense`, `expense_consultant`, `periodik`, `nonrutin`, `direct_payment`, `petty_cash`/`petty_cash_hutang`), input kosong/tipe tidak didukung, sumber hilang, lebih dari 100 detail ID, setiap query/update/delete/begin/status/commit gagal, redaksi `reject_reason`, writer/exit |
| `download_excel_request_payment` | Read / Export | `tgl_from`, `tgl_to`, `tipe`, `row_count`, `format` | model asli dan stub, seluruh cabang lookup tanggal/status (`transportasi`, `kasbon`, `expense`, `periodik`, `nonrutin`, `direct_payment`, `pembayaran_po`), setiap query pada `db`/`consultant`/`hris` gagal, exception PHPExcel, CLI normal exit dan shutdown abort, writer |
| `print_cash` | Read / Print | `document_id`, `jenis_pr`, `delegated_to` | ID kosong (`missing_document`), delegasi ke `print_direct_payment` saat baris ada di `tr_direct_payment`, `pr department` vs `pr asset`, tidak ditemukan (`not_found` + `show_404`), setiap query pada `db`/`hris` gagal, render exception/exit, writer |
| `print_direct_payment` | Read / Print | `document_id`, `jenis_pr`, `tipe`, `delegated_to` | ID kosong (`missing_document`), delegasi ke `print_cash` saat baris ada di `tr_pr_non_po`, ditemukan/tidak ditemukan (`not_found` + `show_404`), lookup SPK/penawaran (`_get_spk_penawaran_data`) dan departemen HRIS, setiap query pada `db`/`consultant`/`hris` gagal, render exception/exit, writer |
| `print_kasbon` | Read / Print | `document_id`, `tipe` | ID kosong (`missing_document`), decode `|` menjadi `/`, ditemukan/tidak ditemukan (`not_found` + `show_404`), lookup SPK/penawaran (`_get_spk_penawaran_data`), setiap query pada `db`/`consultant` gagal, render exception/exit, writer |

### 2. `Approval_request_payment` (4 endpoint)

| Endpoint | Tipe | Metadata allowlist | Cabang verifikasi |
| --- | --- | --- | --- |
| `index` | Read | — (`reason`, `outcome`) | sukses (`outcome => 'rendered'`), render exception/exit, writer |
| `get_data_pending` | Read | `draw`, `start`, `length`, `search_present`, `row_count`, `records_total`, `records_filtered` | daftar terisi dan kosong, pencarian aktif/kosong (tanpa menyalin `search[value]`), setiap query (`recordsTotal`, `recordsFiltered`, `rows`, `SUM(dibayarkan)`) gagal, writer |
| `detail` | Read | `id_pengajuan`, `no_pengajuan`, `item_count` | ditemukan (`outcome => 'rendered'`), header hilang (`not_found`), status bukan `pending` (`already_decided`), resolusi `request_by` Direct Payment vs Cash pada `db`/`consultant` (tanpa membocorkan nama pembuat/keperluan), `build_print_url`, setiap query gagal, render exception/exit, writer |
| `save_decision` | Write | `id_pengajuan`, `no_pengajuan`, `document_ids`, `document_count`, `approved_count`, `rejected_count`, `payment_ids` | permission ditolak (`permission_denied`), header hilang (`not_found`), sudah diputuskan (`already_decided`), keputusan kosong (`invalid_decisions`), batch item kosong (`empty_batch`), alasan reject kosong (`missing_reject_reason`), kombinasi approve + reject (`outcome => 'saved'`), idempoten saat `payment_approve` sudah ada, batas 100 ID dokumen, redaksi `alasan`/`request_by`/`keperluan`, setiap query/update/insert/insert_batch/begin/status/commit gagal, writer/exit |

### 3. `Record_request_payment` (5 endpoint)

| Endpoint | Tipe | Metadata allowlist | Cabang verifikasi |
| --- | --- | --- | --- |
| `index` | Read | — (`reason`, `outcome`) | sukses (`outcome => 'rendered'`), permission ditolak (`--denied`), render exception, writer |
| `get_data_record` | Read | `draw`, `start`, `length`, `search_present`, `row_count`, `records_total`, `records_filtered` | daftar terisi dan kosong, pencarian aktif/kosong (redaksi `search[value]`), setiap query (`recordsTotal`, `recordsFiltered`, `rows`, `SUM(dibayarkan)` per baris) gagal, writer |
| `get_data_record_legacy` | Read | `draw`, `start`, `length`, `search_present`, `row_count`, `records_total`, `records_filtered` | daftar terisi dan kosong, pencarian aktif/kosong (redaksi `search[value]`, `nama`, `keperluan`), setiap query (`recordsTotal`, `recordsFiltered`, `rows`, `build_print_url` per baris) gagal, writer |
| `detail` | Read | `id_pengajuan`, `no_pengajuan`, `row_count` | ditemukan (`outcome => 'rendered'`), header hilang (`not_found` + redirect), resolusi rekening bank (`Kasbon`, `Transport`, `Expense`, `Periodik`, `Cash`, `Direct Payment`, fallback `request_payment`/`payment_approve`) dan `resolve_request_by` (redaksi seluruh nama/rekening/keperluan), setiap query pada `db`/`consultant` gagal, render exception/exit, writer |
| `export_excel` | Read / Export | `id_pengajuan`, `no_pengajuan`, `format`, `row_count` | sukses (`outcome => 'exported'`, `format => 'xlsx'`), header hilang atau record kosong (`not_found` + `show_error` 404), query header/record/enrichment gagal (`query_failure`), exception PHPExcel (`export_exception`), subprocess abort (`--export-abort`) dan subprocess normal exit (`--export-ok` tanpa duplikasi event saat `exit`), writer |

## Keputusan teknis penting

- **Delegasi timbal-balik `print_cash` ↔ `print_direct_payment`:** Saat `print_cash` mendeteksi nomor dokumen ada di `tr_direct_payment` (atau sebaliknya `print_direct_payment` mendeteksi nomor dokumen ada di `tr_pr_non_po`), pemanggilan didelegasikan ke metode pasangan. Karena `Module_audit_recorder::begin()` hanya menerima satu scope aktif per request (`state === 'idle'`), kedua metode mendaftarkan union allowlist key (`document_id`, `jenis_pr`, `tipe`, `delegated_to`) sehingga metode yang didelegasikan menyelesaikan scope pemanggil awal dengan satu event akurat tanpa duplikasi.
- **Pelacakan error DB pada test double berbasis `__call`:** Pada PHP 7.4, `method_exists($conn, 'error')` mengembalikan `false` untuk objek yang mengimplementasikan `error()` melalui magic method `__call()`. Oleh sebab itu `auditDbStatus` dan `trackAuditDbError` memeriksa `is_object($conn)` sebelum memanggil `$conn->error()`, memastikan deteksi error bekerja baik pada driver CodeIgniter asli maupun test fixture.
- **Validasi sumber dan transaksi eksplisit pada `copy_to_payment` dan `reject_req_payment`:** `Request_payment_model::copy_to_payment(&$auditSummary = null)` kini memvalidasi keberadaan dokumen sumber di setiap cabang tipe sebelum membuka transaksi, melacak error query `db`/`consultant`, serta memeriksa `trans_begin()`, setiap `insert_batch`/`update_batch`, `trans_status()`, dan `trans_commit()`. `reject_req_payment` memvalidasi input, keberadaan dokumen sumber, dan status transaksi/commit pada `db` maupun `consultant` sebelum menyelesaikan audit.
- **Redaksi data sensitif dan teks bebas:** Alasan penolakan (`reject_reason`, `alasan`), kata kunci pencarian (`search[value]`), nama pemohon/pembuat (`request_by`, `nama`, `nm_lengkap`), keperluan, serta rincian rekening bank (`bank_nama`, `bank_name`, `bank_no_rek`, `accnumber`, `accname`, `bank_account`, `bank_number`) tidak pernah dimasukkan ke allowlist metadata audit.

## Hasil verifikasi

- **RED → GREEN terverifikasi** untuk ketiga batch (`Batch5_test.php`, `approval_request_payment/tests/Audit_test.php`, dan `record_request_payment/tests/Audit_test.php`).
- **Seluruh 15 suite lulus (exit code 0):**
  - 7 suite fondasi Audit Trail (`Logger_test`, `Recorder_test`, `Legacy_test`, `Auth_test`, `Controller_test`, `Query_test`, `Menu_test`) — PASS
  - `request_payment/tests/Audit_test.php` (Batch 1) — PASS (**94** skenario)
  - `request_payment/tests/Batch2_test.php` (Batch 2) — PASS (**242** skenario)
  - `request_payment/tests/Batch3_test.php` (Batch 3) — PASS (**282** skenario)
  - `request_payment/tests/Batch4_test.php` (Batch 4) — PASS (**810** skenario)
  - `request_payment/tests/Batch5_test.php` (Batch 5) — PASS (**371** skenario)
  - `approval_request_payment/tests/Audit_test.php` — PASS (**60** skenario)
  - `record_request_payment/tests/Audit_test.php` — PASS (**57** skenario)
  - `request_pr_stok/tests/Audit_test.php` — PASS (**24** endpoint)
- **Lint & Diff:** `php -l` pada 13 file PHP yang diubah/dibuat lulus tanpa error; `git -c core.fsmonitor=false diff --check` lulus.
- **Pemeriksaan drift metode `Request_payment.php`:** dibandingkan terhadap snapshot pra-batch 5 (`pre_batch5_methods.json`), tepat **8 metode** berubah (`copy_to_payment`, `reject_req_payment`, `download_excel_request_payment`, `print_cash`, `print_direct_payment`, `print_kasbon`, `_get_spk_penawaran_data`, dan helper baru `auditDbStatus`), sementara **52 metode lainnya tidak berubah**.

## Batas verifikasi dan langkah berikutnya

Tidak melakukan pengujian browser, penulisan DB bisnis live, migration produksi, atau eksekusi PHPUnit penuh. Seluruh **55/55 endpoint Fase 1** telah selesai dan terverifikasi. Pekerjaan berikutnya adalah **Fase 2** (`pembayaran_material`, `request_mutasi`, `pengajuan_rutin` — 51 endpoint baseline), yang menunggu instruksi pengguna sebelum dieksekusi.
