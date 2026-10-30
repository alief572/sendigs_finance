'use strict';
const assert=require('assert'),path=require('path'),vm=require('vm'),{spawnSync}=require('child_process');
const result=spawnSync(path.resolve('scratch/runtime/php74/php.exe'),[path.join(__dirname,'Index_render_test.php'),'--json'],{encoding:'utf8'});
assert.strictEqual(result.status,0,result.stderr+result.stdout);
for(const [mode,html] of Object.entries(JSON.parse(result.stdout))){
 const table=html.match(/<table[^>]*id="table_kwitansi"[\s\S]*?<\/table>/)[0];
 const headerCount=[...table.match(/<thead>([\s\S]*?)<\/thead>/)[1].matchAll(/<th[ >]/g)].length;
 const rows=[...table.match(/<tbody>([\s\S]*?)<\/tbody>/)[1].matchAll(/<tr[^>]*>([\s\S]*?)<\/tr>/g)];
 assert.strictEqual(headerCount,10);
 assert.strictEqual(rows.length,mode==='populated'?2:0);
 assert.strictEqual([...html.matchAll(/class="btn btn-sm btn-default btn-kwitansi-jurnal" data-id="\d+"/g)].length,rows.length);
 assert.ok(html.includes('id="modalJurnalKwitansi"')&&html.includes('/kwitansi_deposit/journal/'));
 for(const row of rows){assert.strictEqual([...row[1].matchAll(/<td[ >]/g)].length,headerCount);assert.ok(!/colspan|rowspan/.test(row[1]));}
 for(const alreadyInitialized of [false,true]){
  const document={};let configuration,initialized=0,destroyed=0,customerSearch,journalHandler;
  function $(selector){
   if(selector===document)return {ready:fn=>fn()};
   if(selector==='select[name="id_customer"]')return {select2:options=>{customerSearch=options;}};
   assert.strictEqual(selector,'#table_kwitansi');
   return {on:(event,child,fn)=>{assert.strictEqual(event,'click');assert.strictEqual(child,'.btn-kwitansi-jurnal');journalHandler=fn;},DataTable:options=>{if(options){configuration=options;initialized++;return {};}return {destroy:()=>destroyed++};}};
  }
  $.fn={DataTable:{isDataTable:selector=>{assert.strictEqual(selector,'#table_kwitansi');return alreadyInitialized;}}};
  const script=html.match(/<script>([\s\S]*?)<\/script>/)[1];vm.runInNewContext(script,{$,document});
  assert.strictEqual(typeof journalHandler,'function');
  assert.strictEqual(initialized,1);assert.strictEqual(destroyed,alreadyInitialized?1:0);
  assert.strictEqual(customerSearch.width,'100%');assert.strictEqual(customerSearch.minimumResultsForSearch,0);
  assert.strictEqual(configuration.language.emptyTable,'Belum ada tagihan kwitansi deposit.');
  assert.strictEqual(configuration.pageLength,25);assert.strictEqual(configuration.order[0][0],1);assert.strictEqual(configuration.order[0][1],'desc');
 }
}
console.log('PASS: native kwitansi index script, empty/filtered-empty/populated column contract and initialization/reinitialization');
