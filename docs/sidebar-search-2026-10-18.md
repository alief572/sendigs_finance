# Pencarian menu sidebar

Implementasi lokal sesuai rencana user. Input Cari menu tersedia sebelum Dashboard dalam header admin global. Semua modul yang memakai template tersebut memperoleh pencarian yang sama. Tidak mengubah generator menu, permission, backend/API, database, desain shell existing, atau pekerjaan Company SPK/pemilik jurnal. Tidak commit/push/deployment.

## Perilaku

- Pencarian lokal terhadap label menu yang sudah dirender, case-insensitive dan spasi dinormalisasi, debounce50ms. Tidak ada request jaringan, query database, hasil dari permission lain, penyimpanan query, atau endpoint tambahan.
- Cocok submenu menampilkan semua ancestor dan membukanya; cocok grup menampilkan turunannya. Entry yang sejak awal hidden/display:none dan turunannya tidak dibuka oleh pencarian.
- Label dibaca melalui clone tanpa ikon/dekorasi. DOM link dan ikon asli tidak diganti, tidak ada highlight/innerHTML, href dan event handler tetap. Kelas active tidak diubah.
- Keadaan inline display dan menu-open disimpan ketika query pertama mulai berlaku, sehingga perubahan buka/tutup user sebelum pencarian ikut dipulihkan. Clear/Escape membatalkan timer, memulihkan keadaan, mengosongkan query, lalu memfokuskan input. Escape tidak diteruskan untuk menutup sidebar mobile.
- Saat sidebar desktop dikecilkan, hanya tombol pencarian44px yang terlihat. Tombol memakai toggle offcanvas AdminLTE yang sudah ada, lalu fokus input. Tanpa JavaScript, komponen tetap hidden dan navigasi menu existing tetap tersedia.
- Input memiliki label dan petunjuk screen-reader tersendiri. Pesan Menu tidak ditemukan menggunakan role=status/aria-live, bukan deskripsi input yang dapat terbacakan pada keadaan normal.

## File dan integrasi

`themes/admin/views/header.php`: stylesheet lokal filemtime dan markup pencarian + wrapper aria-controls. `themes/admin/views/footer.php`: script lokal filemtime setelah AdminLTE. `assets/css/sidebar-search.css`: komponen scoped dan breakpoint mini tanpa mengaktifkan executive-navigation. `assets/js/sidebar-search.js`: adaptasi prototype lokal menjadi filter DOM native dan bootstrap template aktif; tidak membutuhkan plugin tambahan. Init guard mencegah listener ganda.

Tes: `themes/admin/tests/Sidebar_search_client_test.js` dan `Sidebar_search_render_test.php`. Rencana/checkpoint dan manifest khusus search diperbarui. Script prototype sebelumnya belum terhubung dan melakukan rewrite/highlight label serta reset keadaan awal page; kedua perilaku itu sengaja diganti sesuai rencana yang disetujui.

## Validasi

- **55 client checks PASS**: utama, submenu bertingkat, grup, case/spasi, karakter regex/HTML sebagai teks literal, empty state, hidden entry, debounce/latest input/clear race, Escape/fokus, mini button dan adapter toggle AdminLTE, pemulihan keadaan saat mulai search, active/href/icon/event identity, init ganda, markup hilang, serta tidak ada implementasi network.
- **60 PHP render checks PASS**, empat kombinasi module invoicing/dashboard dan menu permission fixture: penempatan sebelum Dashboard, ID unik, label/help/aria, progressive enhancement, menu yang dirender tidak bertambah, cache busting, urutan script, dan tidak mengaktifkan redesign shell.
- Lint PHP header/footer/render test dan syntax JS runtime/test PASS. CSS16 blocks seimbang/scoped; enam pasangan kontras teks/fokus/batas kontrol memenuhi threshold4,5:1 untuk teks dan3:1 untuk kontrol. Rasio terendah3,77:1 merupakan batas kontrol, bukan teks.
- Whitespace scoped/file baru PASS. Seluruh hash file invoicing dalam manifest tetap identik, termasuk owner-policy yang belum dipush. Tidak mengulang suite invoice/DB karena dependency dan kode task tersebut tidak berubah.

Tes menjalankan template PHP dan kode JS aktual dengan DOM/timer doubles, termasuk browser bootstrap adapter via VM. Bukan browser/click-through nyata. Tidak menjalankan browser sesuai instruksi user; visual viewport, transisi sidebar dan announcement screen reader aktual belum diverifikasi. Perbaikan assertion fixture jumlah menu memakai jumlah awal, bukan hitungan manual yang salah, sehingga memeriksa invariant jumlah node tanpa mengubah runtime.

## Antislop delivery gate

Design Read: kontrol pencarian utilitas pada sidebar AdminLTE aktif, ENERGY1/RHYTHM1/MOTION1. Warna gelap mengikuti sidebar existing, putih/placeholder terang, fokus biru. Tidak menambah gambar/font, statistik, feed, badge, gradient, glow, shadow, animasi loop, atau struktur navigasi baru.

- Hard gate PASS dalam cakupan non-browser: permission/source menu dipertahankan, label dan kontrol nyata, input/empty/loading debounce/clear memiliki perilaku; PHP60/client55 serta kontras numerik menjadi bukti. Tidak menggunakan em dash di copy baru. R-03/R-32/R-35 terbatas source/VM/render karena user mengecualikan browser.
- Purpose gate PASS: ikon search untuk pencarian, times untuk clear; focus outline untuk akses keyboard; breakpoint hanya mengubah komponen search saat mini. CSS seluruhnya dibatasi komponen, tanpa aktivasi stylesheet shell lain.
- Liveliness PASS: fokus pada satu input pencarian, spacing14/10/12 membedakan kontrol dari daftar, warna/sidebar dan font existing dipertahankan. Dials sesuai utilitas kecil tanpa dekorasi tambahan.
- Craftsmanship PASS dalam cakupan pengujian: perilaku bertingkat, state restore, controls, data asli dan progressive enhancement diuji. Tidak membuat klaim screenshot, browser, atau audit permission generator baru; generator existing tidak dimodifikasi.

Tidak ada TODO implementasi. Batas tersisa adalah verifikasi visual/runtime browser yang tidak diminta, serta dukungan menu dinamis yang ditambahkan sesudah init di luar scope; navigasi saat ini dibentuk server per halaman.
