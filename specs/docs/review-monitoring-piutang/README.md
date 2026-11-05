# Paket Review — Monitoring Piutang, Follow Up Piutang, Dashboard Plan Bayar

Versi 5 November 2026. Semua isi folder ini dicocokkan dengan kode per tanggal itu.

| File | Isi | Cara buka |
|---|---|---|
| `mockup/index.html` | Mockup klik-able: Invoicing (hulu), Follow Up Piutang, Monitoring Piutang, Dashboard Plan Bayar. "Hari ini" di mockup = 2 Nov 2026. Data contoh, tidak tersimpan (F5 / "Reset data" untuk kembali). Garis putus-putus ungu = bagian baru / berubah. | Buka dengan Chrome / Edge. Folder `mockup/lib` harus ikut (library Excel dan kop surat), tidak perlu internet. |
| `fr-nfr.md` | **FR & NFR (satu dokumen)** dari Invoicing, Penerimaan Uang, Follow Up Piutang, Monitoring Piutang, sampai Dashboard Plan Bayar, versi 5 Nov 2026 (dicocokkan dengan kode): aktor, kriteria terima per fitur, kolom layar dan Excel, kunci step, aturan data, tabel, urutan pemasangan, catatan terbuka, perintah test. | Editor Markdown / VS Code / GitHub. |
| `contoh-export-dashboard-plan-bayar.xlsx` | Contoh hasil Export Excel Dashboard Plan Bayar. | Excel. |
| `use-case.md` | Tabel use case: aktor, prasyarat, alur utama, alur alternatif, hasil akhir, dan acuan FR. | Editor Markdown / VS Code / GitHub. |
| `flow-process.drawio` | Alur proses dari invoice sampai close: lajur Customer, Finance (Billing), Finance AR, Finance Penerimaan, Sistem, Monitoring & Dashboard. | draw.io (desktop atau app.diagrams.net). |
| `erd.drawio` | ERD 18 tabel: hulu Penjualan & Plan Tagih, Invoicing, Jurnal, Penerimaan Uang, dan tabel Follow Up / Monitoring (hijau = tabel baru). | draw.io. |

**Cakupan.** Flow process dan ERD sengaja ikut memuat Invoicing dan Penerimaan Uang, karena Monitoring dan Dashboard membaca data dari sana (nilai invoice, sisa piutang, tanggal bayar).

**Belum diverifikasi.** Kedua diagram belum pernah dirender di laptop ini (draw.io Desktop tidak terpasang). Posisinya sudah dicek dengan skrip (tidak ada kotak bertumpuk atau garis menembus tabel), tapi mohon dicek tampilannya saat dibuka.

**Sumber asli** (folder ini salinan untuk review; perubahan dibuat di sumber lalu disalin ulang):

| Salinan | Sumber |
|---|---|
| `mockup/` | `docs/uji-coba-monitoring-piutang/` (versi kerja: `docs/mockup-monitoring-piutang.html`) |
| `flow-process.drawio` | `docs/flow-invoice-sampai-monitoring-piutang.drawio` |
| `erd.drawio` | `docs/erd-invoice-sampai-monitoring-piutang.drawio` |
| `fr-nfr.md` | `docs/fr-nfr-monitoring-piutang.md` (link di dalamnya diarahkan ke file di folder ini) |
| `contoh-export-dashboard-plan-bayar.xlsx` | `docs/contoh-export-dashboard-plan-bayar.xlsx` |
| `use-case.md` | Diturunkan dari `fr-nfr.md` |

**Hal yang masih menunggu keputusan** (rinciannya di `fr-nfr.md` bagian "Catatan / masih terbuka"):
1. **Write-off** piutang tak tertagih ditunda: invoice yang tidak akan lunas tetap Macet dan tidak bisa di-close.
2. **Tanggal go-live** Follow Up (`application/config/piutang.php`) diisi saat modul mulai dipakai.
3. **Invoice internal** antar-company diputuskan nanti, saat fiturnya dibuat di Invoicing.
4. Surat konfirmasi baru punya profil kop STM.
5. Belum pernah dicoba di database sungguhan; semua Export Excel server dan migrasi 017 (isi ulang nilai plan) perlu dicek di server dev.

Sudah diputuskan 5 Nov (INV-R-09): invoice non konsultasi ikut dipantau; close di Invoicing juga wajib lunas, selisih harus 0 persis; card Belum Diterima + total keseluruhan; realisasi = semua penerimaan; tab 1–4 + label Baru dipertahankan; nilai plan lama diisi ulang dari riwayat; tanggal plan bayar bebas.

Sudah diputuskan 5 Nov (INV-R-08): via JNE / Driver saja; close hanya bila lunas + ada plan bayar; kunci saat step berikutnya disimpan; plan Dashboard tetap menghitung invoice close; plan bayar yang lewat tetap terkunci; invoice lama yang lunas tidak tampil di Follow Up.
