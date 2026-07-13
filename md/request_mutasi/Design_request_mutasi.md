# System Design & Architecture
**Modul:** Request Mutasi (Bank & Kas)

---

## 1. Arsitektur Komponen (CodeIgniter HMVC)
Modul ini berlokasi di `/application/modules/request_mutasi/` dan memanfaatkan pola arsitektur MVC secara disiplin.

- **Controllers (`Request_mutasi.php`):**
  Pusat kendali logika (*Entry Point*). Mengatur rute HTTP, memvalidasi form (termasuk validasi keabsahan entitas database target), merangkai array data, mengatur *database transactions* (`trans_begin`, `trans_commit`, `trans_rollback`), dan mengirim respon berformat JSON untuk panggilan AJAX (*Client-side*).
- **Models (`Request_mutasi_model.php`):**
  Fokus melayani pertukaran data spesifik modul mutasi. Memiliki algoritma krusial untuk menghasilkan ID/Nomor dokumen yang otomatis bertambah (Auto-increment Code Generator) dengan struktur `PREFIX-YYMM-XXXXX`.
- **Layanan Lintas Modul:**
  Modul ini meminjam berbagai fungsionalitas cerdas dari *Global Models* seperti `Jurnal_model` (untuk *generator* nomor Jurnal BUM/BUK) dan `All_model` (untuk menarik data Master COA).

## 2. Struktur Database
Terdapat 3 (tiga) tabel utama yang merekam riwayat (*state*) transaksi secara persisten:
1. **`tr_request_mutasi`**: Tabel *staging* awal. Mencatat permohonan (*request*). Berisi *draft*, nominal yang diprediksi, dan status persetujuan (*Approve/Reject*).
2. **`tr_request_mutasi_aktual`**: Tabel eksekusi (Realisasi). Mencatat jumlah uang riil yang dipindahkan antar bank setelah dikurangi biaya admin/kurs, serta mencatat nomor jurnal (`Nomor_JV`) hasil eksekusi.
3. **`tr_request_mutasi_admin`**: Tabel jalur cepat (*Direct Transaction*). Dirancang khusus untuk menampung transaksi masuk (`KM-XXXX`) dan keluar (`KK-XXXX`) secara langsung tanpa melalui prosedur *request*.

## 3. Desain Algoritma: Multi-Database Routing
Fitur paling *advanced* dari modul ini adalah kemampuan *cross-database journaling* (Pencatatan Silang Database).

### 3.1. Konfigurasi Whitelist & Mapping
Controller mendeklarasikan *whitelist* statis dan pemetaan (`self::$TARGET_DB_MAP`) untuk memastikan sistem tidak menembak database yang salah:
- `accounting_stm` ➔ `db_sendigs_ss_stm`
- `accounting_vuca` ➔ `db_sendigs_ss_vuca`
- `accounting_sustain` ➔ `db_sendigs_ss_sustain`

### 3.2. Resolver Database (`_resolve_target_db`)
Fungsi pelindung berlapis ini dipanggil setiap kali sistem akan memvalidasi *Chart of Account* (COA) dan menyimpannya sebagai jurnal.
- Sistem memanggil `$this->load->database($target_accounting, TRUE)` secara dinamis untuk mengaktifkan koneksi ke entitas terpilih.
- Gagal tersambung (*Cannot Connect*) akan ditolak secara *graceful* dan membatalkan keseluruhan transaksi.

## 4. Desain Eksekusi Pembukuan (Engine Jurnal)
Ketika dana direalisasikan atau dimasukkan secara langsung (Direct), sistem membangkitkan metode pembukuan ganda (`save_jurnal_BUM` / `save_jurnal_BUK`):

### Skema Perekaman Jurnal Bank Masuk (BUM):
1. **Tabel *Header* (`jarh`)**: Merekam metadata BUM. Nominal Total, Nama/Note, Tanggal, dan Nomor Induk Pembayaran.
2. **Tabel *Detail* (`jurnal`) - Baris 1 (Debit)**: Membebani nilai uang ke *COA Bank Tujuan* (`kredit = 0`, `debet = nilai_idr`).
3. **Tabel *Detail* (`jurnal`) - Baris 2 (Kredit)**: Memotong nilai uang dari *COA Bank Asal* (`debet = 0`, `kredit = nilai_idr`).
4. **Counter Master (`pastibisa_tb_cabang`)**: Menambahkan nilai kolom `nobum` sebanyak 1 poin (*Increment*) untuk *reserve* nomor BUM selanjutnya.

Seluruh 4 proses di atas wajib masuk di dalam blok **`trans_begin()`** hingga **`trans_commit()`**. Jika database terputus atau insert *detail* gagal di tengah proses, **`trans_rollback()`** akan dipicu untuk mencabut semua perubahan demi menghindari jurnal cacat (*Unbalanced Jurnal*).

## 5. UI/UX dan Template Pencetakan (Printout)
- **Tampilan Tabel Utama**: Modul ini dibangun dengan sistem DataTable asinkron (AJAX).
- **View Cetak (`print_transaksi.php`)**: Memanfaatkan teknik HTML/CSS *print media-queries* yang disesuaikan ukuran kertas fisik. Komponen didesain *no-border* dan responsif (menggunakan *CSS Flexbox*) untuk mengakomodir variasi logo (seperti logo *PT Sentral Sistem Consulting*) dan teks perusahaan yang fleksibel.
