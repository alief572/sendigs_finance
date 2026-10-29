# Posting Payment ke company tujuan — 28 Oktober 2026

## Hasil

Instruksi terbaru mengizinkan posting Payment pending ke accounting company tujuan walaupun jurnaltras atau accounting default sudah mempunyai jurnal otomatis. Preview dan native handler kini memakai `Payment_reference::inspectForPosting()`. Pemeriksaan ketat `inspect()` yang digunakan patch lama tidak berubah. Nomor referensi header dan detail tetap voucher aktual dari relasi approval/alias tervalidasi.

BK--1026-0004 telah diverifikasi eligible ke `accounting_vuca`, company 4, sumber ID 11176–11179. Registry migration007 sudah dipasang pada development. Izin LOCK/UNLOCK pada target telah diperiksa tanpa DML. Transaksi aslinya belum diposting dan tetap pending. Jurnal lama 101BKA260000276 pada accounting default tetap utuh.

## Perubahan file

- `application/modules/jurnal_all/libraries/Payment_reference.php`: pisahkan pemeriksaan posting target dari inventory ketat; readiness registry/source InnoDB, counter, COA aktif, target InnoDB/MyISAM.
- `application/modules/jurnal_all/libraries/Payment_posting.php`: target scope, lock tabel target legacy, cek duplikasi ulang setelah lock, verifikasi detail/header/counter, dan penanganan hasil sebagian/tidak pasti.
- `application/modules/jurnal_all/controllers/Jurnal_all.php`: preview memakai validasi yang sama dengan native writer.
- `application/modules/jurnal_payment/models/Jurnal_payment_nomor_model.php`: argumen koneksi opsional pada generator BUK. Writer baru memakai koneksi target yang sama dengan lock agar tidak deadlock; pemanggil lama tetap kompatibel.
- `application/modules/jurnal_all/tests/Payment_reference_test.php`: keberhasilan target meski ada histori default/jurnaltras, preview controller sebenarnya, kegagalan MyISAM/registry/commit, corrupt readback, zero lines dan retained duplicate guards.
- `application/modules/jurnal_all/tests/Payment_reference_mysql_test.php`: generator sebenarnya, DATETIME, lock/counter MyISAM, histori utuh dan penolakan duplikasi pada schema disposable.
- `scratch/payment_posting_readiness.php`: utilitas CLI-only development, pemasangan schema007 eksplisit dan pemeriksaan kesiapan tanpa posting; hash sebelum/sesudah. Utilitas operasional lokal, bukan endpoint web.
- `docs/implementation-plan.md`, `docs/task-progress.md`, laporan ini: kontrak terbaru, verifikasi dan batas operasional.

Migration007 sudah ada dari pekerjaan sebelumnya; pekerjaan ini menerapkannya ke development. Tidak mengonversi engine tabel target dan tidak mengubah migrasi008/alias normalisasi.

## Kontrak penulisan

Staging posted, metadata posting, alias tidak lengkap/kontradiktif, mapping tidak valid, company tidak jelas, tidak balance, percobaan sebelumnya yang tidak pasti, dan hasil accounting pada company tujuan tetap diblokir. Nomor lama, nomor voucher, dokumen sumber dan referensi detail lama tetap diperiksa di target. Keberadaan jurnal otomatis pada default/jurnaltras tidak mengubah status sumber atau histori itu.

Claim registry InnoDB di-commit sebelum target ditulis. Source dikunci dan fingerprint diverifikasi. Target transactional memakai transaksi/counter row lock. Bila salah satu tabel target MyISAM, writer memakai WRITE table locks atas jurnal, japh dan counter; generator memakai koneksi itu. Referensi dan nomor accounting diperiksa ulang di dalam lock. Readback memeriksa hasil sebelum staging boleh sukses.

MyISAM tidak menyediakan rollback atomik. Setelah ada kemungkinan penulisan target, kegagalan dicatat `reconcile` (atau durable `started` jika registry writer gagal); retry diblokir. Tidak menghapus jurnal atau mengembalikan counter otomatis. Kegagalan yang terbukti sebelum penulisan dapat `failed_safe`. Metadata/audit failure setelah accounting tidak membuka hak posting ulang. Lock dilepas melalui finally.

## Verifikasi

- Payment reference/posting/strict patch dan preview: **679 assertions lulus**, SQLite storage dengan simulasi primitive MySQL. Termasuk legacy history, target duplicate, registry hilang, zero-value source, commit tidak pasti, detail/header corruption, partial MyISAM, source commit gagal, penulis registry gagal dan lock gagal/dilepas.
- Number normalization: **464 assertions lulus**; cakupan normalisasi sebelumnya tetap utuh.
- MariaDB disposable: **38 assertions lulus**, InnoDB dan MyISAM, generator native pada koneksi terkunci, DATETIME, old journals preserved, counter sekali dan lock tersedia untuk koneksi lain.
- Regresi Jurnal All: audit10, concurrency72, controller89, query135, debt-group26, dispatcher45, legacy198, metadata20, mixed20, mapping47, petty113, revision24, PengembalianKasbon16; client54 lulus.
- Pembayaran material: batch1 248, batch2 581, selection98, journal-validator43 dan invalid-journal regression lulus.
- PHP lint pada file perubahan aktif dan pemeriksaan whitespace/diff lulus. Browser tidak dijalankan sesuai instruksi.

Baseline yang sudah dicatat sebelumnya tidak diuji ulang tanpa perubahan terkait: Challenger_stress invoice counter mock dan general Mysql_test migration001 missing created_by. Keduanya bukan bukti lulus pekerjaan ini. Tidak menjalankan suite remote yang dapat mengubah database aplikasi.

## Bukti development dan batas

Hasil akhir `posting-readiness-20261028.json` disimpan di luar document root pada folder artefak payment-normalization sesi ini. Status eligible, target-lock verified, posting_executed=false. Hash sebelum/sesudah identik untuk seluruh tr_jurnal7643, posting metadata5, aliases1518, jurnaltras1146, tiga detail/satu header accounting default terkait dan nol hasil VUCA terkait.

Percobaan pertama sudah memasang registry007, kemudian cek WRITE lock ditolak oleh session diagnostik read-only. Cek diperbaiki hanya untuk LOCK/UNLOCK tanpa DML, lalu dikembalikan read-only dan hash dibandingkan. Ini bukan kegagalan permission writer aplikasi.

Tidak memindahkan/menghapus jurnal default, tidak merekonsiliasi histori posted, dan tidak menjalankan posting aktual. Jika target legacy gagal sebagian saat digunakan, pemeriksaan manual diperlukan sebelum percobaan lain. Perubahan lokal belum di-commit atau di-push pada pekerjaan ini; workspace juga memiliki perubahan lain di luar scope.
