# Perbaikan jurnal Payment posted (id approval menjadi id_payment)

Normalisasi pending (`docs/payment-pending-normalization.md`) sengaja tidak menyentuh jurnal yang sudah diposting. Dokumen ini
menjelaskan alat terpisah untuk lima kelompok posted yang `tr_jurnal.no_transaksi`-nya masih `payment_approve.id`, beserta
pasangannya di accounting (TRAS: `japh` dan `jurnal`).

## Cakupan (daftar putih di `Payment_posted_repair::WHITELIST`)
| id lama | voucher (`id_payment`) | ledger | tr_jurnal | japh | jurnal |
|---|---|---|---|---|---|
| BK--0426-0045 | BK--0426-0008 | VUCA | 5 | 101BK2600001 | 2 |
| BK--0626-0019 | BK--0926-0001 | STM | 6 | 101BK2600004 | 4 |
| BK--0626-0061 | BK--0926-0010 | STM | 9 | 101BK2600006 | 6 |
| BK--0726-0040 | BK--0126-0001 | STM | 23 | 101BK2600001 | 20 |
| BK--0826-0075 | BK--0926-0014 | STM | 4 | 101BK2600005 | 2 |

Total: `tr_jurnal` 47 baris, `japh` 5 (+5 `note`), `jurnal` 34, alias 5 kelompok. Hanya kolom referensi yang berubah
(`tr_jurnal.no_transaksi`, `japh.no_reff` dan `note`, `jurnal.no_reff`). `jurnaltras`, `keterangan`, nominal, COA, tanggal,
company, `sts`, `tgl_posting`, `posted_by` tidak disentuh.

Sengaja tidak dikerjakan (perlu keputusan manusia): `BK--0825-0001` (voucher dipakai 5 approval, kemungkinan duplikasi posting
KS-2025-00202 di VUCA) dan `BK--1026-0040,...` (kunci dipakai bersama jurnal Refill Pettycash).

## Perintah (hanya CLI)
```
PAYMENT_MANIFEST=/path/di-luar-webroot/posted-repair.json php index.php jurnal_all/payment_reference_cli/repair_posted dry-run
... repair_posted apply     # hanya setelah manifest ditinjau
... repair_posted verify
... repair_posted rollback
```
- `dry-run` hanya membaca; menulis `manifest.json` dan `manifest.json.backup.json` (before-image lengkap + checksum).
- `apply` memeriksa ulang SEMUA kelompok terhadap isi yang ditinjau sebelum menulis apa pun; data yang berubah menggagalkan seluruh run.
- Urutan per kelompok: `japh`, lalu `jurnal` (MyISAM, tidak ada rollback transaksi, tiap UPDATE dicek jumlah barisnya),
  lalu `tr_jurnal` + alias dalam satu transaksi InnoDB. Gagal di tengah memulihkan accounting dari backup.
- Idempoten: kelompok yang sudah berubah dilewati. Progres disimpan per kelompok.

## Prasyarat sebelum apply
1. Persetujuan akuntansi (menulis ke buku accounting yang sudah diposting di STM dan VUCA).
2. `mysqldump` `tr_jurnal`, `tr_jurnal_payment_reference_aliases`, `japh`, `jurnal` (STM, VUCA) sebagai jalur terakhir.
3. Konfirmasi database target (`config/development/database.php` menunjuk `db_sendigs_ss`).
4. Jendela waktu sepi: lock `payment_reference:write` hanya melindungi jalur aplikasi.
