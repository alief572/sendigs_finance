# Normalisasi nomor Payment pending

## Kontrak

Alur ini hanya mengubah `tr_jurnal.no_transaksi` pada jenis tepat `Payment` pending menjadi voucher `payment_approve.id_payment` yang mempunyai record `tr_payment_paid`. Pending dengan accounting existing tetap boleh dinormalisasi; status, nominal, COA, tanggal, company, keterangan dan accounting tidak diubah. Ini berbeda dari alur patch lama yang tetap menolak accounting existing.

Satu cohort meliputi seluruh kelompok yang menuju voucher sama, termasuk anggota yang sudah canonical. Semua kelompok harus pending (status0/kosong), tanpa tanggal/user/metadata posting atau percobaan posting tidak pasti. Setiap kelompok asal balance, company dan tanggal semua anggota sama. Benturan dengan kelompok protected, mapping ambigu, atau relasi tidak tersedia melewatkan seluruh cohort. Baris nol tetap termasuk anggota.

## Alias dan penjagaan posting

Migrasi `008_payment_reference_aliases.sql` membuat tabel InnoDB baru tanpa mengubah schema/data posted. Alias per baris menyimpan batch, ID jurnal, key lama lengkap (termasuk CSV), voucher, approval sumber, active dan waktu pencatatan. Nomor dan alias commit bersamaan dalam database sumber.

Resolver memverifikasi semua anggota memiliki tepat satu alias aktif sesuai voucher serta approval terkini dan tr_payment_paid. Coverage parsial, alias duplikat/bertentangan atau relasi berubah menolak posting. Batch lookup memuat alias per chunk, tidak query per baris. Guard tetap memeriksa metadata/accounting memakai key lama, token lama dan voucher. Tidak membuka posting ulang dengan mengganti nomor.

## CLI

```text
php index.php jurnal_all/payment_reference_cli/normalize dry-run C:/secure/payment-normalization.json
php index.php jurnal_all/payment_reference_cli/normalize apply C:/secure/payment-normalization.json
php index.php jurnal_all/payment_reference_cli/normalize verify C:/secure/payment-normalization.json
php index.php jurnal_all/payment_reference_cli/normalize rollback C:/secure/payment-normalization.json
```

Manifest wajib di luar document root; CLI menolak HTTP dan symlink. Alur lama `payment_reference_cli/index` tetap memakai aturan ketat lama, jangan gunakan manifest normalisasi pada alur tersebut.

Artefak yang disimpan bersama:
- Manifest JSON tetap: cohort, ID eksplisit, mapping, fingerprint sebelum/sesudah, alias yang diharapkan, environment dan batch.
- `.backup.json`: snapshot lengkap seluruh Payment, metadata posting Payment dan histori alias sebelum operasi, dengan checksum pada manifest.
- `.journal`: delta append-only per cohort (status/error/snapshot accounting saat apply), bukan salinan seluruh batch berulang. Baca melalui `Payment_normalization::loadManifest()`; jangan memakai loader patch lama untuk file ini.

Pemeriksaan accounting hanya SELECT. Keberadaannya dicatat sebagai bukti, bukan penolak normalisasi; pemeriksaan yang unavailable tidak memberi izin posting. Jika bukti tidak dapat diverifikasi, rollback otomatis tidak tersedia. Apply memuat ulang anggota/status/metadata/mapping dan mengunci baris serta identitas kelompok. Jika satu kandidat berubah setelah dry-run, proses berhenti dengan checkpoint cohort yang sudah commit. Rerun memverifikasi commit_unknown dan menjadi no-op jika source/alias sesuai hasil yang diharapkan.

Rollback mengembalikan key per kelompok asal dan menonaktifkan alias milik batch, tanpa menghapus histori. Metadata/registry posting baru, perubahan field sumber, perubahan bukti accounting atau target yang berbenturan menolak rollback. Setelah batch sudah dirollback, apply batch yang sama tidak menerapkannya kembali; buat dry-run baru bila normalisasi kembali diotorisasi.

## Operasi development yang diotorisasi

Gunakan konfigurasi development, terapkan migrasi008, dan tahan advisory lock `payment_reference:write` selama backup/review/apply/verify. Jalur pembayaran/posting yang sudah diupgrade menunggu atau menolak sebelum menulis saat maintenance. Lock ini bukan proteksi untuk SQL manual/kode deployment lama; jangan campur versi atau menulis manual saat operasi.

Alat CLI-only `scratch/run_payment_normalization.php` membatasi schema ke suffix `_dev`, menahan lock yang sama, membatasi koneksi accounting ke READ ONLY, menyimpan artefak di luar webroot, dan memeriksa checksum semua non-Payment serta seluruh metadata posting sebelum/sesudah apply. Tidak memanggil handler posting, tidak mengubah engine, dan tidak memperbaiki status/routing accounting.

Verifikasi library memastikan semua Payment identik dengan backup kecuali perubahan nomor yang tercantum pada cohort applied; posted dan metadata tidak berubah, alias lengkap serta seluruh nomor hasil terhubung ke voucher valid. Snapshot accounting tiap cohort dibandingkan terhadap bukti sebelum apply. Kelompok skipped beserta alasannya wajib dilaporkan; jangan melemahkan guard demi mengubah seluruh baris.

Hasil penerapan aktual dan artefaknya dicatat dalam `docs/task-progress.md` setelah review manifest dan penutupan verifikasi.

Penerapan development sudah selesai:1518 nomor berubah melalui enam batch,5 sebelumnya canonical,445pending ditahan, posted54 tetap identik. Batch7 menunjukkan fixed point tanpa eligible. Bukti dan daftar pengecualian tersedia melalui `docs/payment-pending-normalization-result-2026-10-28.md`.

Untuk batch dependent, rollback dari batch terbaru ke terlama. No-op batch terdahulu memakai alias untuk membedakan slot asal yang sekarang telah menjadi voucher canonical lain; rollback tetap menolak menimpa slot tersebut. Verifikasi agregat lintas batch membandingkan hasil terhadap backup awal dan gabungan seluruh perubahan nomor yang diotorisasi.
