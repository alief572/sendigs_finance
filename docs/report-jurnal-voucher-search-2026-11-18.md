# Pencarian nomor voucher Report Jurnal All

Pencarian nomor voucher lengkap pada tab Pembayaran sekarang hanya mencocokkan nomor transaksi yang tampil. Sebelumnya, ID approval internal yang kebetulan sama dengan nomor voucher lain dapat membuat transaksi yang berbeda ikut muncul.

Sebagai contoh, approval `BK--1126-0007` terhubung ke voucher `BK--1126-0015`. Mencari voucher lengkap `BK--1126-0007` tidak lagi mengambil voucher `0015` melalui ID approval tersebut. Voucher yang belum posting tetap tidak masuk report.

Filter memakai token `display_no_transaksi`, termasuk daftar voucher legacy yang dipisah koma, kode bank, kapitalisasi dan spasi tepi. Tabel, total dan ekspor Excel memakai jalur yang sama. Pencarian teks atau nomor parsial tetap luas; pending, tab report lain, resolver dan data jurnal tidak berubah.

## Perubahan

- Model: enam baris filter sebelum pencarian rincian untuk query nomor BK lengkap pada report tab PAY.
- Tes: benturan ID/voucher, kecocokan penuh, histori, refill, hutang lintas company, total/ekspor, pending dan perubahan status menjadi posted pada fixture.
- Fixture divisi periodik: tambah kolom SQLite `no_item` yang sudah dibaca model. Kegagalan fixture lama dibuktikan juga pada model tanpa patch; tidak ada perubahan skema aplikasi.

## Validasi

Checkout publikasi diverifikasi lewat PHP Docker: development lulus 14 file tes terkait; main lulus 12 file tes. Lint enam PHP, pemeriksaan whitespace, dan mutasi guard pencarian yang harus ditolak regresi benturan nomor lulus pada kedua checkout; model dipulihkan byte-identical setelah mutasi. Review correctness, readability, architecture, security dan performance tidak menemukan penghambat pada patch.

Pada main, Controller_test berhenti pada assertion `entry-only redirect jurnal_invoicing`; model baseline tanpa patch menghasilkan kegagalan yang sama dan file routing/controller terkait tidak berubah. Tes Detail_group_company khusus development tidak tersedia pada baseline main. Paket ini mempertahankan perbedaan routing dan detail company antarbranch.

Fixture memakai SQLite di memori, tanpa perubahan database bisnis. Tanpa pengujian browser atau deployment manual; push tidak membuktikan runtime server sudah memakai versi terbaru.
