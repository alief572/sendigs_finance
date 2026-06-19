# Dokumentasi Modul Request Mutasi

## Ringkasan

Modul **Request Mutasi** adalah modul pada sistem SENDIGS Finance yang menangani proses pemindahan dana antar rekening bank (mutasi bank). Modul ini memiliki alur kerja bertahap mulai dari pengajuan request, approval, hingga realisasi mutasi beserta pencatatan jurnal akuntansi.

---

## Struktur File

```
application/modules/request_mutasi/
├── controllers/
│   └── Request_mutasi.php          # Controller utama
├── models/
│   └── Request_mutasi_model.php    # Model untuk query database
└── views/
    ├── index.php                   # Daftar request mutasi (belum terealisasi)
    ├── form.php                    # Form pembuatan request mutasi baru
    ├── index_approval.php          # Daftar request yang menunggu approval
    ├── approval.php                # Form approval/reject request
    ├── add.php                     # Form realisasi/aktual mutasi
    ├── mutasi.php                  # Daftar mutasi yang sudah terealisasi
    ├── index_transaksi.php         # Daftar transaksi bank (kas keluar/masuk)
    ├── form_transaksi.php          # Form pembuatan transaksi bank
    ├── printout.php                # Cetak request mutasi
    ├── print_mutasi.php            # Cetak realisasi mutasi
    └── print_transaksi.php         # Cetak transaksi bank
```

---

## Tabel Database yang Digunakan

| Tabel                         | Fungsi                                               |
| ----------------------------- | ---------------------------------------------------- |
| `tr_request_mutasi`           | Menyimpan data request mutasi                        |
| `tr_request_mutasi_aktual`    | Menyimpan data realisasi/aktual mutasi               |
| `tr_request_mutasi_admin`     | Menyimpan transaksi bank langsung (kas keluar/masuk) |
| `{DBACC}.coa_master`          | Master Chart of Account (rekening bank)              |
| `{DBACC}.jarh`                | Header jurnal penerimaan (BUM)                       |
| `{DBACC}.japh`                | Header jurnal pengeluaran (BUK)                      |
| `{DBACC}.jurnal`              | Detail jurnal (debit/kredit)                         |
| `{DBACC}.pastibisa_tb_cabang` | Counter nomor jurnal per cabang                      |
| `mata_uang`                   | Master mata uang                                     |

---

## Alur Kerja (Workflow)

### Fase 1: Pembuatan Request Mutasi

**URL:** `request_mutasi/create`

1. User membuka form pembuatan request mutasi
2. User mengisi field berikut:
   - **Tanggal** — tanggal request
   - **Dari** — bank asal (dropdown COA bank dengan prefix `1101`)
   - **Ke** — bank tujuan (dropdown COA bank dengan prefix `1101`)
   - **Keterangan** — penjelasan mutasi
   - **Mata Uang** — pilihan mata uang (IDR, USD, dll)
   - **Nilai** — jumlah nominal yang dimutasi
   - **Terbilang** — otomatis terisi berdasarkan nilai (via AJAX ke `request_mutasi/terbilang`)
3. Saat submit, JavaScript melakukan validasi client-side:
   - Semua field wajib terisi
   - Muncul konfirmasi SweetAlert sebelum simpan
4. Data dikirim via AJAX POST ke `request_mutasi/save`

**Proses di Controller `save()`:**

- Generate kode request otomatis format: `MTS-YYMM` + 5 digit urut (contoh: `MTS-260600001`)
- Validasi COA bank asal dan bank tujuan dari database akuntansi
- Insert data ke tabel `tr_request_mutasi` dengan `status = 0` (belum terealisasi) dan `status_approve = 0` (belum diapprove)
- Return JSON response (sukses/gagal)

---

### Fase 2: Approval Request

**URL:** `request_mutasi/approval_mutasi`

1. Approver melihat daftar request dengan `status_approve = 0`
2. Approver klik tombol approval pada salah satu request
3. Sistem menampilkan detail request (view `approval.php`)
4. Approver dapat melakukan:

#### A. Approve

- Klik tombol "Approve Request"
- AJAX POST ke `request_mutasi/save_approval`
- Update `tr_request_mutasi`:
  - `status_approve = 1`
  - `approved_by` = nama user
  - `approved_on` = timestamp

#### B. Reject

- Klik tombol "Reject Request"
- Muncul modal popup untuk mengisi alasan reject
- AJAX POST ke `request_mutasi/reject_mutasi`
- Update `tr_request_mutasi`:
  - `status_approve = 2`
  - `alasan` = alasan penolakan

---

### Fase 3: Realisasi Mutasi

**URL:** `request_mutasi/index` → klik tombol "Add Mutasi" (muncul setelah approved)

**Syarat:** Request sudah berstatus `status_approve = 1` (approved)

1. User membuka halaman index request mutasi
2. Pada request yang sudah di-approve, muncul tombol "Add Mutasi"
3. Klik tombol tersebut mengarah ke `request_mutasi/add_mutasi/{kd_mutasi}`
4. Form realisasi menampilkan:
   - **No Request** — readonly, dari data request
   - **Dari / Ke** — readonly, bank asal dan tujuan
   - **Mata Uang** — bisa pilih mata uang dari/ke
   - **Kurs** — nilai tukar (default 1 untuk IDR)
   - **Tanggal** — tanggal aktual mutasi (bisa diubah)
   - **Nilai Request** — readonly, jumlah yang diminta
   - **Aktual** — jumlah aktual yang direalisasi (bisa berbeda dari request)
5. Submit → AJAX POST ke `request_mutasi/save_mutasi`

**Proses di Controller `save_mutasi()`:**

- Generate kode mutasi aktual format: `MTR-YYMM` + 5 digit urut (contoh: `MTR-260600001`)
- Insert ke tabel `tr_request_mutasi_aktual`
- Update status request menjadi `status = 1` (sudah terealisasi, hilang dari daftar index)
- **Membuat jurnal akuntansi BUM (Bank Uang Masuk):**
  - Header jurnal di tabel `jarh`
  - Detail jurnal di tabel `jurnal`:
    - **Debit:** bank tujuan (uang masuk ke bank tujuan)
    - **Kredit:** bank asal (uang keluar dari bank asal)
  - Update counter nomor BUM di `pastibisa_tb_cabang`
  - Simpan nomor jurnal ke `tr_request_mutasi_aktual`

---

### Fase 4: Daftar Mutasi Terealisasi

**URL:** `request_mutasi/mutasi`

- Menampilkan semua mutasi yang sudah direalisasi dari tabel `tr_request_mutasi_aktual`
- Kolom: Tanggal Mutasi, Kode Mutasi Aktual, Kode Request, Keterangan, Bank Asal, Bank Tujuan, Nilai
- Tersedia tombol Print untuk mencetak bukti mutasi

---

## Fitur Tambahan: Transaksi Bank (Admin)

Selain alur request → approval → realisasi, modul ini juga menyediakan fitur transaksi bank langsung tanpa proses request/approval.

### Transaksi Kas Keluar (BUK)

**URL:** `request_mutasi/create_transaksi` (jenis: keluar)

- Kode otomatis: `KK-YYMM` + 5 digit urut
- Jurnal: tipe BUK (Bank Uang Keluar)
  - Debit: akun tujuan
  - Kredit: bank asal
- Disimpan di header `japh`

### Transaksi Kas Masuk (BUM)

**URL:** `request_mutasi/create_transaksi` (jenis: terima)

- Kode otomatis: `KM-YYMM` + 5 digit urut
- Jurnal: tipe BUM (Bank Uang Masuk)
  - Debit: bank tujuan
  - Kredit: bank asal
- Disimpan di header `jarh`

---

## Format Kode Otomatis

| Jenis          | Prefix | Format               | Contoh          |
| -------------- | ------ | -------------------- | --------------- |
| Request Mutasi | `MTS-` | `MTS-YYMM` + 5 digit | `MTS-260600001` |
| Mutasi Aktual  | `MTR-` | `MTR-YYMM` + 5 digit | `MTR-260600001` |
| Kas Keluar     | `KK-`  | `KK-YYMM` + 5 digit  | `KK-260600001`  |
| Kas Masuk      | `KM-`  | `KM-YYMM` + 5 digit  | `KM-260600001`  |

---

## Status Flow

```
┌──────────────────────────────────────────────────────────────────┐
│                     REQUEST MUTASI                                │
│                                                                  │
│  [Buat Request]                                                  │
│       │                                                          │
│       ▼                                                          │
│  status=0, status_approve=0                                      │
│  (Muncul di daftar request & daftar approval)                    │
│       │                                                          │
│       ├──── [Approve] ──► status_approve=1                       │
│       │                    (Muncul tombol "Add Mutasi")           │
│       │                         │                                │
│       │                         ▼                                │
│       │                    [Realisasi Mutasi]                     │
│       │                    status=1                               │
│       │                    (Hilang dari daftar request)           │
│       │                    (Muncul di daftar mutasi)              │
│       │                    + Jurnal BUM otomatis                  │
│       │                                                          │
│       └──── [Reject] ───► status_approve=2                       │
│                            (Ditandai "Ditolak" + alasan)         │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

---

## Permission / Hak Akses

| Permission              | Keterangan           |
| ----------------------- | -------------------- |
| `Request_mutasi.View`   | Melihat data         |
| `Request_mutasi.Add`    | Membuat request baru |
| `Request_mutasi.Manage` | Mengelola / edit     |
| `Request_mutasi.Delete` | Menghapus data       |

---

## Teknologi & Library

- **Framework:** CodeIgniter 3 (HMVC dengan Modular Extensions)
- **JavaScript:** jQuery, SweetAlert (konfirmasi & notifikasi), Select2 (dropdown), DataTables (tabel)
- **Number Format:** Plugin `number-divider.min.js` untuk format angka dengan koma
- **Template Engine:** Custom Template library dari Bonfire

---

## Diagram Alur Lengkap

```
User                    Approver                 System
 │                         │                       │
 │── Buat Request ────────────────────────────────►│
 │   (form.php → save)     │                       │── Insert tr_request_mutasi
 │                         │                       │   (status=0, status_approve=0)
 │                         │                       │
 │                         │◄── Notifikasi ────────│
 │                         │                       │
 │                         │── Approve/Reject ────►│
 │                         │   (approval.php)      │── Update status_approve
 │                         │                       │
 │◄── Request Approved ────│                       │
 │                         │                       │
 │── Realisasi Mutasi ───────────────────────────►│
 │   (add.php → save_mutasi)                       │── Insert tr_request_mutasi_aktual
 │                         │                       │── Update status=1
 │                         │                       │── Insert jurnal BUM (jarh + jurnal)
 │                         │                       │── Update counter nobum
 │                         │                       │
 │◄── Mutasi Selesai ─────────────────────────────│
 │   (muncul di list mutasi)                       │
```

---

## Catatan Penting

1. **Nomor request otomatis** — user tidak perlu input, di-generate berdasarkan bulan dan urutan terakhir
2. **Terbilang otomatis** — field terbilang terisi otomatis saat user blur dari field nilai (AJAX call)
3. **Jurnal otomatis** — saat realisasi mutasi disimpan, jurnal akuntansi (BUM) otomatis tercipta
4. **Selisih kurs** — pada realisasi mutasi, user bisa input kurs dan nilai aktual berbeda dari request
5. **Transaksi admin** — fitur transaksi bank langsung (`index_transaksi`) tidak memerlukan approval, langsung membuat jurnal
