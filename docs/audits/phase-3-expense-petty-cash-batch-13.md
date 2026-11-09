# Fase 3 — Expense Petty Cash batch 13 (modul `expense_petty_cash` tuntas)

Tanggal: 9 November 2026. Status: **selesai dan terverifikasi pada cakupan batch 13**.
`expense_petty_cash` **25/25 endpoint**. Fase 3 **124/130**. Sisa: batch 14 `petty_cash_vuca_sustain` (6 endpoint).

## Endpoint
| Endpoint | Metadata aman | Hasil |
|---|---|---|
| `save` (tulis) | `id`, `operation`, `detail_count`, `error_count`, `journal`, `reason`, `outcome` | `saved` (1; `journal` = `synced`/`failed`) / `save_failed` (0; juga `reason` `number_exhausted`) / `validation_failed` (0; `validation_errors` + `error_count`) / `denied` (0) |
| `delete` (tulis) | `id`, `reason`, `outcome` | `deleted` (1) / `delete_rejected` (0; `not_found`, `status_not_deletable`, `already_reported`) / `delete_failed` (0; `transaction_failed`) / `validation_failed` (`id_invalid`) / `denied` |
| `retry_journal` (tulis) | `id`, `reason`, `outcome` | `retried` (1) / `retry_failed` (0) / `validation_failed` (`id_invalid`) / `denied` |
| `upload_evidence` (file) | `file_type`, `file_size`, `reason`, `outcome` | `uploaded` (1) / `upload_failed` (0) / `validation_failed` (0; `file_required`, `file_type_invalid`, `file_too_large`) / `denied` |
| `delete_evidence` (file+DB) | `id`, `source` (`id`/`name`), `file_removed`, `outcome` | `deleted` (1) / `not_found` (0) / `denied` |

## Keputusan
- **`save`: pencatatan tersimpan tetapi jurnal gagal** dicatat sebagai `saved` dengan `journal = failed` (status 1 karena data bisnis tersimpan dan respons JSON `status: true` + `warning`), bukan `saved` murni dan bukan kegagalan. Kegagalan jurnal tetap dapat dicari di audit dan di-retry lewat `retry_journal`.
- **Perbaikan model (perubahan perilaku):**
  - `save_pencatatan`: nilai balik `trans_complete()` diperiksa (di CI3 kegagalan commit tidak mengubah `trans_status()`).
  - `delete_pencatatan`: kegagalan `trans_commit()` kini memicu rollback dan `status 0`. **File evidence fisik hanya dihapus setelah commit sukses** (sebelumnya urutannya sama tetapi commit tidak diperiksa, sehingga file bisa terhapus walau commit gagal).
  - `delete_pencatatan` mengembalikan kunci tambahan `reason` (`not_found`, `status_not_deletable`, `already_reported`, `transaction_failed`) agar audit membedakan penolakan dari kegagalan sistem. Kunci ini ikut muncul di respons JSON (tambahan saja, kunci lama tidak berubah).
- Nama file asli dan isi detail pencatatan tidak masuk audit; hanya tipe dan ukuran file. `delete_evidence` mencatat apakah file fisik benar-benar terhapus (`file_removed`).
- **Batas yang disengaja:** isi model jurnal (`create_journal_for_pencatatan`, `create_journal_stm`, `create_journal_intercompany`, `retry_journal`, `delete_journal`) **tidak diubah**. Controller mencatat hasilnya dari nilai balik. Kebenaran transaksi internal jurnal (mis. apakah jurnal multi-koneksi atomik) tidak diperiksa di batch ini.
- **Temuan di luar cakupan, tidak diubah:** `delete_evidence` menghapus baris DB tanpa memeriksa hasil `delete()` dan menerima `encrypted_name` langsung dari POST tanpa memeriksa kepemilikan (hanya `basename` untuk path traversal); pengguna dengan izin Add/Manage dapat menghapus evidence milik pencatatan siapa pun bila mengetahui nama file/id.

## Pengujian
- `application/modules/expense_petty_cash/tests/Batch3_audit_test.php` baru: **PASS 48 skenario + 1 subprocess shutdown** (semua cabang 5 endpoint, `denied`, validasi, jurnal sukses/gagal, upload dengan `$_FILES` dan direktori sementara, hapus file fisik, kegagalan penulis audit tidak mengubah respons, tanpa kebocoran nama file/isi detail, model dengan commit/status gagal, file evidence tidak terhapus saat commit gagal). RED terkonfirmasi: controller+model HEAD (0 event) dan model HEAD saja (commit gagal dianggap sukses).
- `expense_petty_cash` Batch1 (64) dan Batch2 (73), `petty_cash_master` Batch1 (41), suite `audit_trail` (Auth, Controller, Legacy, Logger, Menu, Query, Recorder) PASS. `php -l` dan `git diff --check` bersih.
- Gagal sebelum rollout (diverifikasi di batch 8): `audit_trail/Integration_test`, `expense/Query_state_test`, `expense/Visibility_test`. Tidak disentuh.
- Menu/DB live, upload sungguhan (library `upload` CI dimock), dan browser tidak diperiksa.
