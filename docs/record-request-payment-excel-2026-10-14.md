# Record Request Payment: Cetak ke Excel — 2026-10-14

## Scope dan hasil

User meminta tombol Cetak ke Excel sesuai referensi per item serta menghapus Excel Pengajuan dari kolom Action. HTML lampiran digunakan sebagai referensi tampilan/cakupan ekspor, bukan instruksi tambahan.

Toolbar tab Per Transaksi (Approved) kini memiliki form GET dengan pencarian dan tombol Cetak ke Excel. Aksi baris hanya View dan Cetak Dokumen apabila URL sumber valid. Ekspor batch existing tetap tersedia pada alur sebelumnya.

Endpoint `export_items_excel` memerlukan `Record_Request_Payment.View`; memakai query snapshot tabel yang sama (`tr_rp_record` join header done/non-deleted) dengan parameter filter eksplisit. Pencarian berlaku untuk semua hasil tanpa batas halaman. Ekspor tidak melakukan lookup print/source karena data laporan berasal dari snapshot.

XLSX berisi nomor pengajuan/dokumen, kategori, company, pemohon, waktu pengajuan/approval, approver, keperluan, nominal dan total. Teks memakai TYPE_STRING untuk menjaga angka berawalan nol dan mencegah formula injection. PPH23/21 mengikuti flag snapshot dan PPN mengikuti nilai pada tabel.

File dibuat sepenuhnya dalam buffer sebelum header unduhan dan audit sukses. Query failure, data kosong, atau writer exception menghasilkan pesan generik dan satu audit gagal; hasil parsial dibuang. Audit hanya menyimpan format, keberadaan pencarian, jumlah baris dan outcome/reason. Kegagalan penulis audit tidak mengubah hasil bisnis. Tidak ada mutasi database atau commit transaksi pada endpoint baca ini.

## File implementasi dan tes

- `application/modules/record_request_payment/views/items.php`: tombol toolbar/form pencarian; hapus ekspor batch di baris.
- `application/modules/record_request_payment/views/index.php`: styling toolbar mengikuti pola existing dan dapat membungkus.
- `application/modules/record_request_payment/controllers/Record_request_payment.php`: ekspor XLSX per transaksi dan audit.
- `application/modules/record_request_payment/models/Record_request_payment_model.php`: filter opsional untuk memakai query daftar yang sama tanpa mengganti input request.
- `application/modules/record_request_payment/tests/Items_export_test.php`: endpoint nyata + workbook PHPExcel nyata, inspeksi ZIP/XML; mode writer failure membuang output parsial.
- `application/modules/record_request_payment/tests/Tabs_client_test.js`, `Tabs_query_test.php`, `Tabs_test.php`: revisi aksi, query ekspor nyata, fixture nilai untuk kasus formula/PPH21/desimal.
- Dokumen rencana/checkpoint dan `docs/request-payment-tabs-manifest.json`: status/cakupan terbaru.

## Validasi

- RED client: tiga aksi masih tampil; GREEN: 75 checks, inline JavaScript hasil render dieksekusi dengan VM tanpa browser.
- Ekspor: 412 assertions kumulatif (termasuk 71 assertions fixture Tabs), ZIP/XML workbook nyata; pencarian, pagination diabaikan, snapshot/totals, desimal, PPH23/21, string/formula, array search, kosong, query gagal setiap tahap/exception, permission, audit writer gagal/exception. Mode save failure PASS.
- Query SQLite/CodeIgniter: 16 assertions. View: 46 assertions. Controller/model/recorder Tabs: 71 assertions.
- Regresi Record audit: 57 skenario PASS. Fondasi Recorder/Logger/Legacy PASS.
- Runtime `C:/xampp74/php/php.exe`; Node untuk client. Tes XLSX memakai `-d upload_tmp_dir=C:/web_dev/sendigs_finance/scratch`, setUseUploadTempDirectory, bootstrap autoload standalone dan mengabaikan E_DEPRECATED PHPExcel lama. ZIP native sandbox tidak dapat rename di virtual system temp. Inspeksi ZIP/XML langsung menghindari bug reader legacy `getLibXmlLoaderOptions`; library bersama tidak diubah. Autofilter tidak ditambahkan karena writer legacy memakai count(boolean), sedangkan freeze header tetap digunakan.
- PHP lint file yang diubah, Node syntax, manifest JSON dan scoped whitespace diperiksa pada penutupan.

## Risiko dan tindak lanjut

Tidak menguji browser atau database MySQL live sesuai instruksi. Pengujian workbook nyata memakai fixture data dan temp directory khusus sandbox. Ekspor seluruh hasil masih memakai PHPExcel existing dan memori meningkat mengikuti jumlah data. Tidak ada migrasi/dependency baru atau deployment. Implementasi selesai; bila volume produksi sangat besar, evaluasi ekspor streaming sebagai task terpisah.

## Publikasi GitHub

User mengotorisasi commit dan push pada 2026-10-14. Tujuan origin/main; HEAD awal ee7f1e27 identik dengan remote. Index terpisah dipakai agar hanya file implementasi/tes/manifest/laporan Excel serta bagian rencana/checkpoint task ini masuk commit. Hasil pengujian sebelumnya tetap berlaku karena kode tidak berubah; pemeriksaan diff dan whitespace dilakukan kembali sebelum commit. Status akhir dan SHA publikasi dicatat pada checkpoint lokal setelah remote diverifikasi.
