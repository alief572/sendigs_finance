# Laporan implementasi Payment reference — 28 Oktober 2026

**Pembaruan terakhir:** normalisasi massal pending telah diterapkan melalui alur terpisah dan migrasi alias008:1518 nomor berubah,5 sebelumnya canonical,445pending dilewati; posted54 dan accounting tetap utuh. Lihat `docs/payment-pending-normalization-result-2026-10-28.md`. Pernyataan belum menerapkan data pada bagian awal laporan ini merupakan keadaan pekerjaan sebelumnya; alur patch ketat dan pengaman posting tidak dilonggarkan.

## Hasil

Jenis tepat Payment memakai voucher aktual untuk staging baru, nomor daftar/preview pending, serta referensi header/detail pada handler posting. Relasi diverifikasi lewat approval dan tr_payment_paid. Patch data lama disediakan melalui CLI dry-run/apply/rollback, tanpa menerapkannya pada data aplikasi. Referensi jurnal otomatis, jenis lain, data posted, dan metadata historis dipertahankan.

Perubahan memakai resolver bersama, registry percobaan posting, transaksi terpisah accounting/staging, dan advisory lock bersama sampai metadata selesai. Crash atau commit tidak pasti memblokir retry. Kandidat patch menggunakan ID/fingerprint/environment, journal manifest append-only dan pemeriksaan ulang. Tidak ada setting UI baru.

## File dalam cakupan pekerjaan

Kode baru:
- `application/modules/jurnal_all/libraries/Payment_reference.php`: resolusi, kategori inventory, pemeriksaan posted/accounting/collision/balance/company, lock dan engine.
- `application/modules/jurnal_all/libraries/Payment_posting.php`: penulisan voucher ke header/detail accounting, registry durable, commit dan retry barrier.
- `application/modules/jurnal_all/libraries/Payment_patch.php`: inventory dan lifecycle manifest/patch/rollback.
- `application/modules/jurnal_all/controllers/Payment_reference_cli.php`: CLI-only; manifest di luar document root.
- `application/modules/jurnal_all/migrations/007_payment_reference_attempts.sql`: registry InnoDB, idempotent.

Integrasi:
- `application/modules/pembayaran_material/controllers/Pembayaran_material.php`: lock, cek tabrakan staging, hanya field no_transaksi jenis Payment baru memakai voucher server; auto-jurnal tidak diubah.
- `application/modules/jurnal_all/models/Jurnal_all_model.php`: batch resolver pada metadata pending dan can_post, posted enrichment dipertahankan.
- `application/modules/jurnal_all/controllers/Jurnal_all.php`: validasi preview Payment sebelum tombol posting diaktifkan; perubahan user lain dipertahankan.
- `application/modules/jurnal_all/views/preview.php`: warning dan referensi detail sesuai voucher, tanpa override REF untuk Payment pending.
- `application/modules/jurnal_all/libraries/Jurnal_all_dispatcher.php`: menahan lock Payment selama pemeriksaan ulang, native posting dan metadata.
- `application/modules/jurnal_payment/controllers/Jurnal_payment.php`: delegasi hanya jenis tepat Payment ke writer terjaga; handler jenis lain dipertahankan.
- `application/modules/jurnal/controllers/Jurnal.php`: akses legacy Payment melalui handler terjaga yang sama.

Fixture/tes:
- `application/modules/jurnal_all/tests/Payment_reference_test.php`: tes storage SQLite dan simulasi primitive MySQL, resolusi/preview/posted/existing accounting, apply/rerun/rollback, kegagalan commit/registry/logger.
- `application/modules/jurnal_all/tests/Payment_reference_mysql_test.php`: schema acak dan koneksi MariaDB nyata, lock koneksi kedua, patch/rollback, generator native dan commit posting.
- `application/modules/jurnal_all/tests/Query_test.php`: data master paid fixture untuk kontrak voucher valid.
- `application/modules/jurnal_all/tests/Payment_mapping_test.php`: ekspektasi kontrak baru ambigu/multi-voucher/dokumen-only; batas batch lookup tetap diuji.
- `application/modules/jurnal_all/tests/Legacy_posting_test.php`: Transport menguji branch legacy yang dipertahankan; exact Payment diuji dalam suite baru dengan storage dan failure matrix.
- `application/modules/jurnal_all/tests/Run_mysql_fixture.ps1`: pemilihan suite terisolasi, server tersembunyi dan cleanup schema/directory terjaga.
- `application/modules/jurnal_all/tests/Debt_posting_test.php`: port fixture dapat mengikuti JOURNAL_TEST_PORT; default lama tetap 3307.
- `application/modules/pembayaran_material/tests/Batch1_test.php`: fixture lock/collision; release setelah commit tidak dihitung sebagai query bisnis yang harus mengubah hasil transaksi.
- `application/modules/pembayaran_material/tests/Batch2_test.php`: bukti staging canonical dan auto-jurnal tetap menggunakan approval.
- `application/modules/pembayaran_material/tests/Selection_fixture.php`: dukungan fixture primitive baru; perubahan user yang sudah ada tetap dipertahankan.

Dokumentasi: `docs/implementation-plan.md`, `docs/task-progress.md`, laporan ini, dan `docs/payment-reference-normalization.md` (panduan rollout/CLI/rekonsiliasi).

Tidak menyertakan perubahan workspace lain pada pekerjaan ini. `jurnal_all/views/workspace.php` serta file terkait fitur Expense milik pekerjaan lain tidak diubah oleh implementasi ini.

## Validasi

- Payment_reference_test: **522 assertion lulus** setelah perbaikan konteks identitas, menggunakan query builder dan storage SQLite; GET_LOCK/engine/FOR UPDATE disimulasikan secara eksplisit. Primitive yang disimulasikan juga diverifikasi pada MariaDB nyata. Cakupan tambahan: collision dengan approval unpaid/paid, evidence suffix/REF tepat, prefix yang tidak cocok, bukti bertentangan, kategori/dokumen resolver batch, posted/metadata/accounting/registry tetap utuh, serta collision baru setelah dry-run.
- Payment_reference_mysql_test: **21 assertion lulus**, registry CREATE dua kali, lock dari koneksi kedua, patch/rollback/rerun, existing accounting, nomor native, header/detail, counter, posted exclusion dan retry barrier. Termasuk contextual approval/voucher collision dan pemeriksaan agar normalisasi tidak menghilangkan bukti jurnal. Database acak codex_payment_ref_* saja, dibersihkan setelah tes.
- Debt_posting_test: **142 assertion lulus** pada server MariaDB disposable; jenis hutang tetap tidak dinormalisasi.
- Jurnal All: Query135, Mapping47, Mixed20, Dispatcher45, Metadata20, Controller86, Petty113, Revision24, Debt_group26, Audit10, Concurrency/tampering72 lulus. Pengembalian Kasbon16 dari pekerjaan lain juga lulus ketika seluruh suite lokal dijalankan.
- Client Jurnal All49 dan Selection27 lulus.
- Pembayaran: Batch1 **248 skenario**, Batch2 **581 skenario**, Selection98, validator43, regresi invalid journal, COA17, reconciliation24, voucher repair19, remote profile11, remote model10, remote repair13 lulus.
- Lint seluruh file PHP yang dibuat/diubah dalam cakupan dan diff whitespace lulus. Tidak menjalankan browser.

### Batas baseline dan layanan

- Challenger_stress_test gagal pada fixture Invoice counter rollback. Salinan tes menggunakan Jurnal_all_model dan Jurnal_payment dari HEAD mengulang kegagalan identik pada assertion yang sama, sebelum perubahan Payment. Bukti lokal: scratch/payment-baseline-challenger.php. Tidak melemahkan assertion atau mengubah implementasi Invoice untuk menyembunyikannya.
- Mysql_test umum terhenti di setup migrasi 001 pada fixture menu tanpa kolom created_by, sebelum bagian query/model. Migrasi tersebut tidak diubah pekerjaan ini. Tes database khusus Payment dan hutang berjalan terpisah dan lulus.
- View_payment_new_test tidak dapat mengakses socket database aplikasi dari sandbox; Payment_list_grouping_test melaporkan SKIP tanpa koneksi database. Tidak dicoba ulang ke database aplikasi. Tidak menghitung kedua pemeriksaan tersebut sebagai lulus.
- Fixture MariaDB awal tidak dapat mengakses loopback dalam sandbox. Dengan izin eksekusi lokal, server sementara dan schema acak berjalan; tidak memakai database aplikasi.

## Perbaikan visibilitas dan diagnosis runtime

Pemeriksaan read-only development terhadap BK--1026-0004 mengonfirmasi registry007 belum tersedia, source Payment BK--1026-0073 masih sts0, tetapi jurnaltras3 baris serta accounting default (3 detail/1 header) nomor101BKA260000276 sudah ada. Accounting VUCA tidak memiliki hasil terkait; tabel jurnal/header/counter masih MyISAM. Tidak ada schema/data aplikasi diubah. Bukti lengkap: `docs/payment-bk-1026-0004-posting-diagnosis.md`.

Perubahan lanjutan menyentuh Payment_reference.php (pesan prerequisite aman dan prioritas bukti histori), Jurnal_all.php (show_post/post_block_reason tanpa membuka can_post), assets/js/jurnal-all.js (Posting visible-disabled untuk manager, guard submit keyboard/manual dan reset state), serta Client_test.js/Controller_test.php/Payment_reference_test.php. Alat diagnosis CLI-only tersimpan di scratch/diagnose_payment_reference.php dan tidak mengandung kredensial.

Validasi penutupan terbaru: Client54, Controller89, Payment_reference531, MariaDB Payment21 serta seluruh suite lokal Jurnal All yang bergantung perubahan lulus; lint dan whitespace lulus. Pengujian browser tidak dilakukan. Dua kegagalan baseline terdahulu tetap terdokumentasi dan tidak diulang tanpa perubahan fondasi terkait. Tidak ada patch status/nomor, migrasi, konversi engine atau posting ulang pada database aplikasi.

## Sisa operasional

Migrasi registry belum diterapkan di database aplikasi, inventory aktual belum dibuat, dan apply/rollback data aktual belum dijalankan. Perlu deploy, pemeriksaan engine/koneksi, jendela penghentian penulisan, dry-run dan review manifest sesuai panduan sebelum apply. Tabel target MyISAM ditolak; konversinya bukan bagian task ini. Kelompok dengan hasil auto-jurnal tetap dilewati dan memerlukan rekonsiliasi terpisah. Tidak ada commit/push atau deployment pada task ini.
