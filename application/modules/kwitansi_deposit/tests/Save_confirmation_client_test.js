const assert=require('assert'),fs=require('fs'),path=require('path'),vm=require('vm');
const source=fs.readFileSync(path.join(__dirname,'../views/add.php'),'utf8');
const script=source.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/<\?=[\s\S]*?\?>/g,'/endpoint');
const code=script.slice(script.indexOf('var savingKwitansi'),script.lastIndexOf('    });'));
function harness(){
 let submit;const calls=[],requests=[],fields={};const window={location:{href:''}};
 const form={serializeArray:()=>[{name:'nilai_kwitansi',value:'1.500.000,25'}],attr:()=>'/save'};
 function $(selector){if(typeof selector==='object')return selector;return fields[selector]||(fields[selector]={
  value:selector==='#id_customer'?'C1':selector==='#po_customer'?'SP1':selector==='#nilai_kwitansi'?'1.500.000,25':'',disabled:false,
  val(){return this.value;},on(event,callback){submit=callback;return this;},prop(key,value){this[key]=value;return this;},html(value){this.label=value;return this;}
 });}
 $.each=(rows,fn)=>rows.forEach((v,i)=>fn(i,v));$.ajax=r=>requests.push(r);
 let resolveDialog,rejectDialog;const Swal={fire(options){calls.push(options);return options.showCancelButton?new Promise((resolve,reject)=>{resolveDialog=resolve;rejectDialog=reject;}):Promise.resolve({isConfirmed:true});}};
 vm.runInNewContext(code,{$,Swal,window,parseCurrency:s=>Number(s.replace(/\./g,'').replace(',','.'))});
 ['#id_customer','#po_customer','#nilai_kwitansi'].forEach(selector=>$(selector));
 return {fields,requests,calls,window,submit:()=>submit.call(form,{preventDefault(){}}),confirm:v=>resolveDialog(v),reject:()=>rejectDialog(new Error('dialog failed'))};
}
(async()=>{
 for(const decision of [{isConfirmed:false},{isConfirmed:true}]){
  const h=harness(),pending=h.submit();assert.strictEqual(h.calls.length,1);assert.strictEqual(h.requests.length,0);assert.strictEqual(h.fields['#btn_submit'].disabled,true);
  await h.submit();assert.strictEqual(h.calls.length,1,'Repeated submit while dialog open is blocked');
  h.confirm(decision);await pending;
  assert.strictEqual(h.requests.length,decision.isConfirmed?1:0);
  if(!decision.isConfirmed){assert.strictEqual(h.fields['#btn_submit'].disabled,false);continue;}
  assert.strictEqual(h.requests[0].data[0].value,'1500000.25');await h.submit();assert.strictEqual(h.requests.length,1,'Pending request cannot be duplicated');
  h.requests[0].success({status:1,msg:'Saved'});await Promise.resolve();assert.strictEqual(h.window.location.href,'/endpoint');
 }
 for(const fault of ['server','network','dialog']){
  const h=harness(),pending=h.submit();if(fault==='dialog'){h.reject();await pending;}else{h.confirm({isConfirmed:true});await pending;if(fault==='server')h.requests[0].success({status:0,msg:'Rejected'});else h.requests[0].error({});}
  assert.strictEqual(h.fields['#btn_submit'].disabled,false);assert.ok(h.fields['#btn_submit'].label.includes('Simpan Kwitansi'));
  const retry=h.submit();assert.strictEqual(h.calls[h.calls.length-1].showCancelButton,true,'Every retry requires new confirmation');h.confirm({isConfirmed:false});await retry;
 }
 for(const invalid of ['#id_customer','#po_customer','#nilai_kwitansi']){
  const h=harness();h.fields[invalid].value='';await h.submit();assert.strictEqual(h.requests.length,0);assert.strictEqual(h.calls[0].icon,'warning');
 }
 assert.ok(source.includes("assets/dist/sweetalert2.min.js"));assert.ok(source.includes('id="btn_submit" class="btn btn-primary btn-sm"'));
 assert.ok(!source.includes('Terbitkan Kwitansi'));assert.ok(!source.includes('btn-lg'));
 console.log('PASS: confirmation/cancel/retry, duplicate submit guards, save/error feedback, canonical payload and compact labels');
})().catch(e=>{console.error(e);process.exit(1);});
