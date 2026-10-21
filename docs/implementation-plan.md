# Penambahan Notifikasi SweetAlert2 pada Aksi Posting Jurnal All — 2026-10-16

## Konteks dan Kebutuhan
Pengguna meminta agar saat melakukan posting jurnal pada modul `jurnal_all`, sistem menampilkan notifikasi menggunakan **SweetAlert2**, baik untuk skenario sukses maupun skenario gagal.

## Rencana Fase Implementasi
- **Fase 1: Pemuatan Asset SweetAlert2**:
  - Menyertakan stylesheet `assets/dist/sweetalert2.min.css` dan library script `assets/dist/sweetalert2.min.js` pada `application/modules/jurnal_all/views/workspace.php`.
  - Menambahkan aturan CSS z-index `.swal2-container { z-index: 100000 !important; }` pada `assets/css/jurnal-all.css` untuk memastikan pop-up SweetAlert2 selalu berada di atas modal Bootstrap (`#ja-modal` z-index 1050) dan fixed header.
- **Fase 2: Integrasi Handler Notifikasi pada JavaScript**:
  - Pada `assets/js/jurnal-all.js` method `write(action)`:
    - Menambahkan dialog konfirmasi interaktif menggunakan `Swal.fire` (dengan fallback ke `root.confirm`).
    - Menampilkan notifikasi sukses SweetAlert2 (`icon: 'success'`) saat request posting/revisi berhasil (`result.msg` atau pesan default sukses).
    - Menampilkan notifikasi gagal SweetAlert2 (`icon: 'error'`) saat request posting/revisi gagal (`error.message`).
    - Menjaga pesan inline `#ja-modal-message` dan `#ja-message` tetap berfungsi sebagai fallback aksesibilitas.
- **Fase 3: Pengujian & Validasi**:
  - Menjalankan unit test client (`Client_test.js`) di Node.js.
  - Menjalankan seluruh unit & integration test PHP di container Docker `sendigs_ss`.
  - Verifikasi syntax linting JS dan PHP.
- **Fase 4: Publikasi ke GitHub Remote `development`**:
  - Commit dan push perubahan ke branch `development`.

---

# Penambahan Kolom tgl_posting dan posted_by pada tr_jurnal & Integrasi Posting — 2026-10-16

## Konteks dan Kebutuhan
Sebelumnya, tanggal transaksi jurnal (`tgl_jurnal`) mencerminkan tanggal dokumen operasional (misalnya tanggal invoice atau tanggal voucher kas). Namun, tanggal aktual user mengeksekusi posting jurnal ke General Ledger akuntansi bisa saja berbeda hari atau minggu karena keterlambatan proses administrasi keuangan.
Pengguna meminta:
1. Menambahkan kolom `tgl_posting` (`DATETIME NULL`) dan `posted_by` (`VARCHAR(255) NULL`) langsung pada tabel operasional `tr_jurnal`.
2. Melakukan backfill pada baris data lama yang berstatus sudah terposting (`sts = '1'`) menggunakan nilai `tgl_jurnal` dan user `'203'` (Finance).
3. Mengintegrasikan pencatatan otomatis `tgl_posting` realtime (`NOW()` timezone Asia/Jakarta) dan user login saat user menekan tombol Posting pada `Jurnal_all_dispatcher`.
4. Menyesuaikan model `Jurnal_all_model.php` untuk membaca langsung `tgl_posting` dan `posted_by` dari `tr_jurnal`, serta fallback ke GL tras jika nilai tanggal belum tersedia.

## Keputusan Teknis dan Arsitektur
1. **Skema Database & Backfill Idempotent**:
   - File migrasi: `application/modules/jurnal_all/migrations/005_add_tgl_posting_to_tr_jurnal.sql`.
   - Menambahkan kolom `tgl_posting DATETIME NULL AFTER sts` dan `posted_by VARCHAR(255) NULL AFTER tgl_posting` pada `tr_jurnal`.
   - Update 3.383 baris historis yang sudah terposting (`sts = '1' AND tgl_posting IS NULL`) dengan `tgl_posting = CAST(tgl_jurnal AS DATETIME)` dan `posted_by = '203'`.
2. **Pencatatan Otomatis pada Dispatcher**:
   - Pada `Jurnal_all_dispatcher::execute('post', ...)`: Setelah delegasi modul sumber sukses dan commit terjadi, baris `tr_jurnal` diupdate dengan `tgl_posting = $nowJakarta` dan `posted_by = (string)$actor['id']`.
   - Update diproteksi dengan pemeriksaan metode database agar mock testing tidak terpengaruh.
3. **Pemeriksaan Skema Dinamis (`hasPostingColumns()`)**:
   - `Jurnal_all_model` dilengkapi method `hasPostingColumns()` yang memeriksa apakah kolom `tgl_posting` dan `posted_by` ada di `tr_jurnal`. Hal ini menjamin isolasi unit test SQLite in-memory (seperti `Query_test.php`) tetap berjalan 100% tanpa error column not found.
4. **Hierarki Data Posting**:
   - Nomor Jurnal Accounting (`source_numbers`) tetap diambil secara otoritatif dari GL tras (`javh.nomor`, `jarh.nomor`, `japh.nomor`, `jurnal.nomor`) untuk menghindari nomor duplikat historis pada `tr_jurnal.no_jurnal`.
   - Tanggal Posting (`posted_at`) diambil dari `tr_jurnal.tgl_posting`, dengan fallback ke tanggal GL tras jika kosong.
   - User Posting (`posted_by`) diambil dari `tr_jurnal.posted_by` (ID) yang kemudian dipetakan ke nama lengkap (`users.nm_lengkap` / `users.username`) melalui `resolveUserNames()`.

---

# Integrasi Pengambilan No. Jurnal Accounting, Tgl Posting, dan User dari Database GL Tras (javh, jarh, japh, jurnal) — 2026-10-16

## Konteks dan Kebutuhan
Pada modul `report_jurnal_all` (`localhost:8080/report_jurnal_all`), kolom **No. Jurnal Accounting**, **Tgl Posting**, dan **Diposting Oleh** sebelumnya menampilkan `-` karena hanya mengandalkan tabel staging `tr_jurnal_postings` yang belum ada/terisi untuk data historis, sementara `tr_jurnal.no_jurnal` pada data lama berisi nilai duplikat seragam (`00001-AJV-IX-26`).

User meminta agar nomor jurnal accounting, tanggal posting, dan pembuat jurnal diambil langsung dari database akuntansi (tras) di tabel `javh`, `jarh`, `japh`, dan `jurnal` pada masing-masing database multi-perusahaan (`db_sendigs_ss_stm`, `db_sendigs_ss_vuca`, `db_sendigs_ss_sustain`).

## Keputusan Teknis dan Arsitektur
1. **Lazy Loading Database GL Multi-Company**:
   - Helper `getGlDb($companyId)` memuat koneksi `accounting_stm` (company 1, 6, 7), `accounting_vuca` (company 4), atau `accounting_sustain` (company 3/lainnya) secara terisolasi. Jika koneksi tidak tersedia (misal unit test SQLite), fungsi mengembalikan `null` dengan aman.
2. **Batch Routing & Query Optimization**:
   - `resolveGlMetadata(&$rows)` mengelompokkan baris berdasarkan target database dan menjalankan chunked query `WHERE ... IN (...)` (chunk 200) ke tabel header (`javh` untuk INV/EXP, `jarh` untuk RCV, `japh` untuk PAY/PCS) dan tabel GL `jurnal`.
3. **Resolusi User Posting**:
   - `resolveUserNames($ids)` memetakan user ID/username (misal `203` -> `Finance`, `7` -> `Admin`) melalui tabel `users` di `db_sendigs_ss`.
4. **Hierarki Fallback Metadata**:
   - `tr_jurnal_postings` (jika ada) -> Header GL (`javh`/`jarh`/`japh`) -> Detail GL (`jurnal`) -> `-`.
5. **Formatting Tampilan**:
   - `assets/js/jurnal-all.js` diformat agar menampilkan `DD Bulan YYYY WIB` secara rapi bila data berupa DATE (tanpa jam), dan menyertakan jam bila datetime `created_on` tersedia.

---

# Audit dan Verifikasi Komprehensif Alur Posting Jurnal All (R1–R7) — 2026-10-16

## Konteks dan Tujuan
Audit menyeluruh, verifikasi kesesuaian jalur transaksi, dan pengujian alur posting modul `jurnal_all` terhadap kelima modul sumber asalnya:
1. Invoicing (`jurnal_invoicing` / INV)
2. Penerimaan Piutang (`jurnal_penerimaan` / RCV)
3. Pembayaran (`jurnal_payment` / PAY)
4. Expense Report (`jurnal_expense` / EXP)
5. Pencatatan Petty Cash (`jurnal_payment_petty_cash` / PCS)

Membuktikan kepatuhan arsitektur dispatcher, integritas isolasi parameter `$_POST`, proteksi advisory lock MySQL, konsistensi multi-database accounting (STM, VUCA, Sustain), validasi batas rollback/transaksi, penanganan transaksi khusus Petty Cash (Single, Refill, Intercompany split batch), serta pencatatan metadata nomor accounting final di tabel `tr_jurnal_postings`.

---

## Fondasi Arsitektur dan Klarifikasi Jalur Transaksi

### 1. Klarifikasi Staging Table: `tr_jurnal` vs `jurnaltras`
- **Temuan Lapangan**: Pada modul legacy (seperti `metode_pembelian`, `pembayaran_material`, `penerimaan`), transaksi draft dicatat pada tabel `jurnaltras`. Namun pada arsitektur modern yang menginduki `jurnal_all` beserta kelima modul sumbernya (`jurnal_invoicing`, `jurnal_penerimaan`, `jurnal_payment`, `jurnal_expense`, `jurnal_payment_petty_cash`), tabel staging/draft transaksi jurnal adalah **`tr_jurnal`**.
- **Bukti Kode**: Pencarian kode (grep) pada seluruh direktori `jurnal_all/`, `jurnal_invoicing/`, dan `jurnal_penerimaan/` menghasilkan 0 referensi terhadap tabel `jurnaltras`. Teks respons legacy `"Posting jurnal ke Tras Sukses!"` pada `Jurnal_penerimaan.php:228` adalah penamaan historis pada pesan UI; tabel yang dimutasi dan diperbarui statusnya (`sts = '1'`) secara riil adalah `tr_jurnal`.
- **Tabel Target GL**: Seluruh detail posting jurnal diarahkan ke tabel buku besar umum (General Ledger) yaitu **`jurnal`** pada database akuntansi target (`accounting_stm`, `accounting_vuca`, atau `accounting_sustain`).

### 2. Header Akuntansi dan Penomoran Cabang
Setiap jenis transaksi menuliskan header akuntansi spesifik pada database target:
- **`javh`** (Journal Voucher Header): Digunakan oleh Invoicing (tipe `JV`, format `101-{subcab}JV{yy}{urut5}`) dan Expense Report (tipe `JV`, format `101-{subcab}JV{yy}{urut5}`). Mengupdate counter `nomorJC` pada `pastibisa_tb_cabang`.
- **`jarh`** (Journal AR Header): Digunakan oleh Penerimaan Piutang (tipe `BUM`, format `101BM{yy}{urut5}`). Mengupdate counter `nobum` pada `pastibisa_tb_cabang`.
- **`japh`** (Journal AP Header): Digunakan oleh Pembayaran Kas/Bank dan Petty Cash (tipe `BUK`, format `101BK{subcab}{yy}{urut5}`). Mengupdate counter `nobuk` pada `pastibisa_tb_cabang`.

### 3. Matriks Routing Multi-Perusahaan (Multi-Company Database Selector)
Alokasi database akuntansi target ditentukan secara ketat di sisi server berdasarkan `id_company` transaksi:
- **`accounting_stm`** (`DBACC_STM`): Untuk `id_company` 1, 6, 7 (STM/Konsultasi Utama).
- **`accounting_vuca`** (`DBACC_VUCA`): Untuk `id_company` 4 (VUCA).
- **`accounting_sustain`** (`DBACC_SUST`): Untuk `id_company` lainnya (Sustain / Default).
Klien tidak memiliki akses parameter untuk memanipulasi target routing database.

### 4. Integrasi Subledger Kartu Piutang (`tr_kartu_piutang`)
- **Invoicing (`jurnal_invoicing`)**: Menulis baris piutang baru ke tabel `tr_kartu_piutang` (`tipe = 'JV'`, `debet = total_akhir_jurnal`, `kredit = 0`) saat jurnal diposting.
- **Penerimaan Piutang (`jurnal_penerimaan`)**: **Tidak** menulis ke `tr_kartu_piutang` pada fase posting jurnal GL. Pelunasan piutang (kredit) telah dicatat lebih awal pada fase operasional penerimaan uang (`penerimaan/controllers/Penerimaan.php:906`). Desain ini mencegah duplikasi pencatatan kredit kartu piutang saat verifikasi akuntansi berlangsung.

---

## Cakupan Audit Kebutuhan R1 sampai R7

### R1. Verifikasi Alur Posting Tab Invoicing (INV)
- **Delegasi Dispatcher**: `INV` dipetakan ke rute `post => jurnal_invoicing/save_posting_jurnal` dan `revise => jurnal_invoicing/update_sts_revisi_jurnal`.
- **Penulisan Header & GL**: Header `javh` (`kdcab = '101'`, `jml = total_akhir_jurnal`), detail `jurnal` (`tipe = 'JV'`), update status staging `tr_jurnal.sts = '1'`.
- **Counter Cabang**: Menjalankan query `UPDATE pastibisa_tb_cabang SET nomorJC = nomorJC + 1 WHERE nocab='101'` pada database akuntansi target.
- **Kartu Piutang**: Menulis entry debit ke `tr_kartu_piutang`.
- **Multi-Company**: Resolusi perusahaan melalui relasi berjenjang (`kons_tr_penawaran` -> `kons_tr_spk_penawaran` -> `kons_tr_penawaran_non_konsultasi`) menuju database STM, VUCA, atau Sustain.

### R2. Verifikasi Alur Posting Tab Penerimaan (RCV)
- **Delegasi Dispatcher**: `RCV` dipetakan ke rute `post => jurnal_penerimaan/save_posting_jurnal` dan `revise => jurnal_penerimaan/update_sts_revisi_jurnal`.
- **Penulisan Header & GL**: Header `jarh` (`jenis_reff = 'BUM'`, `jenis_ar = 'BUM'`), detail batch `jurnal` (`tipe = 'BUM'`), update status staging `tr_jurnal.sts = '1'`.
- **Counter Cabang**: Menjalankan query `UPDATE pastibisa_tb_cabang SET nobum = nobum + 1 WHERE nocab = '101'`.
- **Integritas Kartu Piutang**: Memastikan kartu piutang tidak mengalami mutasi ulang ganda (idempotent terhadap pencatatan operasional penerimaan).
- **Multi-Company**: Resolusi `id_company` dari `tr_penerimaan_piutang_detail` -> `tr_invoicing` -> penawaran, mengarahkan BUM ke database akuntansi yang tepat.

### R3. Verifikasi Alur Posting Tab Pembayaran (PAY)
- **Delegasi Dispatcher**: `PAY` dipetakan ke rute `post => jurnal_payment/save_posting_jurnal`.
- **Validasi Sumber**: Memvalidasi eksistensi data induk di `payment_approve` sebelum eksekusi.
- **Penomoran BUK Native**: Mengambil sequence BUK dari `pastibisa_tb_cabang.nobuk` pada database target via `Jurnal_payment_nomor_model::get_no_buk`.
- **Penulisan Header & GL**: Header `japh` (`jenis_reff = 'BUK'`, `jenis_ap = 'V'`), detail batch `jurnal` (`tipe = 'BUK'`).
- **Update Status**: Mengupdate `tr_jurnal.sts = '1'` dan menginkremen `pastibisa_tb_cabang.nobuk + 1`.

### R4. Verifikasi Alur Posting Tab Expense Report (EXP)
- **Delegasi Dispatcher**: `EXP` dipetakan ke rute `post => jurnal_expense/save_posting_jurnal`.
- **Isolasi Kelompok Transaksi**: Pengelompokan baris jurnal pada Expense Report wajib menyertakan `id_company` (`no_transaksi`, `jenis_transaksi`, `id_company`). Ini mencegah pencampuran biaya beda entitas ke dalam satu dokumen jurnal akuntansi.
- **Perlindungan Concurrency Ganda**: Menggunakan dual-layer locking:
  1. Advisory lock grup pada `jurnal_all` (`jurnal_all:<hash>` selama 10 detik).
  2. Advisory lock skema pada `jurnal_expense` (`jurnal_expense:<schema>:101` selama 10 detik).
  3. Row-level lock `FOR UPDATE` saat membaca `tr_jurnal`.
- **Penulisan Header & GL**: Header `javh`, detail batch `jurnal`, dan optimistik lock counter `nomorJC = counter` pada `pastibisa_tb_cabang`.
- **Kompensasi Rollback MyISAM**: Mengingat tabel akuntansi legacy berjenis MyISAM yang tidak mendukung rollback transaksi InnoDB, `Jurnal_expense_posting_model` menerapkan *explicit compensatory rollback*: menghapus record `jurnal` dan `javh` serta mengembalikan `nomorJC` jika terjadi kegagalan commit pada database operasional.

### R5. Verifikasi Alur Posting Tab Pencatatan Petty Cash (PCS)
- **Delegasi Dispatcher**: `PCS` dipetakan ke rute `post => jurnal_payment_petty_cash/save_posting_jurnal`.
- **Proteksi Unclear COA Gate**: Memvalidasi keberadaan akun `2103-01-01` dan `2103-01-02` (Hutang ke STM yang belum diverifikasi Finance). Jika terdeteksi, posting diblokir dengan peringatan.
- **Skenario 1: Single Company**:
  - Transaksi hanya melibatkan 1 perusahaan (STM, VUCA, atau Sustain).
  - Menghasilkan 1 nomor BUK native dari target accounting database.
  - Membuka transaksi ganda (operasional `$this->db` dan `$target_db`), menulis `japh` & `jurnal`, lalu commit terkoordinasi.
- **Skenario 2: Refill Pettycash**:
  - Transaksi pengisian kas kecil. Ditampilkan pada tab `PAY` di UI untuk kemudahan user, namun routing internal didelegasikan secara tepat ke `PCS` (`jurnal_payment_petty_cash`).
  - Menghasilkan 1 nomor BUK native dan commit ke database akuntansi perusahaan pemilik kas.
- **Skenario 3: Intercompany (Split Batch)**:
  - Transaksi melibatkan transaksi silang cabang (VUCA/SUSTAIN) dan pusat (STM).
  - Menginisialisasi **TIGA transaksi database bersamaan**: operasional `$this->db`, cabang `$db_company`, dan pusat `accounting_stm`.
  - Mengambil **DUA nomor BUK native independen**: satu dari cabang dan satu dari STM.
  - Memecah batch baris jurnal: baris beban cabang dimasukkan ke `$db_company` dengan BUK Cabang; baris penyeimbang STM dimasukkan ke `accounting_stm` dengan BUK STM.
  - Melakukan ordered commit: operasional `$this->db` di-commit pertama; jika gagal, kedua database akuntansi di-rollback. Selanjutnya commit STM dan cabang.
  - Mengembalikan kedua nomor BUK dalam array `accounting_numbers` untuk disimpan ke metadata.

### R6. Integritas Dispatcher, Advisory Lock, dan Metadata Posting
- **Isolasi Request (`$_POST` dan Output Buffer)**:
  - Sebelum delegasi, `$_POST` asli disimpan ke `$original = $_POST;` dan level buffer dicatat `$bufferLevel = ob_get_level();`.
  - Pada blok `finally`, `$_POST = $original;` selalu dikembalikan ke state awal dan buffer dibersihkan dengan `while (ob_get_level() > $bufferLevel) ob_end_clean();`.
- **Advisory Lock MySQL**:
  - Mengambil lock via `SELECT GET_LOCK(?, 10)` berbasis hash SHA256 group identity.
  - Lock selalu dilepaskan pada blok `finally` via `SELECT RELEASE_LOCK(?)`.
- **Validasi Keseimbangan Debit-Kredit Pre-Delegation**:
  - Validasi ketat bahwa `sum(debit) == sum(kredit)`, total nominal > 0, dan tidak ada nilai sen negatif (`cents < 0`) sebelum modul native dipanggil.
- **Kekebalan dari Tampering Client**:
  - Input POST dari klien yang memanipulasi nominal, COA, routing, atau company dibuang sepenuhnya. Dispatcher hanya menyusun ulang `$_POST` berisi server-authoritative `id`, `no_transaksi`, dan `jenis_transaksi`.
- **Pencatatan Metadata Posting (`tr_jurnal_postings`)**:
  - Tabel InnoDB `tr_jurnal_postings` menyimpan `group_key` (PK SHA256), `source`, `no_transaksi`, `jenis_transaksi`, `id_company`, `posted_at` (Asia/Jakarta), `posted_by`, `posted_by_name`, dan `accounting_numbers` (JSON).
  - Menyimpan nomor accounting final asli (bukan nomor staging).
  - Jika native posting accounting sukses namun penulisan metadata gagal, status bisnis tetap dikembalikan sukses dengan warning `posted_metadata_missing` (tidak memicu posting ulang / duplicate entry).

### R7. Penanganan Kegagalan, Rollback Boundaries, dan Propagasi Error
- **Rollback Boundary**:
  - Kegagalan SQL pada database akuntansi melempar exception sebelum commit database operasional, memicu `trans_rollback()` pada `$this->db` (membatalkan perubahan status di `tr_jurnal`).
  - Untuk Expense Report, mekanisme kompensasi MyISAM secara proaktif membersihkan baris `javh` dan `jurnal` yang sempat terinsert.
- **Propagasi Error**:
  - Respons error dari modul native (`save === 0` atau `status === 0`) ditangkap dispatcher, tidak mencatat metadata, mengembalikan pesan kesalahan asli ke UI, dan membebaskan advisory lock.

---

## Rencana Fase dan Kriteria Keberhasilan

| Fase | Deskripsi | Lingkup / Modul | Kriteria Keberhasilan | Status |
|---|---|---|---|---|
| **Fase 1** | Baseline Discovery & Docker Test Runner Setup | Lingkungan Docker `sendigs_ss`, PHP 7.4.33 CLI | Perintah eksekusi `docker exec` terverifikasi, syntax lint 13 file modul lulus | **SELESAI** |
| **Fase 2** | Verifikasi Alur Invoicing & Penerimaan (R1, R2) | `jurnal_invoicing`, `jurnal_penerimaan`, `tr_jurnal`, `javh`, `jarh`, `jurnal`, `tr_kartu_piutang` | `Legacy_posting_test.php` (198 assertions) PASS, `Revision_test.php` (24 assertions) PASS | **SELESAI** |
| **Fase 3** | Verifikasi Alur Payment & Expense Report (R3, R4) | `jurnal_payment`, `jurnal_expense`, `japh`, `javh`, `jurnal`, company isolation, MyISAM compensation | `Posting_test.php` (118 assertions) PASS, `Query_test.php` (126 assertions) PASS | **SELESAI** |
| **Fase 4** | Verifikasi Alur Petty Cash (R5) | `jurnal_payment_petty_cash`, Single, Refill, Intercompany split batch, dual BUK | `Petty_posting_test.php` (113 assertions) PASS, Unclear COA gate terverifikasi | **SELESAI** |
| **Fase 5** | Verifikasi Dispatcher, Security & Metadata (R6, R7) | `Jurnal_all_dispatcher`, advisory lock, request isolation, balance gate, `tr_jurnal_postings` | `Dispatcher_test.php` (45 assertions) PASS, `Metadata_test.php` (20 assertions) PASS | **SELESAI** |
| **Fase 6** | Eksekusi Test Suite Penuh & Laporan Handoff | Seluruh 9 test suite PHP + 3 lint checks + client checks | 740 PHP assertions + 46 Node.js assertions PASS (0 failure), handoff report komprehensif | **SELESAI** |

---

# Penyesuaian Plan Tagih, Actual Plan Tagih, Invoicing, dan Jurnal SPK 029/STM/MKT-SPK/III/26 — 2026-10-16

- Konteks: SPK Penawaran nomor 029/STM/MKT-SPK/III/26 (PT Indo Tambangraya Megah Tbk., nilai kontrak Rp 234.234.234,00) mengalami perubahan TOP di `db_consultant_new.kons_tr_spk_penawaran_payment`:
  - Penagihan 1 (id 1642): 30% = Rp 78.078.078,00 (desc: 'setelah kick gap analisis')
  - Penagihan 2 (id 1643): 20% = Rp 39.039.039,00 (desc: 'setelah kick off')
  - Penagihan 3 (id 1848): 50% = Rp 117.117.117,00 (desc: 'setelah dinyatakan lulus oleh badan sertifikasi')
- Masalah: Penagihan 1 sebelumnya dicatat 50% (Rp 117.117.117,00) di Plan Tagih (id 1134), Actual Plan Tagih (57213/ACT-TGH/VIII/26), Invoicing (00277-INV-VIII-26 / no 188/STM/2026), tr_jurnal, kartu piutang, dan sudah diposting ke GL STM (101-AJV2603689). Penagihan 2 masih tercatat 50%, dan Penagihan 3 belum ada di Plan Tagih.
- Keputusan hasil /grill-me:
  1. Koreksi langsung data Penagihan 1 pada Invoice 188/STM/2026, tr_jurnal, kartu piutang, dan jurnal GL STM (101-AJV2603689) agar langsung sinkron ke nilai 30% (Rp 78.078.078,00).
  2. Sinkronkan seluruh termin di `kons_tr_plan_tagih_detail`: ubah Penagihan 1 menjadi 30% (Rp 78.078.078), ubah Penagihan 2 menjadi 20% (Rp 39.039.039), dan tambahkan Penagihan 3 sebesar 50% (Rp 117.117.117) agar total termin 100%.
  3. Set tgl_plan_tagih default untuk baris baru Penagihan 3 ke 2026-12-01 (dapat diubah via UI kemudian).
  4. Update nominal & persen pada baris aktual penagihan aktif (57213/ACT-TGH/VIII/26) yang terhubung ke invoice menjadi Rp 78.078.078 (30%).
- Fase 1: Snapshot & pre-adjustment baseline audit.
- Fase 2: Eksekusi transaksi penyesuaian DB pada db_sendigs_ss dan db_sendigs_ss_stm.
- Fase 3: Verifikasi integritas relasi, kalkulasi pajak, balance jurnal Finance, balance GL STM, dan sinkronisasi kartu piutang.
- Fase 4: Dokumentasi dan pembaruan checkpoint docs/task-progress.md.

# Metadata posting dan nomor final accounting — 2026-10-15

- Status penutupan: fase1–4 selesai dan terverifikasi; schema003 sudah diterapkan hanya development lokal. Bukti: docs/jurnal-all-posting-metadata-2026-10-15.md (963 assertions), tanpa posting accounting nyata atau penerapan environment lain.

- Pengguna meminta penyimpanan waktu/user posting dan tampilan Report; histori tanpa metadata '-'. Klarifikasi nomor: final accounting, bukan nomor gabungan baru. Tidak mengubah counter/algoritme penomoran atau no_jurnal staging lama.
- Fase1 baseline/regresi: petakan output nomor asli INV(JV), RCV(BUM), PAY(branch native), EXP(JV), PCS(BUK single/refill/intercompany). Snapshot hash dependency sebelum perubahan cocok manifest.
- Keputusan setelah audit: native commits lintas DB berbeda; gunakan tabel InnoDB tr_jurnal_postings keyed SHA256 native group identity. Handler menambah accounting_numbers pada respons sukses setelah commit yang sudah diperiksa. Dispatcher menyiapkan auth actor/preflight migration sebelum delegasi, kemudian menyimpan metadata immutable dalam transaksi tersendiri setelah native sukses. Tidak membungkus/mengubah urutan commit mesin posting.
- Kegagalan native/commit tidak menyimpan metadata. Bila accounting sukses tetapi metadata insert/commit gagal, kembalikan business success dengan warning metadata dan audit outcome aman; UI menampilkan warning setelah reload. Jangan memicu retry posting. Crash gap tetap terdokumentasi. Migrasi missing menolak sebelum native writes.
- Tabel metadata group menyimpan tanggal Asia/Jakarta, ID+snapshot username auth, dan JSON nomor final per target company. Tidak menerima actor/nomor/nominal dari POST. Read report menampilkan hanya final terkonfirmasi dari metadata; staging lama bukan fallback nomor accounting. Histori tanpa bukti tetap '-'. Detail/Excel/search ikut, satu report row per source group; intercompany menampilkan semua nomor dengan label target.
- Fase2 migrasi CREATE TABLE IF NOT EXISTS+rollback terpisah dan writer/read model, dispatcher/response adapters; fase3 report table/detail/Excel/user column/format tanggal; fase4 fixture native success/failure/commit/schema missing/metadata failure/audit dedup, metadata reader/histori, migration dua kali, syntax/whitespace/hash/checkpoint dan apply create-table hanya local development setelah validasi.
- Tidak ada posting accounting nyata atau browser test. Penerapan ke server lain tidak termasuk. Batas existing MyISAM/lintas DB tidak dirombak; metadata storage failure tidak mengubah hasil bisnis.

# Tampilan Expense Report dan relasi kasbon — 2026-10-15

- Consultant: Keterangan Expense Report Consultant - [no_transaksi]. EXP semua jenis: No. Referensi nomor kasbon yang benar-benar terhubung, jika tidak ada '-'. Standard: Realisasi Kasbon [Pengembalian/Reimburse] - [nama pengaju PR kasbon].
- Grounding: header/detail tr_expense.id_kasbon dan tr_expense_detail.id_kasbon/kasbon_pr_non_po_pett, reverse tr_kasbon.id_expense_pett_pr_non_po; consultant header.id_header → kasbon header.id. PR user dari tipe_pr/id_pr ke rutin_non_planning_header/material_planning_base_on_produksi/tran_pr_header dan users.id_user=created_by, mengikuti Expense controller.
- Fase1 fixture RED: regular/consultant, no relation/dangling, banyak kasbon/PR types, settlement type, precision/duplicates, source isolation.
- Fase2 enrichment bulk terpisah dari agregasi SUM jurnal. Nomor kasbon unik diurutkan; nama pengaju valid unik. Label jenis dari lebih_bayar/kurang_bayar positif atau selisih total detail; impas/unknown tanpa subtype, user unavailable '-'. Source key, grouping company, eligibility, raw COA/posting tetap.
- Fase3 query/controller/dispatcher/native/client + MariaDB fixture terisolasi, lint/hash/whitespace; dokumentasi. Tidak memakai konfigurasi development remote untuk tes atau query aplikasi.

# Detail Petty Cash per company — 2026-10-15

- Pengguna mengonfirmasi: pisahkan detail preview per company, satu PCP tetap satu row dan satu posting seluruh sisi; jangan memecah daftar/action atau mengubah nominal daftar.
- Design Read: modal review Finance mengikuti preview sumber, ENERGY2/RHYTHM2/MOTION1 sesuai palet aktif. Bagian berjudul Sisi [company], tabel COA serta subtotal/balance sendiri; pakai header biru/status warna existing, tanpa asset/layout global baru.
- Batch: fixture RED → pembentuk section berbasis id_company dan cents shared, dipanggil setelah native resolver preview → renderer per company untuk pending/report → regresi grouping single/intercompany/precision/isolation/escaping dan suite fitur+lint/checkpoint.
- Pertahankan can_post/audit/blocked COA/dispatcher/native routing, PCP/RPC, search/export/list totals dan semua source nonPCS. Test memakai fixture, tidak mengakses koneksi remote di development/database.php.

# Referensi RPC Pencatatan Petty Cash — 2026-10-15

- Ganti referensi tampilan PCS jenis Petty Cash dari PCP ke nomor pelaporan melalui tr_expense_petty_cash.no_pencatatan → tr_pelaporan_petty_cash_detail.pencatatan_id → tr_pelaporan_petty_cash.no_pelaporan. Ikuti relasi aktif existing status draft/waiting/approved; laporan rejected tidak menggantikan referensi aktif.
- Batch: fixture RED → bulk lookup terpisah dari agregasi jurnal (hindari jumlah/nominal ganda) → query/controller/dispatcher/native/client dan MariaDB terisolasi → lint/hash/whitespace/checkpoint. Missing RPC tampil kosong/dash, beberapa RPC aktif unik diurutkan dan digabung.
- Scope list/search/sort/detail header/report/Excel header menggunakan enrichment shared; PCP tetap no_transaksi/action key dan detail COA/posting tetap. Refill/Payment Hutang pada tab PAY tidak diubah. Database development remote tidak dipakai untuk testing.

# Semua Tahun seluruh tab Jurnal — 2026-10-15

- Perluas opsi tahun kosong dari INV ke semua tab pada shared Jurnal/Report; default/reset tetap tahun berjalan. State per tab, summary/list/export/options ikut kontrak sama.
- Tahun kosong tanpa bulan tidak membatasi tanggal. Tahun kosong+bulan: INV tetap match baris nyata, tab lain tetap bulan tanggal pertama kelompok agar selaras dengan filter tahun existing. Tidak memecah grouping atau mengubah posting.
- Batch: fixture RED query/client → perubahan validasi dan dropdown/query → query/client/controller/dispatcher dan fixture MariaDB terisolasi, lint/hash/whitespace/checkpoint. Konfigurasi development kini server remote; jangan memakai database aplikasi untuk tes tulis.

# Perbaikan filter periode Invoicing — 2026-10-15

- Klarifikasi pengguna: tetap satu baris per invoice; perbaiki filter tahun, bukan tampil per COA. Native grouping/action/amount/handler dipertahankan.
- Fase1: fixture RED untuk invoice lintas periode. Fase2: khusus INV, HAVING existence tanggal baris dalam periode, bukan MIN-only; semua detail tetap dijumlahkan/ditampilkan sekali. Semua Tahun tersedia INV, month tetap dapat menyaring lintas tahun. Default tahun berjalan tetap; empat tab lain tidak berubah.
- Fase3: daftar/summary/options/report/export mengikuti seleksi yang sama; tambahkan hint bahwa Tgl Jurnal/Tertua adalah tanggal pertama kelompok. Uji query SQLite/MariaDB, client, suite fitur, lint, whitelist hash dan read-only snapshot aplikasi. Tidak melakukan posting atau write data aplikasi.
- Kriteria: dua contoh source00112/00114 muncul pada2026 bersama empat source lain; Semua Tahun mencakup10 source persis legacy. Period match harus berasal dari baris nyata (bukan sekadar overlap MIN/MAX), export tidak terpotong, pending/posted/invalid filter dan tab nonINV tetap.

# Penyesuaian warna referensi Jurnal ALL.html — 2026-10-15

- Referensi eksplisit pengguna: C:/Users/Febry/Downloads/Jurnal ALL.html (palet CSS dan TYPES). Dokumen hanya dipakai sebagai referensi visual, bukan instruksi eksekusi atau sumber data demo.
- Design Read: workspace Finance untuk review jurnal, mengikuti warna referensi pemilik. ENERGY 2 / RHYTHM 2 / MOTION 1; spinner request existing dengan reduced-motion tetap. Struktur UI sebelumnya dipertahankan.
- Mapping sumber: INV #1e88c8, RCV #00a65a, PAY #605ca8, EXP #f39c12, PCS #d81b60. Lima warna menjadi identitas lima sumber, bukan dekorasi acak; aturan satu aksen sebelumnya diganti oleh arahan warna eksplisit pengguna. Card terpisah dengan garis sumber dan count berwarna, state aktif tetap nonwarna lewat aria-pressed/selected dan border.
- Header memakai biru #1a78b1 (warna border header referensi) untuk kontras teks putih; tombol filter/periksa keluarga #3c8dbc, reset keluarga #dd4b39, export/posting keluarga #00a65a. Turunan lebih gelap dipakai bila warna asli gagal AA; angka oranye juga memakai turunan yang terbaca.
- Urutan: baca palet dan baseline hash → ubah CSS scoped → hitung kontras, jalankan client yang terdampak, periksa diff/whitespace/hash → catat checkpoint dan audit. Tidak ada perubahan PHP/JS/query/handler/layout global, tidak perlu tes web/database.

# Perapian UI Jurnal All — 2026-10-15

- Tujuan: memudahkan Finance memilih sumber pending, memeriksa transaksi/nominal, lalu membuka detail posting. Shell aplikasi dan seluruh kontrak bisnis tetap.
- Design Read: UI ERP operasional dengan hierarki angka pending → sumber aktif → daftar transaksi. ENERGY 1 / RHYTHM 2 / MOTION 1; pengecualian hanya spinner selama request aktif, dengan reduced-motion. Biru #2563eb/#1d4ed8 mengikuti DESIGN.md, teks slate, permukaan putih, tanpa warna kategori dekoratif, gambar, font baru, atau shadow bertingkat. Motif: angka tabular dan garis pemisah horizontal.
- Ringkasan lima sumber tetap setara karena merepresentasikan lima antrean; angka menjadi fokus, jumlah/total debit/umur tetap data server. Tab memakai label pendek dengan nama lengkap aksesibel. Filter dan ekspor dipisahkan; tabel mengutamakan nomor transaksi dan nominal.
- Fase 1: markup/CSS scoped, responsif, modal dan state loading/empty/error.
- Fase 2: presentasi JS (angka ringkasan, judul sumber, tabel dan pagination) tanpa mengganti ID/kunci, permission, query atau handler.
- Fase 3: regresi client/controller, render PHP pending/report, lint PHP/JS, contrast dan whitespace; dokumentasi/checkpoint/manifest. Tidak perlu tes browser atau posting nyata.
- Kriteria: mapping invoice/SPK/customer-project, filter per tab, loading race guard, semua kategori dan aksi lama tetap; layout hanya meluas bersama Report karena shared workspace.

# Nomor daftar Invoicing — 2026-10-15

Selesai: No. Transaksi menampilkan no_invoice dan No. Referensi menampilkan id_spk_penawaran, khusus INV. display_no_transaksi terpisah dari no_transaksi native agar grouping/posting aman; pencarian/sort/Excel/header modal konsisten, COA preview memakai invoice_number terpisah. RED sebelum fix; seluruh fitur505 assertions, PHP lint6 dan JS syntax PASS. Tidak mengubah tab lain, schema, atau handler posting.

# Keterangan daftar Invoicing — 2026-10-15

Selesai satu batch: keterangan header INV pada Jurnal/Report hanya nama customer - nama project; project kosong memakai SPK terhubung, komponen kosong tidak meninggalkan separator. Query enrichment tidak mengubah detail COA, Excel per COA, tab lain atau posting. RED sebelum fix; query48, MySQL14, seluruh suite fitur494 dan PHP lint3 PASS. Bukti dan hash di manifest/laporan.

# Rencana Implementasi Audit Trail

## Publikasi dua modul Expense — 2026-10-15

- Otorisasi: push jurnal_expense dan report_jurnal_expense ke main serta development; isi dua modul wajib identik. Tidak menyertakan ringkas kolom Record atau perubahan lokal modul lain.
- Fase 1 selesai: fetch main09857821/development39167ea6; dependency Jurnal Payment/Report Payment/recorder/logger/helper/constants identik. Regresi260 assertions/checks (Jurnal167 + Report93), audit recorder/logger/legacy PASS; 17 PHP lint dan JS PASS.
- Fase 2 selesai: index terpisah per branch dengan parent remote masing-masing, stage dua modul dan dokumen task saja, preserve HEAD/index/checksum pekerjaan existing. Tidak merge seluruh branch dan tidak force push.
- Fase 3 selesai: push atomik main c89b84fbd965903b9ab56220dd3809dd527cd50e dan development 402be62bb69beb5022c52fe8e4e89f98c0842f1a, ls-remote sesuai. Tree dua modul identik (3fc211d5f1a248da8b2fd12d03320bcd77af9ea8, b54d9cfad0eab1c81082edf2fac106012ee3e0b7), seluruh blob lokal cocok, dependencies/di luar scope tidak berubah. Tidak ada TODO push tersisa. Bukti docs/expense-modules-publication-2026-10-15.md. Migrasi menu hanya disertakan sebagai SQL; tidak menjalankan database remote atau posting accounting.


## Ringkas kolom Record Per Transaksi — 2026-10-14

- Acuan: recItemView HTML user, 11 kolom + Action existing =12. Urutan PGJ, dokumen/kategori, Request By/company, keperluan, DPP, PPN, PPH23, PPH21, admin, dibayarkan, disetujui, Action. Submitted di bawah nomor PGJ; approver tanpa kolom tersendiri.
- Scope: items view dan tes client saja; endpoint/snapshot/export/aksi/style serta Batch/Legacy tetap. Fase ringkas kolom selesai: items.php dan Tabs_client_test.js diubah, 15 menjadi12 kolom mengikuti recItemView HTML + Action. Submitted di bawah PGJ, company di Request By, approver tidak jadi kolom tersendiri. Endpoint/snapshot/style/aksi/Batch/Legacy tidak berubah. Validasi: client90, view46, query16 PASS; PHP lint dan whitespace PASS. Tidak browser/push. Next: tidak ada TODO implementasi kolom; perubahan masih lokal.


## Report Jurnal Expense — 2026-10-14

- Fase 1: model posted Expense Report/Expense Report Consultant, grouping transaksi/jenis/company; daftar, pencarian, filter/dropdown dan detail berdasarkan ID representative.
- Fase 2: controller lima endpoint (index/get_data_jurnal/view_jurnal/export_excel/export_jurnal), seluruhnya Report_Jurnal_Expense.View, audit sekali per request; tiga view mengikuti Report Jurnal Payment, detail cents read-only, export daftar.
- Fase 3: migrasi menu idempotent tepat setelah Report Jurnal Payment dan grant View ekuivalen, verifikasi lokal, regresi/lint/JS/whitespace serta manifest/laporan.
- Default disepakati: seluruh histori sts=1 termasuk jalur lama; tidak rekonsiliasi ledger, ubah posting/mapping, perubahan schema jurnal, browser test, atau Git push. Task ringkas kolom Record diselesaikan terpisah; lihat checkpoint ringkas kolom di atas.
- Status: Fase 1–3 selesai; 27 query, 42 controller, 12 view, 12 client checks PASS. Regresi Jurnal Expense167 + recorder/logger/legacy PASS; 8 PHP lint. Menu482/order16 setelah report payment477/order15, View1850, grant ekuivalen lengkap; dua run migration idempotent. MySQL read-only report kosong (source88 sts0 +8 NULL), tanpa posting live. File: modul report_jurnal_expense baru + manifest/laporan/README; plan/checkpoint diperbarui, source existing tidak diubah. Tidak ada fase implementasi aktif; tidak push. Batas: status source, bukan rekonsiliasi ledger/snapshot. Next: tidak ada TODO report; migration environment lain saat pemasangan. Bukti: docs/report-jurnal-expense-2026-10-14.md.


## Incoming Stok Cash: kode barang opsional — 2026-10-14

Tujuan: master stok valid dapat diterima ketika accessories.id_stock string kosong atau NULL, sesuai aturan bisnis user. Identitas tetap id_material; kode_produk cost book mempertahankan nilai sumber. Hanya model Incoming Cash, tes regresi dan dokumentasi yang berubah; tanpa perubahan Master Barang Stok, schema, helper bersama, data bisnis atau browser. Publikasi Git diotorisasi terpisah pada langkah4.

1. **Fase 1 — regresi RED:** grounding endpoint/query/model/helper dan fixture existing; tambahkan tes kosong/NULL, receipt campuran, master hilang/nama kosong dan rollback barang tanpa kode. Jalankan suite pada schema codex_incoming_test_* terpisah; buktikan kegagalan karena guard kode wajib.
2. **Fase 2 — perbaikan:** hapus hanya syarat empty(id_stock) dari guard model; pertahankan guard nama, nilai kode sumber, approval/outstanding/transaksi/commit/rollback. Selesai setelah seluruh suite Incoming lulus, termasuk konkurensi serta PO/kasbon existing.
3. **Fase 3 — penutupan:** review diff terbatas, PHP lint/whitespace dan perbarui laporan diagnosis/checkpoint. Catat batas verifikasi; tidak save dokumen bisnis aktual.

Dependensi 1 → 2 → 3. Status akhir: seluruh fase selesai; RED sebelum fix, GREEN 138 assertions, form state Node, lint dua file PHP, review diff dan whitespace lulus. Snapshot rollback tes memakai sorting data karena tabel histori harian tidak mempunyai kolom id. Tidak ada TODO implementasi; dokumen bisnis tidak disimpan ulang. Bukti: docs/incoming-stok-diagnosis-2026-10-14.md.

4. **Publikasi Git (diotorisasi user 2026-10-14):** commit/push origin/main hanya model/tes Incoming dan tiga dokumen task. Index terpisah memuat HEAD ditambah perubahan task; plan/checkpoint hanya menyisipkan bagian Incoming agar perubahan task lain tetap lokal. Verifikasi remote SHA sebelum/sesudah push, review staged diff dan pertahankan index/working tree pekerjaan lain. Tidak mengulang tes yang kode/dependensinya identik hasil lulus.

## Modul Jurnal Expense — 2026-10-14

- Fase 1: model daftar/filter/ekspor hanya Expense Report (generator modul Expense) dan Expense Report Consultant dengan sts 0, kosong, atau NULL; grouping per no_transaksi + jenis + company.
- Fase 2: tampilan identik Jurnal Payment, preview dan posting JV ke javh/jurnal/nomorJC dengan mapping company existing (4 Vuca; 1/6/7 STM; lainnya Sustain), validasi server, deduplikasi, audit.
- Fase 3: registrasi menu tepat setelah Jurnal Payment; permission Jurnal_Expense.*, akses awal mengikuti grant ekuivalen Jurnal_Payment.*. Uji/lint/dokumentasi dan verifikasi registrasi lokal.
- Kriteria: posted/jenis lain tidak bisa dilihat atau diposting via ID; NULL ikut; source nominal tidak dari payload; satu dokumen satu aksi; gagal tulis/commit bukan sukses. Tidak melakukan posting accounting live, redesign, mengubah jurnal_payment, migrasi engine, atau publish Git tanpa permintaan task baru.
- Temuan: tujuan javh/jurnal/pastibisa_tb_cabang lokal MyISAM. Gunakan cleanup khusus nomor yang dibuat request saat gagal; advisory lock + source row lock; catat keterbatasan crash/cross-module concurrency.
- Penutupan: Fase 1–3 selesai; 167 assertions baru, regresi audit/Expense, lint/sintaks/whitespace, registrasi menu idempotent, dan query MySQL read-only PASS. Rincian: docs/jurnal-expense-2026-10-14.md. Tidak ada posting accounting live. Fase 4 selesai sesuai otorisasi user: commit 1f4338959efe405b068e7a788c1d397be7d72466 dipush hanya development memakai index terpisah; SHA remote/dependency diverifikasi, main dan pekerjaan lokal lain dipertahankan.


## Koreksi lokasi tab per transaksi — 2026-10-14

Plan user: kembalikan UI request_payment sebelum tab; pertahankan audit/backend existing. Tambah Per Transaksi (Approved) dalam record_request_payment di antara Batch (default) dan Histori Lama. Pakai endpoint snapshot record_items existing, pencarian/entries, View PGJ/Cetak Dokumen/Excel Pengajuan dengan mini-btn existing. Hapus pending-items endpoint dan return context Request yang khusus fitur keliru. Detail dari per-item kembali ke tab Record items; permission hanya Record View.

Fase 1 restore UI Request + cleanup rute/pindah tests; Fase 2 tab Record/items/navigation; Fase 3 validasi/refreshed manifests; Fase 4 scoped commit/push main dan sync tiga modul development via alternate index. Kriteria: Request source view identik baseline 406a0c5f; batch3approved=1row batch/3rows item, rejected/pending/legacy excluded; audit/permission/error/escaping/paging/print/export/navigation benar; regresi/lint/diff PASS; remote tree tiga modul identik. Tanpa migration/browser/DB live writes. Pekerjaan audit lain dan staging paralel dipertahankan.

**Status akhir:** Fase 1–4 selesai; code main d84a73f3, development 8960ab39 terkirim. Tiga tree modul identik/diff remote kosong. Request view sama persis baseline 406a0c5f; Record default Batch + Per Transaksi (Approved) + Legacy. 1.916 regresi, 131 assertions PHP fitur dan 72 checks JS PASS; lint/whitespace PASS. Tanpa browser/DB live/migration; pekerjaan lain dipertahankan.


## Task terpisah: tab Request Payment — 2026-10-14

**Publikasi selesai:** main code 27a11cf5 / development db0e9507 terkirim; seluruh tree tiga modul identik dan diff remote kosong. Commit penutupan hanya dokumentasi. Dependensi minimum ikut tersinkron; perubahan development di luar scope dan pekerjaan lokal lain dipertahankan.

**Publikasi Git (diminta user):** commit/push ke main hanya tiga modul beserta dependensi audit minimum (helper/library/autoload/bridge legacy), tes dan dokumentasi task; perubahan modul lain tetap lokal. Setelah main terkirim, samakan seluruh tree tiga modul pada development dengan main melalui commit terbatas path, termasuk dependensi minimum yang belum tersedia. Pertahankan file development di luar cakupan; tanpa force push. Verifikasi remote SHA dan kesamaan tree tiga modul sebelum selesai.

**Revisi UI tombol terbaru (selesai):** sesuai referensi user, ACTION tab Approval/Record memakai gaya `mini-btn` dari `record_request_payment/views/index.php` (putih, outline, ukuran kecil; View biru muted, Print/Excel slate). Scoped ke tabel baru; grup aksi inline-flex nowrap agar tidak bertumpuk. Fungsi/label/URL tetap; Approval hanya Print. PHP lint, 46 view assertions, 93 client checks, whitespace PASS. Tidak ada perubahan backend atau tes browser.

Rencana disetujui user: tambahkan Request Payment / Approval Request Payment / Record Request Payment dalam `request_payment/index`, tanpa mengubah styling atau komponen Request existing. Approval dan Record satu baris per dokumen; keputusan tetap per PGJ; rejected kembali ke Request dengan histori; legacy tetap di halaman Record lama. Tidak ada migrasi schema atau perubahan proses write.

**Revisi user 2026-10-14 (selesai):** ACTION pada tab Approval di Request Payment hanya Print dokumen sumber. Approve/reject dilakukan di modul Approval Request Payment terpisah. Pertahankan gaya tombol existing, data, permission, dan aksi tab Record. Tes RED sebelum revisi; GREEN client 93 checks, view 46 assertions, PHP/JS syntax serta whitespace PASS; tidak mengulang suite backend yang kode/dependensinya tidak berubah.

1. **Sumber data:** endpoint baru `approval_request_payment/get_data_pending_items` (detail + header pending non-deleted) dan `record_request_payment/get_data_record_items` (snapshot + header done non-deleted). Respons DataTables, pagination/search, permission View masing-masing, audit read aman. Selesai setelah tes status, pagination/search, query failure, permission, writer lulus.
2. **Tab:** tambah markup Bootstrap existing dan panel daftar baru; isi/CSS Request dipertahankan. Load lazy, refresh saat kembali, parameter tab allowlist. Selesai setelah render fixture, sintaks JS, dan perbandingan CSS/isi Request dengan baseline lulus.
3. **Integrasi:** tab Approval hanya Print; proses keputusan di modul terpisah. Detail Record memakai return context fixed internal; permission tombol keputusan di modul Approval sesuai `Approval_Request_Payment.Manage`; halaman lama/export tetap kompatibel. Selesai setelah navigasi/permission/regresi approval dan record lulus.
4. **Penutupan:** seluruh suite Request/Approval/Record dan fondasi audit terdampak, PHP lint, JS syntax, diff whitespace; perbarui checkpoint dan laporan. Tanpa browser, migration, DB bisnis writes, commit/push.

Dependensi berurutan 1 → 2 → 3 → 4. Task ini tidak melanjutkan atau mengubah status rollout audit Fase 2. Baseline perubahan existing disalin ke `scratch/request_payment_tabs_baseline/` untuk membedakan perubahan sesi ini.

**Status akhir 2026-10-14:** Fase 1–4 selesai. Tiga tab/per-doc/permission/navigasi terimplementasi; CSS/markup Request identik baseline. Tes task 142 + 46 + 15 assertions PHP dan 81 checks JS PASS; regresi Fase 1 1.916 skenario dan tujuh suite fondasi audit PASS; lint/diff PASS. Bukti dan keterbatasan: `docs/request-payment-tabs-2026-10-14.md`; manifest endpoint tambahan: `docs/request-payment-tabs-manifest.json`. Belum memverifikasi MySQL live/browser, tidak menjalankan migration/commit/push.

Sinkronisasi: 2026-10-11, WIB. Dokumen ini merekonstruksi rencana dari manifest, laporan implementasi, dan kode aktual; bukan klaim bahwa rencana ini sudah tersedia sebelum implementasi. Checkpoint utama: `docs/task-progress.md`.

## Tujuan dan batas cakupan

Menerapkan audit trail pada endpoint backend modul menu aktif, dengan aktor, aksi, objek, metadata terbatas, dan hasil operasi yang sesuai hasil bisnis. Kegagalan penulisan audit tidak boleh membatalkan transaksi bisnis. Pertahankan permission, respons, dan pola transaksi yang sudah ada; jangan melakukan refactoring di luar kebutuhan audit.

Manifest `docs/audits/active-menu-audit-2026-10-10.json` merupakan baseline historis: 72 controller, dengan Audit Trail viewer dikecualikan dari pencatatan dirinya sendiri dan PR Stok telah diimplementasikan secara terpisah. Jumlah endpoint fase di bawah berasal dari baseline, bukan inventaris live yang sudah diperbarui. Terdapat perbedaan hash dan metode yang perlu direkonsiliasi sebelum fase terkait dimulai.

Sumber verifikasi: kode aktual dan pengujian; checkpoint menyimpan kesimpulannya. Laporan historis dalam `docs/audits/` tetap dipertahankan. Ringkasan 10 Oktober yang menyebut 10/55 endpoint dan batch 2 sebagai pekerjaan berikutnya telah dilewati oleh implementasi batch 2 serta checkpoint 11 Oktober.

Sesi sinkronisasi dokumentasi telah selesai. Pengguna mengotorisasi Fase 1 batch 3 (10 endpoint), batch 4 (10 endpoint), serta 3 batch terakhir Fase 1 (batch 5 `Request_payment` 6 endpoint, `Approval_request_payment` 4 endpoint, dan `Record_request_payment` 5 endpoint). Seluruh **55/55 endpoint Fase 1** kini telah selesai dan terverifikasi pada 2026-10-11 WIB (`docs/audits/phase-1-request-payment-batch-5.md`). Pekerjaan berikutnya adalah **Fase 2** (`pembayaran_material`, `request_mutasi`, `pengajuan_rutin` — 51 endpoint baseline), yang belum dieksekusi dalam sesi ini.

## Kontrak implementasi dan dependensi

- Fondasi: helper `audit_trail`, `Audit_logger`, dan `Module_audit_recorder`; recorder dipakai secara opt-in, bukan autoload untuk semua controller.
- Request Payment menggunakan scope per endpoint, penyelesaian eksplisit, deduplikasi, dan fallback shutdown. Penulisan audit menggunakan koneksi terpisah agar tidak ikut rollback bisnis.
- PR Stok mempertahankan mekanisme `recordAudit` sendiri dengan metadata constructor, deduplikasi, dan fallback shutdown. Jangan menggantinya hanya untuk menyeragamkan arsitektur.
- Pertahankan tujuan dan nilai balik logging legacy; ketika scope recorder aktif, bridge legacy tidak menghasilkan audit ganda.
- Metadata mengikuti allowlist, masking, dan batas ukuran recorder. Jangan memasukkan kredensial, payload bebas, isi unggahan, atau rincian pribadi yang tidak diperlukan.
- Keberhasilan write dicatat setelah transaksi berhasil commit. Penolakan, kegagalan query/transaksi/commit, serta kegagalan sebagian yang sudah dikenali harus tercermin dalam status audit.
- Sebelum setiap batch: baca controller, model terkait, permission, dan alur transaksi; verifikasi endpoint terhadap kode aktual. Maksimal 10 endpoint public per batch controller. Metode public belum otomatis dianggap route yang dapat diakses.
- Urutan rollout fase 1–19 mengikuti rencana yang sudah tercatat. Setiap fase memerlukan fondasi fase 0 dan penyelesaian fase rollout sebelumnya. Ini urutan pengerjaan; dependensi bisnis rinci tiap modul belum semuanya dianalisis dan harus diperiksa saat fasenya aktif.

## Fase, tujuan, dan kriteria keberhasilan

Untuk fase 1–19, kriteria **K**: inventaris endpoint telah diverifikasi; semua endpoint dalam cakupan memiliki audit sesuai hasil bisnis tanpa duplikasi; metadata aman; permission/respons/transaksi tetap sesuai perilaku sebelumnya; pengujian relevan, lint, regresi, dan checkpoint selesai. Keberadaan hook legacy saja tidak memenuhi K.

| Fase | Tujuan / modul | Endpoint baseline | Status saat sinkronisasi | Dependensi / kriteria |
| --- | --- | ---: | --- | --- |
| 0 | Fondasi recorder, logger/helper, bridge legacy; viewer, auth, menu sebagai fondasi pendukung | — | Selesai pada cakupan lokal yang teruji | Scope/dedup/fallback/masking/writer serta permission viewer dan auth teruji; validasi live terpisah |
| 1 | `request_payment`, `approval_request_payment`, `record_request_payment` | 55 | **Selesai: 55/55 endpoint teruji** | Fase 0 + K; seluruh 5 batch `Request_payment` (46), `Approval_request_payment` (4), dan `Record_request_payment` (5) selesai |
| 2 | Pembayaran/mutasi: `pembayaran_material`, `request_mutasi`, `pengajuan_rutin` | 51 | **Selesai: 51/51 endpoint teruji (100% tuntas)** | Fase 1 + K; pembayaran_material 18/18, request_mutasi 21/21, pengajuan_rutin 12/12 selesai |
| 3 | Expense/petty: `expense`, `expense_petty_cash`, `petty_cash_vuca_sustain`, `petty_cash_master` | 121 | **Sedang berjalan: 70/121 endpoint (Expense Batch 1, 2, 3, 4, 5, 6, & 7 selesai)** | Fase 2 + K; Expense batch 1 (10 endpoint), batch 2 (10 endpoint), batch 3 (10 endpoint), batch 4 (10 endpoint), batch 5 (10 endpoint), batch 6 (10 endpoint), & batch 7 (10 endpoint) selesai |

| 2 | Pembayaran/mutasi: `pembayaran_material`, `request_mutasi`, `pengajuan_rutin` | 51 | **38/51 selesai: pembayaran_material 18/18, request_mutasi 20/21; 13 pending** | Fase 1 + K; batas sesi terbaru Request Mutasi batch 2; batch 3 (1) & Pengajuan Rutin (12) pending |
| 3 | Expense/petty: `expense`, `expense_petty_cash`, `petty_cash_vuca_sustain`, `petty_cash_master` | 121 | Belum rollout | Fase 2 + K |
| 4 | Invoice: `plan_tagih`, `actual_plan_tagih`, `invoicing`, `approval_invoice_so` | 58 | Belum rollout | Fase 3 + K |
| 5 | Penerimaan: `penerimaan_uang`, `penerimaan_pph_23`, `unlocated_penerimaan`, `historical_alokasi` | 18 | Belum rollout | Fase 4 + K |
| 6 | `alokasi`, `alokasi_kalibrasi` | 22 | Belum rollout; 5 metode baseline tidak ditemukan | Fase 5 + inventaris ulang + K |
| 7 | `purchase_request`, `metode_pembelian`, `ros` | 138 | Belum rollout | Fase 6 + K |
| 8 | `purchase_order`, `approval_po`, `purchase_order_payment`, `closed_po` | 117 | Belum rollout | Fase 7 + K |
| 9 | `non_rutin`, `app_pr_stock`, `pr_asset`, `asset_planning`, `closed_pr` | 88 | Belum rollout | Fase 8 + K |
| 10 | `incoming_stok`, `incoming_departemen`, `incoming_asset` | 35 | Belum rollout | Fase 9 + K |
| 11 | `outgoing_stok_atk`, `adjustment_stock`, `stok_gudang_barang`, `costbook_stock` | 28 | Belum rollout | Fase 10 + K |
| 12 | `accessories`, `accessories_category`, `unit`, `unit_packing` | 15 | Belum rollout; ada kandidat metode tambahan | Fase 11 + klasifikasi route + K |
| 13 | `price_sup_barang_stok`, `price_ref_barang_stok`, `price_sup_raw_material` | 19 | Belum rollout; 12 metode baseline tidak ditemukan | Fase 12 + inventaris ulang + K |
| 14 | `departements`, `employees`, `master_customers`, `supplier`, `asset` | 63 | Belum rollout | Fase 13 + K |
| 15 | `users`, `menus`, `permissions`, `master_bank`, `coa_expense` | 34 | Rollout penuh belum; auth dasar sudah teruji | Fase 14 + K; jangan menganggap auth mencakup seluruh Users |
| 16 | `budget_coa`, `budget_rutin`, `budget_periodik` | 40 | Belum rollout | Fase 15 + K |
| 17 | `jurnal`, `jurnal_invoicing`, `jurnal_payment`, `jurnal_payment_petty_cash`, `jurnal_penerimaan` | 35 | Belum rollout | Fase 16 + K |
| 18 | `report_actual_plan_tagih`, `report_piutang_per_invoice`, `report_petty_cash` | 13 | Belum rollout | Fase 17 + K |
| 19 | `report_jurnal_invoicing`, `report_jurnal_payment`, `report_jurnal_penerimaan` | 13 | Belum rollout | Fase 18 + K |
| 20 | Verifikasi akhir lintas modul, termasuk regresi PR Stok dan viewer | — | Belum selesai | Fase 1–19 selesai, inventaris direkonsiliasi, integrasi dan keterbatasan deployment dicatat |

PR Stok merupakan implementasi pendahulu yang sudah ada: 24 endpoint terverifikasi. Status ini tidak membuat fase 20 selesai. Viewer memiliki 4 endpoint teruji dan sengaja tidak mencatat akses dirinya sendiri.

## Rekap Penyelesaian Fase 1

Seluruh 55 endpoint baseline Fase 1 telah selesai: `Request_payment` (46/46 endpoint), `Approval_request_payment` (4/4 endpoint), dan `Record_request_payment` (5/5 endpoint). Batch 3 lulus 282 skenario (`docs/audits/phase-1-request-payment-batch-3.md`), batch 4 lulus 810 skenario (`docs/audits/phase-1-request-payment-batch-4.md`), batch 5 lulus 371 skenario, `Approval_request_payment` lulus 60 skenario, dan `Record_request_payment` lulus 57 skenario (`docs/audits/phase-1-request-payment-batch-5.md`).

| Batch Fase 1 | Controller | Endpoint |
| --- | --- | --- |
| 3, selesai | `Request_payment` | `view_jurnal`, `edit_jurnal`, `jurnal_save`, `list_return`, `list_return_approval`, `reject_approval`, `approve_pengembalian_expense`, `reject_pengembalian_expense`, `search_payment_list`, `search_req_payment` |
| 4, selesai | `Request_payment` | `change_tab`, `excel_payment_list`, `view_receive_invoice`, `save_approval_checker_consultant`, `save_approval_consultant`, `get_data_req_payment`, `get_summary_cards`, `added_pilih_data`, `save_request_payment`, `reset_choosed_req_payment` |
| 5, selesai | `Request_payment` | `copy_to_payment`, `reject_req_payment`, `download_excel_request_payment`, `print_cash`, `print_direct_payment`, `print_kasbon` |
| Batch controller terpisah, selesai | `Approval_request_payment` | `index`, `get_data_pending`, `detail`, `save_decision` |
| Batch controller terpisah, selesai | `Record_request_payment` | `index`, `get_data_record`, `get_data_record_legacy`, `detail`, `export_excel` |

Pekerjaan berikutnya adalah **Fase 2** (`pembayaran_material`, `request_mutasi`, `pengajuan_rutin` — 51 endpoint baseline). Baca ulang kode aktual sebelum memulai Fase 2.

## Validasi dan penutupan setiap batch

1. Cocokkan scope endpoint dengan manifest dan kode; klasifikasikan read/write, permission, dan hasil sukses/gagal.
2. Implementasikan hanya batch yang diotorisasi, memakai fondasi dan keputusan yang sudah terverifikasi.
3. Jalankan suite batch dan regresi fondasi, batch sebelumnya, serta PR Stok yang relevan; lakukan lint dan `git diff --check`.
4. Verifikasi status, metadata, deduplikasi, rollback/commit, dan kegagalan writer. Pengujian fixture tidak membuktikan kondisi DB live.
5. Catat file, keputusan, hasil tes, keterbatasan, dan sisa pekerjaan dalam checkpoint sebelum mengumumkan selesai atau berpindah fase.

Sebelum penutupan seluruh task, verifikasi integrasi nyata yang tersedia, inventaris akhir, permission/menu, dan regresi seluruh modul. Migration, backfill, retensi, dan deployment produksi tidak boleh dianggap sudah dilakukan dari kelulusan tes lokal.

## Ketidakpastian yang harus ditangani

- 14 controller berbeda hash dari manifest; 17 metode baseline tidak ditemukan; 6 metode public tambahan perlu diklasifikasikan. Rincian ada di checkpoint. Manifest historis tidak diubah saat sinkronisasi.
- Menu/DB live, visual browser, migration produksi, dan integrasi MariaDB tidak diuji ulang pada sesi ini. Temuan historis bukan bukti keadaan live sekarang.
- Sebagian besar modul fase 2–19 hanya diverifikasi keberadaan/inventarisnya; perilaku bisnis lengkap dan kesiapan instrumentation belum terverifikasi.
- Risiko legacy (SQL interpolation, mapping COA, concurrency ID, berkas upload setelah rollback) memerlukan evaluasi dampak pada fase terkait, bukan refactoring otomatis.
- Writer yang gagal atau proses yang dihentikan paksa tetap dapat kehilangan audit; batas schema IP/charset dan status legacy juga tetap perlu diperhatikan pada verifikasi akhir.

## Perbaikan minus penerimaan Vuca — 2026-10-12
Task ini terpisah dari rollout audit trail; status fase audit tetap dipertahankan.
1. Diagnosis: cocokkan screenshot 038/VSB/2026 dengan process_alokasi dan writer Vuca. Sukses bila jalur 0 - PPh terbukti. Selesai.
2. Regresi dan perbaikan: reproduksi controller tanpa DB/browser; gunakan saldo aktual, helper gross Vuca lama, snapshot gross konsisten, dan hilangkan gross nol saat pembuatan Vuca baru. Dependensi: diagnosis. Sukses bila saldo utuh/cicilan dan kedua pilihan PPh benar.
3. Validasi: jalankan tes fokus, regresi kalkulasi PHP/JS, lint dan diff; catat keterbatasan tanpa menyentuh data transaksi. Dependensi: implementasi.
Status 2026-10-12: seluruh tiga fase perbaikan minus penerimaan Vuca selesai melalui regresi fixture controller, kalkulasi PHP/JS, lint dan review diff. Validasi DB live dan modernisasi v2 berada di luar cakupan; rincian pada checkpoint.

## Pelunasan cicilan 189/STM/2026 — 2026-10-12
1. Diagnosis riwayat PPh dan sumber perhitungan: controller mengisi PPh penuh setiap preview; view menguranginya kembali setiap cicilan N. Audit DB read-only dicoba tetapi koneksi lokal tidak tersedia.
2. Implementasi dan regresi: hitung sisa penyesuaian PPh dari jurnal penerimaan terdahulu yang terpetakan persis ke invoice; gunakan pada preview/JS/save. Saldo dan jurnal uang dihitung server dari input penerimaan/admin, bukan hidden nominal.
3. Validasi: fixture history/controller, kalkulasi PHP/JS, lint dan diff. Jangan mengubah transaksi historis; catat keterbatasan DB dan konkurensi.
Status pelunasan 2026-10-12: fase diagnosis, perbaikan dan validasi selesai. Fixture membuktikan saldo akhir nol dan jurnal PPh tidak berulang; tes inline aktual dijalankan tanpa browser. Data DB live belum terverifikasi (koneksi gagal); tidak ada mutasi historis. Detail validasi dan TODO concurrency ada di docs/task-progress.md.

## Regresi preview tertutup karena jurnal historis kosong — 2026-10-12
1. Diagnosis aktual: akses read-only via Docker (DB hostname mysql hanya terjangkau dalam container). Invoice 00278-INV-VIII-26 / 189/STM/2026 saldo 2574990, net awal 25515810. Detail id 745, penerimaan 23409000, admin 0, sisa 2574990, pilihan N, nomor 00025/RKN-KRG/X/26; tr_jurnal untuk nomor ini kosong.
2. Perbaikan: bila seluruh jurnal transaksi kosong, rekonstruksi PPh dari rantai rincian saldo; validasi semua nominal, urutan detail, batas PPh dan saldo akhir aktual. Jurnal yang ada tetapi ambigu tetap ditolak. Jangan menulis/koreksi DB.
3. Validasi: reproduksi fixture actual history, regresi, lint, dan panggil model aktual pada snapshot DB read-only melalui adapter tanpa browser.
Status regresi pembukaan preview 2026-10-12: ketiga fase selesai. Perbaikan diverifikasi dengan fixture controller dan model aktual pada snapshot read-only DB lokal via Docker; sisa PPh0 dan saldo simulasi pelunasan0. Tidak ada perubahan data historis. Penyebab jurnal lama hilang dan backfill berada di luar scope.

## View penerimaan tidak menampilkan jurnal — 2026-10-12
1. Diagnosis kode/DB: view mencari tr_jurnal.no_transaksi dari ID invoice, tetapi save memakai no_surat penerimaan. Invoice110 punya 4 jurnal posted untuk 00046/RKN-KRG/VIII/26, debit/kredit44100000. Markup COA menaruh angka dalam atribut td.
2. Perbaikan: query berdasarkan no_surat dan jenis Penerimaan Piutang; perbaiki isi sel COA; regresi view dan pemisahan dua penerimaan invoice sama.
3. Validasi: fixture controller/render tanpa browser, lint/diff, audit DB read-only. Jangan membuat ulang/mengubah jurnal posted.
Status view jurnal 2026-10-12: semua fase selesai. Query nomor penerimaan dan rendering COA teruji; empat jurnal posted invoice110 diverifikasi read-only. Tidak ada mutasi database. Perubahan baru belum dipush.

## Download Format Excel dan Impor Massal Asset — 2026-10-12
Scope: template XLSX tiga sheet, preview tervalidasi, penyimpanan batch seluruhnya atau batal; tanpa saldo awal, jurnal, edit asset, push/deploy.
1. Validasi dan workbook: master bernama, dropdown, petunjuk, 5 MB/500 baris/1000 unit, tanggal/nominal/Qty/formula. Sukses: roundtrip XLSX PCLZip dan fixture valid/invalid lulus.
2. Persistence: migration preflight unique kd_asset dan batch token; preview server user/cabang TTL30 menit; transaksi/chunk jadwal/lock cabang/idempotensi. Sukses: DB uji terpisah membuktikan rollback, retry dan konkurensi.
3. Integrasi: endpoint Assets.Add, download/import modal/preview dengan token saja saat simpan. Sukses: permission, master berubah, kontrak JSON, PHP/JS lint dan regresi tambah manual.
4. Review dan dokumentasi rollout: migration harus terpasang sebelum fitur aktif; tidak koreksi duplikasi otomatis. Catat batas pengujian, risiko, dan instruksi migration.
Keputusan: Qty default1 (1–99), nominal per unit rupiah utuh, manfaat1–20; department/costcenter opsional; warehouse desc=costcenter sesuai form existing; master divalidasi lagi saat save.

### Hasil implementasi asset — 2026-10-12
Fase1–4 selesai. Workbook, validator, persistence/transaksi, endpoint dan UI terintegrasi; migration additive telah diterapkan pada Docker lokal db_sendigs_ss. Readiness true,93asset sebelum/sesudah dengan total identik,0batch; tidak membuat/mengubah asset existing. Pengujian DB hanya membuat data pada database disposable (termasuk240000 jadwal), dibersihkan sesudah tes. Push GitHub/deployment lingkungan lain belum dilakukan, sesuai batas tahap ini.

## Penyesuaian Asset: keluarkan Cost Center — 2026-10-12
Scope: hapus pilihan Cost Center dari Tambah/Edit/View Asset serta template dan preview impor. Fase1: telusuri field, AJAX, dan DB; tentukan data historis tetap tersimpan saat edit. Fase2: ubah UI dan penulisan manual/impor, perbarui workbook 8 kolom. Fase3: regresi form, XLSX roundtrip, preview dan transaksi. Kriteria: user tidak melihat/mengisi Cost Center; asset baru menyimpan nilai kosong; edit asset lama tidak menghapus nilai historis; import tetap valid. Template lama 9 kolom perlu diunduh ulang karena header berubah.

## UI index Pelaporan Petty Cash — 2026-10-13
Scope: rapikan filter, tabel, badge status dan card kecil alasan reject; pertahankan endpoint/permission/alur detail-cetak-submit.
1. Analisis: gunakan gaya SENDIGS existing dan skill antislop-ui; alasan tetap escaped dan hanya pada reject.
2. Implementasi: CSS scoped halaman, toolbar filter fleksibel, tabel dengan lebar status terkendali, angka rata kanan, aksi berlabel, alasan dalam kotak kecil.
3. Validasi: PHP/JS syntax, renderer kondisi status/escaping/aksi, diff check; tanpa browser sesuai instruksi proyek.
Kriteria: alasan panjang membungkus tanpa memperbesar kolom status tanpa batas; filter dan aksi existing tetap bekerja; CSS tidak memengaruhi modul lain.

Status UI index Pelaporan Petty Cash: fase1–3 selesai. PHP/JS syntax, renderer/card/escaping/permission/URL/filter serta diff check lulus; stylesheet scoped dan responsive diperiksa statis. Tanpa browser/database runtime; belum commit/push/deploy.


## Multi-attachment kasbon PR Department — 2026-10-13
1. Dokumentasi + migration doc_file ke TEXT nullable, sebelum deployment.
2. Helper normalisasi, salin semua file PR dari DB, error eksplisit, edit mempertahankan lampiran.
3. Integrasi form/detail/approval/cetak + daftar attachment endpoint + fallback baca PR untuk data lama.
4. Regression standalone, PHP lint, JS syntax, diff check; tanpa browser/DB produksi.
Kriteria: seluruh lampiran tersalin ketika file pertama ada, setiap file dapat dibuka, data lama/satu file didukung, input path tidak dipercaya, perubahan lain dipertahankan.

Status multi-attachment kasbon: fase1–4 implementasi selesai dan validasi terfokus lulus. Tambahan minimum: guard kapasitas varchar untuk mencegah truncation sebelum migration, error tanpa auto-dismiss, dan guard response PR stale. Migration005 disiapkan, belum diterapkan. Tes Reject existing gagal identik terhadap HEAD; tidak diperbaiki di scope ini. Tidak commit/push/deploy.

Pengiriman kasbon: commit26440d6d berhasil push origin/main; migration005 tetap perlu diterapkan sebelum deployment. Dokumentasi gabungan tetap lokal.

## Fase 2 Audit Trail — execution 2026-10-13
User meminta melanjutkan seluruh Fase 2: pembayaran_material18, request_mutasi21, pengajuan_rutin12 (51 endpoint baseline). Urutan dan batas10 endpoint/controller tetap. Grounding, checklist, implementasi, fixture/lint, checkpoint per batch wajib. Batch aktif: pembayaran_material batch1; laporan docs/audits/phase-2-pembayaran-material-batch-1.md. Fondasi dan hasil Fase1 dipertahankan; tanpa schema/permission baru atau DB bisnis writes.

## Fase 2 Request Mutasi batch 1 — 2026-10-13
User mengotorisasi hanya10endpoint batch1 request_mutasi. Grounding/controller/model/transaksi lintas schema, checklist docs/audits/phase-2-request-mutasi-batch-1.md, fixture RED/GREEN, regresi/lint dan checkpoint. Batch2/3 serta pengajuan_rutin belum diotorisasi sesi ini. Status awal18/51; target28/51 setelah verifikasi. Permission/routing/jurnal existing dipertahankan. Kegagalan baseline JournalRoutingTest (counter simulator reset vs uniqueness lintas iterasi) didiagnosis sebelum kode berubah; tidak diperbaiki dalam scope.

### Penutupan Request Mutasi batch 1 — 2026-10-13
Sepuluh endpoint selesai dan terverifikasi: Fase2 28/51, RequestMutasi10/21. Batas sesi batch1 dipatuhi; batch2/3 dan PengajuanRutin pending. Suite baru394skenario+2shutdownPASS,17auditregressionPASS,5legacyMutasiPASS,lint4/diffPASS. Satu JournalRouting simulator failure identik baseline tetap dicatat, bukan disembunyikan. Detail phase-2-request-mutasi-batch-1.md; checkpoint utama task-progress.md. Berikutnya RequestMutasi batch2 hanya jika diminta.

### Keputusan efisiensi batch — 2026-10-13

Aturan pengerjaan berikutnya mengikuti bagian **Efisiensi Pengerjaan dan Pengujian per Batch** di `AGENTS.md`, ditambahkan atas permintaan user. Pengujian per batch dipilih berdasarkan endpoint aktif dan dependensi terdampak; seluruh suite fase dijalankan saat penutupan, dan suite audit yang terdampak saat fondasi/helper/kontrak berubah. Grounding, cabang berbeda, transaksi/commit, keamanan metadata, deduplikasi, kegagalan writer, dan checkpoint tetap wajib. Tidak mengubah cakupan atau status endpoint: Fase 2 tetap 28/51; Request Mutasi batch 2 berikutnya jika diminta. Bukti dan batas pengujian dicatat di `docs/task-progress.md` dan laporan batch, tanpa mengulang uraian panjang di semua dokumen.

## Fase 2 Request Mutasi batch 2 — 2026-10-13

User mengotorisasi implementasi Fase 2 Request Mutasi batch 2 (10 endpoint: `reject_mutasi`, `index_transaksi`, `create_transaksi`, `save_transaksi`, `save_jurnal_BUM`, `save_jurnal_BUK`, `printout`, `printout_mutasi`, `printout_transaksi`, `terbilang`). Grounding alur transaksi, pencegahan duplicate audit saat delegasi internal (`save_transaksi` -> `save_jurnal_BUK`/`save_jurnal_BUM`), penanganan input AJAX, parameter binding generator bukti, checklist `docs/audits/phase-2-request-mutasi-batch-2.md`, dan pembuatan test suite `Batch2_audit_test.php`.

### Penutupan Request Mutasi batch 2 — 2026-10-13

Sepuluh endpoint selesai dan terverifikasi penuh: Fase 2 **38/51 selesai**, `pembayaran_material` **18/18**, `request_mutasi` **20/21**; sisa 13 endpoint pending (1 `Request_mutasi` [`getMataUangByKode`], 12 `Pengajuan_rutin`). Suite baru `Batch2_audit_test.php` lulus 276 skenario + 2 shutdown subprocess; suite `Batch1_audit_test.php` lulus 392 skenario + 2 shutdown subprocess; seluruh 24 test suite audit dan legacy mutasi lulus (PASS). PHP lint 0 kesalahan dan `git diff --check` lulus tanpa whitespace issues. Checkpoint utama: `docs/task-progress.md`; laporan detail: `docs/audits/phase-2-request-mutasi-batch-2.md`.

## Fase 2 Request Mutasi batch 3 — 2026-10-14

User mengotorisasi implementasi Fase 2 Request Mutasi batch 3 (1 endpoint: `getMataUangByKode`). Grounding endpoint baca tabel `mata_uang`, penanganan conditional audit scope guard `$startedAudit = $this->beginAudit(...)` untuk mencegah duplikasi log saat dipanggil internal oleh `terbilang()`, allowlist metadata mata uang, laporan `docs/audits/phase-2-request-mutasi-batch-3.md`, dan pembuatan test suite `Batch3_audit_test.php`.

### Penutupan Request Mutasi batch 3 — 2026-10-14

Satu endpoint penutup selesai dan terverifikasi penuh: Modul `Request_mutasi` **21/21 endpoint (100% tuntas)**. Fase 2 **39/51 selesai**, `pembayaran_material` **18/18**, `request_mutasi` **21/21**; sisa 12 endpoint pending (seluruhnya di modul `pengajuan_rutin`). Suite baru `Batch3_audit_test.php` lulus 10 skenario + 1 shutdown subprocess; `Batch1_audit_test.php` lulus 391 skenario + 2 shutdown subprocesses; `Batch2_audit_test.php` lulus 275 skenario + 2 shutdown subprocesses; seluruh 5 suite legacy mutasi lulus (PASS). PHP lint 0 kesalahan dan `git diff --check` lulus tanpa whitespace issues. Checkpoint utama: `docs/task-progress.md`; laporan detail: `docs/audits/phase-2-request-mutasi-batch-3.md`.

## Record Request Payment: tombol Excel per transaksi — 2026-10-14

1. Grounding referensi dan query daftar approved (selesai): snapshot done/non-deleted, pencarian tabel, ekspor existing hanya satu PGJ.
2. Implementasi (selesai): tombol Cetak ke Excel pada toolbar, hapus Excel Pengajuan per baris, endpoint XLSX dengan query/filter yang sama tanpa pagination; permission dan audit setelah file terbentuk.
3. Validasi dan checkpoint (selesai): client RED/GREEN, workbook nyata, failure paths, regresi Record/fondasi audit, lint dan whitespace. Rincian/batas: docs/record-request-payment-excel-2026-10-14.md; endpoint: docs/request-payment-tabs-manifest.json.

Kriteria: semua hasil pencarian approved diekspor, nominal snapshot konsisten, View/Cetak Dokumen tetap berfungsi, teks aman dari formula injection, audit tidak duplikat/berisi data sensitif, kegagalan writer tidak mencatat sukses. Tidak ada perubahan modul lain atau kebutuhan migration. Tidak browser/DB live.
# Jurnal dan Report Jurnal gabungan — 2026-10-15

**Status:** fase1–4 selesai implementasi dan validasi lokal;474 assertions fitur, regresi sumber/audit, lint dan migration MariaDB terisolasi PASS. SQL database aplikasi belum diterapkan; lihat docs/jurnal-all-2026-10-15.md dan checkpoint.

Rencana disetujui: `/jurnal_all` dan `/report_jurnal_all`, lima tab sesuai Jurnal ALL.html; Refill/Payment Hutang Petty Cash tampil di Pembayaran tetapi memakai handler petty cash. Expense Report hanya di Expense. Semua kategori Payment existing dipertahankan. Modal/revisi mengikuti sumber. Permission baru View/Manage dan Report View hanya admin awal; tanpa copy grant. Metadata unavailable ditampilkan —, tanpa schema metadata baru. Modul `/jurnal` dan Buku Besar Petty Cash tetap.

1. Kontrak/baseline: manifest jenis/status/grouping/handler dan fixture sebelum implementasi. Selesai setelah baseline dan RED terverifikasi.
2. Jurnal gabungan: query/filter/ringkasan/Excel/UI dan adapter HMVC berurutan INV→RCV→PAY→EXP→PCS; validasi server, serialisasi aksi, audit tunggal, pemeriksaan commit; mapping/nomor/urutan accounting existing tetap.
3. Report gabungan: klasifikasi identik, histori sts=1, detail read-only, Excel detail seluruh hasil filter dan total presisi cents.
4. Integrasi: migration menu idempotent + rollback, redirect lima index dan empat report; regresi sumber/adapter/audit/permission/commit, lint PHP, JS dan whitespace. Tidak browser, posting live, commit/push, migrasi engine atau perubahan task lain.

Checkpoint `docs/task-progress.md`; bukti/kontrak `docs/jurnal-all-manifest.json` dan `docs/jurnal-all-2026-10-15.md`. Perubahan guard/commit sumber dibatasi kebutuhan integrasi; risiko transaksi lintas DB/MyISAM tetap didokumentasikan.
# Indikator loading tab Jurnal — 2026-10-15

Selesai satu batch: shared workspace Jurnal/Report menampilkan overlay spinner+status langsung sebelum fetch, menutup aksi data lama dan menonaktifkan filter/paging/export sementara. Hanya request tab terkini dapat membersihkan loading; error mengembalikan kontrol retry. CSS scoped, reduced-motion dan asset version sesuai filemtime. Validasi client28 (termasuk RED sebelum fix), controller74, PHP lint view/JS syntax dan whitespace. Tidak mengubah backend/posting atau memerlukan database/browser.


## Pemulihan conflict development - 2026-10-19

Fase 1 selesai: fetch/pull fast-forward b127c8e1 ke 2110bbb4; konflik sudah ter-commit di remote. Semua pekerjaan lokal diamankan pada stash sebelum pull.
Fase 2 aktif: resolusi blok berdasarkan fitur beserta pemeriksaan sintaks dan regresi. Pertahankan guard permission/POST dan pemeriksaan commit jurnal petty cash. Fitur consulting v3, lampiran kasbon, export toolbar dan petty cash baru dipertahankan. Gabungkan riwayat dokumentasi.
Fase 3 pending: verifikasi perubahan, checkpoint, dan pemulihan pekerjaan lokal dari stash tanpa menghapus cadangan; tanpa browser/DB/push.

# Invoice konsultasi reguler versi 3 — 2026-10-18

Rencana diotorisasi user: daftar per TOP, satu TOP satu invoice progress; PKP/WAPU/Kawasan Berikat dipilih pada invoice; presisi sen half-up; PPh aktual saat penerimaan; konfigurasi mapping admin; invoice historis, Vuca dan Non Konsultasi tidak dikonversi. Referensi Excel/HTML Invoice Consulting. Pembalikan bukti setor WAPU terpisah. Tanpa commit/push/deploy atau transaksi DB aplikasi.

1. Baseline dan schema: verifikasi endpoint aktif, fixture/RED; migration aditif idempotent, versi/snapshot invoice, mapping company dan sembilan role COA, rollback guard; kriteria: histori tidak berubah dan kontrak baru teruji.
2. Mapping admin: permission Invoicing.Configure admin awal, master COA berdasarkan owner accounting; validasi lengkap, fingerprint, transaksi/audit; kriteria: mapping salah/placeholder ditolak dan perubahan tidak memengaruhi snapshot.
3. Invoice reguler: tambah/edit/detail/cetak/Excel, kalkulasi sen dan tiga jurnal, snapshot company/COA, generator nomor terkunci, TOP unik, posted/receipt/stale guards, server-authoritative, audit setelah commit. Kriteria: tiga contoh Excel dan gagal/commit/duplikasi lulus pada endpoint aktif.
4. Penerimaan dan integrasi: branch per versi per invoice, PPh aktual, piutang cash+admin+PPh, snapshot rincian untuk rollback, batch campuran, posting memakai owner snapshot, laporan konsisten. Kriteria: cicilan/rollback/legacy dan routing jurnal lulus.
5. Penutupan: regresi sumber/audit sesuai dampak, lint/syntax/whitespace, laporan migration dan prasyarat rilis, checkpoint akhir. Tidak browser; DB disposable bila tersedia, keterbatasan dicatat faktual.

### Penutupan invoice konsultasi reguler v3 — 2026-10-18

Fase1–5 selesai terverifikasi lokal. Snapshot, mapping admin, invoice/jurnal, penerimaan/cicilan/mixed/rollback, posting atomic, report/cetak/Excel dan pengaman integrasi legacy telah diimplementasikan. Guard PPh23 legacy mencegah pengakuan kedua (dependensi tambahan fase4, tanpa fitur bukti baru). Bukti kalkulasi72/parity35/native303, regresi sumber/Jurnal All/audit/report serta lint/syntax/whitespace PASS. Kontrak, runtime inventory/hash, prasyarat schema/mapping/InnoDB/same-server: docs/invoicing-consulting-v3-2026-10-18.md dan docs/invoicing-consulting-manifest.json. Tidak mengubah DB aplikasi atau melakukan commit/push/deploy. Checkpoint utama task-progress.md.

### Prasyarat posting konsultasi lokal STM/Sustain/Vuca — 2026-10-18

Scope: persiapan DB lokal atas permintaan user agar invoice baru dapat diposting; tidak menerbitkan/memposting transaksi aplikasi, tidak mengubah mapping pilihan user atau konfigurasi produksi.

1. Baseline: grounding Consulting_posting invoice/receipt dan engine finance dependencies; identitas server, privileges, branch101/counter, tipe/kolom GL, mapping/permission. Temuan: finance tr_kartu_piutang serta empat tabel GL per schema MyISAM; jurnal.debet/kredit BIGINT tidak menyimpan sen. Finance dependencies lain InnoDB. Ketiga accounting mempunyai branch101.
2. Backup dan perbaikan: backup skema+data semua tabel terdampak di luar webroot sebelum ALTER; konversi hanya finance tr_kartu_piutang dan accounting javh/jarh/jurnal/pastibisa_tb_cabang ke InnoDB. Lebarkan jurnal debit/kredit menjadi DECIMAL(22,2) agar mencakup rentang BIGINT lama serta sen. Audit panjang kolom payload untuk mencegah truncation nama customer/company; perluas hanya yang diperlukan. Tidak mengubah saldo/counter/data historis, tidak mengganti SQL mode global, dan tidak konversi coa_master yang hanya dibaca oleh posting.
3. Verifikasi: fingerprint data/counter sebelum-sesudah (normalisasi representasi angka setelah BIGINT→DECIMAL tanpa mengubah nilai), cek engine/type/server/cross-schema access/cabang/permission/master, jalankan posting invoice dan receipt PKP/WAPU/KB di database disposable dengan skema aktual hasil migrasi dan rollback kegagalan. Tidak melakukan tes tulis ke tabel transaksi aplikasi.
4. Penutupan: backup Windows checksum, hasil dan batas verifikasi dalam laporan serta checkpoint, perbarui status prasyarat manifest. Tidak commit/push/browser/remote deployment.

Penutupan prasyarat posting lokal: fase1–4 selesai; 13tabel InnoDB, presisi GL dua desimal dan kapasitas nama diperbaiki; nilai historis/counter terverifikasi tetap; same-server/branch/privilege/dependency lulus. Pengujian kode posting aktif dengan skema aktual disposable159 checks PASS, termasuk failure rollback untuk3ledger. Rincian/batas/backup: docs/invoicing-local-posting-2026-10-18.md. Tidak mengubah runtime, mapping user, atau membuat transaksi percobaan di aplikasi. Mapping source4Vuca masih perlu diisi user bila diperlukan.

### Perbaikan UI invoicing — 2026-10-18

Skill antislop-ui dengan core, mode during dipilih user untuk sesi ini. Design Read: dokumen kerja invoice SENDIGS, tujuan utama memindai status tagihan dan memeriksa invoice/jurnal sebelum simpan. ENERGY2/RHYTHM2/MOTION1. Identitas mengikuti DESIGN.md: workspace terang, tipografi aplikasi existing, aksen biru #2876a3/#1a78b1 yang sudah digunakan workspace finance; angka tabular rata kanan menjadi motif dokumen keuangan, bukan dekorasi. Ritme berupa header/toolbar padat, tabel lebar, lalu kelompok form lapang dengan ringkasan nominal. Tidak gambar/ilustrasi/statistik palsu atau perubahan shell/global CSS.

1. Daftar: scoped stylesheet invoicing, header/aksi, tab dengan keyboard/aria, toolbar tahun/status, tabel overflow terarah, state loading/kosong/error DataTables; pertahankan endpoint/kolom/filter/permission dan satu TOP per baris.
2. Form/detail konsultasi versi3 dan pengaturan COA: tata ulang identitas/dokumen/TOP/ringkasan/jurnal, penjelasan piutang sesuai PPN, state preview/simpan dan grouping mapping; pertahankan nama field, fingerprint, endpoint, Select2 searchable, escaping serta formula.
3. Validasi dan penutupan: PHP lint, syntax inline/external JS, render template add/edit/detail/error/settings/list dengan fixture tanpa DB, tes client state bila logic berubah, kalkulasi/parity dan regresi relevan, scoped whitespace/manifest hashes serta checklist antislop. Tidak tes browser sesuai instruksi user; keterbatasan visual/keyboard runtime dicatat. Tidak DB mutation/commit/push. Cakupan utama index, consulting_invoice dan consulting_settings; cetak resmi serta form khusus Vuca/non/history mempertahankan tampilan existing.

Penutupan UI invoicing: fase1–3 selesai. Daftar/form konsultasi v3/detail/settings memakai workspace scoped; preview reset dan state submit ditangani. Render203/client38+28/calculation72/parity35, PHP/JS lint, CSS scoping+contrast9pairs dan whitespace PASS. Inventaris/hash runtime baru diperbarui; dependencies lain tetap. Delivery Gate dan batas non-browser: docs/invoicing-ui-2026-10-18.md. Tidak DB/backend/commit/push atau perubahan cetak resmi/jalur khusus.

### Validasi company pemilik jurnal: 2026-10-18

Scope: label Company SPK, daftar owner hanya ID7STM/3Sustain/4Vuca, validasi GET/POST dan pembacaan mapping untuk invoice baru. Company SPK tetap seluruh master termasuk gabungan. Routing snapshot historis accounting_group tidak diubah. Mapping lama noncanonical tidak dikonversi otomatis; halaman meminta owner valid sebelum simpan ulang. Urutan: policy/controller/view, tests policy+render+native disposable, dokumentasi/hash. Tidak perubahan DB aplikasi atau push otomatis.

Penutupan policy owner: runtime/view/test selesai, native341+render208+client38/28 dan lint5 PASS. GET/POST/new issuance memakai allowlist7/3/4; mapping lama diminta dipilih ulang, snapshot histori tidak diubah. Laporan: docs/invoicing-journal-owner-policy-2026-10-18.md. Tidak DB aplikasi/browser/commit/push.

### Pencarian sidebar global: 2026-10-18

Implementasikan rencana user: input Cari menu di atas Dashboard pada header admin aktif, aset CSS scoped dan script footer sesudah AdminLTE. Live filter50ms nama menu case-insensitive/normalisasi spasi; cocok grup menampilkan cabangnya, cocok child membuka ancestor. Search hanya DOM server berpermission, tanpa AJAX/highlight/HTML rewrite. Snapshot display/menu-open saat mulai search lalu restore saat clear/Escape; active/href/icon tetap. Sidebar mini memakai tombol toggle existing lalu fokus. Hidden hingga inisialisasi JS agar navigasi existing tetap berfungsi tanpa script. Fase1 runtime, fase2 render/client/CSS/kontras/lint, fase3 checkpoint; tanpa browser/DB/commit/push, pertahankan owner-policy dan task lain.
Design Read antislop during sesi existing: kontrol utilitas sidebar current AdminLTE, ENERGY1/RHYTHM1/MOTION1, dark #1e282c putih/placeholder#c2cbd2, fokus biru. Tanpa mengaktifkan executive-navigation/navigation-shell lain yang belum terhubung.

Penutupan sidebar search: fase1–3 selesai, client55/render60/lint3/JSsyntax/CSS16blocks+6contrastpairs dan whitespace PASS. Empat runtime scoped dan dua tests; laporan/manifest sidebar-search menyimpan detail. Tidak browser/DB/commit/push, seluruh hash invoicing tetap. Tidak TODO implementasi.

### Tabel konsultasi compact: 2026-10-18

Scope screenshot tab Konsultasi: 10→7 kolom, No/Dokumen(Invoice+SPK)/Customer dan Project/Company/Tim(Project Leader+Sales)/Status/Aksi. Semua teks tetap lengkap, satu TOP perbaris, ikon+warna aksi existing dipertahankan. Render grouped values escaped, tidak mengganti API/query/Excel/filter/pagination. Backend get_data_spk memakai order sts/id tetap dan mengabaikan order DataTables; hilangkan affordance sorting client yang tidak didukung, reset saved state10kolom via count guard. Tabel nonkons10kolom tidak diubah. Fase1 renderer/CSS/view, fase2 client+render/lint/checkpoint. Tidak browser/DB/commit/push untuk compact; permintaan push sebelumnya hanya sidebar+owner sudah dipenuhi commitb127c8e1 dan remotehash diverifikasi.

Penutupan compact: fase1–2 selesai, grouped7kolom lengkap dan escaped, nonkons10tetap; render208/list45/form38 dan lint/CSS/whitespace PASS. Detail laporan compact-table. Push dua fitur sebelumnya b127c8e1 selesai; compact lokal belumdipush, tanpa backend/DB/browser.

### Notifikasi simpan invoicing — 2026-10-18

1. Grounding form aktif: invoice konsultasi v3 add/edit dan COA settings belum SweetAlert2; enam form legacy/Vuca/nonkons sudah memakainya. Pertahankan endpoint, status/msg, dan aturan server.
2. Gunakan SweetAlert2 v11.23.0 lokal yang sudah tersedia dengan helper bersama: loading terkunci, sukses hanya status1 lalu navigasi setelah OK, error teks aman dan fallback inline. Cegah submit ganda; tanpa konfirmasi tambahan sebelum simpan.
3. Uji lifecycle request/modal, render add/edit/view/settings, syntax PHP/JS dan whitespace; perbarui manifest/checkpoint. Tidak browser, DB, commit/push/deploy. Perubahan compact table tetap dipertahankan.


### Penutupan pemulihan conflict development - 2026-10-19

Fase 1-3 selesai lokal: 53 file/160 blok diselesaikan, kerja lama dipulihkan dengan cadangan stash tetap tersedia, 31 suite dan lint57PHP/7JS PASS. Rincian keputusan/daftar file/hasil: docs/conflict-recovery-2026-10-19.md dan manifest pendamping. Tidak commit/push/deploy/DB/browser. Publikasi dan pemeriksaan merge server terpisah dari resolusi lokal ini.

### Perbaikan runtime jurnal_all setelah pull - 2026-10-19

1. Grounding: log lokal menunjukkan Unknown database db_consultant_new_dev; konstanta database dari remote bersufiks _dev, tetapi koneksi lokal memakai nama tanpa sufiks. Cocokkan seluruh alias database dengan konfigurasi aktif, tanpa mengubah kredensial/schema/data.
2. Pulihkan nilai konstanta lokal sesuai konfigurasi sebelum pull. Verifikasi kesesuaian alias dan koneksi serta jalankan listing/options/summary model dengan transaksi READ ONLY di container.
3. Jalankan regresi query/controller/client jurnal gabungan, lint dan whitespace; tutup checkpoint dengan hasil dan batas lingkungan. Tidak browser, posting, migrasi, commit/push/deploy.


Penutupan runtime jurnal_all: diagnosis dan validasi selesai. Konstanta lokal ditemukan sudah dikoreksi saat pemeriksaan lanjutan; pertahankan nama database sesuai koneksi aktif. Runtime READ ONLY memverifikasi options/listing5tab + summary5, semua9alias/7database, dan315assertions regresi PASS. Laporan jurnal-all-runtime-config-fix-2026-10-19.md menyimpan bukti dan batas. Tanpa browser/DB write/commit/push/deploy.


### Publikasi konflik dengan pengecualian - 2026-10-19

User mengotorisasi commit/push development, mengecualikan config.php, constants.php dan seluruh modul invoicing. Kandidat diambil dari snapshot resolusi sebelum pemulihan kerja lokal; tidak menyertakan pekerjaan lokal lama atau aset invoicing. Fase1 pemilahan selesai; fase2 validasi snapshot commit aktif; fase3 commit/push dan verifikasi SHA pending. Kandidat46filekode+2dokumen+1fixturependukung; laporan publikasi akan ditambahkan. Snapshot/hashes lokal tetap untuk memastikan tidak ada file pengguna tertimpa. Tidak browser/DB/deploy.


### Publikasi konflik development: selesai 2026-10-19

- Commit/push sukses: 2d4ff08b8f14e6ef66bcd6d952bcfa5ebf78c3fd (`fix: resolve committed merge conflicts outside invoicing`); ls-remote origin refs/heads/development dan tracking origin/development sama dengan HEAD. Branch main tidak ditarget; tanpa force.
- Tepat50file:46resolusi kode/aset,2dokumen gabungan,1fixture native posting,1laporan publikasi. Snapshot sebelum pemulihan kerja lokal dipakai supaya pekerjaan lokal lama tidak terbawa; seluruh hash file lokal lama tetap identik selain append dokumentasi task.
- EXCLUDED terverifikasi dari diff commit: semua config.php/constants.php, seluruh application/modules/invoicing, aset/manifest/dokumentasi khusus invoicing. Isi lokal tetap ada; index kosong setelah commit.
- Validasi kandidat commit terpisah:44syntax PASS,18/19suite PASS; legacy_vuca_preview_test FAIL karena controller invoicing di HEADremote masih marker dan user mengecualikannya. Tidak mengubah/skip assertion. Kelima file marker remote yang dikecualikan tetap ada (config.php dan4invoicing); bukan klaim resolusi seluruh repository.
- Fase pemilahan/validasi/commit/push/verifikasi selesai; tidak ada publikasi aktif. Bukti docs/conflict-publication-2026-10-19.md dan scratch/conflict-publication-20261019. Tidak browser/DB/deploy. Sisa: perbaikan invoicing/config menunggu otorisasi terpisah; kondisi merge conflict yang masih menggantung di server harus diperiksa sebelum pull ulang.

### Publikasi modul invoicing ke branch development — 2026-10-19

1. **Scope & Otorisasi**:
   - User mengotorisasi commit dan push seluruh perubahan modul invoicing beserta aset dan dokumentasinya ke GitHub branch `development`.
   - Mengatasi sisa marker konflik merge yang ada di remote pada controller, model, dan views invoicing.
   - Pengecualian eksplisit tetap berlaku: `application/config/constants.php` dan modul audit di luar invoicing tetap lokal.
2. **Validasi & Rilis**:
   - Pembersihan marker merge conflict pada seluruh file invoicing lokal terverifikasi (0 sisa marker).
   - Pengujian kalkulasi, UI render, client scripts, SweetAlert2 feedback, dan integration test lulus 100% (927 assertions PASS).
   - Regression test penerimaan legacy (`legacy_vuca_preview_test.php`) kini lulus sepenuhnya berkat resolusi controller invoicing.
   - Pemeriksaan sintaks PHP (34 file) dan JS (10 file) 100% PASS.
   - Pembaruan `docs/invoicing-consulting-manifest.json` mencatat SHA256 terbaru dan status terpublikasi.
3. **Commit & Push**:
   - Hanya file modul invoicing, aset terkait, dan dokumentasi yang di-stage dan di-commit.
   - Push ke `origin/development` dan verifikasi SHA remote.

# Publikasi pembatasan kelompok List Request Payment - 2026-10-20

- Validasi kelompok pada pilih, Proses, form dan simpan; kasbon/expense membedakan SQL NULL persis, string kosong bukan NULL. Checkbox kelompok berbeda disabled; clear/uncheck terakhir melepas kelompok.
- Metadata batch berdasarkan payment_approve.tipe dan no_doc ke tr_kasbon/tr_expense. Lock users.id_user menserialisasi perubahan pilihan/reset; validasi simpan dilakukan dalam transaksi sebelum upload dan penulisan bisnis.
- Publikasi dipisahkan dari pekerjaan audit lokal lain: kandidat dibangun dari dasar development/main masing-masing, hanya endpoint fitur dan helper audit yang diperlukan. Tidak merge antarbranch, migrasi atau perubahan konfigurasi.
- Pengujian kandidat: Selection_test.php (98 skenario) dan Selection_client_test.js (27 pemeriksaan), lint PHP/JS dan whitespace; tanpa browser/database operasional. Detail ada pada checkpoint task ini.

# Detail barang index Outgoing ATK Household - 2026-10-20

- Fase 1: tes RED lalu query header/count/search barang dengan EXISTS, whitelist sorting/pagination dan satu query batch detail halaman aktif. Sumber warehouse_adjustment_detail.qty_order, accessories.stock_name/id_stock dan ms_satuan.code via id_unit; transaksi tetap satu baris.
- Fase 2 (bergantung fase 1): kolom Detail Barang setelah Department, daftar nama - qty dua desimal - satuan dengan escaping dan fallback. Pertahankan Qty Unit total dan tombol detail; perbarui indeks DataTables, detail non-sortable, teks membungkus.
- Fase 3 (bergantung fase 2): tes PHP/Node.js untuk multi-item, pencarian nama/kode tanpa duplikasi, seluruh detail saat satu item cocok, pagination/count/sort, master hilang, XSS, render dan jumlah query batch. Lint/whitespace; tanpa browser atau perubahan database operasional.
- Batas: model/view outgoing_stok_atk dan tes/dokumentasi terkait; tidak mengubah stok, penyimpanan transaksi atau modul lain. Tidak migrasi/commit/push. Checkpoint utama docs/task-progress.md.
- Status akhir 2026-10-20: fase 1-3 selesai, 100 pemeriksaan PHP/Node PASS; daftar file, keputusan dan batas pengujian tercatat pada checkpoint task ini di docs/task-progress.md.
# Keterangan transaksi pada modal detail Outgoing - 2026-10-20

- Tambahkan keterangan utama dari warehouse_adjustment.note di bawah PIC pada modal_request_edit.php; pertahankan keterangan per barang sebagai kolom terpisah. Escape HTML, pertahankan baris baru, tampilkan '-' jika kosong/NULL/field historis belum tersedia.
- Tes render PHP untuk note utama, baris baru, HTML, nol literal, data historis dan keterangan per barang; lint dan whitespace. Tidak mengubah controller, query, penyimpanan, stok atau index. Tanpa browser/commit/push.
- Status keterangan modal: selesai; 11 tes detail dan 15 pemeriksaan regresi render/client PASS. Rincian ada di checkpoint task terkait.

# Publikasi perubahan Outgoing - validasi kandidat 2026-10-20

- Commit ini mencakup detail barang pada index dan keterangan transaksi pada modal Detail Request, beserta empat file tes dan dokumentasi. Tepat sembilan path; tidak mengikutkan perubahan lokal modul lain, konfigurasi atau database.
- Kandidat dibuat dari dasar masing-masing branch development/main, tanpa merge antarbranch. Masing-masing lulus 111 pemeriksaan (85 query, 7 render index, 11 render detail, 8 client), lint PHP/JS dan whitespace sebelum commit.
- Tanpa browser atau database operasional; SQL diuji SQLite in-memory dan client melalui Node VM. Publikasi memakai push atomik tanpa force dan verifikasi SHA remote; hasil akhirnya dicatat pada checkpoint workspace.

# Perbaikan UI Add Invoice Vuca - 2026-10-21

- Tujuan: samakan form tambah Vuca dengan add_invoice; tanpa pilihan PPN dan tanpa perubahan rumus Vuca.
- Fase 1: grounding route tambah/simpan/edit, mapping company sumber, master accounting Vuca, dan dokumentasi. Selesai bila dependensi dan batas perubahan tercatat.
- Fase 2: layout memakai invoicing.css, empat sales_role dari mapping Vuca, preview jurnal, aset SweetAlert2 lokal, dan feedback submit. Bergantung fase 1; selesai setelah tes render/client lulus.
- Fase 3: validasi COA server, pertahankan COA pada edit, transaksi sukses hanya setelah commit. Gunakan Invoice_writer yang sudah memiliki locking, pemeriksaan transaksi, dan rollback; perubahan dibatasi cabang Vuca.
- Fase 4: lint, tes mapping/render/client, regresi perhitungan dan writer, fault injection tulis/commit, audit aman dan deduplikasi. Tanpa browser atau perubahan database produksi.
- Kontrak: DPP=TOP; DPP lain-lain=DPP*11/12; PPh=DPP*0,5%; piutang=DPP-PPh; PPN jurnal=0. Hidden field kompatibilitas lama dipertahankan.
- Cakupan edit: pertahankan akun pendapatan tersimpan, tanpa redesign. Mapping penjualan dibaca dari company sumber dan harus owner Vuca (4); tidak memerlukan validasi mapping PPN.
- Perubahan lokal di luar invoicing tidak disentuh. Seluruh catatan pekerjaan sebelumnya dipertahankan.
- Status akhir: keempat fase selesai; bukti pengujian, inventaris file dan keterbatasan dicatat dalam docs/invoicing-vuca-ui-validation-2026-10-21.md. Status endpoint/hash runtime tersedia di docs/invoicing-vuca-ui-manifest.json. Tidak ada migrasi, browser, perubahan database produksi atau publikasi Git.

## Revisi kepadatan UI Vuca - 2026-10-21

- Tujuan: kurangi whitespace sesuai screenshot pengguna, dengan ukuran teks/kontrol tetap terbaca.
- Perubahan: CSS khusus .vuca-invoice untuk padding/jarak lebih rapat, customer sebagai grid label/nilai, field dokumen tidak stretch; gabungkan TOP dan COA dalam kolom kiri dengan ringkasan terbatas 360px di kanan. Stack satu kolom pada layar sempit; tabel tetap scroll lokal.
- Validasi: tes render Vuca/reguler dan client Vuca, lint view, whitespace, pemeriksaan scope CSS dan manifest hash. Tidak mengubah JavaScript, controller, mapping, rumus atau writer; hasil integrasi sebelumnya dipakai kembali karena dependensinya tetap.

## Pemadatan add_invoice mengikuti Vuca - 2026-10-21

- Terapkan aturan compact Vuca yang sama melalui class invoice-compact hanya pada mode add di consulting_invoice. TOP dan COA menjadi kolom kiri, ringkasan di kanan; mode edit/detail mempertahankan struktur sebelumnya.
- Pertahankan seluruh field PPN, COA, nominal, fingerprint, ID client dan endpoint. Tidak mengubah controller, writer, JavaScript atau rumus.
- Validasi: render semua mode/perlakuan PPN, client reguler/Vuca, lint PHP, DOM grouping, scope CSS, whitespace dan hash manifest. Hasil integrasi transaksi terdahulu dipakai kembali karena seluruh dependensi transaksi tetap.

## Publikasi perubahan invoicing ke development - 2026-10-21

- Fase 1 selesai: fetch remote, pilih 23 path invoicing dan bagian dokumentasi task ini; pertahankan 17 perubahan lokal lain.
- Fase 2 selesai: ekspor staging menjadi kandidat Git terisolasi dengan dependensi HEAD; jalankan ulang tes, integrasi disposable MariaDB, lint, scan conflict dan verifikasi scope/hash.
- Fase 3 selesai: commit kode 535fa47ae63c733e03427d0735c5bee4a299ff9b dan push tanpa force ke origin/development; konfirmasi SHA remote. Hasil publikasi dicatat pada checkpoint dan docs/invoicing-ui-publication-2026-10-21.md sebagai dokumentasi lanjutan.

---

## Dropdown aksi daftar invoicing - 2026-10-21

- Fase 1: grounding kolom option/action, handler modal dan lifecycle DataTables; pertahankan aksi yang dibentuk server berdasarkan status/izin.
- Fase 2: satu tombol Aksi per baris pada kedua tab; menu memakai node aksi asli agar URL, data attribute dan handler modal tetap bekerja. Menu ditempatkan di body saat terbuka agar tidak terpotong scroll container, lalu dikembalikan saat ditutup.
- Fase 3: uji pilihan menu, keyboard, klik luar, pergantian baris, redraw/scroll/resize, posisi viewport, dan pemeliharaan atribut; jalankan regresi render/list/client serta lint. Tanpa perubahan database/rumus atau browser.

### Penyempurnaan ikon dan warna dropdown

- Tambahkan ikon Font Awesome yang sudah digunakan aplikasi: dokumen untuk buat, mata untuk lihat, pensil untuk revisi, printer untuk invoice, PDF untuk kwitansi, tanda tutup untuk aksi tutup yang memang tersedia.
- Aksen biru/hijau lembut membedakan invoice reguler/Vuca; amber untuk revisi dan merah untuk tutup. Warna dibatasi ikon dan hover/focus, tombol Aksi memakai tint biru. Ikon dekoratif aria-hidden dan label teks dipertahankan.
- Validasi hanya batch menu/list, syntax dan kontras warna; hasil tes transaksi serta render sebelumnya dipakai kembali karena dependensinya tidak berubah.

## Pembatasan Pengaturan COA dan publikasi dropdown - 2026-10-21

- Gunakan Auth::is_admin() yang sudah memeriksa group admin (id_group=1), tanpa menebak dari nama user atau user ID. Tombol hanya tampil untuk admin yang mempunyai izin Configure; non-admin tetap tidak melihat meskipun diberi izin Configure.
- Lindungi GET coa_settings dengan 403 untuk non-admin dan save_mapping melalui pengecekan admin di dalam boundary transaksi/audit yang sudah ada. Tidak mengubah role/izin user, akun COA, atau data transaksi.
- Uji render admin/non-admin, GET langsung, POST dengan Configure, konfigurasi tidak berubah saat ditolak dan audit tunggal; jalankan suite integrasi/audit yang terkait.
- Setelah validasi, pilih perubahan dropdown/ikon/admin-COA saja, pertahankan perubahan lokal modul lain dan sebagian dokumen bersama, lalu commit/push ke origin/development dan verifikasi SHA remote.

---
