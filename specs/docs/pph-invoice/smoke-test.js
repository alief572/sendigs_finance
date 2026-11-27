'use strict';
// Smoke test mockup to-be PPh Invoice (PPH-R-03..11) + Request Payment (request-payment.html), tersambung lewat localStorage.
// Jalankan: node specs/docs/pph-invoice/smoke-test.js   (jsdom dari node_modules biasa, atau dari folder di env JSDOM_PATH)
const fs = require('fs'), path = require('path'), assert = require('assert');
let JSDOM;
try { ({JSDOM} = require(process.env.JSDOM_PATH || 'jsdom')); }
catch (e) { console.error('jsdom tidak ditemukan. Pasang: npm i jsdom, atau set JSDOM_PATH.'); process.exit(2); }
const read = f => fs.readFileSync(path.join(__dirname, f), 'utf8').replace(/<script src=[^>]*><\/script>/g, '').replace(/<link [^>]*>/g, '');
const HTML_PPH = read('index.html'), HTML_RP = read('request-payment.html');
const errors = []; let store = {}, W, doc;
// Buka halaman dengan isi localStorage dari halaman sebelumnya (dua mockup tersambung lewat localStorage).
function open(html, file) {
  const dom = new JSDOM(html, {url: 'http://localhost/' + file, runScripts: 'dangerously', pretendToBeVisual: true,
    beforeParse(w) { w.addEventListener('error', e => errors.push(file + ': ' + e.message)); w.print = () => { w.__printed = (w.__printed || 0) + 1; }; w.scrollTo = () => {}; Object.entries(store).forEach(([k, v]) => w.localStorage.setItem(k, v)); }});
  W = dom.window; doc = W.document; }
const keep = () => { store = {}; for (let i = 0; i < W.localStorage.length; i++) { const k = W.localStorage.key(i); store[k] = W.localStorage.getItem(k); } };
open(HTML_PPH, 'index.html');
const $ = id => doc.getElementById(id), q = s => doc.querySelector(s), qa = s => [...doc.querySelectorAll(s)];
const click = el => { assert.ok(el, 'elemen tidak ada'); el.dispatchEvent(new W.MouseEvent('click', {bubbles: true})); };
const setVal = (el, v, ev = 'input') => { assert.ok(el, 'input tidak ada'); el.value = v; el.dispatchEvent(new W.Event(ev, {bubbles: true})); };
const setFile = (el, name) => { Object.defineProperty(el, 'files', {value: [{name}], configurable: true}); el.dispatchEvent(new W.Event('change', {bubbles: true})); };
const flush = () => new Promise(r => setTimeout(r, 5));
let n = 0; const ok = (c, m) => { assert.ok(c, m); n++; };
const nav = p => click(q(`.sidebar [data-go="${p}"]`));
const dlgOn = () => $('dlg').classList.contains('on');
const closeDlg = async () => { click($('dlgOk')); await flush(); };
const custId = nm => [...$('rCust').options].find(o => o.textContent === nm).value;
const pphRow = inv => qa('#pphT tbody tr').find(tr => tr.dataset.inv === inv);
const tab = t => click(q(`[data-ptab="${t}"]`));
const page = () => qa('.page').find(s => !s.classList.contains('hidden')).id.slice(3);

async function receive(mutKet, cust, mode, invs) {
  nav('pp'); click(qa('#ppT tbody tr').find(tr => tr.textContent.includes(mutKet)).querySelector('[data-proc]'));
  setVal($('rCust'), custId(cust), 'change'); setVal($('rPph'), mode, 'change');
  invs.forEach(i => { const c = q(`[data-pick="${i}"]`); c.checked = true; c.dispatchEvent(new W.Event('change', {bubbles: true})); });
  click($('rNext'));
}
async function saveReceipt() { click($('rSave')); await closeDlg(); await flush(); }

(async () => {
  const kb = $('konsepBody').textContent;
  ok(/Ringkasan Perubahan/.test(q('#pg-konsep h1').textContent) && /dikembalikan ke client/.test(kb) && /Process to Request Payment/.test(kb) && /Finance & Accounting/.test(kb) && !/Titip/.test(kb), 'Ringkasan: pengembalian via Request Payment, tanpa Titip Setor');

  // Penerimaan Piutang
  nav('pp'); ok(qa('#ppT tbody tr').length === 6 && /Unlocated/.test($('ppT').textContent), 'Penerimaan Piutang: 6 penerimaan Open, termasuk 1 dari Unlocated');
  click(qa('#ppT tbody tr')[0].querySelector('[data-proc]'));
  ok([...$('rPph').options].map(o => o.textContent).join('|') === '- Pilih -|Dipotong Client|Tidak Dipotong Client|Tidak Ada PPh', 'Pilihan PPh 23: Dipotong Client / Tidak Dipotong Client / Tidak Ada PPh');
  setVal($('rPph'), 'TIDAK', 'change'); ok(/dikembalikan ke client/.test($('rPphHelp').textContent), 'Penjelasan Tidak Dipotong Client: PPh dikembalikan ke client');
  setVal($('rCust'), custId('PT INDO KORDSA TBK'), 'change');
  ok(/INV 245\/SSC\/2026/.test($('rHidden').textContent) && !q('[data-pick="INV 245/SSC/2026"]'), 'Invoice yang sisanya tinggal PPh tidak muncul, ada catatan');
  setVal($('rPph'), '', 'change'); setVal($('rCust'), custId('PT MADA WIKRI TUNGGAL'), 'change'); click($('rNext'));
  ok(/Pilih PPh:/.test($('rMsg').textContent), 'Process tanpa pilihan PPh ditolak');
  click($('rBack'));

  // Dipotong Client
  await receive('MADA WIKRI', 'PT MADA WIKRI TUNGGAL', 'POTONG', ['INV 281/STM/2026']);
  ok(/Balance/.test($('rBan').textContent), 'Dipotong: uang masuk 16.350.000 = tagihan 16.650.000 − PPh 300.000');
  let jv = $('rJvT').textContent;
  ok(/1101-02-09/.test(jv) && /16,350,000\.00/.test(jv) && !/1106-01-02/.test(jv) && /tetap di piutang/.test(jv), 'Jurnal Dipotong: Dr Bank / Cr Piutang, tanpa Dr PPh 23');
  click($('rCmp')); ok(/1106-01-02/.test($('rCmpBox').textContent), 'Pembanding jurnal sekarang: ada Dr PPh 23 Disetor');
  await saveReceipt();
  ok(dlgOn() && /Menunggu Bukti Potong/.test($('dlgB').textContent) && page() === 'pph', 'Tersimpan, PPh masuk PPh Invoice'); await closeDlg();
  ok(/Menunggu Bukti Potong/.test(pphRow('INV 281/STM/2026').textContent), 'Tab PPh Dipotong: INV 281');

  // Tidak Dipotong Client -> pengembalian via Request Payment
  await receive('40000000.00 ARWANA', 'PT ARWANA CITRAMULIA TBK', 'TIDAK', ['INV 284/STM/2026']);
  jv = $('rJvT').textContent;
  ok(/Balance/.test($('rBan').textContent) && /39,200,000\.00/.test(jv) && /1106-01-02/.test(jv) && /PPh 23 Disetor/.test(jv) && /800,000\.00/.test(jv) && !/Hutang/.test(jv), 'Tidak Dipotong: Dr Bank 40.000.000 / Cr Piutang 39.200.000 / Cr PPh 23 Disetor 1106-01-02 800.000');
  await saveReceipt(); ok(/Belum Diproses/.test($('dlgB').textContent), 'Tersimpan: status Belum Diproses'); await closeDlg();
  ok(q('[data-ptab="TIDAK"]').parentNode.classList.contains('active') && /Belum Diproses/.test(pphRow('INV 284/STM/2026').textContent) && /Request Payment/.test($('pphH').textContent), 'Tab PPh Tidak Dipotong: kolom Request Payment');
  ok(!pphRow('INV 284/STM/2026').querySelector('[data-bp]') && pphRow('INV 284/STM/2026').querySelector('[data-rp]'), 'Belum dikembalikan: aksi Process to Request Payment, belum ada Input Bukti Potong');
  click(pphRow('INV 284/STM/2026').querySelector('[data-rp]'));
  ok(/Process to Request Payment/.test($('dlgT').textContent) && $('kAmt').value === '800,000.00', 'Modal Process to Request Payment, nominal = PPh');
  jv = $('kJvT').textContent;
  ok(/\(tanggal Payment\)/.test(jv) && /1106-01-02/.test(jv) && /dipilih saat Payment/.test(jv) && !/1101-02|Bank BCA|Bank OCBC/.test(jv), 'Preview jurnal (terbentuk saat Payment): Dr PPh 23 Disetor / Cr Bank pembayar (dipilih saat Payment, belum tampil bank apa)');
  setVal($('kBank'), 'BCA'); setVal($('kRek'), '12ab');
  ok(/harus angka/.test($('kBan').textContent), 'No. rekening harus angka');
  setVal($('kRek'), '5260123456'); ok(/surat permintaan/.test($('kBan').textContent), 'Surat permintaan client wajib');
  click($('dlgOk')); ok(dlgOn() && /surat permintaan/.test($('dlgErr').textContent), 'Tidak bisa diproses tanpa surat');
  setFile($('kFile'), 'surat-request-arwana.pdf');
  click($('dlgOk')); await flush(); ok(/KPPH\/2026\/11\/0001/.test($('dlgB').textContent) && /Request Payment/.test($('dlgB').textContent), 'Dokumen pengembalian KPPH/2026/11/0001 masuk Request Payment'); await closeDlg();
  ok(/Proses Request Payment/.test(pphRow('INV 284/STM/2026').textContent) && /KPPH\/2026\/11\/0001/.test(pphRow('INV 284/STM/2026').textContent) && !pphRow('INV 284/STM/2026').querySelector('[data-bp]'), 'Status Proses Request Payment, belum bisa input bukti potong');
  ok(/Di Request Payment/.test(q('#pphT [data-stage]').textContent) && q('#pphT [data-rplink]').getAttribute('href') === 'request-payment.html#req', 'Tahap: Di Request Payment, aksi Buka Request Payment');
  nav('jur'); ok(!/KPPH/.test($('jurT').textContent), 'Belum ada jurnal sebelum Payment');
  keep();

  // ===== Request Payment (salinan mockup Request Payment terbaru + kategori Pengembalian PPh) =====
  open(HTML_RP, 'request-payment.html');
  ok(W.eval("(()=>{const d=doc('KPPH/2026/11/0001');return d&&d.kat==='Pengembalian PPh'&&d.dpp===800000&&d.coa==='1106-01-02'&&tax(d).net===800000&&d.supp==='PT ARWANA CITRAMULIA TBK';})()"), 'KPPH/2026/11/0001 otomatis di Request Payment: kategori Pengembalian PPh, 800.000, tanpa PPN/PPh');
  ok(W.eval("docRek(doc('KPPH/2026/11/0001')).no") === '5260123456' && /surat-request-arwana\.pdf/.test(W.eval("doc('KPPH/2026/11/0001').lamp.join()")), 'Rekening tujuan dari isian rekening client, lampiran surat permintaan');
  ok(/Pengembalian PPh/.test($('pphLegend').textContent), 'Catatan kategori Pengembalian PPh tampil');
  setVal($('fKat'), 'Pengembalian PPh', 'change'); click($('rpFilter')); ok(qa('#table_rp tbody tr').length === 1, 'Filter kategori Pengembalian PPh');
  { const c = q('[data-sel="KPPH/2026/11/0001"]'); c.checked = true; c.dispatchEvent(new W.Event('change', {bubbles: true})); }
  click($('rpAtur')); click($('aturOk')); click($('dlgOk')); await flush(); click($('dlgOk'));
  const pgj = W.eval("S.pgjs.find(p=>p.transfers.some(t=>t.items.includes('KPPH/2026/11/0001'))).no");
  keep(); open(HTML_PPH, 'index.html#pph-tidak');
  ok(new RegExp('Approval ' + pgj).test(pphRow('INV 284/STM/2026').textContent), 'PPh Invoice membaca tahap Approval ' + pgj);
  keep(); open(HTML_RP, 'request-payment.html#apv');
  click(q(`[data-open="${pgj}"]`)); click($('adSave')); click($('dlgOk')); await flush(); click($('dlgOk'));
  click(q('.sidebar [data-go="pay"]')); click($('payChoose')); setVal($('jenisPay'), '2'); click($('dlgOk')); await flush();
  { const c = qa('#plT .check_payment').find(c => c.value === pgj + '/T1'); c.checked = true; c.dispatchEvent(new W.Event('change', {bubbles: true})); }
  click($('plProses'));
  ok(/\(pilih bank\)/.test($('pfJ').textContent), 'Form Payment: bank pembayar baru dipilih di sini');
  setVal($('pfBank'), 'BCA', 'change');
  Object.defineProperty($('pfFile'), 'files', {value: [{name: 'bukti-trf-arwana.pdf'}], configurable: true}); $('pfFile').dispatchEvent(new W.Event('change'));
  ok(/1106-01-02/.test($('pfJ').textContent) && /PPh 23 Disetor/.test($('pfJ').textContent) && /1101-02-09/.test($('pfJ').textContent) && /800,000/.test($('pfJ').textContent), 'Jurnal Payment: Dr PPh 23 Disetor 1106-01-02 800.000 / Cr Bank STM 1101-02-09');
  const bk = W.eval('U.pay.form.bk'); click($('pfSave')); click($('dlgOk')); await flush(); click($('dlgOk'));
  keep();

  // ===== Kembali ke PPh Invoice =====
  open(HTML_PPH, 'index.html#pph-tidak');
  ok(!pphRow('INV 284/STM/2026'), 'Setelah Payment: keluar dari tab Tidak Dipotong');
  tab('POTONG'); ok(/Menunggu Bukti Potong/.test(pphRow('INV 284/STM/2026').textContent) && /Dikembalikan KPPH/.test(pphRow('INV 284/STM/2026').textContent) && pphRow('INV 284/STM/2026').querySelector('[data-bp]'), 'Masuk tab PPh Dipotong: Menunggu Bukti Potong, label Dikembalikan');
  nav('jur'); ok(new RegExp(bk).test($('jurT').textContent) && /Payment \(pengembalian PPh\)/.test($('jurT').textContent), 'Jurnal Payment ' + bk + ' dari Request Payment masuk Jurnal PPh'); nav('pph'); tab('POTONG');

  // Bukti potong
  click(pphRow('INV 284/STM/2026').querySelector('[data-bp]'));
  ok($('bNpwp').value === '0013.3456.7890.0510' && !$('bMasa') && !/Masa Pajak/.test($('dlgB').textContent), 'Input Bukti Potong tanpa Masa Pajak; NPWP terisi dari master customer');
  setVal($('bNo'), '2600211001'); setVal($('bTgl'), '2026-11-26'); setVal($('bVal'), '750000');
  ok(/berbeda/.test($('bBan').textContent) && $('bBan').classList.contains('alert-danger'), 'Nilai bukti potong beda dari sistem: ditolak');
  setVal($('bVal'), '800000'); setFile($('bFile'), 'bupot-arwana.pdf');
  jv = $('bJvT').textContent;
  ok(/1106-01-02/.test(jv) && /1102-01-01/.test(jv) && /26 November 2026/.test(jv), 'Jurnal bukti potong: Dr PPh 23 Disetor / Cr Piutang, tanggal bukti potong');
  click($('dlgOk')); await flush(); ok(/BPT\/2026\/11\/0002/.test($('dlgB').textContent), 'Bukti potong tersimpan'); await closeDlg();
  ok(!pphRow('INV 284/STM/2026'), 'Selesai: hilang dari filter Belum Selesai');
  click(pphRow('INV 281/STM/2026').querySelector('[data-bp]'));
  setVal($('bNo'), '2600211001'); ok(/customer lain/.test($('bBan').textContent), 'No bukti potong customer lain ditolak'); click($('dlgNo'));
  ok(/Sebelum berlaku/.test(pphRow('INV 198/STM/2026').textContent) && /age-old/.test(pphRow('INV 198/STM/2026').innerHTML), 'Sebelum tanggal berlaku: label + umur > 30 hari merah');
  click(pphRow('INV 198/STM/2026').querySelector('[data-bp]'));
  setVal($('bNo'), '2600199001'); setVal($('bVal'), '220000'); setFile($('bFile'), 'bupot-asahi.pdf');
  ok(/Tanpa jurnal/.test($('bJvT').textContent), 'Legacy: bukti potong tanpa jurnal'); click($('dlgOk')); await flush(); await closeDlg();

  // Record Bukti Potong Diterima: view + print
  tab('REC');
  ok(!/Masa Pajak/.test($('pphH').textContent), 'Bukti Potong Diterima tanpa kolom Masa Pajak');
  ok(qa('#pphT tbody tr[data-rec]').length === 3 && /No\. Bukti Potong/.test($('pphH').textContent) && $('recTot').textContent === '1,220,000.00', 'Tab Bukti Potong Diterima: 3 record, total 1.220.000');
  const r284 = qa('#pphT tbody tr[data-rec]').find(tr => /INV 284/.test(tr.textContent));
  click(r284.querySelector('[data-rv]'));
  ok(/Record Bukti Potong PPh 23/.test($('dlgB').textContent) && /2600211001/.test($('dlgB').textContent) && /Pengembalian ke client/.test($('dlgB').textContent) && /1106-01-02/.test($('dlgB').textContent), 'View: detail bukti potong + pengembalian + jurnal'); click($('dlgOk'));
  click(r284.querySelector('[data-rpr]'));
  ok(W.__printed === 1 && /2600211001/.test($('printArea').textContent) && /Dicetak/.test($('printArea').textContent), 'Print: isi record dicetak');
  ok(/Lampiran: File Bukti Potong/.test($('printAtt').textContent) && /bupot-arwana\.pdf/.test($('printAtt').textContent) && /page-break-before/.test($('printAtt').getAttribute('style')), 'Print: file bukti potong ikut dicetak (halaman lampiran setelah record)');
  setVal($('pQ'), 'asahi'); ok(qa('#pphT tbody tr[data-rec]').length === 1, 'Search di record'); setVal($('pQ'), '');

  // Filter company + detail
  tab('POTONG'); setVal($('pCo'), 'SUSTAIN', 'change'); ok(qa('#pphT tbody tr[data-inv]').length === 1 && pphRow('INV 245/SSC/2026'), 'Filter company'); setVal($('pCo'), '', 'change');
  setVal($('pSt'), 'SELESAI', 'change'); click(pphRow('INV 284/STM/2026').querySelector('[data-det]'));
  ok(/Pengembalian ke Client KPPH/.test($('detBody').textContent) && /Bukti Potong BPT/.test($('detBody').textContent), 'Detail: riwayat penerimaan, pengembalian, bukti potong');
  click($('detBack')); setVal($('pSt'), 'OPEN', 'change');

  // PPh Final UMKM ikut pola
  await receive('INV 297', 'PT KERETA API INDONESIA', 'POTONG', ['INV 297/VSB/2026']);
  ok(/Balance/.test($('rBan').textContent) && /PPh Final 0,5% 40,000\.00 tetap di piutang/.test($('rJvT').textContent), 'UMKM Dipotong: PPh Final 40.000 tetap di piutang');
  click($('rCmp')); ok(/Beban PPh Final/.test($('rCmpBox').textContent), 'Pembanding UMKM sekarang: Dr Beban PPh Final');
  await saveReceipt(); await closeDlg();
  click(pphRow('INV 297/VSB/2026').querySelector('[data-bp]'));
  setVal($('bNo'), '2600300777'); setVal($('bVal'), '40000'); setFile($('bFile'), 'bupot-kai.pdf');
  ok(/1106-01-05/.test($('bJvT').textContent) && /PPh Final Disetor/.test($('bJvT').textContent) && !/Beban/.test($('bJvT').textContent), 'Bukti potong UMKM: Dr PPh Final Disetor 1106-01-05 / Cr Piutang');
  click($('dlgOk')); await flush(); await closeDlg();

  // Unlocated
  await receive('INDO KORDSA', 'PT INDO KORDSA TBK', 'POTONG', ['INV 299/SSC/2026']);
  jv = $('rJvT').textContent;
  ok(/Balance/.test($('rBan').textContent) && /Penerimaan Belum Teridentifikasi/.test(jv) && !/1101-02-05/.test(jv), 'Unlocated: Dr Penerimaan Belum Teridentifikasi / Cr Piutang');
  await saveReceipt(); await closeDlg(); ok(pphRow('INV 299/SSC/2026'), 'Unlocated: PPh masuk PPh Invoice');

  // Tidak Ada PPh
  await receive('PEMBAYARAN INV 290', 'PT KERETA API INDONESIA', 'NONE', ['INV 290/VSB/2026']);
  ok(/Balance/.test($('rBan').textContent) && !/tetap di piutang/.test($('rJvT').textContent), 'Tidak Ada PPh: tagihan penuh, jurnal seperti sekarang');
  await saveReceipt(); ok(!/PPh Invoice/.test($('dlgB').textContent), 'Tidak masuk PPh Invoice'); await closeDlg();

  // History + data contoh tab Tidak Dipotong
  nav('pph'); tab('HIST');
  ok(qa('#pphT tbody tr').length === 3 && /Nilai Setor/.test($('pphH').textContent) && $('pStWrap').classList.contains('hidden'), 'Tab History: 3 data menu lama, read-only');
  ok(!q('.sidebar [data-go="old"]'), 'Menu lama Penerimaan PPH 23 tidak ada lagi di sidebar');
  tab('TIDAK'); ok(/Belum Diproses/.test(pphRow('INV 233/STM/2026').textContent), 'Data contoh HEINZ tidak dipotong: Belum Diproses');

  ok(!/PPh 23 Invoice|PPh 23 Dipotong|PPh 23 Tidak Dipotong/.test(doc.body.textContent) && /PPh Invoice/.test(q('.sidebar').textContent), 'Menu dan tab memakai label PPh (bukan PPh 23)');
  ok(!/Hutang Pengembalian|Titipan|Simulasi Payment/.test(doc.body.innerHTML), 'Tidak ada akun Hutang Pengembalian / Titipan: akun PPh hanya PPh 23 Disetor dan PPh Final Disetor');
  nav('jur'); ok(qa('#jurT tbody tr').length === 8 && /Payment \(pengembalian PPh\)/.test($('jurT').textContent), 'Jurnal: 5 penerimaan + 1 payment pengembalian + 2 bukti potong');
  click($('btnReset')); click($('dlgOk')); await flush(); nav('pp'); ok(qa('#ppT tbody tr').length === 6, 'Reset data');
  ok(!errors.length, 'Tidak ada error JS: ' + errors.join('; '));
  console.log(`OK — ${n} pemeriksaan lulus.`);
})().catch(e => { console.error('GAGAL:', e.message); process.exit(1); });
