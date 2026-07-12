# System Design & Architecture
**Modul:** Jurnal Payment Petty Cash

Dokumen ini menjelaskan arsitektur teknis, struktur database, dan *routing logic* dari modul Jurnal Payment Petty Cash.

---

## 1. Arsitektur Database & Multi-Koneksi
Sistem ini beroperasi di lingkungan *multi-database*. Aplikasi memiliki 4 (empat) koneksi database terpisah yang digunakan dalam modul ini:
1. **Sendigs Finance DB (Utama):** Menyimpan tabel `tr_jurnal` (tabel *staging* sebelum diposting).
2. **DBACC_STM (`accounting_stm`):** Database khusus pembukuan entitas STM.
3. **DBACC_VUCA (`accounting_vuca`):** Database khusus pembukuan entitas VUCA.
4. **DBACC_SUSTAIN (`accounting_sustain`):** Database khusus pembukuan entitas SUSTAIN.

## 2. Struktur Modul (CodeIgniter HMVC)
Lokasi Modul: `/application/modules/jurnal_payment_petty_cash/`
- **Controllers:**
  - `Jurnal_payment_petty_cash.php`: Pusat *routing* (*entry point*). Mengelola view, validasi HTTP request, *balance check*, dan mengatur arah *posting* ke database yang tepat.
- **Models:**
  - `Jurnal_payment_petty_cash_model.php`: Mengelola seluruh *Query Builder* untuk CRUD dan *Transaction DB*.
  - `Jurnal_payment_petty_cash_nomor_model.php`: Mengelola fungsi *auto-increment* untuk nomor Bukti Uang Keluar (BUK).
- **Views:**
  - `index.php`: Antarmuka utama (DataTables).
  - `modal_detail.php`: HTML *partial* untuk melihat *breakdown* debet-kredit.
  - `buku_besar.php`: Antarmuka laporan mutasi saldo.

## 3. Logika Routing Posting Jurnal (Core Logic)
Proses krusial berada di fungsi `save_posting_jurnal()`. Sistem menentukan ke arah mana jurnal harus disimpan berdasarkan parameter **Perusahaan (Company)** dan **Jenis Transaksi**.

### 3.1. Skenario 1: Posting Normal (Internal STM)
Jika transaksi adalah pengeluaran biasa milik STM (`nm_company = 'STM'`).
- **Alur Data:** `tr_jurnal` (Utama) ➔ **DBACC_STM**
- **Proses:** Generate 1 Nomor BUK ➔ Insert Jurnal ke DBACC_STM ➔ Update Status `tr_jurnal` menjadi *Posted*.

### 3.2. Skenario 2: Posting Inter-company (Lintas Entitas VUCA / SUSTAIN)
Jika pengeluaran Petty Cash digunakan untuk operasional anak perusahaan (Misal: Talangan operasional VUCA oleh kas kecil STM).
- **Alur Data:** `tr_jurnal` (Utama) ➔ **DBACC_VUCA/SUSTAIN** & **DBACC_STM**
- **Proses (Cross-Posting):**
  1. Sistem *generate* 1 Nomor BUK di DB Anak (VUCA/SUSTAIN).
  2. Sistem *generate* 1 Nomor BUK di DB Induk (STM).
  3. Mencatat beban/biaya (Debit) dan hutang (Kredit) ke DB Anak.
  4. Mencatat piutang (Debit) dan kas keluar (Kredit) ke DB Induk (STM).
  5. Update Status `tr_jurnal` menjadi *Posted*.

### 3.3. Skenario 3: Posting Refill (Pengisian Kembali)
Jika transaksi diidentifikasi sebagai pengisian kas kecil (Parameter explicit `jenis_posting == 'refill'` atau keterangan baris terdeteksi mengandung kata `Refill`).
- **Alur Data:** Di-routing secara spesifik ke *single database* perusahaan terkait (Misal: Refill STM hanya masuk DBACC_STM, Refill VUCA hanya masuk DBACC_VUCA).

---

## 4. Keamanan Data & Integritas (Fail-Safe)
Sistem dilengkapi proteksi ketat untuk mencegah *corrupt data*:
1. **Validasi Balance Lapis 1 (UI):** Tombol *Posting* otomatis ter-disable (menggunakan JavaScript) apabila *Total Debit* ≠ *Total Kredit*.
2. **Validasi Balance Lapis 2 (Server-side):** Fungsi controller menolak aksi eksekusi jika *Total Debit* ≠ *Total Kredit* (mengembalikan JSON Error).
3. **Database Transaction (`trans_begin` & `trans_commit`):** Setiap skenario posting menggunakan blok *Transaction DB*. 
   - Jika proses insert gagal di salah satu tabel.
   - Jika *generator* Nomor BUK gagal.
   - Jika *update* nomor BUK (increment) gagal.
   ➔ **Sistem otomatis menjalankan `trans_rollback()` untuk menggagalkan keseluruhan *query*, memastikan tidak ada "jurnal setengah jalan" yang tersimpan.**

## 5. Flowchart Laporan Buku Besar (Petty Cash Ledger)
Algoritma pergerakan/running balance dihitung dinamis (*Server-side Calculation*).
1. Ambil Parameter `tgl_from` dan `tgl_to`.
2. Hitung **Saldo Awal**: Akumulasi (`Total Debit` - `Total Kredit`) dari seluruh transaksi berstatus *Posted* pada tanggal `< tgl_from`.
3. Tampilkan data dari `tgl_from` hingga `tgl_to`.
4. Di setiap baris (row), hitung: `Saldo Saat Ini = Saldo Baris Sebelumnya + Debit Saat Ini - Kredit Saat Ini`.
5. Ekspor data ke `.xlsx` (*PHPExcel library*) jika tombol *Export* ditekan.
