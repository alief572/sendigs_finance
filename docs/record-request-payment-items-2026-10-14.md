# Koreksi tab per transaksi Record Request Payment

## Hasil

Halaman Request Payment kembali tanpa tab tambahan, menggunakan byte view baseline commit `406a0c5f`. Audit dan backend existing tidak dibatalkan. Modul Record kini memiliki Pengajuan Baru (Batch) sebagai default, Per Transaksi (Approved), serta Histori Lama. Layout/table/buttons Batch dan Legacy tetap; tabel items memakai styling/mini-btn Record existing.

Tab items membaca `record_request_payment/get_data_record_items` existing: snapshot approved pada header done non-deleted. Kolom dokumen/PGJ/company/request_by/waktu/approver/keperluan/nominal final, pencarian/entries/pagination. View membuka detail PGJ dengan origin `items`, cetak membuka dokumen sumber, Excel Pengajuan tetap ekspor seluruh approved PGJ. Return detail ke `record_request_payment/index?tab=items`, tanpa ketergantungan Request View; origin tidak valid ke index Record biasa.

Endpoint/model pending-items di Approval dan seluruh return context Request yang khusus penambahan tab sebelumnya dihapus. Keputusan tetap di modul Approval; permission Manage canonical tetap. Partial Request dihapus dan dipindahkan menjadi `record_request_payment/views/items.php`. Tidak ada migration/perubahan write/snapshot, ekspor seluruh hasil filter, atau browser/DB bisnis writes.

## File dan validasi

- Request: `views/rp_index.php` dikembalikan; `views/rp_items.php` dihapus. Controller/model audit tidak diubah oleh koreksi ini.
- Approval: controller/model endpoint pending-items dihapus, detail controller/view kembali ke index modulnya sendiri.
- Record: index/detail/controller detail return diperbarui; items partial baru. Endpoint/model snapshot per-item dipakai kembali tanpa perubahan kontrak.
- Empat tes Tabs dipindahkan dari Request ke Record. Tes pending endpoint yang sudah dihapus diganti cakupan lokasi/Record; seluruh tes audit existing tetap.
- Dokumen rencana/checkpoint/manifest diperbarui sebagai task terpisah, tanpa menimpa progres audit lain. Laporan tab lama merupakan histori, telah digantikan keputusan koreksi ini.

RED view test sebelum tab Record tersedia, lalu GREEN:

| Pemeriksaan | Hasil |
| --- | --- |
| Tabs_test real controller/model/recorder | 71 assertions PASS: Record permission, search/pagination/all/empty, snapshot, query/print/exception failures, writer/dedup/privacy |
| Tabs_view_test | 46 assertions PASS: tab order/default/deep link/invalid, Request tanpa tab/endpoint ekstra, hash CSS/markup Request sama, return fixed/unsafe origin, canonical Manage |
| Tabs_query_test real CodeIgniter/SQLite | 14 assertions PASS: done/non-deleted/snapshot/source ID, search/injection/paging, rejected/resubmission locking; satu batch dengan 3 approved = 1 batch row/3 item rows; mixed rejected excluded; real count query failure |
| Tabs_client_test rendered JS/VM | 72 checks PASS: seluruh scripts index Record berjalan; lazy items/legacy dan deep link, paging/search/refresh, 15 columns, 3 actions/unsafe URL/output escaping, URL state dan parameter lain |
| Regresi tiga modul | Request batch1–5 94+242+282+810+371, Approval60, Record57 = 1.916 skenario PASS |
| Sintaks/diff | 11 PHP lint PASS; Node syntax PASS; whitespace PASS |

SQLite memakai adapter numeric success PDO->mysqli seperti tes sebelumnya. Tidak mengulang fondasi audit yang tidak berubah; regresi modul lengkap dijalankan karena perpindahan UI/rute/test. Browser dan MySQL live belum diverifikasi sesuai batas sesi.

## Publikasi

Commit/push main dan sinkronisasi seluruh tree Request/Approval/Record ke development memakai alternate index. Cakupan commit aplikasi hanya tiga direktori tersebut, plus laporan/manifest task. Rencana/checkpoint lokal tetap memuat pekerjaan paralel; publikasi checkpoint dibuat dari base committed ditambah catatan task ini agar pekerjaan lain tidak ikut terkirim. Verifikasi remote branch/tree wajib sebelum penutupan.

Penutupan publikasi: main code d84a73f305e1b88c211b5cc414094ae132b902d8; development 8960ab39c0ccf036dd753a9be43f2ead249ae5c8. Seluruh tree tiga modul identik: Request d43ab781d2183da74a7724312cec96adce354138; Approval a89067f6b2cb56c7c2603bfb10c933d4e30add9f; Record db78979c47cfeb342b38fb12a452b317713bed2f. Fetch dan diff remote kosong. Publikasi tanpa force, source di luar tiga modul tidak berubah. Commit penutupan selanjutnya hanya dokumentasi, tidak mengubah tree modul.
