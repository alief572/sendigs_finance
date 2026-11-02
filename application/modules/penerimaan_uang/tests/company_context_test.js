'use strict';
const assert=require('assert'), vm=require('vm'), path=require('path'), {spawnSync}=require('child_process');
const render=spawnSync(path.resolve('scratch/runtime/php74/php.exe'),[path.join(__dirname,'render_consulting_fixture.php'),'--add'],{encoding:'utf8'});
assert.strictEqual(render.status,0,render.stderr);
const html=JSON.parse(render.stdout).html;
assert.ok(html.includes('value="C-ID">Same Name'));
assert.ok(!html.includes('receipt_company'),'Form tidak lagi memiliki pilihan company (mengikuti mockup)');
assert.ok(!html.includes('name="id_company"'),'Form tidak mengirim id_company');
const script=[...html.matchAll(/<script>([\s\S]*?)<\/script>/g)].pop()[1];
const nodes={}, handlers={}, requests=[], document={};
const CHECKBOXES='.tbody_list input[name="choose_inv[]"]';
let boxes=[];
const box=(group,name,checked)=>({group,name,checked:!!checked,disabled:false,title:''});
function boxWrapper(list){
 return {isBoxWrapper:true,length:list.length,
  first(){return boxWrapper(list.slice(0,1));},
  data(key){const b=list[0];return key==='group'?b.group:key==='group-name'?b.name:undefined;},
  each(fn){list.forEach(b=>fn.call(boxWrapper([b])));},
  prop(k,v){if(v===undefined)return list[0][k];list.forEach(b=>{b[k]=v;});return this;},
  attr(k,v){list.forEach(b=>{if(k==='title')b.title=v;});return this;}};
}
function $(key){
 if(key===document)return {ready(){},on(event,selector,fn){handlers[event+' '+selector]=fn;}};
 if(key==='<option>')return {value:'',label:'',val(v){this.value=v;return this;},text(v){this.label=v;return this;}};
 if(key===CHECKBOXES+':checked')return boxWrapper(boxes.filter(b=>b.checked));
 if(key===CHECKBOXES)return boxWrapper(boxes);
 if(key&&key.isBoxWrapper)return key;
 if(!nodes[key])nodes[key]={value:'',content:'',options:[],props:{},shown:false};
 const n=nodes[key];return {
 val(v){if(v===undefined)return n.value;n.value=v;return this;},text(v){if(v===undefined)return n.content;n.content=v;return this;},
 html(v){n.content=v;return this;},empty(){n.content='';n.options=[];return this;},append(v){n.options.push(v);return this;},
 hide(){n.shown=false;return this;},show(){n.shown=true;return this;},modal(){return this;},chosen(){return this;},trigger(){return this;},
 prop(k,v){if(v===undefined)return n.props[k];n.props[k]=v;return this;},autoNumeric(op,v){if(op==='set')n.value=v;return this;}
 };
}
$.ajax=req=>requests.push(req);
const ctx={document,$,siteurl:'/',active_controller:'penerimaan_uang/',swal(){},window:{}};
vm.runInNewContext(script,ctx);

// 1. Memilih customer langsung memuat daftar & saldo (tanpa langkah memilih company); respons lama diabaikan.
$('#pph23_dipotong').val('Y');
$('#customer').val('A');handlers['change #customer']();const oldBalance=requests.pop(),oldInvoices=requests.pop();
$('#customer').val('B');handlers['change #customer']();const balance=requests.pop(),invoices=requests.pop();
assert.strictEqual(requests.length,0,'Hanya dua request per pilihan customer');
for(const r of [oldBalance,oldInvoices,balance,invoices])assert.strictEqual(r.data.id_company,undefined,'Request tidak membawa company');
assert.strictEqual(oldBalance.data.id_customer,'A');assert.strictEqual(balance.data.id_customer,'B');
assert.strictEqual(invoices.data.pph23_dipotong,'Y');assert.strictEqual(invoices.data.receipt_kind,'invoice');
oldBalance.success({status:1,saldo:'999.00',formatted:'WRONG',by_company:[]});oldInvoices.success('WRONG');
assert.notStrictEqual(nodes['#lbl_cust_deposit'].content,'WRONG');assert.notStrictEqual(nodes['.tbody_list'].content,'WRONG');

// 2. Saldo gagal: deposit tidak bisa dipakai dan alasan tampil.
balance.success({status:0,error:'Perlu rekonsiliasi'});invoices.success('Correct invoices');
assert.strictEqual(nodes['#deposit_choice'].props.disabled,true);assert.strictEqual(nodes['#deposit_context_message'].content,'Perlu rekonsiliasi');
assert.strictEqual(nodes['.tbody_list'].content,'Correct invoices');

// 3. Saldo per customer + rincian per company; hint mengikuti company dokumen yang dicentang.
$('#customer').val('C');handlers['change #customer']();const activeBalance=requests.pop();requests.pop();
activeBalance.success({status:1,saldo:'100.00',formatted:'Rp 100,00',by_company:[{id:'7',name:'STM',saldo:'60.00',formatted:'Rp 60,00'},{id:'3',name:'SUSTAIN',saldo:'40.00',formatted:'Rp 40,00'}]});
assert.strictEqual(nodes['#lbl_cust_deposit'].content,'Rp 100,00','Saldo total per customer');
assert.strictEqual(nodes['#deposit_choice'].props.disabled,false);
assert.ok(/STM Rp 60,00/.test(nodes['#deposit_context_message'].content)&&/SUSTAIN Rp 40,00/.test(nodes['#deposit_context_message'].content),'Rincian per company tampil sebelum invoice dipilih');
boxes=[box('7','STM',true),box('3','SUSTAIN',false),box('legacy','Invoice lama',false)];
const onCheck=handlers['change '+CHECKBOXES];assert.ok(onCheck,'Handler pembatas satu company terpasang');
onCheck();
assert.strictEqual(boxes[1].disabled,true,'Dokumen company lain dikunci');assert.ok(boxes[1].title.includes('STM'));
assert.strictEqual(boxes[0].disabled,false);
assert.strictEqual(nodes['#deposit_context_message'].content,'Bisa dipakai untuk STM: Rp 60,00');
boxes[0].checked=false;onCheck();
assert.ok(boxes.every(b=>!b.disabled),'Semua dokumen terbuka lagi bila tidak ada yang dicentang');

// 4. Dokumen invoice lama menonaktifkan pemakaian deposit.
boxes[2].checked=true;onCheck();
assert.strictEqual(nodes['#deposit_choice'].props.disabled,true,'Invoice lama tidak dapat memakai deposit');

// 5. Kartu deposit per customer (tanpa company); respons lama diabaikan.
handlers['click #btn_view_kartu_deposit']();const ledger=requests.pop();assert.strictEqual(ledger.data.id_customer,'C');assert.strictEqual(ledger.data.id_company,undefined);
$('#customer').val('D');handlers['change #customer']();requests.pop();requests.pop();
ledger.success({status:1,ledger:[],total_masuk:'WRONG'});
assert.notStrictEqual(nodes['#kartu_total_masuk']?.content,'WRONG','Stale ledger ignored');
console.log('PASS: rendered add form, customer-only deposit lookup, same-company selection guard and stale AJAX protection');
