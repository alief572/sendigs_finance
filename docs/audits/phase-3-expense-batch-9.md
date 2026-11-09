# Fase 3 — Expense batch 9

Tanggal: 9 November 2026. Status: **selesai dan terverifikasi pada cakupan batch 9**.
Expense **89/92 endpoint** (Batch 1–9; 3 sisa untuk batch 10). Fase 3 **89/130**. Rencana: `C:\Users\Febry\.claude\plans\tolong-buatkan-plan-untuk-logical-moore.md`.

## Koreksi cakupan (penting)
Pemeriksaan kode menunjukkan tiga dari sembilan endpoint "baru" di manifest **sudah punya recorder dari commit di luar rollout** (`dc2681ba`):
- `verifikasi_pengembalian`: `beginAudit`/`completeAudit` di method; diverifikasi di tes batch 9.
- `save_pengembalian_dana`: method publik hanya memanggil `withExpenseUploads('saveReturnEvidenceWithFiles')`; instrumentasinya ada di helper privat itu (nama di-hardcode). Diverifikasi di batch 10.
- `verifikasi_pengembalian_approve`: instrumentasi di method; diverifikasi di batch 10.
Deteksi awal saya salah karena memakai pencarian huruf kecil `audit` (tidak cocok `beginAudit`) dan tidak mengikuti delegasi ke helper privat. Dampak: **hanya `verifikasi_pengembalian_reject` yang belum terinstrumentasi** di Expense, jadi batch 10 untuk Expense = 1 endpoint baru + 2 verifikasi tes.

## Endpoint batch 9
| Endpoint | Jenis | Metadata aman | Hasil |
|---|---|---|---|
| `get_dat_approval_expense_manage`, `get_expense_app_finance`, `get_dat_pengembalian_kasbon`, `get_dat_lebih_expense`, `get_dat_verifikasi_pengembalian` | DataTables read | `page`, `row_count`, `outcome` | `rendered` |
| `form_pengembalian`, `verifikasi_pengembalian_detail` | halaman/form by id | `id`, `outcome` (+`is_readonly` untuk form) | `rendered` (1), `not_found` (0) |
| `verifikasi_pengembalian` | halaman (sudah ada) | `page`, `outcome` | `rendered` |
| `set_jurnal_expense` | **preview jurnal, read-only** | `no_doc`, `row_count`, `outcome` | `stored_view`, `empty`, `generated` (semua status 1) |

## Keputusan
- `set_jurnal_expense` ternyata hanya menghasilkan HTML preview (tidak ada insert/update, tidak membuka transaksi). Jadi tidak ada jalur tulis jurnal, rollback, atau nesting dengan `Jurnal_expense`; risiko yang diantisipasi di rencana tidak terjadi. Tiga jalur keluar dicatat terpisah, termasuk `view` tanpa jurnal tersimpan yang jatuh ke `generated`.
- `not_found` pada form/detail dicatat status 0, konsisten dengan batch 7 (`add_kasbon_pr`). Ini pencarian dokumen yang tidak ada, bukan kegagalan sistem.
- `row_count` adalah jumlah baris halaman. Tidak ada nominal, nama, atau isi form di metadata. `no_doc` dan `id` hanya lolos jika cocok pola aman di `auditSafeData`.
- Tidak ada perubahan perilaku respons; hanya `beginAudit` dan `completeAudit` ditambahkan.

## Pengujian
- `Batch9_audit_test.php` baru: **PASS 48 skenario + 1 subprocess shutdown** (data kosong/berisi, kegagalan penulis audit tidak mengganggu respons, `not_found`, `readonly=1`, lima varian `set_jurnal_expense`, tidak ada kebocoran password/nama, `reject` tetap belum terinstrumentasi). RED terkonfirmasi dengan controller HEAD (0 event).
- Batch 1–8 PASS setelah daftar pending dipersempit ke `verifikasi_pengembalian_reject` (jumlah skenario berkurang karena pemanggilan pending yang sudah tidak relevan dihapus; endpointnya kini dicakup tes yang sebenarnya).
- audit_trail (Auth, Controller, Legacy, Logger, Menu, Query, Recorder), `Kasbon_attachments`, `Management_actions`, `Reject` PASS. `php -l` dan `git diff --check` bersih.
- Gagal sebelum batch (diverifikasi di batch 8): `audit_trail/Integration_test`, `expense/Query_state_test`, `expense/Visibility_test`. Tidak disentuh.
- Menu/DB live dan browser tidak diperiksa.

## SHA
`Expense.php` SHA-256 dan jumlah baris tercatat di manifest (`controller_sha256_at_checkpoint`).
