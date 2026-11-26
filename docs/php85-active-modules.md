# Verifikasi modul aktif pada PHP 8.5.13

## Cakupan

Inventaris diambil dari `db_sendigs_ss.menus` pada MySQL Docker lokal dalam transaksi read-only. Dari 362 baris, 266 berstatus aktif. Setelah tautan kosong, eksternal, dan duplikat disaring, tersisa 230 rute menuju 175 folder modul serta beberapa tautan lama tanpa controller. Sidebar grup 1 dengan semua induk aktif memuat 107 rute unik.

Router HMVC dalam image Linux menemukan controller untuk seluruh 107 rute sidebar. Metode `index` untuk `report_jurnal_all` diwarisi dari `Jurnal_all`. Empat tautan berstatus aktif berada di luar sidebar tersebut dan gagal menemukan controller pada Linux: `foh_cost`, `rate_profit`, `logistic_cost`, dan `history_quotation`. Tiga yang pertama tidak memiliki folder modul. Yang terakhir memiliki file controller dengan huruf awal kecil, sedangkan router mencari `History_quotation.php`.

## Perbaikan

- Pulihkan perubahan kompatibilitas yang sebelumnya diuji di branch upgrade untuk modul aktif, termasuk adapter PDF/XLSX, penanganan nilai kosong, dan fixture tes PHP 8.5. Terapkan perubahan dengan basis bersama agar fitur yang baru disalin dari `development` tetap ikut.
- Sertakan helper override company yang dipanggil oleh modul Payment, aset JavaScript/CSS yang dipakai view baru, dan dua gambar kop surat piutang yang dirujuk oleh template.
- Sertakan aturan Apache yang menolak akses langsung ke file bukti penerimaan invoice. Jalur unduh tetap melalui controller yang memeriksa izin.
- Pada ekspor XLSX Jurnal Pembayaran, gunakan huruf kolom yang sama untuk mengisi nilai dan lebar. Nama kolom kompatibel dengan adapter PHPExcel dan PhpSpreadsheet 5 yang memakai indeks numerik mulai dari 1.

## Bukti verifikasi

- Dockerfile membangun image Apache PHP 8.5.13. Pemeriksaan persyaratan platform Composer lulus dengan dependensi terkunci, termasuk mPDF 8.3.1, PhpSpreadsheet 5.12.0, dan HTML2PDF 5.3.3.
- Smoke HTTP lulus untuk routing HMVC, sesi baru dan lama, transaksi commit, dan rollback duplikat. Pembuatan dan pembacaan ulang PDF/XLSX lulus, termasuk nilai rumus dan nol di awal nomor akun.
- Apache mengembalikan HTTP 403 untuk akses langsung ke bukti invoice dan HTTP 200 untuk JavaScript kolom serta kedua gambar kop baru.
- Lint lulus pada 1.740 file PHP dari modul aktif. Dari 185 skrip regresi PHP di 28 modul, 167 lulus setelah tiga kasus yang membutuhkan MySQL dijalankan ulang dengan fixture serta tiga tes terdampak dijalankan ulang setelah perbaikan. Ekspor XLSX Jurnal Pembayaran lulus 193 assertion, Follow Up Piutang 28 cek, dan Monitoring Piutang 24 cek.
- Fixture MySQL sementara lulus untuk Incoming Stok (termasuk rollback), Debt Posting (142 assertion), dan WAPU (119 assertion).
- Delapan tes klien lulus untuk pemilihan pembayaran, pembulatan pembayaran, Jurnal All, tutorial dan bukti piutang, serta perilaku kolom pada Follow Up dan Monitoring Piutang.

## Batas verifikasi

Sepuluh skrip juga gagal pada snapshot sebelum sinkronisasi `5cbd05a9`. Kasusnya adalah Approval Perubahan Budget, Budget Periodik, empat tes Expense, Invoicing List Filter, Jurnal All Challenger, Penerimaan Uang Lookup Bootstrap, dan Request Mutasi Journal Routing. Delapan tes lain membutuhkan konfigurasi, skema, atau data fixture aplikasi untuk impor aset, Invoicing, Penerimaan Uang, Paid Status, serta dua skenario daftar Pembayaran Material.

Log dan hasil pemetaan lokal tersimpan di `scratch/php85-active-check/` pada worktree upgrade. Tes transaksi hanya menulis ke database fixture. Pemeriksaan rute memastikan controller dan metode dapat ditemukan, sedangkan fungsi bisnis diuji melalui skrip yang tersedia dan fixture terpisah.
