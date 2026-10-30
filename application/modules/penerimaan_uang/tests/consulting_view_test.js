'use strict';
const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const {spawnSync} = require('child_process');
const money = require('../../../../assets/js/penerimaan_uang/consulting');
const php = path.resolve(__dirname, '../../../../scratch/runtime/php74/php.exe');
const fixture = path.join(__dirname, 'render_consulting_fixture.php');
function runPhp(args, input) {
    const result = spawnSync(php, [fixture, ...args], {encoding: 'utf8', input});
    assert.strictEqual(result.status, 0, result.stdout + result.stderr);
    return JSON.parse(result.stdout);
}
const html = runPhp([]).html;
const nodes = {}, names = {}, events = {}, inputs = [];
function element(attrs = {}) {
    return {value: attrs.value || '', style: {}, children: [], textContent: '',
        set innerHTML(value) { this.children = []; this._html = value; },
        get innerHTML() { return this._html || ''; },
        getAttribute: name => attrs[name] || null,
        classList: {contains: name => (attrs.class || '').split(' ').includes(name)},
        appendChild(child) { this.children.push(child); }};
}
for (const match of html.matchAll(/<[^>]+>/g)) {
    const attrs = Object.fromEntries([...match[0].matchAll(/([\w-]+)="([^"]*)"/g)].map(m => [m[1], m[2]]));
    const node = element(attrs);
    if (attrs.id) nodes[attrs.id] = node;
    if (attrs.name) { names[attrs.name] = node; inputs.push({name: attrs.name, node}); }
    if ((attrs.class || '').startsWith('reset-pph-link-')) nodes['.' + attrs.class] = node;
}
const submit = {disabled: true};
const container = nodes['consulting-receipt-container'];
container.querySelector = selector => {
    const name = selector.match(/\[name="([^"]+)"\]/);
    return name ? names[name[1]] : nodes[selector];
};
container.addEventListener = (name, callback) => { events[name] = callback; };
const document = {
    getElementById: id => nodes[id],
    querySelector: () => submit,
    createElement: () => element()
};
function $(node) { return {autoNumeric(action, value) { assert.strictEqual(action, 'set'); node.value = value; }}; }
const script = html.match(/<script>([\s\S]*?)<\/script>/)[1];
new vm.Script(script).runInNewContext({document, $, ConsultingReceiptMoney: money, setTimeout: cb => cb()});
assert.strictEqual(submit.disabled, true, 'Initial bank difference requires allocation input');
names.biaya_admin_1.value = '2,500.00';
events.change({target: names.biaya_admin_1});
assert.strictEqual(submit.disabled, false, nodes['error-text'].textContent);
assert.strictEqual(nodes.input_kontrol.value, '0.00');
assert.strictEqual(nodes.tot_jurnal_debit.textContent, '222,000,000.00');
assert.strictEqual(nodes.tot_jurnal_kredit.textContent, '222,000,000.00');
assert.ok(nodes.tbody_preview_jurnal.children.every(row => row.children[0].textContent === '2026-10-26'));
const fields = money.normalizeFields(inputs.map(({name, node}) => ({name, value: node.value})));
const saved = runPhp(['--save'], JSON.stringify(Object.fromEntries(fields.map(f => [f.name, f.value]))));
assert.strictEqual(saved.result.status, 1, JSON.stringify(saved.result));
assert.strictEqual(saved.headers[0].nominal_penerimaan_bank, '218330833.33');
assert.strictEqual(saved.details[0].penerimaan, '222000000.00');
assert.strictEqual(saved.journals.length, nodes.tbody_preview_jurnal.children.length);
for (const [index, line] of saved.journals.entries()) {
    const cells = nodes.tbody_preview_jurnal.children[index].children;
    assert.strictEqual(cells[0].textContent, line.tgl_jurnal);
    assert.strictEqual(cells[1].textContent, line.coa);
    assert.strictEqual(cells[4].textContent, line.keterangan);
    assert.strictEqual(cells[5].textContent, Number(line.debit) > 0 ? Number(line.debit).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '-');
}
names.sisa_piutang_1.value = '1,000,000.00';
events.change({target: names.sisa_piutang_1});
assert.strictEqual(names.penerimaan_1.value, '221000000.00');
assert.strictEqual(submit.disabled, true);
names.penerimaan_1.value = '222,000,000.00';
events.keyup({target: names.penerimaan_1});
assert.strictEqual(names.sisa_piutang_1.value, '0.00');
assert.strictEqual(submit.disabled, false);
names.pph_actual_1.value = '3,666,666.68'; events.change({target: names.pph_actual_1});
assert.strictEqual(submit.disabled, true);
names.pph_actual_1.value = '3,666,666.67'; events.change({target: names.pph_actual_1});
assert.strictEqual(submit.disabled, false);
names.biaya_admin_1.value = 'garbage'; events.change({target: names.biaya_admin_1});
assert.strictEqual(submit.disabled, true);
assert.strictEqual(nodes.input_kontrol.value, '1');
console.log('PASS: actual PHP view script, integer reconciliation, paired inputs, validation, normalized payload save and matching journal preview');
