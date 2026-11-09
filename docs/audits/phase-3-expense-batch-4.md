# Fase 3 — Expense batch 4

Tanggal: 15 Oktober 2026. Branch main. Status: **selesai dan terverifikasi pada cakupan batch 4**.
Modul **Expense 40/83 endpoint (Batch 1, 2, 3, & 4 selesai)**. **Fase 3 sedang berjalan: 40/121 endpoint selesai**:
- Batch 1 (Kasbon Dasar): 10/10 endpoint selesai
- Batch 2 (Approval/Delete Kasbon & Expense Views/Print): 10/10 endpoint selesai
- Batch 3 (Petty Cash Print, Approvals, Expense Save/Delete, Budget Check, Transport Mgmt): 10/10 endpoint selesai
- Batch 4 (Transport Request Finance, All, List, Create, Save, Edit, Print, View, Transport List, Create): 10/10 endpoint selesai
- Batch 5–9 (Transport Save, Detail Views, Approvals, Delete, Petty Cash, Reports, AJAX Lists): 43 endpoint pending

## Grounding dan Cakupan Batch 4

Batch 4 mencakup 10 endpoint lanjutan terkait pengelolaan dan pengajuan transportasi pada controller `Expense`:
1. `transport_req_fin` (Page view) — halaman pengecekan finance penggantian transport.
2. `transport_req_all` (Page view) — halaman daftar seluruh pengajuan transport (header dan rincian permohonan).
3. `transport_req` (Page view) — halaman daftar pengajuan penggantian transport milik user.
4. `transport_req_create` (Page view) — form pembuatan pengajuan penggantian transport baru dengan daftar COA transport.
5. `transport_req_save` (AJAX POST Write) — penyimpanan data pengajuan transport (create mode `$id == ""` maupun update mode `$id != ""`), pembersihan pemisah ribuan pada array biaya (bensin, tol, parkir, lainnya, km), upload berkas struk bukti transport, sinkronisasi detail transportasi terkait, dan evaluasi transaksi strict check `$this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE`.
6. `transport_req_edit` (Page view) — form edit pengajuan transport dengan pemuatan data header, detail item transportasi terkait, dan COA transport.
7. `transport_req_print` (Print document flow) — pencetakan dokumen pengajuan transport dengan resolusi nama departemen dari HRIS, resolusi nama pemohon dari tabel users, dan format COA detail.
8. `transport_req_view` (Page view) — tampilan detail pengajuan transport dalam mode read-only view.
9. `transport` (Page view) — halaman daftar master/riwayat transportasi.
10. `transport_create` (Page view) — form pembuatan transportasi baru dengan pemuatan daftar COA transport.

| Endpoint | HTTP / Method Type | Metadata Aman | Cabang Hasil Utama |
|---|---|---|---|
| `transport_req_fin` | Page View (GET) | `page`, `outcome` | `rendered` (status 1) |
| `transport_req_all` | Page View (GET) | `page`, `item_count`, `outcome` | `rendered` (status 1), `query_error` (status 0) |
| `transport_req` | Page View (GET) | `page`, `outcome` | `rendered` (status 1) |
| `transport_req_create` | Page View (GET) | `form`, `item_count`, `outcome` | `rendered` (status 1), `query_error` (status 0) |
| `transport_req_save` | AJAX (POST Write) | `id`, `no_doc`, `departement`, `amount`, `outcome` | `created`/`updated` (status 1), `transaction_failed` (status 0) |
| `transport_req_edit` | Page View (GET) | `id`, `no_doc`, `mod`, `outcome` | `rendered` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `transport_req_print` | Print / View (GET) | `id`, `no_doc`, `outcome` | `printed` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `transport_req_view` | Page View (GET) | `id`, `no_doc`, `mod`, `outcome` | `rendered` (status 1), `not_found` (status 0), `query_error` (status 0) |
| `transport` | Page View (GET) | `page`, `outcome` | `rendered` (status 1) |
| `transport_create` | Page View (GET) | `form`, `item_count`, `outcome` | `rendered` (status 1), `query_error` (status 0) |

## Implementasi dan Keputusan Teknis

1. **Observasi Query dengan Penanganan Data Kosong Normal (`auditObserve(null)`):**
   - Method `GetListDataTransportRequest()`, `GetListDataTransportRequestAll()`, dan `getCoaTransport()` mengembalikan array kosong atau `false` ketika tabel belum memiliki record.
   - Menggunakan `$this->auditObserve(null)` agar status kueri diamati secara akurat berdasarkan error koneksi/eksekusi database riil (`$db->error()`), bukan sekadar mengecek nilai balik data kosong.
2. **Pembersihan Numerik dan Integritas Transaksi pada `transport_req_save()`:**
   - Input biaya dalam bentuk array (`bensin`, `tol`, `parkir`, `lainnya`, `km_awal`, `km_akhir`) serta total `jumlah_expense` dibersihkan dari pemisah ribuan (`.` dan `,`) sebelum diproses ke database.
   - Pengecekan transaksi strict `$this->db->trans_status() === FALSE || $this->db->trans_commit() === FALSE` diterapkan pada mode update maupun insert untuk memastikan commit berhasil sebelum mengembalikan status sukses (`1`).
   - Jika transaksi gagal, dilakukan rollback eksplisit dan audit mencatat status `0` dengan outcome `transaction_failed`.
3. **Pencetakan Dokumen (`transport_req_print()`):**
   - Resolusi relasi departemen ke HRIS melalui `GetDepartmentName()` dan nama user ke tabel `users` dilakukan dengan audit observer aman.
   - Jika dokumen tidak ditemukan (`GetDataTransportReq` mengembalikan `null`), dicatat audit status `0` dengan outcome `not_found`.
4. **Sanitasi Metadata dan Proteksi PII:**
   - Seluruh payload kredensial (`password`, `credit_card`), data rekening (`accnumber`), dan berkas biner (`doc_file`) disaring keluar dari metadata audit context.
   - Key `nama` tidak dimasukkan ke dalam allowlist recorder untuk mencegah kebocoran informasi identitas karyawan ke tabel audit trail.
5. **Kekebalan terhadap Kegagalan Penulis Audit (Writer Failure Immunity):**
   - Kegagalan internal pada logger audit (baik kegagalan penyimpanan maupun runtime exception) diisolasi sehingga alur bisnis, view template, dan response JSON tidak terganggu.
6. **Preservasi Batch 5–9:**
   - Sebanyak 43 endpoint publik sisa pada controller `Expense` dibiarkan murni tidak terinstrumentasi (*uninstrumented*) untuk mematuhi batasan ketat maksimal 10 endpoint publik per batch.

## Hasil Pengujian dan Verifikasi

1. **Suite Batch 4 Expense (`application/modules/expense/tests/Batch4_audit_test.php`):**
   - **PASS**: 51 fixture scenarios + 1 shutdown subprocess runner.
   - Menguji semua cabang normal, missing record, query failure, transaksi commit/rollback failure, upload berkas, proteksi PII, writer failure immunity, dan isolasi endpoint pending Batch 5.
2. **Suite Batch 3 Expense (`application/modules/expense/tests/Batch3_audit_test.php`):**
   - **PASS**: 59 fixture scenarios + 1 shutdown subprocess runner.
3. **Suite Batch 2 Expense (`application/modules/expense/tests/Batch2_audit_test.php`):**
   - **PASS**: 54 fixture scenarios + 1 shutdown subprocess runner.
4. **Suite Batch 1 Expense (`application/modules/expense/tests/Batch1_audit_test.php`):**
   - **PASS**: 36 fixture scenarios + 1 shutdown subprocess runner.
5. **Regresi Audit Rollout Modul Fase 1 dan Fase 2:**
   - `pengajuan_rutin` Batch 1 (58 scenarios + 1 subprocess) & Batch 2 (13 scenarios + 1 subprocess): **PASS**.
   - `request_mutasi` Batch 1 (391 scenarios + 2 subprocesses), Batch 2 (275 scenarios + 2 subprocesses), Batch 3 (10 scenarios + 1 subprocess): **PASS**.
   - `pembayaran_material` Batch 1 (246 scenarios), Batch 2 (551 scenarios): **PASS**.
   - `request_pr_stok` (24 endpoints regression suite): **PASS**.
   - `Kasbon_attachments_test.php` (kasbon attachments regression suite): **PASS**.
6. **Pemeriksaan Statis:**
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/controllers/Expense.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch4_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch3_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch2_audit_test.php` (0 errors).
   - PHP Lint: `C:/xampp74/php/php.exe -l application/modules/expense/tests/Batch1_audit_test.php` (0 errors).
   - `git diff --check`: PASS (0 whitespace error).

## SHA dan Metrik Controller

- Controller: `application/modules/expense/controllers/Expense.php`
- SHA-256 akhir: `04F68F87D81C5637E383D7647A85B5B73CAAA5E8F6B29E5F4F95666AF4B6B954`
- Total baris: 6183
