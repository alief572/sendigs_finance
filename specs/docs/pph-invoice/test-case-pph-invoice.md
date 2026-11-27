# Test case: PPh Invoice (to-be sendigs)

Buka `specs/docs/pph-invoice/index.html`. Klik **Reset data** dulu. Hari ini di mockup 27-Nov-2026; tanggal berlaku contoh 01-Nov-2026 (menunggu konfirmasi Finance & Accounting). Akun PPh hanya **PPh 23 Disetor 1106-01-02** dan **PPh Final Disetor 1106-01-05** (0,5%). Akun bertanda "COA menyusul" belum punya kode.

## Data contoh

Penerimaan di **Penerimaan Piutang** (tab Penerimaan Piutang Invoice):

| Tanggal | Sumber | Nominal | Untuk case |
|---|---|---|---|
| 20-Nov-2026 | BCA Bank STM | 16.350.000 | PPH-03 Dipotong Client (MADA, INV 281) |
| 21-Nov-2026 | BCA Bank STM | 40.000.000 | PPH-05 Tidak Dipotong Client (ARWANA, INV 284) |
| 24-Nov-2026 | OCBC Bank Vuca | 5.550.000 | PPH-24 Tidak Ada PPh (KAI, INV 290) |
| 25-Nov-2026 | BCA Bank STM | 11.100.000 | bebas (ARWANA, INV 292) |
| 26-Nov-2026 | OCBC Bank Vuca | 7.960.000 | PPH-21 PPh Final UMKM (KAI, INV 297) |
| 18-Nov-2026 | Unlocated UNL-2611-0004 (BCA Bank Sustain) | 13.080.000 | PPH-23 dari Unlocated (INDO KORDSA, INV 299) |

Invoice outstanding (pajak sendigs: DPP = 11/12 Penjualan, PPN 12% × DPP hanya PKP, PPh 23 = 2% Penjualan, UMKM: PPh Final 0,5%):

| No. Invoice | Customer | TAX | Penjualan | PPN | Tagihan | PPh |
|---|---|---|---|---|---|---|
| INV 281/STM/2026 | PT MADA WIKRI TUNGGAL | PKP | 15.000.000 | 1.650.000 | 16.650.000 | 300.000 |
| INV 284/STM/2026 | PT ARWANA CITRAMULIA TBK | WAPU | 40.000.000 | 0 | 40.000.000 | 800.000 |
| INV 292/STM/2026 | PT ARWANA CITRAMULIA TBK | PKP | 10.000.000 | 1.100.000 | 11.100.000 | 200.000 |
| INV 290/VSB/2026 | PT KERETA API INDONESIA | PKP | 5.000.000 | 550.000 | 5.550.000 | 100.000 |
| INV 297/VSB/2026 | PT KERETA API INDONESIA | UMKM Non-PKP | 8.000.000 | 0 | 8.000.000 | 40.000 (PPh Final) |
| INV 299/SSC/2026 | PT INDO KORDSA TBK | PKP | 12.000.000 | 1.320.000 | 13.320.000 | 240.000 |

Sudah ada di PPh Invoice:

| No. Invoice | Customer | Jenis | PPh | Status |
|---|---|---|---|---|
| INV 245/SSC/2026 | PT INDO KORDSA TBK | Dipotong Client | 400.000 | Menunggu Bukti Potong |
| INV 233/STM/2026 | PT HEINZ ABC INDONESIA | Tidak Dipotong Client | 600.000 | Belum Diproses |
| INV 198/STM/2026 | PT ASAHI DENSO INDONESIA | Dipotong Client, sebelum berlaku | 220.000 | Menunggu Bukti Potong |
| INV 150/STM/2026 | PT MADA WIKRI TUNGGAL | Dipotong Client, sebelum berlaku | 200.000 | Selesai (ada di Bukti Potong Diterima) |

## Case

| No | Langkah | Hasil yang diharapkan | Hasil |
|---|---|---|---|
| PPH-01 | Penerimaan Piutang → Process penerimaan mana saja | Pilihan **PPh**: Dipotong Client / Tidak Dipotong Client / Tidak Ada PPh, dengan penjelasan per pilihan | |
| PPH-02 | Pilih customer **PT INDO KORDSA TBK** | Hanya INV 299 yang muncul; INV 245 tidak muncul, ada catatan "sisa piutangnya tinggal PPh … lewat PPh Invoice" | |
| PPH-03 | 16.350.000 → MADA → **Dipotong Client** → INV 281 → Process | Kontrol 0 (16.650.000 − 300.000). Jurnal Dr Bank BCA STM 1101-02-09 / Cr Piutang 1102-01-01 16.350.000, **tanpa** Dr PPh 23 | |
| PPH-04 | **Bandingkan dengan jurnal sekarang** → Save | Pembanding: jurnal sekarang ada Dr PPh 23 Disetor 1106-01-02. Setelah Save: tab PPh Dipotong, INV 281 **Menunggu Bukti Potong** | |
| PPH-05 | 40.000.000 → ARWANA → **Tidak Dipotong Client** → INV 284 → Save | Jurnal Dr Bank 40.000.000 / Cr Piutang 39.200.000 / Cr **PPh 23 Disetor 1106-01-02** 800.000. Tab **PPh Tidak Dipotong**: status **Belum Diproses**, kolom Request Payment "Belum", tidak ada aksi Input Bukti Potong | |
| PPH-06 | Gear INV 284 → **Process to Request Payment** | Nominal Pengembalian 800.000 (readonly). Preview jurnal tanggal "(tanggal Payment)": Dr PPh 23 Disetor 1106-01-02 / Cr **Bank pembayar (dipilih saat Payment)**; belum tampil bank apa | |
| PPH-07 | Isi Bank `BCA`, No. Rekening `12ab` | Ditolak: no. rekening harus angka | |
| PPH-08 | No. Rekening `5260123456`, tanpa surat → klik Process to Request Payment | Ditolak: upload surat permintaan dari client | |
| PPH-09 | Upload surat → Process | "Dokumen pengembalian **KPPH/2026/11/0001** masuk Request Payment". Status **Proses Request Payment**; belum ada jurnal di Accounting → Jurnal | |
| PPH-10 | Status INV 284 di tab PPh Tidak Dipotong → gear **Buka Request Payment** | Tahap "Di Request Payment". Halaman Request Payment: KPPH/2026/11/0001 kategori **Pengembalian PPh**, 800.000, penerima PT ARWANA CITRAMULIA TBK, rekening BCA 5260123456, lampiran surat permintaan; filter Kategori Pengembalian PPh = 1 baris | |
| PPH-10a | Request Payment: centang KPPH → **Atur Pembayaran** → ajukan | Terbentuk PGJ. Di PPh Invoice tahap berubah "Approval PGJ-…" | |
| PPH-10b | Approval Request Payment: buka PGJ → Simpan Keputusan (setuju) | Di PPh Invoice tahap "Menunggu Payment (PGJ-…)" | |
| PPH-10c | Payment List → pilih transfer → Proses → Form Payment | Jurnal baris bank "(pilih bank)" sampai Bank dipilih. Pilih BCA + upload bukti: Dr **PPh 23 Disetor 1106-01-02** 800.000 / Cr Bank STM 1101-02-09. Submit | |
| PPH-11 | Kembali ke PPh Invoice → tab **PPh Dipotong** (juga Accounting → Jurnal) | INV 284 keluar dari tab Tidak Dipotong, sekarang **Menunggu Bukti Potong**, label "Dikembalikan KPPH/2026/11/0001", ada aksi Input Bukti Potong | |
| PPH-12 | INV 284 → Input Bukti Potong: nilai `750000` | NPWP terisi dari master customer. Nilai beda dengan sistem (800.000) ditolak | |
| PPH-13 | No `2600211001`, tanggal 26-Nov-2026, nilai 800.000, file (tidak ada field Masa Pajak) → Proses | Jurnal tanggal 26 November 2026: Dr PPh 23 Disetor 1106-01-02 / Cr Piutang 1102-01-01 800.000. Status **Selesai** | |
| PPH-14 | INV 281 → Bukti Potong dengan No `2600211001` | Ditolak: nomor sudah dipakai customer lain | |
| PPH-15 | INV 198 (label **Sebelum berlaku**, umur merah > 30 hari) → Input Bukti Potong | Preview "Tanpa jurnal"; tersimpan Selesai | |
| PPH-16 | Tab **Bukti Potong Diterima** | 3 record (INV 150, 284, 198), total 1.220.000. Kolom No. Bukti Potong, Tanggal, Pemotong, NPWP/NITKU, No. Invoice, Company, Jenis, Nilai PPh | |
| PPH-17 | Gear INV 284 → **View** | Record: data bukti potong, invoice, penjualan & tarif 2%, penerimaan, **pengembalian ke client KPPH/…**, file, jurnal | |
| PPH-18 | Gear INV 284 → **Print** | Dialog print berisi record yang sama + "Dicetak 27 November 2026", lalu halaman berikutnya **Lampiran: File Bukti Potong** (bupot-arwana.pdf). Di sistem: isi file PDF/gambar ikut tercetak; di mockup hanya kotak penanda | |
| PPH-19 | Search `asahi` di Bukti Potong Diterima; filter Company **SUSTAIN** di PPh Dipotong | 1 baris / 1 baris (INV 245) | |
| PPH-20 | Filter Status Selesai → gear INV 284 → **Detail** | Riwayat: Penerimaan, Pengembalian ke Client KPPH (rekening, surat, tanggal bayar), Bukti Potong, masing-masing dengan jurnal | |
| PPH-21 | 7.960.000 → KAI → Dipotong Client → INV 297 (UMKM) | Kontrol 0 (8.000.000 − PPh Final 40.000). PPh Final tetap di piutang. Pembanding sekarang: Dr Beban PPh Final saat penerimaan | |
| PPH-22 | INV 297 (label PPh Final 0,5%) → Input Bukti Potong nilai 40.000 | Jurnal Dr **PPh Final Disetor 1106-01-05** / Cr Piutang 40.000 | |
| PPH-23 | Penerimaan dari **Unlocated UNL-2611-0004** 13.080.000 → INDO KORDSA → Dipotong Client → INV 299 | Jurnal Dr **Penerimaan Belum Teridentifikasi** (COA menyusul) / Cr Piutang 13.080.000; PPh 240.000 masuk PPh Invoice | |
| PPH-24 | 5.550.000 → KAI → **Tidak Ada PPh** → INV 290 → Save | Tagihan penuh, jurnal seperti sekarang, tidak masuk PPh Invoice | |
| PPH-25 | Tab **History (Penerimaan PPH 23 lama)** | 3 data lama read-only (Nilai PPh, Nilai Setor, Tanggal Setor, Bukti Setor). Menu Penerimaan PPH 23 tidak ada di sidebar | |
| PPH-26 | Tab PPh Tidak Dipotong | Data contoh INV 233 (HEINZ) **Belum Diproses** | |
| PPH-27 | Accounting → **Jurnal** setelah PPH-03..24 | 5 jurnal penerimaan, 1 jurnal Payment pengembalian (No Payment BK-… dari Request Payment), 2 jurnal bukti potong (INV 198 sebelum berlaku tanpa jurnal) | |
