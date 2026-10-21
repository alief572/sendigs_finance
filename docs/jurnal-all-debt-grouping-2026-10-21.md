# Grouping dan posting Payment Hutang Petty Cash

Status 2026-10-21: seluruh fase implementasi selesai. Tidak ada posting pada database aplikasi, koreksi jurnal historis, commit, push, deployment atau pengujian browser.

## Hasil dan bukti lokal

| Referensi | Voucher | Nominal bisnis debit/kredit | Company | Detail |
|---|---|---:|---|---|
| PHP-2026-0012 | BK--0926-0015 | Rp2.647.250 sekali | STM / SUSTAIN | Dua tabel, empat baris asli |
| PHP-2026-0003 | BK--1026-0001 | Rp318.750 sekali | STM / SUSTAIN | Dua tabel, empat baris asli |

Pembacaan model melalui scratch/verify-debt-grouping-20261021.php memakai transaksi READ ONLY dan rollback. Kedua kasus menghasilkan satu kelompok, dua tabel, empat baris Excel, source snapshot identik, registry siap, nol relasi jurnaltras, nol accounting terkait pada tujuan STM/SUSTAIN, dan tidak ada COA sisi yang hilang. Bukti ini merupakan preflight baca; bukan hasil posting aplikasi.

Migrasi006 diterapkan hanya untuk membuat registry percobaan baru di database development lokal. Verifikasi sebelum/sesudah: registry kosong (0 attempts), delapan baris hutang identik, counter ketiga database accounting identik. Tidak ada perubahan pada native key/status/nominal jurnal. BK--1026-0002 tetap mengikuti laporan rekonsiliasi sebelumnya dan tidak dikoreksi.

## Keputusan implementasi

- Resolver menyatukan native key melalui approval Paid bertipe petty_cash_hutang, voucher dan owner PHP terverifikasi; kesamaan teks referensi tidak cukup. ID company1 legacy diperbolehkan hanya bersama label canonical dan sumber PHP yang konsisten. ID/nama bertentangan diblokir.
- Approval unik menentukan nominal bisnis sekali. Setiap tujuan harus balance sendiri dan sesuai porsi dokumen sumber. Ketidakseimbangan yang saling menutup antarcompany tetap ditolak. Kelompok dibentuk sebelum pemilahan pending/posted; sebagian posted terlihat sebagai Posting sebagian.
- Detail dan ekspor mempertahankan seluruh baris/COA/keterangan/native key. Modal memisahkan subtotal per tujuan dan Total seluruh jurnal accounting dari nominal pembayaran.
- Lock voucher gabungan dan seluruh native member diambil dalam urutan tetap. Server membaca ulang anggota/fingerprint; payload browser tidak menentukan membership, nilai atau routing.
- Registry processing dipersist sebelum accounting. Writer dan counter existing diikat ke handle transaksi tujuan yang sama; STM, SUSTAIN dan VUCA dipisahkan eksplisit. Jalur intercompany lama tidak dipanggil dua kali.
- Sumber/registry, detail jurnal accounting dan counter wajib InnoDB. Header japh lokal memakai MyISAM; dukungan legacy ini memakai kompensasi terbatas pada identitas header baru yang dibuktikan belum ada sebelum percobaan. Hanya sebelum commit accounting mana pun dicoba, header tersebut boleh dibersihkan dan hasil cleanup wajib diverifikasi. Header historis atau collision nomor tidak dihapus.
- failed_no_commit boleh dicoba ulang dengan fingerprint sama setelah rollback/cleanup terkonfirmasi. processing/uncertain, commit berhasil sebagian, kehilangan acknowledgment atau cleanup gagal diblokir sebagai Perlu rekonsiliasi. Tidak ada klaim atomik lintas database.
- Staging posted hanya setelah semua tujuan commit terkonfirmasi. Registry tetap melindungi dari replay jika pembaruan source atau metadata gagal. Nomor prepared/writing tidak ditampilkan sebagai nomor terkonfirmasi. Audit satu outcome pada boundary controller; kegagalan audit tidak mengubah hasil bisnis.

## File yang dibuat atau diubah dalam task ini

| Area | File | Perubahan |
|---|---|---|
| Resolver | jurnal_all/libraries/Jurnal_debt_groups.php; models/Jurnal_all_model.php | Identitas gabungan, anggota, source/target validation, jumlah bisnis, status, batch/cache, filter/detail/report/export |
| UI | jurnal_all/controllers/Jurnal_all.php; views/preview.php; views/export_excel.php; assets/js/jurnal-all.js | Detail server, dua tabel, subtotal/status/nomor per tujuan, status blocker escaped |
| Posting | jurnal_all/libraries/Jurnal_debt_posting.php; Jurnal_all_dispatcher.php; migrations/006_debt_posting_attempts.sql | Lock, registry, orchestration, rollback/kompensasi terbatas, ketidakpastian dan metadata |
| Entry/writer | jurnal_payment_petty_cash controller + kedua model; jurnal/controllers/Jurnal.php; jurnal_payment/controllers/Jurnal_payment.php; jurnal_invoicing/models/Jurnal_invoicing_model.php | Satu jalur debt, koneksi writer/counter terikat, guard anchor lintasmodul |
| Pengujian | Debt_group_test.php; Debt_posting_test.php; Payment_mapping_test.php; Petty_posting_test.php; Client_test.js; scratch/verify-debt-grouping-20261021.php | Reproduksi, transaksi nyata disposable, fault injection, regresi cache/legacy/UI, pembacaan lokal |
| Dokumentasi | README modul Jurnal All; docs/implementation-plan.md; docs/task-progress.md; laporan ini | Kontrak, pemasangan, keputusan engine, hasil dan batas |

Path modul pada tabel berada di application/modules kecuali assets, scratch dan docs. Workspace sudah berisi perubahan pekerjaan lain; laporan ini tidak mengklaim seluruh git diff sebagai hasil task ini.

## Validasi

- Debt_group: 26 assertion baru lulus, bersama bootstrap Query130. Mencakup dua native key, ID company legacy sama, voucher berbeda, multidokumen, missing/ambiguous/unpaid source, business amount, filter/search/status/pagination/summary, modal dan Excel.
- Debt_posting: 142 assertion lulus pada empat schema MySQL disposable. Mencakup SUSTAIN/VUCA, real writer/counter/rollback, lock dua koneksi, routing entrypoint, migrasi idempotent, registry failure/lost acknowledgment, target begin/write/counter/commit failure, source commit failure, metadata/audit failure, tampering, invalid COA, crash/replay, MyISAM cleanup/collision dan header historis tetap.
- Regresi Jurnal All lulus: Query130, Payment_mapping47, Mixed_metadata20, Audit10, Challenger_concurrency72, Controller86, Dispatcher45, Legacy_posting198, Metadata20, Petty_posting113, Revision24, Client49. Assertion lama petty tetap dipertahankan; pengujian helper legacy dilakukan eksplisit, entrypoint baru diuji melalui fixture transaksi nyata.
- Validator/material tidak berubah dalam task ini; hasil terverifikasi fase sebelumnya tetap berlaku: validator43, controller journal-validation PASS, reconciliation24, Selection98, Batch1 248, Batch2 572, Selection_client27.
- PHP lint file task dan pemeriksaan whitespace scoped lulus. JavaScript diperiksa melalui Node dan client test. Tidak ada browser test.

Kegagalan baseline tidak disamarkan: Challenger_stress_test gagal rollback counter invoicing pada baris674, sama dengan model sebelum perubahan. Mysql_test fixture gagal permissions.created_by sebelum query model; paid_status fixture modul petty gagal loader.library sebelum validator pada task sebelumnya. Dua fixture terakhir tidak diulang tanpa perubahan lingkungan/fixture. Tes tidak dihapus atau dilemahkan.

## Batas dan tindak lanjut operasional

Tidak ada koreksi data otomatis atau mode reset percobaan. Jika status Perlu rekonsiliasi muncul, operator harus memeriksa registry hasil tiap tujuan, jurnal/japh/counter tujuan serta source; jangan mengulang posting melalui endpoint lain atau menghapus registry. Pengulangan setelah hasil parsial memerlukan prosedur rekonsiliasi terpisah yang ditinjau.

Environment lain perlu memasang migrasi003 dan006 serta memenuhi engine/COA tujuan sebelum fitur posting aktif. Kode menolak konfigurasi yang tidak dapat diverifikasi. Pembacaan seluruh kelompok hutang dicache per request; lookup relasi dan metadata dibatch, tanpa query per baris. Tidak ada perubahan pada Refill atau jenis pembayaran lain.

## Verifikasi kandidat publikasi development

Pengguna kemudian mengotorisasi commit/push development. Kandidat dibangun dalam worktree terpisah dari origin/development ac96db99, hanya grouping hutang, dependensi mapping metadata, tes dan dokumentasi terkait. Formatting controller/workspace serta perubahan audit Expense, validasi pembayaran Material, konfigurasi, unggahan dan pekerjaan lokal lain tidak disertakan. Laporan rekonsiliasi sebelumnya disertakan sebagai konteks; publikasi ini tidak mengklaim menerbitkan seluruh kode Material yang disebut laporan historis tersebut.

Kandidat terisolasi lulus Debt_group26, Debt_posting142 (write hanya disposable), Query130, Mapping47, Mixed20, Concurrency72, Controller86, Dispatcher45, Legacy198, Metadata20, Petty113, Revision24 dan Client49. Lint19 file PHP kandidat dan whitespace lulus. Password fixture posting kini diambil dari JOURNAL_TEST_PASSWORD, tanpa password development tertanam.

Audit_test pada kandidat gagal assertion metadata storage warning baris40. Kegagalan yang sama dibuktikan dengan controller Jurnal All asli dari HEAD ac96db99; workspace utama sebelumnya lulus karena perbaikan ownership audit Expense lokal yang tidak termasuk task ini. Tes/assertion tetap dipertahankan. Ini merupakan baseline tambahan khusus kandidat publikasi, bukan regresi grouping hutang; fault injection audit jalur debt sendiri lulus pada suite disposable. Stress/Mysql baseline yang sudah dicatat tidak diulang tanpa perubahan dependensinya.
