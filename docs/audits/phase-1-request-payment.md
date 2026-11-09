# Fase 1 â€” Request Payment

## Grounding batch 1 (10 endpoint)

SELECT menu lokal 2026-10-10: request_payment/index, payment_list, list_return,
list_return_approval, approval_request_payment dan record_request_payment/index aktif
dengan parent aktif. Menu checker/management lama punya parent nonaktif; controller
tetap mengikuti baseline endpoint, bukan pemindaian seluruh folder.

Controller, Request_payment_model (query/filter/export), All_model (return insert_id
dan affected_rows), dan view rp_export_excel dibaca sebelum perubahan. Model JSON
mengeluarkan respons lalu kembali; view export tidak exit. Tidak ada hook legacy
pada 10 endpoint ini. Recorder dimulai per endpoint, bukan constructor.

| Endpoint | Proses / metadata | Cabang yang wajib diuji |
|---|---|---|
| index | Halaman / jumlah entitas | data, kosong, permission ditolak, render terhenti |
| get_data_request | Tabel / filter tanggal, entitas, kategori, pagination, search_present | data, kosong, error model |
| get_summary_request | Ringkasan / filter sama | data, kosong, error model |
| export_excel_request | Penyiapan Excel / filter dan row_count | data, kosong, render terhenti |
| reject_history | Riwayat reject / no_dokumen, row_count | isi, kosong, parameter kosong |
| submit_for_approval | Batch / ID dokumen terbatas 100, jumlah, entitas, no_pengajuan, nilai | kosong, invalid, terkunci, dokumen hilang, beda entitas, begin/generate/insert/batch/status/commit gagal, sukses |
| payment_list | Halaman Payment List | render sukses/terhenti |
| get_data_payment_list | Tabel pembayaran / tanggal, tipe, status, pagination, search_present | isi, kosong, error model |
| save_request | Simpan request lama / jumlah dokumen, processed_count, upload_failed_count | kosong, enam tipe, upload opsional/ditolak, begin/insert/status/commit gagal |
| list_approve | Halaman approval / row_count | isi, kosong, query/render gagal |

Metadata tidak mencakup nama, rekening, alasan bebas, isi file, request penuh,
atau literal pencarian. Status operasi tulis ditetapkan setelah commit berhasil.
Upload gagal yang sebelumnya opsional tetap tidak mengubah aturan bisnis; audit
mencatat hasil parsial. Hasil daftar kosong tetap sukses. Respons existing dijaga.

## Checkpoint

Batch 1 dan 2 selesai (20/46 endpoint request_payment; 20/55 endpoint Fase 1).
Batch 3 sampai 5 serta approval_request_payment dan record_request_payment masih
pending (total 35 endpoint). Fase 1 belum selesai. Pengujian memakai fixture
terisolasi tanpa koneksi database bisnis. Detail batch 2 ada pada
[laporan batch 2](phase-1-request-payment-batch-2.md).

## Hasil verifikasi batch 1

- RED: fixture controller asli gagal karena index belum menghasilkan satu event.
- GREEN: 90 skenario fixture/subprocess PASS, termasuk delapan endpoint baca,
  semua validasi submit, begin/generate/insert/insert_batch/trans_status/commit gagal,
  kegagalan update sumber pada enam tipe request, upload opsional dan gagal,
  penulis audit false/exception, permission ditolak/shutdown, serta endpoint pending
  yang tidak mengaktifkan recorder. Batch 101 dokumen dengan ID Unicode panjang
  tetap tercatat: 100 ID dibatasi ukuran byte, jumlah total tetap utuh.
- Success audit diperiksa sudah terjadi setelah pemanggilan commit; commit gagal
  menghasilkan respons gagal, rollback dan satu log gagal. Return insert gagal
  juga diuji saat fixture trans_status tetap sehat.
- PHP lint controller/test PASS; git diff --check PASS.
- Recorder, Logger, Legacy, Auth, Controller, Query, Menu dan regresi PR Stok PASS.
- Tidak ada migrasi, penulisan DB bisnis, perubahan JavaScript/UI, atau browser test.

Jalankan test terfokus: `C:/xampp74/php/php.exe application/modules/request_payment/tests/Audit_test.php`.

## File dan alasan perubahan

- Request_payment.php: begin/complete eksplisit pada 10 endpoint; metadata allowlist;
  hasil operasi dari query/transaksi, bukan interpretasi respons. Perbaikan minimal
  pemeriksaan begin/insert/commit agar respons dan audit tidak melaporkan sukses palsu.
- Audit_test.php: controller dan recorder asli, persistence/model/upload/render fixture;
  subprocess untuk permission/exit nyata. Semua DB dan unggah file bersifat mock.
- Manifest, checkpoint, laporan audit awal dan laporan fase ini: status endpoint,
  SHA controller, checks, file dan batch berikutnya tersimpan untuk kelanjutan.

## Risiko / pekerjaan berikutnya

Fase 1 belum selesai: request_payment batch 3–5 (26 endpoint), approval_request_payment
(4), record_request_payment (5). Tidak ada recorder fallback yang diaktifkan pada
35 endpoint pending. Batch berikutnya harus membaca kode/model terbaru dan menulis
checklist sebelum aktivasi. Legacy save_request tetap mengizinkan pilihan kosong;
log sukses menyatakan empty_selection/processed_count=0, bukan klaim dokumen tersimpan.
Upload opsional yang gagal tetap mempertahankan respons bisnis tetapi status audit
0 dengan outcome partial_upload_failure. Log baca tabel/ringkasan adalah aktivitas
backend terpisah sehingga satu kunjungan UI dapat menghasilkan beberapa aktivitas.
Kegagalan koneksi audit atau penghentian proses secara paksa tetap dapat kehilangan log.

## Verifikasi ulang main

2026-10-10T23:36:46+07:00: batch 1 dipulihkan setelah reset/checkout. Resolusi pembuat konsultan milik main dipertahankan. Empat skenario tambahan menguji Direct Payment/Cash, creator kosong dan fallback username: total 94 skenario PASS. Batch 2 belum diimplementasikan. Lihat `audit-recheck-main-2026-10-10.md`.

## Checkpoint batch 2 terbaru

2026-10-11T00:12:19+07:00: batch 2 selesai; 20/55 endpoint Fase 1. PASS 242 skenario batch 2, 94 skenario batch 1, semua 10 suite, lint dan diff check. Lihat [laporan batch 2](phase-1-request-payment-batch-2.md) untuk checklist, keputusan, batasan dan hasil tes. Batch berikutnya request_payment batch 3. Catatan batch 1 dan pemulihan di atas bersifat historis.
