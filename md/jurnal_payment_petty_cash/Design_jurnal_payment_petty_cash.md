# Design Document – Modul Jurnal Payment Petty Cash

## 1. Arsitektur Modul

```
application/modules/jurnal_payment_petty_cash/
├── controllers/
│   └── Jurnal_payment_petty_cash.php       # Main controller (extends Admin_Controller)
├── models/
│   ├── Jurnal_payment_petty_cash_model.php  # DataTables, posting logic, buku besar
│   └── Jurnal_payment_petty_cash_nomor_model.php  # BUK number generation & increment
├── views/
│   ├── index.php              # Halaman daftar jurnal (DataTables)
│   ├── modal_detail.php       # Partial view detail jurnal (AJAX-loaded)
│   └── buku_besar.php         # Halaman laporan buku besar
└── migrations/                # (tidak ada — menggunakan table existing)
```

---

## 2. Database Schema

### 2.1 Staging Table (sumber data modul ini)

**Table**: `tr_jurnal` (Database: DBERP / db_sendigs_ss)

| Column          | Type               | Keterangan                                                          |
| --------------- | ------------------ | ------------------------------------------------------------------- |
| id              | INT AUTO_INCREMENT | PK                                                                  |
| no_jurnal       | VARCHAR            | Nomor jurnal internal (dari expense module)                         |
| tgl_jurnal      | DATE               | Tanggal jurnal                                                      |
| coa             | VARCHAR            | Kode COA (no_perkiraan)                                             |
| nm_coa          | VARCHAR            | Nama COA                                                            |
| keterangan      | TEXT               | Keterangan baris                                                    |
| no_transaksi    | VARCHAR            | Nomor referensi (link ke payment_approve atau no_pencatatan)        |
| jenis_transaksi | VARCHAR            | Tipe: 'Petty Cash', 'Refill Pettycash', 'Payment Hutang Petty Cash' |
| id_company      | VARCHAR            | ID company ('4'=VUCA, '5'=STM, '6'=SUSTAIN)                         |
| nm_company      | VARCHAR            | Nama company                                                        |
| debit           | DECIMAL(15,2)      | Nilai debit                                                         |
| kredit          | DECIMAL(15,2)      | Nilai kredit                                                        |
| sts             | CHAR(1)            | Status: ''/'0' = pending, '1' = posted                              |
| created_by      | INT                | User yang create                                                    |
| created_date    | DATETIME           | Waktu create                                                        |

### 2.2 Target Tables (database akuntansi)

**Table**: `japh` (Header — ada di setiap DB akuntansi)

| Column       | Type    | Keterangan                                  |
| ------------ | ------- | ------------------------------------------- |
| nomor        | VARCHAR | Nomor BUK (PK)                              |
| tgl          | DATE    | Tanggal jurnal                              |
| jml          | DECIMAL | Total amount                                |
| kdcab        | VARCHAR | Kode cabang (default '101')                 |
| jenis_reff   | VARCHAR | Tipe referensi (default 'BUK')              |
| no_reff      | VARCHAR | Nomor referensi (no_transaksi dari staging) |
| bayar_kepada | VARCHAR | Nama company / penerima                     |
| user_id      | INT     | User yang posting                           |
| ho_valid     | VARCHAR | Validasi HO (kosong)                        |
| batal        | CHAR(1) | Flag batal ('0' = aktif)                    |

**Table**: `jurnal` (Detail — ada di setiap DB akuntansi)

| Column       | Type    | Keterangan                   |
| ------------ | ------- | ---------------------------- |
| tipe         | VARCHAR | Tipe jurnal ('BUK')          |
| nomor        | VARCHAR | Nomor BUK (FK ke japh.nomor) |
| tanggal      | DATE    | Tanggal jurnal               |
| no_perkiraan | VARCHAR | Kode COA                     |
| keterangan   | TEXT    | Keterangan                   |
| no_reff      | VARCHAR | Nomor referensi              |
| debet        | DECIMAL | Nilai debit                  |
| kredit       | DECIMAL | Nilai kredit                 |

### 2.3 Counter Table

**Table**: `pastibisa_tb_cabang` (ada di setiap DB akuntansi)

| Column  | Type    | Keterangan                    |
| ------- | ------- | ----------------------------- |
| nocab   | VARCHAR | Kode cabang (e.g., '101')     |
| subcab  | VARCHAR | Sub-cabang (e.g., 'A')        |
| kdcab   | VARCHAR | Kode area (e.g., 'YOG')       |
| nobuk   | INT     | Counter BUK (Buku Kas Keluar) |
| nobum   | INT     | Counter BUM (Buku Kas Masuk)  |
| nomorJP | INT     | Counter Jurnal Pembelian      |
| nomorJC | INT     | Counter Jurnal Penjualan      |
| nomorJM | INT     | Counter Jurnal Memorial       |

### 2.4 Referensi Table

**Table**: `payment_approve` (Database: DBERP)

| Column                | Type    | Keterangan                                         |
| --------------------- | ------- | -------------------------------------------------- |
| id                    | INT     | PK                                                 |
| id_payment            | VARCHAR | Link ke tr_jurnal.no_transaksi                     |
| no_doc                | VARCHAR | Nomor dokumen original                             |
| tipe                  | VARCHAR | Tipe payment (refill_pettycash, petty_cash_hutang) |
| keterangan_pembayaran | TEXT    | Keterangan                                         |
| jumlah                | DECIMAL | Jumlah pembayaran                                  |

---

## 3. Controller Design

### 3.1 Class Diagram

```
Jurnal_payment_petty_cash extends Admin_Controller
├── Properties
│   ├── $viewPermission   = 'Jurnal_Payment_Petty_Cash.View'
│   ├── $addPermission    = 'Jurnal_Payment_Petty_Cash.Add'
│   ├── $managePermission = 'Jurnal_Payment_Petty_Cash.Manage'
│   ├── $accounting_stm   (CI_DB connection)
│   ├── $accounting_vuca   (CI_DB connection)
│   └── $accounting_sustain (CI_DB connection)
│
├── Public Methods
│   ├── index()                 → Render halaman daftar jurnal
│   ├── get_data_jurnal()       → DataTables server-side endpoint
│   ├── get_detail_jurnal()     → Load detail untuk modal
│   ├── save_posting_jurnal()   → Proses posting ke DB akuntansi
│   ├── buku_besar()            → Render halaman buku besar
│   ├── get_data_buku_besar()   → Load data buku besar (AJAX)
│   ├── export_buku_besar()     → Download Excel
│   └── revisi_jurnal()         → Update status jurnal
│
└── Private Methods
    ├── _detect_refill_from_rows($rows)  → Deteksi skenario refill
    ├── _post_stm(...)                   → Logic posting STM internal
    ├── _post_intercompany(...)          → Logic posting inter-company
    └── _post_refill(...)                → Logic posting refill
```

### 3.2 Request Flow

```
[User Browser]
    │
    ├─ GET /jurnal_payment_petty_cash
    │   └─ → index() → render views/index.php (DataTables)
    │
    ├─ POST /jurnal_payment_petty_cash/get_data_jurnal
    │   └─ → Model::get_server_side_data($post) → JSON
    │
    ├─ POST /jurnal_payment_petty_cash/get_detail_jurnal
    │   └─ → Model::get_detail_by_transaksi() → render modal_detail.php → JSON {html, is_balance}
    │
    ├─ POST /jurnal_payment_petty_cash/save_posting_jurnal
    │   └─ → validate → determine scenario → generate BUK → post → increment → update sts → JSON
    │
    ├─ GET /jurnal_payment_petty_cash/buku_besar
    │   └─ → render views/buku_besar.php
    │
    ├─ POST /jurnal_payment_petty_cash/get_data_buku_besar
    │   └─ → Model::get_saldo_awal() + get_buku_besar_data() → running balance → JSON
    │
    └─ GET /jurnal_payment_petty_cash/export_buku_besar
        └─ → PHPExcel → stream .xlsx download
```

---

## 4. Model Design

### 4.1 Jurnal_payment_petty_cash_model

**Extends**: `BF_Model`  
**Table**: `tr_jurnal`

| Method                                                                                      | Return           | Keterangan                                            |
| ------------------------------------------------------------------------------------------- | ---------------- | ----------------------------------------------------- |
| `get_server_side_data($params)`                                                             | array            | DataTables: draw, recordsTotal, recordsFiltered, data |
| `get_detail_by_transaksi($no_transaksi, $jenis_transaksi)`                                  | array of objects | Detail rows filtered debit > 0 OR kredit > 0          |
| `validate_balance($rows)`                                                                   | bool             | Check SUM(debit) == SUM(kredit)                       |
| `post_jurnal_stm($header, $details, $nomor_buk)`                                            | bool             | Insert ke DBACC_STM (japh + jurnal)                   |
| `post_jurnal_intercompany($company, $header, $details, $nomor_buk_company, $nomor_buk_stm)` | bool             | Insert ke 2 DB sekaligus                              |
| `post_jurnal_refill($header, $details, $nomor_buk, $target_db)`                             | bool             | Insert ke 1 target DB                                 |
| `update_status_posted($no_transaksi, $jenis_transaksi)`                                     | bool             | Set sts = '1'                                         |
| `get_buku_besar_data($tgl_from, $tgl_to)`                                                   | array            | Posted transactions dalam periode                     |
| `get_saldo_awal($tgl_from)`                                                                 | float            | Opening balance sebelum tgl_from                      |
| `get_company_filter()`                                                                      | array            | Distinct companies untuk filter dropdown              |
| `begin_transaction($db_name)`                                                               | void             | Start transaction on specific DB                      |
| `commit_transaction($db_name)`                                                              | void             | Commit transaction on specific DB                     |
| `rollback_transaction($db_name)`                                                            | void             | Rollback transaction on specific DB                   |
| `check_transaction_status($db_name)`                                                        | bool             | Check trans_status on specific DB                     |

### 4.2 Jurnal_payment_petty_cash_nomor_model

**Extends**: `CI_Model`

| Method                               | Return       | Keterangan                                           |
| ------------------------------------ | ------------ | ---------------------------------------------------- |
| `get_nomor_buk($cabang, $db_name)`   | string\|null | Generate BUK number: {nocab}BK{subcab}{yy}{sequence} |
| `increment_nobuk($cabang, $db_name)` | bool         | UPDATE nobuk = nobuk + 1                             |
| `get_db_connection($db_name)`        | CI_DB\|null  | Resolve DB connection by name                        |

---

## 5. Posting Flow (Sequence Diagram)

### 5.1 STM Internal

```
User → Controller → Model.get_detail_by_transaksi()
                  → Model.validate_balance() → TRUE
                  → db.trans_begin() + accounting_stm.trans_begin()
                  → Nomor_model.get_nomor_buk('101', 'accounting_stm')
                  → Model.post_jurnal_stm(header, details, nomor_buk)
                      ├─ INSERT INTO japh (header)
                      └─ INSERT BATCH INTO jurnal (details)
                  → Nomor_model.increment_nobuk('101', 'accounting_stm')
                  → Model.update_status_posted(no_transaksi, jenis_transaksi)
                  → Check all trans_status()
                  → db.trans_commit() + accounting_stm.trans_commit()
                  → JSON {status: 1, msg: 'Berhasil'}
```

### 5.2 Inter-Company (VUCA/SUSTAIN)

```
User → Controller → validate...
                  → db.trans_begin() + stm.trans_begin() + company.trans_begin()
                  → Nomor_model.get_nomor_buk('101', db_company) → nomor_buk_company
                  → Nomor_model.get_nomor_buk('101', 'accounting_stm') → nomor_buk_stm
                  → Model.post_jurnal_intercompany(company, header, details, nomor_buk_company, nomor_buk_stm)
                      ├─ [Company Side] INSERT japh + INSERT BATCH jurnal ke target_db
                      └─ [STM Side]     INSERT japh + INSERT BATCH jurnal ke accounting_stm
                  → Nomor_model.increment_nobuk('101', db_company)
                  → Nomor_model.increment_nobuk('101', 'accounting_stm')
                  → Model.update_status_posted()
                  → Check ALL trans_status()
                  → Commit ALL / Rollback ALL
                  → JSON response
```

### 5.3 Refill

```
User → Controller → validate...
                  → Determine target_db from nm_company (STM→stm, VUCA→vuca, SUSTAIN→sustain)
                  → db.trans_begin() + target_db.trans_begin()
                  → Nomor_model.get_nomor_buk('101', target_db)
                  → Model.post_jurnal_refill(header, details, nomor_buk, target_db)
                      ├─ INSERT INTO japh
                      └─ INSERT BATCH INTO jurnal
                  → Nomor_model.increment_nobuk('101', target_db)
                  → Model.update_status_posted()
                  → Commit / Rollback
                  → JSON response
```

---

## 6. UI Design

### 6.1 Halaman Index (Daftar Jurnal)

```
┌──────────────────────────────────────────────────────────────┐
│ [Box Header]                                                 │
│   Company: [Select2 Dropdown ▼]  [🔍 Search] [🔄 Reset]     │
├──────────────────────────────────────────────────────────────┤
│ [DataTable: Server-Side, 10/25/50/100 per page]              │
│                                                              │
│  No │ Tanggal      │ No Transaksi │ Tipe     │ Company │ Ket │ Debit    │ Kredit   │ Action │
│  1  │ 10 Juli 2026 │ PCP-2026-001 │ Petty Ca │ STM     │ ... │ 500.000  │ 500.000  │ [View] │
│  2  │ 09 Juli 2026 │ RPC-2026-002 │ Refill P │ VUCA    │ ... │ 1.000.0  │ 1.000.0  │ [View] │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

### 6.2 Modal Detail Jurnal

```
┌──────────────────────────────────────────────────────────────────────────┐
│ ✕  📖 Detail Jurnal Petty Cash                                          │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│ [Label: Sisi STM]                                                        │
│ ┌─────────────────────────────────────────────────────────────────────┐  │
│ │ Tanggal   │ Tipe       │ COA       │ Nama Account  │ Ket │ Reff │D │K│ │
│ │ 10/07/26  │ Petty Cash │ 6101-01-1 │ Beban ATK     │ ... │ PCP  │500│0│ │
│ │ 10/07/26  │ Petty Cash │ 1101-01-2 │ Kas Kecil     │ ... │ PCP  │0  │500│
│ ├─────────────────────────────────────────────────────────────────────┤  │
│ │                              Balancing │ Rp 500.000  │ Rp 500.000  │  │
│ │                                        │  (HIJAU ✓)  │  (HIJAU ✓)  │  │
│ └─────────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│ [Inter-Company: Tampil section kedua jika VUCA/SUSTAIN]                  │
│ [Label: Sisi VUCA]                                                       │
│ ┌─────────────────────────────────────────────────────────────────────┐  │
│ │ ...rows for VUCA side...                                            │  │
│ └─────────────────────────────────────────────────────────────────────┘  │
│                                                                          │
├──────────────────────────────────────────────────────────────────────────┤
│                              [💾 Posting Jurnal]  [✕ Close]              │
└──────────────────────────────────────────────────────────────────────────┘
```

### 6.3 Halaman Buku Besar

```
┌──────────────────────────────────────────────────────────────┐
│ Laporan Buku Besar Kas Kecil                                 │
├──────────────────────────────────────────────────────────────┤
│ Tanggal Dari: [____📅] Tanggal Sampai: [____📅]             │
│ [🔍 Search]  [📊 Export Excel] (muncul setelah search)      │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  No │ No Trx │ Tanggal │ COA │ Company │ Pengeluaran │ Jenis │ Debit │ Kredit │ Saldo │ Ket │
│  -  │   -    │    -    │  -  │    -    │      -      │   -   │   -   │    -   │ 5.000 │ Saldo Awal │ ← kuning
│  1  │ PCP-001│ 10/07  │6101 │  STM    │ Beban ATK   │Trx    │  500  │    0   │ 4.500 │ ATK  │ ← merah
│  2  │ RPC-001│ 10/07  │1101 │  STM    │ Kas Kecil   │Refill │    0  │ 2.000  │ 6.500 │ Refill│ ← hijau
│                                                              │
└──────────────────────────────────────────────────────────────┘

Warna baris:
- Kuning : Saldo Awal
- Merah  : Transaksi pengeluaran (row-transaksi)
- Hijau  : Refill/pengisian (row-refill)
```

---

## 7. API Endpoints

| Method | URL                                              | Input                                         | Output                                    | Auth   |
| ------ | ------------------------------------------------ | --------------------------------------------- | ----------------------------------------- | ------ |
| GET    | `/jurnal_payment_petty_cash`                     | -                                             | HTML page                                 | View   |
| POST   | `/jurnal_payment_petty_cash/get_data_jurnal`     | DataTables params + company                   | JSON DataTables                           | View   |
| POST   | `/jurnal_payment_petty_cash/get_detail_jurnal`   | id                                            | JSON {html, is_balance}                   | View   |
| POST   | `/jurnal_payment_petty_cash/save_posting_jurnal` | no_transaksi, jenis_transaksi, jenis_posting? | JSON {status, msg}                        | Add    |
| GET    | `/jurnal_payment_petty_cash/buku_besar`          | -                                             | HTML page                                 | View   |
| POST   | `/jurnal_payment_petty_cash/get_data_buku_besar` | tgl_from, tgl_to                              | JSON {saldo_awal, transactions, has_data} | View   |
| GET    | `/jurnal_payment_petty_cash/export_buku_besar`   | tgl_from, tgl_to (query params)               | .xlsx file download                       | View   |
| POST   | `/jurnal_payment_petty_cash/revisi_jurnal`       | (varies)                                      | JSON {status, msg}                        | Manage |

---

## 8. Error Handling

| Kondisi                             | Response                                                     | HTTP |
| ----------------------------------- | ------------------------------------------------------------ | ---- |
| Tidak punya permission              | `{status: 0, msg: 'Anda tidak memiliki akses...'}`           | 200  |
| Data tidak ditemukan / sudah posted | `{status: 0, msg: 'Data tidak valid atau sudah diposting'}`  | 200  |
| Tidak balance                       | `{status: 0, msg: 'Jurnal tidak balance...'}`                | 200  |
| Gagal generate BUK                  | `{status: 0, msg: 'Gagal generate nomor BUK...'}`            | 200  |
| Gagal insert ke DB akuntansi        | `{status: 0, msg: 'Gagal insert jurnal...'}` + rollback      | 200  |
| Gagal update counter                | `{status: 0, msg: 'Gagal update counter BUK...'}` + rollback | 200  |
| Company tidak dikenali              | `{status: 0, msg: 'Company tidak dikenali...'}`              | 200  |
| AJAX timeout (client)               | SweetAlert error "Request timeout..."                        | -    |

---

## 9. Technology Stack

| Layer         | Technology                               |
| ------------- | ---------------------------------------- |
| Backend       | PHP 5.6+, CodeIgniter 3.x HMVC           |
| Base Model    | BF_Model (Bonfire)                       |
| Database      | MySQL 8, mysqli driver, 3 DB connections |
| Frontend      | AdminLTE (Bootstrap 3), jQuery           |
| DataTables    | v2.1.7, server-side processing           |
| UI Components | Select2 4.1.0, SweetAlert2 11            |
| Export        | PHPExcel (Excel2007 writer)              |
| Icons         | Font Awesome 4.x                         |

---

## 10. Security Considerations

| Concern              | Mitigation                                                                 |
| -------------------- | -------------------------------------------------------------------------- |
| Unauthorized posting | Permission check `Jurnal_Payment_Petty_Cash.Add` pada endpoint             |
| SQL Injection        | CI Active Record / Query Binding (`$this->db->get_where()`, parameterized) |
| Double posting       | Status check `sts IN ('', '0')` — hanya data pending yang tampil           |
| Data corruption      | Multi-DB transaction dengan full rollback on failure                       |
| XSS                  | `htmlspecialchars()` pada semua output view                                |
| CSRF                 | CI built-in CSRF token (form action)                                       |

---

## 11. DataTables Query Pattern

### Base Query (grouped)

```sql
SELECT
    MAX(a.id) as id,
    MAX(a.tgl_jurnal) as tgl_jurnal,
    MAX(p.no_transaksi) as no_transaksi,
    a.jenis_transaksi,
    MAX(a.nm_company) as nm_company,
    MAX(a.id_company) as id_company,
    MAX(p.keterangan_pembayaran) as keterangan,
    MAX(p.total_jumlah) as total_debit,
    MAX(p.total_jumlah) as total_kredit
FROM tr_jurnal a
INNER JOIN (
    SELECT id_payment as join_key, MAX(tipe) as tipe,
           GROUP_CONCAT(no_doc SEPARATOR ", ") as no_transaksi,
           GROUP_CONCAT(keterangan_pembayaran SEPARATOR ", ") as keterangan_pembayaran,
           SUM(jumlah) as total_jumlah
    FROM payment_approve GROUP BY id_payment
    UNION
    SELECT CAST(id AS CHAR), tipe, no_doc, keterangan_pembayaran, jumlah
    FROM payment_approve
) p ON p.join_key = a.no_transaksi
WHERE a.jenis_transaksi IN ('Refill Pettycash', 'Payment Hutang Petty Cash')
  AND (p.tipe IN ('refill_pettycash', 'petty_cash_hutang') OR p.no_transaksi LIKE '%RPC%')
  AND a.sts IN ('', '0')
GROUP BY a.no_transaksi, a.jenis_transaksi
```

---

## 12. Relationship Diagram

```
┌─────────────────────┐         ┌──────────────────────┐
│  expense_petty_cash │         │   payment_approve    │
│  (Pencatatan)       │         │                      │
│                     │         │ id_payment → links   │
│  → creates entries  │         │ to tr_jurnal         │
│    in tr_jurnal     │         │ .no_transaksi        │
└──────────┬──────────┘         └──────────┬───────────┘
           │                               │
           ▼                               ▼
┌──────────────────────────────────────────────────────┐
│                    tr_jurnal                          │
│              (Staging Table - DBERP)                  │
│                                                      │
│  sts='0' = pending    sts='1' = posted               │
└──────────────────────────┬───────────────────────────┘
                           │
              ┌────────────┼─────────────┐
              │            │             │
              ▼            ▼             ▼
    ┌─────────────┐ ┌──────────────┐ ┌──────────────────┐
    │  DBACC_STM  │ │  DBACC_VUCA  │ │  DBACC_SUSTAIN   │
    │             │ │              │ │                  │
    │  japh       │ │  japh        │ │  japh            │
    │  jurnal     │ │  jurnal      │ │  jurnal          │
    │  pastibisa_ │ │  pastibisa_  │ │  pastibisa_      │
    │  tb_cabang  │ │  tb_cabang   │ │  tb_cabang       │
    └─────────────┘ └──────────────┘ └──────────────────┘
```

---

## 13. Configuration

### Database Connections (application/config/development/database.php)

```php
$db['accounting_stm'] = array(
    'hostname' => '...',
    'database' => 'db_sendigs_ss_stm',
    ...
);

$db['accounting_vuca'] = array(
    'hostname' => '...',
    'database' => 'db_sendigs_ss_vuca',
    ...
);

$db['accounting_sustain'] = array(
    'hostname' => '...',
    'database' => 'db_sendigs_ss_sustain',
    ...
);
```

### Menu Entry

Module terdaftar di sistem menu dengan:

- **Parent**: Finance / Jurnal
- **Label**: Jurnal Payment Petty Cash
- **Icon**: `fa fa-building-o`
- **Sub-menu**: Buku Besar (link ke `/jurnal_payment_petty_cash/buku_besar`)

---

## 14. Future Considerations

| Item              | Catatan                                                |
| ----------------- | ------------------------------------------------------ |
| Batch posting     | Posting multiple transaksi sekaligus (select checkbox) |
| Undo/void posting | Reverse posted jurnal tanpa direct DB manipulation     |
| Notification      | Alert ke finance jika ada jurnal pending > 3 hari      |
| Audit log         | Dedicated audit table untuk setiap aksi posting/revisi |
| Auto-posting      | Scheduler untuk auto-post jurnal yang sudah balance    |
