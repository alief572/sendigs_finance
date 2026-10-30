## Print lampiran PDF Periodik Full Halaman (A4) — 30 Oktober 2026

Tujuan: Di modul expense/periodik_print, jika attachment berupa PDF, tampilkan berkas PDF tersebut menjadi 1 halaman penuh (A4 utuh, proporsional, per halaman tanpa container iframe terpotong). Pola mengikuti arsitektur yang sudah terbukti pada transport_req_print.php dan assets/js/transport-print.js.

1. Fase 1: Analisis & Spesifikasi Pengujian (RED Test) - Buat unit test Node.js application/modules/expense/tests/Periodik_print_js_test.js.
2. Fase 2: Implementasi Script & View - Buat assets/js/periodik-print.js dan perbarui application/modules/expense/views/periodik_print.php.
3. Fase 3: Validasi & Regresi - Jalankan Periodik_print_js_test.js, Transport_print_js_test.js, PHP lint, Batch1_audit_test.php, dan git diff --check.

Status: Seluruh fase selesai. Kriteria keberhasilan terpenuhi 100%.

# Implementation Plan: Penyelarasan Jalur Posting Tab Pembayaran Jurnal All Mengikuti Entitas Jurnal Payment (VUCA)

Dokumen perencanaan teknis ini disusun untuk menyelaraskan jalur posting transaksi pada modul **Jurnal All (Tab Pembayaran)** agar mengikuti aturan entitas perusahaan yang benar sebagaimana diakui pada modul **Jurnal Payment**.

---

## 1. Analisis Kebutuhan & Hasil Investigasi

### Keputusan User
- Jalur yang benar adalah aturan entitas pada modul `jurnal_payment`, di mana company `STM-Vuca` diakui sebagai entitas **VUCA**.
- Jalur posting pada modul `jurnal_all` di tab Pembayaran harus diselaraskan 100% mengikuti aturan tersebut sehingga data `STM-Vuca` masuk ke **`db_sendigs_ss_vuca_dev` (`accounting_vuca`)**.

### Pemetaan Lengkap Multi-Entity
1. **ID 1 (`STM-Vuca`) & ID 4 (`Vuca`)** $\rightarrow$ Entitas **VUCA** $\rightarrow$ Database: `db_sendigs_ss_vuca_dev` (`accounting_vuca`).
2. **ID 6 (`STM-Sustain`) & ID 3 (`Sustain`)** $\rightarrow$ Entitas **Sustain** $\rightarrow$ Database: `db_sendigs_ss_sustain_dev` (`accounting_sustain`).
3. **ID 7 (`STM`)** $\rightarrow$ Entitas **STM** $\rightarrow$ Database: `db_sendigs_ss_stm_dev` (`accounting_stm`).

---

## 2. Rincian Perubahan Kode

### Modul Jurnal All
1. `application/modules/jurnal_all/libraries/Payment_reference.php`:
   - `target($company)`: petakan `['1', '4']` ke `accounting_vuca`, `['3', '6']` ke `accounting_sustain`, `['7']` ke `accounting_stm`.
2. `application/modules/jurnal_all/models/Jurnal_all_model.php`:
   - `getGlDb($companyId)` & `resolveGlMetadata(&$rows)`: selaraskan penentuan database accounting dan GL connection selector.

### Modul Jurnal Payment
1. `application/modules/jurnal_payment/models/Jurnal_payment_nomor_model.php`:
   - `get_no_buk()` & `get_Nomor_Jurnal_Sales()`: ambil counter BUK/JC dari database VUCA bila `$id_company == '4' || $id_company == '1'`.
2. `application/modules/jurnal_payment/controllers/Jurnal_payment.php`:
   - `_get_acc_db()`: arahkan `$id_company == '1' || $id_company == '4'` ke `accounting_vuca` (`DBACC_VUCA`).

### Rangkaian Pengujian & Test Fixtures
1. `application/modules/jurnal_all/tests/Payment_reference_test.php`:
   - Perbarui loop company target agar menguji ID 1 ke `accounting_vuca` dan ID 6 ke `accounting_sustain`.
2. Konfigurasi verifikasi histori dan read-only checks diselaraskan.

---

## 4. Status Implementasi: SELESAI (29 Oktober 2026)
- Seluruh perubahan pada `Payment_reference.php`, `Jurnal_all_model.php`, `Jurnal_payment_nomor_model.php`, `Jurnal_payment.php`, `Payment_reference_test.php`, dan `Legacy_posting_test.php` telah diimplementasikan secara komprehensif.
- Seluruh 15 test suite unit dan regresi telah dijalankan via container Docker `sendigs_ss` dengan status 100% PASS tanpa error.
- Checkpoint dan dokumentasi kemajuan pekerjaan telah dicatat di `docs/task-progress.md`.
