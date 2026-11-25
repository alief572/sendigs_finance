# PHP 8.5 dengan CI3 — build, verifikasi, dan rollback

Branch `upgrade/php-8.5` berbasis `ccd47e6e54a6773561e0290c7e74b0bf11203f90` dari `origin/development`. Image lokal memakai PHP 8.5.13 dan Apache. Deployment mengikuti web server dan SAPI server existing setelah kompatibilitasnya diverifikasi. Gunakan `composer.lock` untuk membuat direktori `vendor` dari awal pada setiap rilis.

## Build dan smoke test lokal

Jalankan dari worktree upgrade. Nama container di bawah dikhususkan untuk pengujian ini. MySQL memakai tmpfs dan jaringan tanpa akses keluar. Konfigurasi sementara dan controller probe dipasang hanya selama tes.

```powershell
docker build -t sendigs-upgrade-php85:local .
docker run --rm --network none sendigs-upgrade-php85:local php -v
docker run --rm --network none sendigs-upgrade-php85:local composer validate --no-check-publish
docker run --rm --network none sendigs-upgrade-php85:local composer check-platform-reqs --no-dev

docker run --rm -d --name sendigs-php85-fixture --network none --tmpfs /var/lib/mysql -e MYSQL_ALLOW_EMPTY_PASSWORD=yes mysql:8.0
docker logs --tail 20 sendigs-php85-fixture
# Lanjutkan setelah MySQL menyatakan siap koneksi pada port 3306.
Get-Content -Raw tools/php85/schema.sql | docker exec -i sendigs-php85-fixture mysql -uroot
docker run --rm --network container:sendigs-php85-fixture -e SENDIGS_DISPOSABLE_FIXTURE=1 -v "${PWD}/tools/php85:/fixture:ro" sendigs-upgrade-php85:local sh /fixture/smoke.sh
docker run --rm --network none -v "${PWD}/tools/php85:/var/www/html/tools/php85:ro" sendigs-upgrade-php85:local php tools/php85/document_test.php
docker stop sendigs-php85-fixture
```

Smoke test memakai dua permintaan HTTP dengan cookie yang sama dan satu permintaan dengan ID sesi format lama. Ketiganya harus melaporkan CI3 3.1.13, PHP 8.5.13, dan Apache. Counter harus tersimpan pada sesi database. Percobaan insert duplikat harus di-rollback dan jumlah baris harus sama dengan jumlah transaksi yang berhasil.

## Pemeriksaan sebelum deploy ke server existing

1. Catat SHA yang benar-benar terpasang, artefak rilisnya, dan versi PHP CLI serta web. Verifikasi SAPI, worker atau proses layanan, file INI aktif, ekstensi, timezone, OPcache, dan PHP executable per cron. Bandingkan diff SHA itu dengan kandidat upgrade. Review perubahan fitur atau migrasi yang ikut terbawa dari `development`.
2. Siapkan direktori rilis baru dan gunakan lockfile untuk instalasi. Jalankan `composer install --no-dev --no-interaction --prefer-dist` dan `composer check-platform-reqs --no-dev` dengan executable PHP 8.5. Cocokkan ekstensi web dengan CLI, terutama MySQLi, GD, mbstring, zip, OpenSSL, fileinfo, dan XML. Cek bahwa `vendor/autoload.php` berasal dari build baru.
3. Pasang konfigurasi per lingkungan di luar artefak dan cek semua grup database yang aktif. Verifikasi aturan rewrite, batas upload, akses file, lokasi sesi, izin log/cache, dan semua penyimpanan lampiran. Jika server memakai Nginx/FPM, terapkan aturan Apache untuk mencegah eksekusi script di direktori upload pada konfigurasi Nginx yang ekuivalen.
4. Bila tersedia staging yang mengikuti setup server, gunakan salinan data yang sudah disanitasi. Uji login/izin, request → approval → payment → jurnal, laporan invoicing/piutang, upload/unduh, dan contoh PDF/XLSX dengan data sama di kedua runtime. Bandingkan nominal dan format. Periksa error log, audit, sesi yang sudah ada, dan job terjadwal.

## Rilis dan pemulihan

- Siapkan rilis lama berupa pasangan artefak, lockfile terpasang, runtime, dan konfigurasi web/cron. Siapkan backup yang sesuai dengan titik cutover untuk setiap database yang berubah. Upgrade runtime sendiri tidak memerlukan migrasi skema.
- Setelah keputusan rilis, aktifkan kandidat dalam jendela perubahan. Jalankan pemeriksaan kesehatan HTTP, login, sesi, pembayaran, jurnal, serta PDF/XLSX. Pantau log PHP, HTTP 5xx, kegagalan transaksi dan cron.
- Jika ditemukan kegagalan, arahkan web dan cron kembali ke rilis serta runtime sebelumnya. Reload layanan yang terlibat dan verifikasi PHP web serta CLI. Cek sesi lama, transaksi terakhir, keseimbangan jurnal, dan audit sebelum membuka akses normal. Jika ada migrasi fitur, rekonsiliasi transaksi baru dan ikuti prosedur pemulihan yang telah diuji untuk migrasi tersebut.

Saat ini staging belum tersedia. Perubahan server, merge, dan deploy produksi menunggu pemeriksaan oleh tim rilis.
