const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const {execFileSync} = require('child_process');
const path = require('path');
const php = process.argv[2];
assert(php, 'Usage: node Client_test.js <php executable>');
const html = execFileSync(php, [path.join(__dirname, 'View_test.php'), '--render'], {encoding:'utf8'});
const scripts = [...html.matchAll(/<script\b[^>]*>([\s\S]*?)<\/script>/gi)].map(m=>m[1]).filter(s=>s.trim());
let checks=0, ajax, opened, tableOptions;
const handlers=new Map(), elements=new Map();
function el(key){
    if(elements.has(key))return elements.get(key);
    const e={value:'',nodes:[],ready(fn){fn();return this;},on(event,selector,fn){handlers.set(selector,fn);return this;},
        attr(){return this;},flatpickr(){return this;},select2(){return this;},trigger(){return this;},val(value){if(value===undefined)return this.value;this.value=value;return this;},
        data(name){return this[name];},DataTable(options){tableOptions=options;return this;},html(value){this.htmlValue=value;return this;},modal(){return this;},
        empty(){this.nodes=[];return this;},append(value){this.nodes.push(value);return this;},addClass(){return this;},css(){return this;}};
    elements.set(key,e);return e;
}
const document={querySelector(){return {_flatpickr:{clear(){}}};},createTextNode(text){return {text};}};
function $(key){return typeof key==='object'?(key===document?el('document'):key):el(key);}
$.ajax=options=>{ajax=options;};
const sandbox={$ , document, siteurl:'http://localhost/', active_controller:'report_jurnal_expense/', Swal:{fire(){}}, window:{open(url){opened=url;}}};
vm.createContext(sandbox);for(const script of scripts){new vm.Script(script);vm.runInContext(script,sandbox);checks++;}
assert.strictEqual(tableOptions.columns.length,7);checks++;
assert.strictEqual(tableOptions.ajax.url,'http://localhost/report_jurnal_expense/get_data_jurnal');checks++;
const row=el('row');row.id=7;row.no_transaksi='<img src=x onerror=alert(1)>';handlers.get('.view_jurnal').call(row);
assert.strictEqual(ajax.url,'http://localhost/report_jurnal_expense/view_jurnal');checks++;
assert.strictEqual(JSON.stringify(ajax.data),'{"id":7}');checks++;
ajax.success('<table>read only</table>');
assert.strictEqual(el('#myModalLabel').nodes[1].text,' View Report Jurnal Expense - '+row.no_transaksi);checks++;
assert.strictEqual(el('#myModalLabel').htmlValue,undefined);checks++;
el('#tgl_jurnal').value='2026-10-14';el('#no_transaksi').value='ER-1&company=evil';el('#company').value='4';sandbox.export_jurnal();
const url=new URL(opened);assert.strictEqual(url.pathname,'/report_jurnal_expense/export_excel');checks++;
assert.strictEqual(url.searchParams.get('no_transaksi'),'ER-1&company=evil');checks++;
assert.strictEqual(url.searchParams.get('company'),'4');checks++;
sandbox.search_jurnal();const data={};tableOptions.ajax.data(data);assert.strictEqual(data.no_transaksi,'ER-1&company=evil');checks++;
sandbox.reset_search_jurnal();assert.strictEqual(el('#company').value,'');checks++;
console.log(`PASS Report Jurnal Expense client: ${checks} checks, rendered JavaScript without browser`);
