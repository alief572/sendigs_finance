# Implementation Plan

## Publikasi dua modul Expense — 2026-10-15

- Otorisasi: push jurnal_expense dan report_jurnal_expense ke main serta development; isi dua modul wajib identik. Tidak menyertakan ringkas kolom Record atau perubahan lokal modul lain.
- Fase 1 selesai: fetch main09857821/development39167ea6; dependency Jurnal Payment/Report Payment/recorder/logger/helper/constants identik. Regresi260 assertions/checks (Jurnal167 + Report93), audit recorder/logger/legacy PASS; 17 PHP lint dan JS PASS.
- Fase 2 selesai: index terpisah per branch dengan parent remote masing-masing, stage dua modul dan dokumen task saja, preserve HEAD/index/checksum pekerjaan existing. Tidak merge seluruh branch dan tidak force push.
- Fase 3 selesai: push atomik main c89b84fbd965903b9ab56220dd3809dd527cd50e dan development 402be62bb69beb5022c52fe8e4e89f98c0842f1a, ls-remote sesuai. Tree dua modul identik (3fc211d5f1a248da8b2fd12d03320bcd77af9ea8, b54d9cfad0eab1c81082edf2fac106012ee3e0b7), seluruh blob lokal cocok, dependencies/di luar scope tidak berubah. Tidak ada TODO push tersisa. Bukti docs/expense-modules-publication-2026-10-15.md. Migrasi menu hanya disertakan sebagai SQL; tidak menjalankan database remote atau posting accounting.


## Report Jurnal Expense — 2026-10-14

- Fase 1: model posted Expense Report/Expense Report Consultant, grouping transaksi/jenis/company; daftar, pencarian, filter/dropdown dan detail berdasarkan ID representative.
- Fase 2: controller lima endpoint (index/get_data_jurnal/view_jurnal/export_excel/export_jurnal), seluruhnya Report_Jurnal_Expense.View, audit sekali per request; tiga view mengikuti Report Jurnal Payment, detail cents read-only, export daftar.
- Fase 3: migrasi menu idempotent tepat setelah Report Jurnal Payment dan grant View ekuivalen, verifikasi lokal, regresi/lint/JS/whitespace serta manifest/laporan.
- Default disepakati: seluruh histori sts=1 termasuk jalur lama; tidak rekonsiliasi ledger, ubah posting/mapping, perubahan schema jurnal, browser test, atau Git push. Task ringkas kolom Record diselesaikan terpisah; lihat checkpoint ringkas kolom di atas.
- Status: Fase 1–3 selesai; 27 query, 42 controller, 12 view, 12 client checks PASS. Regresi Jurnal Expense167 + recorder/logger/legacy PASS; 8 PHP lint. Menu482/order16 setelah report payment477/order15, View1850, grant ekuivalen lengkap; dua run migration idempotent. MySQL read-only report kosong (source88 sts0 +8 NULL), tanpa posting live. File: modul report_jurnal_expense baru + manifest/laporan/README; plan/checkpoint diperbarui, source existing tidak diubah. Tidak ada fase implementasi aktif; tidak push. Batas: status source, bukan rekonsiliasi ledger/snapshot. Next: tidak ada TODO report; migration environment lain saat pemasangan. Bukti: docs/report-jurnal-expense-2026-10-14.md.

## Modul Jurnal Expense — 2026-10-14

- Fase 1: model daftar/filter/ekspor hanya Expense Report (generator modul Expense) dan Expense Report Consultant dengan sts 0, kosong, atau NULL; grouping per no_transaksi + jenis + company.
- Fase 2: tampilan identik Jurnal Payment, preview dan posting JV ke javh/jurnal/nomorJC dengan mapping company existing (4 Vuca; 1/6/7 STM; lainnya Sustain), validasi server, deduplikasi, audit.
- Fase 3: registrasi menu tepat setelah Jurnal Payment; permission Jurnal_Expense.*, akses awal mengikuti grant ekuivalen Jurnal_Payment.*. Uji/lint/dokumentasi dan verifikasi registrasi lokal.
- Kriteria: posted/jenis lain tidak bisa dilihat atau diposting via ID; NULL ikut; source nominal tidak dari payload; satu dokumen satu aksi; gagal tulis/commit bukan sukses. Tidak melakukan posting accounting live, redesign, mengubah jurnal_payment, migrasi engine, atau publish Git tanpa permintaan task baru.
- Temuan: tujuan javh/jurnal/pastibisa_tb_cabang lokal MyISAM. Gunakan cleanup khusus nomor yang dibuat request saat gagal; advisory lock + source row lock; catat keterbatasan crash/cross-module concurrency.
- Penutupan: Fase 1–3 selesai; 167 assertions baru, regresi audit/Expense, lint/sintaks/whitespace, registrasi menu idempotent, dan query MySQL read-only PASS. Rincian: docs/jurnal-expense-2026-10-14.md. Tidak ada posting accounting live. Fase 4 selesai sesuai otorisasi user: commit 1f4338959efe405b068e7a788c1d397be7d72466 dipush hanya development memakai index terpisah; SHA remote/dependency diverifikasi, main dan pekerjaan lokal lain dipertahankan.
