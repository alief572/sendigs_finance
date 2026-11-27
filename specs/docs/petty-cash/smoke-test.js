'use strict';
// Smoke test mockup Petty Cash to-be, alur penuh: Transaksi → Pelaporan (Saldo Petty Cash) → Approval (jurnal pencatatan)
// → Payment Hutang VUCA/SUSTAIN → Request Payment → Approval → Record → Payment per transfer → Jurnal All.
// Jalankan: node specs/docs/petty-cash/smoke-test.js
// jsdom dicari dari node_modules biasa, atau dari folder di env JSDOM_PATH.
const fs = require('fs');
const path = require('path');
const assert = require('assert');
let JSDOM;
try { ({JSDOM} = require(process.env.JSDOM_PATH || 'jsdom')); }
catch (e) { console.error('jsdom tidak ditemukan. Pasang: npm i jsdom, atau set JSDOM_PATH.'); process.exit(2); }

const html = fs.readFileSync(path.join(__dirname, 'index.html'), 'utf8').replace(/<script src=[^>]*><\/script>/g, '').replace(/<link [^>]*>/g, '');
const errors = [];
const dom = new JSDOM(html, {runScripts: 'dangerously', pretendToBeVisual: true,
    beforeParse(w) { w.scrollTo = () => {}; w.addEventListener('error', e => errors.push(e.message)); }});
const {window} = dom, doc = window.document;
const $ = id => doc.getElementById(id);
const q = s => doc.querySelector(s);
const qa = s => [...doc.querySelectorAll(s)];
const click = el => { assert.ok(el, 'elemen untuk diklik tidak ada'); el.dispatchEvent(new window.MouseEvent('click', {bubbles: true})); };
const setVal = (el, v, ev = 'input') => { assert.ok(el, 'input tidak ada'); el.value = v; el.dispatchEvent(new window.Event(ev, {bubbles: true})); };
const tickIn = (sel) => { const c = q(sel); assert.ok(c, 'checkbox ' + sel); c.checked = true; c.dispatchEvent(new window.Event('change', {bubbles: true})); };
const tick = no => tickIn(`#pcT [data-sel="${no}"]`);
const dlgOpen = () => $('dlg').classList.contains('on');
const ok_ = () => click($('dlgOk'));
const nav = g => click(q(`.sidebar [data-go="${g}"]`));
const S = () => window.eval('S');
const jurOf = no => S().jur.filter(j => j.trx === no);
const msSisa = id => { click(q(`#msT [data-msv="${id}"]`)); const t = $('msSisa').textContent; click($('dlgNo')); return t; };
let checks = 0;
const ok = (c, m) => { assert.ok(c, m); checks++; };

// ===== Data awal =====
ok(qa('#pcT tbody tr').length === 9 && $('c_pcp').textContent === '4', 'Transaksi: 9 pencatatan, 4 draft');
ok(qa('#pcT thead th').map(t => t.textContent.trim()).slice(1).join('|') === 'No|No Pencatatan|No Pelaporan|Tanggal|Company|Request By|Keterangan|Nominal|Status|Action', 'Kolom daftar pencatatan seperti sistem');
ok(/17\/11\/2026/.test(q('#pcT tr[data-row="PCP-2026-0101"]').textContent) && /Draft/.test(q('#pcT tr[data-row="PCP-2026-0101"] .label').textContent), 'Tanggal dd/mm/yyyy, status label');
ok(q('#pcT tr[data-row="PCP-2026-0101"] [data-pe]') && !q('#pcT tr[data-row="PCP-2026-0104"] [data-pe]'), 'Edit/hapus hanya untuk draft yang belum masuk pelaporan');
click(q('#pcT tr[data-row="PCP-2026-0101"] [data-pv]'));
ok(!$('pg-pcv').classList.contains('hidden') && /nota-0101\.jpg/.test($('pcvBody').textContent) && /Belum ada/.test($('pcvJs').textContent) && /Detail Pencatatan Petty Cash/.test($('pcvBody').textContent), 'View pencatatan (halaman): evidence, Journal Status belum ada');
click($('pcvBack'));
click(q('#pcT tr[data-row="PCP-2026-0090"] [data-pv]'));
ok(/dibuat saat simpan \(lama\)/.test($('pcvJs').textContent), 'Pencatatan lama: jurnal dibuat saat simpan, dibiarkan'); click($('pcvBack'));
nav('ms');
ok(qa('#msT thead th').map(t => t.textContent.trim()).join('|') === 'No|Nama|Keterangan|COA Petty Cash BARU|Action' && /1101-01-02 - Kas Kecil/.test(q('#msT tr[data-row="PC-01"]').textContent) && qa('#msT tbody tr').length === 1, 'Master: 1 master (project ini), kolom seperti sistem + COA Petty Cash (baru)');
ok(msSisa('PC-01') === 'Rp 2.650.000', 'View master: sisa budget = 5.000.000 − belum di-refill semua company (STM 1.350.000 + VUCA 1.000.000)');
click(q('#msT [data-mse="PC-01"]'));
{ const rows = qa('#dlgB tbody tr').slice(0, 3).map(tr => tr.textContent);
  ok(/Nama/.test(rows[0]) && /Keterangan/.test(rows[1]) && /COA Petty Cash/.test(rows[2]) && $('mfCoa').value === '1101-01-02' && qa('#mfRows tr').length === 3 && $('mfTot').textContent === 'Rp 5.000.000', 'Form master: Nama, Keterangan, COA Petty Cash (di bawah Keterangan), Detail COA, Total Budget'); }
setVal($('mfCoa'), '', 'change'); ok_();
ok(/COA Petty Cash wajib/.test($('dlgErr').textContent), 'COA Petty Cash wajib dipilih');
setVal($('mfCoa'), '1101-01-03', 'change'); ok_(); ok_();
ok(/1101-01-03 - Kas Kecil Proyek/.test(q('#msT tr[data-row="PC-01"]').textContent), 'COA Petty Cash tersimpan');
{ const L = window.eval("jurLines(pc('PCP-2026-0101'))"); ok(L[0].lines[L[0].lines.length - 1].coa === '1101-01-03' && L[0].lines[L[0].lines.length - 1].c === 350000, 'Jurnal pencatatan memakai COA Petty Cash master (Kredit)'); }
click(q('#msT [data-mse="PC-01"]')); setVal($('mfCoa'), '1101-01-02', 'change'); ok_(); ok_();
click($('msAdd')); setVal($('mfNama'), 'Petty Cash Gudang'); setVal($('mfKet'), 'Kas kecil gudang'); ok_();
ok(/COA Petty Cash wajib/.test($('dlgErr').textContent), 'Tambah master: COA Petty Cash wajib'); click($('dlgNo'));

// ===== Form pencatatan (halaman penuh seperti sistem) =====
nav('pcp');
const nJ0 = S().jur.length;
click($('pcAdd')); ok(qa('#dlgB [data-ms]').length === 1 && /Budget: Rp 5\.000\.000/.test($('dlgB').textContent), 'Pilih Master Petty Cash: 1 master, kartu budget'); click(q('#dlgB [data-ms="PC-01"]'));
ok(!$('pg-pcf').classList.contains('hidden') && $('sbBud').textContent === '5.000.000' && qa('#pcfT thead th').map(t => t.textContent.trim()).join('|') === 'No|COA|Pengeluaran|Spesifikasi|Jumlah|Nominal|Total|Evidence *|Aksi', 'Form: halaman penuh, small-box budget, kolom detail seperti sistem');
ok(q('#pcfBody input[value="Auto-generated"]') && $('pcfCo').value === '' && $('pcfKet').placeholder === 'Keterangan (opsional)', 'No Pencatatan auto, Company dipilih, Keterangan opsional');
click($('pcfSave'));
ok(/Validasi Gagal/.test($('dlgB').textContent) && /Company wajib dipilih/.test($('dlgB').textContent) && /Request By wajib diisi/.test($('dlgB').textContent) && /Baris 1: Evidence wajib diupload/.test($('dlgB').textContent), 'Validasi seperti sistem (Company, Request By, Jumlah, Nominal, Evidence)'); ok_();
setVal($('pcfCo'), 'VUCA', 'change'); setVal($('pcfBy'), 'Dewi');
setVal(q('#pcfRows [data-f="coa"]'), '6101-03-01', 'change');
ok(q('#pcfRows [data-f="peng"]').value === 'ATK', 'Pilih COA mengisi Pengeluaran (jenis pengeluaran master)');
setVal(q('#pcfRows [data-f="qty"]'), '2');
// ketik nominal satu per satu: field tidak di-render ulang, fokus tetap
{ const nom = q('#pcfRows [data-f="nom"]'); let v = '';
  for (const ch of '50000') { v += ch; setVal(nom, v); }
  ok(q('#pcfRows [data-f="nom"]') === nom && nom.value === '50000' && $('pcfTot0').value === '100.000' && $('pcfGrand').textContent === '100.000', 'Ketik nominal 50000 langsung: field yang sama, total ikut terhitung');
  nom.dispatchEvent(new window.Event('blur')); ok(nom.value === '50.000', 'Keluar dari field: nominal diformat 50.000'); }
ok(/Sisi VUCA/.test($('pcfJv').textContent) && /Sisi STM \(Inter-Company\)/.test($('pcfJv').textContent) && /2103-01-01/.test($('pcfJv').textContent), 'Simulasi Jurnal: Sisi VUCA + Sisi STM (Inter-Company)');
ok(/TOTAL COMPANY/.test($('pcfJv').textContent) && /Hutang ke STM atas petty cash/.test($('pcfJv').textContent) && /seluruh company seimbang/.test($('pcfJv').textContent), 'PC-R-10: jurnal 2 company per sisi + TOTAL COMPANY + keterangan per baris');
setVal(q('#pcfRows [data-f="nom"]'), '2000000');
ok($('pcfWarn').style.display === '' && /Grand Total melebihi Sisa Budget/.test($('pcfWarn').textContent), 'Peringatan Grand Total > Sisa Budget (tidak menolak)');
setVal(q('#pcfRows [data-f="nom"]'), '25.000');
click(q('#pcfRows [data-up]'));
ok(/nota-1\.jpg/.test($('pcfRows').textContent) && q('#pcfRows [data-f="nom"]').value === '25.000', 'Upload evidence; isian tetap');
click($('pcfSave'));
ok(/PCP-2026-0107/.test($('dlgB').textContent) && S().jur.length === nJ0 && S().pcp.find(p => p.no === 'PCP-2026-0107').co === 'VUCA' && S().pcp.find(p => p.no === 'PCP-2026-0107').items[0].nom === 25000, 'Simpan: draft tanpa jurnal, company VUCA, nominal 25.000'); ok_();
ok(/VUCA/.test(q('#pcT tr[data-row="PCP-2026-0107"]').textContent), 'Daftar pencatatan: company dari pencatatan');
click(q('#pcT tr[data-row="PCP-2026-0107"] [data-pe]'));
ok(/Edit Pencatatan Petty Cash/.test($('pcfBody').textContent) && q('#pcfRows [data-f="nom"]').value === '25.000', 'Edit: form terisi');
setVal($('pcfKet'), 'Fotokopi modul training'); click($('pcfSave'));
ok(/Tidak ada jurnal yang dihapus/.test($('dlgB').textContent) && S().jur.length === nJ0, 'Edit: tanpa hapus-buat ulang jurnal'); ok_();
click(q('#pcT tr[data-row="PCP-2026-0107"] [data-pd]')); ok_(); ok_();
ok(!q('#pcT tr[data-row="PCP-2026-0107"]'), 'Hapus draft');

// ===== Buat pelaporan (seperti sistem) → Ajukan dari Pelaporan + Saldo Petty Cash =====
tick('PCP-2026-0101');
ok(q('#pcT tr[data-row="PCP-2026-0104"] input').disabled && !q('#pcT tr[data-row="PCP-2026-0106"]').classList.contains('row-lock'), 'Hanya company yang dikunci di daftar (seperti sistem)');
tick('PCP-2026-0106'); click($('pcBuat'));
ok(/minggu yang sama/.test($('dlgB').textContent), 'Beda minggu ditolak saat Buat Pelaporan'); ok_();
tickIn('#pcT [data-sel="PCP-2026-0106"]'); q('#pcT [data-sel="PCP-2026-0106"]').checked = false; q('#pcT [data-sel="PCP-2026-0106"]').dispatchEvent(new window.Event('change', {bubbles: true}));
tick('PCP-2026-0102'); tick('PCP-2026-0103');
ok(/Buat Pelaporan 3/.test($('pcBuat').textContent), 'Tombol Buat Pelaporan menampilkan jumlah');
click($('pcBuat')); ok(/Buat Pelaporan\?/.test($('dlgB').textContent), 'Konfirmasi Buat Pelaporan'); ok_();
ok(/RPC-2026-0043/.test($('dlgB').textContent) && /Detail Pelaporan/.test($('cfTitle').textContent), 'Pelaporan draft dibuat, tampil detail'); ok_();
nav('rpc');
ok(qa('#rpcT thead th').map(t => t.textContent.trim()).join('|') === 'No|No Pelaporan|Periode|Company|Pencatatan|Total periode (Rp)|Saldo Petty Cash BARU|Status|Aksi' && /16 Nov 2026 - 20 Nov 2026/.test(q('#rpcT tr[data-row="RPC-2026-0043"]').textContent), 'Daftar Pelaporan seperti sistem (+ kolom Saldo Petty Cash)');
click(q('#rpcT tr[data-row="RPC-2026-0043"] [data-cf]'));
ok(/RPC-2026-0043/.test($('cfBody').textContent) && /Rp 1\.300\.000/.test($('cfBody').textContent) && /Konfirmasi Pelaporan/.test($('cfTitle').textContent), 'Ajukan → halaman Konfirmasi');
ok($('kSeh').textContent === '2.650.000', 'Saldo seharusnya = 5.000.000 − 1.300.000 − 50.000 (STM) − 1.000.000 (VUCA belum di-refill)');
ok(qa('#cfDet .jvT').length === 3 && /Preview Jurnal/.test($('cfDet').textContent) && /17\/11\/2026/.test($('cfDet').textContent) && /Jurnal STM/.test($('cfDet').textContent), 'Preview jurnal per pencatatan (tampilan sistem), tanggal pencatatan');
click($('cfAjukan'));
ok(/Saldo Petty Cash wajib diisi/.test($('dlgB').textContent), 'Saldo Petty Cash wajib saat Ajukan'); ok_();
setVal($('cfSaldo'), '2600000');
ok(/-50\.000/.test($('kSel').textContent), 'Selisih langsung tampil');
ok($('selWarn') && /Ada selisih saldo Rp -50\.000/.test($('selWarn').textContent) && /tetap bisa diajukan/.test($('selWarn').textContent), 'Peringatan selisih tampil di konfirmasi');
setVal($('cfSaldo'), '2650000'); ok(!$('selWarn'), 'Tanpa selisih: tidak ada peringatan'); setVal($('cfSaldo'), '2600000');
click($('cfAjukan')); ok(/Waiting Approval/.test($('dlgB').textContent) && /selisih -50\.000/.test($('dlgB').textContent) && /Dept Head Finance/.test($('dlgB').textContent), 'Konfirmasi Ajukan (teks sistem) + selisih + peringatan'); ok_(); ok_();
ok(/2\.600\.000/.test(q('#rpcT tr[data-row="RPC-2026-0043"]').textContent) && /Waiting/.test(q('#rpcT tr[data-row="RPC-2026-0043"] .label').textContent), 'Daftar Pelaporan: saldo + status Waiting');
q('#rpcCo').options[1].selected = true; click($('rpcGo'));
ok(qa('#rpcT tbody tr').length === 2, 'Filter company (Terapkan filter)'); click($('rpcReset'));
click(q('#rpcT tr[data-row="RPC-2026-0043"] [data-pr]'));
ok(/LAPORAN PETTY CASH/.test($('prDoc').textContent) && /2\.600\.000/.test($('prDoc').textContent) && /-50\.000/.test($('prDoc').textContent), 'Print pelaporan dengan kontrol saldo'); click($('dlgNo'));

// ===== Approval STM =====
nav('apv');
ok(qa('#apvT tbody tr').length === 2 && !/Approver/.test($('apvT').textContent) && /Approval/.test(q('#apvT [data-ap]').textContent), 'Approval: 2 pelaporan, kolom seperti sistem (tanpa Approver), tombol Approval');
ok(q('#apvT tr[data-row="RPC-2026-0043"]').classList.contains('row-sel') && /Perlu dicek/.test(q('#apvT tr[data-row="RPC-2026-0043"]').textContent), 'Daftar Approval: baris berselisih disorot, Perlu dicek');
click(q('#apvT [data-ap="RPC-2026-0043"]'));
ok(q('#apvJv') && /3 jurnal/.test($('apvJv').textContent) && qa('#apvJv .jvT').length === 3 && /1101-01-02 Kas Kecil/.test($('apvJv').textContent) && /17\/11\/2026/.test($('apvJv').textContent) && /Request Payment refill Rp 1\.300\.000/.test($('apvJv').textContent) && !qa('#cfDet .jvT').length, 'Approval: Preview Jurnal tersendiri (3 jurnal, tanggal pencatatan, COA Petty Cash master, info refill)');
ok(qa('#apvJv .jvT')[0].querySelectorAll('thead th').length === 9 && [...qa('#apvJv .jvT')[0].querySelectorAll('thead th')].map(t => t.textContent).join('|') === 'Tanggal|Tipe|Company|No. COA|Nama COA|Keterangan|No. Reff|Debit|Kredit' && /No\. Transaksi/.test($('apvJv').textContent) && /GRAND TOTAL/.test($('apvJv').textContent) && /Pengeluaran petty cash PCP-/.test($('apvJv').textContent), 'PC-R-10: preview jurnal pakai template Jurnal All + keterangan per baris');
ok(/nota-0101\.jpg/.test($('cfDet').textContent), 'Approval: evidence per item tampil');
ok($('kSeh').textContent === '2.650.000' && !$('cfSaldo'), 'Approver melihat kontrol, tidak bisa diubah');
ok($('selWarn') && /Dept Head Finance wajib mengecek/.test($('selWarn').textContent), 'Halaman Approval: peringatan untuk Dept Head Finance');
const nJ1 = S().jur.length;
click($('apOk')); ok(/Approve Pelaporan\?/.test($('dlgB').textContent) && /selisih saldo -50\.000/.test($('dlgB').textContent), 'Konfirmasi approve menyebut selisih'); ok_();
ok(/3 jurnal pencatatan/.test($('dlgB').textContent) && S().jur.length === nJ1 + 3, 'Approve: 3 jurnal pencatatan'); ok_();
ok(jurOf('PCP-2026-0101')[0].tgl === '2026-11-17' && jurOf('PCP-2026-0103')[0].tgl === '2026-11-19' && jurOf('PCP-2026-0101')[0].sts === 'pending', 'Tanggal jurnal = tanggal pencatatan, pending');
ok(S().rp.find(x => x.no === 'RPC-2026-0043').jumlah === 1300000, 'Request Payment refill = Grand Total RPC');

// ===== Approval VUCA → Payment Hutang draft =====
click(q('#apvT [data-ap="RPC-2026-0042"]')); click($('apOk')); ok_();
ok(/PHP-2026-0012/.test($('dlgB').textContent), 'Approve VUCA: Payment Hutang PHP-2026-0012 dibuat'); ok_();
ok(jurOf('PCP-2026-0104').map(j => j.buku).join() === 'VUCA,STM' && jurOf('PCP-2026-0104')[0].lines.some(l => l.coa === '2103-01-01'), 'VUCA: jurnal company + jurnal STM per pencatatan');

// ===== Reject: tanpa jurnal =====
nav('pcp'); tick('PCP-2026-0106'); click($('pcBuat')); ok_(); ok_();
nav('rpc'); click(q('#rpcT tr[data-row="RPC-2026-0044"] [data-cf]')); setVal($('cfSaldo'), '2650000'); click($('cfAjukan')); ok_(); ok_();
nav('apv'); click(q('#apvT [data-ap="RPC-2026-0044"]'));
const nJ2 = S().jur.length;
click($('apNo')); setVal($('rjAl'), 'kurang'); ok_(); ok(/minimal 10/.test($('dlgErr').textContent), 'Alasan reject min. 10 karakter');
setVal($('rjAl'), 'Nota parkir tidak terbaca'); ok_();
ok(S().jur.length === nJ2 && S().pcp.find(p => p.no === 'PCP-2026-0106').sts === 'draft', 'Reject: pencatatan kembali draft, tanpa jurnal'); ok_();
nav('rpc'); ok(/Alasan penolakan/.test(q('#rpcT tr[data-row="RPC-2026-0044"]').textContent), 'Daftar Pelaporan: alasan penolakan tampil');

// ===== Petty Cash VUCA/Sustain (seperti sistem) =====
nav('php');
ok(qa('#phpT thead th').map(t => t.textContent.trim()).join('|') === 'No|No Pelaporan|No Payment Hutang|Periode|Company|Jumlah Pencatatan|Grand Total Periode|Status|Action', 'Kolom Petty Cash VUCA/Sustain seperti sistem');
ok(/Waiting Payment/.test(q('#phpT tr[data-row="PHP-2026-0011"]').textContent) && q('#phpT tr[data-row="PHP-2026-0012"] [data-ph]'), 'PHP-0011 waiting payment, PHP-0012 draft');
click(q('#phpT [data-ph="PHP-2026-0012"]'));
ok(!$('pg-phc').classList.contains('hidden') && /PHP-2026-0012/.test($('phcBody').textContent) && $('phcGo'), 'Payment Hutang → halaman konfirmasi');
click($('phcGo')); ok(/Data akan dikirim ke Request Payment/.test($('dlgB').textContent), 'Konfirmasi Proses Payment Hutang (teks sistem)'); ok_();
ok(/masuk Request Payment/.test($('dlgB').textContent) && S().rp.find(x => x.no === 'PHP-2026-0012').sts === 'wait', 'Payment Hutang → dokumen Request Payment (Petty Cash Hutang)'); ok_();
setVal($('phpSts'), 'draft', 'change'); click($('phpGo')); ok(/No data/.test($('phpT').textContent), 'Filter status'); click($('phpReset'));

// ===== Request Payment → Atur Pembayaran (konsep terbaru) =====
nav('rq');
ok(qa('#rqT [data-rq]').length === 3 && $('c_rq').textContent === '3', 'Request Payment: 3 dokumen (refill RPC-0043, RPC-0042, PHP-0012)');
ok(q('#rqT tr[data-row="RPC-2026-0042"]').children[5].textContent === 'STM' && /436\.300\.9911/.test(q('#rqT tr[data-row="RPC-2026-0042"]').textContent), 'Refill pelaporan VUCA: company STM, rekening pemegang petty cash');
tickIn('#rqT [data-rq="RPC-2026-0043"]');
ok(q('#rqT [data-rq="PHP-2026-0012"]').disabled, 'Dokumen company lain dikunci');
tickIn('#rqT [data-rq="RPC-2026-0042"]'); click($('rqAtur'));
ok(!$('pg-atur').classList.contains('hidden') && qa('#aturBody .trf-blk').length === 2 && $('aRek0no').value === '436.300.9911', 'Atur Pembayaran: 1 dokumen = 1 transfer, rekening dari dokumen');
tickIn('#aturBody [data-ap="RPC-2026-0043"]'); tickIn('#aturBody [data-ap="RPC-2026-0042"]'); click($('aturMerge'));
ok(qa('#aturBody .trf-blk').length === 1 && $('aRek0no').value === '', 'Gabung jadi 1 transfer, rekening dikosongkan');
click($('aturOk')); ok(/Rekening tujuan belum lengkap/.test($('dlgB').textContent), 'Rekening tujuan wajib'); ok_();
setVal($('aRek0bank'), 'BCA', 'change'); setVal($('aRek0no'), '436.300.9911'); setVal($('aRek0an'), 'Erna Susanti');
setVal($('aAdm0'), '2500', 'change'); setVal($('aBr0'), 'perusahaan', 'change');
ok(/Keluar bank 2\.302\.500/.test($('aturBody').textContent) && $('aRek0no').value === '436.300.9911', 'Admin 1x per transfer, ditanggung perusahaan');
click($('aturOk')); ok(/PGJ-0080/.test($('dlgB').textContent) && /Approval Request Payment/.test($('dlgB').textContent), 'Ajukan → PGJ-0080'); ok_();
ok(/Approval RP PGJ-0080/.test(window.eval("refillTxt(rpcOf('RPC-2026-0043'))")), 'Status refill mengikuti Request Payment');
nav('rq'); tickIn('#rqT [data-rq="PHP-2026-0012"]'); click($('rqAtur'));
ok($('aRek0no').value === '436.300.5287', 'Rekening tujuan Payment Hutang = rekening STM'); click($('aturOk')); ok_();

// ===== Approval Request Payment + Record =====
nav('rapv');
ok(qa('#rapvT tbody tr').length === 2, 'Approval Request Payment: 2 PGJ');
click(q('#rapvT [data-rv="PGJ-0081"]')); click(q('#rvDocT [data-rj="PHP-2026-0012"]'));
ok(/Semua dokumen akan ditolak/.test($('rvTrfT').textContent), 'Reject dokumen: transfer batal');
click($('rvSave')); ok_(); ok(/Alasan reject wajib/.test($('dlgErr').textContent), 'Alasan reject wajib');
setVal($('rvAl'), 'Nominal perlu dicek ulang'); ok_(); ok_();
nav('rq'); ok(/ditolak: Nominal perlu dicek ulang/.test(q('#rqT tr[data-row="PHP-2026-0012"]').textContent), 'Dokumen ditolak kembali ke Request Payment dengan alasan');
nav('rapv'); click(q('#rapvT [data-rv="PGJ-0080"]'));
ok(qa('#rvTrfT tbody tr').length === 1 && /2\.302\.500/.test($('rvTrfT').textContent), 'Rincian Transfer: 1 transfer, keluar bank 2.302.500');
click($('rvSave')); ok_(); ok(/1 transfer disetujui/.test($('dlgB').textContent), 'PGJ-0080 disetujui'); ok_();
nav('rrec'); ok(/Belum dibayar 1 dari 1/.test(q('#rrecT tr[data-row="PGJ-0080"]').textContent) && /Ditolak/.test(q('#rrecT tr[data-row="PGJ-0081"]').textContent), 'Record: status pembayaran per PGJ');
click(q('#rrecT [data-rr="PGJ-0080"]')); ok(/Belum/.test($('dlgB').textContent), 'View record: status bayar per transfer'); click($('dlgNo'));

// ===== Payment refill (per transfer, 1 nomor BK) =====
nav('pay'); ok($('c_pay').textContent === '2', 'Payment List: 2 transfer menunggu');
click($('payNew')); click(q('#dlgB [data-tp="nonpr"]'));
ok(qa('#prqT [data-pt]').length === 2 && $('prqGo').disabled, 'List Request Payment per transfer: 2');
tickIn('#prqT [data-pt="PGJ-0080|T1"]'); click($('prqGo'));
ok(/BK-1126-0023/.test($('pfNoTrx').textContent) && /1 nomor/.test($('pfNoTrx').textContent) && /2 dokumen/.test($('pfNoTrx').textContent), '1 payment = 1 nomor BK (2 dokumen)');
click($('pfSubmit')); ok(/Pilih Bank/.test($('dlgB').textContent) && /Bukti bayar/.test($('dlgB').textContent), 'Bank & bukti bayar wajib'); ok_();
setVal($('pfBank'), '1101-02-09', 'change'); click($('pfUp'));
{ const t = $('pfJv').textContent; ok(/1101-01-02/.test(t) && /Administrasi Bank/.test(t) && /Bank BCA STM/.test(t) && $('pfOut').textContent === '2.302.500', 'Jurnal refill: Dr COA Petty Cash per dokumen, Dr Administrasi Bank, Cr bank yang dipilih'); }
setVal($('pfCharge'), '6500', 'change'); click($('pfSubmit')); ok(/Keterangan Pembayaran wajib/.test($('dlgB').textContent), 'Bank Charge beda → keterangan wajib'); ok_();
setVal($('pfCharge'), '2500', 'change');
click($('pfSubmit')); ok(/BK-1126-0023/.test($('dlgB').textContent), 'Payment refill tersimpan'); ok_();
{ const J = jurOf('BK-1126-0023'); ok(J.length === 1 && J[0].jenis === 'Refill Pettycash' && J[0].lines.filter(l => l.coa === '1101-01-02').length === 2 && J[0].lines.find(l => l.coa === '1101-02-09').c === 2302500, 'Jurnal refill: 1 nomor BK untuk 2 dokumen'); }
nav('ms');
ok(msSisa('PC-01') === 'Rp 4.950.000', 'Setelah refill RPC-0043 + RPC-0042 dibayar, Sisa Budget kembali (4.950.000)');
nav('rpc');
ok(/Dibayar/.test(window.eval("refillTxt(rpcOf('RPC-2026-0043'))")) && /BK-1126-0023/.test(window.eval("refillTxt(rpcOf('RPC-2026-0042'))")), 'Status refill Dibayar + nomor BK');

// ===== Payment hutang (bank dipilih saat Payment) =====
nav('pay'); click($('payNew')); click(q('#dlgB [data-tp="nonpr"]')); tickIn('#prqT [data-pt="PGJ-0079|T1"]'); click($('prqGo'));
ok($('pfIc') && /2103-01-01 Hutang ke STM/.test($('pfBody').textContent) && $('pfBankStm').value === '1101-02-09' && /BCA VUCA/.test($('pfBank').textContent), 'Form payment hutang: pilihan bank VUCA, bank STM default dari rekening tujuan, COA Hutang ke STM');
setVal($('pfBank'), '1101-02-01', 'change'); click($('pfUp')); click($('pfSubmit'));
ok(/Done Payment/.test($('dlgB').textContent), 'Payment hutang tersimpan'); ok_();
nav('php');
ok(/Paid/.test(q('#phpT tr[data-row="PHP-2026-0011"]').textContent) && S().php.find(h => h.no === 'PHP-2026-0011').sts === 'done payment', 'Payment Hutang: Paid (done payment)');
const ph = S().jur.filter(j => j.jenis === 'Payment Hutang Petty Cash');
ok(ph.length === 2 && ph[0].trx === ph[1].trx && /^BK-1126-0024$/.test(ph[0].trx) && ph[0].buku === 'VUCA' && ph[0].lines[0].coa === '2103-01-01' && ph[0].lines[1].coa === '1101-02-01' && ph[1].lines[0].coa === '1101-02-09' && ph[1].lines[1].coa === '1103-01-01', 'Jurnal payment hutang: 1 nomor BK; VUCA Dr 2103-01-01 / Cr bank VUCA yang dipilih; STM Dr bank STM / Cr 1103-01-01');
ok(!S().jur.some(j => j.lines.some(l => l.coa === '2203-01-01')), 'Tidak ada lagi 2203-01-01 Hutang Hubungan Istimewa');
nav('pay'); ok(qa('#payT tbody tr').length === 4, 'Payment List: 4 payment (2 lama + 2 baru)');

// ===== Jurnal All =====
nav('jur');
setVal($('jurJn'), 'Petty Cash', 'change');
ok(qa('#jurT tbody tr').every(tr => tr.children[2].textContent === 'Petty Cash'), 'Filter jenis');
setVal($('jurJn'), '', 'change'); setVal($('jurSt'), 'pending', 'change');
const nP = qa('#jurT tbody tr').length;
click(q('#jurT [data-post]'));
ok(qa('#jurT tbody tr').length === nP - 1, 'Posting jurnal');
ok(S().jur.every(j => j.lines.reduce((a, l) => a + l.d - l.c, 0) === 0), 'Semua jurnal balance');
setVal($('jurSt'), '', 'change'); click(q('#jurT [data-jv]')); ok(q('#dlgB .jvT'), 'Lihat jurnal'); click($('dlgNo'));

// ===== Ringkasan & reset =====
click($('navSum'));
ok(/Tidak ada pertanyaan terbuka/.test($('sumQ').textContent) && /kas kecil STM/.test($('pg-sum').textContent), 'Ringkasan: semua pertanyaan terjawab (PC-R-09)');
click($('btnReset')); ok_();
ok(S().jur.length === 6 && S().rpc.length === 3 && S().pay.length === 2, 'Reset data');

// ===== Tutorial v2 =====
{
const TC = window.eval('TOUR_CFG');
ok(q('.tour-bar #tourBtn'), 'Tutorial: tombol di baris atas konten');
click($('tourBtn'));
ok(!$('tourMenu').hidden && $('tourMenu').querySelectorAll('[data-tour]').length === TC.flows().length + 1, 'Menu Tutorial');
const tmiss = [];
const hid = el => !el || el.closest('.hidden,[hidden]') || (el.closest('#dlg') && !el.closest('#dlg').classList.contains('on'));
for (const f of TC.flows()) { f.steps.forEach((s, i) => { if (s.go) s.go(); if (hid(q(s.el))) tmiss.push(f.title + ' #' + (i + 1) + ' ' + s.el); }); TC.cleanup(); }
ok(!tmiss.length, 'Setiap langkah alur menunjuk elemen yang tampil: ' + tmiss.join('; '));
ok(S().jur.length === 6 && S().pay.length === 2 && S().pcp.length === 9, 'Tutorial tidak menyimpan data');
for (const p of ['pcp', 'rpc', 'apv', 'php', 'rq', 'rapv', 'rrec', 'pay', 'jur']) { nav(p); ok(window.tourDef()[p].every(s => q(s.element)), 'Tutorial halaman ' + p); }
click($('tourBtn')); click($('tourMenu').querySelector('[data-tour="0"]'));
ok(dlgOpen() && /belum termuat/.test($('dlgB').textContent), 'Tanpa driver.js: pesan tutorial belum termuat'); ok_();
}
ok(!errors.length, 'Tidak ada error JS: ' + errors.join('; '));
console.log(`OK — ${checks} pemeriksaan lulus.`);
