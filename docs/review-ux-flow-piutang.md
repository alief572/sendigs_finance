# Review UX & Flow: Monitoring Piutang, Follow Up Piutang, Plan Bayar

Tanggal: 2026-11-09. Metode: baca spec dan kode, tanpa uji browser/DB.
Acuan spec: `specs/docs/review-monitoring-piutang/` (`fr-nfr.md`, `use-case.md`, `mockup/index.html`).
Selisih dengan spec dihitung sebagai gap, termasuk yang mungkin disengaja (ditandai "perlu konfirmasi").

Severity: **T** tinggi (flow/aturan beda dari spec), **S** sedang (UX mengganggu), **R** rendah (polish).

## 1. Monitoring Piutang (`application/modules/monitoring_piutang`)

| ID | Spec | Implementasi | Status | Sev |
|---|---|---|---|---|
| MP-FR-01/02/03 | Kartu Total/Lancar/Potensi/Macet, Belum Diterima abu-abu, total keseluruhan, klik kartu = filter, klik lagi = lepas | `views/index.php:23-28`, toggle di JS | Sesuai | - |
| MP-FR-07 | Filter Customer (dropdown/chip yang bisa dihapus) | Input `readonly` (`index.php:17`), hanya terisi dari klik di tab Rekap. Tidak bisa dipilih atau diketik langsung. | **Gap** | S |
| MP-FR-06 | Kolom list bisa diurutkan (mockup: no, cust, nilai, terbayar, sisa, umur) | `<th>` biasa tanpa sort (`index.php:36`) | **Gap** | S |
| MP-FR-16 | Surat Konfirmasi dengan field edit No. surat, Ditujukan, Kota, Penandatangan, Jabatan | Toolbar edit ada di halaman surat (`Surat_konfirmasi.php:147-148`) | Sesuai | - |
| MP-FR-12 | Excel di tiap kartu + tombol kategori aktif | Ada (`index.php:34`) | Sesuai | - |
| UX | Label tombol Excel saat kategori `all` | Tampil "Total Piutang" (`NAMA.all`, `index.php:52`), padahal itu "semua" | Catatan | R |
| UX | Loading dan error | `load()` tanpa indikator, tidak ada `.fail`, error via `alert()` (`index.php:70`) | **Gap** | S |
| UX | Aksesibilitas | Kartu klik berupa `div` tanpa role/tabindex/keyboard (`:23-28`); tab `<a href="#">` tanpa `role=tab` (`:31-32`); modal tanpa `aria-labelledby` (`:43`) | **Gap** | S |
| UX | Pencarian | Memakai `keyup` (`:103`), paste via mouse tidak memicu | Catatan | R |
| UX | Tombol Customer filter | Tidak ada cara menghapus customer selain "Reset filter" | Catatan | R |

## 2. Follow Up Piutang (`application/modules/follow_up_piutang`)

| ID | Spec | Implementasi | Status | Sev |
|---|---|---|---|---|
| MP-FR-08 | Tab Semua, 1-4, FU Terlambat, Invoice Close, badge jumlah, filter, "Baru · dari" | Ada, tab sudah `role=tablist` (`views/index.php:85-92`) | Sesuai | - |
| MP-FR-09 | Terima hanya tanggal + keterangan | Ditambah **upload bukti terima wajib** (PDF/JPG/PNG, 5 MB), `index.php:281-290,460` dan controller `save` | **Gap** (perlu konfirmasi) | T |
| MP-FR-09 | No resi opsional, boleh diisi belakangan | Label "wajib bila via JNE" (`index.php:332`); hint di `:334` menyebut boleh diisi belakangan, jadi pesan bertentangan | **Gap** (perlu konfirmasi) | T |
| MP-FR-10/10b | Flow kirim, terima, PIC, FU1-4, FU tambahan, plan; jadwal otomatis; kunci per step | Sesuai (`Piutang_aging` `default_fu_plan`, `lock_saved`, `validate_ar`) | Sesuai | - |
| MP-FR-10a | Plan bayar mengunci seluruh form, ada peringatan | Hanya hint kuning, tanpa dialog konfirmasi sebelum Simpan | Catatan | S |
| MP-FR-11 | Close hanya bila plan ada dan sisa 0, keterangan wajib | Sesuai (`close_block_reason`, cek di UPDATE) | Sesuai | - |
| UX | Alasan Close nonaktif | Hanya atribut `title` (`index.php:221`), tak terbaca di layar sentuh/keyboard | **Gap** | S |
| UX | Filter Customer | "nama persis" tanpa dropdown/autocomplete (`index.php:98`), mudah nol hasil | **Gap** | S |
| UX | Pesan | `#fu-msg` ber-`role=status` dipakai juga untuk error (`:122,183`); pesan sukses tanpa tombol tutup/timeout | Catatan | R |
| UX | Riwayat perubahan | `created_by` ditampilkan apa adanya (`:478`), `data_json` diringkas | Catatan (cek apakah nama atau ID) | R |
| UX | Error filter `data()` | Server membalas 200 + field `error`; `errMode='none'` (`:227`) hanya menangani error ajax, pesan filter tidak valid mungkin tak tampil | Perlu uji | S |
| Dugaan awal | Input FU hilang saat form dibangun ulang (`:427-431`) | `serializeArray` seluruh form digabung ke `cur.row` sebelum `buildForm`, jadi **tidak terbukti** hilang | Ditolak | - |
| Dugaan awal | Input file hilang setelah rebuild | Nama file dijaga lewat `picked`, tetapi `<input type=file>` terlihat kosong; hint teks menutupi | Catatan | R |

## 3. Plan Bayar (`follow_up_piutang/plan_bayar`)

| ID | Spec | Implementasi | Status | Sev |
|---|---|---|---|---|
| MP-FR-14 | Kartu All + 12 bulan, Bulan ini/Lewat, klik kartu memfilter, tabel Plan dan Realisasi, Janji vs Bayar | Sesuai (`views/plan_bayar.php:107-112`, `aria-pressed`) | Sesuai | - |
| MP-FR-15 | Export Ringkasan/Rincian/Realisasi, hasil mengikuti tampilan | `export` (`Plan_bayar.php:~149`) tidak membaca kata cari DataTables, layar memakainya | **Gap** | S |
| UX | Loading saat ganti tahun/company | Teks "Memuat..." hanya di awal (`plan_bayar.php:69`); kartu lama tetap sampai respons | Catatan | R |
| UX | Performa | `load_data()` dipanggil di `data`, `plan_data`, `real_data` (`Plan_bayar.php:34,59,96`), berulang per filter | Catatan | R |
| UX | Tabel | Tanpa `<caption>` (`:73,82`) | Catatan | R |
| Input | Plan bayar diisi di Follow Up, bukan di dashboard | Sesuai spec | Sesuai | - |

## Perbaikan gap sedang (9 Nov 2026)

Sudah diperbaiki (belum diuji di browser; PHP belum di-lint karena `php` tidak ada di PATH, JS inline lolos `node --check`):
- Monitoring `views/index.php`: filter Customer jadi input dengan datalist (event `change`), kolom list bisa diurutkan (klik header, `aria-sort`), loading (opacity) dan banner error `#mp-err` menggantikan `alert()` serta `.fail`, kartu `role=button` + `tabindex` + Enter/Spasi, modal `aria-labelledby`, pencarian pakai `input`, label export "Semua".
- Follow Up `views/index.php` dan `Follow_up_piutang::data()`: filter Customer pakai datalist dari respons `customers`; alasan Close nonaktif tampil sebagai teks; konfirmasi `confirm()` saat plan bayar pertama kali diisi; error filter (200 + `error`) ditampilkan di banner.
- Plan Bayar `Plan_bayar.php` + `views/plan_bayar.php`: export membawa `cari_plan` dan `cari_real` dari kotak cari tiap tabel lewat helper `search_rows()` yang sama dengan tampilan.

Risiko: sheet Ringkasan export "All" mungkin masih dihitung dari data tanpa kata cari (belum dicek).

## Ringkasan prioritas
1. **T (konfirmasi dulu):** upload bukti terima wajib dan no resi wajib JNE, keduanya tidak ada di spec. Bila memang permintaan baru, perbarui spec; bila tidak, sesuaikan kode.
2. **S:** filter Customer Monitoring (readonly), tidak ada sort kolom, loading/error handling, aksesibilitas kartu; FU filter Customer tanpa autocomplete, alasan Close hanya `title`, tanpa konfirmasi sebelum plan bayar mengunci; export Plan Bayar abaikan pencarian.
3. **R:** polish label, `keyup`, caption, dan lainnya.

## Keterbatasan
- Tidak diuji di browser maupun DB (sesuai kesepakatan), jadi temuan "Perlu uji" belum terbukti.
- `specs/docs/statuses.md` kosong. Mockup `specs/docs/mockup-monitoring-piutang.html` tidak dibandingkan dengan versi review.
- Tidak ada kode aplikasi yang diubah.
