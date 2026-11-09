# Rekonsiliasi invoice "Bayar sebagian" yang tidak dikoreksi (9 Nov 2026)

Sumber: DB dev, baca-saja, setelah migrasi 025/027/028 di lokal. Tidak ada data piutang pada daftar ini yang diubah.
"Bruto" = `tagihan_ppn_jurnal`; "neto" = `total_akhir_jurnal` (dasar `saldo_piutang`).

## Lebih bayar nyata
| Invoice | Neto | Dibayar (cash) | Saldo | Keterangan |
|---|---|---|---|---|
| 184/STM/2025 | 22.565.180 | 24.527.180 | -1.962.000 | Customer membayar lebih Rp 1.962.000. Perlu keputusan finance: dikembalikan atau dijadikan deposit (butuh jurnal), bukan sekadar menolkan saldo. |

## Kurang bayar tercatat (sisa diinput pada penerimaan itu sendiri)
| Invoice | Neto | Dibayar | Saldo | Dugaan penyebab / tindakan |
|---|---|---|---|---|
| 015/VSB/2026 (12 invoice, nomor sama) | 1.990.000 | 1.960.000 | 30.000 | Customer memotong PPh 2% (40.000) sedangkan anggaran PPh invoice VSB 0,5% (10.000); sisa = selisih PPh. Keputusan finance/pajak: tagih, hapus, atau perbaiki anggaran PPh. Periksa juga kenapa 12 invoice berbagi nomor `015/VSB/2026`. |
| 029/VSB/2026 | 20.397.500 | 20.090.000 | 307.500 | Sama: PPh 2% (410.000) vs anggaran 0,5% (102.500). |
| 049/STM/2025 | 49.050.000 | 33.000.000 | 16.050.000 | Dicicil 2 kali (7,5 jt + 25,5 jt); kurang bayar sungguhan. Tindak lanjut lewat Follow Up Piutang. |
| 142/STM/2026 | 13.080.000 | 12.120.000 | 1.200.000 | PPh N; dibayar 12,12 jt dari bruto 13,32 jt. Kurang bayar sungguhan. |
| 148/STM/2026 | 5.412.219,51 | 5.181.175 | 231.044 | Kurang bayar sungguhan (PPh Y). |
| 205/STM/2026 | 42.510.000 | 40.950.000 | 1.560.000 | Kurang bayar sungguhan; selisih = 4% DPP, mungkin potongan lain oleh customer. Konfirmasi ke customer. |
| 206/STM/2025 | 26.160.000 | 21.800.000 | 4.360.000 | Detail penerimaan mencatat `sisa_piutang` 0 tetapi invoice masih 4.360.000. Bila memang belum lunas, yang salah adalah detail penerimaan (bukan saldo). Konfirmasi finance. |

## Lain-lain, belum dianalisis tuntas
| Invoice | Saldo | Catatan |
|---|---|---|
| 194/STM/2024, 195/STM/2024 | besar | Memiliki penerimaan nyata sekaligus baris `INJECT`; belum jelas lunas atau belum. |
| 41 invoice dengan baris `INJECT` (nominal NULL) | total ±1,54 miliar | Baris tambalan tanpa nominal/tanggal buat; 22 di antaranya tampil sebagai "pembayaran" bernominal 0 di realisasi 2026. Perlu keputusan lunas atau belum. |

## Dikoreksi (rujukan)
32 invoice (biaya admin, saldo tidak turun, saldo negatif pembulatan/PPh) lewat `monitoring_piutang/migrations/028_fix_saldo_lunas.sql`, rollback `026_rollback_fix_saldo_desimal.sql`.
