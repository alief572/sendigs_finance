// Jalankan: node application/modules/follow_up_piutang/tests/Tutorial_client_test.js
// Mesin tutorial (assets/js/piutang-tutorial.js) dengan driver.js dan DOM tiruan: `go` dijalankan sebelum langkahnya tampil,
// elemen dicari ulang setelah `go`, elemen yang tidak ada jadi popover tengah, dan Selesai menjalankan cleanup.
const fs = require('fs'), path = require('path'), assert = require('assert');
const src = fs.readFileSync(path.join(__dirname, '../../../../assets/js/piutang-tutorial.js'), 'utf8');

const els = {}; // selector -> elemen tiruan yang "terlihat"
const document = {
    querySelector: (s) => els[s] || null, getElementById: () => null, createElement: () => ({}), head: {appendChild: () => {}}
};
let drv = null, cfgSeen = null;
const timers = [];
const window = {
    alert: (m) => { throw new Error('alert: ' + m); }, console,
    driver: {js: {driver: (cfg) => {
        cfgSeen = cfg;
        drv = {idx: -1, steps: cfg.steps, destroyed: false,
            setSteps() { throw new Error('setSteps saat tur berjalan menumpuk popover'); },
            drive(i) { this.idx = i; }, moveNext() { this.idx++; }, movePrevious() { this.idx--; },
            getActiveIndex() { return this.idx; }, destroy() { this.destroyed = true; cfg.onDestroyed(); }};
        return drv;
    }}}
};
new Function('window', 'document', 'setTimeout', src)(window, document, (fn) => timers.push(fn));
const flush = () => { while (timers.length) { timers.shift()(); } };

const log = [];
let cleaned = false;
const steps = [
    {pg: 'Hal A', el: '#a', t: 'A', d: 'a', go: () => { log.push('goA'); els['#a'] = {getClientRects: () => [1]}; }},
    {el: '#tidak-ada', t: 'B', d: 'b'},
    {pg: 'Hal C', go: () => { log.push('goC'); els['#c'] = {getClientRects: () => [1]}; }, el: '#c', t: 'C', d: 'c'}
];
window.PiutangTour.run('Alur', steps, () => { cleaned = true; });
assert.deepStrictEqual(log, ['goA'], 'go langkah 1 dijalankan sebelum tampil');
flush();
assert.strictEqual(drv.idx, 0, 'langkah 1 tampil');
assert.strictEqual(drv.steps[0].element, els['#a'], 'elemen dicari setelah go');
assert.ok(drv.steps[0].popover.description.includes('Hal A'), 'nama halaman di popover');
cfgSeen.onNextClick(); flush();
assert.strictEqual(drv.idx, 1, 'langkah 2');
assert.strictEqual(drv.steps[1].element, undefined, 'elemen tidak ada = popover tengah');
assert.deepStrictEqual(log, ['goA', 'goA'], 'langkah tanpa go memakai go langkah sebelumnya');
cfgSeen.onNextClick(); flush();
assert.strictEqual(drv.idx, 2, 'langkah 3');
assert.strictEqual(drv.steps[2].element, els['#c'], 'elemen langkah 3 setelah go');
cfgSeen.onPrevClick(); flush();
assert.strictEqual(drv.idx, 1, 'kembali ke langkah 2');
cfgSeen.onNextClick(); flush(); cfgSeen.onNextClick();
assert.ok(drv.destroyed && cleaned, 'Selesai menutup tutorial dan menjalankan cleanup');
console.log('Tutorial client PASS');
