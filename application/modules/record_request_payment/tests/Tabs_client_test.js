const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const {execFileSync} = require('child_process');
const path = require('path');
const php = process.argv[2];
assert(php, 'Usage: node Tabs_client_test.js <php executable>');
const render = tab => execFileSync(php, [path.join(__dirname, 'Tabs_view_test.php'), '--render', tab], {encoding: 'utf8'});
let checks = 0;
for (const active of ['batch', 'items', 'legacy']) {
    const html = render(active);
    const scripts = [...html.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)].map(match => match[1]).filter(Boolean);
    for (const script of scripts) { new vm.Script(script); checks++; }
    const handlers = new Map();
    const elements = new Map();
    const tables = new Map();
    const ready = [];
    const reloads = [];
    const draws = [];

    let updatedUrl = '';
    function element(selector) {
        if (elements.has(selector)) return elements.get(selector);
        const el = {
            value: '', textValue: '', attrs: {},
            on(event, fn) { handlers.set(selector + ':' + event, fn); return this; },
            hasClass(name) { return name === 'active' && selector === '#tab_items' && active === 'items'; },
            ready(fn) { ready.push(fn); return this; },
            attr(key, value) { if (value === undefined) return this.attrs[key]; this.attrs[key] = value; return this; },
            data(key) { return key === 'rp-tab' ? this.tab : null; },
            val() { return this.value; },
            text(value) { this.textValue = String(value); return this; },
            html() { return this.textValue.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); },
            DataTable(options) {
                assert(!tables.has(selector), 'table initialized once'); checks++;
                tables.set(selector, options);
                const table = {
                    columns: {adjust() { return table; }},
                    ajax: {reload(callback, reset) { reloads.push([selector, reset]); }},
                    draw() { draws.push(selector); },
                    page: {len(value) { draws.push([selector, value]); return table; }}
                };
                return table;
            }
        };
        elements.set(selector, el);
        return el;
    }
    function $(arg) {
        if (typeof arg === 'function') { ready.push(arg); return; }
        return typeof arg === 'object' ? (arg === sandbox.document ? element('document') : arg) : element(arg);
    }
    $.fn = {dataTable: {render: {text: () => value => element('<div>').text(value == null ? '' : value).html()}}};
    const sandbox = {$, URL, document: {}, history: {replaceState(state, title, url) { updatedUrl = url; }},
        window: {location: {href: 'http://localhost/record_request_payment/index?keep=1'}},
        alert() { throw new Error('Unexpected alert'); }};
    vm.createContext(sandbox);
    for (const script of scripts) vm.runInContext(script, sandbox);
    ready.forEach(fn => fn());
    assert.strictEqual(tables.size, active === 'batch' ? 1 : 2, 'initial Batch and lazy item/legacy tables'); checks++;
    for (const kind of ['items']) {
        const key = '#record_tabs a[href="#tab_items"]:shown.bs.tab';
        handlers.get(key)();
        handlers.get(key)();
        assert(tables.has('#table_record_' + kind), 'table loaded when opened'); checks++;
        assert(reloads.some(item => item[0] === '#table_record_' + kind && item[1] === false), 'refresh preserves page'); checks++;
        const options = tables.get('#table_record_' + kind);
        assert.strictEqual(options.columns.length, 15, 'column/header alignment'); checks++;
        const malicious = '<img src=x onerror=alert(1)>';
        const columns = options.columns;
        assert(!columns[0].render(malicious, 'display', {kategori: malicious}).includes('<img'), 'document output escaped'); checks++;
        const action = columns[columns.length - 1].render(10, 'display', {print_url: 'http://localhost/print/" onclick="evil'});
        assert(action.includes('?from=items'), 'detail origin stays in Record items'); checks++;
<<<<<<< HEAD
        assert.strictEqual((action.match(/<a /g) || []).length, 3, 'View, document Print and Excel actions'); checks++;
        const noPrint = columns[columns.length - 1].render(10, 'display', {print_url:'javascript:alert(1)'});
        assert(!noPrint.includes('javascript:') && (noPrint.match(/<a /g) || []).length === 2, 'unsafe print URL omitted, other actions preserved'); checks++;
        assert(!action.includes('href="http://localhost/print/" onclick='), 'URL attribute escaped'); checks++;
        assert(action.includes('Excel Pengajuan'), 'export explicitly per PGJ'); checks++;
=======
        assert.strictEqual((action.match(/<a /g) || []).length, 2, 'View and document Print actions'); checks++;
        const noPrint = columns[columns.length - 1].render(10, 'display', {print_url:'javascript:alert(1)'});
        assert(!noPrint.includes('javascript:') && (noPrint.match(/<a /g) || []).length === 1, 'unsafe print URL omitted, View preserved'); checks++;
        assert(!action.includes('href="http://localhost/print/" onclick='), 'URL attribute escaped'); checks++;
        assert(!action.includes('Excel Pengajuan'), 'no batch export inside item actions'); checks++;
        assert(html.includes('Cetak ke Excel') && html.includes('record_request_payment/export_items_excel'), 'toolbar exports per-item list'); checks++;
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
        element('#rec_' + kind + '_search').value = 'DOC-2';
        const payload = {};
        options.ajax.data(payload);
        assert.strictEqual(payload.search.value, 'DOC-2', 'search payload'); checks++;
        handlers.get('#rec_' + kind + '_search:keyup')();
        element('#rec_' + kind + '_entries').value = '50';
        handlers.get('#rec_' + kind + '_entries:change').call(element('#rec_' + kind + '_entries'));
        assert(draws.some(item => Array.isArray(item) && item[1] === 50), 'page length change'); checks++;
    }
    const navHandler = handlers.get('#record_tabs a[data-toggle="tab"]:shown.bs.tab');
    for (const href of ['#tab_items', '#tab_baru', '#tab_lama']) {
        const target = element('nav-' + href); target.attrs.href = href;
        navHandler({target});
        const expected = {'#tab_items':'items', '#tab_baru':'batch', '#tab_lama':'legacy'}[href];
        assert.strictEqual(new URL(updatedUrl).searchParams.get('tab'), expected, 'URL tab state'); checks++;
        assert.strictEqual(new URL(updatedUrl).searchParams.get('keep'), '1', 'other URL params preserved'); checks++;
    }
    assert(tables.has('#table_record_legacy'), 'legacy initialized on first opening'); checks++;

}
console.log(`PASS Record Request Payment tab client: ${checks} checks (rendered JavaScript, no browser)`);
