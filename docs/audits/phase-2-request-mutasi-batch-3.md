# Fase 2 — Request Mutasi batch 3

Tanggal: 14 Oktober 2026. Branch main. Status: **selesai dan terverifikasi pada cakupan batch 3**.
Modul **Request Mutasi 21/21 endpoint (100% tuntas)**. Fase 2 **39/51 endpoint selesai** (Pembayaran Material 18/18, Request Mutasi 21/21, Pengajuan Rutin 0/12).

## Grounding

SHA controller awal batch 3: `9e82ae7c7f4f6c8a20d2b2757d3d55d0ff1d71698a22dce81751246a66556f97`.
Endpoint tunggal dalam batch penutup modul ini:
1. `getMataUangByKode` (read) — lookup nama mata uang berdasarkan kode mata uang dari tabel `mata_uang`.

| Endpoint | Proses dan metadata | Cabang hasil |
|---|---|---|
| `getMataUangByKode` | Lookup nama mata uang; currency (allowlist 3 huruf ISO), outcome (found, not_found, empty_code, query_error) | parameter kosong (`empty_code`), query gagal (`query_error`), data tidak ada di tabel (`not_found`), data ditemukan (`found`) |

Raw SQL query, parameter mentah panjang, dan kredensial tidak disertakan dalam metadata audit.

## Implementasi dan Keputusan Teknis

1. **Opt-in Recorder & Pencegahan Duplikasi Audit Scope:**
   - Endpoint `getMataUangByKode` diinstrumentasi menggunakan `$startedAudit = $this->beginAudit(...)`.
   - Jika dipanggil langsung (direct call), scope audit baru dimulai dan ditutup dengan `completeAudit`.
   - Jika dipanggil sebagai helper internal oleh endpoint `terbilang()`, `$startedAudit` bernilai `false` sehingga tidak membuat duplikat log atau memutus/menutup scope audit `terbilang()` yang sedang aktif.
2. **Penanganan Hasil dan Error Query:**
   - Status audit `1` (sukses) dicatat jika proses lookup berhasil dilakukan (baik mata uang ditemukan maupun data tidak ditemukan di tabel).
   - Status audit `0` (gagal) dicatat jika parameter kode mata uang kosong (`empty_code`) atau terjadi kegagalan query database (`query_error` via `$this->auditObserve($query)`).
3. **Penyelarasan Backward Compatibility:**
   - Signature function dipertahankan fleksibel `public function getMataUangByKode($kode = null)`.
   - Tidak menambahkan pembatasan permission baru di luar kode yang sudah ada.

## Hasil Pengujian dan Verifikasi

1. **Suite Batch 3 (`application/modules/request_mutasi/tests/Batch3_audit_test.php`):**
   - **PASS**: 10 fixture scenarios + 1 shutdown subprocess.
   - Menguji semua cabang: lookup found (USD, IDR), record not found (XYZ), empty code parameter ('', null), query error (error code 999), query false exception, writer failure immunity, dan penanganan delegasi internal dari `terbilang`.
2. **Suite Batch 1 & 2 Regresi:**
   - `Batch1_audit_test.php`: **PASS** (391 fixture scenarios + 2 shutdown subprocesses).
   - `Batch2_audit_test.php`: **PASS** (275 fixture scenarios + 2 shutdown subprocesses).
3. **Legacy Suites Mutasi:**
   - `CoaLookupTargetDbTest.php`: **PASS** (16 tests).
   - `DisplayLabelMappingTest.php`: **PASS** (11 tests).
   - `PersistenceRoundTripTest.php`: **PASS** (16 tests).
   - `TargetAccountingInheritanceTest.php`: **PASS** (12 tests).
   - `TargetAccountingResolverTest.php`: **PASS** (15 tests).
   - `JournalRoutingTest.php`: 13 PASS, 1 failure bawaan baseline simulator (identik dengan baseline awal sebelum perubahan).
4. **Pemeriksaan Statis:**
   - PHP Lint: 0 syntax error di seluruh file PHP yang diubah.
   - `git diff --check`: PASS (0 whitespace error).

## SHA dan Metrik Controller

- Controller: `application/modules/request_mutasi/controllers/Request_mutasi.php`
- SHA-256 akhir: `cd51f27eb6b46d3a2c7ea370079a37ece4a35a98733c57b04fc052382858f76d`
- Total baris: 1.180
