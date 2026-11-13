# Tabel konsultasi compact

Permintaan user berdasarkan screenshot tab Konsultasi: gabungkan informasi yang berkaitan agar tabel lebih ringkas. Implementasi lokal mengurangi10 menjadi7 kolom: No., Dokumen, Customer & Project, Company, Tim, Status, Aksi. Tidak mengubah tabel Non Konsultasi yang tetap10kolom.

Dokumen berisi nomor invoice dan SPK; invoice yang belum ada diberi teks Belum ada invoice. Customer ditampilkan sebagai informasi utama dengan project lengkap di bawahnya. Tim menampilkan label Project Leader dan Sales beserta kedua nama. Tidak memakai pemotongan teks/clamp atau menyembunyikan field. Company dan status tetap terpisah untuk pemindaian cepat; tombol tetap ikon kecil dan warna original sesuai koreksi user sebelumnya.

CSS scoped mengurangi min-width konsultasi1200→980px, menetapkan proporsi kolom dengan table-layout fixed, padding10px, dan pengelompokan teks. Nama/nomor panjang tetap dapat wrap; tabel tetap mempunyai scroll region untuk layar sempit. Tipografi/sidebar/workspace lain tidak diubah. Antislop during sesi existing: ENERGY2/RHYTHM2/MOTION1 mengikuti laporan UI, dengan grouping berdasarkan dokumen/customer/tanggung jawab, tanpa statistik/aset/dekorasi baru.

Renderer DataTables menggabungkan field response existing tanpa mengubah query, endpoint, filter, pencarian server, paginasi, Excel atau aturan satu TOP perbaris. Nilai dokumen/customer/project/company/tim di-escape sebelum menjadi HTML; status dan aksi tetap kontrak HTML existing. Backend get_data_spk mengurutkan sts_invoice/id dan tidak memproses order DataTables. Ordering client dimatikan agar tidak menampilkan affordance sorting palsu. Saved state dengan10kolom ditolak melalui count guard; state7kolom tetap diterima. Ini dapat mereset search/page/length DataTables lama pada kunjungan pertama setelah perubahan, tanpa mengubah filter/data transaksi.

File: `application/modules/invoicing/views/index.php`, `assets/css/invoicing.css`; tes `Ui_render_test.php` dan `Ui_list_client_test.js`; manifest, rencana dan checkpoint diperbarui. Tidak backend/database/browser/commit/push pada perubahan compact.

Validasi:

- Render208 checks PASS, konsultasi7kolom dan nonkons10kolom.
- List client45 checks PASS, termasuk grup field lengkap, project panjang tidak dipotong, no invoice kosong, escaping customer/company/nama, data non-display, kontrak aksi, fixed backend order dan saved state10→7; seluruh regresi tab/filter/loading/error tetap.
- Form client38 checks PASS; PHP lint index/render-test dan syntax JS list test PASS.
- CSS124 blocks scoped/seimbang dan9 pasangan kontras PASS, minimum4,99:1; scoped whitespace PASS. Hash backend/owner-policy/sidebar seluruhnya tetap identik, sehingga native backend341 dan sidebar55/60 checks sebelumnya tidak diulang.
- Tidak browser sesuai instruksi user. Perubahan jumlah/renderer diuji melalui kode aktual dengan doubles dan render PHP; tinggi/pixel layout viewport browser belum diverifikasi.

Gate antislop untuk scope ini: grouping dan hierarchy memiliki alasan domain invoice; sumber data tetap nyata dan lengkap; aksi tetap berfungsi; empty/error/accessibility source tetap; tidak ada font/gambar/gradient/animasi baru. Bukti functional/render/CSS di atas, dengan pengecualian browser sesuai instruksi user.

Permintaan push sebelumnya untuk search menu dan perbaikan Company SPK/pemilik jurnal sudah selesai secara terpisah: commit `b127c8e19d1f7bed84ac03533b27c503f3837a47` pada origin/development terverifikasi. Tabel compact belum dimasukkan ke commit tersebut. Perubahan task lain tetap dipertahankan.
