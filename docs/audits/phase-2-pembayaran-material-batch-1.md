# Fase 2 — Pembayaran Material batch 1

Tanggal mulai: 13 Oktober 2026. Branch main. Status: sedang dikerjakan.

## Grounding dan checklist

Controller aktual tetap mempunyai 18 endpoint baseline; perubahan company resolution, admin charge dan detail/jurnal pembayaran dipertahankan. Model terkait: Pembayaran_material_model, Master_model, Jurnal_model dan All_model. All_model dataUpdate mengembalikan affected_rows (nol bisa merupakan no-op), dataSave mengembalikan insert ID; hasil tersebut tidak boleh dianggap boolean query tanpa pemeriksaan.

| Endpoint | Proses | Metadata aman | Cabang |
|---|---|---|---|
| index | Daftar request material/nonmaterial | row_count | isi/kosong, query/render gagal, legacy history |
| request_payment_save | Edit request dan pilihan item PO | request_id, request_number, transaction_type, item_count | validasi, sumber hilang, material/nonmaterial, begin/update/status/commit gagal |
| payment_list | Halaman Payment List | — | render berhasil/terhenti |
| get_data_payment_list_pr | Tabel pembayaran PR | start,length,search_present,row_count | isi/kosong, tiap query gagal |
| get_data_payment_list_non_pr | Tabel pembayaran non-PR | start,length,search_present,row_count | isi/kosong, tiap query gagal |
| form_payment_new | Form pembayaran | payment_ids,document_count,jenis_payment,admin_charge_bearer | parameter kosong, sumber kosong, query/render gagal |
| save_payment_new | Simpan pembayaran material + jurnal | document_ids,document_count,payment_date,currency,payment_number,admin_charge_bearer | validasi, sumber hilang, TR-01/TR-02/FORWARDER, IDR/valas, penanggung admin, begin/insert/update/query/status/commit gagal |
| view_payment_new | Detail voucher dan jurnal | payment_id,row_count,journal_count | voucher/input kosong, resolusi ID voucher/item, fallback jurnal, tiap query/render gagal |
| save_payment_new_nonmaterial | Simpan pembayaran nonmaterial + jurnal | sama jalur material | validasi, sumber hilang, TR-01/TR-02/FORWARDER, IDR/valas, penanggung admin, begin/insert/update/query/status/commit gagal |
| list_request_payment | Reset pilihan dan buka daftar | jenis_payment | delete/reset transaksi gagal, render terhenti |

Tidak menyalin keterangan, rekening, nama supplier/user, isi request/file atau SQL mentah. Hasil tulis ditentukan setelah commit; history lama dipertahankan melalui defer recorder. Pembacaan daftar kosong sukses; detail/form tanpa sumber gagal. Endpoint batch 2 belum diaktifkan.

## Validasi

Belum selesai. Fixture controller/model asli, kegagalan per-query/per-write, transaksi, metadata dan deduplikasi harus lulus sebelum penandaan selesai. Tidak memakai database bisnis atau browser.

## Checkpoint batch 1 selesai

10/18 endpoint pembayaran_material selesai; Fase2 10/51. PASS246 skenario fixture controller/model/jurnal asli dan subprocess interrupted. PASS15suite regresi fondasi/Fase1/PRStok; lint3PHP dan diff check PASS. RED awal membuktikan atribusi/outcome index belum eksplisit, GREEN setelah implementasi. Query error pertama dipertahankan; write false diuji meskipun fixture trans_status sehat. Penulisan hanya setelah commit terkonfirmasi; helper history tetap menulis histories dengan satu audit. Konteks dibatasi, 101referensi menyimpan maksimal100ID dengan jumlah total utuh. Tidak DB bisnis/browser/migration/commit. Batch2 belum diinstrumentasi saat checkpoint ini.

File berubah: controller pembayaran_material, model Pembayaran_material_model (hasil query opsional dan reset transaksi), test Batch1_test, manifest, rencana/checkpoint dan laporan ini. Pemeriksaan begin/update/insert/query/commit diperbaiki hanya untuk hasil audit akurat; rumus jurnal/admin charge existing dipertahankan. Risiko SQL interpolasi/concurrency/file legacy belum diselesaikan global. Berikutnya8endpoint batch2.
