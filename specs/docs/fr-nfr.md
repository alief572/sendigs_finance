# FR & NFR — Pembayaran Periodik

Cakupan: Budget Pembayaran Periodik, Approval Perubahan Budget, Pengajuan Pembayaran Periodik, dan Approval Pengajuan Periodik, sampai item diserahkan ke Request Payment.

Terakhir diperbarui: 2026-10-29.

**Sumber**
- Permintaan user R-01 s/d R-09 di [`requests.md`](requests.md).
- Mockup yang sudah disetujui user: [`mockup-periodik-per-item.html`](mockup-periodik-per-item.html), mengikuti revisi user di `input/Periodik.html`.
- Alur proses: [`flow-pengajuan-periodik.drawio`](flow-pengajuan-periodik.drawio).
- Bahan dari klien: `input/Modul Budget Periodik + Pengajuan Periodik + Approval Periodik.html` dan `input/Contoh Stepper.png`.

**Status implementasi** (dicek terhadap kode di branch `development` pada 2026-10-29):

| Status | Arti |
|---|---|
| **Ada** | Sudah ada di kode |
| **Sebagian** | Sebagian sudah ada, sisanya disebut di kolom catatan |
| **Belum** | Baru ada di mockup |
| **Belum dicek** | Tidak bisa dipastikan dari kode |

## Aktor

| Aktor | Peran |
|---|---|
| Admin Departemen | Menyusun budget periodik departemennya dan membuat pengajuan pembayaran. |
| Direktur | Approver **Approval Perubahan Budget** dan **Approval Pengajuan Periodik** (R-09). |
| Finance | Memproses item yang sudah approved di Request Payment. Di luar cakupan dokumen ini. |

## Functional Requirements

### Modul Budget Pembayaran Periodik

| ID | Aktor | Kebutuhan | Kriteria penerimaan | Sumber | Status |
|---|---|---|---|---|---|
| FR-01 | Admin Departemen | Melihat daftar budget per departemen. | Index menampilkan departemen yang sudah punya budget: jumlah item aktif, total budget per bulan, total budget per tahun, dan status (Aktif / Menunggu approval BG-…). Tersedia aksi Lihat (rincian item dan riwayat BG) dan Edit. | R-05 | **Sebagian**: index per departemen sudah ada; kolom ringkasan, status BG, dan riwayat belum. |
| FR-02 | Admin Departemen | Menambah budget untuk sebuah departemen. | Klik + New, lalu pilih departemen dari dropdown yang bisa dicari. Departemen yang sudah punya budget membuka form yang sama berisi item yang ada. Per item diisi: Post Pengeluaran (COA), Nama Barang/Jasa, Bulanan/Tahunan, Jadwal Pembayaran, Perkiraan Biaya, dan Masa berlaku (Selalu, atau Terbatas n kali). **Tidak ada kolom Baseline.** Hasilnya diajukan sebagai perubahan budget (FR-03). | R-03, R-05, R-06 | **Sebagian**: form dan dropdown departemen sudah ada; Masa berlaku belum; kolom baseline/keterangan masih ada di form. |
| FR-03 | Admin Departemen | Mengajukan perubahan budget: item baru, ubah nominal atau masa berlaku, aktif/nonaktif. | Simpan membuat satu nomor perubahan **BG-YYMM-NNN** berstatus menunggu, dan master **tidak berubah** sampai disetujui. Selama departemen punya BG yang menunggu, form departemen itu terkunci dan menampilkan tautan ke Approval Perubahan Budget. Validasi: nama wajib diisi, biaya > 0, masa berlaku Terbatas tidak boleh kurang dari jumlah yang sudah terpakai. | R-03 | **Belum**: `budget_periodik` masih menyimpan langsung ke master. |
| FR-04 | Direktur | Memutuskan perubahan budget. | Approval Perubahan Budget menampilkan tab Menunggu approval dan Record approval. Dari index: Lihat (sebelum/sesudah) atau Proses approval. Keputusan diambil per baris perubahan (Approve/Reject); reject wajib diberi alasan. Perubahan yang disetujui langsung berlaku di master dan di form pengajuan. Keputusan tidak bisa diubah setelah disimpan. | R-03, R-09 | **Belum**: menu Approval Perubahan Budget belum ada. |

### Modul Pengajuan Pembayaran Periodik

| ID | Aktor | Kebutuhan | Kriteria penerimaan | Sumber | Status |
|---|---|---|---|---|---|
| FR-05 | Admin Departemen | Membuat pengajuan pembayaran periodik. | Klik + New, lalu pilih departemen. Departemen tanpa budget tidak bisa dipilih. Form hanya menampilkan item budget yang aktif dan masa berlakunya belum habis. Item yang sudah diajukan di periode yang sama (bulan untuk Bulanan, tahun untuk Tahunan) dan belum ditolak tampil terkunci beserta nomor itemnya. Per item diisi: perkiraan biaya, keterangan, dan dokumen lampiran. Simpan membuat batch **BP-YYMM-NNN**, dan setiap item yang diisi mendapat nomor **PI-YYYY-NNNNN** dengan status Waiting. Item yang ditolak dan ditutup (FR-12) tidak muncul lagi di form untuk periode itu. | R-01, R-05, R-10 | **Sebagian**: nomor batch, cek duplikat per periode, dan lampiran sudah ada; nomor item masih `PERIODIK-YYYY-NNNNN` (lihat NFR-01); penyaringan item nonaktif/masa habis belum (bergantung FR-02). |
| FR-06 | Admin Departemen | Mengubah atau menghapus batch yang belum diputuskan. | Edit dan Hapus hanya tersedia untuk batch yang semua itemnya masih Waiting. Nomor item lama tetap; item baru mendapat nomor baru. | R-01 | **Ada** (simpan ditolak jika batch sudah diputuskan). |
| FR-07 | Admin Departemen | Memantau pengajuan tanpa tercampur dengan yang sudah selesai. | Index punya tab **Menunggu approval** (hanya batch yang belum diputuskan) dan **Sudah diputuskan** (batch Approved, Partially Approved, Rejected). Setelah approver menyimpan keputusan, batch otomatis pindah ke Sudah diputuskan. Kedua tab bisa ditampilkan Per Batch atau Per Item. Tab Sudah diputuskan punya filter status batch atau status item. | R-02, R-07 | **Sebagian**: tampilan Per Batch/Per Item sudah ada; pemisahan Menunggu/Sudah diputuskan belum. |
| FR-08 | Admin Departemen | Membaca progres setiap item. | Kolom Progres berupa stepper dengan pola `input/Contoh Stepper.png`: garis 4 titik (Diajukan → Approval Pengajuan → Diajukan Finance → Approval Finance), judul langkah saat ini, badge status, dan satu baris info (alasan reject, nomor revisi atau "Ditutup, tidak diajukan ulang", tanggal, atau no. pengajuan Finance). Warna: hijau = selesai, kuning = posisi sekarang, merah = ditolak. Stepper tampil di Per Item (kedua tab) dan di Lihat batch. | R-08 | **Belum**: saat ini status masih berupa badge dan kolom Status Request Payment. |
| FR-09 | Admin Departemen | Mengajukan ulang item yang ditolak. | Item yang ditolak approver bisa diajukan lagi di batch baru. Item baru mencatat "Revisi dari PI-…", dan item lama mencatat "Diajukan ulang sebagai …". | R-01 | **Sebagian**: pengajuan ulang item ditolak sudah diizinkan; tautan revisi **belum dicek** di kode. |
| FR-12 | Admin Departemen | Menutup item yang ditolak tanpa mengajukan ulang, misalnya tagihannya batal atau sudah dibayar lewat jalur lain. | Di form pengajuan, item yang ditolak dan belum diajukan ulang punya tombol **Tidak diajukan ulang**. Alasan wajib diisi; selama kosong, dialog tetap terbuka. Setelah ditutup, item tidak muncul lagi di form untuk periode itu, dan stepper-nya mencatat "Ditutup, tidak diajukan ulang (alasan)". Isian item lain di form tidak hilang. | R-10 | **Belum**. |

### Modul Approval Pengajuan Periodik

| ID | Aktor | Kebutuhan | Kriteria penerimaan | Sumber | Status |
|---|---|---|---|---|---|
| FR-10 | Direktur | Memutuskan pengajuan per item. | Dari index (tab Menunggu approval / Record approval), buka Lihat atau Proses approval. Semua item default Approve. Reject wajib diberi alasan, per item atau lewat alasan reject umum. Ringkasan menampilkan status batch yang akan terbentuk (Approved / Partially Approved / Rejected) sebelum disimpan. Keputusan tidak bisa diubah setelah disimpan. | R-01, R-05, R-09 | **Sebagian**: keputusan per item dan wajib alasan sudah ada. Approver Direktur diatur lewat hak akses `Approval_Pengajuan_Pembayaran_Rutin`; penugasan ke Direktur **belum dicek**. |
| FR-11 | Direktur (pemicu) | Menyerahkan item yang disetujui ke Finance. | Setiap item approved masuk Request Payment sebagai satu baris per nomor item. Item yang ditolak tidak ikut. Item yang ditolak Finance di Approval Request Payment tetap di Request Payment untuk diperbaiki Finance dan tidak kembali ke pengaju. | R-01 | **Ada** (lewat `v_request_payment`). |

## Non-Functional Requirements

| ID | Berlaku untuk | Kebutuhan | Kriteria terukur | Sumber | Status |
|---|---|---|---|---|---|
| NFR-01 | Pengajuan | Penomoran unik dan berurutan. | No. batch `BP-YYMM-NNN` urut mulai 001 setiap bulan. No. item `PI-YYYY-NNNNN` unik di seluruh tabel (unique key `uq_no_item`), dengan seri sendiri. Prefix `PI` wajib berbeda dari nomor lama `PERIODIK-YYYY-NNNNN`, yang di sistem sekarang adalah nomor batch, supaya kedua jenis nomor tidak tertukar. Dua penyimpanan bersamaan tidak boleh menghasilkan nomor yang sama. | R-01, R-10 | **Sebagian**: nomor batch dan unique key sudah ada; nomor item masih diambil dari `format_nonpo` (`PERIODIK-YYYY-NNNNN`), belum `PI-`. |
| NFR-02 | Request Payment, jurnal, record, audit | Kompatibel dengan data sebelum fitur per item. | Modul hilir menemukan pengajuan baik dari nomor item maupun dari nomor batch lama (`no_item` NULL), dengan 0 data lama yang tidak terbaca. Setiap perubahan `v_request_payment` tetap mempertahankan kedua cabang periodik. | R-01 | **Ada** (`periodik_helper.php`, migrasi `001_periodik_per_item.sql`). |
| NFR-03 | Approval Perubahan Budget, Approval Pengajuan | Jejak keputusan lengkap. | 100% keputusan menyimpan approver dan waktu. 100% reject punya alasan yang tidak kosong. | R-01, R-03 | **Sebagian**: ada untuk pengajuan (`item_reject_ket`, `item_approved_by`, `item_approved_date`); untuk budget bergantung FR-04. |
| NFR-04 | Approval Perubahan Budget, Approval Pengajuan | Keputusan bersifat final. | Upaya menyimpan keputusan kedua untuk batch atau BG yang sudah diputuskan ditolak sistem. | R-01, R-03 | **Sebagian**: ada untuk pengajuan; untuk budget bergantung FR-04. |
| NFR-05 | Semua layar di cakupan | Konsisten dengan tampilan SENDIGS yang ada. | Layar memakai komponen, menu, dan pola yang sama dengan mockup yang disetujui: tombol + New dengan dropdown departemen, tombol Kembali, action bar menempel, konfirmasi sebelum simpan/putuskan/hapus, dan peringatan saat meninggalkan isian yang belum disimpan. | R-05 | **Belum dicek** per layar. |
| NFR-06 | Kolom Progres (FR-08) | Stepper ringkas dan tetap informatif. | Mengikuti `input/Contoh Stepper.png`; lebar stepper maksimal 380 px di dalam sel tabel; tidak ada info yang dobel dengan kolom lain di baris yang sama (no. item, tanggal keputusan). | R-08 | **Belum**. |

## Belum ditetapkan, perlu klarifikasi

Butir-butir ini sengaja tidak ditulis sebagai requirement karena belum pernah dinyatakan user:

1. **Lampiran dokumen di pengajuan**: wajib atau opsional? Di kode dan mockup saat ini opsional.
2. **Langkah "Dibayar / Paid" di stepper**: kode Request Payment membedakan "Disetujui Finance" dan "Paid" (ada tanggal bayar). Apakah stepper perlu langkah ke-5?
3. **Approval Perubahan Budget dan Approval Pengajuan hanya satu tingkat (Direktur saja)?** Atau ada approver lain sebelum Direktur?
4. **Batas waktu dan performa** (misalnya waktu muat index dengan jumlah batch tertentu) belum ditetapkan, sehingga belum ada NFR performa.
