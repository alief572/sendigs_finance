# Fase 1 — Request Payment batch 2

## Grounding dan checklist sebelum implementasi

Branch main; SHA256 controller sebelum perubahan:
`f828a454a4f2fb906a473229856f62ce4f778a1ea227a0101e34fd9d459d1cd9`,
cocok dengan manifest checkpoint pemulihan. Controller, model Request Payment,
All_model dan helper recorder dibaca. Endpoint ini belum memakai hook legacy.
Permission existing tetap dipertahankan; tidak ada aktivasi recorder di constructor.

| Endpoint | Metadata yang diizinkan | Cabang verifikasi |
|---|---|---|
| list_approve_checker | row_count | isi/kosong, error query/model, render terhenti |
| list_approve_management | row_count | isi/kosong, error query/model, render terhenti |
| approval_payment | tipe, request_id, row_count | enam tipe, parameter invalid, request/header hilang, query/render gagal |
| approval_payment_checker | tipe, request_id, row_count | enam tipe, parameter invalid, request/header hilang, query/render gagal |
| save_approval_cons | no_dokumen, payment_id, item_count | kasbon/expense fallback, sumber/user hilang, ID gagal, begin/insert/update/status/commit gagal |
| save_approval | no_dokumen, tipe, payment_id, item_count, amount | tujuh tipe, item invalid/kosong, header/detail hilang, duplicate existing, generate/begin/write/status/commit gagal |
| save_approval_checker | no_dokumen, tipe, item_count, processed_count | periodik/transportasi/expense/kasbon/lainnya, input invalid/kosong, request hilang, begin/update/status/commit gagal |
| list_payment | row_count | isi/kosong, query/model/render gagal |
| save_payment | document_ids, document_count, processed_count, upload_failed_count, journal_count | lima tipe jurnal + tipe legacy lain, nilai nol/negatif, pilihan kosong, input invalid, payment hilang, upload opsional/gagal, begin/update/insert/status/commit gagal |
| payment_jurnal_list | row_count | isi/kosong, query/model/render gagal |

Hasil tulis harus diperiksa setelah commit; return penulis audit tidak menentukan
respons bisnis. Metadata tidak menyalin alasan, nama, rekening, isi file, atau POST
utuh. Error SQL mentah diganti respons gagal generik. Upload opsional yang gagal
tetap mengikuti respons bisnis existing dengan audit partial_upload_failure.
Pilihan kosong pada checker/payment tetap mengikuti respons lama dengan jumlah
diproses nol. Fixture tidak menggunakan database bisnis atau browser.

## Checkpoint selesai

2026-10-11T00:12:19+07:00: batch 2 selesai di main. Request Payment mencakup 20/46 endpoint;
Fase 1 mencakup 20/55 endpoint. Tersisa 26 endpoint Request Payment, 4 endpoint
approval_request_payment, dan 5 endpoint record_request_payment (total 35).

Pemeriksaan menu lokal read-only masih menunjukkan request_payment/index,
payment_list, list_return, list_return_approval serta dua controller Fase 1 lain
aktif dengan parent aktif. list_approve_checker/list_approve_management memiliki
parent 276 nonaktif; payment_jurnal_list berstatus nonaktif. Tidak ada menu atau
permission yang diubah. Schema accounting db_sendigs_gl_ss belum tersedia lokal.

## Keputusan implementasi

- Scope dimulai hanya pada sepuluh endpoint batch 2. Empat daftar dan dua detail
  menyimpan hasil baca; daftar/detail kosong tetap sukses bila sumber header ada.
- Hasil error tiap query diingat sebelum query berikutnya, sehingga query sukses
  yang datang belakangan tidak menghapus bukti kegagalan operasi sebelumnya.
- Empat endpoint tulis memeriksa begin, return setiap write, trans_status dan
  commit. Commit gagal menghasilkan rollback dan respons gagal dengan format
  existing (status/msg, save, atau hasil). Return penulis audit tidak mempengaruhi
  transaksi atau respons bisnis. Tidak ada perubahan pada model shared.
- Header/detail/user yang dibutuhkan diperiksa sebelum dereference. ID payment
  atau detail yang gagal dihasilkan tidak diteruskan ke insert. Insert gagal
  tidak lagi menampilkan SQL/error database mentah.
- Approval dengan payment existing ditolak sebelum detail baru dimasukkan, agar
  tidak membuat detail yang menunjuk ID payment baru tanpa header.
- Metadata berisi referensi, tipe, jumlah item/nilai dan outcome; SQL explicit
  null mengikuti batch 1. Tidak ada nama, rekening, alasan bebas, isi upload atau
  POST utuh. document_ids pada save_payment adalah ID payment terpilih; daftar
  dibatasi 100 ID, jumlah total dan processed_count tetap utuh.
- Pilihan kosong checker/payment tetap sukses dengan empty_selection dan
  processed_count=0. Nilai payment nol dilewati dan tidak menjalankan insert batch
  jurnal kosong. Nilai negatif tetap mengikuti jalur existing.
- Upload opsional gagal tetap mempertahankan hasil bisnis existing; audit status
  gagal dengan partial_upload_failure dan upload_failed_count. Tidak mengunggah
  file bukan kegagalan.
- Item petty_cash_hutang memakai no_doc tanpa mewajibkan ID item yang tidak
  digunakan. ID sumber payment diwajibkan hanya untuk periodik, sesuai pemakaian.

## Verifikasi

- RED awal: list_approve_checker belum menghasilkan event. RED tambahan
  menangkap error query awal yang tertutup query berikutnya. GREEN: 242 skenario
  batch 2 PASS, termasuk tujuh tipe approval, kasbon PR, expense kurang bayar dan
  kredit kasbon, lima cabang jurnal beserta tipe legacy lain, sumber hilang,
  input invalid, duplicate, generate/begin/write/trans_status/commit failure,
  writer false/exception, upload dan metadata 101 dokumen.
- Subprocess menguji exit nyata pada keenam endpoint baca serta penghentian
  sebelum commit approval. Satu log interrupted, tanpa data privat. Endpoint
  view_jurnal batch 3 tetap tidak mengaktifkan recorder.
- Semua 10 suite PASS: tujuh Audit Trail, batch 1 (94 skenario), batch 2
  (242 skenario), dan PR Stok (24 endpoint). Lint tiga file PHP yang berubah PASS.
- Mutation test membalik kondisi commit payment; tes sukses mendeteksi perubahan.
  File kemudian dikembalikan dan suite final PASS. Pemeriksaan token PHP membuktikan
  hanya 10 endpoint batch 2 dan satu helper privat yang berubah terhadap baseline.
- git diff --check PASS. Tidak ada migration, penulisan DB bisnis, browser test,
  atau pengujian ulang MariaDB disposable. PHPUnit seluruh repo tetap belum
  dijalankan karena test existing membutuhkan PHP 8, runtime fixture PHP 7.4.

Jalankan: `C:/xampp74/php/php.exe application/modules/request_payment/tests/Batch2_test.php`.

## File berubah dan risiko

Controller menambah scope/outcome serta pemeriksaan hasil transaksi minimum.
Audit_test.php mengizinkan fixture dipakai batch 2 dan mengganti pemeriksaan
endpoint pending yang kini aktif dengan pemeriksaan constructor opt-in.
Batch2_test.php menyediakan fixture dan regresi khusus batch 2. Laporan fase,
laporan batch ini, manifest dan checkpoint mencatat cakupan serta urutan berikutnya.

Pengujian fixture tidak membuktikan koneksi live accounting atau seluruh aturan
bisnis. Query SQL interpolasi, validasi pemetaan COA, penomoran payment berbasis
MAX dan risiko request approval/payment bersamaan merupakan perilaku legacy;
batch ini bukan perombakan keamanan atau konkurensi bisnis. File upload yang
sudah tersimpan dapat tetap ada bila transaksi kemudian rollback, sebagaimana
existing. Koneksi audit gagal/penghentian paksa tetap dapat menyebabkan log hilang.
Perubahan belum di-commit; file lain dalam workspace tetap dipertahankan.

Batch berikutnya: Fase 1 / request_payment / batch 3 (10 endpoint): view_jurnal,
edit_jurnal, jurnal_save, list_return, list_return_approval, reject_approval,
approve_pengembalian_expense, reject_pengembalian_expense, search_payment_list,
search_req_payment. Fase 1 belum selesai dan Fase 2 belum dimulai.
