'use strict';
const assert = require('assert');
const path = require('path');
const {spawnSync} = require('child_process');
const calc = require(path.resolve(__dirname, '../../../../assets/js/invoicing/consulting.js'));
const php = process.argv[2];
if (!php) throw new Error('Pass PHP CLI executable as the first argument.');
const result = spawnSync(php, [path.join(__dirname, 'Consulting_calculation_test.php'), '--json'], {encoding: 'utf8'});
assert.strictEqual(result.status, 0, result.stderr);
let checks = 0;
for (const row of JSON.parse(result.stdout)) {
    assert.deepStrictEqual(calc.totals(row.amount, row.type), row.totals);
    checks++;
}
for (const value of ['-1', '1.005', '1,23', '', 'NaN', '10000000000000']) {assert.throws(() => calc.cents(value));checks++;}
assert.deepStrictEqual(calc.receipt(111000, 98000, 1000, 2000), {reduction: 101000, remaining: 10000, adjustment: 0});
assert.throws(() => calc.receipt(100, 100, 0, 1));
console.log(`PASS: ${checks + 2} PHP/JavaScript parity and receipt checks`);
