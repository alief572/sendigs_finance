# Diagnosis save Incoming Stok Pembelian Cash — 2026-10-14

Status: diagnosis dan perbaikan Incoming Cash selesai. User menegaskan accessories.id_stock opsional dan meminta perubahan hanya pada Incoming Stok. Tidak mengubah data bisnis, Master Barang Stok atau schema.

## Penyebab terverifikasi

Dokumen `DPM-2026-00080`, PR `PR260900002`, memiliki dua detail approved:

| Detail PR | ID master accessories | Nama stok | id_stock / kode barang | Qty PR |
| --- | --- | --- | --- | --- |
| 224 | 66 | Materai Fisik 10000 | string kosong | 100 |
| 225 | 71 | Materai Digital (Peruri) | Materai Digital | 150 |

Master ID 66 ada, namanya terisi, dan statusnya aktif. Jadi pesan "Barang tidak memiliki master stok." tidak berarti record master hilang: pemicu aktualnya adalah kode barang kosong.

Alur: `Incoming_stok::request_stok()` mengarahkan sumber cash ke `Incoming_cash_model::receive()`. `items()` mengambil nama/kode melalui `LEFT JOIN accessories a ON a.id=d.id_material`. Saat diagnosis, model menolak detail dengan qty positif jika `empty(stock_name)` atau `empty(id_stock)` pada baris 134, sebelum insert header/detail penerimaan. Setelah perbaikan, hanya pemeriksaan nama yang dipertahankan. Kode sumber tetap digunakan sebagai `tr_cost_book.kode_produk` pada baris 184.

Penyebab awal adalah ketidaksesuaian validasi: `accessories/views/add.php` baris 156–163 menjadikan Item Code / Kode Barang opsional; validasi JavaScript hanya mewajibkan kategori/nama dan controller `Accessories::add()` menyimpan `id_stock` tanpa menolak nilai kosong. Incoming Cash sebelumnya mewajibkan field ini. Kedua detail tetap ditampilkan oleh query/form Incoming, sehingga kegagalan baru terlihat ketika save.

## Bukti dan pemeriksaan

- Log aplikasi `application/logs/log-2026-10-14.php`: error Incoming Cash pada 16:17:10, sesuai screenshot user.
- Query database aktual melalui container `sendigs_ss`, konfigurasi database aplikasi, dalam `START TRANSACTION READ ONLY`, ditutup rollback: memverifikasi dokumen, detail PR, dan field master di atas.
- Query receipt Cash dengan dokumen/category terkait menghasilkan nol row; belum ada penerimaan Cash tersimpan untuk dokumen ini saat pemeriksaan.
- PHP 7.4 mengevaluasi predicate validasi yang sama terhadap nilai terverifikasi: Materai Fisik ditolak; Materai Digital lolos guard master (bukan klaim seluruh proses save akan berhasil).
- Pemeriksaan diagnosis mencakup controller, query/model, form daftar Incoming, serta validasi Master Barang Stok. Saat diagnosis belum menjalankan suite aplikasi; bukti pengujian implementasi dicatat di bawah. Tidak melakukan save ulang dokumen bisnis atau pengujian browser.

## Perbaikan dan validasi implementasi

- Perubahan produksi satu kondisi di `Incoming_cash_model.php`: hapus `empty(id_stock)` dari guard. Master tidak ditemukan atau nama kosong tetap ditolak. Tidak mengubah query, cost book, helper, transaksi atau kontrak endpoint.
- `tr_cost_book.kode_produk` dan accessories.id_stock nullable menurut metadata database aktual. Sebanyak 1.376 row cost book sudah memakai kode kosong/NULL pada pemeriksaan read-only sebelum implementasi. Helper stok menggunakan id_material, bukan kode opsional.
- Nilai kode produk tetap identik sumber: string kosong tetap kosong, NULL tetap NULL. Tidak mengganti dengan ID/nama barang atau kode buatan.
- Tambahan tes di `tests/cash_receipt_test.php` memakai schema acak `codex_incoming_test_*`, dibersihkan pada finally. RED mereproduksi pesan error lama untuk master berkode kosong sebelum perbaikan.
- GREEN: 138 assertions PASS. Kasus kosong/NULL/berkode dan receipt campuran memverifikasi receipt, identitas material, stok, histori harian, price book, outstanding, nilai/cost book; koneksi kedua membuktikan hasil committed. Trigger gagal sesudah update stok membuktikan snapshot tujuh tabel tetap identik dan outstanding tidak berubah untuk kosong maupun NULL.
- Regresi master hilang/nama kosong/NULL, approval, penerimaan parsial, outstanding, konkurensi, konversi/valuasi, PO/kasbon dan routing jurnal existing lulus. Tidak menonaktifkan atau melemahkan tes existing.
- Node `form_state_test.js` PASS; PHP lint model/tes dan pemeriksaan whitespace PASS. Percobaan GREEN pertama menemukan snapshot tes mengasumsikan kolom id pada warehouse_stock_per_day; perbaikan sorting snapshot di sisi tes menyelesaikannya, kemudian seluruh suite lulus.

## Batas dan tindak lanjut

Implementasi selesai; tidak perlu mengisi kode master66 agar lolos guard Incoming. Muat ulang halaman dan proses kembali sesuai alur biasa. Dokumen bisnis aktual belum disimpan ulang; hasil runtime dibuktikan melalui fixture MySQL terpisah. Tidak ada migration atau perubahan data bisnis, UI/Master Barang Stok/helper. User mengotorisasi commit/push perbaikan ke origin/main pada 2026-10-14; publikasi terbatas model, tes dan tiga dokumen task melalui index terpisah. Checkpoint/rencana diperbarui tanpa mengubah status pekerjaan lain.
