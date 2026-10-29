# Requests

Append-only capture of raw functional requests. Not a task queue — a request only becomes
a workbook use case once confirmed as a genuine functional need (see `context/document-writer-only/workbook-conventions.md`).

## Pembayaran Periodik (Budget, Pengajuan, Approval)

Permintaan disampaikan user lewat sesi chat dengan Claude Code. Teks permintaan dikutip apa adanya; tanggal 2026-10-28 berasal dari catatan keputusan sesi sebelumnya (memori proyek), bukan kutipan langsung.

| ID | Tanggal | Permintaan | Diproses ke |
|---|---|---|---|
| R-01 | 2026-10-28 | Pengajuan Pembayaran Periodik dipecah per item supaya approver bisa approve/reject per item, bukan satu batch utuh. Pilihan user: kolom item di detail (batch tetap satu header). | FR-05, FR-09, FR-10, NFR-01 |
| R-02 | 2026-10-28 | Daftar pengajuan punya 2 tampilan: Per Batch (dengan view detail) dan Per Item (dengan kolom No. Batch). | FR-07 |
| R-03 | 2026-10-28 | Menu Approval dipisah: Approval Pengajuan Periodik, dan Approval Budget untuk perubahan master Budget Pembayaran Periodik (item baru, ubah nominal/masa, aktif/nonaktif; nomor BG-YYMM-NNN; perubahan berlaku setelah disetujui). | FR-02, FR-03, FR-04 |
| R-04 | 2026-10-29 | "buatkan flow process dari pengajuan pembayaran periodik ya, dari budget, approval budget, pengajuan, approval pengajuan. buat di draw io" | Bukan FR (dokumen): `docs/flow-pengajuan-periodik.drawio` |
| R-05 | 2026-10-29 | "coba buat mock up nya itu lengkap, dari index dulu lalu klik tambah budget, pilih department, isi item, isi harga, dsbnya. pas pengajuan juga. pas approval jg dari index, yang lengkap ya" | FR-01, FR-02, FR-05, FR-10; mockup `docs/mockup-periodik-per-item.html` |
| R-06 | 2026-10-29 | "di budget periodik gausah ada baseline" | FR-02 |
| R-07 | 2026-10-29 | "di index pengajuan periodik, apabila sudah di approve dan reject masuk ke record aja, sekarang kegabung semua antara approved, rejected, dan waiting approval. jadi berantakan." | FR-07 |
| R-08 | 2026-10-29 | "boleh ga buat progressnya menjadi tipe stepper" → "dibuat lebih kecil ya, jangan terlalu besar, menuhin tabel banget, lebih simple tapi informatif" → ikuti `input/Contoh Stepper.png` | FR-08, NFR-06 |
| R-09 | 2026-10-29 | "approval budget oleh direktur, approval pengajuan oleh direktur" | FR-04, FR-10 |
| R-10 | 2026-10-29 | "di input aku masukan HTML dengan nama "Periodik" nah ikutin kayak gitu ya, ada perubahan tadi". Isi perubahan di `input/Periodik.html`: nomor item PI-YYYY-NNNNN (bukan PERIODIK-, karena PERIODIK- adalah nomor batch lama), menu "Approval Perubahan Budget", tab "Sudah diputuskan", tombol "Tidak diajukan ulang" untuk item yang ditolak. | FR-05, FR-07, FR-08, FR-12, NFR-01 |
