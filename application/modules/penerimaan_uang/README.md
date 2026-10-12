# Penerimaan piutang versi 2

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
