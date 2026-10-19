'use strict';
const assert = require('assert');
const path = require('path');
const {spawnSync} = require('child_process');
const calc = require(path.resolve(__dirname, '../../../../assets/js/invoicing/consulting.js'));
const fs = require('fs');
const defaultPhp = path.resolve(__dirname, '../../../../scratch/runtime/php74/php.exe');
const php = process.argv[2] || process.env.INVOICE_TEST_PHP || (fs.existsSync(defaultPhp) ? defaultPhp : 'php');
const result = spawnSync(php, [path.join(__dirname, 'Consulting_calculation_test.php'), '--json'], {encoding: 'utf8'});
assert.strictEqual(result.status, 0, result.stderr);
let checks = 0;
for (const row of JSON.parse(result.stdout)) {
    assert.deepStrictEqual(calc.totals(row.amount, row.type), row.totals);
    checks++;
}
for (const value of ['-1', '1.005', '1,23', '', 'NaN', '10000000000000', null, undefined, true, false, 'abc', '  ', '1e5', '-0.01']) {
    assert.throws(() => calc.cents(value));
    checks++;
}
assert.strictEqual(calc.cents('0.00'), 0); checks++;
assert.strictEqual(calc.cents('0.01'), 1); checks++;
assert.strictEqual(calc.cents('100.55'), 10055); checks++;
assert.throws(() => calc.totals('0', 'PKP')); checks++;
assert.throws(() => calc.totals('100', 'INVALID_TAX')); checks++;
assert.deepStrictEqual(calc.receipt(111000, 98000, 1000, 2000), {reduction: 101000, remaining: 10000, adjustment: 0}); checks++;
assert.deepStrictEqual(calc.receipt(10000, 10000, 0, 0), {reduction: 10000, remaining: 0, adjustment: 0}); checks++;
assert.deepStrictEqual(calc.receipt(10000, 0, 0, 10000), {reduction: 10000, remaining: 0, adjustment: 0}); checks++;
assert.throws(() => calc.receipt(100, 100, 0, 1)); checks++;
assert.throws(() => calc.receipt(100, -1, 0, 0)); checks++;
assert.throws(() => calc.receipt(100, 0, -1, 0)); checks++;
assert.throws(() => calc.receipt(100, 0, 0, -1)); checks++;
assert.throws(() => calc.receipt(-100, 50, 0, 0)); checks++;
assert.throws(() => calc.receipt(100, 0, 0, 0)); checks++;
assert.throws(() => calc.receipt(100, NaN, 0, 0)); checks++;
assert.throws(() => calc.receipt(100, undefined, 0, 0)); checks++;
assert.throws(() => calc.receipt(100, '100', 0, 0)); checks++;
assert.throws(() => calc.receipt(100, null, 0, 0)); checks++;
assert.throws(() => calc.receipt(100, 50, {}, 0)); checks++;
const accts = {
    receivable: {code: '1102-01-01', name: 'Piutang'},
    vat_domestic: {code: '2104-01-01', name: 'PPN DN'},
    vat_wapu_prepaid: {code: '1106-01-01', name: 'UM WAPU'},
    vat_wapu_payable: {code: '2104-01-02', name: 'Hutang WAPU'},
    sales_consulting: {code: '4101-01-01', name: 'Penjualan'}
};
for (const type of ['PKP', 'WAPU', 'KB']) {
    const t = calc.totals('1000', type);
    const j = calc.journal(t, accts, 'sales_consulting', 'INV-01', 'SPK-01');
    const debits = j.reduce((sum, r) => sum + r.debit, 0);
    const credits = j.reduce((sum, r) => sum + r.credit, 0);
    assert.strictEqual(debits, credits, 'Journal balanced for ' + type); checks++;
    assert.strictEqual(j.length, type === 'WAPU' ? 4 : (type === 'PKP' ? 3 : 2), 'Journal row count for ' + type); checks++;
    assert.ok(j[j.length - 1].description.includes('INV-01 - SPK-01'), 'Journal contains document numbers'); checks++;
    if (type === 'KB') {
        assert.ok(j[j.length - 1].description.endsWith(' - Kawasan Berikat'), 'KB journal has Kawasan Berikat suffix'); checks++;
    }
}
assert.throws(() => calc.journal(calc.totals('1000', 'PKP'), {}, 'sales_consulting', 'INV-01', 'SPK-01')); checks++;
console.log(`PASS: ${checks} PHP/JavaScript parity and receipt checks`);

