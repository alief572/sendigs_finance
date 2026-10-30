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
const html = runPhp(['--kwitansi']).html;
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
assert.ok(html.includes('readonly'));
assert.strictEqual(names.pph_actual_1.value,'0.00');assert.strictEqual(names.dep_1.value,'0.00');assert.strictEqual(names.sisa_piutang_1.value,'0.00');
names.biaya_admin_1.value='5000.00';events.change({target:names.biaya_admin_1});assert.strictEqual(submit.disabled,false,nodes['error-text'].textContent);
assert.strictEqual(nodes.tot_jurnal_debit.textContent,'20,000.00');assert.strictEqual(nodes.tot_jurnal_kredit.textContent,'20,000.00');
assert.strictEqual(nodes.tbody_preview_jurnal.children.length,3);
names.biaya_admin_1.value='10000.01';events.change({target:names.biaya_admin_1});assert.strictEqual(submit.disabled,true);
console.log('PASS: rendered kwitansi modal, full allocation, zero forbidden fields, admin cap and balanced bank/admin/receivable journal');
