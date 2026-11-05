'use strict';
const assert = require('assert');
const money = require('../../../../assets/js/penerimaan_uang/consulting.js');
assert.strictEqual(money.cents('222,000,000.00'), 22200000000);
assert.strictEqual(money.cents('0.01'), 1);
assert.strictEqual(money.decimal(22200000000), '222000000.00');
for (const value of ['1,23.00', '1.234,56', '12garbage', '0.001', 'Infinity']) {
    assert.throws(() => money.cents(value));
}
const fields = money.normalizeFields([
    {name: 'penerimaan_1', value: '222,000,000.00'},
    {name: 'pph_actual_1', value: '3,666,666.67'},
    {name: 'biaya_admin_1', value: '2,500.00'},
    {name: 'sisa_piutang_1', value: '0.00'},
    {name: 'dep_1', value: '10,000,000.00'},
    {name: 'lebih_bayar_1', value: '5,000,000.00'},
    {name: 'deposit_amount', value: '10,000,000.00'},
    {name: 'id_inv_1', value: '00123'}
]);
assert.deepStrictEqual(fields.map(f => f.value), ['222000000.00', '3666666.67', '2500.00', '0.00', '10000000.00', '5000000.00', '10000000.00', '00123']);
assert.strictEqual(money.cents('222000000') - money.cents('3666666.67') - money.cents('2500'), 21833083333);
console.log('PASS: exact cents, strict grouped input and actual form monetary fields normalized');
