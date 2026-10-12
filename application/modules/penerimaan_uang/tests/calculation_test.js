'use strict';
const assert = require('assert');
const C = require('../../../../assets/js/penerimaan_uang/calculation.js');
assert.strictEqual(C.cents('2.574.990,50'), 257499050);
assert.strictEqual(C.format(257499050), '2.574.990,50');
assert.strictEqual(C.format(-101), '-1,01');
assert.deepStrictEqual(C.calculate(2551581000,46818000,'N',2340900000,0), {adjustment:46818000,reduction:2294082000,remaining:257499000});
assert.deepStrictEqual(C.calculate(257499000,0,'N',257499000,0), {adjustment:0,reduction:257499000,remaining:0});
assert.deepStrictEqual(C.calculate(100000,20000,'N',10000,0), {adjustment:10000,reduction:0,remaining:100000});
assert.strictEqual(C.calculate(10050,500,'Y',10000,50).remaining,0);
assert.deepStrictEqual(C.calculate(1492500000,7500000,'N',1500000000,0), {adjustment:7500000,reduction:1492500000,remaining:0});
assert.strictEqual(C.calculate(1492500000,7500000,'Y',1492500000,0).remaining,0);
assert.throws(()=>C.calculate(1492500000,7500000,'Y',1500000000,0));
assert.throws(()=>C.cents('NaN'));
assert.throws(()=>C.calculate(100,0,'N',101,0));
console.log('PASS: preview money parsing and installment calculation');

const fs=require('fs'), path=require('path'), vm=require('vm');
for (const name of ['add.php','process.php','index.php','detail.php']) {
    const source=fs.readFileSync(path.join(__dirname,'../views',name),'utf8');
    for (const match of source.matchAll(/<script>([\s\S]*?)<\/script>/g)) {
        new vm.Script(match[1].replace(/<\?[\s\S]*?\?>/g,'null'),{filename:name});
    }
}
console.log('PASS: inline view JavaScript syntax');
