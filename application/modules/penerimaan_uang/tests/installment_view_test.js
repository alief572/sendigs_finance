'use strict';
// Execute the actual inline calculation with a small jQuery adapter, without a browser.
const assert = require('assert');
const fs = require('fs');
const vm = require('vm');
const path = require('path');
const view = fs.readFileSync(path.join(__dirname, '../views/process.php'), 'utf8');
const script = view.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/<\?[\s\S]*?\?>/g, '1');
function scenario({balance, receipt, pph, choice = 'N', admin = '0', coa = '1106-01-02', masuk = receipt, lebih = ''}) {
    const values = {piutang_dagang_1: balance, penerimaan_1: receipt, biaya_admin_1: admin,
        remaining_pph_1: pph, pph23_dipotong: choice, kredit_bank_debit: '0', uang_masuk: masuk, lebih_bayar_1: lebih};
    const cells = new Set([`.td_kredit_${coa}_1`, '.td_kredit_1102-01-01_1', '.td_debit_7201-01-04_1']);
    function api() { return {addClass: cls => { values.alert_class = cls; return api(); }, removeClass: () => api(), show: () => api()}; }
    function $(selector) {
        const match = selector.match(/^input\[name="([^"]+)"\]$/);
        const name = match ? match[1] : selector.replace(/^#/, '');
        return {length: cells.has(selector) ? 1 : 0,
            val: () => values[name],
            autoNumeric: (action, value) => { if (action === 'set') values[name] = value; },
            text: text => { values[name] = text; },
            toggle: shown => { values[name + '_shown'] = shown; },
            prop: (key, value) => { values[name + '_' + key] = value; },
            each: () => {}, show: () => { values[name + '_visible'] = true; },
            removeClass: () => api(), addClass: cls => { values[name + '_class'] = cls; return api(); },
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
const over = scenario({balance:'140.610.000,00', receipt:'140.610.000,00', pph:'0.00', masuk:'332.710.000,00'});
assert.strictEqual(over.values.kontrol, -192100000);
assert.ok(/Lebih Bayar/.test(over.values.kontrol_hint) && /192,100,000/.test(over.values.kontrol_hint) && /belum 0/.test(over.values.kontrol_title), 'Hint explains the excess bank money');
assert.strictEqual(over.values.alert_class, 'alert-danger');
const under = scenario({balance:'140.610.000,00', receipt:'140.610.000,00', pph:'0.00', masuk:'100.000.000,00'});
assert.ok(/melebihi uang masuk 40,610,000/.test(under.values.kontrol_hint));
assert.ok(/Kontrol 0/.test(cents.values.kontrol_title), 'Zero Kontrol shows the confirmation note');
const lebih = scenario({balance:'140.610.000,00', receipt:'140.610.000,00', pph:'0.00', masuk:'332.710.000,00', lebih:'192.100.000,00'});
assert.strictEqual(lebih.values.kontrol, 0, 'Penerimaan + Lebih Bayar = Uang Masuk');
assert.strictEqual(lebih.values.total_lebih, 192100000);
assert.strictEqual(lebih.values.footer_lebih, 192100000, 'Footer Lebih Bayar mirrors the total');
assert.strictEqual(lebih.values.footer_piutang, 140610000, 'Footer Total Piutang mirrors the Piutang Dagang total');
assert.strictEqual(lebih.values.sisa_piutang_1, 0, 'Lebih Bayar tidak memengaruhi sisa piutang');
assert.strictEqual(lebih.values.debit_bank_debit, '332710000', 'Bank debit = penerimaan + lebih bayar');
assert.strictEqual(lebih.values.kredit_lebih, '192100000', 'Kredit Deposit Customer = lebih bayar');
const SAVE = 'dialog-popup button[type="submit"]_disabled';
assert.strictEqual(lebih.values[SAVE], false, 'Save button enabled when Kontrol is 0 and the journal balances');
assert.strictEqual(over.values[SAVE], true, 'Save button stays disabled while Kontrol is not 0');
assert.strictEqual(under.values[SAVE], true, 'Save button stays disabled when receipt exceeds bank money');
assert.ok(/Kontrol 0/.test(lebih.values.kontrol_title) && /Periksa jurnal/.test(lebih.values.kontrol_hint), 'Zero Kontrol shows the confirmation note');
assert.strictEqual(lebih.values.alert_class, 'alert-info');
console.log('PASS: actual inline view settlement, first/partial installments, retyping, Vuca and deducted PPh with cents/admin');
