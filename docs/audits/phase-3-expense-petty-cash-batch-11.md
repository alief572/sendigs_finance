# Fase 3 — Expense Petty Cash batch 11

Tanggal: 9 November 2026. Status: **selesai dan terverifikasi pada cakupan batch 11**.
`expense_petty_cash` **10/25 endpoint**. Fase 3 **109/130** (sebelumnya tertulis 108/130 dan sisa 22; dikoreksi 2026-11-09). Sisa 21: `expense_petty_cash` 15 (batch 12: 10, batch 13: 5) dan `petty_cash_vuca_sustain` 6 (batch 14). Rencana: `C:\Users\Febry\.claude\plans\tolong-buatkan-plan-untuk-logical-moore.md`.

## Endpoint
| Endpoint | Metadata aman | Hasil |
|---|---|---|
| `index`, `pelaporan` | `outcome` | `rendered` / `denied` |
| `get_data_pencatatan`, `get_data_pelaporan` | `row_count`, `outcome` | `rendered` / `denied` |
| `create($petty_cash_id)` | `petty_cash_id`, `reason`, `outcome` | `rendered` / `redirected` (0; `petty_cash_required`, `budget_not_found`) / `denied` |
| `edit($id)` | `id`, `current_status`, `reason`, `outcome` | `rendered` / `not_found` (0) / `redirected` (0; `status_not_editable`) / `denied` |
| `view($id)`, `view_pelaporan($id)` | `id`, `outcome` | `rendered` / `not_found` (0) / `denied` |
| `confirm_pelaporan($id)` | `id`, `current_status`, `reason`, `outcome` | `rendered` / `not_found` (0) / `redirected` (0; `not_draft`) / `denied` |
| `create_pelaporan` (tulis) | `pelaporan_id`, `item_count`, `reason`, `outcome` | `created` (1) / `create_failed` (0) / `validation_failed` (0; `ids_required`, `ids_invalid`, `records_not_found`, `not_draft`, `already_reported`, `company_mismatch`, `week_mismatch`) / `denied` (0) |

## Keputusan
- Helper `beginAudit`/`completeAudit`/`auditSafeData`/`auditRestrict` ditambahkan di controller (modul `expense_petty_cash`), pola sama dengan `petty_cash_master`. Metadata tidak memuat data bebas pengguna; `id`, `petty_cash_id`, `pelaporan_id` hanya lolos jika cocok pola aman.
- **`completeAudit` ditulis sebelum `redirect()`**, karena `redirect()` CodeIgniter memanggil `exit` dan tidak ada kode sesudahnya yang berjalan. Redirect karena data tidak ada atau status tidak sesuai dicatat status 0; `redirected` dipakai untuk penolakan berbasis status.
- Penolakan izin lewat `auth->restrict()` dicatat `denied` sebelum `restrict` menghentikan request (`auditRestrict`). `create_pelaporan` memeriksa izin sendiri dan mencatat `denied` pada cabangnya.
- **Perbaikan model (satu-satunya perubahan perilaku):** `Expense_petty_cash_pelaporan_model::create_pelaporan` hanya memeriksa `trans_status()` setelah `trans_complete()`. Di CodeIgniter 3, kegagalan commit hanya terlihat dari nilai balik `trans_complete()`, jadi sebelumnya kegagalan commit bisa dianggap sukses dan mengembalikan id pelaporan. Kini nilai balik itu diperiksa juga; hasil audit `created` mengikuti hasil ini.
- Model lain di `expense_petty_cash` yang memakai pola `trans_start/trans_complete` yang sama (`submit_pelaporan`, approve/reject, `save`) **belum diubah**; dibahas di batch masing-masing.
- Allowlist recorder membuang key yang tidak didaftarkan di `begin`; `current_status` ditambahkan eksplisit di `edit` dan `confirm_pelaporan` setelah tes menemukannya hilang.

## Pengujian
- `application/modules/expense_petty_cash/tests/Batch1_audit_test.php` baru: **PASS 64 skenario + 1 subprocess shutdown** (semua cabang 10 endpoint, `denied`, redirect, tujuh alasan validasi `create_pelaporan`, kegagalan model, kegagalan penulis audit tidak mengubah respons, tidak ada kebocoran password, model dengan commit gagal/status gagal). RED terkonfirmasi dua kali: controller HEAD (0 event) dan model HEAD (commit gagal dianggap sukses).
- Suite `audit_trail` (Auth, Controller, Legacy, Logger, Menu, Query, Recorder), `petty_cash_master` Batch1 (41) dan Expense Batch 10 (17) PASS. `php -l` dan `git diff --check` bersih.
- Gagal sebelum batch (diverifikasi di batch 8): `audit_trail/Integration_test`, `expense/Query_state_test`, `expense/Visibility_test`. Tidak disentuh.
- Menu/DB live dan browser tidak diperiksa. View aktual tidak dirender; template dimock.

## File diubah
`application/modules/expense_petty_cash/controllers/Expense_petty_cash.php`, `models/Expense_petty_cash_pelaporan_model.php`, `tests/Batch1_audit_test.php` (baru), `docs/audits/active-menu-audit-2026-10-10.json`, laporan ini. SHA tercatat di manifest.
