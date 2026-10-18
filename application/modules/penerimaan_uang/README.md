# Integrasi invoice konsultasi reguler versi 3 — 18 Oktober 2026

Batch yang memuat invoice reguler baru memakai Consulting_receipt: PPh aktual didebit saat penerimaan, pengurangan piutang kas+admin+PPh, snapshot per detail, rollback tepat, dan posting GL per owner snapshot. Batch campuran mempertahankan formula legacy pada tiap baris lama. Old-only tetap jalur existing dengan mutex; alokasi induk yang dipakai v3 dilindungi dari overwrite/penghapusan split legacy.

Schema003, pengaturan COA dan bukti pengujian: docs/invoicing-consulting-v3-2026-10-18.md. Test aktif: invoicing/tests/Consulting_integration_test.php --isolated-local-db. Dokumentasi v2 historis berikut tetap konteks historis, bukan klaim bahwa seluruh endpoint lama telah dikonversi.

# Penerimaan piutang versi 2

## Perbaikan daftar jurnal pada view — 2026-10-12

Detail penerimaan sekarang mencari `tr_jurnal.no_transaksi` menggunakan
`tr_penerimaan_piutang.no_surat`, sesuai writer penerimaan. Pencarian lama memakai
ID invoice sehingga jurnal tidak muncul; pencarian berdasarkan invoice juga
berisiko mencampur beberapa cicilan. Filter jenis transaksi tetap
`Penerimaan Piutang`, tanpa mengecualikan jurnal posted. Kolom COA diperbaiki
agar kode akun tampil di dalam sel, bukan menjadi atribut HTML.

DB lokal mengonfirmasi invoice 110/STM/2026 memiliki empat jurnal posted untuk
00046/RKN-KRG/VIII/26, dengan total debit/kredit masing-masing 44.100.000.
Tidak ada perubahan atau pembuatan ulang jurnal. Regresi controller/render:
`C:/xampp74/php/php.exe application/modules/penerimaan_uang/tests/detail_journal_test.php`.
Tool diagnosis menerima pilihan PPh opsional Y/N; default mengikuti riwayat
penerimaan agar invoice dengan pilihan Y dapat diaudit tanpa penolakan palsu.

## Catatan kode aktif — 2026-10-12

Bagian v2 di bawah adalah dokumentasi historis: controller aktual belum memiliki
preview token, locking, dan penghitungan server v2 yang dijelaskan di sana.
`tests/integration_test.php` menargetkan implementasi v2 tersebut dan belum sesuai
dengan controller aktual; kelulusan tes kalkulasi helper bukan bukti integrasi v2.

Perbaikan kasus 038/VSB/2026: preview memakai `saldo_piutang` tersimpan tanpa
menggantinya dengan `tagihan_ppn_jurnal - pph_jurnal`. Ini menjaga saldo cicilan
dan menghindari minus PPh saat gross invoice Vuca lama bernilai nol. Daftar invoice,
preview, dan snapshot penerimaan memakai helper gross existing (DPP + PPN hanya
untuk Vuca gross nol). Pembuatan invoice Vuca baru menyimpan gross sebesar DPP
karena PPN alur Vuca nol. Tidak ada koreksi database atau jurnal historis.

Regresi controller aktif tanpa DB/browser:
`C:/xampp74/php/php.exe application/modules/penerimaan_uang/tests/legacy_vuca_preview_test.php`.
Tes mencakup kedua pilihan PPh, cicilan, selisih saldo kecil, invoice reguler,
gross nonzero, daftar invoice, snapshot penerimaan dan pembuatan Vuca.
Data invoice 038 live belum diverifikasi.

### Perbaikan lanjutan pelunasan cicilan — 2026-10-12

Preview dan save menghitung sisa penyesuaian PPh dari jurnal penerimaan sebelumnya
(nomor penerimaan, COA PPh dan keterangan invoice persis). Kredit yang sudah
dipakai tidak diterapkan kembali. Pilihan PPh harus konsisten dengan riwayat;
riwayat yang hilang/ambigu atau kredit melebihi PPh invoice ditolak untuk
rekonsiliasi, bukan dianggap nol. Cicilan kecil hanya memakai penyesuaian hingga
nominal penerimaan, sehingga sisanya tetap dapat dipakai pada cicilan berikutnya.

Save menghitung saldo, kredit piutang/PPh, debit admin dan debit bank dari saldo
aktual serta penerimaan/admin dalam satuan sen; hidden saldo/jurnal POST diabaikan.
View memakai sisa PPh tetap dari preview, bukan nilai kredit yang berubah ketika
diketik ulang. Ini menyelesaikan fixture 189/STM/2026: saldo dan penerimaan terakhir
2.574.990, PPh sebelumnya 468.180 sudah dipakai, sisa piutang 0 dan kredit PPh 0.

Tes tambahan: `node application/modules/penerimaan_uang/tests/installment_view_test.js`
menjalankan fungsi inline aktual melalui adapter jQuery tanpa browser.
Audit Windows awal gagal karena hostname mysql berada di jaringan Docker. Audit
read-only lewat container kemudian berhasil (lihat catatan berikut). Tidak ada
koreksi historis. Locking/revalidasi transaksi bersamaan dan rollback v2 di bawah
belum diimplementasikan oleh perubahan ini; suite integrasi v2 tetap historis.

### Kompatibilitas rincian penerimaan tanpa jurnal — 2026-10-12

DB lokal mengonfirmasi detail 745 / 00025/RKN-KRG/X/26 untuk invoice 189, dengan
penerimaan 23.409.000, admin 0 dan sisa 2.574.990; seluruh jurnal transaksi itu
tidak ditemukan. Model sekarang mendukung keadaan ini dengan rekonstruksi:
penyesuaian PPh = penerimaan + admin - (saldo sebelumnya - saldo sesudahnya).
Untuk kasus ini: 23.409.000 - (25.515.810 - 2.574.990) = 468.180 sudah dipakai.

Fallback hanya berlaku saat seluruh jurnal transaksi kosong. Semua rincian harus
lengkap, terurut dengan ID detail unik, nominal nonnegatif, saldo tidak meningkat,
penyesuaian tidak melebihi penerimaan/PPh invoice, dan saldo rincian terakhir sama
dengan saldo invoice aktual. Bila ada transaksi berjurnal dalam rantai yang sama,
nilai jurnal harus cocok dengan rekonstruksi. Jurnal sebagian atau ambigu tetap
ditolak. Penyebab hilangnya jurnal lama belum diketahui; tidak ada backfill.

Tool CLI read-only: `tools/diagnose_receipt.php 189/STM/2026`. Model aktual pada
snapshot DB menghasilkan sisa penyesuaian PPh 0, kredit piutang pelunasan 2.574.990
dan saldo akhir 0. Tool tidak menyimpan penerimaan atau mengubah saldo/jurnal.

Pembayaran bertahap memakai pilihan PPh penerimaan pertama. Untuk pilihan N,
penyesuaian PPh transaksi adalah nilai terkecil antara penerimaan dan PPh yang
belum dikreditkan. Kredit piutang = penerimaan + admin - penyesuaian tersebut.
Penyimpanan menghitung ulang semua nilai dan jurnal di server, menggunakan sen.

## Penerapan

1. Terapkan `migrations/001_receipt_calculation_v2.sql` sekali pada database finance
   sebelum merilis kode. Migration hanya menambah kolom nullable dan indeks;
   tidak mengubah saldo atau jurnal lama. Periksa keberadaan kolom/indeks dahulu
   bila melanjutkan penerapan yang sebelumnya terputus.
2. Rilis controller, model, library, views, dan
   `assets/js/penerimaan_uang/calculation.js` bersamaan. Tanpa migration,
   preview/save/rollback menampilkan kebutuhan migration, bukan memakai rumus lama.
3. Jalankan audit melalui endpoint terautentikasi `penerimaan_uang/audit_pph`.
   Untuk satu invoice gunakan `?id_inv=<ID internal invoice>`.
4. Rekonsiliasi temuan data lama sebagai pekerjaan terpisah. Audit tidak menyediakan
   endpoint koreksi dan berjalan dalam snapshot database READ ONLY.

Audit JSON berisi penerimaan, pilihan PPh, kredit aktual, status posting, saldo
rekonstruksi, saldo simulasi aturan v2, dan selisih. Prioritas 0 menunjukkan masalah
atau selisih; 1 menunjukkan beberapa penerimaan N; 2 adalah sisanya. Rekonstruksi
dimulai dari `total_akhir_jurnal`, sehingga koreksi eksternal tetap membutuhkan
review. Saldo rekonstruksi bernilai null bila bukti jurnal tidak lengkap.

Riwayat lama dicocokkan berdasarkan nomor penerimaan, COA, dan keterangan persis
`nm_coa + ' - ' + no_invoice`. Pemetaan ambigu, riwayat PPh campuran, atau kredit
melebihi PPh invoice harus direkonsiliasi sebelum penerimaan berikutnya.

Preview disimpan di session (maksimal 10 preview, berlaku 1 jam). Save membaca ulang
saldo, alokasi, dan riwayat di dalam transaksi serta menolak preview yang berubah.
`FOR UPDATE` mengunci invoice dalam urutan ID dan alokasi; advisory lock MySQL
`sendigs_receipt_v2` menserialkan penerimaan/rollback sekaligus penomoran surat.
Kode mengandalkan InnoDB dan dukungan GET_LOCK pada koneksi finance.

Rollback hanya boleh untuk penerimaan terakhir setiap invoice, tanpa jurnal posted
atau bukti potong terkait. Nilai yang dikembalikan mengikuti kredit piutang aktual.
Pemakaian alokasi dikurangi sebesar penerimaan yang dibatalkan; split lain dan
status split induk dipertahankan. Respons AJAX menggunakan `status`/`message`,
dengan alias `msg` untuk kompatibilitas pemanggil lama.

## Pengujian tanpa web

```powershell
C:/xampp74/php/php.exe application/modules/penerimaan_uang/tests/calculation_test.php
node application/modules/penerimaan_uang/tests/calculation_test.js
C:/xampp74/php/php.exe application/modules/penerimaan_uang/tests/integration_test.php
```

Integration test memakai MySQL/MariaDB **loopback 127.0.0.1:3306**, akun lokal
`root` tanpa password. Tidak membaca konfigurasi database aplikasi. Test membuat
database acak `codex_receipt_test_<hex>` berisi schema/fixture sintetis, menerapkan
migration, menjalankan controller melalui adapter mysqli, lalu menghapus hanya
database uji tersebut dalam finally. Worker koneksi kedua menguji konkurensi nyata.
Test ini tidak boleh diarahkan ke database aplikasi atau produksi.

Skenario mencakup MINARTA lunas dengan PPh sekali, data legacy posted, nominal
desimal/admin, cicilan kecil, kedua COA, pemetaan beberapa invoice, manipulasi POST,
kegagalan atomik, snapshot stale, rollback/dependensi/bukti potong, split lain,
audit read-only, dan JavaScript inline. Pengujian tidak membuka browser.

## Verifikasi penerapan pada 5 Oktober 2026

Migration tambahan telah diterapkan pada database finance yang dikonfigurasi
(db_sendigs_ss), memakai ALGORITHM=INPLACE dan LOCK=NONE dengan lock timeout 10 detik.
Terdapat 704 detail historis saat verifikasi; seluruh kolom baru tetap NULL pada
baris tersebut (backfilled_rows=0). Keenam tabel transaksi terkait memakai InnoDB.
Migration diuji terlebih dahulu pada database terisolasi. Jangan menerapkan ulang
file migration pada database yang sudah memiliki kolom dan indeks ini.

Riwayat invoice 189/STM/2026 (00278-INV-VIII-26) diverifikasi secara read-only:

- Penerimaan pertama: 00025/RKN-KRG/X/26, tanggal 2026-10-05 10:49:58.
- Pilihan PPh: N; penerimaan Rp23.409.000,00; admin nol.
- Kredit piutang Rp22.940.820,00 dan kredit PPh Rp468.180,00.
- Saldo invoice Rp2.574.990,00; jurnal penerimaan tersebut masih belum posted.

Model baru juga dijalankan terhadap data aktual dalam transaksi READ ONLY:
sisa penyesuaian PPh nol, simulasi penerimaan Rp2.574.990,00 menghasilkan kredit
piutang Rp2.574.990,00 dan saldo nol. Simulasi ini tidak menyimpan pembayaran.
Tidak ada koreksi saldo atau jurnal historis yang dijalankan.

Kode aplikasi diperbarui di workspace ini. Upload/rilis kode ke server website
belum dilakukan dan bukan bagian verifikasi database di atas. Pengujian tidak
mengakses halaman web.

## Invoice Vuca 036/VSB/2026 — 6 Oktober 2026

Sumber masalah: save_invoice_vuca menyimpan tagihan_ppn_jurnal=0 meskipun DPP
positif. Preview penerimaan lama mengganti saldo aktual dengan gross minus PPh,
sehingga menghasilkan -75.000. Saldo database sebenarnya 14.925.000.

Pembuatan/edit Vuca sekarang menghitung gross/net dan nominal jurnal di server
dengan satuan sen. PPN Vuca tetap nol sesuai alur sebelumnya. Edit mengunci invoice
dan riwayat sebelum mutasi, menolak invoice yang berubah atau sudah memiliki
penerimaan, serta mempertahankan larangan edit jurnal posted. Invoice Vuca lama
dengan gross nol mendapat fallback DPP+PPN untuk tampilan dan snapshot tagihan
penerimaan; fallback tidak pernah mengubah saldo aktual.

Koreksi terbatas telah dilakukan pada 00297-INV-IX-26: hanya tagihan_ppn_jurnal
berubah dari 0 menjadi 15.000.000. Saldo tetap 14.925.000, belum ada penerimaan,
keempat jurnal posted identik sebelum/sesudah. Saldo_piutang_tanpa_pph historis
dibiarkan: pencarian kode menemukan penulisan pada invoicing tetapi tidak ada
pembaca yang membutuhkan perubahan historisnya. Invoice baru menggunakan gross
untuk kolom tersebut mengikuti pola invoice reguler.

Bukti koreksi dan audit: tests/vuca_036_correction_audit.json. Audit read-only
menemukan lima invoice lain: 035, 037, 038, 039, 040/VSB/2026. Tidak ada koreksi
massal. Tool CLI tools/check_vuca_036.php default read-only; --apply hanya untuk
target ini, idempotent, memakai lock penerimaan, row lock, validasi bukti jurnal,
dan pemeriksaan seluruh kolom/jurnal sebelum commit. Kredensial dibaca dari config
aplikasi dan tidak disalin ke laporan.

Pengujian tambahan mencakup edit tanpa field gross, hidden net/jurnal dimanipulasi,
blokir edit posted/berpenerimaan, penerimaan Vuca kedua pilihan, penolakan lebih
bayar, dan snapshot gross legacy. Regresi MINARTA dan concurrency tetap lulus.
Kode hanya berada di workspace; belum diupload ke server website.
