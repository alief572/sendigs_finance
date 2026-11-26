// Jalankan: node application/modules/monitoring_piutang/tests/Esc_client_test.js
// esc() di view piutang dipakai di dalam atribut "...", jadi tanda kutip wajib ikut di-escape (stored XSS).
const fs = require('fs'), path = require('path'), assert = require('assert');
const views = ['monitoring_piutang/views/index.php', 'follow_up_piutang/views/index.php', 'follow_up_piutang/views/plan_bayar.php'];
// Tiruan $('<div>').text(s).html(): serialisasi text node hanya meng-escape & < > (kutip dibiarkan), sama dengan browser.
const $ = () => { let t = ''; const el = {text: (s) => { t = s; return el; }, html: () => t.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')}; return el; };

const jahat = 'PT "A" \' <img src=x> & x" onmouseover="y';
views.forEach((v) => {
    const src = fs.readFileSync(path.join(__dirname, '../..', v), 'utf8');
    const m = src.match(/function esc\(s\) \{.*\}/);
    assert(m, v + ': fungsi esc ada');
    const esc = new Function('$', m[0] + '\nreturn esc;')($);
    const out = esc(jahat);
    assert(!/["'<>]/.test(out), v + ': tidak boleh ada kutip atau kurung sudut mentah: ' + out);
    assert.strictEqual(out.replace(/&quot;/g, '"').replace(/&#39;/g, "'").replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&'), jahat, v + ': teks asli utuh');
    assert.strictEqual(esc(null), '', v + ': null jadi kosong');
});
console.log('Esc client PASS');
