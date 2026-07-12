# Product Requirements Document (PRD)
**Modul:** Report Petty Cash

---

## 1. Pendahuluan
**Report Petty Cash** adalah modul pelaporan sentral yang berfungsi menyajikan riwayat mutasi kas kecil (Buku Besar Kas Kecil / *Running Balance Ledger*). Modul ini memberikan visibilitas komprehensif kepada tim keuangan mengenai seluruh pergerakan aliran dana kas, baik dari sisi pengisian kembali (*refill*) maupun pengeluaran operasional lintas entitas.

## 2. Target Pengguna & Hak Akses
- **Pengguna Utama:** Tim Finance / Akuntansi.
- **Hak Akses & Otorisasi:** 
  - `View` (Akses Kode: `Report_Petty_Cash.View`): Mengizinkan pengguna membuka halaman laporan, menggunakan filter tanggal, dan melihat mutasi di layar secara *real-time*.
  - `Download` (Akses Kode: `Report_Petty_Cash.Download`): Mengizinkan pengguna mengunduh / mengekspor laporan tersebut ke format Microsoft Excel (*.xlsx*).

## 3. Aturan Bisnis & Logika Keuangan (Business Rules)
Modul ini tidak sekadar menampilkan data, tetapi mengimplementasikan aturan bisnis (*business logic*) keuangan perusahaan secara akurat, khususnya untuk menangani transaksi antar-perusahaan (STM vs VUCA/SUSTAIN).

### 3.1. Definisi Pemasukan Kas (Refill)
- **Sumber Data:** Seluruh pengisian kas kecil bersumber dari tabel pelaporan (`tr_pelaporan_petty_cash`).
- **Kondisi Valid:** Sebuah *refill* baru diakui sebagai **Pemasukan (Debit)** apabila:
  1. Statusnya telah disetujui (`status = 'approved'`).
  2. Telah diajukan pembayarannya dan disahkan dalam jurnal (`tr_jurnal.sts = 1`). Ini memastikan bahwa uang dari bank sentral benar-benar sudah ditransfer ke kas kecil.

### 3.2. Definisi Pengeluaran Kas (Expense)
- **Sumber Data:** Transaksi ditarik dari pencatatan pengeluaran operasional harian (`tr_expense_petty_cash`).
- **Kondisi Valid per Entitas:**
  - **Untuk Entitas STM (Internal):** Transaksi pengeluaran langsung diakui sebagai **Pengurang Saldo (Kredit)** begitu pengajuan expense disetujui (`status = 'approved'`).
  - **Untuk Entitas VUCA / SUSTAIN (Inter-company):** Transaksi talangan pengeluaran untuk VUCA/SUSTAIN **TIDAK** langsung memotong saldo kas. Pengeluaran baru akan dicatat sebagai Kredit JIKA pengeluaran tersebut sudah direkap di akhir siklus, diajukan pembayaran hutangnya (ke STM), dan jurnal pembayaran hutang tersebut telah lunas/diposting (`tr_jurnal.sts = 1`). *Rule* ini mencegah defisit semu pada buku kas kecil.

### 3.3. Algoritma Buku Besar (Ledger Calculation)
- **Auto-Kalkulasi Saldo Awal (Opening Balance):** Sebelum menampilkan data di rentang waktu pencarian, sistem menelusuri seluruh riwayat transaksi di masa lalu. Saldo awal didapat dari `Total Valid Refill` dikurangi `Total Valid Expense` sebelum periode *Start Date* yang dipilih pengguna.
- **Running Balance:** Di setiap baris mutasi yang tampil di layar, saldo akhir baris dihitung otomatis dengan rumus: 
  **`Saldo Berjalan = Saldo Baris Sebelumnya + Nominal Debit - Nominal Kredit`**

## 4. Struktur Antarmuka & Elemen Laporan (UI/UX)
- **Filter Tanggal:** Input *Start Date* dan *End Date* (Mandatory).
- **Struktur Kolom Laporan:**
  - **No:** Nomor urut.
  - **No Transaksi:** ID unik dari dokumen (*Expense* atau *Pelaporan/Refill*).
  - **Tanggal:** Tanggal terjadinya pengeluaran / tanggal disetujuinya *refill*.
  - **COA:** Kode Chart of Accounts (COA) dari biaya, atau kode Kas (1101-01-02) untuk *Refill*.
  - **Company:** Perusahaan pengalokasi biaya (STM, VUCA, SUSTAIN).
  - **Pengeluaran & Keterangan:** Deskripsi rinci tujuan dana.
  - **Jenis Jurnal:** Label klasifikasi transaksi ("Transaksi" atau "Refill").
  - **Debit / Kredit:** Nilai nominal mutasi.
  - **Saldo:** Saldo *Running Balance* kumulatif.
- **Export Data (Excel):** Tombol *Export to Excel* menghasilkan laporan yang sama presisi bentuk tabelnya dengan tampilan di web, lengkap dengan format *Accounting/Currency* pada kolom nilai uang.
