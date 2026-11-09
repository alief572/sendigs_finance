# Fase 2 — Request Mutasi batch 1

Tanggal: 13 Oktober 2026. Branch main. Status: **selesai dan terverifikasi pada cakupan batch 1**. Request Mutasi 10/21 endpoint, Fase 2 28/51 endpoint. Satu kegagalan suite lama yang identik dengan baseline dijelaskan di bawah; tidak diklaim seluruh test repository hijau.

## Grounding

SHA controller awal cocok manifest: `8346d2094fb6dc3d327953120bcc7fbbc9172cda36800b4279cdad8b87f26966`. Daftar sepuluh endpoint cocok kode. Baca controller/model Request Mutasi, generator BUM pada Jurnal_nomor, helper form pada All/Penerimaan Acc, Admin_Controller dan pemanggil views.

Jurnal aktual memakai koneksi bisnis dengan nama schema target; koneksi terpisah hasil resolver dipakai untuk lookup/validasi. Karena itu audit sukses harus sesudah commit koneksi bisnis, dengan semua insert/update/counter jurnal diperiksa. Routing STM/VUCA/Sustain dan perhitungan existing dipertahankan. Tidak menambah aturan permission; akses mengikuti Admin_Controller dan mekanisme existing. Recorder tidak dimuat constructor.

| Endpoint | Proses dan metadata | Cabang |
|---|---|---|
| index | Daftar request; row_count | isi/kosong, query/render gagal |
| create | Form request | query invoice/customer/COA/kurs/template, render gagal |
| save | Simpan request; request_id,date,currency,amount,target_accounting,COA asal/tujuan | input invalid, target invalid/unavailable, generator, lookup COA hilang/error, begin/insert/status/commit gagal |
| mutasi | Daftar aktual; row_count | isi/kosong, query/render gagal |
| approval_mutasi | Daftar approval; row_count | isi/kosong, query/render gagal |
| add_mutasi | Form aktual; request_id,row_count | ID/sumber hilang, query/helper/render gagal |
| save_mutasi | Simpan aktual+jurnal BUM; request_id,actual_id,date,currency,amount,rate,target | request/target hilang, generator, insert/update/jurnal header/detail/counter/nomor/status/commit gagal |
| get_coa_by_target | Lookup COA; target_accounting,row_count | target invalid/unavailable, query gagal/kosong |
| approval | Detail approval; request_id,row_count | sumber/ID hilang, query/render gagal |
| save_approval | Approve; request_id | validasi/sumber hilang, begin/update/status/commit gagal |

Nama/rekening, keterangan/terbilang bebas, session/request lengkap dan pesan exception tidak masuk audit. COA dicatat sebagai kode akun saja. Empty list sukses; dokumen/form tidak ditemukan gagal. Sebelas endpoint batch berikutnya tetap pending.

## Baseline pengujian

Lima suite standalone lama PASS. JournalRoutingTest.php sudah FAIL sebelum implementasi: 13 PASS/1 FAIL pada testPropertyJournalNumberAlwaysNonEmptyAndUnique, iterasi24 nomor BUK-101-112026-00001 berulang. Test memakai simulator sendiri, bukan controller asli; reset() mengembalikan journal_counter=1 pada tiap iterasi, tetapi generated_numbers dipertahankan. Kegagalan ini tidak diperbaiki/dihapus dalam cakupan audit; hasil akhir harus memisahkannya dari regresi baru.

## Implementasi dan keputusan

- Sepuluh endpoint memakai begin/complete recorder secara eksplisit; constructor dan sebelas endpoint pending tidak mengaktifkannya. Tidak menambah endpoint publik.
- Hasil baca ditentukan dari query/helper yang dipanggil. Daftar kosong berhasil; form/detail dengan sumber tidak ditemukan menghasilkan satu audit gagal sebelum 404. Render atau query yang terhenti mendapatkan fallback interrupted tanpa exception/SQL mentah.
- Save request, realisasi dan approval memeriksa input yang diperlukan, sumber dokumen, begin, hasil insert/update, status transaksi, serta commit. Sukses hanya dicatat sesudah commit; writer audit false/exception tidak mengubah respons bisnis.
- Save aktual mengambil target/currency dari request asal. Target yang dikirim ulang dari form tidak mengalihkan jurnal. Jurnal BUM, debit tujuan/kredit asal, nilai rupiah/valas, pembaruan counter dan penyimpanan nomor jurnal dipertahankan.
- Private writer jurnal memeriksa setiap insert header/detail/update counter/reference. Last affected_rows yang sehat tidak dipakai sebagai bukti bahwa seluruh operasi sebelumnya berhasil.
- Lookup target memisahkan koneksi bisnis dan koneksi lookup accounting. Error lookup asal disimpan sebelum query tujuan menghapus error driver. Invalid/unavailable target dan COA tidak ditemukan tetap menghasilkan status JSON 2 sebagaimana pola existing.
- Generator request menerima argumen status query opsional agar error pertama dalam count/MAX tidak tertutup query berikutnya. Query GetDataApprove dibinding; hasil/parameter pemanggilan lama tetap kompatibel.
- Helper shared Jurnal_nomor::get_Coa_Bank_Aja mendapat argumen status opsional. Lookup cabang/COA bulan berjalan/fallback bulan sebelumnya diingat hasilnya; dictionary hasil dan pemanggilan lama satu argumen tetap sama. Helper ini tidak membuka transaksi atau menulis database.
- Metadata hanya referensi, tanggal, nilai numerik/rate, kode currency/COA, target whitelist, jumlah baris dan outcome/reason. Keterangan/terbilang/nama bank, request/session lengkap, password dan SQL tidak diteruskan. Failure approval tetap membawa referensi dokumen. SQL eksplisit null mengikuti pola Fase 1.
- Tidak menambahkan perizinan/aturan approval baru. Pemeriksaan akses mengikuti implementasi existing Admin_Controller; keberadaan properti permission pada controller tidak diklaim sebagai pembuktian otorisasi setiap aksi.

## Hasil verifikasi

- RED: index pada fixture controller asli gagal karena belum menghasilkan audit; GREEN setelah instrumentasi.
- **Batch1_audit_test.php PASS: 394 skenario fixture + dua subprocess shutdown nyata.** Controller, recorder, Request_mutasi_model, generator Jurnal_nomor, All_model dan Acc_model terkait asli; database/render/upload/loader aplikasi memakai fixture, tanpa konfigurasi DB bisnis.
- Matrix mencakup STM/VUCA/Sustain, IDR/USD, generator baru/existing, lookup invalid/unavailable/hilang, empty reads, sumber hilang, query false dan errno yang terhapus, write false/errno dengan trans_status sehat, begin/status/commit gagal, writer false/exception, header/detail/counter/reference jurnal, fallback COA bulan sebelumnya dan kompatibilitas pemanggilan helper lama.
- Dua subprocess membuktikan satu event interrupted saat query form atau commit aktual dihentikan. Tidak menyimpan exception atau SQL mentah.
- **17 suite regresi audit sebelumnya PASS**: tujuh Audit Trail, lima batch Request Payment, Approval/Record Request Payment, PR Stok dan dua batch Pembayaran Material.
- **Lima suite lama Request Mutasi PASS**: CoaLookupTargetDbTest (16), DisplayLabelMappingTest (11), PersistenceRoundTripTest (16), TargetAccountingInheritanceTest (12), TargetAccountingResolverTest (15).
- **JournalRoutingTest tetap 13 PASS/1 FAIL**, sama persis dengan baseline sebelum perubahan, pada iterasi/nomor jurnal yang sama. Simulator mengulang counter setelah reset; suite ini tidak memakai controller/model produksi. Tes lama tidak diubah atau dilemahkan. Perbaikan simulator/definisi uniqueness berada di task terpisah.
- PHP lint empat file controller/model/shared model/test PASS; git diff --check PASS. JavaScript tidak berubah, sehingga JS syntax/browser tidak diperlukan.
- Perbandingan body metode dengan HEAD: hanya sepuluh endpoint batch ini dan dua private helper resolver/journal existing berubah, ditambah delapan helper audit private. Sebelas metode batch 2/3 persis tidak berubah dan tidak mengaktifkan scope. Nomor baris/status/SHA manifest diperbarui. Bukti lokal di scratch/phase2_request_mutasi_batch1_review.json.

## File berubah dan alasan

| File | Alasan |
|---|---|
| `C:/web_dev/sendigs_finance/application/modules/request_mutasi/controllers/Request_mutasi.php` | Pencatatan hasil sepuluh endpoint, transaksi/jurnal terkonfirmasi, metadata aman dan failure eksplisit |
| `C:/web_dev/sendigs_finance/application/modules/request_mutasi/models/Request_mutasi_model.php` | Status generator opsional dan binding lookup ID |
| `C:/web_dev/sendigs_finance/application/modules/jurnal_nomor/models/Jurnal_model.php` | Status lookup COA opsional, termasuk kegagalan cabang sebelum fallback |
| `C:/web_dev/sendigs_finance/application/modules/request_mutasi/tests/Batch1_audit_test.php` | Suite baru terhadap kode asli dengan kegagalan/metadata/dedup/routing dan kompatibilitas |
| `C:/web_dev/sendigs_finance/docs/audits/active-menu-audit-2026-10-10.json` | Status sepuluh endpoint, SHA, line, hasil tes dan next batch |
| `C:/web_dev/sendigs_finance/docs/implementation-plan.md` | Status/batas sesi Fase 2 diperbarui |
| `C:/web_dev/sendigs_finance/docs/task-progress.md` | Checkpoint utama lengkap dan pekerjaan berikutnya |
| `C:/web_dev/sendigs_finance/docs/audits/audit-rollout-checkpoint.md` | Ringkasan checkpoint terbaru |
| Laporan ini dan snapshot scratch lokal | Checklist, keputusan, hasil verifikasi dan bukti batas perubahan |

## Batasan dan kelanjutan

Berhenti setelah batch 1 sesuai permintaan user. **Fase 2 belum selesai: 28/51**, terdiri dari Pembayaran Material 18/18 dan Request Mutasi 10/21. Sisa sebelas Request Mutasi dan dua belas Pengajuan Rutin = **23 endpoint baseline**. Fase 0/1 tetap selesai; Fase 3–20 belum selesai rollout/verifikasi akhir.

Berikutnya jika diminta: Request Mutasi batch 2 — reject_mutasi, index_transaksi, create_transaksi, save_transaksi, save_jurnal_BUM, save_jurnal_BUK, printout, printout_mutasi, printout_transaksi, terbilang. Batch 3 getMataUangByKode tetap pending.

Docker API lokal kembali menghasilkan permission denied saat pemeriksaan read-only docker ps. Tidak menjalankan SELECT menu/DB live, migration, penulisan DB bisnis, browser, commit/push, atau integrasi MariaDB disposable. Database target dan atomicity tabel lintas schema belum diverifikasi live; kelulusan fixture tidak membuktikan deployment.

Risiko/TODO: satu failure simulator lama; concurrency generator MAX/counter, policy approval/permission existing, kesiapan schema/privilege/engine target; kehilangan audit bila writer tidak tersedia atau proses dimatikan paksa; perubahan working tree belum commit. Tidak memperbaiki aturan bisnis/permission, generator concurrency atau modul pending di luar scope batch.
