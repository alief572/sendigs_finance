# Product Requirements Document (PRD)
**Modul:** Request Mutasi (Bank & Kas)

---

## 1. Pendahuluan
**Request Mutasi** adalah modul finansial terpusat yang berfungsi mengelola perpindahan dana internal antar rekening bank maupun kas kecil perusahaan. Modul ini mengakomodasi siklus pemindahan dana dari tahap pengajuan (Request), persetujuan (Approval), hingga eksekusi aktual yang otomatis membentuk jurnal akuntansi di entitas (company) yang bersangkutan.

## 2. Target Pengguna & Hak Akses
- **Staf Keuangan / Kasir:** Membuat pengajuan mutasi dan mencatat realisasi mutasi aktual. Membutuhkan akses `Add` dan `View`.
- **Manajer Keuangan / Supervisor:** Melakukan tinjauan (review) dan memberikan persetujuan atau penolakan. Membutuhkan akses `Manage` atau hak *Approval*.
- **Admin Finansial:** Memasukkan transaksi bank langsung (kas masuk/keluar) tanpa melalui alur pengajuan.

## 3. Fitur Utama (Key Features)

### 3.1. Pengajuan Mutasi (Request Mutasi)
Memungkinkan pengguna untuk mengajukan permohonan transfer dana antar akun (misal: Bank BCA ke Kas Kecil, atau Mandiri IDR ke Mandiri USD).
- **Pemilihan Entitas Tujuan:** Pengguna dapat menentukan kepada database entitas mana jurnal ini nanti akan dicatat (contoh: *STM, VUCA, SUSTAIN*).
- **Detail Asal & Tujuan:** Input akun asal (COA pengirim) dan akun tujuan (COA penerima).
- **Multi-Mata Uang:** Mendukung penginputan mutasi dalam valuta asing beserta nilai tukarnya (kurs).

### 3.2. Persetujuan Mutasi (Approval Workflow)
Proses kontrol internal sebelum dana benar-benar dipindahkan secara pembukuan.
- **Tinjauan Detail:** Menampilkan nominal yang diminta, asal, dan tujuan bank.
- **Aksi:**
  - **Approve:** Mengubah status dokumen agar siap dieksekusi / direalisasi.
  - **Reject:** Menolak pengajuan dengan memberikan *mandatory input* berupa alasan penolakan.

### 3.3. Realisasi Mutasi (Aktual)
Tahapan eksekusi pembukuan setelah pengajuan disetujui. Seringkali nilai yang diajukan berbeda dengan nilai aktual saat ditransfer (misal karena fluktuasi kurs atau biaya admin).
- Pengguna memasukkan nilai **Aktual (IDR)** dari dana yang berpindah.
- **Auto-Journaling (BUM):** Sistem akan otomatis membuat dan merekam Jurnal Bank Uang Masuk (BUM) di *database* target (mencatat Debit di Bank Tujuan dan Kredit di Bank Asal).

### 3.4. Transaksi Bank Langsung (Direct Admin Transaction)
Fasilitas pintasan (*bypass*) untuk mencatat transaksi masuk/keluar yang tidak relevan dimasukkan ke alur pengajuan panjang.
- **Kas Keluar (Bank Payment):** Memilih jenis transaksi "Keluar". Akan meng-generate nomor dokumen `KK-XXXX` dan mencetak Jurnal Bukti Uang Keluar (BUK).
- **Kas Masuk (Bank Receipt):** Memilih jenis transaksi "Terima". Akan meng-generate nomor dokumen `KM-XXXX` dan mencetak Jurnal Bukti Uang Masuk (BUM).

### 3.5. Bukti Cetak (Printout)
Menghasilkan dokumen PDF/HTML fisik berupa "Bukti Penerimaan Bank" atau "Bukti Pengeluaran Bank" yang rapi, berlogo resmi (*PT Sentral Sistem Consulting*), dilengkapi tanda tangan pembuat, pemeriksa, dan penyetuju.

---

## 4. Aturan Bisnis Khusus (Business Logic)
1. **Multi-Database Accounting:** Modul tidak mencatat jurnal ke database utama, melainkan "menyeberang" ke database entitas spesifik (`db_sendigs_ss_stm`, `db_sendigs_ss_vuca`, dll.) sesuai inputan pengguna (*Target Accounting*).
2. **Validasi Keseimbangan (Balance):** Setiap eksekusi jurnal selalu menjaga keseimbangan berpasangan (*double-entry bookkeeping*) di mana Debit selalu setara dengan Kredit.
3. **Pemisahan Fase Pencatatan:** Mutasi tidak mencatat pembukuan (jurnal) saat berstatus *Request* atau *Approved*. Jurnal baru tercipta saat mutasi resmi diinput pada menu *Mutasi Aktual*.
