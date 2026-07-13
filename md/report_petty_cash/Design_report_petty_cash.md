# System Design & Architecture
**Modul:** Report Petty Cash

Dokumen ini membedah arsitektur teknis dan rancangan algoritma agregasi data dari modul laporan Petty Cash, dengan fokus pada *Query SQL* dan logika Kalkulasi *Server-side*.

---

## 1. Arsitektur Komponen (CodeIgniter HMVC)
Lokasi Modul: `/application/modules/report_petty_cash/`

- **Controllers (`Report_petty_cash.php`):** *Entry point* yang menjembatani *View* dan *Model*. Mengendalikan logika HTTP GET/POST (`start_date`, `end_date`), menjalankan perulangan *running balance* di level *backend*, serta merespon dalam format JSON untuk DataTables atau HTML View untuk export Excel.
- **Models (`Report_petty_cash_model.php`):** Otak agregasi database. Menyatukan data dari berbagai sumber ke dalam bentuk antarmuka laporan yang tunggal dengan bantuan *Query Builder* dan fungsi `UNION ALL`.
- **Views:**
  - `index.php`: Antarmuka yang memuat input filter tanggal dan merender tabel DataTables.
  - `export_excel.php`: Modifikasi *header* PHP murni untuk mengelabui *browser* agar mengunduh tabel HTML ke dalam format MS Excel (`application/vnd.ms-excel`).

## 2. Bedah Logika & Query SQL (Database Architecture)
Karena sistem keuangan tidak menggunakan skema "Tutup Buku Bersaldo", sistem menerapkan pendekatan agregasi total (Total Aggregation) dari awal riwayat transaksi untuk menentukan saldo kas berjalan.

### 2.1. Logic Pencarian Saldo Awal (Opening Balance)
Fungsi: `get_saldo_awal($start_date)`
Mengkalkulasi saldo dari seluruh waktu *sebelum* parameter `start_date`.
- **Total Pemasukan (Debit) Query:** 
  - **Refill:** Menjumlahkan `SUM(grand_total)` dari `tr_pelaporan_petty_cash` yang berstatus *'approved'*, sebelum tanggal awal, dan dibuktikan lewat relasi `EXISTS` ke tabel `tr_jurnal` bahwa jurnal `payment_approve`-nya telah diposting (`sts = 1`).
  - **Transaksi Bank:** Menjumlahkan `SUM(transaksi)` dari tabel `tr_request_mutasi_admin` di mana dana secara riil disalurkan ke kas kecil (`bank_tujuan = '1101-01-02'`) khusus untuk entitas STM (`target_accounting = 'accounting_stm'`) sebelum tanggal awal.
- **Total Expense Query:**
  Menjumlahkan `SUM(debit)` langsung dari tabel staging `tr_jurnal` di mana `jenis_transaksi = 'Petty Cash'`, status jurnal sudah diposting (`sts = 1`), dan `debit > 0` sebelum tanggal awal. Metode ini menjamin saldo awal tersinkronisasi 100% secara matematis dengan baris data laporan.

### 2.2. Logic Agregasi Data Laporan (Query UNION ALL)
Fungsi: `get_report_data($start_date, $end_date)`
Sistem menggabungkan data pemasukan dan pengeluaran ke dalam 1 output array (*result set*) dengan menggunakan `UNION ALL`.

1. **Sub-Query Expense (Sebagai Kolom Kredit/Pengeluaran):** 
   - **Sumber Data:** Diambil faktual langsung dari tabel staging `tr_jurnal`.
   - **Filter Wajib:** `jenis_transaksi = 'Petty Cash'`, `sts = '1'` (Sudah Diposting), dan nilai mutlak pengeluaran ada (`debit > 0`). Filter tanggal merujuk secara akurat pada kolom `tgl_jurnal`.
   - **Pemetaan (Mapping):** Nilai statis `'Transaksi'` dialiaskan sebagai *jenis_jurnal*. Nilai asli dari kolom `debit` jurnal di-mapping (dipindahkan) posisinya menjadi kolom **Kredit** dalam laporan, sedangkan nilai **Debit** laporan dipaksa `0`. Data nomor transaksi, kode COA, nama pengeluaran (dari `nm_coa`), keterangan detail, serta entitas perusahaan ditarik murni secara 1:1 dari rekaman jurnal tanpa proses *join* eksternal.
2. **Sub-Query Refill (Sebagai Kolom Debit/Pemasukan Utama):**
   - **Sumber Data:** Diambil dari tabel pelaporan `tr_pelaporan_petty_cash`.
   - **Filter Wajib:** Pengajuan `status = 'approved'`. Dilengkapi relasi wajib ke tabel `tr_jurnal` (via `payment_approve`) untuk memastikan dana sudah disahkan cair (`sts = 1`). Filter tanggal merujuk pada histori `approved_on`.
   - **Pemetaan (Mapping):** Kolom COA direkayasa statis menjadi `1101-01-02` (Kas Kecil). Kolom **Debit** diisi penuh dari `grand_total` pencairan, dan **Kredit** di-set `0`.
3. **Sub-Query Transaksi Bank (Sebagai Kolom Debit/Pemasukan Tambahan):**
   - **Sumber Data:** Diambil dari fitur transaksi langsung admin (`tr_request_mutasi_admin`).
   - **Filter Wajib:** Terjadi di entitas STM (`target_accounting = 'accounting_stm'`) dan spesifik diarahkan masuk ke brankas kas kecil (`bank_tujuan = '1101-01-02'`).
   - **Pemetaan (Mapping):** Label kolom *jenis_jurnal* di-hardcode dengan string `'Transaksi Bank'`, COA direkayasa ke `1101-01-02`. Nilai `transaksi` IDR disalurkan seutuhnya ke kolom **Debit**, sedangkan **Kredit** di-set `0`.
4. **Pengurutan Laporan (Sorting):**
   Seluruh matriks data gabungan (UNION ALL) dari ketiga sub-query di atas diurutkan berdasarkan waktu agar tampilan laporan kronologis dan masuk akal secara finansial. Query menggunakan `ORDER BY tanggal ASC, sort_date ASC`. (Variabel `sort_date` masing-masing merujuk ke parameter urutan waktu terkecilnya).

### 2.3. Eksekusi Kalkulasi Berjalan (Server-Side Running Balance Loop)
Hasil raw query `UNION ALL` di atas belum berisi saldo berjalan. Proses akumulasi saldo ini di-kalkulasi efisien pada layer eksekusi PHP Controller.
```php
$running_balance = $saldo_awal;
foreach ($records as $row) {
    $running_balance = $running_balance + $row->debit - $row->kredit;
    $row->saldo = number_format($running_balance, 2);
}
```
Arsitektur terpisah ini dirancang untuk memastikan beban kalkulasi finansial tidak terlalu memberatkan *resource query planner* memori database, dan jauh lebih luwes (*flexible*) saat merender komponen antarmuka tabel.

## 3. Sistem Keamanan & Otorisasi
- **Layer Akses Kontroler:** Menggunakan fungsi *middleware* kustom (contoh `$this->auth->restrict()`) yang diblok di awal *method* konstruktor. Eksekusi kode akan ditolak sejak fase inisialisasi jika *Role* tidak sesuai.
- **Otorisasi Elemen Antarmuka (UI-Level):** Fitur vital ekspor Excel hanya ditampilkan (di-render) apabila akun login mengantongi variabel boolean hak akses `$has_download == true`. Metode ini secara pasif mengurangi celah keamanan data internal dari tindakan unduh paksa oleh akun tak berwenang.
