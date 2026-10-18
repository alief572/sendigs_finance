# UI invoicing SENDIGS

Implementasi lokal selesai, memakai antislop core + antislop-ui dengan mode **during**, dipilih user pada sesi ini. Scope meliputi daftar konsultasi/non konsultasi, form tambah/edit/detail konsultasi versi3, dan pengaturan COA. Tidak mengubah form historis/jalur khusus Vuca/non konsultasi, dokumen cetak resmi, database, controller, query, permission backend, maupun formula.

## Design Read dan alasan

Tujuan layar adalah memindai tagihan per TOP, melengkapi dokumen invoice, lalu memeriksa nominal dan jurnal sebelum menyimpan. Identitas mengikuti DESIGN.md: workspace terang dengan biru SENDIGS #2876a3, judul slate, dan tipografi aplikasi existing. ENERGY2/RHYTHM2/MOTION1. Tidak menambah font atau aset gambar.

- Daftar berpusat pada tabel, bukan statistik/dashboard baru. Judul, tab, filter tahun/status, dan ekspor punya posisi berbeda sesuai frekuensi pemakaian. Atas koreksi user setelah redesign, aksi per baris kembali berupa ikon kecil dengan warna asli AdminLTE dan tooltip existing. Aria-label tetap dipertahankan tanpa menambah teks visual.
- Form berstruktur customer/dokumen, TOP, akun penjualan/ringkasan, lalu jurnal. Nominal rata kanan dengan tabular numerals menjadi motif dokumen finance. Total tagihan diberi penekanan biru; piutang awal tetap terlihat terpisah karena WAPU/KB berbeda dari total tampilan.
- COA dibagi menjadi piutang/pajak dan kategori penjualan, dengan konteks source/owner yang benar-benar dimuat. Sebelas select company/akun tetap searchable memakai Select2 lokal. Submit tidak tersedia ketika master kosong.
- Container tanpa bayangan, radius kecil pada field/tombol, dan warna status hanya untuk status nyata. Tidak ada gradient, glow, ilustrasi, statistik, feed atau badge dekoratif. Ikon existing Font Awesome untuk pengaturan/ekspor/aksi mewakili fungsi kontrol.
- Gutter lebar memisahkan kelompok kerja; toolbar dan tabel lebih padat. Grid menjadi satu kolom pada breakpoint1000/767. Tabel lebar berada dalam region horizontal-scroll berlabel dan focusable, sehingga tidak menyembunyikan kolom finance. Tidak menambahkan motion selain feedback request existing.

## File dan perilaku

- `assets/css/invoicing.css`: semua selector komponen dibatasi `.invoice-workspace`; tidak mengubah shell/sidebar atau modul lain. Cache-busting filemtime pada tiga view.
- `application/modules/invoicing/views/index.php`: header, toolbar, tab button dengan aria state + Arrow/Home/End, table region, label aksi, permission Add pada tautan buat nonkons, bahasa DataTables dan status loading/error/recovery. Endpoint, sepuluh kolom per tabel, server-side pagination/search, filter, ekspor dan modal existing dipertahankan.
- `application/modules/invoicing/views/consulting_invoice.php`: form/detail tersusun, field/fingerprint dan escaping tetap, nominal TOP readonly, ringkasan receivable tambahan, jurnal dan aksi. Detail tidak memuat client submit atau tombol simpan.
- `assets/js/invoicing/consulting-form.js`: preview/submit dipisah dari template, tetap memakai calculator dan endpoint existing. Hapus PPN/akun mengosongkan preview dan total debit/kredit sebelumnya. State validasi/pending/error/sukses ditampilkan, submit serentak diblok, respons kosong ditangani, konten jurnal memakai textContent.
- `application/modules/invoicing/views/consulting_settings.php`: grouping sembilan mapping, konteks company, empty-master/first-use state dan feedback penyimpanan. Kontrak GET/POST, fingerprint, CSRF existing, dan search Select2 dipertahankan.
- Tes baru: `Ui_render_test.php`, `Ui_client_test.js`, `Ui_list_client_test.js`. Rencana/checkpoint/manifest diperbarui, termasuk hash runtime baru. Tidak commit/push.

## Hasil pengujian

| Pemeriksaan | Hasil |
|---|---|
| PHP native template render | 203 checks PASS: add/edit/view × PKP/WAPU/KB, angka, escaping, required field, fingerprint, read-only, mapping, permission, dan error/empty states |
| Client form JavaScript | 38 checks PASS: preview dan reset pilihan, perbedaan gross/receivable, tanggal, teks literal, validasi, pending duplicate, rejection/network/malformed response/success |
| Client daftar JavaScript | 28 checks PASS: endpoint/filter/kolom, state loading/error/recovery, aria/tab order, Arrow/Home/End/wrapping, serta panel nonkons |
| Kalkulasi konsultasi | 72 checks PASS |
| PHP/JS parity dan receipt calculation | 35 checks PASS |
| PHP lint | Tiga view + test render PASS |
| JavaScript syntax | Seluruh inline hasil render dan external form/test script PASS |
| Stylesheet | 114 rule blocks balanced/scoped; 9 pasangan warna teks workspace PASS AA, rasio minimum4,99:1. Warna tombol aksi existing dipulihkan mengikuti permintaan user, bukan termasuk9 pasangan tersebut. |
| Manifest | Hash dependencies runtime lain tetap; inventaris/hash tiga view dan dua runtime baru diperbarui |
| Whitespace | Scoped diff dan file baru PASS |

Client tests menjalankan kode aktual dengan DOM/jQuery/deferred doubles. Ini bukan eksekusi browser atau pengujian network/database aplikasi. Tidak menjalankan suite DB303 lagi karena controller/writer/calculator/schema tidak berubah. Bukti integrasi backend sebelumnya tetap terpisah pada laporan v3 dan laporan prasyarat posting lokal.

## Delivery Gate antislop

Status berikut mengacu pada pemeriksaan source, render PHP, client tests dan stylesheet. **Browser/click-through/visual viewport tidak dijalankan**, mengikuti instruksi user. Tidak mengklaim hasil screenshot, pemeriksaan keyboard browser penuh, atau tidak adanya console error browser. R-03/R-32/R-35 dibatasi cakupan non-browser tersebut, bukan bukti visual runtime.

- R-02 PASS: placeholder pilihan memakai teks biasa, nilai belum tersedia memakai hyphen; tidak menambah em dash pada UI.
- R-03 PASS untuk struktur source: grid minmax(0,1fr), dua breakpoint, region tabel overflow-x yang focusable. Keterbatasan viewport runtime dinyatakan di atas.
- R-17 PASS: tidak menambah statistik/klaim angka operasional; angka invoice berasal dari data existing.
- R-18 PASS: tidak menambah testimonial/profil.
- R-23 PASS: tidak menambah gambar, logo, avatar, atau struktur navigasi shell. Tab jenis invoice existing dipertahankan.
- R-24 PASS: href kembali/settings/buat/ekspor tetap endpoint existing; tab menggunakan button.
- R-25 PASS: sembilan pasangan teks CSS diperiksa numerik, minimum4,99:1. Library/plugin existing tidak diaudit ulang seluruhnya.
- R-26 PASS: form, filter, ekspor, tab, dan aksi tetap terhubung ke handler existing; client state diuji38+28 checks.
- R-27 PASS: state first-use/master kosong/preview/loading/error/recovery/sukses memiliki pesan dan tindakan yang relevan; render203 checks.
- R-28 PASS: tidak menambah FAQ.
- R-32 PASS untuk source/client: native button, label/for, aria-selected/tabindex dan live status, visible focus outline; Arrow/Home/End/wrap diuji. Batas verifikasi browser dinyatakan di atas.
- R-33 PASS: implementasi tersimpan di source PHP/CSS/JS, bukan injeksi melalui console/browser.
- R-34 PASS: tidak menambah tema/toggle; mengikuti workspace terang existing.
- R-35 PASS dalam cakupan non-browser yang diminta user: template PHP dan client JavaScript aktual dijalankan; click-through browser dikecualikan secara eksplisit.
- R-36 PASS: tidak menambah klaim keamanan/compliance/performance/customer.
- R-37 PASS: Design Read/dials/rationale dicatat sebelum implementasi, berdasarkan DESIGN.md.
- R-38 PASS: tidak memasukkan fixture atau data palsu ke UI aplikasi; dummy values hanya di tes.
- R-01 PASS: tidak memakai gradient/default glow; warna mengikuti arah finance SENDIGS.
- R-04 PASS: ikon pengaturan/Excel/aksi menunjukkan fungsi; tidak menambah emoji atau ikon dekoratif.
- R-06 PASS: font aplikasi existing dipertahankan, nominal memakai tabular numerals untuk keterbacaan.
- R-07 PASS: tidak menambah tekstur/grid latar.
- R-08 PASS: tidak menambah panah dekoratif pada CTA.
- R-09 PASS: badge hanya status tagihan/journaled existing, tanpa eyebrow/pill baru.
- R-10 PASS: tidak menambah glassmorphism.
- R-12 PASS: workspace tanpa box-shadow; tidak membuat setiap elemen mengambang.
- R-13 PASS: focus state memakai outline, tidak glow dekoratif.
- R-14 PASS: tidak membuat feature/stat cards. Customer, summary dan field groups berbeda sesuai perannya.
- R-19 PASS: tidak menambah animasi loop/masuk/scroll, MOTION1 konsisten.
- R-22 PASS: tidak memakai ilustrasi.
- Liveliness PASS: ENERGY2/RHYTHM2/MOTION1; fokus daftar pada tabel, fokus form pada total tagihan, fokus settings pada mapping yang dimuat; gutter dan angka tabular memberi struktur dokumen.
- C-1 PASS: alasan warna, spacing, hierarki dan grouping tercatat dalam Design Read.
- C-2 PASS: kontrol terhubung handler/endpoint, kasus state diuji client dan render.
- C-3 PASS: setiap bagian mendukung pemeriksaan/penerbitan/mapping invoice, tanpa bagian pengisi.
- C-4 PASS untuk source/client: wrapping/grid/scroll region dan state guards diperiksa; keterbatasan runtime browser di atas tetap berlaku.
- C-5 PASS: angka operasional tetap sumber aplikasi; semua hasil test di laporan berasal dari eksekusi tool.
- R-05 PASS: komposisi header/toolbar/tabel dan customer/summary/jurnal mengikuti tugas, bukan template landing page.
- R-11 PASS: radius5px pada kontrol dan6–8px pada kelompok, tanpa pill di semua elemen.
- R-15 PASS: CTA spesifik Simpan invoice, Simpan mapping, Muat COA, Terapkan filter, Ekspor Excel.
- R-16 PASS: tidak menambah copy pemasaran/buzzword.
- R-20 PASS: motif dokumen invoice/TOP/receivable/jurnal dan kategori akun menggunakan domain kerja SENDIGS.
- R-21 PASS: tidak memaksakan dark mode.
- R-29 PASS: slate/white sebagai dasar, biru aksen, warna semantik hanya status.
- R-30 PASS: tidak meniru layout produk eksternal; mempertahankan shell aplikasi.
- R-31 PASS: alasan tiap kelompok visual dicatat di atas.

## Risiko dan batas

Tampilan visual, interaksi Select2/DataTables di browser, dan viewport nyata belum diverifikasi sesuai instruksi user. DataTables/SweetAlert tetap memakai CDN existing; task ini tidak mengganti dependensinya. Form khusus Vuca/nonkons/historis dan cetak resmi tidak direstrukturisasi. Semua perubahan backend/database sebelumnya dipertahankan. Untuk melihat stylesheet baru, refresh halaman aplikasi; filemtime membantu menghindari cache aset lama.
