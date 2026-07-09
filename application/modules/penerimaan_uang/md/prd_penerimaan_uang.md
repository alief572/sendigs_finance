# Product Requirements Document (PRD): Modul Penerimaan Uang (Penerimaan Piutang)

## 1. Pendahuluan

### 1.1. Tujuan Dokumen
Dokumen ini berfungsi sebagai panduan teknis dan bisnis (PRD) yang sangat komprehensif untuk modul **Penerimaan Uang (Penerimaan Piutang)** pada sistem `sendigs_finance`. Dokumen ini bertujuan menyamakan pemahaman antara Product Manager, tim Developer, dan Stakeholder terkait fungsionalitas, alur data, integrasi database, hingga arsitektur kodingan modul tersebut.

### 1.2. Deskripsi Modul
Modul **Penerimaan Uang** adalah gerbang utama bagi tim Finance untuk mengalokasikan uang yang masuk ke rekening bank perusahaan dan mencatatnya sebagai pelunasan atas piutang pelanggan (*Accounts Receivable*). Modul ini menangani pemotongan saldo piutang (*invoice*), perhitungan pajak (PPN dan PPh23), pencatatan biaya administrasi bank, hingga *auto-generation* jurnal akuntansi multi-database.

### 1.3. Target Pengguna (User Persona)
- **User Utama:** Tim Finance / Account Receivable.
- **Wewenang:** Melakukan alokasi dana, memproses pelunasan invoice, melakukan pengecekan selisih bayar, mencetak jurnal penerimaan, dan mem-posting jurnal ke program terpusat (Program TRAS).

---

## 2. Alur Proses Bisnis (High-Level Business Flow)

1. **Penarikan Dana Alokasi:** Sistem menarik data penerimaan bank (mutasi masuk) dari tabel `tr_alokasi_split` dan `tr_alokasi_detail` (termasuk *backward compatibility* untuk data *legacy*).
2. **Pemilihan Customer & Invoice:** User Finance memilih Customer. Sistem akan menarik semua *invoice* yang masih *outstanding* (`saldo_piutang > 0`), baik proyek Konsultasi maupun Non-Konsultasi.
3. **Pengisian Nominal (Alokasi):** User memasukkan nominal penerimaan aktual per *invoice*, termasuk mencatatkan potongan biaya admin bank (`biaya_admin`) dan sisa tagihan jika ada (`sisa_piutang`).
4. **Validasi Kontrol (Zero Balance Check):** Sistem memastikan total alokasi yang dipecah ke *invoice* cocok dengan nominal dana masuk dari bank. Field kontrol di UI harus bernilai `0` agar proses bisa di-simpan (Save).
5. **Pencatatan Database & Jurnal (Draft):** Jika validasi sukses, sistem akan:
   - Membuat *record* di `tr_penerimaan_piutang` (Header) dan `tr_penerimaan_piutang_detail`.
   - Mengurangi `saldo_piutang` di tabel `tr_invoicing`.
   - Menandai dana di `tr_alokasi_detail/split` sebagai *Used* (`nilai_terpakai`).
   - Melahirkan Jurnal Akuntansi dengan status **Draft** (`sts = '0'`) di tabel `tr_jurnal`.
6. **Posting Jurnal:** Tim Finance mem-posting jurnal (di luar modul ini/di modul Jurnal). Status berubah menjadi `sts = '1'` dan data dikirim ke Program TRAS.
7. **Fitur Rollback:** Apabila terjadi kesalahan alokasi, User Finance dapat melakukan *Rollback* untuk mengembalikan saldo piutang dan membatalkan alokasi. *Rollback* **hanya bisa dilakukan jika jurnal masih berstatus Draft (`sts = '0'`)**.

---

## 3. Spesifikasi Fungsional & Teknis (Controller Level)

File: `application/modules/penerimaan_uang/controllers/Penerimaan_uang.php`

### 3.1. Penarikan Data Bank (DataTables)
- **Fungsi:** `get_alokasi_penerimaan()`
- **Logic:** 
  Menampilkan list uang masuk yang belum dialokasikan secara penuh. Mengambil data dari `tr_alokasi_split` di-*join* ke `tr_alokasi_detail`. Data difilter untuk transaksi tahun 2025 ke atas.
- **Status Indikator:**
  - `<span class="badge bg-yellow">Draft</span>`: Jika uang masuk belum dipakai.
  - `<span class="badge bg-green">Used</span>`: Jika uang masuk sudah di-relasikan di `tr_penerimaan_piutang`.

### 3.2. Form Alokasi & Pemilihan Invoice
- **Fungsi:** `get_inv_by_cust()`
- **Logic:**
  Menerima parameter `nm_customer` (via AJAX). Menjalankan query ke `tr_invoicing` untuk memunculkan invoice dengan `saldo_piutang > 0`. Menandai invoice khusus dengan flag `non_kons` menggunakan `<span class="label label-warning">Non Kons</span>`.

### 3.3. Pemrosesan Kalkulasi & Draft Jurnal UI
- **Fungsi:** `process_alokasi()`
- **Logic:**
  - Ini adalah *engine* kalkulasi utama sebelum di-save ke database.
  - Membaca konfigurasi pajak: `ppn_dipotong` dan `pph23_dipotong`.
  - Mengambil referensi dari Database Eksternal (`consultant`):
    - Jika Non-Konsultasi: Set otomatis company ke ID 1 (`STM-Vuca`).
    - Jika Konsultasi: Menelusuri rantai `tr_invoicing` ➔ `kons_tr_spk_penawaran` ➔ `kons_tr_penawaran` ➔ `kons_tr_company` untuk mendapatkan detail Company.
  - Mengambil referensi dari Database Eksternal (`accounting`):
    - Menarik data nama COA Bank (Bagan Akun Standar) dari `coa_master`.
  - **Generasi Tabel Dinamis Jurnal:**
    Membentuk dua pasang Jurnal (Baris Bank [Debit] dan Baris Piutang/Pajak [Kredit]). Tergantung parameter pajak, jika `tipe_invoice` adalah `1` (VUCA), maka COA PPh yang dipakai adalah `1106-01-05`, selain itu `1106-01-02`.

### 3.4. Proses Penyimpanan Utama (Save & Transaksi DB)
- **Fungsi:** `save_penerimaan_piutang()`
- **Teknis:** Dibungkus dalam blok `DB Transaction` (`$this->db->trans_begin()`) untuk menjamin konsistensi data. Jika satu tabel gagal di-insert/update, seluruh proses di-*rollback*.
- **Alur Eksekusi Data:**
  1. Generate ID Penerimaan (`generate_id()`).
  2. Resolusi ID Alokasi (`resolve_alokasi()`).
  3. Insert ke `tr_penerimaan_piutang` (Header).
  4. Looping invoice yang dilunasi:
     - Insert ke `tr_penerimaan_piutang_detail`.
     - Hitung pemotongan piutang dan Update `saldo_piutang` di tabel `tr_invoicing`.
  5. **Auto Generate Jurnal:** Membuat data di tabel `tr_jurnal` (Status `sts = 0`). Format nomor jurnal didapat dari `generate_id_invoice_jurnal()`.
  6. Update kolom `nilai_terpakai` pada `tr_alokasi_detail` dan `tr_alokasi_split` agar status di dashboard berubah menjadi *Used*.

### 3.5. Fitur Rollback
- **Fungsi:** `rollback_penerimaan()`
- **Validasi Kritis:**
  ```php
  $check_posted = $this->db->get_where('tr_jurnal', ['no_transaksi' => $no_surat, 'sts' => '1'])->num_rows();
  if ($check_posted > 0) { // Tolak Rollback }
  ```
  Tidak bisa dieksekusi jika jurnal di `tr_jurnal` sudah bernilai `sts = '1'` (Posted ke program TRAS).
- **Proses Reversal:**
  - Menghitung ulang saldo piutang. Ada pengecekan *backward compatibility* (*legacy vs new logic*).
  - Jika logika baru (pasca rilis 9 Juni 2026): Saldo ditambahkan berdasarkan perhitungan `penerimaan` dan `biaya_admin`, dengan memperhatikan status potongan PPh23.
  - Memulihkan (null-kan) nilai `nilai_terpakai` di alokasi agar kembali menjadi berstatus Draft.
  - Menghapus record *split* di `tr_alokasi_split`.
  - Menyisipkan catatan audit (log) di `log_alokasi_history` bahwa rollback terjadi.

---

## 4. Spesifikasi Database & Model

File: `application/modules/penerimaan_uang/models/Penerimaan_uang_model.php`

### 4.1. Cross-Database Architecture
Modul ini menghubungkan 3 *Database Connection*:
1. **Default (Finance DB):** Tempat tabel operasional (`tr_penerimaan_piutang`, `tr_invoicing`, `tr_alokasi_split`).
2. **Consultant DB (`$this->consultant`):** Menarik data Master Customer dan Master Penawaran/SPK.
3. **Accounting DB (`$this->accounting`):** Menarik referensi COA (Chart of Account) dari `coa_master`.

### 4.2. Resolusi Alokasi (Legacy vs New Split Architecture)
- **Fungsi:** `resolve_alokasi($id_alokasi)`
- **Penjelasan:** Modul dirancang tahan banting terhadap perubahan struktur database di masa lalu. Sistem membedakan antara alokasi lama (*legacy*) yang langsung merujuk ke `tr_alokasi_detail` dengan alokasi baru yang menggunakan tabel perantara `tr_alokasi_split`. Fungsi ini mengembalikan flag `'is_legacy' => true/false` agar Controller tahu cara meng-update datanya dengan benar saat Save/Rollback.

---

## 5. Aturan Bisnis & Validasi Kesalahan (Business Rules)

1. **Input Kontrol Harus Nol (0):**
   Di antarmuka (UI), total akumulasi dana dari Bank dikurangi rincian pengalokasian invoice **harus persis 0**. Jika nilai kontrol $\neq 0$, form *Submit* akan menolak menyimpan data. Hal ini mencegah kebocoran pencatatan.
2. **Struktur Jurnal Akuntansi (Double Entry):**
   - **Debit:** COA Bank (Diambil dari Master Bank)
   - **Kredit:** COA Piutang (`1102-01-01`), COA Pendapatan/Pajak, dan/atau Biaya Admin.
   Total Debit **harus selalu sama dengan** Total Kredit sebelum Jurnal masuk ke database.
3. **Immutability of Posted Journal:**
   Data yang sudah diverifikasi dan di-posting oleh Finance Manager (menjadi `sts = 1`) di-*lock* dan tidak bisa diedit maupun di-*rollback* lewat modul Penerimaan Uang. Jika ada kesalahan setelah posting, user wajib melakukan Jurnal Balik (Reversal) secara manual lewat modul Accounting.
4. **Kategorisasi COA PPh Otomatis:**
   Sistem cerdas dalam menentukan COA PPh berdasarkan *flag* `tipe_invoice`. Jika perusahaan berjenis VUCA (`tipe_invoice = 1`), ia menggunakan COA `1106-01-05`, dan selain itu menggunakan `1106-01-02`.

---
*Dokumen ini merupakan panduan resmi. Segala perubahan struktur alokasi atau logika pajak di masa mendatang wajib memperbarui dokumen PRD ini.*
