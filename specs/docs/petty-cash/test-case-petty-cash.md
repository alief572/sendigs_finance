# Test case: Petty Cash (alur penuh)

Dijalankan di mockup `index.html`. Klik **Reset data** dulu supaya nomor sama dengan hasil yang diharapkan. Tanggal hari ini di mockup: 23-Nov-2026.

## Data awal

| Data | Keterangan |
|---|---|
| Petty Cash (1 master, semua company) | Budget 5.000.000, COA Petty Cash 1101-01-02 Kas Kecil |
| RPC-2026-0040 (STM, 9–13 Nov) | Approved, refill dibayar 17-Nov (PGJ-0078, BK-1126-0022). Pencatatan PCP-0090/0091 punya jurnal lama (dibuat saat simpan) |
| RPC-2026-0039 (VUCA, 2–6 Nov) | Approved, refill dibayar (PGJ-0077, BK-1126-0021). **PHP-2026-0011** Waiting Payment (350.000), sudah di-approve di **PGJ-0079** (VUCA), belum dibayar |
| PCP-2026-0101 / 0102 / 0103 (STM) | Draft, 17 / 18 / 19-Nov, 350.000 / 450.000 / 500.000 |
| PCP-2026-0106 (STM) | Draft, 23-Nov, 50.000 (minggu ini) |
| RPC-2026-0042 (VUCA, 16–20 Nov) | Waiting Approval, Saldo Petty Cash 2.650.000, saldo seharusnya 2.700.000, selisih −50.000 |

## Alur utama

| No | Menu | Langkah | Hasil yang diharapkan |
|---|---|---|---|
| PC-00 | Master Petty Cash | Daftar hanya 1 master (Petty Cash) → Edit | Di bawah Keterangan ada **COA Petty Cash** (terisi 1101-01-02 Kas Kecil). Kosongkan → Simpan ditolak (wajib). Daftar master punya kolom COA Petty Cash |
| PC-01 | Transaksi Petty Cash | Tambah Pencatatan → pilih master (hanya 1) → halaman form → Simpan kosong | **Validasi Gagal**: Company wajib dipilih, Request By wajib diisi, Baris 1: Jumlah / Nominal / Evidence (seperti sistem). Keterangan opsional |
| PC-01a | (lanjutan) | Company **VUCA**, Request By Dewi, COA 6101-03-01 (Pengeluaran terisi ATK), Jumlah 2, ketik Nominal **50000** langsung | Angka bisa diketik tanpa klik ulang; Total 100.000, Grand Total, Sisa Budget, Simulasi Jurnal (Sisi VUCA + Sisi STM Inter-Company) ikut berubah; keluar dari field → 50.000 |
| PC-01b | (lanjutan) | Upload evidence → Simpan | Draft PCP baru company VUCA; belum ada jurnal (to-be). View: Journal Status "Belum ada" |
| PC-02 | (lanjutan) | Edit pencatatan baru (halaman form terisi) → Simpan | Tersimpan; tidak ada jurnal yang dihapus / dibuat ulang |
| PC-03 | (lanjutan) | Centang PCP-0101, 0102, 0103 → **Buat Pelaporan** (badge 3) → Ya, Buat | RPC-2026-0043 draft dibuat, tampil Detail Pelaporan. Centang pencatatan beda minggu → ditolak "Pelaporan hanya dapat berisi pencatatan dari minggu yang sama" |
| PC-03a | Pelaporan Petty Cash | RPC-2026-0043 → **Ajukan** | Halaman Konfirmasi: Grand Total 1.300.000, saldo seharusnya **2.650.000** (5.000.000 − 1.300.000 − 50.000 STM − 1.000.000 VUCA belum di-refill), Preview Jurnal per pencatatan bertanggal 17/11, 18/11, 19/11/2026 |
| PC-04 | (lanjutan) | Isi Saldo Petty Cash **2.600.000** → Ajukan | Selisih −50.000 dan **kotak peringatan** "Ada selisih saldo"; dialog Ajukan juga memperingatkan; tetap bisa Ajukan → status Waiting Approval. Isi 2.650.000 → peringatan hilang |
| PC-05 | Pelaporan Petty Cash | Print RPC-2026-0043 | Laporan berisi daftar pencatatan, Grand Total, Saldo seharusnya, Saldo Petty Cash, Selisih |
| PC-05b | Approval Pelaporan | Lihat daftar | Baris RPC-2026-0043 dan 0042 disorot kuning, "Perlu dicek" |
| PC-05a | Approval Pelaporan | Approval RPC-2026-0043 | Kotak **Preview Jurnal (dibentuk saat Approve)**: 3 jurnal bertanggal 17 / 18 / 19-Nov, Kredit COA Petty Cash master (1101-01-02 Kas Kecil), total debit, info Request Payment refill 1.300.000 |
| PC-06 | Approval Pelaporan | Approval RPC-2026-0043 → Approve | Halaman menampilkan peringatan "Dept Head Finance wajib mengecek"; dialog Approve menyebut selisih; tetap bisa Approve. 3 jurnal pending di Jurnal All bertanggal 17 / 18 / 19-Nov; dokumen Request Payment refill 1.300.000 (company STM) di menu Request Payment |
| PC-07 | (lanjutan) | Approval RPC-2026-0042 (VUCA) → Approve | Per pencatatan 2 jurnal (buku VUCA + buku STM); refill 1.000.000; **PHP-2026-0012** draft |
| PC-08 | Petty Cash VUCA/Sustain | PHP-2026-0012 → **Payment Hutang** (halaman konfirmasi) → Proses Payment Hutang | Masuk **Request Payment** (Petty Cash Hutang, company VUCA, rekening STM); status Waiting Payment |
| PC-08a | Request Payment | Lihat daftar | 3 dokumen: RPC-2026-0043, RPC-2026-0042 (keduanya company **STM**, rekening pemegang petty cash), PHP-2026-0012 (VUCA) |
| PC-08b | Request Payment | Centang RPC-0043 → PHP-0012 terkunci; centang RPC-0042 → **Atur Pembayaran & Ajukan** | 2 transfer (1 dokumen = 1 transfer), rekening terisi dari dokumen |
| PC-08c | Atur Pembayaran | Centang keduanya → Gabungkan jadi 1 transfer → Ajukan | Rekening dikosongkan; Ajukan ditolak sampai rekening diisi |
| PC-08d | (lanjutan) | Isi BCA 436.300.9911 Erna Susanti, admin 2.500 ditanggung Perusahaan → Ajukan | **PGJ-0080** masuk Approval; keluar bank 2.302.500. Pelaporan: refill "Approval RP PGJ-0080" |
| PC-08e | Request Payment | PHP-0012 → Atur Pembayaran & Ajukan | Rekening tujuan = rekening STM (BCA 436.300.5287); **PGJ-0081** |
| PC-08f | Approval Request Payment | PGJ-0081 → Reject PHP-0012 → Simpan Keputusan (alasan wajib) | Dokumen kembali ke Request Payment dengan alasan; Record: PGJ-0081 Ditolak |
| PC-08g | Approval Request Payment | PGJ-0080 → Simpan Keputusan | 1 transfer disetujui; Record: "Belum dibayar 1 dari 1" |
| PC-09 | Payment List | Payment → Pembayaran Non PR → pilih transfer PGJ-0080 / Transfer 1 → Proses | Form Payment: No Transaksi **BK-1126-0023**, 1 nomor untuk 2 dokumen. Submit tanpa bank / bukti bayar ditolak |
| PC-09a | (lanjutan) | Pilih bank BCA STM, upload bukti; ubah Bank Charge 6.500 → Submit | Ditolak: keterangan wajib karena bank charge beda. Kembalikan 2.500 → Submit |
| PC-09b | (lanjutan) | Lihat hasil | 1 jurnal Refill Pettycash BK-1126-0023: Dr 1101-01-02 1.300.000 + 1.000.000, Dr Administrasi Bank 2.500, Cr 1101-02-09 2.302.500. Master: Sisa Budget 4.950.000. Pelaporan: refill Dibayar + nomor BK |
| PC-10 | Payment List | Payment → Pembayaran Non PR → PGJ-0079 / Transfer 1 (PHP-0011) → Proses | Pilih Bank berisi rekening **VUCA**; **Bank STM penerima** terisi 1101-02-09 (dari rekening tujuan) |
| PC-10a | (lanjutan) | Pilih bank Mandiri VUCA (1101-02-01), upload bukti → Submit | BK-1126-0024, 2 jurnal: VUCA Dr **2103-01-01 Hutang ke STM** / Cr **1101-02-01** (bank yang dipilih); STM Dr 1101-02-09 / Cr **1103-01-01 Piutang VUCA**. Tidak ada 2203-01-01. PHP-0011 Done Payment |
| PC-11 | Jurnal All · Petty Cash | Filter Pending → Posting | Jurnal berstatus Posted |

## Uji negatif

| No | Langkah | Hasil yang diharapkan |
|---|---|---|
| PC-N1 | Simpan pencatatan tanpa evidence | Ditolak: "Baris n: Evidence wajib diupload" |
| PC-N2 | Grand Total melebihi Sisa Budget | Peringatan, tetap bisa disimpan (seperti sekarang) |
| PC-N3 | Centang pencatatan beda company / beda minggu | Beda company: baris dikunci. Beda minggu: ditolak saat Buat Pelaporan (seperti sistem) |
| PC-N4 | Ajukan tanpa Saldo Petty Cash | Ditolak |
| PC-N5 | Reject dengan alasan kurang dari 10 karakter | Ditolak |
| PC-N6 | Reject dengan alasan lengkap | Pencatatan kembali draft; tidak ada jurnal yang dibuat atau dihapus |
| PC-N7 | Submit payment tanpa bank / bukti bayar | Ditolak |
| PC-N8 | Request Payment: centang 2 company berbeda | Baris company lain dikunci |
| PC-N9 | Atur Pembayaran: transfer gabungan tanpa rekening tujuan | Ajukan ditolak |
| PC-N10 | Approval Request Payment: reject tanpa alasan | Ditolak |
| PC-N11 | Payment Hutang tanpa Bank STM penerima | Ditolak |
