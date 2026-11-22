# Modul penampung alokasi

Empat halaman daftar mengikuti Alokasi Kalibrasi. Data dibaca langsung dari `tr_alokasi_split`; tidak ada tabel bisnis baru, pemindahan data, rollback, atau pembuatan jurnal.

| Jenis | Menu | Route | Permission |
|---|---|---|---|
| 3 | Alokasi Pengembalian Kasbon | `alokasi_pengembalian_kasbon` | `Alokasi_Pengembalian_Kasbon.View` |
| 4 | Alokasi Mutasi | `alokasi_mutasi` | `Alokasi_Mutasi.View` |
| 5 | Alokasi Transaksi Bank | `alokasi_transaksi_bank` | `Alokasi_Transaksi_Bank.View` |
| 6 | Alokasi Pembayaran | `alokasi_pembayaran` | `Alokasi_Pembayaran.View` |

Setiap route memiliki controller sendiri, dengan `index` dan `get_alokasi`. Keempatnya memakai model `alokasi/Alokasi_penampung_model` dan view `alokasi/penampung`, agar query, validasi, dan tampilan tidak diduplikasi.

Tabel menampilkan tanggal, reference number, bank/rekening, keterangan, dan nominal **split** dua desimal. Filter tanggal/bank, pencarian, pagination 10/25/50/100, Search dan Reset tersedia. Urutan tetap tanggal terbaru lalu ID split terbaru. Master bank yang hilang tidak menghilangkan baris alokasi. Tanggal pending tampil `PEND`.

Endpoint data memakai POST DataTables (`draw`, `start`, `length`, `search[value]`, `startDate`, `endDate`, `bank`). Jenis ditetapkan controller. Parameter tidak valid menghasilkan HTTP 400; kegagalan query menghasilkan HTTP 500 dengan pesan umum. Permission diperiksa pada halaman dan endpoint. Teks tabel dan opsi bank di-escape; aset DataTables memakai file lokal yang sudah tersedia.

## Registrasi sidebar

Migrasi: `application/modules/alokasi/migrations/005_register_penampung_menus.sql`. Jalankan pada DBERP memakai klien yang berhenti ketika terjadi error, tanpa `--force`. Tidak ada migration runner otomatis.

Migrasi mencari anchor lewat route `alokasi_kalibrasi` atau `alokasi_kalibrasi/index`; tidak memakai ID tetap. Empat menu ditempatkan setelahnya, mengikuti parent/group/target/icon/pusat. Registrasi ulang tidak menduplikasi menu, permission, grant, atau pergeseran urutan. Referensi hilang menyebabkan kegagalan sebelum penulisan persisten.

Akses awal mengikuti irisan izin sidebar referensi dan guard controller `Alokasi.View`. Izin grup, langsung, dan kombinasi lintas grup diperhitungkan. Pengguna yang hanya memiliki salah satunya tidak mendapat akses tambahan. Admin tetap mengikuti bypass bawaan Auth. Perubahan izin setelah migrasi dikelola melalui fasilitas permission project.

## Hasil lokal, 21 November 2026

- Migrasi sudah commit pada DB development lokal terverifikasi `db_sendigs_ss`, melalui mapping Docker `127.0.0.1:3307`; tidak mengubah konfigurasi aplikasi.
- Menu baru ID 491–494, parent 451, urutan 5–8, permission ID 1871–1874. Akses efektif diwariskan kepada 20 pengguna; 80 grant langsung ditambahkan. Referensi dicatat sebagai bukti hasil, bukan ID yang dikunci dalam implementasi.
- Metadata menu/grant sebelumnya dipertahankan; hanya urutan menu sesudah anchor pada parent/group sama bergeser empat posisi. Backup dan hasil verifikasi lokal: `scratch/alokasi-menu-backup-20261121-154923.json`, `scratch/alokasi-menu-result-20261121.json`.
- Query model pada DB lokal PASS: jenis 3 memiliki 94 baris, jenis 4 memiliki 329, jenis 5 memiliki 347, jenis 6 memiliki 1.445. Data bisnis hanya dibaca; angka merupakan kondisi saat verifikasi.

## Pemeriksaan

```powershell
& scratch/runtime/php74/php.exe application/modules/alokasi/tests/Penampung_test.php
node application/modules/alokasi/tests/Penampung_client_test.js
& scratch/runtime/php74/php.exe application/modules/alokasi/tests/Penampung_menu_test.php --isolated-local-db
```

Tes PHP daftar: 84 pemeriksaan, termasuk SQL fixture SQLite, empat controller/permission, kegagalan query daftar/master bank, validasi, resolusi HMVC asli, aset lokal, dan render escaping. Tes JS mencakup filter/reset, escaping seluruh kolom, callback sukses/gagal, dan tidak ada retry loop. Tes migrasi: 28 pemeriksaan pada database MySQL disposable dengan struktur tabel project, termasuk idempotensi, kombinasi hak akses, guard anchor, dan rollback ketika penulisan gagal. Database fixture dihapus setelah pengujian.

Regresi parser, manual insert, view contract Alokasi, serta permission menu PHP/JS PASS. Sintaks PHP/JS dan whitespace diperiksa. Verifikasi DB lokal membuktikan query daftar/filter berjalan serta metadata lama terjaga. Tidak diuji melalui browser; belum commit, push, atau deploy ke server lain. Database tujuan lain perlu menjalankan migrasi yang sama setelah kode tersedia.

## Perbaikan UI — 21 November 2026

Skill `antislop-ui` dan core dipakai dalam mode during, sesuai pilihan pengguna. Arah desain dari DESIGN.md: workspace finance putih/slate dengan header tabel navy dan aksen biru pada aksi penerapan filter. ENERGY 2 / RHYTHM 1 / MOTION 1. Tabel menjadi fokus; tidak menambah kartu statistik, gambar, font, dependensi, atau animasi.

Perubahan terbatas pada view bersama, stylesheet `assets/css/alokasi-penampung.css`, dan dua tes terkait. Filter tanggal/bank memakai grid responsif, label Bahasa Indonesia, kontrol minimal 44px, serta tombol Terapkan filter dan Reset netral. Tabel memakai angka tabular, nominal rata kanan, pemisah baris, wrap keterangan/referensi panjang, dan scroll horizontal yang dapat difokuskan keyboard. Kontrol pencarian/pagination tetap berfungsi lewat DataTables lokal.

Jumlah alokasi berasal dari `recordsFiltered`, bukan angka tetap. Loading memakai teks dan `aria-busy`; kondisi kosong awal, pencarian tanpa hasil, dan kegagalan permintaan memiliki pesan berbeda beserta langkah berikutnya. Respons permintaan lama diabaikan agar tidak menimpa hasil/status terbaru. Pengguna dapat mencoba ulang dengan Terapkan filter. Reset juga membersihkan pencarian.

Validasi akhir UI: tes PHP meningkat dari 84 menjadi 97 cek; tes JS mencakup label, interaksi filter/reset, escaping, jumlah hasil, loading/error, respons usang, akses keyboard pada area scroll, dan scoping CSS. Render PHP empat route serta resolusi HMVC PASS. Lint PHP, sintaks JS inline/tes, struktur CSS dan whitespace PASS. Regresi View_contract_test Alokasi PASS. Hash model, migrasi dan empat controller identik dengan baseline sebelum perbaikan UI; tidak ada akses/tulisan database pada task UI ini.

### Delivery Gate antislop — cakupan CLI

- Hard Gate PASS: kontrol filter/reset/pencarian diuji dengan tes klien, input tetap memiliki label, teks tabel dan opsi bank di-escape, hasil tidak dibuat-buat. Backend dan guard tetap terbukti melalui 97 cek PHP. Sesuai instruksi pengguna, tidak ada browser click-through; penilaian visual langsung belum dilakukan.
- Purpose Gate PASS: warna/type mengikuti DESIGN.md; navy membedakan header kolom, biru menandai aksi utama, spacing memisahkan filter dan daftar. Tidak ada gradient, glow, badge dekoratif, gambar atau animasi. Seluruh selector CSS dibatasi `.allocation-workspace` dan diverifikasi tes.
- Liveliness PASS: ENERGY 2 / RHYTHM 1 / MOTION 1 terwujud melalui hierarki heading/filter/tabel, kontras header navy, nominal tabular, count nyata, dan feedback hover/fokus; tidak menambah pola dashboard di luar pekerjaan penelusuran alokasi.
- Craftsmanship PASS pada pemeriksaan CLI: handler sukses/gagal/permintaan usang, empat route, escaping, scoping, media query dan fokus teruji. Kontras dihitung memakai antislop-human/contrast-check.py: tombol putih/biru 6,70:1; header putih/navy 15,29:1; teks slate/surface 7,24:1; border input/slate pada putih 4,76:1; pesan error 7,60:1. Breakpoint 1199/767/479px, font input mobile 16px, dan kontrol minimal 44px diperiksa pada CSS; rendering browser, reflow aktual dan navigasi keyboard langsung belum diverifikasi.

Tidak ada perubahan menu, permission, data, atau alur transaksi. Belum commit/push/deploy; batas utama tetap verifikasi visual langsung yang tidak diminta pengguna.

## Paket publikasi development/main — 22 November 2026

Paket rilis berisi empat controller penampung, model/view bersama, CSS scoped, migrasi menu production, tiga tes Penampung dan dokumen ini (12 file). Perbedaan modul Alokasi lama pada development/main dipertahankan. Konfigurasi lokal, backup database, scratch, dan dokumen progres gabungan tidak termasuk paket.

Validasi pada checkout bersih: development 18 pemeriksaan dan main 16 pemeriksaan PASS, termasuk tes modul PHP 97 cek, migrasi 28 cek pada DB fixture terpisah, tes UI JavaScript, regresi parser/manual/view Alokasi, lint delapan PHP, dan sintaks JS. Development juga lulus tes permission menu PHP/JS; kedua tes itu belum tersedia di main. Konfigurasi fixture lokal diabaikan Git dan tidak dipublikasikan. Review kebenaran, keamanan, kesederhanaan, arsitektur dan performa tidak menemukan penghambat.

Publikasi kode tidak menjalankan migrasi production. SQL sidebar tetap dijalankan pengguna pada DB ERP production setelah kode tersedia. Hasil push dan commit final dicatat dalam checkpoint lokal docs/task-progress.md.
