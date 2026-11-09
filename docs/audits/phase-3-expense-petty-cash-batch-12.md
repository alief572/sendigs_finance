# Fase 3 — Expense Petty Cash batch 12

Tanggal: 9 November 2026. Status: **selesai dan terverifikasi pada cakupan batch 12**.
`expense_petty_cash` **20/25 endpoint**. Fase 3 **119/130** (hitung ulang dari manifest; angka "sisa 22 / 108" di laporan batch 11 salah, yang benar sisa 21 / 109 saat itu). Sisa: batch 13 (5) dan batch 14 `petty_cash_vuca_sustain` (6).

## Endpoint
| Endpoint | Metadata aman | Hasil |
|---|---|---|
| `submit_pelaporan` (tulis) | `id`, `current_status`, `reason`, `outcome` | `submitted` (1) / `submit_failed` (0) / `not_found` (0) / `validation_failed` (0; `id_invalid`, `not_draft`) / `denied` (0) |
| `approval` | `outcome` | `rendered` / `denied` |
| `get_data_approval` | `row_count`, `outcome` | `rendered` / `denied` |
| `view_approval` | `id`, `reason`, `outcome` | `rendered` / `not_found` (0) / `forbidden` (0; `not_assigned_approver`) / `denied` |
| `approve` (tulis) | `id`, `integration`, `reason`, `outcome` | `approved` (1; `integration` = `request_payment_ok`/`request_payment_failed`) / `approve_failed` (0) / `validation_failed` (0; `id_invalid`) / `denied` (0) |
| `reject` (tulis) | `id`, `reason`, `outcome` | `rejected` (1) / `reject_failed` (0) / `validation_failed` (0; `id_invalid`, `alasan_required`, `alasan_too_short`, `alasan_too_long`) / `denied` (0) |
| `print_pelaporan` | `id`, `outcome` | `rendered` / `not_found` (0) / `denied` |
| `get_budget_info` | `petty_cash_id`, `reason`, `outcome` | `rendered` / `not_found` (0) / `validation_failed` (0; `petty_cash_required`) / `denied` |
| `get_coa_list` | `petty_cash_id`, `row_count`, `reason`, `outcome` | `rendered` / `empty` (1; tanpa master) / `denied` |
| `get_petty_cash_list` | `row_count`, `outcome` | `rendered` / `denied` |

## Keputusan
- Pola batch 11 dipakai ulang: `completeAudit` sebelum `redirect()`, `auditRestrict` untuk `denied`, key metadata didaftarkan di `begin`. **Alasan reject tidak masuk audit** (teks bebas pengguna; hanya kategori panjang).
- **Perbaikan model (perubahan perilaku):** `submit_pelaporan`, `approve_pelaporan`, `reject_pelaporan` hanya memeriksa `trans_status()` setelah `trans_complete()`. Kini nilai balik `trans_complete()` juga diperiksa; kegagalan commit mengembalikan `false`.
- **Integrasi request_payment pada approve:** model menangkap `Exception` dari `create_request_payment` lalu hanya me-log; approve tetap `true`. Agar audit jujur, model sekarang menyimpan `request_payment_ok` (false bila exception) dan controller mencatatnya di `integration`. Keterbatasan: `create_request_payment` sendiri selalu mengembalikan `true` dan hanya gagal lewat exception, sehingga kegagalan tanpa exception tidak terdeteksi.
- **Temuan di luar cakupan, tidak diubah:** pesan error `approve`/`reject` menyebut "Anda adalah approver yang ditunjuk", tetapi `approve_pelaporan` dan `reject_pelaporan` **tidak memeriksa `approver_id`** (hanya `view_approval` yang memeriksa). Pengguna dengan izin Approve dapat meng-approve atau me-reject pelaporan yang ditujukan ke approver lain bila mengetahui id-nya. Perlu ditinjau pemilik modul.

## Pengujian
- `application/modules/expense_petty_cash/tests/Batch2_audit_test.php` baru: **PASS 73 skenario + 1 subprocess shutdown** (semua cabang 10 endpoint, `denied`, redirect, validasi, kegagalan penulis audit tidak mengubah respons, tanpa kebocoran alasan reject, model dengan commit/status gagal, integrasi request_payment gagal). RED terkonfirmasi: controller+model HEAD (0 event) dan model HEAD saja (commit gagal dianggap sukses).
- `expense_petty_cash` Batch1 (64), `petty_cash_master` Batch1 (41), suite `audit_trail` (Auth, Controller, Legacy, Logger, Menu, Query, Recorder) PASS. `php -l` dan `git diff --check` bersih.
- Gagal sebelum rollout (diverifikasi di batch 8): `audit_trail/Integration_test`, `expense/Query_state_test`, `expense/Visibility_test`. Tidak disentuh.
- Menu/DB live dan browser tidak diperiksa; template dan view dimock.
