# System Design & Architecture
**Modul:** Metode Pembelian (Procurement Cycle)

---

## 1. Arsitektur Komponen (CodeIgniter HMVC)
Lokasi Modul: `/application/modules/metode_pembelian/`

Modul ini dibangun menggunakan pola arsitektur *Model-View-Controller* yang ekstensif, mengingat kompleksitas alur pengadaan barang:
- **Controllers (`Metode_pembelian.php`):** *Entry point* raksasa yang menangani seluruh siklus HTTP Request, mulai dari memuat form RFQ, memproses data perbandingan, *approval*, hingga penerbitan PO dan pencatatan *Term of Payment* (TOP) serta Invoicing. Fungsi di-*grouping* berdasarkan tahapan siklus (PR, RFQ, Perbandingan, Approval, PO, dsb).
- **Models (`Metode_pembelian_model.php`):** Model utama yang mengeksekusi operasi DML (Data Manipulation Language) ke dalam *database*. Menyediakan fungsionalitas CRUD kompleks dan pengolahan data untuk DataTables (Server-side processing). Mengelola banyak *transaction block* untuk menjaga integritas data lintas-tabel.
- **Views:** Terdiri atas puluhan file `*.php` (HTML/UI) yang dipecah spesifik per tahap. (Misal: `pr.php`, `rfq.php`, `perbandingan.php`, `purchase_order.php`, `approval_po.php`, hingga ke form modal dinamis seperti `modal_detail_pr.php` dan `modal_edit_rfq.php`).

## 2. Struktur Database & Relasi (Database Architecture)
Karena modul ini menangani siklus yang panjang, data mengalir dari satu tabel ke tabel berikutnya dengan membawa referensi ID (Foreign Keys) yang kuat:

### 2.1. Sumber Kebutuhan (PR)
- `rutin_non_planning_detail`: PR dari departemen internal reguler.
- `material_planning_base_on_produksi_detail`: PR turunan dari modul *Production Planning*.
- Kolom pengait utama adalah `no_pr`.

### 2.2. Request for Quotation (RFQ)
- `tr_rfq_header` & `tr_rfq_detail`: Menyimpan informasi barang apa saja yang sedang diminta penawarannya ke *supplier*.
- Modul melakukan agregasi dari *detail PR* menggunakan `no_pr_group` atau ID unik lainnya.

### 2.3. Perbandingan & Pengajuan
- `tr_perbandingan_header` & `tr_perbandingan_detail`: Menyimpan rekam jejak input harga, diskon, ongkos kirim, TOP, dan spesifikasi per *supplier*.
- `tr_pengajuan_header` & `tr_pengajuan_detail`: Menyimpan keputusan *purchasing* (rekomendasi *supplier* pemenang) yang akan dilempar ke *Approval*.

### 2.4. Purchase Order (PO) & Pembayaran
- `tr_purchase_order`: Menyimpan dokumen induk PO yang sah (berstatus *approved*).
- Invoice dan TOP dikelola dalam rangkaian tabel validasi penagihan dan pengiriman ke modul `Expense` untuk penjurnalan AP (Account Payable).

### 2.5. Tracking History (Audit Trail)
- `tr_tracking_pembelian`: Menghubungkan setiap aksi spesifik (seperti Create RFQ, Approval PO) kembali ke `no_pr`. Tabel ini menyimpan detail `tipe_dokumen`, `no_dokumen`, `aksi`, `keterangan`, `created_by`, dan `created_at`. Logika sistem secara proaktif melacak *foreign keys* dari masing-masing aksi ke PR sumbernya melalui agregasi.

## 3. Aliran Data & Server-Side Processing
Untuk mengelola ribuan transaksi tanpa membebani memori peramban (*browser*), semua antarmuka tabel menggunakan **Server-Side DataTables**.

**Mekanisme Server-Side:**
Setiap halaman memanggil metode Controller spesifik (contoh: `server_side_rfq()`, `server_side_perbandingan()`, `server_side_purchase_order()`). Controller akan meneruskannya ke Model, di mana Model menyusun *Query Builder* secara dinamis berdasarkan parameter AJAX (Paging `limit`, Searching `like`, dan Ordering `order_by`). Model mengembalikan JSON terstruktur.

## 4. Keamanan & Integritas Transaksi
- **Database Transactions (`$this->db->trans_start()`):** Diterapkan secara ketat pada setiap aksi simpang (Submit RFQ, Approve Pengajuan, Create PO, Cancel PO). Jika terjadi anomali (misal: *network timeout* di tengah proses *insert* relasi *detail*), sistem me-*rollback* seluruh *query* agar data tidak *corrupt* (menggantung).
- **Access Control List (ACL):** Setiap fungsi vital di-*block* di *backend* menggunakan `$this->auth->restrict('Permission_Name')` untuk memastikan hanya *role* yang berhak (seperti Direktur untuk Approval, atau Staff untuk pengajuan) yang bisa mengakses *endpoint* eksekusi.
- **Repeat PO Validation:** Saat melakukan fitur pengulangan (*repeat*), *backend* memastikan spesifikasi, harga (*kurs*), dan atribut lain tersalin dengan akurat ke struktur PO yang baru, mencegah *human error* akibat pengetikan ulang.
- **Audit Logging Terpusat:** Metode helper `history()` disandingkan dengan `insert_tracking_pr()` pada model `Metode_pembelian_model` untuk mencegat setiap transaksi (Ubah RFQ, Pengajuan, PO) dan merekam aksi *real-time* ke basis data riwayat (`tr_tracking_pembelian`).
