# Jurnal gabungan

Menu Finance **Jurnal**, route `jurnal_all`, menyajikan pending dari lima sumber. Pasang bersama `report_jurnal_all`; source `/jurnal` dan Buku Besar Petty Cash tidak diganti.

## Akses dan pemasangan

Jalankan `migrations/001_register_menu.sql` pada database ERP saat pemasangan. Script idempotent membuat dua menu dan permission `Jurnal_All.View`, `Jurnal_All.Manage`, `Report_Jurnal_All.View`; tidak menyalin grant role/user. Admin memakai bypass existing. Non-admin baru memperoleh akses setelah administrator memberikan permission yang diperlukan; posting memerlukan View dan Manage.

Script menyalin posisi dari menu Invoicing/Report Invoicing, menyimpan visibility sembilan menu lama dalam `jurnal_all_menu_backup`, lalu menyembunyikannya. Pastikan kedua anchor tersedia dan dua menu baru terbentuk. `002_rollback_menu.sql` mengembalikan status visibility lama secara tepat dan menonaktifkan dua menu baru. Untuk rollback fitur lengkap, kembalikan pula delapan belas route entry pada `application/config/routes.php`; permission/grant tidak dihapus.

## Kontrak sumber

- INV: `Invoicing`, pending 0/kosong/NULL, invoice dan company tersedia, ada detail bernilai positif.
- RCV: `Penerimaan Piutang`, pending non-NULL selain1, invoice tersedia dan eligibility COA mengikuti source.
- PAY: Payment/Transport/Transportasi/Kasbon/Expense, pending non-NULL selain1 dan company tersedia. Kategori mengikuti `payment_approve` tanpa menambah nominal dari join.
- EXP: Expense Report/Expense Report Consultant, pending 0/kosong/NULL, group transaksi/jenis/company. Jenis ini hanya muncul di Expense.
- PCS: Petty Cash/Refill Pettycash/Payment Hutang Petty Cash, pending0/kosong. Refill/Hutang tampil di Pembayaran tetapi tetap memakai posting PCS. Multi-company satu aksi; filter company tidak memotong sisi lainnya.

Identitas asli `tr_jurnal` tidak diganti nomor dokumen tampilan. Group selain Expense adalah transaksi/jenis. Khusus INV, filter periode memilih invoice jika ada baris jurnal dalam periode tersebut; Semua Tahun tersedia di seluruh tab dan bulan dapat dipilih lintas tahun. Invoice tetap satu transaksi dengan seluruh nominal/detail, termasuk baris di luar periode; Tgl Jurnal/Tertua tetap tanggal terawal kelompok. Jumlah antarperiode INV dapat overlap dan tidak boleh dijumlahkan sebagai transaksi unik. Empat tab lain tetap memakai tanggal terawal kelompok; saat Semua Tahun dipilih, filter bulan juga memakai bulan tanggal terawal tersebut. Daftar, ringkasan, pencarian dan Excel memakai query yang sama. Search membaca header dan detail COA; ringkasan tidak dibatasi search. Excel berisi seluruh COA hasil filter, tanpa batas pagination; format XLS HTML mengikuti pola proyek, teks escaped dan formula injection dicegah. Perhitungan menggunakan integer cents; nominal di luar kapasitas integer PHP ditolak secara eksplisit.

Preview memakai renderer escaped bersama dengan perilaku source: INV/RCV/EXP/PCS read-only, textarea Payment mengikuti UI asal. Handler posting tetap mengambil nominal/keterangan authoritative dari database seperti sebelumnya. PCS pending memakai detail resolver source untuk fallback nama COA/referensi dan menampilkan company setiap baris. Revisi hanya INV/RCV: endpoint PCS lama tidak mempunyai implementasi model dan tidak ditampilkan UI asal.

Posting/revisi didelegasikan melalui `Modules::run` memakai allowlist tetap. Server memvalidasi ID/sumber/status seluruh kelompok, mengecek balance dan nominal, mengunci satu kelompok dengan advisory lock, lalu membaca ulang sebelum delegasi. Payload browser tidak menentukan company/nominal/refill. Buffer dan POST dipulihkan pada kegagalan. Respons `save`/`status` dinormalisasi.

Perubahan source dibatasi guard POST/permission gabungan, pemeriksaan hasil begin/write/commit, propagasi hasil transaksi PCS, ownership audit nested Expense, dan respons nomor final accounting setelah commit berhasil. Nomor, counter, rumus, tujuan database, serta urutan commit tidak diubah. Recorder gabungan memiliki satu scope request; metadata hanya outcome/reason/count, tanpa payload atau SQL; writer failure tidak mengubah hasil bisnis.

## Validasi tanpa browser

```powershell
Get-ChildItem application/modules/jurnal_all/tests -Filter '*_test.php' |
  Where-Object Name -ne 'Mysql_test.php' |
  ForEach-Object { & C:/xampp74/php/php.exe $_.FullName }
node application/modules/jurnal_all/tests/Client_test.js
```

`Mysql_test.php` membuat dan menghapus schema acak `codex_journal_test_*`, tidak memakai schema aplikasi. Parameter lingkungan: `JOURNAL_TEST_PORT` dan `JOURNAL_TEST_PASSWORD`; default development mengikuti fixture MySQL proyek. Jalankan hanya pada instance pengujian. Tes mencakup migrasi dua run, rollback, tidak adanya grant otomatis, dan query/export nyata.

Untuk XAMPP lokal, `tests/Run_mysql_fixture.ps1` menyiapkan instance MariaDB terisolasi di scratch dengan password acak dan port33317, menjalankan tes, lalu menghentikan proses dan menghapus datadir. Script tidak memakai service atau datadir aplikasi. Jika sandbox memblokir socket localhost, jalankan melalui approval eksekusi lokal yang sesuai.

Risiko yang tetap: transaksi lintas database/MyISAM belum atomik terhadap crash/putus koneksi; handler legacy yang dipanggil langsung tidak memakai advisory lock gabungan. Jika commit accounting gagal setelah source commit, hasil dilaporkan gagal dan administrator perlu memeriksa konsistensi; jangan otomatis retry. Fitur ini tidak merekonsiliasi accounting dan tidak mengubah engine.

Pada tab INV, `display_no_transaksi` berisi nomor invoice dan `no_referensi` berisi ID SPK Penawaran. `no_transaksi` tetap kunci source untuk grouping/posting; jangan menggantinya dengan nomor invoice tampilan. `invoice_number` dipakai terpisah oleh COA preview. Daftar/report, Excel, pencarian dan sorting mengikuti nomor tampilan; SPK tidak tersedia tetap kosong.

Pada tab Pembayaran (`PAY` dan Refill/Payment Hutang `PCS`), `display_no_transaksi` menampilkan nomor voucher pembayaran (`payment_approve.id_payment`, misal `BK--1026-0003`). Bila nomor voucher belum terbentuk, sistem fallback ke `no_transaksi`. `no_transaksi` tetap kunci source native (`payment_approve.id`, misal `BK--1026-0074` atau daftar gabungan ID) untuk integritas grouping dan posting. Pencarian di Jurnal All mendukung pencarian multi-identitas: nomor voucher pembayaran, nomor dokumen referensi (`no_doc`), serta ID approval internal.

Pada tab Pencatatan Petty Cash (jenis Petty Cash), No. Referensi memakai no_pelaporan RPC dari relasi tr_expense_petty_cash.no_pencatatan → detail pelaporan pencatatan_id → header pelaporan. Status draft/waiting/approved mengikuti relasi aktif modul Expense Petty Cash. Nomor PCP tetap no_transaksi dan display_no_transaksi/kunci posting; COA/referensi native tidak diubah. Lookup batch terpisah dari agregasi mencegah nominal ganda, nomor unik diurutkan bila ada beberapa relasi aktif, dan referensi kosong bila tidak tersedia. Mapping berlaku pada list/search/sort/header detail/report dan Excel header; Refill/Payment Hutang pada tab Pembayaran tetap memakai pemetaan asal.

Detail Pencatatan Petty Cash (jenis Petty Cash) ditampilkan per id_company sesudah resolver COA native: setiap sisi memiliki tabel, subtotal cents dan status balance sendiri. Header tetap satu PCP/RPC dan satu posting seluruh sisi; daftar/saldo gabungan, ekspor, blocked COA dan validasi posting tidak berubah. Company berlabel sama tetap dipisahkan bila ID berbeda. Refill/Payment Hutang di Pembayaran mempertahankan layout preview sebelumnya. Histori posted memakai section company yang sama tanpa aksi posting.

Kolom Keterangan header/tab Penerimaan memakai `Pelunasan [no_invoice] - [nm_customer]` dari invoice terkait pada relasi penerimaan existing. Komponen kosong ditampilkan dash unavailable. Mapping berlaku untuk daftar, pencarian dan header report/detail; keterangan asli per baris COA/Excel serta payload/routing posting tetap authoritative source.

Tab Expense Report memakai metadata tampilan khusus: Consultant = `Expense Report Consultant - [no_transaksi]`; regular = `Realisasi Kasbon [Pengembalian/Reimburse] - [pengaju PR kasbon]`. No. Referensi berisi kasbon yang benar-benar ada melalui header/detail expense, relasi PR petty/reverse kasbon, atau header consultant; tanpa relasi valid tampil `-`. Beberapa nomor/nama unik diurutkan dan dipisahkan koma. Pengaju PR mengikuti tipe departemen/stok/asset dan user created_by PR, tanpa fallback ke pembuat jurnal. Jenis pengembalian/reimburse mengikuti lebih_bayar/kurang_bayar positif lalu selisih detail kasbon-expense; impas/unknown tidak diberi subtype, requester unavailable `-`. Metadata lookup terpisah dari agregasi jurnal; grouping company, source key, nominal, keterangan COA/Excel detail dan posting tidak diubah.

## Metadata posting dan nomor accounting

Posting melalui dispatcher gabungan memerlukan `migrations/003_posting_metadata.sql`. Migrasi idempotent menambah tabel InnoDB `tr_jurnal_postings`, tanpa mengubah `tr_jurnal` atau counter accounting; sudah diterapkan pada development lokal. Environment lain belum diterapkan. Jika schema belum tersedia, dispatcher menolak posting sebelum memanggil handler native.

Handler native mengembalikan nomor yang benar-benar dihasilkan pada respons sukses sesudah pemeriksaan commit. Dispatcher menyimpan nomor per company, waktu Asia/Jakarta, ID dan snapshot username auth dalam transaksi metadata tersendiri setelah native sukses. Identitas immutable memakai SHA256 kelompok native (termasuk company untuk Expense). Input browser tidak menentukan actor atau nomor final; revisi tidak mencatat metadata posting.

Commit metadata diperiksa sebelum `metadata_saved=true`. Bila accounting berhasil tetapi metadata gagal, respons bisnis tetap sukses dengan warning `posted_metadata_missing`; UI menampilkan warning setelah memuat ulang daftar. Jangan mengulang posting accounting untuk memperbaiki metadata. Log teknis hanya berisi hash kelompok, dan recorder bisnis tetap satu hasil per request. Terdapat celah crash antara native commit dan metadata commit; transaksi lintas database tidak dirombak pada perubahan ini.

Report, detail dan Excel membaca nomor accounting terkonfirmasi, tanggal/jam WIB dan username tersimpan. Histori tanpa metadata serta posting langsung melalui jalur legacy tetap `-`; tidak ada backfill tanggal/user atau penggantian nomor staging lama. Untuk antar-company semua nomor disimpan dengan company tujuan. Nomor pada Excel adalah metadata tingkat transaksi.

`004_rollback_posting_metadata.sql` adalah rollback eksplisit yang menghapus tabel dan seluruh metadata; tidak dijalankan otomatis atau pada database development aplikasi. Fixture terisolasi menguji CREATE dua kali dan rollback. Baca laporan `docs/jurnal-all-posting-metadata-2026-10-15.md` untuk keputusan dan bukti pengujian.
