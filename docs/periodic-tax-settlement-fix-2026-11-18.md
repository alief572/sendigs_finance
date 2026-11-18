# Pelunasan PPh periodik

Pembayaran periodik dengan pokok Hutang PPh21/23 sebelumnya ditolak oleh
validator potongan PPh walaupun kontrol nol dan jurnal balance. Contoh
BK--1126-0005: debit Hutang PPh23 Rp20.192.299 dan kredit bank sama, tanpa
potongan PPh tambahan.

Validator kini membaca detail periodik dari pilihan payment server dengan
parameter terikat dan FOR UPDATE dalam transaksi save. Debit pokok wajib
cocok seluruh nominal per payment/COA sumber; potongan PPh tambahan tetap
divalidasi terpisah. Pemeriksaan bank, balance, referensi, rollback, commit
dan audit tetap berlaku. Metadata klien tidak menentukan pengecualian.

Perubahan produksi hanya pada Pembayaran_material_model::validate_payment_journal
dan caller Pembayaran_material::save_payment. Keduanya perlu dirilis bersama.
Tidak ada migrasi, akun baru, perubahan frontend atau koreksi data historis.
Cakupan: pokok Hutang PPh21/23 pada sumber periodik, nomor batch maupun item.

Validasi PHP7.4: Periodic_tax_settlement_test (71 skenario endpoint dan isolasi
dua payment), Payment_rounding_integration (83), hitung PPh, Batch1 (248),
Batch2 (595), Selection (98), Journal_validator (43), Journal_validation,
Reconciliation (24), Voucher_repair (19), Coa_completion (17). Node:
Payment_rounding_client dan Selection_client (27). Lint dan whitespace diperiksa.

Tes baru mencakup sumber berubah/hilang, manipulasi COA/nominal/referensi,
duplikasi, potongan tambahan, admin, staging/accounting/GL, kegagalan query,
write, transaksi, commit serta writer audit. Tes gagal pada kode sebelum fix.
Tidak ada tes browser, live save atau uji konkurensi lock pada MySQL nyata.
Tes baca DB grouping/detail yang terhalang sandbox tidak dianggap lulus.
