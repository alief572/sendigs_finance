const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const {execFileSync} = require('child_process');
const path = require('path');
const php = process.argv[2];
assert(php, 'Usage: node Tabs_client_test.js <php executable>');
const render = tab => execFileSync(php, [path.join(__dirname, 'Tabs_view_test.php'), '--render', tab], {encoding: 'utf8'});
let checks = 0;
for (const active of ['request', 'approval', 'record']) {
    const html = render(active);
    const scripts = [...html.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g)].map(match => match[1]).filter(Boolean);
    for (const script of scripts) { new vm.Script(script); checks++; }
    const handlers = new Map();
    const elements = new Map();
    const tables = new Map();
    const ready = [];
    const reloads = [];
    const draws = [];
    let summary = 0;
    let updatedUrl = '';
    function element(selector) {
        if (elements.has(selector)) return elements.get(selector);
        const el = {
            value: '', textValue: '', attrs: {},
            on(event, fn) { handlers.set(selector + ':' + event, fn); return this; },
            hasClass(name) { return name === 'active' && selector === '#rp_tab_' + active; },
            attr(key, value) { this.attrs[key] = value; return this; },
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
        return typeof arg === 'object' ? arg : element(arg);
    }
    $.fn = {dataTable: {render: {text: () => value => element('<div>').text(value == null ? '' : value).html()}}};
    const sandbox = {$, URL, history: {replaceState(state, title, url) { updatedUrl = url; }},
        window: {location: {href: 'http://localhost/request_payment/index?keep=1'}},
        rpTable: {columns: {adjust() {}}, ajax: {reload(callback, reset) { reloads.push(['request', reset]); }}},
        rpLoadSummary() { summary++; }, alert() { throw new Error('Unexpected alert'); }};
    vm.createContext(sandbox);
    for (const script of scripts.filter(code => code.includes('$(function()'))) vm.runInContext(script, sandbox);
    ready.forEach(fn => fn());
    assert.strictEqual(tables.size, active === 'request' ? 0 : 1, 'lazy initial list load'); checks++;
    for (const kind of ['approval', 'record']) {
        const key = '#rp_module_tabs a[data-rp-tab="' + kind + '"]:shown.bs.tab';
        handlers.get(key)();
        handlers.get(key)();
        assert(tables.has('#table_rp_' + kind), 'table loaded when opened'); checks++;
        assert(reloads.some(item => item[0] === '#table_rp_' + kind && item[1] === false), 'refresh preserves page'); checks++;
        const options = tables.get('#table_rp_' + kind);
        assert.strictEqual(options.columns.length, kind === 'approval' ? 13 : 15, 'column/header alignment'); checks++;
        const malicious = '<img src=x onerror=alert(1)>';
        const columns = options.columns;
        assert(!columns[0].render(malicious, 'display', {kategori: malicious}).includes('<img'), 'document output escaped'); checks++;
        const action = columns[columns.length - 1].render(10, 'display', {print_url: 'http://localhost/print/" onclick="evil'});
        if (kind === 'approval') {
            assert(action.includes('> Print</a>') && (action.match(/<a /g) || []).length === 1, 'Approval action is Print only'); checks++;
            assert(!action.includes('Approve') && !action.includes('/approval_request_payment/detail'), 'no approval/detail action in Request Payment'); checks++;
            assert(action.includes('target="_blank"') && action.includes('rel="noopener"'), 'Print opens separately'); checks++;
            assert.strictEqual(columns[columns.length - 1].render(10, 'display', {}), '-', 'missing print URL has no action'); checks++;
            assert.strictEqual(columns[columns.length - 1].render(10, 'display', {print_url:'javascript:alert(1)'}), '-', 'unsafe print URL has no action'); checks++;
        } else {
            assert(action.includes('?from=request_payment'), 'fixed detail origin'); checks++;
        }
        assert(!action.includes('href="http://localhost/print/" onclick='), 'URL attribute escaped'); checks++;
        if (kind === 'record') { assert(action.includes('Excel Pengajuan'), 'export explicitly per PGJ'); checks++; }
        element('#rp_' + kind + '_search').value = 'DOC-2';
        const payload = {};
        options.ajax.data(payload);
        assert.strictEqual(payload.search.value, 'DOC-2', 'search payload'); checks++;
        handlers.get('#rp_' + kind + '_search:keyup')();
        element('#rp_' + kind + '_entries').value = '50';
        handlers.get('#rp_' + kind + '_entries:change').call(element('#rp_' + kind + '_entries'));
        assert(draws.some(item => Array.isArray(item) && item[1] === 50), 'page length change'); checks++;
    }
    handlers.get('#rp_module_tabs a[data-toggle="tab"]:shown.bs.tab').call({tab: 'request', attr() { return this; }, data() { return this.tab; }});
    assert.strictEqual(new URL(updatedUrl).searchParams.get('tab'), 'request', 'URL tab state'); checks++;
    assert.strictEqual(new URL(updatedUrl).searchParams.get('keep'), '1', 'other URL params preserved'); checks++;
    assert.strictEqual(summary, 1, 'Request summary refreshed on return'); checks++;
}
console.log(`PASS Request Payment tab client: ${checks} checks (rendered JavaScript, no browser)`);
