// Jalankan: node application/modules/follow_up_piutang/tests/Bukti_client_test.js
// Memeriksa nama file tempel (MP-FR-20), ukuran huruf Follow Up (MP-NFR-10), dan skeleton (MP-NFR-11) langsung dari view.
const fs = require('fs'), path = require('path'), assert = require('assert');
const read = (f) => fs.readFileSync(path.join(__dirname, '../views/' + f), 'utf8').split('\r\n').join('\n'); // view bisa CRLF di Windows
const view = read('index.php');
const plan = read('plan_bayar.php');

const m = view.match(/function pasteName\(fl, n\) \{[\s\S]*?\n    \}\n/);
assert(m, 'fungsi pasteName ada');
const FixedDate = class extends Date { constructor() { super(2026, 10, 23, 9, 5, 7); } };
const pasteName = new Function('Date', m[0] + '; return pasteName;')(FixedDate);
assert.strictEqual(pasteName({type: 'image/png'}, 1), 'tempel-20261123-0905-1.png');
assert.strictEqual(pasteName({type: 'image/jpeg'}, 3), 'tempel-20261123-0905-3.jpg');

assert(/\.fu-page \{ font-size: 16px; \}/.test(view), 'teks dasar 16px');
assert(/\.fu-page \.fu-tbl th \{ font-size: 15px; \}/.test(view), 'header tabel 15px');
assert(/\.fu-page \.fu-pill \{ font-size: 13px; \}/.test(view), 'label status 13px');
assert(!plan.includes('MP-NFR-10'), 'menu lain (Plan Bayar) tidak berubah');
assert(view.includes('id="fu-skel"') && view.includes('prefers-reduced-motion'), 'skeleton + reduced motion');
assert(/preXhr\.dt/.test(view) && /xhr\.dt error\.dt/.test(view), 'skeleton hidup saat muat dan mati saat selesai/gagal');
assert(view.includes("'.bukti-open'") && view.includes('dataTransfer'), 'seret file ke kotak bukti');
// Fase 6 (sisa gap mockup): usulan jadwal FU berikutnya memakai jarak yang sama dengan Piutang_aging::FU_OFFSET.
const fnSrc = (name) => { const x = view.match(new RegExp('function ' + name + '\\([^)]*\\) \\{[^\\n]*\\}')); assert(x, 'fungsi ' + name + ' ada'); return x[0]; };
const helpers = new Function(fnSrc('addDays') + '; var GAP = [3, 7, 14, 14]; ' + fnSrc('gapOf') + '; return {addDays: addDays, gapOf: gapOf};')();
assert.strictEqual(helpers.addDays('2026-11-25', 7), '2026-12-02', 'FU2 = FU1 dilakukan + 7');
assert.strictEqual(helpers.addDays('2026-12-25', 14), '2027-01-08', 'lintas tahun');
assert.strictEqual(helpers.addDays('2027-02-27', 3), '2027-03-02', 'lintas Februari');
assert.deepStrictEqual([1, 2, 3, 4, 5, 9].map(helpers.gapOf), [3, 7, 14, 14, 14, 14], 'jarak FU 1-4 dan tambahan');
assert(view.includes("inp('fu' + next + '_plan', sug, 'date'"), 'jadwal FU n+1 ikut terkirim sebagai fu{n+1}_plan');
assert(view.includes('if (!akt || bm) {'), 'status INVOICE BERMASALAH menyembunyikan jadwal berikutnya');
assert(view.includes("class=\"fu-bmwarn\" hidden") && view.includes("$a.children('.fu-bmwarn').prop('hidden', !bm)"), 'peringatan bermasalah saat status dipilih');
assert(view.includes('fu-extra-cancel') && view.includes('fu-extra-del') && view.includes('fu-add-next'), 'FU tambahan: tambah, batal, hapus');
assert(view.includes("inp('no_faktur', r.no_faktur, 'text', ro || !!L.faktur"), 'no faktur bisa dikoreksi di tahap 3 sampai FU 1');
assert(view.includes('location.search') && view.includes('resolveTab'), 'deep link ?tab=&open=');
assert(view.includes('lastSub[g] || GROUPS[g][0]'), 'tab utama kembali ke sub-tab terakhir');
assert(/var lb = lt && !\(d\.bukti_boleh && !ro\)/.test(view), 'bukti invoice lama bisa dilengkapi walau data terima terkunci');
assert(plan.includes('id="f-bulan"') && plan.includes("setBulan('all'); load();"), 'Dashboard: dropdown Bulan, ganti tahun = All');
console.log('Bukti/Font/Skeleton client PASS');
