# Prasyarat posting konsultasi pada database lokal

Status: selesai pada 18 Oktober 2026. Scope hanya Docker MySQL lokal aplikasi; tidak commit/push atau deployment remote. Tidak membuat atau memposting invoice/penerimaan percobaan pada tabel aplikasi.

## Perubahan database

Finance `db_sendigs_ss.tr_kartu_piutang` dan tabel `javh`, `jarh`, `jurnal`, `pastibisa_tb_cabang` pada masing-masing `db_sendigs_ss_stm`, `db_sendigs_ss_sustain`, `db_sendigs_ss_vuca` dikonversi dari MyISAM ke InnoDB. Total 13 tabel. Dependencies finance lain yang dipakai invoice, penerimaan, dan posting sudah InnoDB dan diperiksa kembali.

Pada tiga tabel GL `jurnal`, `debet` dan `kredit` berubah dari BIGINT nullable menjadi DECIMAL(22,2) nullable. Presisi ini menampung rentang integer lama dan pecahan sen. Nama company `nm_perusahaan` diperlebar dari VARCHAR(11) ke VARCHAR(255), nama customer `jarh.customer` dari VARCHAR(30) ke VARCHAR(255), serta keterangan kartu piutang dari VARCHAR(150) ke VARCHAR(350). Tipe/default/nullability lain dipertahankan. Tidak mengubah master COA, mapping pilihan user, saldo, nomor counter, atau nominal historis.

Server identity finance dan ketiga accounting identik; cabang101 masing-masing tersedia tepat satu baris. Account koneksi finance memiliki privilege SELECT/INSERT/UPDATE/DELETE dan DDL lintas schema yang diperlukan. Tidak perlu menambah grant atau mengganti credential. Tidak ditemukan trigger pada tabel mutasi posting yang menulis ke dependencies tersembunyi. Tidak mengubah SQL mode global; pengecualian zero-date hanya pada session ALTER untuk mempertahankan tanggal historis saat rebuild, lalu mode session dikembalikan.

## Backup dan validasi data

Backup skema dan seluruh baris 13 tabel dibuat dan dibaca ulang sebelum ALTER. Salinan di luar webroot:

`C:/Users/Febry/AppData/Local/Temp/sendigs-posting-prerequisites-20261018-121439/`

File `affected-tables.backup.json` SHA256:

`86601448408b31a1a1c14c3bbd7e060b86d704801f3c4774c772c1126f9483b2`

Salinan Windows cocok dengan backup container. `verification.json` mencatat engine, jumlah baris, dan kesamaan nilai sebelum/sesudah. Fingerprint seluruh data identik; hanya representasi string angka BIGINT→DECIMAL dinormalisasi sebelum dibandingkan, tanpa konversi float. Jumlah baris:

| Database | Kartu piutang | javh | jarh | jurnal | Cabang |
|---|---:|---:|---:|---:|---:|
| Finance | 3155 | — | — | — | — |
| STM | — | 369 | 272 | 2098 | 1 |
| Sustain | — | 37 | 16 | 140 | 1 |
| Vuca | — | 61 | 43 | 429 | 1 |

DDL memiliki implicit commit, sehingga tidak diklaim sebagai satu transaksi yang dapat di-rollback. Dua mutex writer aplikasi dipegang selama perbaikan. Pemeriksaan privilege awal hanya mengenali format ALL PRIVILEGES dan sempat menolak format grant eksplisit MySQL setelah seluruh ALTER sukses. Pemeriksa diperbaiki untuk menerima daftar privilege yang benar; fase verifikasi dilanjutkan terhadap backup semula tanpa mengulang ALTER.

## Pengujian kode posting aktif

`scratch/check_local_posting_clones.php --disposable-only`: **159 checks PASS** melalui CodeIgniter DB driver dan `Consulting_posting` aktual. Script membuat empat database acak dengan prefix `codex_posting_`, menyalin definisi tabel aplikasi setelah perbaikan, serta mengambil metadata cabang dan master akun sebagai sumber baca. Seluruh fixture dan jurnal pengujian hanya ditulis pada database disposable. Database tersebut dihapus di finally; pemeriksaan akhir tidak menemukan schema pengujian yang tertinggal.

- Invoice PKP, WAPU, dan Kawasan Berikat berhasil diposting di masing-masing STM/Sustain/Vuca. Nominal pecahan sen dan debit/kredit diverifikasi persis, serta posting kedua ditolak.
- Penerimaan dengan bank100,01/admin1,02/PPh2,03 berhasil diposting di ketiga ledger dengan total103,06. Customer pengujian lebih panjang dari30 karakter, tanpa pemotongan.
- Tiap ledger diuji invoice dan receipt untuk kegagalan SQL nyata pada insert jurnal menggunakan trigger disposable, serta kegagalan commit yang diinjeksi pada DB driver. Header GL, baris GL, counter cabang, kartu piutang, dan status draft finance kembali tepat ke kondisi sebelum aksi.
- Audit memakai recorder project dengan sink uji; setiap posting sukses menghasilkan satu kejadian. Ini menguji kontrak recorder, bukan persistensi audit pada aplikasi.
- PHP lint dua script serta pemeriksaan whitespace dokumen/script PASS. Tidak browser sesuai instruksi user. Tidak menjalankan ulang suite kalkulasi/historis yang kode/dependensinya tidak berubah; perubahan skema diverifikasi menggunakan definisi aktual, bukan schema fixture suite historis.

Fixture pertama memakai integer0 untuk enum `tipe_invoice`, lalu diperbaiki menjadi string'0' sesuai writer aplikasi aktual. Ini kesalahan fixture; tidak mengubah kode aplikasi.

## File dan batas penggunaan

Kode runtime aplikasi tidak berubah. Tool lokal: `scratch/local_posting_prerequisites.php` (apply/verify dengan allowlist target lokal), `scratch/check_local_posting_clones.php` (pengujian disposable). Keduanya CLI-only dan bukan bagian rilis runtime. Rencana, checkpoint dan manifest diperbarui untuk mencatat kondisi database lokal.

Pada pemeriksaan akhir, mapping user tersedia untuk source/owner1,3,6,7. Mapping source4/Vuca belum tersedia; lengkapi melalui Pengaturan COA Invoicing bila hendak memakai jalur invoice reguler yang memerlukan mapping tersebut. Mapping tidak ditebak atau diubah otomatis. Admin7 mempunyai `Jurnal_All.Manage`, yang diterima guard posting versi baru. Jalur khusus Vuca tetap mengikuti aturan existing.

Prasyarat teknis posting lokal sudah diverifikasi untuk ketiga accounting. Uji ini tidak memposting transaksi aplikasi, tidak menggantikan pilihan mapping user, dan tidak menjamin konfigurasi server lain. Backup disimpan di folder Temp Windows; pindahkan ke lokasi arsip bila perlu dipertahankan jangka panjang.
