'use strict';
const assert=require('assert'), vm=require('vm'), path=require('path'), {spawnSync}=require('child_process');
const render=spawnSync(path.resolve('scratch/runtime/php74/php.exe'),[path.join(__dirname,'render_consulting_fixture.php'),'--add'],{encoding:'utf8'});
assert.strictEqual(render.status,0,render.stderr);
const html=JSON.parse(render.stdout).html;
assert.ok(html.includes('value="C-ID">Same Name'));
const script=[...html.matchAll(/<script>([\s\S]*?)<\/script>/g)].pop()[1];
const nodes={}, handlers={}, requests=[], document={};
function $(key){
 if(key===document)return {ready(){},on(event,selector,fn){handlers[event+' '+selector]=fn;}};
 if(key==='<option>')return {value:'',label:'',val(v){this.value=v;return this;},text(v){this.label=v;return this;}};
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
$('#customer').val('A');handlers['change #customer']();const old=requests.pop();
$('#customer').val('B');handlers['change #customer']();const current=requests.pop();
old.success({status:1,companies:[{id:'7',name:'Wrong'}]});
assert.strictEqual(nodes['#receipt_company'].options.length,1,'Stale companies ignored');
current.success({status:1,companies:[{id:'7',name:'STM'},{id:'3',name:'Sustain'}]});
assert.strictEqual(nodes['#receipt_company'].options.length,3);
assert.strictEqual(nodes['#receipt_company'].value,'','Multiple companies require choice');
$('#receipt_company').val('7');handlers['change #receipt_company']();const oldBalance=requests.pop(),oldInvoices=requests.pop();
assert.strictEqual(oldBalance.data.id_customer,'B');assert.strictEqual(oldBalance.data.id_company,'7');
$('#receipt_company').val('3');handlers['change #receipt_company']();const balance=requests.pop(),invoices=requests.pop();
oldBalance.success({status:1,saldo:'999.00',formatted:'WRONG'});oldInvoices.success('WRONG');
assert.notStrictEqual(nodes['#lbl_cust_deposit'].content,'WRONG');assert.notStrictEqual(nodes['.tbody_list'].content,'WRONG');
balance.success({status:0,error:'Perlu rekonsiliasi'});invoices.success('Correct invoices');
assert.strictEqual(nodes['#deposit_choice'].props.disabled,true);assert.strictEqual(nodes['#deposit_context_message'].content,'Perlu rekonsiliasi');
assert.strictEqual(nodes['.tbody_list'].content,'Correct invoices');
$('#customer').val('C');handlers['change #customer']();requests.pop().success({status:1,companies:[{id:'7',name:'STM'}]});
assert.strictEqual(nodes['#receipt_company'].value,'7','Single company selected automatically');
const activeBalance=requests.pop();requests.pop();activeBalance.success({status:1,saldo:'100.00',formatted:'Rp 100,00'});
assert.strictEqual(nodes['#deposit_choice'].props.disabled,false);
handlers['click #btn_view_kartu_deposit']();const ledger=requests.pop();assert.strictEqual(ledger.data.id_company,'7');
$('#receipt_company').val('3');handlers['change #receipt_company']();ledger.success({status:1,ledger:[],total_masuk:'WRONG'});
assert.notStrictEqual(nodes['#kartu_total_masuk']?.content,'WRONG','Stale ledger ignored');
console.log('PASS: rendered add form, stable customer ID, company selection, scoped requests and stale AJAX protection');
