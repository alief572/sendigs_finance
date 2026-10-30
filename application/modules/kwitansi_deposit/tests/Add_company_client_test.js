const assert=require('assert'),fs=require('fs'),path=require('path'),vm=require('vm');
const source=fs.readFileSync(path.join(__dirname,'../views/add.php'),'utf8');
const start=source.indexOf("$('#select_customer').on('change'");
const end=source.indexOf("$('#po_customer').on('change'",start);
const code=source.slice(start,end).replace(/<\?=[\s\S]*?\?>/g,'/endpoint');
for(const item of [
 {id_spk_penawaran:'SPK1',id_company:'1',nm_company:'STM-Vuca'},
 {id_spk_penawaran:'SPK2',id_company:'6',nm_company:'STM-Sustain'},
 {id_spk_penawaran:'SPK3',id_company:null,nm_company:'',company_error:'Company penawaran belum ditentukan.'}
]){
 let callback,request,options=[];
 const fields={};
 function $(selector,attributes){
  if(selector==='<option>'){const o={...attributes,attrs:{},attr(k,v){this.attrs[k]=v;return this;}};return o;}
  if(typeof selector==='object')return selector;
  return fields[selector]||(fields[selector]={value:selector==='#select_customer'?'C1':'',
   val(v){if(v===undefined)return this.value;this.value=v;return this;},
   find(){return {data(){return 'Customer';}};},
   on(event,fn){callback=fn;},
   empty(){options=[];return this;},append(o){options.push(o);return this;}});
 }
 $.ajax=r=>request=r;$.each=(rows,fn)=>rows.forEach((r,i)=>fn(i,r));
 vm.runInNewContext(code,{$,updateJournalPreview(){}});
 callback.call($('#select_customer'));assert.strictEqual(request.data.id_customer,'C1');
 request.success({status:1,data:[item]});
 const option=options[1];assert.strictEqual(option.disabled,!item.id_company||!item.nm_company);
 assert.strictEqual(option.attrs['data-company-id'],item.id_company||'');
 assert.strictEqual(option.attrs['data-company-name'],item.nm_company||'');
 assert.ok(option.text.includes(item.nm_company||item.company_error));
}
assert.ok(!source.includes('PT Sentral Tehnologi Manunggal'));
console.log('PASS: actual customer AJAX callback labels company1/6 and disables unresolved company without fallback');
