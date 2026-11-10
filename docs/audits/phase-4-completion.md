# Fase 4 Audit Trail — Penutupan (Penagihan dan invoice)

Tanggal: 9 November 2026. Status: **selesai dan terverifikasi** pada cakupan fixture CLI (tanpa DB live dan browser). Empat modul, **60/60 endpoint**, satu sesi.

## Hasil per modul (manifest `docs/audits/active-menu-audit-2026-10-10.json`)
| Modul | Endpoint | Batch | Tes baru | Skenario |
|---|---|---|---|---|
| `plan_tagih` | 8/8 | 1 | `plan_tagih/tests/Batch1_audit_test.php` | 41 |
| `actual_plan_tagih` | 11/11 | 1 (10) + 2 (1) | `actual_plan_tagih/tests/Batch1_audit_test.php` | 66 |
| `approval_invoice_so` | 3/3 | 1 | `approval_invoice_so/tests/Batch1_audit_test.php` | 21 |
| `invoicing` | 38/38 | 1–4 (10/10/10/8) | `invoicing/tests/Batch1_audit_test.php` | 155 |
| **Total** | **60/60** | | | **283** |

Total 60, bukan 58 seperti baseline: `invoicing` punya dua endpoint baru sejak 10 Oktober (`coa_settings`, `save_coa_mapping`, dari commit `913c336d`). Nomor baris dan SHA `invoicing` di manifest sudah usang dan di-rebaseline. Urutan eksekusi: `plan_tagih` → `actual_plan_tagih` → `approval_invoice_so` → `invoicing` (urutan manifest lama menaruh `approval_invoice_so` terakhir).

## Pola dan keputusan
- Pola Fase 2/3 dipakai ulang: `beginAudit` / `completeAudit` / `auditSafeData` / `auditRestrict` per controller. `auditRestrict` mencatat `denied` sebelum `restrict()` mengalihkan.
- `invoicing` memakai tiga helper tambahan: `auditPage` (halaman/cetak/unduh), `auditMutation` (hasil dari service/writer: status 1 = committed) dan `auditFinish` (menutup scope bila `legacy_consulting_boundary` menelan request tanpa menjalankan endpoint, sehingga hasilnya `rejected`, bukan `interrupted`).
- Alur save/update konsultasi (`Invoice_consulting_service::mutate`) dan Vuca (`Invoice_writer::save`) sudah punya recorder sendiri di HEAD untuk modul `invoicing`. Controller kini membuka scope lebih dulu, jadi `mutate()` mendapat `begin()` = false (scope ditolak karena sudah aktif) dan tidak menulis event ganda. Hasil tetap satu event per request.
- `write_vuca()` (recorder sudah ada di HEAD) tidak ditulis ulang: hanya dipindah supaya `denied` tercatat sebelum `restrict()`, dan allowlist ditambah `reason`.
- Metadata hanya scalar: `id`, `id_invoicing`, `id_penawaran`, `id_spk_penawaran`, `invoice_type`, `row_count`, `filter_status`, `processed_count`, `failed_count`, `reason`, `outcome`. ID yang bentuknya tidak wajar dibuang. Isi form, nama customer, alamat, dan alasan closing tidak dicatat (tes menegaskan tidak ada `PRIVATE` yang bocor).
- Unduhan dan cetak: `complete` dicatat **sebelum** view, karena view langsung mengirim isi file.
- `batch_process_tagih`: sebagian berhasil dihitung **sukses** (data sudah commit per record), semua gagal = gagal; `outcome` `processed` / `partial_failure` / `all_failed` / `no_records` / `denied`.
- Jalur lama `print_r` + `exit` dipertahankan apa adanya; hasil dicatat sebelum keluar.

## Perubahan perilaku di luar pemasangan audit (sukses tulis ditentukan setelah commit)
Di CodeIgniter 3, kegagalan commit hanya terlihat dari nilai balik `trans_commit()`. Dulu diabaikan; kini diperiksa dan diuji dengan commit gagal:
- `Plan_tagih`: `save_plan_tagih`, `revisi_plan_tagih`, `updateee`. `updateee` juga tidak lagi memanggil `update_batch` dengan array kosong (di CI3 itu bernilai FALSE).
- `Actual_plan_tagih`: `save_actual_plan_tagih` (hasil `update` sebelumnya diabaikan), `save_actual_plan_tagih_macet`, `batch_process_tagih` (commit gagal kini masuk `failed_records`, bukan `total_success`).
- `Approval_invoice_so`: `approve_invoice`, `reject_invoice`.
- `Invoicing`: `legacy_update_invoice`, `save_keterangan_print`, `_non_kons`, `_kwitansi`, `_vuca`, `_kwitansi_vuca`, `legacy_save_invoice_non_konsultasi`, `legacy_update_invoice_non_konsultasi`, `save_close_invoice`, `close_penawaran_non_kons`.
- Dua jalur try/catch `save_keterangan_print*` kini juga melempar bila `update` mengembalikan FALSE (dulu tetap membalas sukses).
- `legacy_save_invoice_non_konsultasi`: jalur "melebihi outstanding" kini `trans_rollback()` (transaksi sebelumnya dibiarkan terbuka; tidak ada data yang ditulis di jalur itu).

Perubahan lokal `Invoicing.php` yang sudah ada (cek lunas `saldo_piutang` pada `save_close_invoice`) dipertahankan dan dibangun di atasnya.

## Verifikasi
1. **Skrip drift terhadap kode** (bukan manifest): method publik `Plan_tagih` 8, `Actual_plan_tagih` 11, `Approval_invoice_so` 3, `Invoicing` 38 = manifest; seluruh 60 memiliki `beginAudit` (dua di antaranya, `save_invoice_vuca`/`update_invoice_vuca`, lewat `write_vuca()`) dan `completeAudit` atau helper setara.
2. **RED terkonfirmasi** untuk keempat modul: tes yang sama dijalankan terhadap controller sebelum instrumentasi dan gagal dengan 0 event; controller dipulihkan dan hash dicocokkan.
3. **Suite segar 9 November 2026 (PHP 7.4), semuanya PASS** selain baseline di bawah: Fase 4 (283 skenario), Fase 0 `audit_trail` (7), PR Stok, Fase 1 (`request_payment` Audit + Batch2–5, `approval_request_payment`, `record_request_payment`), Fase 2 (`pembayaran_material` B1–2, `request_mutasi` B1–3, `pengajuan_rutin` B1–2), Fase 3 (Expense B1–10, `expense_petty_cash` B1–3, `petty_cash_master`, `petty_cash_vuca_sustain`). Tes `invoicing` yang menyentuh library/UI: `Print_invoice`, `Vuca_accounts`, `Vuca_render`, `Ui_render`, `calculation`, `Consulting_calculation` (PHP) dan `Action_menu`, `Consulting_calculation`, `Save_feedback_client`, `Ui_client`, `Vuca_client`, `calculation` (Node).
4. **Baseline gagal, bukan akibat Fase 4**: empat baseline Fase 3 (`audit_trail/Integration_test`, `expense/Query_state_test`, `expense/Visibility_test`, `petty_cash_vuca_sustain/paid_status_test`) tetap gagal dengan sebab yang sama. Dua tambahan di `invoicing`: `Ui_list_client_test.js` (mengharapkan `filter_status: uninvoiced` di tab Consulting; membaca `views/index.php` yang tidak berubah dari HEAD) dan `Wapu_repair_test.php` (butuh `WAPU_TEST_DB_HOST`; pekerjaan WAPU di luar Fase 4). Tidak ada yang diperbaiki atau dilemahkan.
5. `php -l` seluruh file PHP yang diubah dan `git diff --check` bersih.

## Temuan di luar cakupan (tidak diubah, perlu tinjauan pemilik modul)
1. **Banyak endpoint tanpa `auth->restrict()` di controller** (hanya diketahui lewat pemindaian statis; belum dicek apakah `Admin_Controller` menambahkan pemeriksaan umum):
   - `Plan_tagih`: `add_plan_tagih`, `view_plan_tagih`, `edit_plan_tagih`, `save_plan_tagih`, `revisi_plan_tagih`, `updateee`, `get_data_spk`.
   - `Actual_plan_tagih`: semuanya kecuali `index` dan `batch_process_tagih` (yang memakai `has_permission`).
   - `Approval_invoice_so`: `index`, `approve_invoice`, `reject_invoice`.
   - `Invoicing`: `save_keterangan_print*` (5), `get_data_spk`, `download_excel`, `get_data_quotation_non_konsultasi`, `view_invoicing_non_kons`, `change_jurnal_invoice`, `save_coa_mapping` (izin hanya di service).
   Audit mencatat `denied` hanya pada endpoint yang memang memanggil `restrict()`.
2. `Plan_tagih::updateee` dapat dipanggil siapa pun yang bisa mengaksesnya dan menimpa status semua detail plan tagih.
3. `Invoicing::view_invoicing_non_kons` membaca `$get_invoicing->tanggal_invoice` sebelum `empty()`; pada ID yang tidak ada terjadi notice PHP lalu redirect.
4. `approve_invoice`/`reject_invoice` tidak membedakan ID yang tidak ada: `UPDATE` tanpa baris tetap membalas `status: 1`. `affected_rows` dicatat di audit supaya terlihat.
5. `Actual_plan_tagih::batch_process_tagih` memakai persentase PPN dan PPh yang hardcoded (11/12, 12%, 0,5%), berbeda dari rumus di `Invoicing`.

## Keterbatasan
- Menu/DB live (Docker/DB) dan browser tidak diperiksa; template, view, upload CI, dan model dimock.
- `Invoice_consulting_service::mutate` dan `Invoice_writer::save` dijalankan sebagai kelas asli di atas fixture DB; transaksi internalnya tidak diuji ulang di sini (suite `Consulting_integration_test` dan `integration_test` butuh MariaDB). Jalur sukses `save_invoice`, `save_coa_mapping`, dan `write_vuca` tidak diuji end-to-end; yang diuji penolakan, pencatatan hasil status, dan keutuhan respons.
- `Wapu_repair_test.php` tidak dijalankan (butuh DB).
- Tidak ada commit, push, atau migration. Seluruh perubahan Fase 4 belum di-commit; `plan_tagih/tests/`, `actual_plan_tagih/tests/`, `approval_invoice_so/tests/`, dan `invoicing/tests/Batch1_audit_test.php` masih untracked.

## Langkah berikutnya
Fase 5 (Penerimaan dan riwayat: `penerimaan_uang`, `penerimaan_pph_23`, `unlocated_penerimaan`, `historical_alokasi`); manifest menunjuk `penerimaan_uang` batch 1. Mulai hanya jika diminta. Hash controller Fase 4 ada di manifest (`phase_4_completion_verification.sha256`).
