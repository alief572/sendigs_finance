# Jurnal dan Report Jurnal gabungan — bukti implementasi

## Follow-up indikator loading tab — 2026-10-15

- Spinner dan status Memuat tampil sinkron sebelum fetch. Overlay menutup data lama dan kontrol filter/paging/export dikunci sampai berhasil/gagal; tab tetap dapat dipindah selama loading.
- Token request menjaga respons lama tidak menghilangkan loading tab yang masih berjalan. Error menutup overlay dan mengembalikan kontrol retry. Jurnal dan Report memakai komponen yang sama.
- File: shared workspace, CSS/JS scoped, dan tes client. Asset filemtime memperbarui cache CSS/JS saat refresh. Tidak ada perubahan endpoint, query, database, atau posting.
- Validasi: RED sebelum fix; client28 dan controller74 PASS, PHP view lint/JS syntax dan whitespace. Hasil regresi backend sebelumnya tetap dipakai karena kode/dependency tidak berubah.
- Antislop-ui statis PASS: spinner menunjukkan aktivitas fetch nyata, tanpa delay palsu; role status/aria-live, reduced-motion, warna/struktur existing dipertahankan. Tidak melakukan pengujian browser.

## Hasil

Kode selesai dan terverifikasi lokal. Dua menu memakai route baru `jurnal_all` / `report_jurnal_all`, lima tab, ringkasan pending, filter tersimpan per tab, pencarian header/COA, paging, modal, dan Excel seluruh detail hasil filter. Jurnal posted dibaca dari source sts1; nomor source diberi label jelas, timestamp/user posting yang tidak tersedia ditampilkan —.

Expense Report tidak diduplikasi ke Payment. Refill/Payment Hutang Petty Cash dipresentasikan di Pembayaran dengan sumber dan handler PCS tetap. Company filter pada PCS tidak memotong sisi inter-company. ID/kunci transaksi native dipisahkan dari nomor dokumen tampilan. Perhitungan cents dan formatting string tidak melewati float untuk total/tampilan baru; overflow integer ditolak.

## File dan alasan

- `application/modules/jurnal_all/`: model query bersama, controller, dispatcher HMVC, view workspace/preview/export, migration/rollback, README, dan tes. Adapter menyediakan integrasi tanpa menyalin mesin posting.
- `application/modules/report_jurnal_all/`: controller mode read-only dan README; menggunakan model/view bersama agar kategori, filter, dan angka konsisten.
- `assets/css/jurnal-all.css` / `assets/js/jurnal-all.js`: layout mengikuti HTML reference, seluruh CSS scoped, state tab/paging/modal dan pengendalian respons terlambat/submit ganda.
- `application/config/routes.php`:18 exact entry redirects (root/index lima source dan empat report). Handler POST, `/jurnal`, dan Buku Besar tidak dialihkan.
- INV controller/model, RCV controller/model, Payment controller, PCS controller/model: guard permission/POST serta pemeriksaan begin/write/commit minimum. Rumus, nomor/counter, database company, header/detail, dan urutan commit source tetap.
- Expense controller: menerima permission gabungan dan tidak menutup scope audit pemanggil HMVC. Tidak mengubah model/posting Expense.
- `docs/implementation-plan.md`, `docs/task-progress.md`, dan manifest: rencana/checkpoint/kontrak serta hash file untuk kelanjutan pekerjaan.

Preview menggunakan renderer escaped bersama, bukan meneruskan HTML source yang belum seluruhnya escaped. Nilai dan kemampuan tetap mengikuti source; resolver detail PCS native digunakan untuk nama COA/reference. Payment mempertahankan textarea UI asal dan authoritative database native. Revisi INV/RCV tetap memakai handler lama; PCS tidak diekspos karena endpoint legacy tidak punya implementasi model dan tidak tampil di UI asal.

## Validasi penutupan

| Pemeriksaan | Hasil |
|---|---|
| Query source/filter/detail/search/export SQLite + CI builder |40 assertions PASS|
| Dispatcher identitas/native handler/lock/POST restore |41 assertions PASS|
| Handler INV/RCV/PAY asli, routing company dan titik gagal |168 assertions PASS|
| Handler/model PCS asli: single/refill/inter-company, balance/COA, begin/write/commit |89 assertions PASS|
| Controller permission/report read-only/routes/render/export escaping |74 assertions PASS|
| Recorder asli + nested Expense + commit/writer failure |8 assertions PASS|
| Handler Revisi asli: payload, update/transaksi/commit failure |24 assertions PASS|
| Client tanpa browser: state tab/filter/paging/modal/double submit/late response/precision |18 assertions PASS|
| MariaDB10.4 fixture: migration dua-run, rollback, admin-only, query/export/search nyata |12 assertions PASS|
| Total tes fitur baru |474 assertions PASS|
| Regresi Jurnal Expense / Report Jurnal Expense |167 +93 assertions/checks PASS|
| Regresi PCS |600 property iterations PASS; routing dan unclear-COA checks PASS|
| Recorder/logger/legacy audit existing |3 suites PASS|
| PHP / JavaScript / PowerShell |24 PHP lint PASS; JS syntax dan runner parse PASS|

Suite dijalankan setelah perubahan kode terakhir yang memengaruhi cakupannya. Baseline Expense167 PASS sebelum implementasi, dan RED query gabungan diverifikasi sebelum model dibuat. Tidak ada tes dihapus/dilemahkan. Pemeriksaan whitespace/JSON dan review diff dilakukan pada penutupan dokumentasi.

Koneksi port3307/Docker awal terhambat. Pengujian native diselesaikan dengan MariaDB XAMPP pada localhost33317 dan datadir scratch terisolasi; akses socket dijalankan melalui approval sandbox. Runner memakai password acak, membuat/menghapus hanya schema `codex_journal_test_*`, lalu menghentikan proses miliknya dan membersihkan datadir. Tidak ada database aplikasi atau posting accounting nyata yang dipakai.

## Pemeriksaan UI statis (antislop)

- PASS arah/desain: `Jurnal ALL.html` adalah acuan user; ENERGY1/RHYTHM2/MOTION1 sesuai workspace pemeriksaan jurnal. Navigasi aplikasi existing tetap digunakan.
- PASS aset/konten: tidak membuat logo, foto, statistik demo, atau aset eksternal. Semua angka ringkasan berasal dari query source.
- PASS komposisi/purpose: ringkasan untuk antrean pending, tab untuk sumber, satu panel daftar dan satu modal detail; warna kategori mengikuti reference dan membantu identifikasi.
- PASS kontrol: Search/Reset/Excel/paging/tab/preview/post/revisi memiliki handler; test client18 mencakup state penting dan request asynchronous. Button write juga dibatasi server.
- PASS escaping/presisi: preview/table/export escaped; Excel text formula injection dicegah; cents/string formatting diuji.
- PASS keyboard/responsive dari inspeksi source: focus visible, label input, tab arrow/Home/End, aria-selected/busy/live, Bootstrap dialog existing, horizontal table overflow dan breakpoint640/1100px. Tidak mengklaim pengujian visual browser.

## Pemasangan dan batas

Jalankan migration `jurnal_all/migrations/001_register_menu.sql` pada database ERP target bersama deployment kode. Script sudah diuji idempotent/rollback pada fixture; **belum diterapkan ke database aplikasi**. Permission gabungan tidak diberikan ke user/role lama secara otomatis; admin memiliki bypass existing. Pastikan dua anchor menu Invoicing tersedia. Rollback SQL mengembalikan visibility exact; rollback fitur lengkap juga memerlukan pengembalian route entry.

Tidak melakukan commit/push, deploy, browser test, posting live, migrasi engine, atau perubahan global Auth/Menu generator. Perubahan lokal task lain dipertahankan.

Risiko existing lintas DB/MyISAM tetap: crash/commit ambigu atau legacy direct posting yang tidak mengikuti lock gabungan belum atomik. Kegagalan commit tidak dinyatakan sukses, tetapi source yang sudah commit sebelum target gagal tetap membutuhkan pemeriksaan administrator. Status report adalah status source, bukan rekonsiliasi accounting. Tidak ada TODO kode fitur yang diketahui; langkah pemasangan adalah menerapkan SQL dan memberikan akses non-admin bila diperlukan.

## Follow-up Keterangan Invoicing — 2026-10-15

Kolom Keterangan daftar INV, baik pending maupun report posted, hanya berisi nama customer - nama project. Project invoice kosong memakai nama project SPK yang sudah di-join; komponen kosong diabaikan tanpa separator berlebih. Detail COA/Excel tetap teks asli source dan tab Penerimaan tidak berubah. File kode: model shared dan dua tes query/MariaDB; tidak mengubah handler posting.

RED diverifikasi sebelum fix. Hasil akhir: query48/MariaDB14/controller74/dispatcher41/audit8/native168/PCS89/revisi24/client28 (494 assertions), PHP lint3, JSON/hash dan whitespace PASS. Instance/schema/datadir MariaDB fixture dibersihkan; tanpa browser atau DB aplikasi. Tidak ada TODO kode.

## Follow-up nomor Invoicing — 2026-10-15

No. Transaksi pada INV memakai nomor invoice dan No. Referensi memakai ID SPK Penawaran. Tampilan daftar/report, metadata modal, Excel dan urutan/pencarian konsisten. No. transaksi native tetap disimpan sebagai identitas grouping/dispatcher; field display_no_transaksi memisahkan presentasi, dan invoice_number menjaga COA preview tidak memakai ID SPK sebagai referensi invoice. SPK yang tidak tersedia ditampilkan kosong. Tab lain dan payload posting tidak diubah.

File: model shared, JS, preview/export views dan empat tes. RED diverifikasi, lalu505 assertions fitur PASS (query55/controller76/client29/MariaDB15/dispatcher41/audit8/native168/PCS89/revisi24); PHP lint6 dan JS syntax PASS. Database MariaDB fixture/schema/datadir dibersihkan, tidak menggunakan DB aplikasi/browser. Manifest/hash/checkpoint diperbarui; tidak ada TODO kode.
