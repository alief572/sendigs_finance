# Fase 3 — Expense batch 5

Tanggal: 15 Oktober 2026. Branch main. Status: **selesai dan terverifikasi pada cakupan batch 5**.
Modul **Expense 50/83 endpoint (Batch 1, 2, 3, 4, & 5 selesai)**. **Fase 3 sedang berjalan: 50/121 endpoint selesai**:
- Batch 1 (Kasbon Dasar): 10/10 endpoint selesai
- Batch 2 (Approval/Delete Kasbon & Expense Views/Print): 10/10 endpoint selesai
- Batch 3 (Petty Cash Print, Approvals, Expense Save/Delete, Budget Check, Transport Mgmt): 10/10 endpoint selesai
- Batch 4 (Transport Request Finance, All, List, Create, Save, Edit, Print, View, Transport List, Create): 10/10 endpoint selesai
- Batch 5 (Transport Save, List Req Transport, Get Transport, Transport Edit, Transport View, Transport Req Approve, Transport Approve, Transport Delete, Transport Req Delete, Petty Cash): 10/10 endpoint selesai
- Batch 6–9 (Petty Cash Create/Edit/View, Reject, Approvals, Details, Reports, AJAX Lists): 33 endpoint pending

## Grounding dan Cakupan Batch 5

Batch 5 mencakup 10 endpoint lanjutan terkait penyimpanan transportasi, resolusi AJAX data transport, persetujuan dan penghapusan transport, serta indeks petty cash pada controller `Expense`:
1. `transport_save` (AJAX POST Write) — penyimpanan data transportasi (create mode `$id == ""` maupun update mode `$id != ""`), penanganan upload berkas struk transport, dan evaluasi transaksi strict check `$this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE`.
2. `get_list_req_transport` (AJAX Read) — pengambilan daftar data transportasi yang siap diajukan untuk form pengajuan transport.
3. `get_transport` (AJAX Read) — pengambilan data transportasi aktif berdasarkan nama dan departemen.
4. `transport_edit` (Page view) — form edit data transportasi dengan pemuatan data riwayat dan daftar COA transport.
5. `transport_view` (Page view) — tampilan detail transportasi dalam mode read-only view.
6. `transport_req_approve` (AJAX POST/GET Write) — persetujuan pengajuan transport untuk finance (`status = 1`) maupun management (`status = 2`), dengan pencatatan timestamp dan integrasi bridge `simpan_aktifitas`.
7. `transport_approve` (AJAX POST/GET Write) — persetujuan data transportasi dengan integrasi bridge `simpan_aktifitas`.
8. `transport_delete` (AJAX POST Delete) — penghapusan data transportasi dalam transaksi database dengan strict commit check.
9. `transport_req_delete` (AJAX POST Delete) — penghapusan pengajuan transport beserta pelepasan relasi transportasi terkait kembali ke status `0` dalam transaksi database.
10. `petty_cash` (Page view) — halaman daftar petty cash.

| Endpoint | HTTP / Method Type | Metadata Aman | Cabang Hasil Utama |
|---|---|---|---|
| `transport_save` | AJAX (POST Write) | `id`, `no_doc`, `departement`, `amount`, `outcome` | `created`/`updated` (status 1), `upload_failed` (status 0), `transaction_failed` (status 0) |
| `get_list_req_transport` | AJAX (POST/GET Read) | `departement`, `date1`, `date2`, `item_count`, `outcome` | `fetched` (status 1), `query_error` (status 0) |
| `get_transport` | AJAX (POST/GET Read) | `departement`, `item_count`, `outcome` | `fetched` (status 1), `query_error` (status 0) |
| `transport_edit` | Page View (GET) | `id`, `no_doc`, `outcome` | `rendered` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `transport_view` | Page View (GET) | `id`, `no_doc`, `outcome` | `rendered` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `transport_req_approve` | AJAX (POST/GET Write) | `id`, `status`, `stage`, `outcome` | `approved` (status 1), `empty_id` (status 0), `update_failed` (status 0) |
| `transport_approve` | AJAX (POST/GET Write) | `id`, `status`, `outcome` | `approved` (status 1), `empty_id` (status 0), `update_failed` (status 0) |
| `transport_delete` | AJAX (POST Delete) | `id`, `outcome` | `deleted` (status 1), `delete_failed` (status 0) |
| `transport_req_delete` | AJAX (POST Delete) | `id`, `no_doc`, `outcome` | `deleted` (status 1), `not_found` (status 0), `delete_failed` (status 0) |
| `petty_cash` | Page View (GET) | `page`, `outcome` | `rendered` (status 1) |

## Implementasi dan Keputusan Teknis

1. **Integritas Transaksi dan Penanganan Upload pada `transport_save()`:**
   - Evaluasi upload berkas dilakukan sebelum commit database; kegagalan upload memicu rollback transaksi dan audit mencatat status `0` dengan outcome `upload_failed`.
   - Transaksi database dievaluasi secara strict dengan `$this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE` pada mode create maupun update.
2. **Pembersihan dan Pengembalian Data Bersih pada AJAX Read (`get_list_req_transport` & `get_transport`):**
   - Menghilangkan pemanggilan `die()` agar eksekusi controller dapat kembali secara teratur setelah menyelesaikan pencatatan audit trail.
   - Observasi query dilakukan dengan `$this->auditObserve($query !== false)` untuk menangkap kegagalan kueri database tanpa terpengaruh oleh jumlah baris kosong normal.
3. **Penyelarasan Legacy `simpan_aktifitas` pada Persetujuan Transport:**
   - Pemanggilan fungsi legacy `simpan_aktifitas()` pada `transport_req_approve` dan `transport_approve` tetap dipertahankan dan ditangani secara mulus oleh mekanisme `defer()` recorder tanpa menimbulkan duplikasi log audit.
4. **Pelepasan Relasi Dokumen pada `transport_req_delete()`:**
   - Sebelum header `tr_transport_req` dihapus, seluruh data transportasi anak pada tabel `tr_transport` yang terkait nomor dokumen dilepaskan relasinya (`no_req = ''` dan `status = 0`) di dalam transaksi database.
   - Jika record header tidak ditemukan (`GetDataTransportReq` menghasilkan `null`), transaksi di-rollback dan audit mencatat status `0` dengan outcome `not_found`.
5. **Sanitasi Metadata dan Proteksi PII:**
   - Seluruh payload kredensial (`password`, `credit_card`) dan data file biner (`doc_file`) disaring keluar dari metadata audit context.
   - Field `nama` tidak dimasukkan ke dalam allowlist recorder untuk mencegah kebocoran informasi identitas karyawan.
6. **Kekebalan terhadap Kegagalan Penulis Audit (Writer Failure Immunity):**
   - Kegagalan internal pada logger audit (baik kegagalan write maupun exception tak terduga) diisolasi sehingga tidak menggagalkan eksekusi bisnis maupun merusak respon JSON/view.
7. **Preservasi Batch 6–9:**
   - Sebanyak 33 endpoint publik sisa pada controller `Expense` dibiarkan murni tidak terinstrumentasi (*uninstrumented*) untuk menjaga batasan batch maksimal 10 endpoint publik.

## Hasil Pengujian dan Verifikasi

1. **Suite Batch 5 Expense (`application/modules/expense/tests/Batch5_audit_test.php`):**
   - **PASS**: 58 fixture scenarios + 1 shutdown subprocess runner.
   - Menguji semua cabang sukses dan gagal pada 10 endpoint, upload file dan upload failure, approval finance & management, pelepasan relasi delete, observasi query, penanganan missing record, writer failure immunity, dan isolasi method pending Batch 6.
2. **Suite Batch 4 Expense (`application/modules/expense/tests/Batch4_audit_test.php`):**
   - **PASS**: 52 fixture scenarios + 1 shutdown subprocess runner.
3. **Suite Batch 3 Expense (`application/modules/expense/tests/Batch3_audit_test.php`):**
   - **PASS**: 60 fixture scenarios + 1 shutdown subprocess runner.
4. **Suite Batch 2 Expense (`application/modules/expense/tests/Batch2_audit_test.php`):**
   - **PASS**: 55 fixture scenarios + 1 shutdown subprocess runner.
5. **Suite Batch 1 Expense (`application/modules/expense/tests/Batch1_audit_test.php`):**
   - **PASS**: 37 fixture scenarios + 1 shutdown subprocess runner.
6. **Regresi Audit Rollout Seluruh Modul Fase 1 dan Fase 2:**
   - `pengajuan_rutin` Batch 1 (58 scenarios + 1 subprocess) & Batch 2 (13 scenarios + 1 subprocess): **PASS**.
   - `request_mutasi` Batch 1 (391 scenarios + 2 subprocesses), Batch 2 (275 scenarios + 2 subprocesses), Batch 3 (10 scenarios + 1 subprocess): **PASS**.
   - `pembayaran_material` Batch 1 (246 scenarios), Batch 2 (551 scenarios): **PASS**.
   - `request_pr_stok` (24 endpoints regression suite): **PASS**.
   - `Kasbon_attachments_test.php` (kasbon attachments regression suite): **PASS**.
7. **Pemeriksaan Statis:**
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/controllers/Expense.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch5_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch4_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch3_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch2_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch1_audit_test.php` (0 errors).
   - `git diff --check`: PASS (0 whitespace error).

## SHA dan Metrik Controller

- Controller: `application/modules/expense/controllers/Expense.php`
- SHA-256 akhir: `3C9AB009FC5AFA1E26354B955AFE73285F6CD595400EB7CC38B9A3DA05C9EC9A`
- Total baris: 6323
