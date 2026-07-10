# Design Document — Modul Invoicing

## 1. Arsitektur Modul

### 1.1 Struktur File

```
application/modules/invoicing/
├── controllers/
│   └── Invoicing.php              # Main controller (extends Admin_Controller)
├── models/
│   └── Invoicing_model.php        # Model (extends BF_Model)
└── views/
    ├── index.php                   # Halaman utama (2 tabs: Konsultasi & Non Kons)
    ├── add_invoice.php             # Form create invoice Sentral
    ├── add_invoice_vuca.php        # Form create invoice VUCA
    ├── add_invoice_non_konsultasi.php  # Form create invoice Non Konsultasi
    ├── edit_invoice.php            # Form edit invoice Sentral
    ├── edit_invoice_vuca.php       # Form edit invoice VUCA
    ├── edit_invoice_non_konsultasi.php # Form edit invoice Non Konsultasi
    ├── view_invoice.php            # Detail view Sentral
    ├── view_invoice_vuca.php       # Detail view VUCA
    ├── view_invoice_non_konsultasi.php # Detail view Non Konsultasi
    ├── list_penawaran_non_kons.php # List penawaran non kons (pilih untuk invoice)
    ├── print_invoice.php           # Template print PDF Sentral
    ├── print_invoice_vuca.php      # Template print PDF VUCA
    ├── print_invoice_non_konsultasi.php # Template print PDF Non Kons
    ├── print_kwitansi.php          # Template print kwitansi Sentral
    ├── print_kwitansi_vuca.php     # Template print kwitansi VUCA
    └── download_excel.php          # Template export Excel
```

### 1.2 Database Connections

```php
$this->consultant      = $this->load->database('consultant', true);   // DB: db_consultant_new
$this->accounting      = $this->load->database('accounting', true);   // DB: db_sendigs_gl_ss
$this->accounting_vuca = $this->load->database('accounting_vuca', true); // DB: db_sendigs_ss_vuca
$this->hris            = $this->load->database('hris', true);         // DB: hr_sentral
```

### 1.3 Controller Inheritance

```
MX_Controller
  └── Base_Controller
        └── Admin_Controller (auth, template, pagination)
              └── Invoicing (protected permissions, multi-DB connections)
```

---

## 2. Database Schema

### 2.1 Tabel Utama: `tr_invoicing`

| Column                    | Type          | Deskripsi                                                      |
| ------------------------- | ------------- | -------------------------------------------------------------- |
| `id`                      | VARCHAR       | PK, auto-generated: `XXXXX-INV-{roman month}-{year}`           |
| `id_actual_plan_tagih`    | VARCHAR       | FK ke `kons_tr_actual_plan_tagih` (konsultasi)                 |
| `id_detail_plan_tagih`    | VARCHAR       | FK ke `kons_tr_plan_tagih_detail`                              |
| `id_penawaran`            | VARCHAR       | FK ke `kons_tr_penawaran` / `kons_tr_penawaran_non_konsultasi` |
| `id_spk_penawaran`        | VARCHAR       | FK ke `kons_tr_spk_penawaran`                                  |
| `id_customer`             | VARCHAR       | ID customer                                                    |
| `nm_customer`             | VARCHAR       | Nama customer (denormalized)                                   |
| `address`                 | TEXT          | Alamat customer                                                |
| `id_project`              | VARCHAR       | ID project konsultasi                                          |
| `nm_project`              | VARCHAR       | Nama project (denormalized)                                    |
| `id_project_leader`       | VARCHAR       | ID project leader                                              |
| `nm_project_leader`       | VARCHAR       | Nama PL (denormalized)                                         |
| `id_sales`                | VARCHAR       | ID sales                                                       |
| `nm_sales`                | VARCHAR       | Nama sales (denormalized)                                      |
| `tanggal_invoice`         | DATE          | Tanggal invoice                                                |
| `no_invoice`              | VARCHAR       | Nomor invoice (unique per tipe)                                |
| `no_po`                   | VARCHAR       | Nomor PO customer                                              |
| `no_faktur`               | VARCHAR       | Nomor faktur pajak                                             |
| `total_nominal`           | DECIMAL       | DPP                                                            |
| `dpp_nilai_lain`          | DECIMAL       | DPP × 11/12                                                    |
| `pajak`                   | DECIMAL       | PPN (DPP lain-lain × 12%)                                      |
| `total_akhir`             | DECIMAL       | DPP + PPN                                                      |
| `total_nominal_jurnal`    | DECIMAL       | DPP untuk jurnal                                               |
| `dpp_lain_lain_jurnal`    | DECIMAL       | DPP lain-lain untuk jurnal                                     |
| `ppn_jurnal`              | DECIMAL       | PPN untuk jurnal                                               |
| `tagihan_ppn_jurnal`      | DECIMAL       | DPP + PPN (tanpa potong PPh)                                   |
| `pph_jurnal`              | DECIMAL       | PPh 23                                                         |
| `total_akhir_jurnal`      | DECIMAL       | DPP + PPN - PPh                                                |
| `saldo_piutang`           | DECIMAL       | Sisa piutang (berkurang saat penerimaan)                       |
| `saldo_piutang_tanpa_pph` | DECIMAL       | Piutang tanpa potong PPh                                       |
| `tipe_invoice`            | ENUM('0','1') | 0=Sentral, 1=VUCA                                              |
| `non_kons`                | ENUM('0','1') | 1=Invoice Non Konsultasi                                       |
| `biaya_kirim`             | DECIMAL       | Biaya kirim (non kons)                                         |
| `no_revisi`               | INT           | Counter revisi                                                 |
| `print_keterangan`        | TEXT          | Keterangan untuk print                                         |
| `sts_close`               | ENUM('0','1') | 1=Closed                                                       |
| `close_by`                | VARCHAR       | User yang close                                                |
| `close_date`              | DATETIME      | Tanggal close                                                  |
| `close_reason`            | TEXT          | Alasan close                                                   |
| `created_by`              | VARCHAR       | Creator                                                        |
| `created_date`            | DATETIME      | Tanggal pembuatan                                              |

### 2.2 Tabel Detail Non Konsultasi: `tr_invoice_detail_non_kons`

| Column      | Type     | Deskripsi               |
| ----------- | -------- | ----------------------- |
| `id`        | INT      | PK auto increment       |
| `id_header` | VARCHAR  | FK ke `tr_invoicing.id` |
| `nm_item`   | VARCHAR  | Nama item/jasa          |
| `qty`       | DECIMAL  | Quantity                |
| `harga`     | DECIMAL  | Harga satuan            |
| `total`     | DECIMAL  | qty × harga             |
| `input_by`  | VARCHAR  | User input              |
| `input_at`  | DATETIME | Waktu input             |

### 2.3 Tabel Jurnal: `tr_jurnal`

| Column            | Type          | Deskripsi                                |
| ----------------- | ------------- | ---------------------------------------- |
| `id`              | INT           | PK auto increment                        |
| `no_jurnal`       | VARCHAR       | Nomor jurnal: `XXXXX-AJV-{roman}-{year}` |
| `tgl_jurnal`      | DATE          | Tanggal jurnal                           |
| `coa`             | VARCHAR       | Nomor COA                                |
| `id_company`      | VARCHAR       | ID company                               |
| `nm_company`      | VARCHAR       | Nama company                             |
| `nm_coa`          | VARCHAR       | Nama akun                                |
| `debit`           | DECIMAL       | Nilai debit                              |
| `kredit`          | DECIMAL       | Nilai kredit                             |
| `keterangan`      | TEXT          | Keterangan jurnal                        |
| `sts`             | ENUM('0','1') | 0=Draft, 1=Posted                        |
| `no_transaksi`    | VARCHAR       | Referensi ke `tr_invoicing.id`           |
| `jenis_transaksi` | VARCHAR       | "Invoicing"                              |
| `created_by`      | VARCHAR       | Creator                                  |
| `created_date`    | DATETIME      | Tanggal pembuatan                        |

### 2.4 Tabel Terkait (DB Consultant)

| Tabel                                     | Deskripsi                                    |
| ----------------------------------------- | -------------------------------------------- |
| `kons_tr_plan_tagih_detail`               | Detail plan tagih (term payment, %, nominal) |
| `kons_tr_actual_plan_tagih`               | Realisasi plan tagih                         |
| `kons_tr_spk_penawaran`                   | SPK/kontrak dengan customer                  |
| `kons_tr_penawaran`                       | Penawaran/quotation konsultasi               |
| `kons_tr_penawaran_non_konsultasi`        | Penawaran non konsultasi                     |
| `kons_tr_detail_penawaran_non_konsultasi` | Detail item penawaran                        |
| `kons_tr_company`                         | Master company (Sentral, VUCA, dll)          |
| `kons_master_konsultasi_header`           | Master project konsultasi                    |

---

## 3. API Endpoints (Controller Methods)

### 3.1 Halaman & Navigasi

| Method                               | HTTP | URL                                             | Deskripsi                          |
| ------------------------------------ | ---- | ----------------------------------------------- | ---------------------------------- |
| `index()`                            | GET  | `/invoicing`                                    | Halaman utama (2 tabs)             |
| `add_invoice($id)`                   | GET  | `/invoicing/add_invoice/{id}`                   | Form create invoice Sentral        |
| `add_invoice_vuca($id)`              | GET  | `/invoicing/add_invoice_vuca/{id}`              | Form create invoice VUCA           |
| `add_invoice_non_konsultasi($id)`    | GET  | `/invoicing/add_invoice_non_konsultasi/{id}`    | Form create non kons (simple)      |
| `create_invoice_non_konsultasi($id)` | GET  | `/invoicing/create_invoice_non_konsultasi/{id}` | Form create non kons (full/jurnal) |
| `edit_invoicing($id)`                | GET  | `/invoicing/edit_invoicing/{id}`                | Form edit Sentral                  |
| `edit_invoicing_vuca($id)`           | GET  | `/invoicing/edit_invoicing_vuca/{id}`           | Form edit VUCA                     |
| `edit_invoice_non_kons($id)`         | GET  | `/invoicing/edit_invoice_non_kons/{id}`         | Form edit non kons                 |
| `view_invoicing($id)`                | GET  | `/invoicing/view_invoicing/{id}`                | View Sentral                       |
| `view_invoicing_vuca($id)`           | GET  | `/invoicing/view_invoicing_vuca/{id}`           | View VUCA                          |
| `view_invoicing_non_kons($id)`       | GET  | `/invoicing/view_invoicing_non_kons/{id}`       | View non kons (legacy)             |
| `view_invoice_non_kons($id)`         | GET  | `/invoicing/view_invoice_non_kons/{id}`         | View non kons (IDOR protected)     |
| `list_penawaran_non_kons()`          | GET  | `/invoicing/list_penawaran_non_kons`            | List penawaran non kons            |

### 3.2 AJAX / Data Endpoints

| Method                                | HTTP | URL                                            | Deskripsi                               |
| ------------------------------------- | ---- | ---------------------------------------------- | --------------------------------------- |
| `get_data_spk()`                      | POST | `/invoicing/get_data_spk`                      | DataTables server-side: list konsultasi |
| `get_data_quotation_non_konsultasi()` | POST | `/invoicing/get_data_quotation_non_konsultasi` | DataTables: list non kons               |
| `hitung_jurnal()`                     | GET  | `/invoicing/hitung_jurnal`                     | Recalculate jurnal (AJAX)               |
| `change_jurnal_invoice()`             | GET  | `/invoicing/change_jurnal_invoice`             | Ganti COA revenue                       |

### 3.3 Save / Update Endpoints

| Method                            | HTTP | URL                                        | Deskripsi               |
| --------------------------------- | ---- | ------------------------------------------ | ----------------------- |
| `save_invoice()`                  | POST | `/invoicing/save_invoice`                  | Simpan invoice Sentral  |
| `save_invoice_vuca()`             | POST | `/invoicing/save_invoice_vuca`             | Simpan invoice VUCA     |
| `save_invoice_non_konsultasi()`   | POST | `/invoicing/save_invoice_non_konsultasi`   | Simpan invoice Non Kons |
| `update_invoice()`                | POST | `/invoicing/update_invoice`                | Update invoice Sentral  |
| `update_invoice_vuca()`           | POST | `/invoicing/update_invoice_vuca`           | Update invoice VUCA     |
| `update_invoice_non_konsultasi()` | POST | `/invoicing/update_invoice_non_konsultasi` | Update invoice Non Kons |
| `save_close_invoice()`            | POST | `/invoicing/save_close_invoice`            | Close invoice           |
| `close_penawaran_non_kons()`      | POST | `/invoicing/close_penawaran_non_kons`      | Close penawaran         |

### 3.4 Print Endpoints

| Method                                  | HTTP | URL                                                | Deskripsi              |
| --------------------------------------- | ---- | -------------------------------------------------- | ---------------------- |
| `print_invoicing($id, $company)`        | GET  | `/invoicing/print_invoicing/{id}/{company}`        | Print PDF Sentral      |
| `print_invoicing_vuca($id, $company)`   | GET  | `/invoicing/print_invoicing_vuca/{id}/{company}`   | Print PDF VUCA         |
| `print_invoice_non_kons($id, $company)` | GET  | `/invoicing/print_invoice_non_kons/{id}/{company}` | Print PDF Non Kons     |
| `print_kwitansi($id, $company)`         | GET  | `/invoicing/print_kwitansi/{id}/{company}`         | Print kwitansi Sentral |
| `print_kwitansi_vuca($id, $company)`    | GET  | `/invoicing/print_kwitansi_vuca/{id}/{company}`    | Print kwitansi VUCA    |

### 3.5 Print Keterangan (Save before Print)

| Method                                  | HTTP | URL                                              | Deskripsi                                 |
| --------------------------------------- | ---- | ------------------------------------------------ | ----------------------------------------- |
| `save_keterangan_print()`               | POST | `/invoicing/save_keterangan_print`               | Simpan keterangan + update company jurnal |
| `save_keterangan_print_vuca()`          | POST | `/invoicing/save_keterangan_print_vuca`          | Simpan keterangan VUCA                    |
| `save_keterangan_print_non_kons()`      | POST | `/invoicing/save_keterangan_print_non_kons`      | Simpan keterangan Non Kons                |
| `save_keterangan_print_kwitansi()`      | POST | `/invoicing/save_keterangan_print_kwitansi`      | Simpan keterangan kwitansi                |
| `save_keterangan_print_kwitansi_vuca()` | POST | `/invoicing/save_keterangan_print_kwitansi_vuca` | Simpan keterangan kwitansi VUCA           |

### 3.6 Utility

| Method             | HTTP | URL                         | Deskripsi             |
| ------------------ | ---- | --------------------------- | --------------------- |
| `download_excel()` | GET  | `/invoicing/download_excel` | Download Excel report |

### 3.7 Internal/Private Methods

| Method                                 | Deskripsi                                                      |
| -------------------------------------- | -------------------------------------------------------------- |
| `_render_status_invoice_non_kons($id)` | Render badge status (Invoice Created / Closed) untuk DataTable |
| `_render_action_invoice_non_kons($id)` | Render action buttons (View/Edit/Print/Close) untuk DataTable  |
| `_send_response($code, $message)`      | Helper untuk output JSON response dengan HTTP status code      |

---

## 4. Flow Diagram

### 4.1 Create Invoice Konsultasi (Sentral)

```
User klik "Create Invoice" pada row plan tagih
        │
        ▼
GET /invoicing/add_invoice/{id_plan_tagih_detail}
        │
        ├── Query kons_tr_plan_tagih_detail + spk + penawaran + company
        ├── Hitung DPP, DPP lain-lain, PPN, PPh, Total
        ├── Generate preview jurnal (4 baris COA)
        ├── No. Invoice = readonly "Auto Generated"
        │
        ▼
User isi: tanggal_invoice, nomor_po, nomor_faktur
        │
        ▼
POST /invoicing/save_invoice
        │
        ├── Auto-generate no_invoice via generate_no_invoice(company, '0')
        ├── Generate ID invoice (XXXXX-INV-{M}-{YY})
        ├── INSERT tr_invoicing (no_invoice = auto-generated)
        ├── INSERT BATCH tr_jurnal (4 records)
        ├── UPDATE kons_tr_actual_plan_tagih SET sts_invoice = 1
        ├── UPDATE kons_tr_plan_tagih_detail SET sts_invoice = 1
        │
        ▼
Response JSON { status: 1, msg: 'Data has been saved !' }
```

### 4.2 Create Invoice Non Konsultasi

```
User klik "Add Invoice" → pilih penawaran dari list
        │
        ▼
GET /invoicing/create_invoice_non_konsultasi/{id_penawaran}
        │
        ├── Query kons_tr_penawaran_non_konsultasi
        ├── Query detail item penawaran
        ├── Hitung total_invoiced sebelumnya (outstanding check)
        ├── Generate preview jurnal
        ├── No. Invoice = readonly "Auto Generated"
        │
        ▼
User isi: tanggal, no_po, no_faktur, items (nama/qty/harga), biaya_kirim
        │
        ▼  (AJAX) hitung_jurnal → recalculate preview
        │
        ▼
POST /invoicing/save_invoice_non_konsultasi
        │
        ├── Auto-generate no_invoice via generate_no_invoice(company, '0')
        ├── Server-side DPP calculation dari items
        ├── Validasi: DPP > outstanding? → reject
        ├── Generate ID invoice
        ├── INSERT tr_invoicing (no_invoice = auto-generated)
        ├── INSERT BATCH tr_invoice_detail_non_kons
        ├── INSERT BATCH tr_jurnal (4 records)
        │
        ▼
Response JSON { status: 1, msg: 'Data has been saved !' }
```

### 4.3 Revisi Invoice

```
User klik "Edit" pada invoice yang sudah created
        │
        ▼
GET /invoicing/edit_invoicing/{id}
        │
        ├── Check: jurnal sudah posting? → block edit
        ├── Load data invoice + plan tagih + jurnal preview
        │
        ▼
User edit: tanggal, no_invoice, no_po, no_faktur
        │
        ▼
POST /invoicing/update_invoice
        │
        ├── Check: jurnal sudah posting (sts=1)? → reject
        ├── DELETE tr_jurnal WHERE no_transaksi = id
        ├── UPDATE tr_invoicing
        ├── INSERT BATCH tr_jurnal (regenerate)
        │
        ▼
Response JSON { status: 1, msg: 'Data has been updated !' }
```

### 4.4 Print Invoice

```
User klik icon "Print" → Modal muncul
        │
        ├── User pilih Company (header surat)
        ├── User isi keterangan print (optional)
        │
        ▼
POST /invoicing/save_keterangan_print
        │
        ├── Update print_keterangan di tr_invoicing
        ├── Update id_company & nm_company di tr_jurnal (jika belum posting)
        │
        ▼
Window.open → GET /invoicing/print_invoicing/{id}/{company}
        │
        ├── Load data invoice + plan tagih
        ├── Render view (HTML → direct print / PDF)
        │
        ▼
Browser print dialog
```

---

## 5. Model Layer (Invoicing_model)

### 5.1 Public Methods

| Method                                       | Return           | Deskripsi                                                  |
| -------------------------------------------- | ---------------- | ---------------------------------------------------------- |
| `generate_id()`                              | string           | Generate ID invoice format `XXXXX-INV-{M}-{YY}`            |
| `generate_id_invoice_jurnal($nomor)`         | string           | Generate ID jurnal `XXXXX-AJV-{M}-{YY}`                    |
| `generate_no_invoice($id_company, $tipe)`    | string           | Generate nomor invoice `XXX/{ENTITY}/{YEAR}` (auto-number) |
| `get_data_spk()`                             | void (echo JSON) | DataTables server-side untuk tab Konsultasi                |
| `get_penawaran_non_konsultasi($id)`          | object           | Get penawaran non kons by id_penawaran                     |
| `get_detail_penawaran_non_konsultasi($id)`   | array            | Get detail items penawaran                                 |
| `jurnal_invoicing_non_konsultasi($id, $tgl)` | array            | Generate HTML jurnal preview                               |
| `get_invoice($id)`                           | object           | Get single invoice by ID                                   |
| `get_invoice_non_kons($id_penawaran)`        | array            | Get invoices non kons by penawaran                         |
| `get_invoice_non_kons_detail($id_header)`    | array            | Get detail items invoice non kons                          |
| `get_list_penawaran_non_kons()`              | array            | List penawaran eligible (deal, not closed)                 |
| `get_view_jurnal_invoice_non_kons($id)`      | array            | Get jurnal HTML for view                                   |
| `total_invoiced_non_kons($id_penawaran)`     | float            | Sum total_nominal invoiced                                 |
| `is_no_invoice_exists($no, $tipe)`           | bool             | Check duplicate no_invoice                                 |

---

## 6. Frontend Architecture

### 6.1 JavaScript Libraries

| Library     | Version | Usage                    |
| ----------- | ------- | ------------------------ |
| jQuery      | 3.x     | DOM manipulation, AJAX   |
| DataTables  | 2.1.7   | Server-side tables       |
| SweetAlert2 | 11      | Confirmation dialogs     |
| Bootstrap   | 3.x     | Layout, modals, tabs     |
| AutoNumeric | -       | Number formatting inputs |

### 6.2 UI Components (index.php)

```
┌─────────────────────────────────────────────────────┐
│  [Tab: Konsultasi]  [Tab: Non Konsultasi]           │
├─────────────────────────────────────────────────────┤
│  Filter: [Status ▼] [Filter] [Excel]                │
│                                                     │
│  ┌─────────────────────────────────────────────┐    │
│  │ DataTable: No | Invoice | Company | SPK ... │    │
│  │ Action: [View] [Print] [Kwitansi]           │    │
│  └─────────────────────────────────────────────┘    │
├─────────────────────────────────────────────────────┤
│  Modals:                                            │
│  - #modal_print (pilih company + keterangan)        │
│  - #modal_print_vuca (keterangan only)              │
│  - #modal_print_non_kons (pilih company)            │
│  - #modal_print_kwitansi (pilih company + ket)      │
│  - #modal_print_kwitansi_vuca (keterangan only)     │
│  - #modal_list_non_kons (close invoice form)        │
└─────────────────────────────────────────────────────┘
```

### 6.3 AJAX Flow Pattern

```javascript
// Pattern: Form submit → SweetAlert confirm → AJAX POST → SweetAlert result → redirect
$(document).on("submit", "#frm-data", function (e) {
  e.preventDefault();
  Swal.fire({ title: "Warning !", showCancelButton: true }).then((next) => {
    if (next.isConfirmed) {
      $.ajax({
        type: "post",
        url: siteurl + active_controller + "save_invoice",
        data: formData,
        dataType: "json",
        success: function (result) {
          if (result.status == 1) {
            /* success → redirect */
          } else {
            /* show error */
          }
        },
      });
    }
  });
});
```

---

## 7. Integrasi Detail

### 7.1 Invoicing → Penerimaan Uang

**Titik integrasi:**

- Modul `penerimaan_uang` query `tr_invoicing` WHERE `nm_customer = X` AND `saldo_piutang > 0`
- Saat save penerimaan: `UPDATE tr_invoicing SET saldo_piutang = {sisa}` via batch update
- Rollback penerimaan: `saldo_piutang = saldo_piutang + amount_to_add`

**Fields yang dibaca oleh penerimaan_uang:**

```
tr_invoicing.id, .nm_customer, .no_invoice, .non_kons,
.total_nominal_jurnal, .dpp_lain_lain_jurnal, .ppn_jurnal,
.pph_jurnal, .tagihan_ppn_jurnal, .total_akhir_jurnal, .saldo_piutang
```

### 7.2 Invoicing → Penerimaan PPh 23

**Titik integrasi:**

- Modul `penerimaan_pph_23` query `tr_penerimaan_piutang_detail` JOIN `tr_invoicing`
- Filter: hanya penerimaan piutang dengan `pph23_dipotong = 'Y'`
- Membaca `tr_invoicing.tipe_invoice` untuk menentukan COA PPh:
  - tipe_invoice = 1 (VUCA) → COA `1106-01-05`
  - tipe_invoice = 0 (Sentral) → COA `1106-01-02`
- Generate jurnal: debit PPh (dari bukti potong), kredit Piutang

**Fields yang dibaca oleh penerimaan_pph_23:**

```
tr_invoicing.id, .tipe_invoice, .total_nominal, .id_penawaran,
.id_detail_plan_tagih, .id_spk_penawaran, .print_keterangan, .nm_project
```

---

## 8. ID Generation Logic

### 8.1 Invoice ID (Internal Identifier)

```php
// Format: XXXXX-INV-{Roman Month}-{Year}
// Example: 00045-INV-VII-25
$srcMtr = "SELECT MAX(id) as maxP FROM tr_invoicing WHERE id LIKE '%-{year}%'";
$urutan = (int)substr($maxP, 0, 5) + 1;
$id = sprintf('%05s', $urutan) . '-INV-' . int_to_roman(date('m')) . '-' . date('y');
```

### 8.2 No. Invoice (Auto-Generated, User-Facing)

```php
// Format: XXX/{ENTITY_CODE}/{YEAR}
// Examples: 001/STM/2025, 002/VSB/2025, 003/SSC/2025

// Entity code mapping:
// - STM  → company id 1, 6, 7
// - VSB  → company id 4 OR tipe_invoice = '1' (VUCA)
// - SSC  → default (selain di atas)

$kode_entitas = 'SSC';
if (in_array($id_company, [1, 6, 7])) {
    $kode_entitas = 'STM';
} elseif ($id_company == 4 || $tipe_invoice == '1') {
    $kode_entitas = 'VSB';
}

$query = "SELECT MAX(CAST(SUBSTRING_INDEX(no_invoice, '/', 1) AS UNSIGNED)) as max_seq
          FROM tr_invoicing
          WHERE no_invoice LIKE '%/{$kode_entitas}/{$tahun}'";

$sequence = (int)$result->max_seq + 1;
return sprintf('%03d/%s/%d', $sequence, $kode_entitas, $tahun);
```

> **Catatan:** Nomor invoice sekarang di-generate server-side saat save. Field `nomor_invoice` pada form bersifat **readonly** dengan placeholder "Auto Generated". User tidak perlu input manual.

### 8.3 Jurnal ID

```php
// Format: XXXXX-AJV-{Roman Month}-{Year}
// Example: 00123-AJV-VII-25
$srcMtr = "SELECT MAX(id) as maxP FROM tr_jurnal WHERE no_jurnal LIKE '%{M}-{year}%'";
$urutan = (int)substr($maxP, 0, 5) + $nomor;
$id = sprintf('%05s', $urutan) . '-AJV-' . int_to_roman(date('m')) . '-' . date('y');
```

---

## 9. Security & Validation

### 9.1 Authorization

```php
// Setiap method memiliki permission check
$this->auth->restrict($this->viewPermission);   // hard block → redirect
has_permission($this->addPermission);            // boolean check
```

### 9.2 Input Validation

| Validasi             | Implementasi                                                                   |
| -------------------- | ------------------------------------------------------------------------------ |
| XSS Filter           | `$this->input->post('field', true)` — CI XSS clean                             |
| No. Invoice Readonly | Field readonly di frontend; generated server-side (user tidak bisa manipulasi) |
| Outstanding Limit    | Server-side: `if ($dpp > $outstanding) reject`                                 |
| Jurnal Posted Guard  | `if (jurnal.sts == '1') reject update/delete`                                  |
| IDOR Prevention      | Verify invoice exists & belongs to valid penawaran before render               |
| Close Validation     | Alasan closing wajib diisi; affected_rows check untuk mencegah double-close    |

### 9.3 Transaction Safety

```php
$this->db->trans_begin();
try {
    // ... multiple DB operations ...
    $this->db->trans_commit();
} catch (Exception $e) {
    $this->db->trans_rollback();
    // return error response
}
```

---

## 10. COA Mapping

### 10.1 Invoice Sentral

| COA          | Nama                       | Posisi                           |
| ------------ | -------------------------- | -------------------------------- |
| `1102-01-01` | Piutang Dagang             | Debit: Total Akhir (DPP+PPN-PPh) |
| `2104-01-07` | PPN Keluaran               | Kredit: PPN                      |
| `1106-01-02` | PPh 23 Dibayar Dimuka      | Debit: PPh 23                    |
| `4101-01-01` | Pendapatan Jasa Konsultasi | Kredit: DPP                      |

### 10.2 Invoice VUCA

| COA          | Nama                       | Posisi                      |
| ------------ | -------------------------- | --------------------------- |
| `1102-01-01` | Piutang Dagang             | Debit: DPP - PPh            |
| `1106-01-05` | PPh 23 VUCA Dibayar Dimuka | Debit: PPh 0.5%             |
| `4101-01-01` | Pendapatan Jasa            | Kredit: DPP                 |
| `2104-01-07` | PPN Keluaran               | Kredit: 0 (tidak dikenakan) |

### 10.3 Invoice Non Konsultasi (Sentral)

| COA                         | Nama                         | Posisi             |
| --------------------------- | ---------------------------- | ------------------ |
| `1102-01-01`                | Piutang Dagang               | Debit: Total Akhir |
| `2104-01-07`                | PPN Keluaran                 | Kredit: PPN        |
| `1106-01-02`                | PPh 23 Dibayar Dimuka        | Debit: PPh 2%      |
| `4101-01-03` / `4101-01-07` | Pendapatan (user selectable) | Kredit: DPP        |

---

## 11. Known Design Decisions & Trade-offs

| Keputusan                            | Alasan                                                                                         |
| ------------------------------------ | ---------------------------------------------------------------------------------------------- |
| Auto-generate no_invoice server-side | Menghindari duplikat dan human error; sequence per entity/tahun; field readonly di frontend    |
| Entity code per company              | STM/VSB/SSC membedakan seri penomoran invoice antar entitas bisnis                             |
| Denormalized customer/project names  | Avoid cross-DB joins at read time; data snapshot saat invoice dibuat                           |
| Jurnal delete + re-insert on edit    | Simpler than partial update; safe karena ada guard "jurnal sudah posting"                      |
| Separate Sentral vs VUCA methods     | Berbeda COA, tarif PPh, dan layout print; code duplication tapi lebih maintainable             |
| print_keterangan save before print   | User bisa custom keterangan tiap kali print tanpa edit invoice                                 |
| saldo_piutang di tr_invoicing        | Denormalized balance; updated by penerimaan_uang module; enables fast query                    |
| Partial invoicing (Non Kons)         | Satu penawaran bisa punya multiple invoice selama total DPP <= grand_total penawaran           |
| Dual view methods (non kons)         | `view_invoicing_non_kons` (legacy) dan `view_invoice_non_kons` (newer, dengan IDOR prevention) |

---

## 12. Error Handling

| Scenario              | Handling                                                   |
| --------------------- | ---------------------------------------------------------- |
| DB insert gagal       | `trans_rollback()` + return JSON `{status: 0, msg: error}` |
| Jurnal sudah posting  | Block update, return message "jurnal sudah diposting"      |
| DPP > outstanding     | Return early, no DB write                                  |
| Invalid ID / IDOR     | Redirect ke index dengan flash message                     |
| Close sudah dilakukan | `affected_rows === 0` → throw exception                    |
| Exception uncaught    | `catch(Exception)` → rollback + HTTP 500 + JSON error      |
