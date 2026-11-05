# Penutupan Penerimaan Uang dan Deposit Consultant

Tanggal: 29 Oktober 2026. Status: empat fase implementasi selesai dan terverifikasi melalui CLI dan MariaDB disposable. Tidak mengubah database aplikasi, menjalankan browser, deployment, commit, atau push.

## Perilaku akhir dan keputusan

- Customer menggunakan ID, bukan nama. Nama sama tetap customer berbeda; perubahan nama historis tidak menggandakan opsi customer atau kelompok monitoring.
- Customer dipilih lalu company. Company tunggal otomatis; invoice baru menggunakan owner accounting dari snapshot. Pilihan `legacy` mempertahankan proses invoice lama/Vuca tanpa memperluas penggunaan deposit ke alur tersebut.
- Daftar dokumen, saldo dan kartu deposit dibatasi customer/company. Respons AJAX yang sudah kedaluwarsa tidak mengganti konteks terbaru; perubahan konteks membersihkan dokumen, deposit dan modal preview.
- Saldo tidak valid ditampilkan sebagai belum dapat dipakai dengan pesan rekonsiliasi. Cash-only receipt tetap dapat disimpan; histori tidak dikoreksi otomatis.
- Validasi voucher meliputi tanggal aktif, status, nominal positif, persamaan awal-terpakai=saldo, sumber unik, mutasi IN/OUT serta rantai saldo. Voucher aktif bersaldo nol/negatif atau status habis tetapi masih memiliki saldo ditolak.
- Kwitansi harus lunas, mempunyai satu invoice DEP berpiutang nol, snapshot nominal/owner yang sesuai dan penerimaan pelunasan yang valid. Lebih bayar harus cocok dengan sumber penerimaan dan snapshot detail.
- Header/detail penerimaan sumber diverifikasi terhadap owner/customer, snapshot nominal, piutang, admin, PPh, saldo dan total uang bank. Total OUT semua voucher pada satu penerimaan harus tepat sama dengan total penggunaan deposit snapshot; voucher ganda dari sumber yang sama ditolak.
- Rantai saldo voucher diperiksa menurut urutan ID pencatatan; kartu menampilkan histori menurut tanggal/ID. FIFO konsumsi voucher memakai tanggal aktif lalu ID, termasuk saat urutan aktivasi berbeda dari urutan pembuatan.
- Preview invoice dan distribusi otomatis mengikuti tanggal invoice lalu ID (perbandingan ID sama dengan urutan kolom varchar database). Nominal save dipasangkan kembali ke ID request; perubahan urutan tidak mengalihkan nominal ke invoice lain. Distribusi manual tetap didukung dan tidak melebihi kapasitas invoice.
- Endpoint invoice/deposit mengunci jenis transaksi masing-masing. Field PPh/deposit/lebih bayar kwitansi yang bukan nol ditolak meskipun pilihan tingkat form tidak mengaktifkannya. Kwitansi lunas setelah admin mengaktifkan saldo penuh nilai dokumen.
- Penguncian, transaksi, sukses setelah commit, satu hasil audit per aksi, metadata allowlist dan toleransi kegagalan audit menggunakan mekanisme existing yang ikut diuji. Monitoring tetap hanya baca.

## Kontrak endpoint

Semua lookup memakai permission View existing. Save/rollback/posting mempertahankan permission masing-masing.

| Endpoint | Input | Hasil/kompatibilitas |
| --- | --- | --- |
| get_receipt_companies | id_customer, receipt_kind | status dan companies[{id,name}] dari dokumen terbuka; tidak mengandalkan nama customer |
| get_inv_by_cust | id_customer, id_company, receipt_kind, pilihan PPh | daftar hanya untuk pasangan terpilih; request lama berbasis nm_customer dibatasi dokumen legacy |
| get_customer_deposit | id_customer + id_company, atau id_invoice Consultant baru | status, saldo desimal, formatted, konteks; ID invoice menurunkan owner/customer dari snapshot dan menolak identitas eksplisit yang bertentangan |
| get_customer_deposit_ledger | konteks yang sama | mutasi hanya customer/company tersebut; total integer sen, saldo tersedia tervalidasi dan pesan anomali |
| process_alokasi / save_penerimaan_piutang | payload existing + konteks dari form | memaksa jenis invoice; campuran customer/company/DEP ditolak |
| process_kwitansi_deposit / save_kwitansi_deposit | payload kwitansi + konteks | memaksa deposit dan split jenis 8; hanya pelunasan penuh |

Company kosong tidak menghasilkan agregat lintas company. Lookup saldo gagal mengembalikan `status=0`, `saldo=null`, `error/message`, bukan angka nol yang dianggap valid. Model lookup saldo kompatibel juga memakai validator yang sama. Caller lama lookup deposit berbasis nama harus memperbarui payload; tidak ada fallback nama.

## File implementasi dan tes

Kode runtime:
- application/modules/penerimaan_uang/controllers/Penerimaan_uang.php
- application/modules/penerimaan_uang/libraries/Consulting_receipt.php
- application/modules/penerimaan_uang/views/add.php
- application/modules/penerimaan_uang/views/consulting_process.php
- application/modules/deposit_customer/models/Deposit_customer_model.php

Adapter/fixture/regresi diperbarui:
- application/modules/invoicing/tests/Consulting_integration_test.php
- application/modules/penerimaan_uang/tests/Concept_integration_cases.php
- application/modules/penerimaan_uang/tests/consulting_fixture.php
- application/modules/penerimaan_uang/tests/Deposit_company_test.php
- application/modules/penerimaan_uang/tests/Deposit_fifo_test.php
- application/modules/penerimaan_uang/tests/legacy_vuca_preview_test.php
- application/modules/penerimaan_uang/tests/render_consulting_fixture.php

Tes baru:
- Deposit_integrity_test.php: bukti urutan tanggal/ID, sumber sah dan 28 cabang anomali.
- Lookup_bootstrap_test.php: request controller tanpa dependensi yang dipreload harness; customer/company wajib.
- Deposit_closure_cases.php: native CI/MariaDB, lookup/form, sumber deposit, fault, concurrency, FIFO, report dan GL.
- company_context_test.js: form PHP sesungguhnya dijalankan dalam Node VM; pilihan company, ID customer, scoped request, dan stale AJAX.
- consulting_fifo_view_test.js: distribusi otomatis, manual, distribusi ulang dan kapasitas per invoice.
- consulting_kwitansi_view_test.js: modal khusus kwitansi, full allocation, admin dan jurnal seimbang.

Semua tes baru di atas berada pada application/modules/penerimaan_uang/tests. README modul, rencana implementasi dan checkpoint diperbarui. Inventaris hash dan status endpoint ada di penerimaan-deposit-closure-manifest.json. Perubahan workspace task lain dipertahankan.

## Bukti verifikasi akhir

| Pemeriksaan | Hasil |
| --- | --- |
| Native CI/MariaDB disposable | PASS 943 checks |
| Suite PHP/Node independen database | PASS 28/28 suite (19 PHP, 9 Node) |
| Integritas voucher | PASS sumber sah dan 28 cabang anomali |
| PHP lint | PASS 23 file |
| JavaScript syntax | PASS 8 file, termasuk render inline melalui Node |
| git diff --check cakupan task | PASS |

Integrasi mencakup creation/use/rollback lebih bayar; dua proses dengan alokasi/invoice berbeda yang memperebutkan saldo sama; beberapa kwitansi/admin Rp10.000; submit kwitansi bersamaan (satu commit); penolakan field terlarang; fault header/detail/jurnal/invoice/kwitansi/deposit/mutasi/alokasi/commit; fingerprint seluruh tabel terkait tanpa partial writes; rollback dependensi/posted; native BUM sukses/duplikat/commit failure; JV kwitansi; report dan ekspor menggunakan snapshot; uang bank multi-detail dihitung sekali; permission dan audit writer failure; regresi legacy/Vuca.

Pengujian berjalan memakai scratch/runtime/php74/php.exe dan Node. Perintah database:

```powershell
& scratch/runtime/php74/php.exe application/modules/invoicing/tests/Consulting_integration_test.php --isolated-local-db
```

Runner tidak membaca konfigurasi kredensial aplikasi. Menggunakan MariaDB loopback 33319, database acak codex_consulting_*, dua worker nyata dan cleanup finally. Migration 003_deposit_contract diuji dua kali untuk membuktikan idempotensi. Output 28 suite disimpan pada scratch/penerimaan-closure-verification.json (artefak lokal, tidak perlu dipublikasikan).

Kegagalan awal telah ditutup: urutan mengikuti request; fixture deposit tidak memiliki sumber/IN; ConsultingLoader tidak memiliki model(); fixture legacy tidak mengembalikan null untuk POST yang tidak ada; blok render daftar menduplikasi baris; bootstrap lookup mandiri tidak memuat service. Tes tidak dihapus atau dilemahkan: fixture dilengkapi dan assertion OUT mempertahankan pemeriksaan mutasi yang sama.

## Prasyarat penggunaan dan batas

Tidak ada TODO coding pada plan penutupan ini. Sebelum fitur dipakai pada lingkungan aplikasi, pastikan schema migration invoice/receipt/deposit existing, mapping company, COA bank/admin/piutang/deposit, permission, InnoDB dan konfigurasi target GL tersedia. Penerapan migration dan deployment berada di luar pekerjaan ini. Tidak ada migration baru atau backfill otomatis.

Histori yang tidak mempunyai bukti sumber/snapshot/mutasi lengkap sengaja diblokir dari penggunaan deposit dan perlu rekonsiliasi terpisah. Hasil disposable tidak merupakan verifikasi terhadap isi atau konfigurasi database aplikasi/production. Tidak ada tes browser sesuai instruksi pengguna.
