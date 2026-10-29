const fs = require('fs');
const path = require('path');
const vm = require('vm');
const assert = require('assert');
const {execFileSync} = require('child_process');
const php = process.argv[2] || path.resolve(__dirname, '../../../../scratch/runtime/php74/php.exe');
const html = execFileSync(php, [path.join(__dirname, 'Index_view_test.php'), '--render'], {encoding: 'utf8'});
const scripts = [...html.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)].map(m => m[1]).filter(s => s.trim());
const document = {};
const configurations = new Map();
const handlers = new Map();
const elements = new Map();
function element(selector) {
    if (!elements.has(selector)) elements.set(selector, {
        ready() { return this; },
        on(event, target, handler) { handlers.set(target, handler); return this; },
        DataTable(config) {
            if (config) { configurations.set(selector, config); }
            return {search: () => 'Kertas & A4', order: () => [[2, 'asc']]};
        },
        html(value) { this.htmlValue = value; return this; },
        append(value) { this.appended = value; return this; },
        css(key, value) { this[key] = value; return this; }
    });
    return elements.get(selector);
}
function $(selector) { return element(selector === document ? 'document' : selector); }
const sandbox = {$, document, siteurl: '/', active_controller: 'outgoing_stok_atk/'};
$.param = values => new URLSearchParams(values).toString();
sandbox.window = {location: {href: ''}};
let checks = 0;
function test(condition, message) { assert(condition, message); checks++; }
for (const script of scripts) { new vm.Script(script); vm.runInNewContext(script, sandbox); }
sandbox.DataTables2();
const config = configurations.get('#example2');
test(config.serverSide && config.processing && config.stateSave, 'Listing remains server-side with saved state');
test(config.ajax.url === '/outgoing_stok_atk/data_side_request_material', 'Same listing endpoint');
test(config.columnDefs.some(c => c.targets === 'no-sort' && c.orderable === false), 'Detail header disables sorting');
test(config.aaSorting[0][0] === 1 && config.aaSorting[0][1] === 'desc', 'Default transaction sorting preserved');
test(JSON.stringify(config.aLengthMenu[0]) === JSON.stringify([10,20,50,100,150]), 'Page length options preserved');
test(handlers.has('.detail'), 'Existing detail modal handler retained');
test(handlers.has('#export-excel'), 'Excel export handler registered');
let prevented = false;
handlers.get('#export-excel').call({href: '/fixture/outgoing_stok_atk/export_excel'}, {preventDefault() { prevented = true; }});
const download = new URL(sandbox.window.location.href, 'https://fixture.test');
test(prevented && download.pathname === '/fixture/outgoing_stok_atk/export_excel', 'Export navigates to download endpoint');
test(download.searchParams.get('search') === 'Kertas & A4', 'Search text encoded without losing special characters');
test(download.searchParams.get('column') === '2' && download.searchParams.get('direction') === 'asc', 'Active sorting sent to export');
test(!download.searchParams.has('start') && !download.searchParams.has('length'), 'Export is not limited to active page');
config.ajax.error();
test(element('#example2 tbody').htmlValue.includes('colspan="10"'), 'Transport failure fits ten columns');
test(element('#example2_processing').display === 'none', 'Failure clears active table processing');
console.log('PASS Outgoing index client: ' + checks + ' checks');
