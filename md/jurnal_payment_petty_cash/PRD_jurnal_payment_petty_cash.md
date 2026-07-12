# Product Requirements Document (PRD)
**Modul:** Jurnal Payment Petty Cash

---

## 1. Pendahuluan
**Jurnal Payment Petty Cash** adalah modul keuangan yang berfungsi untuk me-review, memvalidasi, dan mencatatkan (*posting*) transaksi kas kecil (Petty Cash) ke dalam pembukuan (Buku Besar Akuntansi) masing-masing entitas perusahaan. 

Modul ini bertindak sebagai jembatan yang menarik data mentah (*staging data*) dari tabel jurnal sementara, dan mengirimkannya ke database akuntansi secara final.

## 2. Target Pengguna & Hak Akses
- **Pengguna Utama:** Tim Finance / Akuntansi.
- **Hak Akses yang Tersedia:** 
  - `View`: Hak untuk melihat daftar jurnal *unposted*/*posted* dan melihat laporan Buku Besar.
  - `Add` (Posting): Hak untuk mengeksekusi pencatatan jurnal ke database akuntansi.
  - `Manage`: Hak untuk merevisi atau memodifikasi status jurnal jika diperlukan.

## 3. Alur Kerja Utama (Workflow)
Modul ini dirancang cepat dan ringkas, **tidak memerlukan sistem *approval* berjenjang**. 
1. **Penarikan Data:** Sistem menampilkan seluruh transaksi Petty Cash yang belum diposting (sumber ditarik langsung dari proses atau tabel `tr_jurnal`).
2. **Review & Validasi:** Tim Finance membuka detail transaksi. Sistem akan melakukan validasi matematika (Keseimbangan Jurnal / *Balance Check*) secara otomatis.
3. **Eksekusi Posting:** Tim Finance menekan tombol **Posting**.
4. **Pencatatan Otomatis (Masuk ke Transaksi/Tras):** Sistem memproses data, men-generate Nomor Bukti Uang Keluar (BUK), memasukkan (*insert*) transaksi ke database akuntansi terkait, dan mengubah status di tabel sumber menjadi *Posted*.

## 4. Fitur & Fungsionalitas Modul

### 4.1. Manajemen Jurnal (Index & Detail)
- **Daftar Jurnal (*DataTables*):** Menampilkan daftar jurnal secara *real-time* dan responsif. Dilengkapi dengan filter nama perusahaan (Company).
- **Detail Jurnal (Modal View):** Menampilkan rincian transaksi per nomor dokumen (`no_transaksi`). 
- **Auto-Balance Checking:** Menghitung akumulasi Nominal Debit dan Nominal Kredit di setiap detail baris jurnal. Proses *posting* hanya akan aktif dan diizinkan jika **Total Debit = Total Kredit**.

### 4.2. Engine Posting (Multi-Company Routing)
Sistem memiliki kemampuan kecerdasan rute pencatatan (Routing Logic) berdasarkan Entitas (Company) dari transaksi tersebut.
- **Posting Internal (STM):** Jika transaksi murni milik entitas STM, jurnal dicatat secara langsung ke database Akuntansi STM.
- **Posting Inter-company (VUCA & SUSTAIN):** Apabila pengeluaran Petty Cash digunakan untuk talangan/kebutuhan perusahaan anak (VUCA atau SUSTAIN), sistem akan membangkitkan 2 (dua) nomor dokumen sekaligus dan memasukkan pencatatan silang ke database anak perusahaan (sebagai biaya) DAN ke database STM (sebagai piutang/pengurang kas).
- **Posting Refill (Pengisian Kembali):** Mendeteksi otomatis apabila transaksi adalah proses *top-up/refill* saldo kas kecil.

### 4.3. Buku Besar Kas Kecil (Petty Cash Ledger)
Laporan mutasi untuk melihat pergerakan uang (keluar-masuk) kas kecil.
- **Filter Periode:** Pemfilteran buku besar berdasarkan Tanggal Awal dan Tanggal Akhir.
- **Running Balance:** Kalkulasi matematis yang otomatis menampilkan `Saldo Awal` dari periode sebelumnya, dan menampilkan saldo berjalan (`Saldo Berjalan = Saldo Awal + Debit - Kredit`) di setiap baris transaksi.
- **Export to Excel:** Fitur pengunduhan laporan Buku Besar Kas Kecil dalam format `.xlsx` (menggunakan PHPExcel).

---
## 5. Batasan & Validasi Sistem (Edge Cases)
1. **Jurnal Tidak Balance:** Menghasilkan error dan menghentikan proses (Hard-block).
2. **Gagal Generate Nomor BUK:** Jika *counter* database akuntansi gagal memberikan nomor bukti baru, proses dibatalkan (Rollback).
3. **Gagal Koneksi Database Lintas Entitas:** Pada proses Inter-company, jika pencatatan sukses di DB VUCA namun gagal di DB STM, maka transaksi di DB VUCA akan dibatalkan (*Transaction Rollback*) demi menjaga konsistensi keuangan antar perusahaan.
