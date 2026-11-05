'use strict';
const assert=require('assert'),path=require('path'),vm=require('vm'),{spawnSync}=require('child_process');
const run=spawnSync(path.resolve('scratch/runtime/php74/php.exe'),[path.join(__dirname,'Index_render_test.php'),'--json'],{encoding:'utf8'});
assert.strictEqual(run.status,0,run.stderr+run.stdout);
const rendered=JSON.parse(run.stdout);
for(const [mode,html] of Object.entries(rendered)){
 const tableHtml=html.match(/<table[^>]*id="table_deposit_summary"[\s\S]*?<\/table>/)[0];
 const headerCount=[...tableHtml.match(/<thead>([\s\S]*?)<\/thead>/)[1].matchAll(/<th[ >]/g)].length;
 const body=tableHtml.match(/<tbody>([\s\S]*?)<\/tbody>/)[1];
 const rows=[...body.matchAll(/<tr([^>]*)>([\s\S]*?)<\/tr>/g)];
 let configuration;
 const document={};
 function $(selector){
  if(selector===document)return {ready:fn=>fn()};
  if(selector==='#table_deposit_summary')return {DataTable:options=>{
   configuration=options;
   for(const row of rows){assert.strictEqual([...row[2].matchAll(/<td[ >]/g)].length,headerCount,'Each DOM data row must supply all DataTables columns');assert.ok(!/colspan|rowspan/.test(row[2]));}
   return {};
  }};
  throw new Error('Unexpected selector '+selector);
 }
 const script=html.match(/<script>([\s\S]*?)<\/script>/)[1];vm.runInNewContext(script,{$,document});
 assert.strictEqual(headerCount,10);
 assert.strictEqual(configuration.language.emptyTable,'Belum ada data deposit customer.');
 assert.strictEqual(configuration.order[0][0],7,'Total Sisa Saldo remains the default sorting column');
 assert.strictEqual(rows.length,mode==='empty'?0:2);
}
console.log('PASS: actual index script, empty-table language, ten-column DOM contract and balance sorting');
