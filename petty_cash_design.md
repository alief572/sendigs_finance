# Design System: Petty Cash (Konsep Baru)

Dokumen ini merangkum desain sistem Petty Cash berdasarkan konsep baru yang ada di file referensi. Sistem ini terbagi menjadi 5 modul/bagian utama:

---

## 1. Master Petty Cash
Modul ini digunakan untuk mengatur data master dari Petty Cash.

### A. Index Master Petty Cash
Tabel utama untuk menampilkan daftar master petty cash yang sudah dibuat.

| No | Nama | Keterangan | Action |
|---|---|---|---|
| 1 | *tarik dari proses pembuatan master* | | View / Edit / Delete |
| 2 | *tarik dari proses pembuatan master* | | View / Edit / Delete |

### B. Action: Tambah / Edit Master
Formulir untuk menambah atau mengubah data master petty cash.

**Input Header:**
*   **Nama:** Input Nama Petty Cash
*   **Keterangan:** Input manual keterangan

**Input Detail Pengeluaran:**

| No | COA | Jenis Pengeluaran | Nominal |
|---|---|---|---|
| *Auto* | *Select COA* | *Input manual Jenis Pengeluaran* | *Input Nominal* |

---

## 2. Expense Petty Cash (Modul Utama)
Modul untuk melakukan pencatatan transaksi pengeluaran (expense) kas kecil.

### Index Sub Modul Transaksi Petty Cash
Menampilkan daftar pencatatan pengeluaran dengan filter dan status.

| Select | No | Nomor Pencatatan | Tanggal | Company | Request By | Keterangan | Nominal | Status | Action |
|---|---|---|---|---|---|---|---|---|---|
| [ ] | 1 | *tarik dari tambah pengeluaran* | | | | | | Draft | View / Edit / Delete |
| [ ] | 2 | *tarik dari tambah pengeluaran* | | | | | | Waiting Approval / Approved | View / Delete |
| [ ] | 3 | *tarik dari tambah pengeluaran* | | | | | | Reject | View / Edit / Delete |

> **Catatan Status:** Status yang tersedia meliputi Draft, Waiting Approval, Approved, dan Reject. Hak akses aksi (Edit/Delete) bergantung pada status dokumen.

---

## 3. Petty Cash VUCA & Sustain
Modul khusus untuk mengelola pelaporan dan pembayaran hutang Petty Cash untuk entitas VUCA & Sustain.

**Filter Pencarian:**
*   **Status:** Draft / Waiting Payment / Done Payment

### Tabel Index Pelaporan

| No Pelaporan | No Payment Hutang | Periode | Company | Jumlah Pencatatan | Grand Total Periode | Status | Action |
|---|---|---|---|---|---|---|---|
| RPC-2026-0001 | PHP-2026-0001 | *tarik dari pelaporan* | | | | Draft / Waiting Payment / Done Payment | Payment Hutang / View / Print |
| RPC-2026-0002 | PHP-2026-0002 | *tarik dari pelaporan* | | | | Draft / Waiting Payment / Done Payment | Payment Hutang / View / Print |

> **Workflow:** Ketika klik action **"Payment Hutang"**, maka pelaporan akan masuk ke request payment.

---

## 4. Simulasi Jurnal Payment Petty Cash
Aturan pencatatan akuntansi (jurnal) untuk setiap kejadian di dalam sistem.

### A. Jurnal STM (Kasus Normal)
**Case:** Staf STM mengajukan pengeluaran kas kecil (misal: Fotocopy Rp 300.000, Keperluan RT Rp 200.000). Total: Rp 500.000.

**Jurnal Pencatatan (Saat Pencatatan Expense):**
| Tanggal Jurnal | COA | Company | Nama Account | Debet | Kredit |
|---|---|---|---|---|---|
| 25/06/2026 | 5104-01-01 | STM | Fotocopy dan Jilid | Rp 300.000 | |
| 25/06/2026 | 6201-01-02 | STM | Keperluan Rumah Tangga | Rp 200.000 | |
| 25/06/2026 | 1101-01-02 | STM | Kas Kecil | | Rp 500.000 |
| **Balancing** | | | | **Rp 500.000** | **Rp 500.000** |

*Note: Jurnal Refill dilakukan saat payment pengisian kembali kas kecil.*

### B. Jurnal Lintas Entitas (Talangan VUCA oleh STM)
**Case:** Staf VUCA mengajukan pengeluaran operasional yang ditalangi oleh kas kecil STM.

**Sisi VUCA/SUSTAIN (Jurnal Pencatatan):**
| Tanggal Jurnal | COA | Company | Nama Account |
|---|---|---|---|
| 25/06/2026 | 5104-01-01 | VUCA | Fotocopy dan Jilid |
| 25/06/2026 | 6201-01-02 | VUCA | Keperluan Rumah Tangga |
| 25/06/2026 | *COA Hutang* | VUCA | Hutang ke STM |

**Sisi STM:** Akan ada *Jurnal Payment Hutang* (saat payment dibayarkan VUCA ke STM) dan *Jurnal Terima Uang STM* (saat STM menerima pembayaran hutang).

---

## 5. Report Petty Cash
Laporan untuk melacak mutasi kas kecil (running balance).

**Filter Pencarian:**
*   **Filter Periode:** `Pilih Tanggal Awal` - `Pilih Tanggal Akhir`

### Tabel Report (Mutasi / Buku Besar Kas Kecil)

| No | No Transaksi | Tanggal | COA | Company | Pengeluaran | Jenis Jurnal | Debit | Kredit | Saldo | Keterangan |
|---|---|---|---|---|---|---|---|---|---|---|
| | *saldo awal >>* | | | | | | | | **Rp 1.500.000** | Saldo Awal Petty Cash |
| 1 | PCP-2026-0001 | 01/06/2026 | 1101-01-02 | STM | Fotocopy materi training | Transaksi | | Rp 300.000 | Rp 1.200.000 | Jurnal otomatis transaksi |
| 2 | PCP-2026-0001 | 01/06/2026 | 1101-01-02 | STM | Pembelian Aqua kantor | Transaksi | | Rp 200.000 | Rp 1.000.000 | Jurnal otomatis transaksi |
| 3 | RPC-2026-0001 | 14/06/2026 | 1101-01-02 | STM | Refill Kas Kecil STM | Refill | Rp 1.100.000 | | Rp 2.100.000 | Dari Bank STM |

> **Aturan Report:**
> 1. **Debet:** Kas kecil BERTAMBAH (Sumber: Jurnal Refill - RPC)
> 2. **Kredit:** Kas kecil BERKURANG (Sumber: Jurnal Pencatatan Transaksi - PCP)
> 3. **Saldo (Running Balance):** Saldo Sebelumnya + Debet - Kredit
> 4. **Indikator Warna (UI):** Transaksi (Merah), Refill (Hijau)
