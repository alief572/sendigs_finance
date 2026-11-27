# PHP 8.5.12: isi branch, hasil verifikasi, dan runbook rilis

## Isi branch

`upgrade/php-8.5` = `origin/main` `0a4c1800` + perubahan kompatibilitas PHP 8.5. Tidak ada fitur dari
`development`, tidak ada migrasi database, dan `application/config/constants.php` identik dengan `main`.
Branch lama disimpan di `backup/upgrade-php-8.5-20261127` dan `backup/upgrade-php-8.5-remote-20261127`.

- Dependensi: mPDF 8.3.1, PhpSpreadsheet 5.12.0, HTML2PDF 5.3.3. `vendor/` tidak lagi di Git.
- `application/libraries/{Mpdf,PHPExcel}.php` adalah adapter ke pustaka Composer.
- `application/libraries/MPDF57/mpdf.php` sekarang shim ke adapter mPDF 8, karena view cetak lama masih
  meng-include path itu dan mPDF 6 tidak bisa di-parse PHP 8.
- `composer_autoload` menunjuk `vendor/autoload.php` di root. `display_errors=Off` pada `php.ini` repo.

## Hasil verifikasi (2026-11-27, Docker, PHP 8.5.12, tanpa database nyata)

| Pemeriksaan | Hasil |
| --- | --- |
| `composer validate`, `check-platform-reqs --no-dev` | Lulus |
| Tes PHP dan JS | 178 lulus, 43 gagal, 2 dilewati |
| Pembanding `main` di PHP 7.4.33 | Ke-43 kegagalan juga gagal di sana; tidak ada kegagalan baru |
| HTTP smoke (HMVC, sesi database, commit/rollback) | Lulus |
| Pembuatan dan pembacaan ulang PDF/XLSX | Lulus |
| Shim `MPDF57` membuat PDF | Lulus |
| Rute menu aktif | Sama dengan `main`: seluruh rute sidebar menemukan controller |

Tes database dengan MariaDB 10.11 sementara: Wapu_repair (119), Payment_identity (21), Payment_reference (39),
Consulting non-konsultasi (1.050) lulus di kedua runtime. Gagal di titik yang sama pada kedua runtime:
penerimaan_uang dan invoicing `integration_test`, Debt_posting, paid_status, Company_mysql,
Consulting (konsultasi), Mysql_test, cash_receipt, Coa_completion dan Voucher_repair (dua terakhir butuh skema
aplikasi). **Jalur yang dicakup tes gagal itu belum terbukti**, baik di `main` maupun di branch ini.

Sisa parse error: `*/controllers/backup_customer.php`, `produksi_200901/views/report_produksi.php` (juga gagal di
PHP 7.4) dan pustaka bawaan lama yang tidak lagi dimuat.

Daftar rute diambil dari menu database lokal, bukan dari production.

## Sebelum rilis

1. Catat SHA yang terpasang di server, versi PHP web dan CLI/cron, dan file INI aktif.
2. Siapkan PHP **8.5.12** dengan ekstensi: ctype, curl, dom, fileinfo, filter, gd, iconv, intl, libxml, mbstring,
   mysqli, openssl, simplexml, xml, xmlreader, xmlwriter, zip, zlib. Composer harus berjalan dengan PHP 8.5.
3. Backup folder aplikasi (termasuk `vendor/` dan file yang tidak dilacak) serta database.
4. Periksa di server: `git status` bersih untuk file yang dilacak, dan apakah ada kode yang bergantung pada
   `application/vendor/` (tidak dilacak Git; konfigurasi lama memuatnya, konfigurasi baru tidak).
5. PHP server tidak membaca `php.ini` di repo secara otomatis. Set `display_errors=Off`, `log_errors=On`,
   `memory_limit=512M`, `upload_max_filesize=50M`, `post_max_size=50M` pada konfigurasi server.

## Rilis (dalam jendela maintenance)

`git pull` **menghapus** `vendor/`. Aplikasi mati sampai `composer install` selesai. Kode lama tidak jalan di
PHP 8.5 dan kode baru mensyaratkan 8.5, jadi pergantian runtime dan kode dilakukan bersamaan.

```bash
git pull
composer install --no-dev --no-interaction --prefer-dist
composer check-platform-reqs --no-dev
mkdir -p application/cache/mpdf   # harus bisa ditulis user web
```

Lalu arahkan web dan cron ke PHP 8.5.12, restart layanan, dan uji: login dan hak akses, request → approval →
payment → jurnal, invoicing/piutang, upload/unduh, cetak PDF dan ekspor Excel. Pantau log PHP dan HTTP 5xx.

## Rollback

Kembali ke SHA lama (`git reset --hard <sha-lama>` mengembalikan `vendor/` yang dilacak), arahkan web dan cron ke
runtime lama, restart, lalu cek transaksi dan jurnal yang masuk selama jendela rilis.

## Masih terbuka

- Belum ada staging atau uji dengan data nyata, dan tidak ada uji web.
- Versi PHP dan konfigurasi server production belum diketahui.
- Jika `main` bertambah commit, merge `main` ke branch ini dan ulangi tes sebelum rilis.
