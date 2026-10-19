# Download Format Excel dan Impor Massal Asset

Alur pengguna: **Download Format Excel → isi Data Asset → Import Excel → Periksa Preview → Simpan Semua Asset**. Tombol muncul di List Assets untuk permission `Assets.Add` setelah schema siap. Permission View tetap berlaku untuk halaman list.

Template memiliki sheet aktif Data Asset, Panduan & Contoh (tidak diimpor), dan Referensi tersembunyi. Pilihan master diambil saat download: kategori aktif/nondeleted, company consultant, dan department HRIS dengan company. Nama identik dibedakan dengan ID. Department opsional. Format saat ini menggunakan delapan kolom A–H; file lama sembilan kolom harus diunduh ulang. Asset baru menyimpan Cost Center kosong, sementara data historis tetap dipertahankan saat asset lama diedit.

Qty kosong berarti 1; rentang 1–99. Nilai perolehan positif **per unit**, rupiah utuh tanpa Rp/pemisah ribuan. Qty 5 dan 3000000 membuat lima asset senilai Rp3.000.000 masing-masing. Masa manfaat 1–20 tahun. Kode dan penyusutan bulanan dibuat server; pembulatan setengah rupiah naik, mulai bulan perolehan, mengikuti form existing. Tidak mengimpor saldo awal, jurnal, akumulasi historis, atau mengedit asset lama.

## Rollout schema

Jalankan migration CLI pada database tujuan **sebelum fitur diaktifkan**. Database ditentukan secara eksplisit; kredensial memakai konfigurasi environment aplikasi. File hanya bisa menjalankan migration lewat CLI.

```sh
# Docker lokal: ganti DATABASE dengan nama database aplikasi dari konfigurasi development.
docker exec sendigs_ss php /var/www/html/application/modules/asset/migrations/import_schema.php --apply DATABASE
```

Migration memeriksa kode duplikat dan InnoDB sebelum DDL, menambahkan unique index tunggal `asset.kd_asset`, lalu tabel `asset_import_batches`. Duplikasi menghentikan migration tanpa menghapus/mengoreksi asset. Jalankan saat writer asset sedang tidak aktif; unique-index creation juga menolak duplikasi yang muncul setelah pemeriksaan. Migration idempotent. Backup schema/data sesuai prosedur lingkungan tujuan. Rollback aplikasi dapat mempertahankan schema additive ini; jangan menghapus batch receipts karena dipakai untuk idempotensi.

CLI memakai config database dasar bila tersedia, lalu override environment bila ada. Default `CI_ENV=development`; set `CI_ENV` sesuai environment tujuan saat rollout di luar Docker lokal. Config yang tidak ditemukan menghentikan CLI dengan pesan yang jelas.

Impor menggunakan koneksi mysqli aplikasi yang sama, GET_LOCK per database/cabang, transaksi asset + jadwal + hasil batch, dan insert jadwal dalam chunk 500. Writer manual/PR tidak menggunakan lock impor, tetapi unique index melindungi kode: bila terjadi konflik, batch impor rollback seluruhnya. Tidak pernah menimpa asset.

## Kontrak dan batas

- GET `asset/download_import_template`; POST multipart `asset/preview_import` (`file`, `nonce`); POST `asset/save_import` (`token`, `nonce`). Semua memerlukan Assets.Add. Nonce scoped session melindungi POST tanpa mengubah konfigurasi CSRF global.
- JSON: `status`, `pesan`, `token`/`ringkasan`/`preview`/`kesalahan` sesuai endpoint. Error berisi `baris`, `kolom`, `pesan`; baris 0 berarti error file/batch. Save tidak menerima nilai/baris dari browser.
- Preview valid disimpan di DB dengan random token 64 hex, owner username dan cabang session, expiry UTC 30 menit. Master direvalidasi saat konfirmasi. Completed receipt disimpan; retry token yang sama mengembalikan hasil semula, termasuk setelah expiry preview. Preview expired yang belum disimpan dibersihkan bertahap saat preview berikutnya.
- Maksimal file 5 MB, 500 baris nonkosong, 1.000 unit; baris kosong diabaikan. Formula, header salah, master tidak tersedia, nominal/tanggal/Qty salah ditolak. Tanggal mendukung Excel 1900/1904, dd/mm/yyyy, yyyy-mm-dd.
- Guard tambahan: 200 entri ZIP/30 MB isi, 10.000 baris fisik sheet input, hanya kolom A–H; XML DTD/entity, macro dan link eksternal ditolak. Nominal maksimal Rp999.999.999.999 per unit. Kode existing membatasi kategori ID 1–99, urutan grup 1–999 per bulan/kategori/cabang.
- PHPExcel existing dengan PCLZip, tanpa kebutuhan ZipArchive. Download template diserialkan melalui file lock karena backend legacy memakai nama file sementara bersama. Tidak mengganti library Excel project.
- PHP `upload_max_filesize` minimal 5M dan `post_max_size` lebih besar dari 5M. Docker lokal saat validasi 50M/50M, memory512M. Pantau timeout request untuk batch terbesar di server tujuan; tidak menaikkan timeout global.
- Asset existing memakai tabel latin1. Konversi/truncation warning database membatalkan batch agar data tidak berubah diam-diam; nama di luar charset perlu penanganan schema tersendiri.

## Pengujian tanpa browser

```sh
docker exec sendigs_ss php /var/www/html/application/modules/asset/tests/import_rules_test.php
docker exec sendigs_ss php /var/www/html/application/modules/asset/tests/import_controller_test.php
docker exec sendigs_ss php /var/www/html/application/modules/asset/tests/import_excel_test.php
docker exec sendigs_ss php /var/www/html/application/modules/asset/tests/import_database_test.php
node --check assets/js/asset/import.js
```

Tes DB menciptakan database `asset_import_test_<random>`, menyalin **struktur** dua tabel tanpa data live, dan menghapus database uji di finally. Memerlukan CREATE/DROP DATABASE, CREATE TRIGGER, serta `proc_open` untuk dua worker paralel. Tes meliputi rollback jadwal, konflik unique, ownership, expiry, master berubah, idempotensi, konkurensi, kode/jadwal, dan 500 baris/1000 unit. Fixture controller mengeksekusi saved() existing pada adapter in-memory serta menolak permission/method/nonce yang salah.

Roundtrip workbook memeriksa sheet/dropdown/style, native date/1904, formula/header/ukuran/ZIP expansion/XML entity. Tidak memakai browser atau aplikasi Microsoft Excel; validasi dropdown dilakukan melalui struktur XLSX yang dibaca ulang.
