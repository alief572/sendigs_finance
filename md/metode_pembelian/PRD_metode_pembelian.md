# Product Requirements Document (PRD)
**Modul:** Metode Pembelian (Procurement Cycle)

---

## 1. Pendahuluan
**Metode Pembelian** adalah modul sentral untuk manajemen rantai pasok (Procurement/Purchasing) yang mengendalikan seluruh siklus pengadaan barang dan jasa perusahaan end-to-end. Modul ini menjembatani kebutuhan dari berbagai departemen (Purchase Requisition) hingga menjadi pesanan resmi ke supplier (Purchase Order), dan memproses penagihannya (Invoice) ke tim Keuangan.

## 2. Tujuan & Sasaran
- Menstandarkan alur pengadaan barang (SOP Purchasing).
- Memberikan transparansi dalam pemilihan supplier melalui mekanisme perbandingan harga (Quotation Comparison).
- Mengontrol *budget* dan pengeluaran kas melalui sistem persetujuan berjenjang (Approval Matrix).
- Mendokumentasikan *Term of Payment* (TOP) dan penjadwalan pembayaran tagihan (Invoice).

## 3. Fitur Utama & Alur Bisnis (End-to-End Procurement)

Modul ini terbagi ke dalam beberapa sub-proses yang saling berkesinambungan:

### 3.1. Purchase Request (PR)
- Menampilkan daftar permintaan pembelian (*Purchase Requisition*) yang datang dari Departemen internal (`rutin_non_planning_detail`) maupun dari Kebutuhan Produksi (`material_planning_base_on_produksi_detail`).
- Fitur pengecekan spesifikasi dan kuantitas barang yang dibutuhkan.

### 3.2. Request for Quotation (RFQ)
- Pembuatan dokumen permintaan penawaran harga kepada satu atau beberapa supplier/vendor.
- Memungkinkan staff *purchasing* untuk memecah atau menggabungkan item dari beberapa PR ke dalam satu RFQ.
- Cetak dokumen RFQ untuk dikirimkan ke supplier.

### 3.3. Perbandingan (Quotation Comparison)
- Input harga, spesifikasi, dan *Term of Payment* dari masing-masing supplier yang merespon RFQ.
- Matriks perbandingan secara *side-by-side* untuk membantu pengambilan keputusan secara objektif (berdasarkan harga termurah, kualitas, atau *lead time* terbaik).

### 3.4. Pengajuan & Approval (Approval Matrix)
- **Pengajuan:** Staff *purchasing* merekomendasikan salah satu supplier pemenang dari hasil perbandingan.
- **Approval:** Manajer/Direktur melakukan peninjauan terhadap rekomendasi tersebut. Persetujuan ini wajib sebelum sistem mengizinkan penerbitan Purchase Order (PO).

### 3.5. Purchase Order (PO)
- Penerbitan dokumen pemesanan resmi (PO) yang mengikat secara hukum kepada supplier pemenang.
- **Repeat PO:** Fitur *shortcut* untuk memesan kembali barang yang sama kepada supplier yang sama tanpa harus melewati siklus RFQ dan Perbandingan dari awal.
- Manajemen kuantitas pesanan, termasuk fitur pembatalan sebagian (Cancel sebagian PO).

### 3.6. Invoice & Request Payment
- **Receive Invoice:** Pencatatan tagihan masuk dari supplier setelah barang diterima.
- Mengelola *Term of Payment* (TOP) dan cicilan pembayaran jika PO menerapkan skema uang muka (DP) atau termin.
- **Request Payment:** Meneruskan tagihan yang sudah tervalidasi ke departemen Keuangan (Modul *Expense* / AP) untuk dijadwalkan pencairannya.

### 3.7. Non-PO Expense
- Manajemen pengeluaran/pembelian langsung berskala kecil yang tidak memerlukan siklus panjang (tanpa RFQ dan perbandingan).
- Tetap melewati sistem *Approval Non-PO* untuk kontrol pengeluaran.

### 3.8. Tracking History (Audit Trail)
- **End-to-End Tracking:** Modul memiliki sistem pencatatan riwayat proses (*audit trail*) yang ditarik secara menyeluruh berdasarkan nomor *Purchase Request* (PR).
- Setiap aksi di seluruh modul (seperti pembuatan RFQ, Update RFQ, Approval, hingga pembuatan PO dan pembatalan) akan dicatat beserta nama user pemroses (*created_by*) dan timestamp (*created_date*).
- **Akses Transparansi:** Timeline history ini dapat dilihat langsung melalui tombol "View History" pada halaman Index PR, memberikan kemudahan pengawasan bagi manajemen.

## 4. Pengguna & Hak Akses
- **Staff Purchasing:** Membuat RFQ, menginput data perbandingan, mengajukan PO, dan mencatat Invoice.
- **Manager Purchasing / Direktur:** Menyetujui pengajuan perbandingan (Approval) dan PO.
- **Finance / AP:** Menerima *Request Payment* hasil verifikasi tim Purchasing.
