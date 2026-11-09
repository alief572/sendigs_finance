# Fase 2 — Request Mutasi batch 2

Tanggal: 13 Oktober 2026. Branch main. Status: **selesai dan terverifikasi pada cakupan batch 2**. Request Mutasi 20/21 endpoint, Fase 2 38/51 endpoint. Seluruh 24 suite audit dan regresi PASS.

## Grounding

SHA controller awal batch 2: `e54737d2f9d8d6411d51a66ff31526487eecda39d529bfa4e8dc3a73c09b3fcb`. Daftar sepuluh endpoint cocok dengan deklarasi controller dan manifest baseline. Baca controller, model `Request_mutasi_model`, model shared `Jurnal_model`, `Auth`, helper `app_helper` (`ynz_terbilang`), dan views printout terkait.

Sepuluh endpoint dalam batch ini:
1. `reject_mutasi` (write) — penolakan pengajuan mutasi
2. `index_transaksi` (read) — daftar transaksi mutasi bank admin
3. `create_transaksi` (read) — form pencatatan transaksi mutasi bank admin
4. `save_transaksi` (write) — simpan transaksi mutasi bank admin ke `tr_request_mutasi_admin` dan picu pembuatan jurnal BUM/BUK
5. `save_jurnal_BUM` (write) — pembentukan dan penulisan jurnal BUM transaksi ke target accounting database
6. `save_jurnal_BUK` (write) — pembentukan dan penulisan jurnal BUK transaksi ke target accounting database
7. `printout` (read/render) — cetak dokumen request mutasi
8. `printout_mutasi` (read/render) — cetak dokumen realisasi mutasi aktual
9. `printout_transaksi` (read/render) — cetak dokumen bukti transaksi mutasi bank admin
10. `terbilang` (read/helper) — kalkulasi teks terbilang nominal dan nama mata uang

| Endpoint | Proses dan metadata | Cabang hasil |
|---|---|---|
| reject_mutasi | Penolakan request; request_id, outcome: rejected | validasi id gagal, sumber tidak ditemukan (404), begin/update/commit gagal, sukses |
| index_transaksi | Daftar transaksi admin; row_count | isi/kosong, query gagal |
| create_transaksi | Form transaksi; row_count | query invoice/customer/COA/template/kurs/bank, render gagal |
| save_transaksi | Simpan transaksi mutasi + delegasi jurnal; request_id, date, currency, amount, target_accounting, origin_coa, destination_coa, transaction_type, outcome: saved | input invalid, target invalid/unavailable, generator notr/nokm gagal, COA asal/tujuan hilang/error, begin/insert gagal, delegasi jurnal gagal, commit gagal, sukses |
| save_jurnal_BUM | Simpan jurnal BUM transaksi; request_id, journal_number, target_accounting, amount, outcome: saved | dokumen admin hilang, target invalid, generator nomor JV gagal, header jarh/detail jurnal/counter nobum/reference jurnal1 gagal, sukses |
| save_jurnal_BUK | Simpan jurnal BUK transaksi; request_id, journal_number, target_accounting, amount, outcome: saved | dokumen admin hilang, target invalid, generator nomor JV gagal, header japh/detail jurnal/counter nobuk/reference jurnal2 gagal, sukses |
| printout | Render cetak request; request_id, outcome: rendered | id kosong/invalid, data tidak ditemukan (show_404), query gagal, sukses |
| printout_mutasi | Render cetak realisasi; actual_id, request_id, outcome: rendered | id kosong/invalid, data aktual tidak ditemukan (show_404), request asal hilang/ada, render gagal, sukses |
| printout_transaksi | Render cetak transaksi admin; request_id, outcome: rendered | id kosong/invalid, data tidak ditemukan (show_404), query gagal, sukses |
| terbilang | AJAX pembacaan terbilang; amount, currency, outcome: calculated | nilai/mata uang valid/kosong/variasi mata uang |

Nama bank, nomor rekening lengkap, keterangan bebas, kata sandi, dan SQL mentah tidak disertakan dalam metadata audit.

## Implementasi dan Keputusan Teknis

1. **Opt-in Recorder & Pencegahan Duplikasi Delegasi:**
   - Seluruh 10 endpoint diinstrumentasi dengan recorder `beginAudit` dan `completeAudit` secara eksplisit.
   - Endpoint `save_jurnal_BUM` dan `save_jurnal_BUK` dapat dipanggil secara langsung sebagai endpoint independen, maupun didelegasikan dari `save_transaksi`.
   - Menggunakan guard `$startedAudit = $this->beginAudit(...)`: jika dipanggil langsung, ia mencatat audit miliknya sendiri; jika dipanggil dari `save_transaksi`, scope `save_transaksi` dipertahankan dan penyelesaian audit tetap dilakukan oleh `save_transaksi` setelah commit transaksi utama berhasil.
2. **Commit Nyata Sebelum Audit Sukses:**
   - `reject_mutasi`, `save_transaksi`, `save_jurnal_BUM`, dan `save_jurnal_BUK` hanya mencatat audit sukses setelah commit transaksi database terverifikasi berhasil.
   - Kegagalan query, begin, insert, update, atau commit mencatat status audit 0 dengan reason kegagalan terstruktur.
3. **Penyelarasan Generator Kode:**
   - Helper generator `generate_notr` dan `generate_nokm` pada `Request_mutasi_model` diperluas dengan parameter referensi opsional `&$auditQueriesOk = null` dan parameter binding untuk query LIKE, mencegah hilangnya bukti error query generator pertama.
4. **Proteksi Input & Akses Auth:**
   - Pemanggilan `$this->auth->user_id()` diproteksi dengan `method_exists` agar kompatibel dengan test double dan library session.
   - Pembacaan input `terbilang` menggunakan `$_GET` dengan null coalescing untuk menjaga kompatibilitas pemanggilan AJAX tanpa memicu E_NOTICE.
5. **Metadata Terbatas & Sanitasi:**
   - Metadata yang dicatat dibatasi pada allowlist yang telah disetujui: `request_id`, `actual_id`, `date`, `currency`, `amount`, `rate`, `target_accounting`, `origin_coa`, `destination_coa`, `transaction_type`, `row_count`, `journal_number`, `reason`, dan `outcome`.
   - Keterangan teks bebas dan alasan penolakan disaring dari metadata log.

## Hasil Verifikasi

- **Suite Baru `Batch2_audit_test.php` PASS: 276 skenario fixture + 2 shutdown subprocess nyata (exit 0).**
  - Menguji kesepuluh endpoint pada berbagai skenario: STM/VUCA/Sustain, IDR/USD, transaksi keluar (BUK)/terima (BUM)/lain, generator notr/nokm, COA valid/invalid/missing, transaksi rollback/commit, writer failure/exception isolasi, 404 show handler, terbilang variations, delegasi jurnal, dan 2 subprocess penghentian paksa.
- **Suite `Batch1_audit_test.php` PASS: 392 skenario fixture + 2 shutdown subprocess nyata (exit 0).**
  - Satu-satunya metode yang diverifikasi berstatus pending adalah `getMataUangByKode` (Batch 3).
- **Semua 17 Suite Regresi Audit Sebelumnya PASS (exit 0):**
  - Tujuh suite fondasi Audit Trail (Logger, Recorder, Legacy, Auth, Controller, Query, Menu)
  - Modul PR Stok (24 endpoint)
  - Lima batch Request Payment (Batch 1–5, 46 endpoint)
  - Approval Request Payment (4 endpoint)
  - Record Request Payment (5 endpoint)
  - Dua batch Pembayaran Material (Batch 1–2, 18 endpoint)
- **Lima Suite Legacy Request Mutasi PASS (exit 0):**
  - `CoaLookupTargetDbTest` (16 skenario)
  - `DisplayLabelMappingTest` (11 skenario)
  - `PersistenceRoundTripTest` (16 skenario)
  - `TargetAccountingInheritanceTest` (12 skenario)
  - `TargetAccountingResolverTest` (15 skenario)
- **Total Pengujian Keseluruhan: 24 SUITE AUDIT & REGRESI PASS.**
- **PHP Lint 4 Berkas PHP Relevan: 0 kesalahan sintaks.**
- **Git Diff Whitespace Check: `git -c core.fsmonitor=false diff --check` PASS (0 error).**

## File Berubah dan Alasan

| File | Alasan Perubahan |
|---|---|
| `application/modules/request_mutasi/controllers/Request_mutasi.php` | Instrumentasi opt-in audit recording pada 10 endpoint Batch 2, penanganan delegasi jurnal, proteksi commit & sanitasi metadata |
| `application/modules/request_mutasi/models/Request_mutasi_model.php` | Penambahan parameter referensi status query opsional dan query binding pada `generate_notr` dan `generate_nokm` |
| `application/modules/request_mutasi/tests/Batch1_audit_test.php` | Penyesuaian fixture row dengan field transaksi/nobuk dan pembaruan pending check ke `getMataUangByKode` |
| `application/modules/request_mutasi/tests/Batch2_audit_test.php` | Berkas pengujian unit/fixture baru untuk 10 endpoint Batch 2 (276 skenario + 2 shutdown) |
| `docs/audits/active-menu-audit-2026-10-10.json` | Sinkronisasi status 10 endpoint Batch 2 menjadi selesai, pembaruan SHA-256 dan line count controller |
| `docs/audits/audit-rollout-checkpoint.md` | Pembaruan catatan checkpoint rollout terkini |
| `docs/implementation-plan.md` | Pembaruan ringkasan progres Fase 2 (38/51 selesai) |
| `docs/task-progress.md` | Checkpoint utama diperbarui dengan detail hasil eksekusi Batch 2 |

## Batasan dan Pekerjaan Berikutnya

- **Status Fase 2 saat ini: 38/51 endpoint selesai** (Pembayaran Material 18/18, Request Mutasi 20/21).
- **Sisa Pending di Fase 2: 13 endpoint baseline**:
  - `Request_mutasi` Batch 3: 1 endpoint (`getMataUangByKode`).
  - `Pengajuan_rutin`: 12 endpoint baseline.
- **Pekerjaan Berikutnya:**
  - Eksekusi `Request_mutasi` Batch 3 (1 endpoint) untuk menuntaskan modul `Request_mutasi` menjadi 21/21 endpoint.
  - Atau melanjutkan ke modul `Pengajuan_rutin` (12 endpoint baseline).
