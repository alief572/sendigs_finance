# Fase 3 Audit Trail — Penutupan (Expense dan petty cash)

Tanggal: 9 November 2026. Status: **selesai dan terverifikasi** (dengan 4 kegagalan baseline yang tidak disebabkan Fase 3, lihat di bawah).

## Hasil per modul (manifest `docs/audits/active-menu-audit-2026-10-10.json`)
| Modul | Endpoint | Batch | Laporan |
|---|---|---|---|
| `expense` | 92/92 | 1–10 | `phase-3-expense-batch-1.md` … `phase-3-expense-batch-10.md` |
| `petty_cash_master` | 7/7 | 10 | `phase-3-expense-batch-10.md` |
| `expense_petty_cash` | 25/25 | 11–13 | `phase-3-expense-petty-cash-batch-11.md`, `-12.md`, `-13.md` |
| `petty_cash_vuca_sustain` | 6/6 | 14 | `phase-3-petty-cash-vuca-sustain-batch-14.md` |
| **Total** | **130/130** | | |

Total 130, bukan 121 seperti baseline awal: ditemukan 9 endpoint pengembalian/verifikasi Expense yang belum ada di manifest (3 di antaranya sudah punya recorder dari commit di luar rollout). Koreksi angka: laporan batch 11 menulis "108/130, sisa 22"; yang benar 109/130, sisa 21 (sudah dikoreksi).

## Verifikasi
1. **Skrip drift terhadap kode** (bukan manifest): method publik controller sama dengan manifest untuk keempat modul (92/25/7/6) dan seluruh 130 endpoint memiliki `beginAudit`/`completeAudit`, langsung atau lewat helper privat. Lima endpoint `save` Expense memakai `withExpenseUploads` dan helper `save*WithFiles` yang masing-masing berisi `beginAudit` dan `completeAudit`.
2. **Suite dijalankan segar pada 9 November 2026 (PHP 7.4), semuanya PASS** kecuali empat di bawah:
   - Fase 0: `audit_trail` Auth, Controller, Legacy, Logger, Menu, Query, Recorder.
   - Fase 1: `request_payment` Audit (94) + Batch2–5 (242/282/815/374), `approval_request_payment` (60), `record_request_payment` (57).
   - Fase 2: `pembayaran_material` Batch1–2 (248/581), `request_mutasi` Batch1–3 (391/275/10), `pengajuan_rutin` Batch1–2 (72/47).
   - PR Stok (24 endpoint).
   - Fase 3: Expense Batch1–10 (31/49/54/46/52/45/43/35/48/17), `Kasbon_attachments`, `Management_actions`, `Reject`, `petty_cash_master` (41), `expense_petty_cash` Batch1–3 (64/73/48), `petty_cash_vuca_sustain` (35).
3. **Empat kegagalan baseline (bukan akibat Fase 3, semuanya diverifikasi terhadap kode HEAD sebelum perubahan terkait):**
   - `audit_trail/Integration_test`: butuh server MySQL lokal; koneksi ditolak walau `--isolated-local-db` diberikan (keterbatasan lingkungan).
   - `expense/Query_state_test` dan `expense/Visibility_test`: fixture usang terhadap `get_dat_pengembalian_kasbon` dan `index()` yang berubah karena pekerjaan non-audit.
   - `petty_cash_vuca_sustain/paid_status_test`: mock loader tidak punya `library()` sehingga fatal di `Pembayaran_material::beginAudit()` (instrumentasi Fase 2); identik di HEAD.
   Keempatnya perlu pembaruan fixture sebagai pekerjaan terpisah; tidak diubah agar tidak melemahkan atau menyamarkan tes.
4. `php -l` dan `git diff --check` bersih pada seluruh file yang diubah (hanya peringatan LF/CRLF git).

## Perubahan perilaku di luar pemasangan audit (semua: sukses tulis ditentukan setelah commit)
Di CodeIgniter 3, kegagalan commit hanya terlihat dari nilai balik `trans_complete()`/`trans_commit()`, bukan `trans_status()`. Diperbaiki dan diuji dengan commit gagal pada:
- `Petty_cash_master_model`: `save_with_details`, `delete_with_details`.
- `Expense_petty_cash_pelaporan_model`: `create_pelaporan`, `submit_pelaporan`, `approve_pelaporan`, `reject_pelaporan`.
- `Expense_petty_cash_model`: `save_pencatatan`, `delete_pencatatan` (file evidence hanya dihapus setelah commit sukses; hasil kini memuat `reason`).
- `Petty_cash_vuca_sustain_model`: `process_payment_hutang`.
Perubahan kecil lain: `Expense::get_coa_name` mengembalikan `nm_coa: null` tanpa notice saat COA tidak ada; `Expense_petty_cash_pelaporan_model::request_payment_ok` menandai kegagalan integrasi request_payment pada approve.

## Temuan di luar cakupan (tidak diubah, perlu tinjauan pemilik modul)
1. `Petty_cash_master::save()` dan `get_data()` tanpa `auth->restrict()`.
2. `Expense_petty_cash_pelaporan_model::approve_pelaporan`/`reject_pelaporan` tidak memeriksa `approver_id` walau pesan error menyiratkan demikian (hanya `view_approval` yang memeriksa).
3. `Expense_petty_cash::delete_evidence` tanpa pemeriksaan kepemilikan evidence dan tanpa cek hasil delete DB.
4. `Expense::get_dat_approval_expense` tidak mengeluarkan respons apa pun (dipertahankan, dicatat `no_output`).
5. `create_request_payment` selalu mengembalikan `true`; kegagalannya hanya terdeteksi bila melempar exception.

## Keterbatasan
- Menu/DB live (Docker/DB) dan browser tidak diperiksa; template, view, upload CI, dan model dimock di semua tes batch.
- Jalur sukses `save_pengembalian_dana` dan `verifikasi_pengembalian_approve` belum diuji (butuh mock upload dan transaksi jurnal).
- Model jurnal `expense_petty_cash` (`create_journal_*`, `retry_journal`, `delete_journal`) tidak diubah dan transaksi internalnya tidak diperiksa.
- Tidak ada commit, push, atau migration. `docs/audits/` dan `audit_trail/` masih untracked di git.

## Langkah berikutnya
Fase 4 (Penagihan dan invoice: `plan_tagih`, `actual_plan_tagih`, `invoicing`, `approval_invoice_so`) belum dikerjakan; manifest menunjuk modul pertama `plan_tagih`, batch 1.
