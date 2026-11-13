# Implementasi penyelarasan penerimaan_uang — 26 Oktober 2026

## Hasil

Fase 1 kontrak nominal form/server dan rekonsiliasi bank, fase 2 riwayat PPh/legacy, fase 3 jurnal/snapshot/report/rollback, serta fase 4 pengujian/review selesai. Deposit tetap tidak dibuat. Tidak ada perubahan database aplikasi, koreksi historis atau deployment.

## Perubahan dan alasan

1. **Controller, model, Consulting_receipt, Penerimaan_calculation**: alokasi gross dipisahkan dari kas net; kontrol menggunakan sisa bank database; admin dibatasi total Rp10.000; PPh menggunakan riwayat snapshot/jurnal dengan batas kumulatif 2% DPP; fingerprint mencakup riwayat; mixed ditolak; daftar invoice yang perlu rekonsiliasi tidak dapat dipilih.
2. **views/add.php, consulting_process.php, detail.php dan assets/js/penerimaan_uang/consulting.js**: nominal POST mentah; perhitungan sen integer; input alokasi/sisa saling memperbarui; batas PPh, admin, kas nonnegatif dan kontrol memblokir save; tanggal jurnal dari koran; teks jurnal aman melalui textContent; nominal historis dan dua desimal dari snapshot.
3. **invoicing/libraries/Invoice_consulting.php**: canonical receipt_journal mendukung snapshot kalkulasi 4 dengan kredit saldo awal/debit sisa serta deskripsi SPK/KB. Writer dan posting GL memakai generator yang sama; format snapshot lama tetap didukung. Tidak mengubah aturan invoice/invoicing.
4. **Report/detail/ekspor**: mapper snapshot mengembalikan angka transaksi historis meskipun field invoice berubah; kas header tetap dihitung sekali. Rollback memverifikasi jurnal terhadap snapshot, urutan cicilan dan bukti potong, lalu memulihkan saldo/alokasi/PPh dalam transaksi existing.
5. **Tes**: fixture To-Be existing dipisahkan ke consulting_fixture.php dan kontraknya diperbarui dari kas net menjadi alokasi gross. Tambahan concept_fixture.php, Concept_contract_test.php, Concept_integration_cases.php, render_consulting_fixture.php, consulting_client_test.js, consulting_view_test.js. Runner Consulting_integration_test diperluas: fixture no_spk, helper print existing, operasi worker receipt, injeksi failure receipt, dan ekspektasi mixed ditolak. Audit scratch lama diteruskan ke regresi baru.
6. **Dokumentasi**: README modul, implementation-plan, task-progress dan laporan ini mencatat kontrak terbaru, rilis bersama, hasil validasi dan batasan. Laporan audit awal dipertahankan sebagai riwayat temuan.

## Bukti verifikasi

- RED: Concept_contract_test awal gagal pada payload alokasi gross dengan pesan kontrol -3.669.166,67, sesuai audit. Setelah implementasi lulus.
- MariaDB terisolasi: **627 checks PASS** (`Consulting_integration_test.php --isolated-local-db`), mencakup receipt race pada dua koneksi, kegagalan tulis tiap tahap/commit, rollback gagal/sukses, permission, bukti potong, PPh, GL, laporan/ekspor HTML aktual, audit tepat satu hasil termasuk writer gagal, regresi consulting/Vuca. Runner memakai kredensial fixture tersendiri dan database acak yang dihapus dalam finally; tidak membaca kredensial aplikasi.
- Suite PHP penerimaan PASS: Concept_contract_test, enam kelompok Consulting_receipt_tobe_test, calculation_test, detail_journal_test, legacy_vuca_preview_test.
- Consulting_calculation_test PASS **99 checks**, termasuk kontrak snapshot jurnal lama.
- Node PASS: consulting_client_test, consulting_view_test, calculation_test dan installment_view_test. Payload berasal dari view PHP aktual; preview dicocokkan dengan jurnal simpan.
- Lint PHP seluruh modul penerimaan dan dua berkas invoice terkait PASS; syntax Node helper dan dua tes baru PASS; git diff --check tanpa error whitespace.
- Tidak menguji melalui browser. Suite report lama yang mengandalkan jumlah data aplikasi tertentu tidak dijalankan; query report dan ekspor aktual sudah diuji dengan database disposable.

## Penerapan dan risiko tersisa

- Upload paket produksi secara bersama: modul penerimaan controller/model/library/views, JS consulting, dan shared Invoice_consulting.php. Tes/dokumentasi tidak perlu dieksekusi pada database aplikasi. Tidak ada schema migration baru.
- Snapshot kalkulasi baru memerlukan generator jurnal yang diperbarui untuk posting GL; hindari mencampur file lama/baru. Belum ada deployment dari task ini.
- Riwayat legacy dan consulting lama tidak dikoreksi. Riwayat PPh/saldo/jurnal yang tidak konsisten sengaja diblokir dan perlu rekonsiliasi terpisah.
- Kategori Non PPN umum tidak ditambahkan; pajak mengikuti PKP/WAPU/KB yang sudah didukung. Invoice lama tetap alur historis, dan penerimaan gabungan lama/baru harus dipisahkan.
- Tidak ada TODO implementasi dalam cakupan rencana yang disepakati. Review visual/browser dan verifikasi lingkungan deployment belum dilakukan sesuai cakupan pengguna.
