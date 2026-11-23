'use strict';
// Smoke test mockup Monitoring Piutang / Follow Up Piutang (MP-R-15..18: font, status FU, no faktur, tab Semua Visual Control).
// Jalankan: node specs/docs/monitoring-piutang/smoke-test.js   (jsdom dari node_modules biasa atau env JSDOM_PATH)
const fs = require('fs');
const path = require('path');
const assert = require('assert');
let JSDOM;
try { ({JSDOM} = require(process.env.JSDOM_PATH || 'jsdom')); }
catch (e) { console.error('jsdom tidak ditemukan. Pasang: npm i jsdom, atau set JSDOM_PATH.'); process.exit(2); }

const html = fs.readFileSync(path.join(__dirname, 'mockup-monitoring-piutang.html'), 'utf8').replace(/<script src=[^>]*><\/script>/g, '').replace(/<link [^>]*>/g, '');
const XL = [];
const colNum = s => s.split('').reduce((a, c) => a * 26 + c.charCodeAt(0) - 64, 0) - 1;
const colStr = n => { let s = ''; for (n++; n > 0; n = Math.floor((n - 1) / 26)) s = String.fromCharCode(65 + (n - 1) % 26) + s; return s; };
const fakeXlsx = {utils: {
  book_new: () => ({sheets: []}),
  aoa_to_sheet: aoa => ({__aoa: aoa, '!ref': 'A1:' + colStr(Math.max(...aoa.map(r => r.length), 1) - 1) + aoa.length}),
  decode_range: () => ({s: {r: 0, c: 0}, e: {r: 0, c: 0}}),
  decode_col: colNum, encode_cell: ({r, c}) => colStr(c) + (r + 1),
  book_append_sheet: (wb, ws, name) => wb.sheets.push({ws, name})},
  writeFile: (wb, file) => XL.push({wb, file})};
const errors = [];
const dom = new JSDOM(html, {runScripts: 'dangerously', pretendToBeVisual: true,
  beforeParse(w){ w.SKELETON_MS = 0; w.scrollTo = () => {}; w.XLSX = fakeXlsx; w.print = () => {}; w.open = () => null; w.addEventListener('error', e => errors.push(e.message)); }});
const {window} = dom, doc = window.document;
const $ = s => doc.querySelector(s), qa = s => [...doc.querySelectorAll(s)];
const click = el => { assert.ok(el, 'elemen tidak ada'); el.dispatchEvent(new window.MouseEvent('click', {bubbles: true})); };
const setVal = (el, v, evs = ['input', 'change']) => { assert.ok(el, 'input tidak ada'); el.value = v; evs.forEach(ev => el.dispatchEvent(new window.Event(ev, {bubbles: true}))); };
const submit = () => $('#fuForm').dispatchEvent(new window.Event('submit', {bubbles: true, cancelable: true}));
const GRP = {'': 'mon', belum_kirim: 'proses', dikirim: 'proses', diterima: 'proses', plan_bayar: 'proses', lewat: 'hasil', masalah: 'hasil', close: 'hasil'};
// MP-R-31: tab anak hanya tampil di tab utamanya; buka tab utama dulu bila perlu
const tab = k => { if (k === '' || !$(`[data-fuview="${k}"]`)) click($(`[data-fumain="${GRP[k]}"]`)); if (k !== '') click($(`[data-fuview="${k}"]`)); };
const rowOf = no => qa('#fuBody tbody tr').find(tr => tr.querySelector('.no') && tr.querySelector('.no').textContent.startsWith(no));
const nos = () => qa('#fuBody tbody tr .no').map(td => td.textContent.match(/^\d{3}\/[A-Z]+\/\d{4}/)[0]);
const q_ = s => doc.querySelector(s);
const nosVC = () => qa('#fuBody tbody tr .no').map(td => td.textContent.match(/^\d{3}\/[A-Z]+\/\d{4}/)[0]);
const act = k => $(`#fuActive .fu-in[data-k="${k}"]`);
let checks = 0;
const ok = (c, m) => { assert.ok(c, m); checks++; };

click($('.side a[data-go="fu"]'));
ok(doc.body.classList.contains('pg-fu'), 'MP-R-15: font besar aktif di Follow Up');
// ===== MP-R-31: tab utama + tab anak =====
ok(qa('[data-fumain]').map(b => b.textContent).join('|') === 'Proses|Monitoring|Perlu Perhatian & Selesai' && $('[data-fumain="proses"]').classList.contains('on') && $('[data-fuview="belum_kirim"]').classList.contains('on'), 'MP-R-31: 3 tab utama, default Proses → 1. Kirim Invoice');
ok(qa('[data-fuview]').map(b => b.dataset.fuview).join() === 'belum_kirim,dikirim,diterima,plan_bayar', 'Tab anak Proses: 1 - 4 saja');
click($('[data-fumain="hasil"]'));
ok(qa('[data-fuview]').map(b => b.dataset.fuview).join() === 'lewat,masalah,close' && $('[data-fuview="lewat"]').classList.contains('on'), 'Tab anak Hasil & Kendala: FU Terlambat, Invoice Bermasalah, Invoice Close');
click($('[data-fuview="masalah"]')); click($('[data-fumain="proses"]')); click($('[data-fumain="hasil"]'));
ok($('[data-fuview="masalah"]').classList.contains('on'), 'Pindah tab utama kembali ke tab anak terakhir');
click($('[data-fumain="mon"]'));
ok(!qa('[data-fuview]').length && $('#fuBody table.vc, #fuBody .ag-root') && !$('#fuBody [data-edit]') && $('#fuBody [data-fuproc]'), 'Monitoring: tanpa tab anak, tanpa Update, ada Buka di Proses');
// MP-R-25: filter periode Follow Up default bulan berjalan (tanggal invoice)
ok($('#fuPY').value === '2026' && $('#fuPM').value === '11' && nosVC().sort().join() === '036/VSB/2026,064/SSC/2026,276/STM/2026', 'MP-R-25: Follow Up default November 2026 (tanggal invoice)');
setVal($('#fuPD1'), '2026-11-02', ['change']);
ok(nosVC().sort().join() === '036/VSB/2026,064/SSC/2026', 'Filter tanggal dari');
setVal($('#fuPD1'), '', ['change']); setVal($('#fuPM'), '', ['change']); setVal($('#fuPY'), '', ['change']);
ok(nosVC().length > 20, 'Filter periode Semua: semua invoice belum close');

// ===== MP-R-29 / MP-R-31: card ringkasan Follow Up per tab utama =====
const fcard = k => $(`[data-fucard="${k}"]`);
const fnum = k => +fcard(k).querySelector('.cv').textContent.match(/\d+/)[0];
tab('belum_kirim');
ok(['belum_kirim', 'dikirim', 'belum_fu', 'plan_bayar'].every(k => fcard(k)) && !fcard('masalah') && !fcard('close') && /Sisa piutang Rp/.test(fcard('dikirim').textContent), 'Card Proses: Belum dikirim, Belum diterima, Belum di-follow up, Tunggu bayar');
ok(fnum('belum_kirim') === window.eval("all().filter(r => !r.closed && r.status === 'belum_kirim').length") && fnum('belum_fu') === window.eval("all().filter(r => !r.closed && r.status === 'diterima' && !r.fu[0].aktual).length") && fnum('plan_bayar') === window.eval("all().filter(r => !r.closed && r.status === 'plan_bayar' && !r.batal && (r.sisa > 0 || !!r.kirim)).length"), 'Card Proses: angka sesuai data (filter periode Semua)');
click(fcard('belum_fu'));
ok($('[data-fuview="diterima"]').classList.contains('on') && $('#fuFu').value === '1' && fcard('belum_fu').classList.contains('on') && nos().every(n => window.eval(`!DATA.find(x => x.no === '${n}').fu[0].aktual`)), 'Klik Belum di-follow up: tab 3 + filter FU 1 belum dilakukan');
click(fcard('plan_bayar'));
ok($('[data-fuview="plan_bayar"]').classList.contains('on'), 'Klik Tunggu bayar: tab 4');
click(fcard('belum_kirim'));
ok($('[data-fuview="belum_kirim"]').classList.contains('on') && nos().length === fnum('belum_kirim'), 'Klik Belum dikirim: tab 1, jumlah baris = angka card');
setVal($('#fCompany'), 'VUCA');
ok(fnum('belum_kirim') === window.eval("all().filter(r => !r.closed && r.status === 'belum_kirim' && r.comp === 'VUCA').length"), 'Card ikut filter company');
setVal($('#fCompany'), '');
click($('[data-fumain="hasil"]'));
ok(['lewat', 'masalah', 'close'].every(k => fcard(k)) && !fcard('belum_kirim') && /Nilai invoice Rp/.test(fcard('close').textContent) && fnum('masalah') === 3, 'Card Hasil & Kendala: FU terlambat, Invoice bermasalah, Closed');
click(fcard('close'));
ok($('[data-fuview="close"]').classList.contains('on'), 'Klik Closed: tab Invoice Close');
click(fcard('lewat'));
ok($('[data-fuview="lewat"]').classList.contains('on') && nos().length === fnum('lewat'), 'Klik FU terlambat: tab FU Terlambat, jumlah baris = angka card');
tab('');
ok(!$('[data-fucard]'), 'Monitoring tanpa card');

// ===== Tab Semua = Visual Control =====
const vc = $('#fuBody table.vc');
ok(vc, 'MP-R-18: tab Semua memakai tabel lebar Visual Control');
const heads = qa('#fuBody thead th').map(t => t.textContent.trim());
ok(['No Faktur', 'DPP', 'PPN', 'Tanggal', 'Pengiriman', 'FU 1', 'FU 4', 'Status Invoice'].every(h => heads.includes(h)) && !heads.includes('Revisi Invoice'), 'Kolom Visual Control: faktur, tanggal, nilai, pengiriman, FU 1-4, status');
ok(!heads.some(h => /PPh/i.test(h)) && !/PPh/i.test($('#fuBody').textContent), 'MP-R-20: PPh 23 tidak ada di tab Semua');
ok(!heads.includes('Customer') && !heads.includes('Company') && !heads.includes('No Resi') && !heads.includes('Tgl Kirim'), 'MP-R-20: invoice/customer/SPK/company, pengiriman, dan tanggal masing-masing satu kolom');
const c255 = rowOf('255/STM/2026');
ok(/PT\. PELAYARAN NASIONAL INDONESIA/.test(c255.querySelector('.stk2').textContent) && /SPK/.test(c255.querySelector('.stk2').textContent) && /STM/.test(c255.querySelector('.stk2 .pill.co').textContent), 'Kolom Invoice: no invoice, customer, SPK, company');
const dts = tr => [...tr.querySelectorAll('.vc-dl dt')].map(d => d.textContent);
ok(['Invoice', 'Kirim', 'Terima', 'Plan bayar', 'Via', 'Penerima'].every(k => dts(c255).includes(k)), 'Kolom Tanggal dan Pengiriman berlabel');
ok(dts(c255).includes('Resi'), 'Via JNE menampilkan baris Resi');
ok(!dts(rowOf('020/VSB/2024')).includes('Resi'), 'Via Driver tanpa baris Resi');
ok(qa('#fuBody thead tr:nth-child(2) th').filter(t => t.textContent === 'Plan · Aktual').length === 4 && qa('#fuBody thead tr:nth-child(2) th').filter(t => t.textContent === 'Status · Keterangan').length === 4 && !heads.includes('Keterangan'), 'MP-R-23: per FU dua kolom (Plan · Aktual, Status · Keterangan)');
const c239 = rowOf('239/STM/2023'), longKet = c239.querySelector('.ket.clamp');
ok(longKet && /tidak mau bayar/.test(longKet.textContent) && longKet.nextElementSibling.matches('[data-ketx]'), 'Keterangan panjang diringkas, ada tombol selengkapnya');
click(longKet.nextElementSibling);
ok(longKet.classList.contains('open') && /ringkas/.test(longKet.nextElementSibling.textContent), 'Klik selengkapnya membuka keterangan');
click(longKet.nextElementSibling);
ok(!longKet.classList.contains('open'), 'Klik ringkas menutup lagi');
click($('[data-ketall]'));
ok($('#fuBody table.vc').classList.contains('ket-all') && /Ringkas semua/.test($('[data-ketall]').textContent), 'Tampilkan semua keterangan sekaligus');
click($('[data-ketall]'));
ok(qa('#fuBody .stk2').length > 3 && !$('#fuBody .stk3'), 'Kolom Invoice · Customer tetap (sticky)');
ok(rowOf('239/STM/2023').classList.contains('bad') && /INVOICE BERMASALAH/.test(rowOf('239/STM/2023').textContent) && /Invoice bermasalah/.test(rowOf('239/STM/2023').textContent), 'MP-R-25: contoh INVOICE BERMASALAH (batal) ditandai');
ok(/INVOICE BERMASALAH/.test(rowOf('261/STM/2026').textContent) && /Revisi invoice: customer minta/.test(rowOf('261/STM/2026').textContent) && !/Revisi ke-/.test($('#fuBody').textContent), 'Contoh revisi jadi INVOICE BERMASALAH dengan keterangan, tanpa alur kirim ulang');
ok(/BELUM DI TERIMA DI KEUANGAN/.test(rowOf('256/STM/2026').textContent), 'Contoh status FU 1 BELUM DI TERIMA DI KEUANGAN');
ok(/Total/.test($('#fuBody tfoot').textContent), 'Baris total');

// ===== Tab Invoice Bermasalah =====
tab('masalah');
ok(/Invoice Bermasalah/.test($('[data-fuview="masalah"]').textContent) && nos().sort().join() === '050/SSC/2025,239/STM/2023,261/STM/2026' && !$('[data-cnt]') && /^Invoice Bermasalah$/.test($('[data-fuview="masalah"]').textContent.trim()), 'MP-R-30 tab tanpa angka; Tab Invoice Bermasalah: 239 (batal), 261 (revisi), 050/SSC (masalah lain)');
ok(/Revisi invoice: customer minta nilai DPP/.test(rowOf('261/STM/2026').textContent) && /INVOICE BERMASALAH/.test(rowOf('261/STM/2026').textContent), 'Keterangan kenapa bermasalah tampil');
// MP-R-28: kolom per tab
const th = () => qa('#fuBody thead th').map(x => x.textContent.trim());
ok(th().join('|') === 'No|Invoice|Customer|Sisa Piutang|Umur|Status FU|Keterangan|', 'Tab Invoice Bermasalah: kolom Umur, Status FU, Keterangan');
tab('belum_kirim');
ok(th().join('|') === 'No|Invoice|Customer|Nilai Invoice|' && !th().some(h => /Diterima|Dikirim|Plan/.test(h)), 'Tab 1. Kirim Invoice: tanpa kolom tanggal terima / kirim / plan');
tab('dikirim');
ok(th().join('|') === 'No|Invoice|Customer|Dikirim|No Resi|' && /tidak perlu \(via Driver\)|belum diisi|JNE/.test($('#fuBody').textContent), 'Tab 2. Konfirmasi Diterima: kolom Dikirim + No Resi');
tab('plan_bayar');
ok(th().join('|') === 'No|Invoice|Customer|Rencana Bayar|Pembayaran|' && /Lunas/.test(rowOf('245/STM/2026').textContent), 'Tab 4: kolom Rencana Bayar + Pembayaran (lunas / sisa)');
tab('lewat');
ok(th().join('|') === 'No|Invoice|Customer|Sisa Piutang|PIC|FU Terjadwal|' && /terlambat \d+ hari/.test($('#fuBody').textContent), 'Tab FU Terlambat: kolom PIC + FU Terjadwal (terlambat n hari)');
tab('diterima');
ok(th().join('|') === 'No|Invoice|Customer|Sisa Piutang|Diterima / Umur|PIC|Follow Up Berikutnya|', 'Tab 3: kolom follow up');
tab('diterima');
ok(!nos().includes('239/STM/2023') && !nos().includes('050/SSC/2025'), 'Invoice bermasalah tidak lagi di tab 3. Follow Up Customer');

// ===== Status wajib =====
click($('[data-edit="240/STM/2026"]'));
ok($('#mForm').classList.contains('show') && act('st') && act('st').options.length === 4 && [...act('st').options].map(o => o.value).includes('INVOICE BERMASALAH') && ![...act('st').options].some(o => /^(BATAL|REVISI INVOICE|BERMASALAH)$/.test(o.value)), 'MP-R-25: status FU 4 = BELUM PLAN BAYAR, MASIH MENUNGGU JAWABAN, INVOICE BERMASALAH');
setVal(act('ket'), 'Sudah ditelepon');
setVal(act('akt'), '2026-11-02', ['change']);
ok(/Pilih status FU 4/.test($('#fuActErr').textContent) && act('akt').value === '2026-11-02', 'MP-R-22: tanggal dilakukan tetap terisi walau status belum dipilih (diingatkan)');
submit();
ok(/Pilih status FU 4/.test($('#fErr').textContent), 'Simpan ditolak bila tanggal dilakukan diisi tanpa status');
setVal(act('plan'), '2026-11-01');
setVal(act('st'), 'MASIH MENUNGGU JAWABAN');
ok(act('akt').value === '2026-11-02' && act('plan').value === '2026-11-01', 'Jadwal dan tanggal dilakukan diisi bersamaan, tidak hilang');
submit();
const r240 = window.eval('DATA.find(x => x.no === "240/STM/2026")');
ok(r240.fu[3].plan === '2026-11-01' && r240.fu[3].aktual === '2026-11-02' && r240.fu[3].st === 'MASIH MENUNGGU JAWABAN', 'Jadwal + tanggal dilakukan + status tersimpan sekaligus');
click($('[data-edit="240/STM/2026"]'));
ok(!$('#fuDone .fu-last') && /terkunci/.test($('#fuDone').textContent) && !/bisa dikoreksi/.test($('#fuDone').textContent) && $('[data-fuadd]'), 'FU yang disimpan langsung terkunci, lanjut FU berikutnya (Tambah FU 5)');
click($('[data-close]'));
click($('[data-edit="229/STM/2026"]'));
setVal(act('plan'), '2026-11-05');
submit();
const r229 = window.eval('DATA.find(x => x.no === "229/STM/2026")');
ok(r229.fu[0].plan === '2026-11-05' && !r229.fu[0].aktual, 'Boleh simpan jadwal saja, tanggal dilakukan menyusul');
click($('[data-edit="229/STM/2026"]'));
ok(act('plan').value === '2026-11-05' && !act('akt').value && !$('#fuDone li'), 'FU 1 masih berjalan dengan jadwal tersimpan');
setVal(act('st'), 'SUDAH DI TERIMA DI KEUANGAN'); setVal(act('ket'), 'Sudah diterima'); setVal(act('akt'), '2026-11-02', ['change']);
submit();
ok(window.eval('DATA.find(x => x.no === "229/STM/2026").fu[0].aktual') === '2026-11-02', 'Tanggal dilakukan menyusul tersimpan');

// ===== INVOICE BERMASALAH (MP-R-25) =====
click($('[data-edit="256/STM/2026"]'));
ok(/BELUM DI TERIMA DI KEUANGAN/.test($('#fuDone').textContent) && act('st').options.length === 4, 'FU 1 menampilkan status; FU 3 punya 3 pilihan');
setVal(act('st'), 'INVOICE BERMASALAH');
ok(/INVOICE BERMASALAH/.test($('#stHint').textContent) && /kenapa/.test($('#stHint').textContent) && /Invoice Bermasalah/.test($('#stHint').textContent), 'Efek INVOICE BERMASALAH dijelaskan sebelum simpan (keterangan kenapa)');
setVal(act('ket'), 'Revisi invoice: alamat NPWP salah');
setVal(act('akt'), '2026-11-02', ['change']);
ok(!$('#fuActive .fu-next'), 'INVOICE BERMASALAH: jadwal FU berikutnya tidak diusulkan');
submit();
ok(!$('#mForm').classList.contains('show') && /Invoice Bermasalah/.test($('#fuMsg').textContent), 'Tersimpan, invoice pindah ke tab Invoice Bermasalah');
tab('masalah');
ok(nos().includes('256/STM/2026') && /alamat NPWP salah/.test(rowOf('256/STM/2026').textContent), 'Tab Invoice Bermasalah memuat 256/STM/2026 dengan keterangannya');
click($('[data-edit="256/STM/2026"]'));
ok(/INVOICE BERMASALAH/.test($('#fuForm .warn').textContent) && /alamat NPWP salah/.test($('#fuForm .warn').textContent) && $('[data-step="bayar"]').hidden, 'Form: FU berhenti, keterangan tampil, plan bayar disembunyikan');
ok(!$('#fuDone .fu-last'), 'MP-R-22: status yang sudah disimpan terkunci');
click($('[data-close]'));

// ===== No faktur =====
tab('dikirim');
click($('[data-edit="270/STM/2026"]'));
const fk = $('#fuForm [name="faktur"]');
ok(fk && !fk.disabled && /^010\./.test(fk.value), 'MP-R-17: No faktur dari Invoicing tampil di step diterima, bisa dikoreksi');
setVal(fk, '');
submit();
ok(/No faktur tidak boleh kosong/.test($('#fErr').textContent), 'No faktur wajib');
setVal(fk, '010.004-26.99990001');
submit();
const r270 = window.eval('DATA.find(x => x.no === "270/STM/2026")');
ok(r270.faktur === '010.004-26.99990001' && /koreksi faktur/.test(r270.log.map(l => l.aksi).join()), 'Koreksi no faktur tersimpan dan tercatat di riwayat');

// ===== MP-R-19: alur kirim -> terima -> follow up =====
tab('belum_kirim');
click($('[data-edit="062/SSC/2026"]'));
ok(!$('#fuForm [data-step="terima"]') && $('#fuForm [data-step="fu"]').hidden, 'Tab 1: bagian Diterima Customer dan Follow Up belum muncul');
setVal($('#fuForm [name="kirim"]'), '2026-11-02');
ok(!$('#fuForm [data-step="terima"]'), 'Mengisi tanggal kirim tidak memunculkan Diterima Customer sebelum disimpan');
setVal($('#fuForm [name="via"]'), 'Driver');
submit();
tab('dikirim');
click($('[data-edit="062/SSC/2026"]'));
ok($('#fuForm [name="kirim"]').disabled && $('#fuForm [name="via"]').disabled && $('#fuForm [data-step="terima"]') && !$('#fuForm [name="resi"]') && $('#fuForm [data-step="fu"]').hidden, 'Tab 2 (Driver): data kirim terkunci, Diterima Customer muncul, tanpa no resi, Follow Up belum muncul');
setVal($('#fuForm [name="terima"]'), '2026-11-02');
ok($('#fuForm [data-step="fu"]').hidden, 'Follow Up belum muncul sebelum tanggal terima disimpan');
submit();
ok(!$('#mForm').classList.contains('show'), 'Driver: tanggal terima tersimpan tanpa no resi');
click($('[data-edit="270/STM/2026"]'));
ok($('#fuForm [name="kirim"]').disabled && $('#fuForm [name="resi"]') && !$('#fuForm [name="resi"]').disabled, 'Tab 2 (JNE): data kirim terkunci, no resi bisa diisi');
setVal($('#fuForm [name="terima"]'), '2026-11-02');
submit();
ok(/No resi JNE wajib/.test($('#fErr').textContent), 'JNE: no resi wajib saat menyimpan tanggal terima');
setVal($('#fuForm [name="resi"]'), 'JNE0099887766');
submit();
ok(!$('#mForm').classList.contains('show') && window.eval('DATA.find(x => x.no === "270/STM/2026").resi') === 'JNE0099887766', 'JNE: tanggal terima + no resi tersimpan');
// ===== MP-R-21: bukti terima (pilih file, Ctrl+V, hanya JPG/PNG/PDF, lebih dari 1) =====
tab('diterima');
click($('[data-edit="270/STM/2026"]'));
ok($('#bkDrop') && $('#bkFile').multiple && /\.pdf/.test($('#bkFile').accept), 'Bukti terima: kotak tempel/seret + pilih file (multiple, JPG/PNG/PDF)');
Object.defineProperty($('#bkFile'), 'files', {value: [{name: 'tanda-terima.pdf', type: 'application/pdf', size: 2048}, {name: 'catatan.docx', type: 'application/msword', size: 900}], configurable: true});
$('#bkFile').dispatchEvent(new window.Event('change', {bubbles: true}));
ok(qa('#bkList li').length === 1 && /catatan\.docx/.test($('#bkErr').textContent), 'Pilih file: PDF diterima, DOCX ditolak');
Object.defineProperty($('#bkFile'), 'files', {value: [{name: 'scan-besar.pdf', type: 'application/pdf', size: 6 * 1024 * 1024}], configurable: true});
$('#bkFile').dispatchEvent(new window.Event('change', {bubbles: true}));
ok(qa('#bkList li').length === 1 && /lebih dari 5 MB\): scan-besar\.pdf/.test($('#bkErr').textContent), 'MP-R-33: file lebih dari 5 MB ditolak');
const pv = new window.Event('paste', {bubbles: true, cancelable: true});
Object.defineProperty(pv, 'clipboardData', {value: {files: [{name: 'image.png', type: 'image/png', size: 51200}]}});
$('#fuForm').dispatchEvent(pv);
ok(qa('#bkList li').length === 2 && /^tempel-.*\.png/.test(qa('#bkList .nm')[1].textContent) && pv.defaultPrevented, 'Ctrl+V menempel screenshot sebagai file kedua');
const pt = new window.Event('paste', {bubbles: true, cancelable: true});
Object.defineProperty(pt, 'clipboardData', {value: {files: []}});
$('#fuForm [name="ketTerima"]').dispatchEvent(pt);
ok(!pt.defaultPrevented && qa('#bkList li').length === 2, 'Paste teks biasa tetap masuk ke kotak isian');
click(q_('[data-bkdel="0"]'));
ok(qa('#bkList li').length === 1, 'Bukti bisa dihapus sebelum disimpan');
submit();
const r270b = window.eval('DATA.find(x => x.no === "270/STM/2026")');
ok(r270b.bukti.length === 1 && /^tempel-/.test(r270b.bukti[0].name) && r270b.log.some(l => l.aksi === 'bukti terima'), 'Bukti terima tersimpan dan tercatat di riwayat');
click($('[data-edit="270/STM/2026"]'));
ok(!$('#fuForm [data-step="fu"]').hidden && !$('#fuForm [name="pic"]').value, 'Tab 3: Follow Up muncul setelah tanggal terima disimpan');
setVal(act('st'), 'SUDAH DI TERIMA DI KEUANGAN');
setVal(act('ket'), 'Sudah diterima bagian keuangan');
setVal(act('akt'), '2026-11-02', ['change']);
ok(!$('#fuActErr').textContent, 'PIC tidak wajib: FU 1 bisa ditandai dilakukan tanpa PIC');
submit();
ok(!$('#mForm').classList.contains('show') && window.eval('DATA.find(x => x.no === "270/STM/2026").fu[0].aktual') === '2026-11-02', 'FU 1 tersimpan tanpa PIC');
click($('[data-edit="270/STM/2026"]'));
ok(!$('#bkDrop') && qa('#bkList li').length === 1 && !$('[data-bkdel]'), 'Bukti terima terkunci setelah FU 1 disimpan');
setVal($('#fuForm [name="picTelp"]'), '12ab');
submit();
ok(/tidak valid/.test($('#fErr').textContent), 'Nomor PIC yang diisi tetap divalidasi');
click($('[data-close]'));

// ===== Export Semua =====
tab('');
ok(/📎 1 file/.test(rowOf('270/STM/2026').textContent), 'Tab Semua: kolom Pengiriman menampilkan jumlah bukti terima');
click($('#btnFuExport'));
const aoa = XL.pop().wb.sheets[0].ws.__aoa, H = aoa[3];
ok(['No Faktur', 'DPP', 'FU1 Status', 'FU4 Keterangan', 'Status Invoice'].every(h => H.includes(h)) && !H.includes('Revisi Invoice') && !H.some(h => /PPh/i.test(h)), 'Export Excel: kolom Visual Control, tanpa PPh 23');
const tot = aoa[aoa.length - 1];
ok(tot[H.indexOf('Sisa Piutang')] && tot[H.indexOf('Sisa Piutang')].f === `SUM(Q5:Q${aoa.length - 1})` && tot[H.indexOf('DPP')].f.startsWith('SUM(M5'), 'Export: total di kolom DPP s.d. Sisa Piutang');
const r239 = aoa.find(r => r[1] === '239/STM/2023');
ok(H.includes('Bukti Terima') && /^tempel-/.test(aoa.find(r => r[1] === '270/STM/2026')[H.indexOf('Bukti Terima')]), 'Export: kolom Bukti Terima');
ok(r239[H.indexOf('FU3 Status')] === 'INVOICE BERMASALAH' && /Invoice bermasalah: Batal: customer/.test(r239[H.indexOf('Status Invoice')]), 'Export: status FU dan status invoice + keterangan bermasalah');

// ===== MP-R-27: Close hanya di Follow Up tab 4 =====
click($('.side a[data-go="fu"]'));
tab('');
ok(!$('#fuBody [data-close-inv]'), 'Tab Semua tanpa tombol Close');
tab('diterima');
ok(!$('#fuBody [data-close-inv]'), 'Tab 3 tanpa tombol Close');
tab('plan_bayar');
ok($('#fuBody [data-close-inv="245/STM/2026"]') && !$('#fuBody [data-close-inv="245/STM/2026"]').disabled, 'Tab 4: tombol Close aktif untuk invoice lunas + plan bayar');
click($('#fuBody [data-close-inv="245/STM/2026"]'));
ok($('#mClose').classList.contains('show'), 'Close dibuka dari tab 4');
click($('#mClose [data-close]'));
click($('.side a[data-go="inv"]'));
ok(q_('#ivBody [data-ivact="lihat"]') && !q_('[data-ivact="close"]'), 'Menu Invoicing tab Invoiced tanpa tombol Close');

// ===== Monitoring =====
click($('.side a[data-go="mp"]'));
ok(!doc.body.classList.contains('pg-fu'), 'Font besar hanya di Follow Up');
const mrow = no => qa('#tabBody tbody tr').find(tr => tr.dataset.inv === no);
ok(mrow('239/STM/2023').classList.contains('bad') && /Invoice bermasalah/.test(mrow('239/STM/2023').textContent) && /tidak mau bayar/.test(mrow('239/STM/2023').textContent), 'Monitoring: invoice bermasalah ditandai merah + keterangan');
ok(mrow('050/SSC/2025').classList.contains('bad') && /restrukturisasi/.test(mrow('050/SSC/2025').textContent), 'Monitoring: 050/SSC bermasalah ditandai');
ok(mrow('256/STM/2026') && mrow('256/STM/2026').classList.contains('bad'), 'Monitoring: invoice yang baru ditandai bermasalah tetap dihitung piutang');

// ===== MP-R-24: Dashboard Plan Bayar =====
click($('.side a[data-go="pb"]'));
const card = m => $(`.pbcard[data-pbm="${m}"] .cv`).textContent;
ok(card(10) === 'Rp 9.990.000', 'Card Oktober tetap Rp 9.990.000 walau 245/STM/2026 sudah lunas');
const pbHead = qa('#pbBody thead th').map(x => x.textContent);
ok(!pbHead.some(h => /Sisa/.test(h)) && !/Lunas/.test($('#pbBody tbody').textContent), 'Tabel plan bayar tanpa Sisa Sekarang / Lunas');
const reaHead = qa('#pbReaBody thead th').map(x => x.textContent);
ok(reaHead.includes('Plan Bayar') && reaHead.includes('Plan vs Bayar') && !/[Jj]anji/.test($('#pbReaBody').textContent), 'Realisasi: Janji Bayar diganti Plan Bayar');
click($('#btnPbExport'));
const pbx = XL.pop().wb.sheets;
const rin = pbx.find(x => x.name === 'Rincian').ws.__aoa, reax = pbx.find(x => x.name === 'Realisasi').ws.__aoa;
ok(!rin[3].some(h => /Sisa|Status/.test(h)) && reax[3].includes('Plan Bayar') && !reax[3].some(h => /Janji/.test(h)), 'Export: Rincian tanpa Sisa/Status, Realisasi pakai Plan Bayar');
// plan diisi setelah invoice lunas: nilai plan tidak 0
window.eval('DATA.find(x => x.no === "127/STM/2026").bayar.push({tgl: "2026-11-01", no: "PNR-2611-009", nom: 35520000})');
click($('.side a[data-go="fu"]')); tab('diterima');
click($('[data-edit="127/STM/2026"]'));
setVal($('#fuForm [name="planBayar"]'), '2026-11-25');
submit();
ok(window.eval('all().find(x => x.no === "127/STM/2026").planNom') === 35520000, 'Plan diisi setelah lunas: nilai plan = tagihan, bukan 0');


// ===== Tutorial v2: tombol di baris atas konten, menu Halaman ini + alur proses lintas halaman =====
{
const TC = window.eval('TOUR_CFG');
ok(doc.querySelector('.tour-bar #tourBtn') && !doc.querySelector('.tour-fab'), 'Tutorial: tombol di baris atas konten, bukan mengambang di bawah (tidak menutupi Simpan)');
doc.getElementById('tourBtn').dispatchEvent(new window.MouseEvent('click', {bubbles: true}));
const tmenu = doc.getElementById('tourMenu');
ok(!tmenu.hidden && tmenu.querySelectorAll('[data-tour]').length === TC.flows().length + 1 && tmenu.querySelector('[data-tour="page"]'), 'Menu Tutorial: Halaman ini + ' + TC.flows().length + ' alur proses');
const tmiss = [];
const hid = el => !el || el.closest('.hidden,[hidden]') || (el.closest('.modal') && !el.closest('#dlg') && !el.closest('.modal').classList.contains('show')) || (el.closest('#dlg') && !el.closest('#dlg').classList.contains('on'));
for (const f of TC.flows()) { f.steps.forEach((s, i) => { if (s.go) s.go();  if (hid(doc.querySelector(s.el))) tmiss.push(f.title + ' #' + (i + 1) + ' ' + s.el); }); TC.cleanup(); }
ok(!tmiss.length, 'Setiap langkah alur proses menunjuk elemen yang tampil setelah halamannya dibuka: ' + tmiss.join('; '));
ok(TC.flows().every(f => f.steps.length >= 5 && f.steps.every(s => s.t && s.d && s.el)), 'Setiap langkah punya judul, penjelasan, dan elemen');
doc.getElementById('tourBtn').dispatchEvent(new window.MouseEvent('click', {bubbles: true}));
doc.getElementById('tourMenu').querySelector('[data-tour="0"]').dispatchEvent(new window.MouseEvent('click', {bubbles: true}));
ok(doc.getElementById('tourMenu').hidden && /belum termuat/.test(doc.getElementById('toast').textContent), 'Tanpa driver.js: pesan tutorial belum termuat');

}
ok(errors.length === 0, 'Tidak ada error JS: ' + errors.join(' | '));

// ===== MP-R-25: mode AG Grid sungguhan + skeleton loading =====
// Butuh paket ag-grid-community@32.3.3 (di samping jsdom, atau env AG_GRID_PATH ke ag-grid-community.min.js).
(async () => {
  const cand = [process.env.AG_GRID_PATH, process.env.JSDOM_PATH && path.join(path.dirname(process.env.JSDOM_PATH), 'ag-grid-community', 'dist', 'ag-grid-community.min.js')].filter(Boolean);
  let agFile = cand.find(f => fs.existsSync(f));
  if (!agFile) { try { agFile = require.resolve('ag-grid-community/dist/ag-grid-community.min.js'); } catch (e) {} }
  if (!agFile) { console.log(`OK — ${checks} pemeriksaan lulus (bagian AG Grid dilewati: ag-grid-community tidak ditemukan).`); return; }
  const lib = fs.readFileSync(agFile, 'utf8'), errs2 = [];
  const html2 = html.replace('<style>', () => '<script>' + lib.replace(/<\/script/gi, '<\/script') + '</script><style>');
  const d2 = new JSDOM(html2, {runScripts: 'dangerously', pretendToBeVisual: true,
    beforeParse(w){ w.scrollTo = () => {}; w.XLSX = fakeXlsx; w.print = () => {}; w.open = () => null; w.addEventListener('error', e => errs2.push(e.message)); }});
  const W = d2.window, D = W.document, g = s => D.querySelector(s), ga = s => [...D.querySelectorAll(s)];
  const wait = ms => new Promise(r => setTimeout(r, ms));
  const until = async (fn, ms = 3000) => { const t0 = Date.now(); while (!fn() && Date.now() - t0 < ms) await wait(50); };
  const clk = el => { assert.ok(el, 'elemen grid tidak ada'); el.dispatchEvent(new W.MouseEvent('click', {bubbles: true})); };
  const gRows = sel => ga(sel + ' .ag-center-cols-container .ag-row');
  const gHeads = sel => ga(sel + ' .ag-header-cell-text, ' + sel + ' .ag-header-group-text').map(x => x.textContent.trim());
  ok(W.agGrid && typeof W.agGrid.createGrid === 'function', 'AG Grid Community termuat');
  ok(g('#tabBody .sk.skrow') && g('#cards .skcard'), 'MP-R-25: skeleton tabel dan card tampil saat memuat');
  await wait(800);
  ok(g('#tabBody .mp-grid') && !g('#tabBody .sk'), 'Monitoring: skeleton diganti AG Grid');
  ok(gRows('#tabBody').length === W.eval('mpPiutang().length') && ['No Invoice', 'Customer', 'Sisa Piutang', 'Umur', 'Kategori', 'Follow Up'].every(h => gHeads('#tabBody').includes(h)), 'Monitoring per invoice: AG Grid berisi semua piutang + kolom');
  await until(() => ga('#tabBody .ag-row-pinned').some(r => /Total \d+ invoice/.test(r.textContent)));
  ok(ga('#tabBody .ag-row-pinned').some(r => /Total \d+ invoice/.test(r.textContent)), 'Monitoring: baris total di bawah (pinned)');
  ok(g('#tabBody .ag-pinned-left-cols-container .ag-row'), 'Kolom No dan No Invoice tetap di kiri (pinned)');
  clk(g('#tabBody button[data-inv]'));
  ok(g('#mDetail').classList.contains('show'), 'Tombol Detail di grid membuka detail invoice');
  clk(g('#mDetail [data-close]'));
  clk(g('[data-tab="cust"]')); await wait(800);
  ok(gHeads('#tabBody').includes('Total Sisa Piutang') && gRows('#tabBody').length > 3, 'Rekap per customer: AG Grid');
  // Follow Up
  clk(g('.side a[data-go="fu"]'));
  ok(g('#fuBody .sk.skrow'), 'Follow Up: skeleton tabel');
  await wait(800);
  clk(g('[data-fumain="mon"]')); await wait(800);
  ok(g('#fuBody .mp-grid.vc') && gRows('#fuBody').length === 3 && ['Invoice & Pajak', 'Nilai', 'FU 1', 'FU 4', 'Status · Keterangan', 'Status Invoice'].every(h => gHeads('#fuBody').includes(h)), 'Follow Up tab Semua: AG Grid dengan group header, default 3 invoice November');
  ok(!g('#fuBody [data-edit]'), 'MP-R-31: Monitoring tanpa tombol Update');
  clk(g('#fuBody [data-fuproc]')); await wait(800);
  ok(g('#mForm').classList.contains('show') && g('[data-fumain="proses"]').classList.contains('on'), 'Buka di Proses: pindah ke tab Proses + form terbuka');
  clk(g('#mForm [data-close]'));
  W.eval("state.fuPY = ''; state.fuPM = ''; state.fuStatus = 'masalah'; show();"); await wait(800);
  ok(gRows('#fuBody').length === 3 && /Revisi invoice: customer minta/.test(g('#fuBody').textContent), 'Tab Invoice Bermasalah (grid ringkas) + keterangan');
  // Plan Bayar
  clk(g('.side a[data-go="pb"]'));
  ok(g('#pbCards .skcard') && g('#pbBody .sk'), 'Plan Bayar: skeleton card dan tabel');
  await wait(800);
  ok(g('#pbMonthSel').value === '11' && g('.pbcard.on[data-pbm="11"]'), 'Plan Bayar default bulan berjalan (November)');
  ok(g('#pbBody .mp-grid') && gRows('#pbBody').length === W.eval('pbRows().filter(r => +r.planBayar.slice(5, 7) === 11).length') && gHeads('#pbBody').includes('Nilai Plan'), 'Rincian plan bayar: AG Grid');
  ok(g('#pbReaBody .mp-grid') && gHeads('#pbReaBody').includes('Plan vs Bayar') && gHeads('#pbReaBody').includes('Ketepatan'), 'Realisasi bayar: AG Grid dengan group header');
  ok(errs2.length === 0, 'Tidak ada error JS di mode AG Grid: ' + errs2.join(' | '));
  console.log(`OK — ${checks} pemeriksaan lulus.`);
})().catch(e => { console.error(e); process.exit(1); });
