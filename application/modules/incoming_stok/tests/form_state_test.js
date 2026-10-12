// Execute the actual form script with a small jQuery adapter, without a browser.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(require('node:path').join(__dirname, '../views/request.php'), 'utf8');
const script = source.match(/<script type="text\/javascript">([\s\S]*?)<\/script>/)[1]
    .replace(/<\?[\s\S]*?\?>/g, '');
const handlers = new Map();
const requests = [];
const elements = new Map();
const document = {};
let checked = [{value: 'PO1', checked: true}];
function element(selector) {
    if (!elements.has(selector)) elements.set(selector, {value: '', html: '', text: ''});
    return elements.get(selector);
}
function $(selector) {
    const state = typeof selector === 'string' ? element(selector) : selector;
    const api = {
        ready(fn) { fn(); return api; },
        on(event, target, fn) { handlers.set(event + ':' + target, fn); return api; },
        click() { return api; }, select2() { return api; }, datepicker() { return api; },
        autoNumeric() { return api; },
        val(value) { if (value === undefined) return state.value; state.value = value; return api; },
        html(value) { if (value === undefined) return state.html; state.html = value; return api; },
        text(value) { if (value === undefined) return state.text; state.text = value; return api; },
        empty() { state.html = ''; return api; },
        each(fn) { if (selector === '.check_po') checked.forEach(row => fn.call(row)); return api; },
        is() { return state.checked; }
    };
    return api;
}
$.ajax = options => { requests.push(options); };
const context = { $, document, window: {}, siteurl: '/', swal() {}, console };
vm.runInNewContext(script, context, {filename: 'request.php embedded script'});
function changeSource(value) {
    element('#supplier').value = value;
    handlers.get('change:.pilih_supplier').call(element('#supplier'));
}
function changeDocs() { handlers.get('change:.check_po, #id_gudang').call({}); }
element('#id_gudang').value = '17';
element('#supplier').value = 'SUP1';
changeDocs();
const oldDetail = requests.find(request => request.url.endsWith('/detail_purchasing_order'));
const oldJournal = requests.find(request => request.url.endsWith('/set_jurnal'));
element('.tbody_list_jurnal').html = 'old journal';
changeSource('cash');
assert.equal(element('.list_no_po').html, '');
assert.equal(element('.tbody_list_jurnal').html, '');
assert.match(element('#body_req').html, /Pilih dokumen/);
oldDetail.success({header: 'stale PO rows'});
oldJournal.success({hasil_jurnal: 'stale PO journal', ttl_debit: 100, ttl_kredit: 100});
assert.notEqual(element('#body_req').html, 'stale PO rows');
assert.equal(element('.tbody_list_jurnal').html, '');
checked = [{value: 'CASH1', checked: true}, {value: 'CASH2', checked: true}];
requests.length = 0;
changeDocs();
assert.equal(requests.length, 1);
assert.equal(requests[0].data.supplier, 'cash');
assert.deepEqual(Array.from(requests[0].data.no_po), ['CASH1', 'CASH2']);
requests[0].success({header: 'Cash rows'});
assert.equal(element('#body_req').html, 'Cash rows');
checked = [];
requests.length = 0;
changeDocs();
assert.equal(requests.length, 0);
assert.match(element('#body_req').html, /Pilih dokumen/);
assert.equal(element('.tbody_list_jurnal').html, '');
console.log('PASS: source switch clears old state; stale responses ignored; Cash skips journal; multiple documents supported; deselection clears items');
