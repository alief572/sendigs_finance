# Fase 1 — Request Payment batch 3

Selesai: 2026-10-11 00:45 WIB, branch `main`. Cakupan: 10 endpoint batch 3. Fase 1 kini **30/55**; Request Payment **30/46**; tersisa **25 endpoint** fase 1. Checkpoint utama: `docs/task-progress.md`.

## Grounding

AGENTS, rencana, checkpoint utama, controller, model Request Payment, All_model, Jurnal_nomor/Jurnal_model, generator jurnal private, dan view terkait diperiksa. Hash controller sebelum perubahan `d7e30021f61c19cf121bbf18bd3b83fa0d73a9bc55e0d1ea71c0dae9f1950b98` cocok checkpoint batch 2. Tidak ada recorder di constructor; permission/menu yang sudah ada tetap dipertahankan. Tidak melakukan pemeriksaan menu/DB live ulang.

## Checklist endpoint

| Endpoint | Metadata yang diizinkan | Cabang verifikasi |
| --- | --- | --- |
| `view_jurnal`, `edit_jurnal` | journal_id, row_count | isi/kosong, ID invalid, query awal/akhir, model/render/exit |
| `jurnal_save` | journal_id, item_count, amount | struktur array/nilai/tanggal invalid, source journal hilang, nomor gagal, update 0 affected tetap valid, setiap update/insert/query/begin/status/commit gagal, writer/exit |
| `list_return`, `list_return_approval` | row_count | isi/kosong, query/render gagal, shutdown |
| `reject_approval` | no_dokumen, approval_level, tipe, item_count | enam tipe × checker/management, tipe mismatch, sumber hilang, item periodic invalid, update/delete/query/begin/status/commit gagal |
| `approve_pengembalian_expense` | return_id, no_dokumen, journal_count, journal_outcome | sumber/detail hilang, lebih kasbon/lebih expense/seimbang/nol/existing, query/update/insert/begin/status/commit gagal, writer/exit |
| `reject_pengembalian_expense` | return_id, no_dokumen | ID invalid/sumber hilang, query/update/begin/status/commit gagal, writer |
| `search_payment_list` | date_from, date_to, bank_filter_present, row_count | model asli, isi/kosong, dua query masing-masing gagal, rendering, writer/exit |
| `search_req_payment` | date_from, date_to, vendor_filter_present, tab, row_count | tujuh tab model asli, hasil kosong/terfilter, kegagalan setiap query, model terhenti, writer |

Semua scope mengizinkan reason/outcome generik melalui helper. Tidak menyimpan SQL mentah, POST utuh, alasan bebas, nama/rekening, isi file, nama supplier/bank bebas, atau keterangan jurnal.

## Keputusan dan perubahan perilaku

- Enam endpoint read dimulai sebelum operasi bisnis dan selesai setelah rendering/response. Kegagalan query awal diingat sebelum query berikutnya. Daftar/jurnal kosong tetap sukses; row_count pencarian request menghitung hasil yang lolos filter.
- Model `search_payment_list` mendapat argumen referensi keempat yang opsional untuk statistik audit. Ini menjaga bukti error pada dua query internal tanpa mengubah rendering/return maupun caller tiga argumen.
- Empat write memeriksa begin, hasil tiap query/write, status transaksi, dan commit. Audit sukses mengikuti commit; writer gagal/exception tidak mengubah respons bisnis.
- `jurnal_save` memvalidasi bentuk array dan sumber jurnal sebelum update/accounting insert. Nomor yang gagal dihasilkan tidak dipakai. `trans_complete` yang sebelumnya diikuti commit kedua diganti satu keputusan commit eksplisit agar hasil commit bisa diverifikasi. Update dengan affected_rows 0 tetap boleh sukses bila query tidak gagal dan sumber ada.
- Penolakan memakai tipe sumber yang diverifikasi dan menolak mismatch/tingkat/item invalid; alasan penolakan tetap masuk data bisnis, tidak masuk audit. Penolakan bisnis yang berhasil dicatat sebagai audit sukses dengan outcome `rejected`.
- Generator jurnal pengembalian mengembalikan ok/count/outcome eksplisit. Expense/detail hilang atau query/insert gagal membatalkan approval; existing journal dilewati tanpa duplikasi dan zero totals mempertahankan skip. Rumus debit/kredit, mapping COA, staging tr_jurnal, penomoran, dan optional enrichment company mengikuti implementasi sebelumnya.
- Format respons tetap: jurnal `save` boolean, penolakan `save` angka, pengembalian `status` angka. Input/data hilang yang sebelumnya dapat menghasilkan sukses atau error sekarang menghasilkan respons gagal generik. Read nomor jurnal menggunakan parameter binding.
- Tes batas rollout batch 2 dipindahkan dari batch 3 ke endpoint batch 4 yang masih pending. Semua skenario batch 2 tetap dijalankan; tidak mengubah implementasi batch 1/2.

## Hasil verifikasi

- RED awal: `view_jurnal` belum menghasilkan event. RED tambahan menangkap response integer penolakan dan sumber jurnal hilang.
- GREEN: **282 skenario batch 3** PASS. Controller/recorder asli; pencarian payment serta tujuh tab pencarian request menggunakan model asli. Persistence/rendering terisolasi fixture; empat subprocess membuktikan shutdown nyata.
- Regresi batch 1 **94**, batch 2 **242**, PR Stok **24 endpoint**, serta tujuh suite fondasi PASS: total **11 suite**, exit 0.
- PHP lint controller, model, Batch2_test, Batch3_test PASS; `git diff --check` PASS.
- Perbandingan per metode dengan snapshot sebelum batch: hanya 10 endpoint batch 3 dan private `_generate_jurnal_pengembalian_expense` berubah, private `batch3AuditFailure` ditambahkan. Tidak ada metode dihapus; controller memiliki 30 scope endpoint.
- Hash controller setelah batch: `3f1e5cab12f489729f3b19a896f65a98d03c672bb08f83e6ac9c257af2217ed4`.
- Hash model setelah batch: `e64ee09fee8526c1c25107a1392b0cb1d6b990033deccb75ee4e3e7dd1261bf7`.

## Batas verifikasi dan pekerjaan berikutnya

Tidak menjalankan browser, DB live, migration, integration MariaDB, atau full PHPUnit repo. Kelulusan fixture bukan bukti deployment/DB accounting live. Risiko concurrency penomoran/double approval, SQL/filter dan HTML legacy, mapping COA, serta write yang terputus tetap memerlukan evaluasi terpisah; tidak mengubah schema/permission maupun refactoring modul lain.

Berikutnya **Fase 1 batch 4** (10 endpoint), lalu batch 5 (6), Approval Request Payment (4), Record Request Payment (5). Fase 2 belum dimulai. Manifest memperbarui status/hash/nomor baris Request Payment saja; perbedaan inventaris modul lain tetap tercatat pada checkpoint utama.
