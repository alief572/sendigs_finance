'use strict';
// Execute the actual inline calculation with a small jQuery adapter, without a browser.
const assert = require('assert');
const fs = require('fs');
const vm = require('vm');
const path = require('path');
const view = fs.readFileSync(path.join(__dirname, '../views/process.php'), 'utf8');
const script = view.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/<\?[\s\S]*?\?>/g, '1');
function scenario({balance, receipt, pph, choice = 'N', admin = '0', coa = '1106-01-02'}) {
    const values = {piutang_dagang_1: balance, penerimaan_1: receipt, biaya_admin_1: admin,
        remaining_pph_1: pph, pph23_dipotong: choice, kredit_bank_debit: '0', uang_masuk: receipt};
    const cells = new Set([`.td_kredit_${coa}_1`, '.td_kredit_1102-01-01_1', '.td_debit_7201-01-04_1']);
    function $(selector) {
        const match = selector.match(/^input\[name="([^"]+)"\]$/);
        const name = match ? match[1] : selector.replace(/^#/, '');
        return {length: cells.has(selector) ? 1 : 0,
            val: () => values[name],
            autoNumeric: (action, value) => { if (action === 'set') values[name] = value; },
            html: html => {
                const input = html.match(/name="([^"]+)" value="([^"]+)"/);
                if (input) values[input[1]] = input[2];
            }};
    }
    $.each = (items, callback) => items.forEach((item, index) => callback(index, item));
    const context = vm.createContext({$, console});
    new vm.Script(script).runInContext(context);
    context.hitungAll();
    return {values, recalculate: amount => { values.penerimaan_1 = amount; context.hitungAll(); }};
}
const last = scenario({balance:'2.574.990,00', receipt:'2.574.990,00', pph:'0.00'});
assert.strictEqual(last.values.sisa_piutang_1, 0);
assert.strictEqual(last.values['kredit_1102-01-01_1'], '2574990');
assert.strictEqual(last.values['kredit_1106-01-02_1'], '0');
const first = scenario({balance:'25.515.810,00', receipt:'23.409.000,00', pph:'468180.00'});
assert.strictEqual(first.values.sisa_piutang_1, 2574990);
assert.strictEqual(first.values['kredit_1106-01-02_1'], '468180');
first.recalculate('100.000,00');
assert.strictEqual(first.values['kredit_1106-01-02_1'], '100000');
first.recalculate('23.409.000,00');
assert.strictEqual(first.values['kredit_1106-01-02_1'], '468180');
const partial = scenario({balance:'5.000.000,00', receipt:'1.000.000,00', pph:'368180.00'});
assert.strictEqual(partial.values.sisa_piutang_1, 4368180);
const vuca = scenario({balance:'14.925.000,00', receipt:'15.000.000,00', pph:'75000.00', coa:'1106-01-05'});
assert.strictEqual(vuca.values.sisa_piutang_1, 0);
assert.strictEqual(vuca.values['kredit_1106-01-05_1'], '75000');
const deducted = scenario({balance:'100,50', receipt:'100,00', pph:'500.00', choice:'Y', admin:'0,50'});
assert.strictEqual(deducted.values.sisa_piutang_1, 0);
assert.strictEqual(deducted.values['kredit_1106-01-02_1'], '0');
const cents = scenario({balance:'2.574.990,50', receipt:'2.574.990,50', pph:'0.00'});
assert.strictEqual(cents.values.sisa_piutang_1, 0);
assert.strictEqual(cents.values.grand_total, 2574990.5);
assert.strictEqual(cents.values.kontrol, 0);
console.log('PASS: actual inline view settlement, first/partial installments, retyping, Vuca and deducted PPh with cents/admin');
