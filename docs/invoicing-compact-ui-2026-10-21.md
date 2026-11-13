# Pemadatan UI add_invoice mengikuti Vuca - 2026-10-21

Status: selesai lokal; perubahan tampilan saja.

## Perubahan dan alasan

- application/modules/invoicing/views/consulting_invoice.php: class invoice-compact hanya pada mode add; TOP dan COA dikelompokkan dalam kolom kiri, ringkasan tagihan di kolom kanan. Mode edit/detail mempertahankan struktur sebelumnya.
- assets/css/invoicing.css: aturan kepadatan Vuca dipakai juga oleh invoice-compact melalui selector :is. Nilai CSS, breakpoint, ukuran kontrol, warna, focus state dan jarak Vuca tidak berubah. Ini menghindari duplikasi atau perbedaan tampilan kedua halaman.
- Panel customer memakai grid label/nilai, field tidak stretch mengikuti tinggi customer, heading/body/section/table lebih rapat, dan ringkasan dibatasi 360px. Di <=1000px layout menjadi satu kolom dengan scroll tabel lokal.
- Perlakuan PPN PKP/WAPU/Kawasan Berikat, COA, nominal, ID/field form, fingerprint, JavaScript, endpoint, controller dan writer tidak berubah.
- Arah desain tetap ENERGY 1 / RHYTHM 1 / MOTION 1. Pemadatan menggunakan struktur data yang sama, tanpa dekorasi, animasi atau tema baru.

## Validasi

- PASS 254 Ui_render_test.php: semua mode dan perlakuan PPN beserta kontrak field, angka dan escaping.
- PASS 25 Vuca_render_test.php: regresi form Vuca.
- PASS 43 Ui_client_test.js, 30 Vuca_client_test.js, 48 Save_feedback_client_test.js. Total pemeriksaan bernomor batch ini 400.
- PASS pemeriksaan DOM tambahan pada sembilan kombinasi add/edit/view dan PKP/WAPU/KB: compact hanya add, TOP/COA dikelompokkan, empat option PPN tetap ada, struktur edit/detail tetap.
- PASS kesetaraan CSS: setelah selector compact dikembalikan ke selector Vuca semula, seluruh stylesheet identik dengan snapshot sebelum revisi ini. Tidak mengubah properti CSS Vuca atau halaman lain.
- PASS lint PHP view, whitespace dan pembaruan hash view/CSS pada manifest terkait.
- Hasil integrasi 532 pemeriksaan sebelumnya dipakai kembali karena kode dan dependensi transaksi tidak berubah. Tidak mengulang pengujian database atau mengakses database produksi.

## Dokumentasi dan batasan

- docs/implementation-plan.md dan docs/task-progress.md diperbarui tanpa menghapus catatan sebelumnya.
- docs/invoicing-consulting-manifest.json mencatat revisi dan hash view/CSS; docs/invoicing-vuca-ui-manifest.json memperbarui hash CSS yang dipakai bersama.
- Tanpa browser sesuai instruksi pengguna; tidak mengklaim verifikasi screenshot atau pengukuran visual responsive. Tidak ada migrasi, commit, push atau TODO implementasi tersisa.
