# Publikasi invoicing ke development

User meminta commit dan push perubahan invoicing ke GitHub branch `development` pada 18 Oktober 2026. Remote: `https://github.com/alief572/sendigs_finance.git`. Parent sebelum publikasi: `28fefd9f4ed7f8a920089ab241fa337d0e4aeb91`; HEAD dan origin/development identik sesudah fetch. Tidak mengubah branch main atau menggunakan force-push.

Scope mengikuti runtime/migration/tests pada `invoicing-consulting-manifest.json`, ditambah README invoicing/penerimaan dan laporan khusus invoicing. Mencakup kalkulasi versi3, mapping/snapshot COA, penerimaan PPh aktual, posting, report piutang terkait, UI dan pengembalian tombol aksi ke ikon berwarna original.

Perubahan task lain tetap di working tree, termasuk Jurnal All, expense, navigation, audit trail dan request payment. Shared `implementation-plan.md`/`task-progress.md` tetap lokal karena berisi perubahan banyak task; checkpoint invoicing tetap dipelihara di sana. Test Legacy_posting Jurnal All dicatat sebagai referensi validasi lokal, tidak diterbitkan sebagai bagian task ini. Konfigurasi database, backup SQL, data unggahan, scratch/runtime, tool perbaikan DB lokal dan file pribadi tidak disertakan.

Validasi implementasi sebelum publikasi telah dicatat pada laporan v3, UI, dan prasyarat posting lokal. Pemeriksaan publikasi memverifikasi hash manifest, file staging, whitespace, serta mengekspor source staging untuk menjalankan kalkulasi/parity/render/client tests tanpa bergantung pada perubahan modul lain di working tree. Commit yang dipublikasikan tidak mengubah database mana pun.

Hasil pemeriksaan staging: allowlist40 file tepat; snapshot80 file mencakup source baseline dan file commit yang diperlukan tes. Render203, client form38, client list28, kalkulasi72, parity35 (total376) PASS; lint25 file PHP dan staged whitespace PASS. Sisa index.lock kosong dari pagi dibersihkan setelah memastikan tidak ada operasi Git yang aktif selain daemon fsmonitor; tidak menghapus index atau perubahan source.

Rilis aplikasi di lingkungan lain tetap memerlukan migration003, mapping sembilan akun berdasarkan master accounting yang benar, dan prasyarat engine/presisi/kolom/akses lintas database sebagaimana laporan posting lokal. Akun WAPU dan perubahan engine yang sudah dilakukan pada Docker lokal merupakan perubahan database, bukan bagian yang otomatis berpindah melalui Git. Tidak menjalankan deployment atau tes browser.

## Publikasi lanjutan search sidebar dan pemilik jurnal

User meminta push dua perbaikan tersebut ke development. Scope16file: search global (header/footer/CSS/JS/tests/report/manifest), label Company SPK + owner canonical/controller/service/view/tests/report/manifest. Hash source cocok dengan bukti sebelumnya: native341, render owner208, client sidebar55 dan render sidebar60 PASS. Runtime/lokalDB/backup/task lain tetap tidak disertakan. Perubahan tabel compact yang diminta berikutnya tidak termasuk publikasi lanjutan ini.
