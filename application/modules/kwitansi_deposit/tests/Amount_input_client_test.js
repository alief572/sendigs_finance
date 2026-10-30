const assert=require('assert'),fs=require('fs'),path=require('path'),vm=require('vm');
const source=fs.readFileSync(path.join(__dirname,'../views/add.php'),'utf8');
const script=source.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/<\?=[\s\S]*?\?>/g,'/endpoint');
new vm.Script(script);
const start=script.indexOf('function formatCurrencyInput(input)');
const end=script.indexOf("$('#nilai_kwitansi').on('input'",start);
const context={};vm.createContext(context);vm.runInContext(script.slice(start,end),context);
for(const [raw,expected] of [['1500000','1.500.000'],['1500000,25','1.500.000,25'],['1.500.000,25','1.500.000,25'],['1500,','1.500,'],['0,05','0,05'],['',''],['-1500','-1.500'],['9999999999999','9.999.999.999.999']]){
 const input={value:raw,selectionStart:raw.length,setSelectionRange(a,b){this.caret=a;assert.strictEqual(a,b);}};
 context.formatCurrencyInput(input);assert.strictEqual(input.value,expected);assert.strictEqual(input.caret,expected.length);
}
const edit={value:'12500.000',selectionStart:3,setSelectionRange(p){this.caret=p;}};
context.formatCurrencyInput(edit);assert.strictEqual(edit.value,'12.500.000');assert.strictEqual(edit.caret,4);
const payloadCode=script.slice(script.indexOf('var payload ='),script.indexOf('$.ajax({',script.indexOf('var payload =')));
for(const [display,canonical] of [['1.500.000','1500000'],['1.500.000,25','1500000.25'],['0,05','0.05']]){
 const fields=[{name:'nilai_kwitansi',value:display},{name:'keterangan',value:'Catatan 1.000'}];
 const $=()=>({serializeArray:()=>fields});$.each=(rows,fn)=>rows.forEach((v,i)=>fn(i,v));
 vm.runInNewContext(payloadCode,{$});assert.strictEqual(fields[0].value,canonical);assert.strictEqual(fields[1].value,'Catatan 1.000');
}
assert.strictEqual((source.match(/> Kembali/g)||[]).length,1);
assert.ok(source.includes('class="btn btn-danger btn-sm"><i class="fa fa-arrow-left"></i> Kembali'));
assert.ok(!source.includes('>Batal</a>'));assert.ok(!source.includes('box-tools pull-right'));
console.log('PASS: live grouping, decimals, paste, large amounts, caret, canonical payload, one red back button');
