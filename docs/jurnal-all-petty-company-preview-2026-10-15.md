# Preview Pencatatan Petty Cash per company — 15 Oktober 2026

## Klarifikasi dan hasil

Pengguna mengonfirmasi detail dipisah per company seperti preview sumber; satu PCP tetap satu row/action dan posting seluruh sisi. Preview PCS jenis Petty Cash kini mempunyai section Sisi [nama company], tabel detail, TOTAL COMPANY debit/kredit dan status balance masing-masing. Untuk fixture contoh VUCA/STM443750.00 per sisi, modal tidak menampilkan grand total gabungan887500.00 sebagai subtotal company. Header satu PCP/RPC dan seluruh baris COA tetap.

No. company dikelompokkan berdasarkan id_company, bukan hanya nama; baris interleaved tetap masuk sisi yang benar. Nama unavailable memakai Company [id] atau Company tidak tersedia. Subtotal memakai helper cents/fromCents existing agar tidak membulat lewat float. Company negatif/unbalanced diberi status yang sesuai; imbalance antarcompany tidak saling menutupi di label balance section. Ini perhitungan presentasi, tidak mengganti can_post/dispatcher/native validation.

Pembentuk section dipanggil controller setelah resolver COA/ref native pada pending; posted memakai baris histori existing. Perubahan ini hanya jenis Petty Cash di Pencatatan; Refill/Payment Hutang pada tab Pembayaran dan preview source lain tetap sebelumnya. Blocked COA Finance pada screenshot tetap; tidak ada bypass atau perubahan routing/company/nomor/counter/posting. Nominal daftar/ringkasan/ekspor tetap gabungan sesuai kontrak awal.

## File

- model Jurnal_all_model::company_sections untuk grouping dan subtotal presentasi.
- controller preview menyiapkan sections setelah native resolver.
- views/preview.php memakai renderer tabel existing per section untuk pencatatan; nonPCS/nonrecording tetap satu tabel.
- assets/css/jurnal-all.css menambahkan spacing/judul section, mengikuti palet aktif dan breakpoint/table scroll existing.
- Query_test.php dan Controller_test.php, README serta plan/checkpoint/manifest/laporan ini.

## Validasi

RED sebelum implementasi: method company_sections belum tersedia. Final: query108/controller84/dispatcher41/audit8/native posting168/PCS89/revisi24/client41 PASS (563 assertions dijalankan pada batch). Cakupan baru: dua sisi443750, header/escaping/posted/read-only/refill unchanged, integrasi native resolver, single company, nama sama ID berbeda, rows interleaved, zero-only, precision90071992547409.25 dan imbalance antarcompany.

PHP lint5 PASS. CSS source braces dan whitespace/manifest/hash diperiksa. Query SQL tidak berubah pada batch ini; hasil fixture MariaDB27 dari batch RPC sebelumnya dipakai kembali (total bukti fitur590 termasuk hasil reuse, bukan590 tes baru dijalankan). Tidak mengakses database remote, menjalankan posting nyata, atau browser test sesuai instruksi pengguna.

## Antislop delta gate

- R-05/R-14/R-20/R-31 PASS: section mengikuti sisi company yang nyata; judul/subtotal dipakai untuk review distribusi jurnal, bukan dekorasi. Design Read sebelum implementasi ada di plan.
- R-25/R-03/R-32 PASS source: judul #334155 pada putih; status success/danger dan focus/table scroll existing tetap, tidak menambah fixed width atau kontrol baru. Tidak mengklaim inspeksi viewport/keyboard browser.
- R-17/R-23/R-38/C-5 PASS: nama/baris/nominal berasal dari source, fallback unavailable eksplisit, tidak ada data/asset baru buatan.
- R-26/R-27/R-35/C-2/C-4 PASS dalam cakupan source/harness pengguna: native posting/block/permission/loading regresi tetap, render pending/posted/refill dan escaping lulus; tanpa browser click-through sesuai instruksi pengguna.
- R-01/R-06/R-19/R-29/R-37 PASS: warna/font/motion existing dipertahankan, hanya spacing section dan heading; ENERGY2/RHYTHM2/MOTION1 tetap.

Tidak ada TODO kode diketahui. Data remote/runtime visual belum diperiksa. Risiko konsistensi lintas database/MyISAM existing tetap. Tidak ada commit/push/deploy.
