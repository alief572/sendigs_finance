# Fase 3 — Expense batch 10 (Expense selesai) + Petty Cash Master

Tanggal: 9 November 2026. Status: **selesai dan terverifikasi pada cakupan batch 10**.
- Expense **92/92 endpoint selesai** (modul Expense tuntas).
- `petty_cash_master` **7/7 endpoint selesai**.
- Fase 3: **98/130**. Sisa: `expense_petty_cash` 25 (batch 11–13) dan `petty_cash_vuca_sustain` 6 (batch 14). Rencana: `C:\Users\Febry\.claude\plans\tolong-buatkan-plan-untuk-logical-moore.md`.

## Expense
- `verifikasi_pengembalian_reject` diinstrumentasi (satu-satunya endpoint Expense yang tersisa). Hasil: `rejected` (1), `validation_failed` dengan reason `id_required` / `reason_required` (0), `not_found` (0), `transaction_failed` (0, rollback dipanggil). **Alasan penolakan tidak masuk metadata audit** (teks bebas dari pengguna).
- `save_pengembalian_dana` (instrumentasi di helper privat `saveReturnEvidenceWithFiles`) dan `verifikasi_pengembalian_approve` sudah punya recorder dari commit di luar rollout. Di batch ini hanya **cabang validasi** yang diverifikasi dengan tes (id kosong, expense tidak ada, bank belum dipilih, id tidak valid, checklist tidak lengkap, kegagalan penulis audit). **Jalur sukses dan kegagalan transaksi/upload keduanya belum diuji di sini** (butuh mock upload dan transaksi jurnal); dicatat sebagai keterbatasan.
- Daftar "pending" di tes Batch 1–9 kini kosong karena tidak ada lagi endpoint Expense tanpa recorder.

## Petty Cash Master (`Petty_cash_master`)
| Endpoint | Metadata aman | Hasil |
|---|---|---|
| `index`, `create` | `outcome` | `rendered` / `denied` |
| `edit`, `view` | `id`, `outcome` | `rendered` / `not_found` (0) / `denied` |
| `get_data` | `row_count`, `outcome` | `rendered` |
| `save` | `id`, `operation` (create/update), `detail_count`, `reason`, `outcome` | `saved` / `save_failed` (0) / `validation_failed` (0; reason `nama_required`, `keterangan_required`, `detail_required`, `nominal_invalid`) |
| `delete` | `id`, `outcome` | `deleted` / `delete_failed` (0) / `not_found` (0) / `denied` |

Keputusan:
- Helper `beginAudit`/`completeAudit`/`auditSafeData` ditambahkan di controller (pola yang sama dengan controller lain; modul `petty_cash_master`). Nama master dan keterangan tidak dicatat.
- **Penolakan akses** dicatat lewat `auditRestrict()`: jika `has_permission` gagal, event `denied` ditulis dulu, baru `auth->restrict()` mengalihkan/menghentikan request (restrict berakhir dengan `exit`, jadi tanpa ini request ditolak tidak terlacak).
- **Perbaikan model (satu-satunya perubahan perilaku):** `save_with_details` dan `delete_with_details` sebelumnya mengembalikan `status 1` tanpa memeriksa hasil `trans_commit()`. Kini kegagalan commit memicu rollback dan `status 0`, sesuai aturan "sukses tulis ditentukan setelah commit berhasil". Hasil audit `saved`/`deleted` mengikuti status ini.
- **Temuan di luar cakupan, tidak diubah:** `save()` tidak memanggil `auth->restrict()` sama sekali (hanya `index/create/edit/view/delete` yang memeriksa izin), dan `get_data()` juga tanpa pemeriksaan izin. Perilaku dipertahankan; endpoint tulis tanpa pemeriksaan izin sebaiknya ditinjau pemilik modul.

## Pengujian
- `application/modules/expense/tests/Batch10_audit_test.php` baru: **PASS 17 skenario + 1 subprocess shutdown**. RED terkonfirmasi terhadap HEAD (0 event untuk `verifikasi_pengembalian_reject`).
- `application/modules/petty_cash_master/tests/Batch1_audit_test.php` baru: **PASS 41 skenario + 1 subprocess shutdown** (semua cabang endpoint, `denied`, kegagalan penulis audit tidak mengubah respons, tidak ada kebocoran password/nama, model dengan commit gagal/berhasil). RED terkonfirmasi terhadap HEAD.
- Batch 1–9 Expense PASS; audit_trail (Auth, Controller, Legacy, Logger, Menu, Query, Recorder), `Kasbon_attachments`, `Management_actions`, `Reject` PASS. Skrip drift per fungsi: seluruh 92 endpoint Expense terinstrumentasi (lima method `save` memakai helper privat `withExpenseUploads`). `php -l` dan `git diff --check` bersih.
- Gagal sebelum batch (diverifikasi di batch 8): `audit_trail/Integration_test`, `expense/Query_state_test`, `expense/Visibility_test`. Tidak disentuh.
- Menu/DB live dan browser tidak diperiksa.

## SHA
`Expense.php`, `Petty_cash_master.php`, dan modelnya tercatat di manifest.
