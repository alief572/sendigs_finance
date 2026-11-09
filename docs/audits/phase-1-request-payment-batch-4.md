# Fase 1 — Request Payment batch 4

Selesai: 2026-10-11 01:25 WIB, branch `main`. Cakupan: 10 endpoint batch 4. Fase 1 kini **40/55**; Request Payment **40/46**; tersisa **15 endpoint** fase 1. Checkpoint utama: `docs/task-progress.md`.

## Grounding

`AGENTS.md`, `docs/implementation-plan.md`, `docs/task-progress.md`, controller `Request_payment.php`, model `Request_payment_model.php`, `Approval_request_payment_model.php`, dan view terkait (`excel_payment_list`, `detail_invoice_modal`) diperiksa. Hash controller sebelum perubahan `3f1e5cab12f489729f3b19a896f65a98d03c672bb08f83e6ac9c257af2217ed4` dan hash model sebelum perubahan `e64ee09fee8526c1c25107a1392b0cb1d6b990033deccb75ee4e3e7dd1261bf7` cocok checkpoint batch 3. Tidak ada recorder di constructor; permission/menu dan respons bisnis yang sudah ada tetap dipertahankan. Tidak melakukan pemeriksaan menu/DB live ulang.

## Checklist endpoint

| Endpoint | Metadata yang diizinkan | Cabang verifikasi |
| --- | --- | --- |
| `change_tab` | tab, row_count | 6 tab (`kasbon`, `transportasi`, `expense`, `nonrutin`, `periodik`, `pembayaran_po`), tab kosong/unknown, isi/kosong, query/model gagal, writer/exit |
| `excel_payment_list` | date_from, date_to, tipe, status_filter, row_count | model asli dan stub, isi/kosong, query/model/render gagal, writer/exit |
| `view_receive_invoice` | invoice_id, found | segmen URI dan POST `id_invoice`, ditemukan/kosong, query/render gagal, writer |
| `save_approval_checker_consultant` | no_dokumen, approval_level, tipe, amount | `kasbon`/`expense`, sumber ditemukan/hilang (`status => 0`), setiap query/update/insert/delete/begin/status/commit gagal, writer |
| `save_approval_consultant` | no_dokumen, approval_level, tipe, amount | `kasbon`/`expense`, sumber ditemukan/hilang (`status => 0`), setiap query/update/insert/begin/status/commit gagal, writer/exit |
| `get_data_req_payment` | company_id, date_from, date_to, kategori, search_present, records_total, records_filtered, row_count | model asli (union, company filter, date range, kategori, search, enrichment HRIS/consultant) dan stub, setiap query gagal, model exception, writer |
| `get_summary_cards` | company_id, date_from, date_to, kategori, total_pengajuan, total_nilai | model asli dan stub, isi/kosong, setiap query gagal, model exception, writer |
| `added_pilih_data` | document_id, tipe, action | pilih (`wdo=1`, insert) dan batal pilih (`wdo!=1`, delete), ID kosong (`status => 0`), query/begin/status/commit gagal, writer |
| `save_request_payment` | document_ids, document_count, processed_count | 8 kategori (`kasbon`, `transportasi`, `expense`, `nonrutin`, `periodik`, `direct_payment`, `kasbon_consultant`/`expense_consultant`, `petty_cash`/`petty_cash_hutang`/`refill_pettycash`), seleksi kosong (`empty_selection`), sumber hilang/item invalid, lebih dari 100 dokumen (dibatasi 100 ID), setiap query/update/insert_batch/delete/begin/status/commit/`copy_to_payment` gagal, writer/exit |
| `reset_choosed_req_payment` | — (reason/outcome generik) | sukses (`outcome => 'reset'`), delete/begin/status/commit gagal, writer |

Semua scope mengizinkan `reason` dan `outcome` generik melalui helper controller. Tidak menyimpan SQL mentah, POST utuh, nilai pencarian bebas (`search[value]`), nama/rekening bank, nama pemohon/supplier, atau keterangan dokumen bebas.

## Keputusan dan perubahan perilaku

- Lima endpoint read (`change_tab`, `excel_payment_list`, `view_receive_invoice`, `get_data_req_payment`, `get_summary_cards`) dimulai sebelum query/model dan selesai sesudah output/view selesai. Daftar/ringkasan/invoice kosong tetap dicatat sebagai audit sukses (`status = 1`) apabila tidak ada kegagalan query/exception.
- `Request_payment_model` dilengkapi pelacakan error query lintas koneksi (`$this->db`, `$this->consultant`, dan koneksi HRIS) serta parameter referensi opsional `&$auditSummary = null` pada `excel_payment_list`, `get_data_req_payment`, dan `get_summary_cards`. Pendekatan ini mempertahankan tanda tangan pemanggilan lama sekaligus menangkap kegagalan query per-cabang (termasuk lookup company, resolusi consultant, dan departemen HRIS) yang sebelumnya tertimpa oleh query berikutnya.
- `save_approval_checker_consultant` dan `save_approval_consultant` memvalidasi keberadaan dokumen sumber consultant (`tr_kasbon_project_header` atau `tr_expense_report_project_header`) serta memverifikasi `trans_begin()`, setiap query `update`/`insert`/`delete`, `trans_status()`, dan `trans_commit()` sebelum mencatat audit sukses. Kegagalan mengembalikan JSON `['status' => 0, 'pesan' => ...]` setelah `trans_rollback()`.
- `added_pilih_data` dan `reset_choosed_req_payment` kini memeriksa `trans_begin()`, hasil `insert`/`delete`, `trans_status()`, dan `trans_commit()` secara eksplisit (menggantikan asumsi bahwa `trans_complete()` selalu sukses) dengan rollback pada kegagalan.
- `save_request_payment` mempertahankan perilaku no-op saat `tr_added_req_payment` kosong (`outcome => 'empty_selection'`, `processed_count => 0`, tetap membersihkan `tr_added_req_payment` dan menjalankan `copy_to_payment()`), memvalidasi keberadaan dokumen sumber pada setiap cabang tipe sebelum `insert_batch`/`update`, membatasi metadata `document_ids` maksimal 100 item sambil mencatat `document_count` penuh, dan mencatat audit gagal (`reason => 'copy_to_payment_failure'`, `status => 0`) apabila `copy_to_payment()` gagal.
- Tes batas rollout pada `Batch2_test.php` dan `Batch4_test.php` kini menguji `copy_to_payment()` (endpoint batch 5 yang masih pending) agar tetap memastikan sifat opt-in per endpoint.

## Hasil verifikasi

- RED awal: `Batch4_test.php` gagal pada `change_tab: exactly one audit` sebelum instrumentasi controller.
- GREEN: **810 skenario batch 4** PASS (`Batch4_test.php`), mencakup model asli dan stub, kegagalan setiap query individual pada model asli (`excel_payment_list`, `get_data_req_payment`, `get_summary_cards`) dan seluruh cabang write (`save_approval_checker_consultant`, `save_approval_consultant`, `added_pilih_data`, `save_request_payment`, `reset_choosed_req_payment`), writer nonblocking, batas 100 ID dokumen, serta empat subprocess shutdown nyata (`change_tab`, `excel_payment_list`, `save_approval_consultant`, `save_request_payment`).
- Regresi: batch 1 **94**, batch 2 **242**, batch 3 **282**, PR Stok **24 endpoint**, serta tujuh suite fondasi Audit Trail PASS: total **12 suite**, seluruhnya exit 0.
- PHP lint 5 file PHP (`Request_payment.php`, `Request_payment_model.php`, `Batch2_test.php`, `Batch3_test.php`, `Batch4_test.php`) PASS; `git diff --check` PASS.
- Perbandingan per metode terhadap snapshot sebelum batch 4: tepat **10 metode batch 4** berubah pada `Request_payment.php`, 0 metode lain berubah/ditambah/dihapus, dan jumlah pemanggilan `$this->beginAudit(` meningkat dari 30 menjadi **40**.
- Hash controller setelah batch 4: `f1e2b140976bab1bf052808fa94d6814d339d27847e6ae73df99f3716b714ccf`.
- Hash model setelah batch 4: `ee5783f459bfd59c2cc445c54129fd85994291ccdd0d698536b183bd8dd9dbcc`.

## Batas verifikasi dan pekerjaan berikutnya

Tidak menjalankan pengujian browser, DB live, migration, integrasi MariaDB, atau full PHPUnit repo. Kelulusan fixture bukan bukti deployment produksi atau kondisi DB bisnis/konsultan/HRIS live. `copy_to_payment()` yang dipanggil pasca-commit oleh `save_request_payment` masih menjalankan transaksi model terpisah hingga batch 5 menginstrumentasi endpoint sisanya.

Berikutnya **Fase 1 batch 5** (6 endpoint terakhir `Request_payment`: `copy_to_payment`, `reject_req_payment`, `download_excel_request_payment`, `print_cash`, `print_direct_payment`, `print_kasbon`), dilanjutkan dengan `Approval_request_payment` (4 endpoint) dan `Record_request_payment` (5 endpoint). Fase 2 belum dimulai.
