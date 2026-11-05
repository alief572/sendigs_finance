'use strict';
const assert=require('assert'), fs=require('fs'), path=require('path'), vm=require('vm'), cp=require('child_process');
const C=require('../../../../assets/js/invoicing/calculation.js');
const defaultPhp = path.resolve(__dirname, '../../../../scratch/runtime/php74/php.exe');
const php = process.env.INVOICE_TEST_PHP || (fs.existsSync(defaultPhp) ? defaultPhp : (fs.existsSync('C:/xampp74/php/php.exe') ? 'C:/xampp74/php/php.exe' : 'php'));
const cases=JSON.parse(cp.execFileSync(php,[path.join(__dirname,'calculation_test.php'),'--json'],{encoding:'utf8'}));
for(let n=0;n<cases.length;n++) {
    const expected=cases[n], vuca=n===cases.length-1, actual=C.totals(expected.dpp,expected.rate,vuca);
    for(const key of Object.keys(actual)) assert.strictEqual(actual[key],expected[key],key+' cents');
}
assert.strictEqual(C.item('1.5','100.25'),15038);
assert.throws(()=>C.item('-1','1.00'));
assert.throws(()=>C.cents('1.234,50',true));
for(const name of ['add_invoice.php','edit_invoice.php','add_invoice_vuca.php','edit_invoice_vuca.php','add_invoice_non_konsultasi.php','edit_invoice_non_konsultasi.php']) {
    const source=fs.readFileSync(path.join(__dirname,'../views',name),'utf8').replace(/<\?[\s\S]*?\?>/g,'null');
    for(const match of source.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)) new vm.Script(match[1].replace(/<\?[\s\S]*?\?>/g,'null'),{filename:name});
}
console.log('PASS: PHP/JavaScript parity, fractional items and all six form script syntax');
