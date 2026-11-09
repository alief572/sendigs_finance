# Fase 3 — Expense batch 8

Tanggal: 9 November 2026. Status: **selesai dan terverifikasi pada cakupan batch 8**.
Expense **80/92 endpoint** (Batch 1–8). Fase 3 **80/130**. Sisa Expense 12: batch 9 (9) dan batch 10 (3). Lihat `docs/task-progress.md` dan rencana di `C:\Users\Febry\.claude\plans\tolong-buatkan-plan-untuk-logical-moore.md`.

## Endpoint (semua read, tanpa jalur tulis)
| Endpoint | Metadata aman | Hasil |
|---|---|---|
| `get_data_transport_req_all` | `page`, `outcome` | `rendered` |
| `get_coa_name` | `page`, `outcome` | `found` / `not_found` (status 1; ini pencarian, bukan kegagalan) |
| `kasbon_fin_manage` | `page`, `outcome` | `rendered` |
| `get_dat_list_kasbon`, `get_dat_kasbon_list`, `get_dat_app_kasbon`, `get_dat_app_kasbon_manage`, `get_dat_expense_data`, `get_dat_expense_pc` | `page`, `row_count`, `outcome` | `rendered` |
| `get_dat_approval_expense` | `page`, `outcome` | `no_output` |

## Keputusan
- Memakai helper yang ada (`beginAudit`/`completeAudit`) seperti batch 7; tidak ada helper baru.
- `get_dat_approval_expense` menjalankan query lalu berhenti tanpa mengeluarkan respons apa pun (tidak ada `echo`/`output`, tidak ada referensi view). Perilaku ini **dipertahankan** dan dicatat jujur sebagai `no_output`, bukan `rendered`. Perlu keputusan pemilik: endpoint ini kemungkinan belum selesai atau tidak dipakai.
- `get_coa_name`: satu-satunya perubahan perilaku. Saat COA tidak ditemukan, akses `->nama` pada null memicu notice PHP; kini `nm_coa` bernilai `null` tanpa notice. Respons JSON tetap berbentuk sama.
- `row_count` = jumlah baris halaman yang dikembalikan, bukan total; tidak ada data baris/PII di metadata.
- Endpoint DataTables tidak punya early return, jadi `completeAudit` di akhir method cukup; jika query gagal fatal di tengah, fallback shutdown mencatat `interrupted`.

## Pengujian
- `application/modules/expense/tests/Batch8_audit_test.php` baru: **PASS 35 skenario + 1 subprocess shutdown** (cabang data kosong dan berisi, writer failure/exception tidak mengganggu respons, `found`/`not_found`, tidak ada kebocoran password/nama, endpoint batch 9 tetap belum terinstrumentasi). RED terkonfirmasi dengan controller HEAD (0 event).
- Batch 1–7 PASS setelah daftar "pending" di masing-masing tes dipersempit ke endpoint batch 9 (jumlah skenario turun 3 per tes karena 3 pemanggilan pending dihapus; endpoint itu kini dicakup Batch 8). Tidak ada asersi yang dilemahkan selain itu.
- Suite audit_trail (Auth, Controller, Legacy, Logger, Menu, Query, Recorder), `Kasbon_attachments_test`, `Management_actions_test`, `Reject_test` PASS. `php -l` dan `git diff --check` bersih (hanya peringatan LF/CRLF git).
- **Gagal sejak sebelum batch ini (diverifikasi terhadap controller HEAD, tidak diubah):** `audit_trail/tests/Integration_test.php` (butuh flag `--isolated-local-db`), `expense/tests/Query_state_test.php`, `expense/tests/Visibility_test.php` (fixture usang terhadap `index()` dan `get_dat_pengembalian_kasbon` yang baru).
- Menu/DB live dan browser tidak diperiksa.

## SHA
`Expense.php`: `B744BAEDC3C9A879ED366E42D92E0323CDB9A58CD41FB7CB0C303DAFE294CE8E`, 7956 baris.

## File diubah
`application/modules/expense/controllers/Expense.php`, `tests/Batch8_audit_test.php` (baru), `tests/Batch1–7_audit_test.php` (pending list; Batch2 juga fixture dari Batch 0), `docs/audits/active-menu-audit-2026-10-10.json`, laporan ini.
