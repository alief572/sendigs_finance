# System Design & Architecture
**Modul:** Report Petty Cash

Dokumen ini membedah arsitektur teknis dan rancangan algoritma agregasi data dari modul laporan Petty Cash, dengan fokus pada *Query SQL* dan logika Kalkulasi *Server-side*.

---

## 1. Arsitektur Komponen (CodeIgniter HMVC)
Lokasi Modul: `/application/modules/report_petty_cash/`

- **Controllers (`Report_petty_cash.php`):** *Entry point* yang menjembatani *View* dan *Model*. Controller ini bertugas menerima parameter dari permintaan HTTP POST (`start_date`, `end_date`), mengkalkulasi pergerakan *running balance*, serta mengembalikan *response* berupa JSON untuk DataTables maupun HTML *view* untuk ekspor ke Excel.
- **Models (`Report_petty_cash_model.php`):** Otak agregasi database. Model ini mengompilasi logika bisnis yang kompleks (seperti pengakuan biaya beda entitas dan status jurnal) ke dalam bentuk *Query Builder* dan *Raw SQL Query*.
- **Views:**
  - `index.php`: Menginisialisasi tabel laporan dan merender DataTables dengan fitur pemanggilan AJAX.
  - `export_excel.php`: Merender tabel HTML dasar tanpa CSS, di mana PHP mengatur modifikasi HTTP *Header* (`Content-Type: application/vnd.ms-excel`) sehingga *browser* mengenali keluaran sebagai format Excel (.xls).

## 2. Bedah Logika & Query SQL (Database Architecture)
Karena sistem keuangan ini tidak mengenal penyimpanan "Saldo Tetap" bulanan, sistem menerapkan pendekatan agregasi total (Total Aggregation) dari riwayat transaksi.

### 2.1. Logic Pencarian Saldo Awal (Opening Balance)
Fungsi: `get_saldo_awal($start_date)`
Mengkalkulasi semua total transaksi yang terjadi *sebelum* `start_date` yang dipilih pengguna. Rumusnya adalah `Saldo Awal = (Total Refill - Total Expense)`.

- **Total Refill Query:** 
  Menggunakan fungsi `SUM(grand_total)` dari tabel `tr_pelaporan_petty_cash`. 
  *Syarat:* Status dokumen harus *'approved'*, tanggal `< start_date`, DAN wajib lulus validasi `EXISTS` di `tr_jurnal` (via join ke tabel perantara `payment_approve`). Artinya, pengajuan pelaporan sudah dibayarkan dan jurnal terposting lunas.
- **Total Expense Query:**
  Menggunakan `SUM(d.total)` dari relasi tabel *header* `tr_expense_petty_cash` dan detailnya. 
  *Syarat Spesial Lintas Entitas:* Query dibungkus menggunakan operasi `OR` logika bisnis:
  1. Jika Company = 'STM', langsung diterima asalkan *status* = 'approved'.
  2. Jika Company != 'STM', wajib lolos pengecekan `EXISTS` bertingkat yang menelusuri alur: Detail Pelaporan ➔ Header Pelaporan ➔ Relasi Hutang Lintas Entitas (`tr_petty_cash_vuca_sustain`) ➔ Payment Approve ➔ Tr Jurnal posting.
  
### 2.2. Logic Agregasi Data Laporan (Query UNION ALL)
Fungsi: `get_report_data($start_date, $end_date)`
Bertujuan menggabungkan data pengeluaran (Expense) dan pemasukan (Refill) ke dalam satu bentuk *result set* menggunakan `UNION ALL`.

1. **Sub-Query Expense (Sebagai Kolom Kredit):** 
   - Menarik data dari `tr_expense_petty_cash`.
   - Menginjeksikan nilai statis `'Transaksi'` ke kolom *jenis_jurnal*, nilai `0` ke kolom *Debit*.
   - Mengambil nominal `total` sebagai nilai *Kredit*.
   - Filter `WHERE` menerapkan logika bisnis Lintas Entitas yang sama persis seperti pada kalkulasi *Saldo Awal*.
2. **Sub-Query Refill (Sebagai Kolom Debit):**
   - Menarik data dari `tr_pelaporan_petty_cash`.
   - Menginjeksikan nilai statis `1101-01-02` (COA Kas Kecil), `STM` (Company), `'Refill'` (jenis_jurnal).
   - Nilai *Kredit* di set `0`, sedangkan *Debit* mengambil nilai `grand_total`.
3. **Pengurutan Laporan (Sorting):**
   Seluruh gabungan data `UNION ALL` diurutkan secara waktu (*chronological order*) menggunakan sintaks `ORDER BY tanggal ASC, sort_date ASC`. `sort_date` berfungsi sebagai validasi urutan waktu hingga satuan detik (*timestamp*) jika terdapat banyak transaksi di tanggal yang sama.

### 2.3. Eksekusi Kalkulasi Berjalan (Server-Side Running Balance Loop)
Setelah hasil Query `UNION ALL` dikembalikan ke Controller, PHP mengambil alih proses hitung *running balance* menggunakan blok iterasi (looping) array:
```php
$running_balance = $saldo_awal;
foreach ($records as $row) {
    $running_balance = $running_balance + $row->debit - $row->kredit;
    // ... insert data to response array ...
}
```
Hasil akhirnya di-*format* sebagai *Currency* (menggunakan `number_format`) sebelum dikembalikan via JSON untuk dirender oleh *library* DataTables di sisi *client*.

## 3. Sistem Keamanan & Otorisasi
- **Layer Akses Kontroler:** Menggunakan fungsi *middleware* kustom CodeIgniter (misalnya, via base class `Admin_Controller`). Metode seperti `$this->auth->restrict('Permission.Name')` diterapkan secara *rigid* (kaku) di awal setiap *method* (`index`, `get_data`, `export_excel`) sehingga pencegahan kebocoran data dilakukan sejak fase validasi akses *backend*.
- **Otorisasi Tombol (UI-Level):** Tombol *Export to Excel* di-*render* secara *conditional* di *View*, disembunyikan menggunakan pengecekan boolean PHP jika `$has_download` bernilai *false*, mencegah pengguna awam mengetahui keberadaan *endpoint* export data.
