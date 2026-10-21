# Dropdown kolom Aksi invoicing - 2026-10-21

- Status: selesai lokal. Tidak mengubah controller/model, database, izin, perhitungan atau transaksi.
- index.php memuat action-menu.js dan memanggil enhance pada createdCell kolom option/action kedua tab. Satu delegated preDraw handler menutup menu sebelum tabel digambar ulang.
- action-menu.js mempertahankan node aksi asli, href, data-id, data-toggle/data-target dan class handler modal; hanya presentasi tombol diubah menjadi item menu berlabel. Tidak menambah aksi yang tidak dikirim server.
- Satu tombol Aksi membuka menu; menu dipasang ke body dengan posisi fixed untuk menghindari clipping scroll table, dibatasi viewport dan membuka ke atas bila perlu. Menu dikembalikan ke cell saat ditutup.
- Keyboard panah/Home/End, Escape dengan pemulihan fokus, Tab, klik luar, satu menu aktif, pemilihan aksi, scroll/resize dan redraw ditangani. Tanpa JavaScript modul, tombol server asli tetap tersedia.
- Styling menggunakan warna, radius dan focus state invoice yang sudah ada. Shadow hanya memisahkan menu mengambang dari tabel; ukuran tap target mobile minimum 44px. ENERGY 1 / RHYTHM 1 / MOTION 1.

## Validasi

- PASS 36 Action_menu_test.js: node/URL/modal attributes, enhancement idempoten, state ARIA, portal, penempatan atas/kanan, keyboard, item disabled, klik luar, pemilihan aksi, scroll/resize, satu menu aktif dan empty actions.
- PASS 55 Ui_list_client_test.js: kedua callback action column memanggil enhancer, redraw menutup menu, dan regresi filter/paginasi/tab/kolom tetap lulus.
- PASS 43 UI client, 48 shared feedback, 254 UI render dan 25 Vuca render. Total 461 pemeriksaan bernomor.
- PASS lint PHP index, syntax tiga file JavaScript, whitespace, daftar rilis dan hash manifest yang diperbarui.
- RED awal karena modul belum ada; setelah implementasi seluruh tes hijau. Ekspektasi koordinat awal dikoreksi dengan right-width=760, lalu clamp viewport diuji sebagai kasus terpisah.
- Hasil integrasi transaksi sebelumnya dipakai kembali: seluruh kode/dependensi pencatatan dan transaksi tetap sama. Tanpa browser atau akses database produksi sesuai instruksi.

## File

- Runtime: application/modules/invoicing/views/index.php, assets/js/invoicing/action-menu.js (baru), assets/css/invoicing.css.
- Tes: Action_menu_test.js (baru) dan Ui_list_client_test.js pada modul invoicing.
- RELEASE_FILES.txt ditambah dependency action-menu.js; README, implementation-plan, task-progress dan kedua manifest diperbarui.
- Batas: posisi/klik modal nyata belum diverifikasi melalui browser; pengujian DOM fixture memastikan node/atribut dan callback tetap. Tidak ada TODO implementasi; belum commit/push untuk perubahan dropdown ini.

## Revisi ikon dan warna

- action-menu.js menambahkan ikon Font Awesome dekoratif aria-hidden serta label span. Node aksi, href, class handler dan atribut modal tetap sama; klik pada ikon di dalam link tetap menutup menu dengan benar.
- Biru: buat reguler, lihat dan cetak invoice. Hijau: buat Vuca dan kwitansi. Amber: revisi. Merah: tutup, hanya jika server sudah mengirim aksi tersebut. Warna ada pada latar ikon dan hover/focus, tanpa mewarnai seluruh menu; tombol Aksi diberi tint biru.
- Tidak menambah aksi/izin/endpoint atau dependency ikon baru. Font Awesome sudah dipakai aplikasi; unknown title memakai ikon ellipsis dan teks asli, termasuk nama property prototype yang ditangani dengan own-property lookup.
- Batch ikon: 50 pemeriksaan menu dan 55 list client, syntax dua JS, pemeriksaan kontras warna dan whitespace. Hasil tes render/feedback/client lain serta integrasi sebelumnya dipakai kembali; tidak menguji browser/database, commit atau push.

## Pengaturan COA khusus admin

- Penanda admin menggunakan Auth::is_admin() yang sudah ada (membership group 1), tanpa menambahkan pengecekan username atau user ID. Izin Invoicing.Configure yang ada tetap dipertahankan.
- index.php menyembunyikan tombol dari non-admin, bahkan jika Configure diberikan. Controller coa_settings menolak akses langsung non-admin dengan HTTP 403; service save_mapping menolak POST non-admin di dalam boundary transaksi/audit.
- Tidak mengubah role user atau konfigurasi COA. Penolakan POST mempertahankan mapping dan menghasilkan tepat satu audit gagal dengan mekanisme metadata aman yang sudah ada.
- Tes RED render awal menunjukkan tombol masih terlihat untuk non-admin, lalu PASS setelah guard ditambahkan. Ui_render_test.php kini 255 pemeriksaan dan Consulting_integration_test.php kini 538 terhadap database acak sementara.
- Service COA ditambahkan ke daftar rilis agar pembatasan server ikut diperbarui saat deployment. Perubahan dropdown, ikon dan pembatasan admin disiapkan bersama untuk branch development; status publikasi dicatat setelah push.

## Publikasi development

- Commit kode: dbfaed8cbba4edc2c8864f7a522f60b002b3bdd6, parent ea95598af33aa80ce8282d50d414e5b7da1f0049.
- Candidate tree: 9c66c68506ed35aa7bb66638fb5c8de96d98b4eb. Tepat 16 path terkait task; catatan pekerjaan lain dalam dokumen bersama tidak ikut staging. Sebanyak 17 file lokal termodifikasi di luar scope tetap identik.
- Push ke origin/development berhasil tanpa force; git ls-remote mengonfirmasi commit kode. Dokumentasi hasil ini dikirim sebagai commit lanjutan.
- Tes diulang pada kandidat terisolasi dengan dependensi HEAD: 538 integrasi MariaDB sementara + 709 pemeriksaan menu/render/list/client/kalkulasi = 1.247 pemeriksaan unik; parity PHP/JS dan legacy Vuca receipt juga PASS.
- Tiga suite audit Recorder/Logger/Legacy PASS. Lint 5 PHP/3 JS, dependency rilis, conflict-marker scan, staged whitespace dan hash scope PASS. Tidak mengandalkan perubahan lokal modul lain.
- Snapshot lokal di scratch/publish-invoicing-actions-admin-20261021 tidak dipublikasikan. Tanpa browser, migrasi, database produksi atau perubahan branch lain. Tidak ada TODO implementasi/publikasi kode tersisa.
