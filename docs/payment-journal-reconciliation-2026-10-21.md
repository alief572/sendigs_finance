# Validasi Jurnal Pembayaran dan Rekonsiliasi BK--1026-0002

## Status akhir

Implementasi pencegahan jurnal tidak balance dan metadata legacy selesai. Dry-run/laporan koreksi tersedia. **Koreksi existing belum layak diterapkan dan tidak diterapkan.** Tidak ada migrasi, perubahan data bisnis, posting, commit, push, deployment atau pengujian browser.

## Perubahan dan alasan

- Library `Payment_journal_validator` dan guard controller `save_payment`: validasi per no_transaksi/jenis sesudah jurnal lengkap, sebelum insert staging. Cents eksak, overflow nominal/penjumlahan, COA aktif, nominal negatif/invalid, kelompok nol/kosong diperiksa. Response existing tetap, satu audit invalid_journal dan rollback. Tidak mengubah writer/counter accounting maupun membuat baris penyeimbang otomatis.
- Fixture Batch1/Selection/Batch2 diperbaiki menjadi jurnal seimbang untuk skenario sukses. Seluruh assertion transaksi/query/write failure, commit, audit deduplication dan writer failure dipertahankan. Tes validator/controller baru membuktikan penolakan sebelum insert staging/accounting; writer audit gagal tidak mengubah hasil bisnis.
- Model Jurnal All: batch/cache sibling (200 key) memisahkan metadata tipe khusus hanya bila terdapat kelompok pendamping dengan native key sama, termasuk yang posted. Prioritas approval/voucher/dokumen sebelumnya tetap. Header Payment memakai kategori unik terurut + voucher/native; COA detail, nominal, grouping/status dan keterangan sisi hutang/piutang tidak diubah.
- Library `Payment_voucher_reconciliation` dan CLI khusus: hanya SELECT pada transaksi READ ONLY/REPEATABLE READ, rollback, tidak ada mode apply. Koleksi referensi melalui token approval/voucher/dokumen, daftar comma/semicolon yang berbeda urutan, keterangan dan nomor accounting terkait; kandidat kredit bank tanggal/nominal sama juga ditandai konservatif. Penelusuran nomor terkait dibatasi empat putaran dan gagal tertutup jika belum stabil/target tidak tersedia.
- Dokumen rencana/checkpoint, README kedua modul dan laporan ini menyimpan kontrak serta bukti. CLI menghasilkan evidence JSON ke stdout; artefak lokal pengujian disimpan di `scratch/reconcile-bk-1026-0002.json` dan hasil pengulangan.

## Temuan sumber dan jurnal

Enam approval: BK--1026-0006 (PERIODIK-2026-00181; 5401202), 0008 (PERIODIK-2026-00177; 1475000), 0039 (RPC-2026-0029; 207000), 0040 (DPM-2026-00081; 200000), 0041 (DPM-2026-00083; 797000), 0042 (DPM-2026-00082; 840000). Seluruhnya voucher BK--1026-0002, tanggal bayar 2026-10-05, status2, kurs1; pembayaran/total 8920202, selisih0 dan bank_charge0 sesuai sumber.

| Kelompok | ID tr_jurnal | Debit existing | Kredit existing | Usulan target kredit |
| --- | --- | ---: | ---: | ---: |
| Payment (Cash + Periodik) | 11079–11087 | 8713202.00 | 0.00 | 8713202.00 |
| Refill Pettycash | 11088–11089 | 207000.00 | 207000.00 | 207000.00 |

Native key tidak diubah: `BK--1026-0040,BK--1026-0006,BK--1026-0008,BK--1026-0039,BK--1026-0041,BK--1026-0042`. Snapshot seluruh kolom sebelas baris terverifikasi identik sebelum/sesudah dan cocok baseline SHA256 `0188d46ad78db0b71a80c438f7e93e5c0879bf452dbb224a382cc591b6816a17`. Normalisasi fingerprint mempertahankan null dan menyeragamkan tipe scalar hasil mysqli prepared/text.

Model nyata memunculkan dua kelompok: Payment hanya lima referensi Cash/Periodik dan header `Pembayaran Cash, Periodik - BK--1026-0002`; refill hanya RPC-2026-0029. Detail 9+2 dan Excel11 baris tetap, Payment tetap dilaporkan tidak balance. Perubahan metadata tidak menyamarkan kekurangan kredit.

## Rekonsiliasi accounting dan blocker

Sumber `payment_approve.coa_bank = 18` adalah ID `ms_bank`, bukan nomor COA. `ms_bank.id18` adalah PT. Sentral Tehnologi Managemen, aktif, menunjuk `1101-02-01`; COA accounting master mengonfirmasi Bank BCA. Tabel bank tidak menyediakan foreign key owner company yang authoritative. Company pada tiga baris Cash 11085–11087 kosong; label STM dari MAX header tidak digunakan sebagai bukti company.

Penelusuran melalui `no_request = BK--1026-0002` menemukan:

| jurnaltras.id | no_perkiraan | Debit | Kredit | stspos |
| --- | --- | ---: | ---: | --- |
| 1183 | 18 | 0 | 8920202 | 1 |
| 1184 | 2101-01-01 | 8920202 | 0 | 1 |
| 1185 | 6101-01-24 | 0 | 0 | 1 |

Nomor/no_reff pada jurnaltras terpotong oleh panjang kolom; pencarian exact-match daftar utuh sebelumnya tidak cukup. Nilai bank18 di jurnaltras berbeda dari COA master1101-02-01 yang sudah diresolusikan. Ini merupakan anomali accounting tambahan; tidak dinormalisasi atau dihapus otomatis.

Metadata `tr_jurnal_postings` terkait kosong. Tidak ditemukan record terkait pada delapan tabel jurnal/japh di db_sendigs_gl_ss, db_sendigs_ss_stm, db_sendigs_ss_vuca dan db_sendigs_ss_sustain setelah penelusuran nomor stabil. Hasil tersebut tidak membatalkan potensi overlap jurnaltras, serta bukan konfirmasi posting yang aman.

Output usulan: kredit8713202, debit0, native key/type Payment asli, tanggal2026-10-05, COA1101-02-01 / Bank BCA. `id_company`, `nm_company`, dan `no_jurnal` tetap null; `apply_ready=false`. Status selalu konservatif untuk instalasi tanpa relasi owner bank eksplisit.

**TODO sebelum pekerjaan koreksi terpisah:** tetapkan company tiga baris Cash dan owner bank dari bukti sumber; telusuri status/jalur jurnaltras1183–1185 dan penggunaan no_perkiraan18; putuskan rekonsiliasi lintas sumber agar kredit bank tidak terhitung dua kali. Jangan membuat pembayaran/voucher ulang atau menambah kredit langsung dari laporan ini.

## Bukti pengujian

- RED controller: fixture debit8713202/kredit0 diterima sebelum guard; assertion penolakan gagal. GREEN: status0, rollback, tanpa commit/insert staging-accounting, satu alasan invalid_journal dan pesan selisih; mencakup journal kosong serta writer audit failure/exception.
- Journal_validator43 assertions: jenis jurnal yang tersedia, multi-ID satu kelompok, dua sisi hutang, admin company/recipient, pajak nol/positif, 0.10+0.20=0.30, batas integer cents, overflow jumlah, invalid/negatif/boolean, COA/identitas kosong, dan ketidakseimbangan antarjenis/key. Identitas kelompok memakai serialisasi byte-safe agar kegagalan encoding JSON tidak menyatukan kelompok berbeda.
- Selection98 skenario, Batch1 248, Batch2 572, Selection_client27 checks: PASS. Fault injection per query/write, begin/status/commit, status Paid dan audit writer tetap diuji.
- Reconciliation24 assertions: sumber berubah/malformed, posted, metadata posting, missing company, overlap ledger, target unavailable, owner/COA belum pasti, fingerprint, token prefix/list/JSON, dan penolakan CLI --apply sebelum koneksi.
- Jurnal All: Query130, Mixed_metadata20, Payment_mapping47, Audit10, Concurrency72, Controller86, Dispatcher45, Legacy_posting198, Metadata20, Petty_posting113, Revision24, Client48: PASS. Lookup205 kelompok memakai dua query payment dan dua query sibling; missing sibling cache tidak mengulang query detail.
- Model database lokal READ ONLY: dua kelompok, sebelas detail/Excel, metadata benar, snapshot tetap. CLI dijalankan ulang dengan hasil baseline/snapshot identik; tidak ada koreksi data.
- PHP lint seluruh file PHP yang ditambah/diubah pada task ini dan scoped whitespace PASS. Review diff mempertahankan perubahan pekerjaan lain; tidak ada refactoring di luar cakupan.

## Batas validasi

- Challenger_stress_test gagal pada assertion rollback counter invoicing baris674; kegagalan identik sudah dibuktikan dengan model sebelum perbaikan mapping pada laporan sebelumnya. Jalur tersebut tidak memakai validator/sibling baru; tes tidak dihapus/dilemahkan.
- paid_status_test (dependensi controller yang ditemukan saat final review) membuat disposable schema dan lulus pemeriksaan model status awal, lalu gagal `PaidTestLoader::library()` pada `beginAudit()` save_payment sebelum guard baru. Fixture belum mengikuti integrasi audit yang sudah ada sebelum task ini. Tidak diperbaiki di luar cakupan; database fixture dibersihkan oleh finally existing.
- Mysql_test Jurnal All memiliki kegagalan baseline schema fixture permissions.created_by pada migrasi menu, sebelum pemanggilan model. Fixture/migrasi tidak berubah; tidak mengulang percobaan identik. Bukti sebelumnya pada docs/jurnal-all-payment-mapping-2026-10-21.md dipakai sebagai batas validasi yang masih berlaku.
- Rollback mengikuti transaksi/engine existing; tidak merombak atomisitas MyISAM/lintas database maupun cleanup file upload. Tidak ada klaim bahwa seluruh suite hijau atau semua kegagalan existing telah diperbaiki.
