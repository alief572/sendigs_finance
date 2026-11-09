# Fase 2 — Pembayaran Material batch 2

13 Oktober 2026. Status: **selesai dan terverifikasi**. Batch 1 selesai 10 endpoint; batch 2 selesai delapan endpoint berikut. Modul `pembayaran_material` selesai **18/18**, sehingga Fase 2 mencapai **18/51** endpoint baseline.

| Endpoint | Proses/metadata | Cabang |
|---|---|---|
| check_payment | Pilih/batal pilih; payment_id,checked | insert/delete/begin/status/commit, invalid ID |
| clear_choosed_payment | Reset pilihan | delete/begin/status/commit |
| proses_payment | Baca pilihan dan indikasi admin; document_count,has_admin_fee | kosong, admin payment, fallback admin request, tiap query |
| save_payment | Pembayaran+upload+jurnal+status petty cash; payment_ids,count,date,amount,voucher,upload_failed_count,journal_count | validasi/sumber hilang, bank lookup, ID generator, upload opsional/parsial, petty/nonpetty/main/refill jurnal, query/write/begin/commit/status/pcvs gagal |
| used_choosed_payment | Hapus pilihan setelah digunakan | delete/begin/status/commit |
| get_list_req_payment | Tabel request; pagination,search_present,jenis_payment,row_count | kosong, PHP/RPC/resolusi company lintas koneksi, tiap query |
| set_jurnal | Penyiapan preview jurnal; payment_ids,count,row_count | tipe kasbon/transport/expense/direct/refill/hutang/cash, sumber kosong, query lintas koneksi |
| set_jurnal_refill | Penyiapan preview refill; payment_ids,count,row_count | transport/title TIT009, bank/COA/company, kosong/query gagal |

Preview jurnal adalah pembacaan/penyiapan hasil, bukan posting. Perhitungan, company resolution, admin-charge-bearer, penandaan Paid petty cash existing dipertahankan. Input jurnal/keterangan/file/rekening/session penuh tidak disalin ke metadata. Recorder hanya pada endpoint aktif; nested model/HMVC bagian scope luar.

## Implementasi dan keputusan penting

- Scope recorder dimulai secara eksplisit pada delapan endpoint batch 2. Satu aktivitas menghasilkan satu event audit; constructor tidak mengaktifkan recorder.
- Pemilihan/reset memeriksa hasil begin, insert/delete, status transaksi, dan commit. Respons existing dipertahankan: check/used tetap tidak mengeluarkan JSON; clear tetap memakai status angka.
- `save_payment` memvalidasi sumber pembayaran sebelum proses, memeriksa write/query secara eksplisit, serta menyelesaikan audit sukses setelah commit. Kegagalan query/write tetap gagal meskipun fixture status transaksi terlihat sehat.
- Jalur jurnal utama, hutang petty cash, refill, company resolution dan penanggung admin tetap mengikuti perhitungan existing. Pemanggilan `update_status_done` tetap bagian transaksi pembayaran; kegagalannya membatalkan pembayaran. Layanan tersebut diperiksa dan tidak membuka transaksi sendiri.
- Upload opsional tanpa berkas tidak dianggap gagal. Upload yang dicoba tetapi gagal mempertahankan respons bisnis lama, sementara audit status 0 menyatakan `partial_upload_failure` dan jumlah kegagalan. Nama/isi berkas tidak dicatat.
- Preview dan tabel model mendapat argumen statistik opsional. Error query pertama dipertahankan sebelum query berikutnya menghapus error driver, termasuk koneksi accounting/consultant/hris. Header sumber preview yang hilang dicatat gagal; tabel kosong tetap sukses.
- Metadata hanya ID dokumen, jumlah, tanggal, nominal, voucher, kategori navigasi dan hasil. Referensi dibatasi 100 item; pencarian hanya `search_present`, sorting disimpan sebagai indeks/arah yang diizinkan. SQL audit eksplisit null mengikuti pola Fase 1.
- Pesan exception/driver tidak disalin ke audit. Failure JSON pembayaran memakai pesan generik. Debug aplikasi yang menyalin payload jurnal request lengkap di jalur simpan ini dihapus agar payload tidak bocor ke error log.
- Identitas/helper/logger/permission/schema existing tidak diubah. Perubahan transaksi dibatasi pada hasil yang diperlukan untuk audit yang benar; risiko bisnis legacy lain tidak direfactor.

## Verifikasi dan checkpoint

- RED: fixture awal clear_choosed_payment gagal karena tidak menghasilkan event audit; GREEN setelah instrumentasi.
- **Batch 2: 551 skenario fixture PASS**, memakai controller, recorder, model pembayaran, generator jurnal dan helper terkait yang asli; persistensi/render/upload memakai fixture. Dua subprocess nyata memverifikasi shutdown saat preview/query dan commit terhenti, masing-masing satu event interrupted.
- **Batch 1: 246 skenario fixture PASS** dan satu subprocess interrupted tetap lulus setelah perubahan batch 2.
- **15 suite regresi existing PASS**: tujuh Audit Trail, lima batch Request Payment, Approval/Record Request Payment, dan PR Stok 24 endpoint.
- PHP lint empat file controller/model/test PASS. `git diff --check` PASS. Tidak ada JavaScript berubah, sehingga pemeriksaan sintaks JS tidak diperlukan.
- Daftar 18 endpoint publik cocok dengan manifest dan terdapat 18 pemanggilan beginAudit eksplisit. SHA controller serta nomor baris/status endpoint diperbarui. Hash controller Request Mutasi/Pengajuan Rutin tetap sama seperti sebelum sesi dan seluruh endpointnya pending.
- Tidak menguji browser, menjalankan migration, membuat commit/push, atau menulis database bisnis lokal/production. Integrasi MariaDB disposable tidak dijalankan ulang.

## File yang dibuat/diubah

- `C:/web_dev/sendigs_finance/application/modules/pembayaran_material/controllers/Pembayaran_material.php`: pencatatan delapan endpoint tambahan dan pemeriksaan hasil transaksi/query/upload.
- `C:/web_dev/sendigs_finance/application/modules/pembayaran_material/models/Pembayaran_material_model.php`: statistik pembacaan opsional, pelacakan error lintas koneksi dan sumber preview kosong; format bisnis tetap.
- `C:/web_dev/sendigs_finance/application/modules/pembayaran_material/tests/Batch1_test.php`: fixture bersama diperluas dan regresi batch pertama dipertahankan.
- `C:/web_dev/sendigs_finance/application/modules/pembayaran_material/tests/Batch2_test.php`: suite baru untuk delapan endpoint, cabang bisnis, failure injection dan shutdown.
- Dokumentasi: laporan ini, manifest active-menu-audit-2026-10-10.json, implementation-plan.md, task-progress.md, serta audit-rollout-checkpoint.md. Laporan batch 1 dibuat pada batch sebelumnya dalam sesi yang sama.
- Artefak `scratch/phase2_*` merupakan alat/snapshot pemeriksaan lokal, bukan dependensi aplikasi atau file deployment.

## Batas sesi dan pekerjaan tersisa

User meminta berhenti setelah batch 2 `pembayaran_material`. **Tidak melanjutkan `request_mutasi` atau `pengajuan_rutin` dalam sesi ini.** Fase 2 belum selesai: sisa 21 endpoint Request Mutasi dan 12 Pengajuan Rutin, total **33 endpoint baseline**. Fase 0/1 tetap selesai; Fase 3–20 belum selesai rollout/verifikasi akhir.

Pemeriksaan menu live dicoba melalui `docker ps`, tetapi akses Docker API lokal menghasilkan permission denied. Percobaan akses lanjutan terinterupsi; tidak ada SELECT/menu atau mutasi database yang dilakukan. Jumlah cakupan tetap baseline, bukan inventaris live terbaru. Ini tidak menghalangi fixture, tetapi harus diperiksa pada kelanjutan yang memiliki akses database.

Risiko tersisa: interpolasi SQL/konkurensi generator ID existing, file yang terlanjur diunggah sebelum rollback, perbedaan keadaan fixture dengan DB live, serta kehilangan audit saat writer tidak tersedia atau proses dimatikan paksa. Koneksi audit terpisah dan dedup tetap mengikuti fondasi teruji. Working tree belum commit dan mengandung pekerjaan lain yang dipertahankan.
