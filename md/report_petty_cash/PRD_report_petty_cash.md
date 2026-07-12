# Product Requirements Document (PRD)
**Modul:** Report Petty Cash

---

## 1. Pendahuluan
**Report Petty Cash** adalah modul pelaporan sentral yang berfungsi menyajikan riwayat mutasi kas kecil (Buku Besar Kas Kecil / *Running Balance Ledger*). Modul ini memberikan visibilitas komprehensif kepada tim keuangan mengenai seluruh pergerakan aliran dana kas, baik dari sisi pengisian kembali (*refill*) maupun pengeluaran operasional.

## 2. Target Pengguna & Hak Akses
- **Pengguna Utama:** Tim Finance / Akuntansi.
- **Hak Akses & Otorisasi:** 
  - `View` (Akses Kode: `Report_Petty_Cash.View`): Mengizinkan pengguna membuka halaman laporan, menggunakan filter tanggal, dan melihat mutasi di layar secara *real-time*.
  - `Download` (Akses Kode: `Report_Petty_Cash.Download`): Mengizinkan pengguna mengunduh / mengekspor laporan tersebut ke format Microsoft Excel (*.xlsx*).

## 3. Aturan Bisnis & Logika Keuangan (Business Rules)
Modul ini mengimplementasikan aturan bisnis (*business logic*) keuangan perusahaan secara akurat, memastikan bahwa saldo kas kecil selalu merepresentasikan pergerakan uang yang sesungguhnya (telah diposting) di dalam sistem.

### 3.1. Definisi Pemasukan Kas (Refill / Pelaporan)
- **Sumber Data:** Pengisian kas kecil ditarik dari tabel pelaporan (`tr_pelaporan_petty_cash`).
- **Kondisi Valid:** Sebuah *refill* (dokumen RPC) diakui sebagai **Pemasukan (Debit)** apabila:
  1. Status pengajuannya telah disetujui (`status = 'approved'`).
  2. Dana pengisian ulang tersebut telah diproses dan disahkan dalam jurnal (`tr_jurnal.sts = 1`). Hal ini dibuktikan melalui validasi pembayaran di tabel `payment_approve`.

### 3.2. Definisi Pengeluaran Kas (Expense / Pencatatan)
- **Sumber Data:** Transaksi ditarik secara faktual langsung dari tabel staging jurnal pembukuan (`tr_jurnal`), mengabaikan kompleksitas pengajuan lintas entitas di hulu.
- **Kondisi Valid:** Sebuah pengeluaran (dokumen PCP) diakui sebagai **Pengurang Saldo (Kredit)** secara mutlak apabila:
  1. Termasuk dalam klasifikasi transaksi pengeluaran kas kecil (`jenis_transaksi = 'Petty Cash'`).
  2. Telah resmi disahkan dan diposting ke dalam pembukuan perusahaan (`sts = 1`).
  3. Memiliki nilai nominal pengeluaran riil di sisi debit jurnal (`debit > 0`).
  
*Catatan: Dengan menarik data langsung dari `tr_jurnal` yang sudah diposting, sistem menjamin tidak adanya "pengeluaran menggantung" (unposted) yang secara prematur dapat mengurangi saldo riil buku kas kecil.*

### 3.3. Algoritma Buku Besar (Ledger Calculation)
- **Auto-Kalkulasi Saldo Awal (Opening Balance):** Sebelum menampilkan data di rentang waktu pencarian, sistem menelusuri seluruh riwayat transaksi di masa lalu. Saldo awal didapat dari akumulasi Total Pemasukan dikurangi Total Pengeluaran *sebelum* periode awal (Start Date) yang dipilih pengguna. 
*(Saat ini, khusus untuk kalkulasi komponen pengeluaran di saldo awal, sistem masih mempergunakan algoritma pengecekan lintas entitas lama berbasis tabel `tr_expense_petty_cash`. Sinkronisasi ke `tr_jurnal` masih direncanakan di fase berikutnya).*
- **Running Balance:** Di setiap baris mutasi yang tampil di layar, saldo akhir baris dihitung otomatis secara kronologis dengan rumus: 
  **`Saldo Berjalan = Saldo Baris Sebelumnya + Nominal Debit - Nominal Kredit`**

## 4. Struktur Antarmuka & Elemen Laporan (UI/UX)
- **Filter Tanggal:** Input *Start Date* dan *End Date* (Mandatory).
- **Struktur Kolom Laporan:**
  - **No:** Nomor urut.
  - **No Transaksi:** ID unik dari dokumen (*Expense / Pencatatan* atau *Pelaporan/Refill*).
  - **Tanggal:** Waktu terjadinya transaksi pengeluaran (berdasarkan Tanggal Jurnal) atau waktu disetujuinya *refill*.
  - **COA:** Kode Chart of Accounts (COA) dari biaya, atau akun kas `1101-01-02` (jika Refill).
  - **Company:** Perusahaan pengalokasi biaya / perusahaan tempat transaksi dibebankan.
  - **Pengeluaran & Keterangan:** Deskripsi rinci alokasi atau tujuan dana.
  - **Jenis Jurnal:** Label klasifikasi transaksi ("Transaksi" atau "Refill").
  - **Debit / Kredit:** Nilai nominal mutasi transaksi.
  - **Saldo:** Saldo *Running Balance* kumulatif per baris.
- **Export Data (Excel):** Tombol *Export to Excel* merender data laporan ke format *spreadsheet* (*.xlsx*) secara dinamis dengan layout tabel yang presisi dan konversi otomatis menjadi format *currency/accounting*.
