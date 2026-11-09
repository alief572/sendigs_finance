# Fase 3 — Expense batch 3

Tanggal: 15 Oktober 2026. Branch main. Status: **selesai dan terverifikasi pada cakupan batch 3**.
Modul **Expense 30/83 endpoint (Batch 1, 2, & 3 selesai)**. **Fase 3 sedang berjalan: 30/121 endpoint selesai**:
- Batch 1 (Kasbon Dasar): 10/10 endpoint selesai
- Batch 2 (Approval/Delete Kasbon & Expense Views/Print): 10/10 endpoint selesai
- Batch 3 (Petty Cash Print, Approvals, Expense Save/Delete, Budget Check, Transport Mgmt): 10/10 endpoint selesai
- Batch 4–9 (Transport Requests, Periodic, Reports, AJAX Lists): 53 endpoint pending

## Grounding dan Cakupan Batch 3

Batch 3 mencakup 10 endpoint lanjutan pada controller `Expense`:
1. `expense_pettycash_print` (Print document flow) — pencetakan dokumen expense petty cash beserta file bukti lampiran.
2. `list_expense_approval` (Page view) — halaman approval expense tingkat finance.
3. `list_expense_approval_manage` (Page view) — halaman approval expense tingkat management dengan observasi query join dan group by.
4. `expense_list_all` (Page view) — halaman daftar seluruh expense report.
5. `approval` (Page view) — form persetujuan expense (mendukung mode direct, petty cash, dan expense report).
6. `approve` (AJAX POST Write) — persetujuan expense untuk finance (`sts_finance == 0`) maupun management (`sts_finance == 1`), termasuk update batch detail, auto post jurnal expense report, dan bridge simpan_aktifitas.
7. `save` (AJAX POST Write) — penyimpanan data expense (create mode & update mode) dengan validasi wajib bon bukti pettycash, validasi bukti transfer balik pengembalian lebih kasbon, penanganan upload file, dan evaluasi transaksi commit/rollback.
8. `delete` (AJAX POST Delete) — penghapusan expense dengan pelepasan relasi kasbon/pengembalian dan pembersihan detail.
9. `cekbudget` (AJAX Read) — pengecekan sisa anggaran COA tahun berjalan secara dinamis (summary maupun detail).
10. `transport_req_mgt` (Page view) — halaman persetujuan manajemen untuk permohonan penggantian transport.

| Endpoint | HTTP / Method Type | Metadata Aman | Cabang Hasil Utama |
|---|---|---|---|
| `expense_pettycash_print` | Print / View (GET) | `id`, `no_doc`, `outcome` | `printed` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `list_expense_approval` | Page View (GET) | `page`, `outcome` | `rendered` (status 1) |
| `list_expense_approval_manage` | Page View (GET) | `page`, `item_count`, `outcome` | `rendered` (status 1), `query_error` (status 0) |
| `expense_list_all` | Page View (GET) | `page`, `all`, `outcome` | `rendered` (status 1) |
| `approval` | Page View (GET) | `id`, `no_doc`, `form_type`, `outcome` | `rendered` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `approve` | AJAX (POST Write) | `id`, `stage`, `status`, `outcome` | `approved` (status 1), `empty_id` (status 0), `not_found` (status 0), `update_failed` (status 0) |
| `save` | AJAX (POST Write) | `id`, `no_doc`, `departement`, `amount`, `outcome`, `reason` | `created`/`updated` (status 1), `validation_failed` (status 0), `upload_failed` (status 0), `transaction_failed` (status 0) |
| `delete` | AJAX (POST Delete) | `id`, `no_doc`, `outcome` | `deleted` (status 1), `not_found` (status 0), `delete_failed` (status 0) |
| `cekbudget` | AJAX (POST Read) | `coa`, `tahun`, `sisa`, `outcome` | `found` (status 1), `not_found` (status 1), `query_error` (status 0) |
| `transport_req_mgt` | Page View (GET) | `page`, `item_count`, `outcome` | `rendered` (status 1), `query_error` (status 0) |

## Implementasi dan Keputusan Teknis

1. **Evaluasi Validasi Server-Side dan Transaksi pada `save()`:**
   - Validasi petty cash: jika field petty cash terisi namun berkas bon bukti tidak disertakan pada baris baru, proses dibatalkan sebelum transaksi database dengan audit event berstatus `0` (`validation_failed`, reason `bon_bukti_required`).
   - Validasi lebih kasbon: jika total kasbon melebihi expense dan bukti transfer pengembalian belum ada, proses dihentikan dengan audit event berstatus `0` (`validation_failed`, reason `bukti_pengembalian_required`).
   - Transaksi database (`trans_begin()`) diupdate dengan pengecekan strict `$this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE` pada kedua cabang (update maupun insert) untuk memastikan integritas commit.
   - Jika upload file gagal di tengah proses transaksi, dilakukan rollback dan dicatat audit `upload_failed`.
2. **Penanganan Multi-Stage Approval (`approve()`):**
   - Mendukung dua tahapan persetujuan:
     - Tahap Finance (`sts_finance == '0'`): melakukan update flag status finance dan tanggal approval finance.
     - Tahap Management: melakukan update batch status dokumen utama dan detail item expense, serta memanggil `_post_jurnal_expense_report` jika dokumen merupakan pelaporan kasbon.
   - Bridge legacy `simpan_aktifitas` diintegrasikan melalui mekanisme `defer()` recorder.
3. **Pembersihan Relasi Kasbon dan Pengembalian pada `delete()`:**
   - Observasi query dilakukan secara defensif; relasi `kasbon_pr_non_po_pett` pada `tr_kasbon` dan `id_expense_bayar_sisa` pada `tr_expense` serta `tr_pengembalian_expense` dibersihkan sebelum header dan detail expense dihapus via `All_model->dataDelete()`.
4. **Audit Aman pada `cekbudget()`:**
   - `Expense_model->GetBudget()` mengembalikan `false` apabila record anggaran belum ada di tabel `ms_budget`. Hasil ini diperlakukan sebagai cabang normal (`outcome => 'not_found'` dengan `status => 1`), sedangkan error query database yang sebenarnya diamati melalui observer status koneksi.
5. **Sanitasi Metadata dan Proteksi PII:**
   - Seluruh payload kredensial (`password`, `credit_card`) dan data file biner (`doc_file`) disaring keluar dari metadata audit context.
   - Field `nama` tidak dimasukkan ke dalam allowlist recorder untuk mencegah kebocoran PII nama karyawan.
6. **Resiliensi Kegagalan Penulis Audit (Writer Failure Immunity):**
   - Kegagalan internal pada logger audit (baik kegagalan write maupun exception tak terduga) diisolasi sehingga tidak menggagalkan eksekusi bisnis maupun merusak respon JSON/view.
7. **Preservasi Batch 4–9:**
   - Sisa 53 endpoint publik pada controller `Expense` dibiarkan murni tidak terinstrumentasi (*uninstrumented*) untuk menjaga batasan batch maksimal 10 endpoint publik.

## Hasil Pengujian dan Verifikasi

1. **Suite Batch 3 Expense (`application/modules/expense/tests/Batch3_audit_test.php`):**
   - **PASS**: 59 fixture scenarios + 1 shutdown subprocess runner.
   - Menguji semua cabang sukses dan gagal pada 10 endpoint, validasi bon bukti & transfer balik, multi-stage approval finance & management, observasi query, penanganan missing record, writer failure immunity, dan isolasi method pending Batch 4.
2. **Suite Batch 2 Expense (`application/modules/expense/tests/Batch2_audit_test.php`):**
   - **PASS**: 54 fixture scenarios + 1 shutdown subprocess runner.
3. **Suite Batch 1 Expense (`application/modules/expense/tests/Batch1_audit_test.php`):**
   - **PASS**: 36 fixture scenarios + 1 shutdown subprocess runner.
4. **Regresi Audit Rollout Seluruh Modul Fase 1 dan Fase 2:**
   - `pengajuan_rutin` Batch 1 (58 scenarios + 1 subprocess) & Batch 2 (13 scenarios + 1 subprocess): **PASS**.
   - `request_mutasi` Batch 1 (391 scenarios + 2 subprocesses), Batch 2 (275 scenarios + 2 subprocesses), Batch 3 (10 scenarios + 1 subprocess): **PASS**.
   - `pembayaran_material` Batch 1 (246 scenarios), Batch 2 (551 scenarios): **PASS**.
   - `request_pr_stok` (24 endpoints regression suite): **PASS**.
5. **Pemeriksaan Statis:**
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/controllers/Expense.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch3_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch2_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch1_audit_test.php` (0 errors).
   - `git diff --check`: PASS (0 whitespace error).

## SHA dan Metrik Controller

- Controller: `application/modules/expense/controllers/Expense.php`
- SHA-256 akhir: `FCFB9C94BC2E077700CD05E8E5E34338F9B3E70CE494AC18EF8ACE85403BCADA`
- Total baris: 6088
