'use strict';
const fs = require('node:fs'), path = require('node:path'), vm = require('node:vm'), assert = require('node:assert/strict');
const template = fs.readFileSync(path.join(__dirname, '../views/index.php'), 'utf8');
const script = Array.from(template.matchAll(/<script\b(?![^>]*\bsrc=)[^>]*>([\s\S]*?)<\/script>/g), match => match[1]).join('\n');
assert.equal(script.includes('<?'), false, 'List inline scripts must be syntax-checkable without PHP substitutions');
const elements = new Map(), delegated = new Map(), ready = [], tableOptions = new Map();
function element(selector) { if (!elements.has(selector)) elements.set(selector, {selector, attributes: {}, handlers: {}, visible: true, classes: new Set(), value: '', text: ''}); return elements.get(selector); }
const document = {};
class Query {
    constructor(items) { this.items = items; this.length = items.length; }
    ready(callback) { ready.push(callback); return this; }
    on(event, selector, handler) { if (typeof selector === 'string') delegated.set(event + ' ' + selector, handler); else this.items.forEach(item => { item.handlers[event] = selector; }); return this; }
    attr(key, value) { if (typeof key === 'string' && value === undefined) return this.items[0].attributes[key]; this.items.forEach(item => Object.assign(item.attributes, typeof key === 'object' ? key : {[key]: value})); return this; }
    removeAttr(key) { this.items.forEach(item => { delete item.attributes[key]; }); return this; }
    text(value) { this.items.forEach(item => { item.text = value; }); return this; }
    val() { return this.items[0].value; }
    show() { this.items.forEach(item => { item.visible = true; }); return this; }
    hide() { this.items.forEach(item => { item.visible = false; }); return this; }
    addClass(name) { this.items.forEach(item => item.classes.add(name)); return this; }
    removeClass(name) { this.items.forEach(item => item.classes.delete(name)); return this; }
    dataTable(options) { tableOptions.set(this.items[0].selector, options); return this; }
    index(item) { return this.items.indexOf(item); }
    eq(index) { return new Query([this.items[index]]); }
    find() { return new Query([]); }
    each(callback) { this.items.forEach(item => callback.call(item)); return this; }
    trigger(event) { this.items.forEach(item => { if (event === 'focus') focused = item.selector; if (event === 'click') delegated.get('click ' + ({'#invoice-tab-other': '.tab_2', '#invoice-tab-invoiced': '.tab_3'}[item.selector] || '.tab_1')).call(item); }); return this; }
}
function $(selector) {
    if (selector === document) return new Query([element('document')]);
    if (selector === '.invoice-tabs [role="tab"]') return new Query([element('#invoice-tab-consulting'), element('#invoice-tab-other'), element('#invoice-tab-invoiced')]);
    return new Query([typeof selector === 'string' ? element(selector) : selector]);
}
$.extend = Object.assign; $.param = obj => new URLSearchParams(obj).toString();
let focused, checks = 0;
const context = {document, $, siteurl: '/', active_controller: 'invoicing/', window: {}};
vm.createContext(context); vm.runInContext(script, context); ready.forEach(callback => callback());
function check(actual, expected, message) { checks++; assert.deepEqual(actual, expected, message); }
function tableEvent(selector, event, json) { return element(selector).handlers[event].call(element(selector), {}, {}, json); }
const consulting = tableOptions.get('#table_penawaran');
check(consulting.ajax.url, '/invoicing/get_data_spk', 'Consulting endpoint preserved');
check(consulting.serverSide, true, 'Server-side pagination retained');
check(consulting.columns.length, 7, 'Consulting information grouped into seven columns');
check(consulting.ordering, false, 'No unsupported client sorting for fixed backend order');
check(consulting.stateLoadParams({}, {columns: Array(10)}), false, 'Old ten-column state rejected');
check(consulting.stateLoadParams({}, {columns: Array(7)}), undefined, 'Current column state accepted');
check(consulting.stateLoadParams({}, {}), false, 'Incomplete state rejected');
const row = {no_invoice: '001/STM/2026', no_spk: 'SPK-001', customer: 'Customer & <script>alert(1)</script>', project: 'Project panjang '.repeat(40), project_leader: 'PL "Nama"', sales: "Sales 'Nama'", company: '<img src=x onerror=alert(1)>'};
const documentCell = consulting.columns[1].render(null, 'display', row);
assert.match(documentCell, /001\/STM\/2026/); checks++;
assert.match(documentCell, /SPK-001/); checks++;
assert.match(consulting.columns[1].render(null, 'display', {...row, no_invoice: null}), /Belum ada invoice/); checks++;
const customerCell = consulting.columns[2].render(null, 'display', row);
check(customerCell.includes('<script>'), false, 'Customer HTML escaped');
check(customerCell.includes('&lt;script&gt;'), true, 'Customer text retained');
check(customerCell.includes(row.project), true, 'Full project description retained without truncation');
const teamCell = consulting.columns[4].render(null, 'display', row);
check(teamCell.includes('PL &quot;Nama&quot;'), true, 'Project leader rendered and escaped');
check(teamCell.includes('Sales &#39;Nama&#39;'), true, 'Sales rendered and escaped');
check(consulting.columns[3].render(row.company, 'display').includes('<img'), false, 'Company HTML escaped');
check(consulting.columns[1].render(null, 'filter', row), '001/STM/2026 SPK-001', 'Document plain text for non-display use');
check(consulting.columns[2].render(null, 'filter', row), row.customer + ' ' + row.project, 'Customer/project plain text retained');
check(consulting.columns[3].render(row.company, 'filter'), row.company, 'Company plain text retained for non-display use');
check(consulting.columns[4].render(null, 'filter', row), row.project_leader + ' ' + row.sales, 'Both team members retained');
check(consulting.columns[6].data, 'option', 'Action HTML contract unchanged');
check(consulting.language.search, 'Cari:', 'Search control labelled');
element('#filter_tahun').value = '2025'; const request = {}; consulting.ajax.data(request);
check(request, {filter_status: 'uninvoiced', filter_tahun: '2025'}, 'Consulting tab always requests uninvoiced only');
tableEvent('#table_penawaran', 'preXhr.dt');
check(element('#invoice-list-status').attributes['data-state'], 'loading', 'Loading state');
check(element('#table_penawaran').attributes['aria-busy'], 'true', 'Busy table state');
check(tableEvent('#table_penawaran', 'xhr.dt', null), true, 'AJAX error handled without duplicate DataTables alert');
check(element('#invoice-list-status').attributes['data-state'], 'error', 'Failure state');
check(element('#table_penawaran').attributes['aria-busy'], 'false', 'Busy cleared on error');
tableEvent('#table_penawaran', 'xhr.dt', {data: []});
check(element('#invoice-list-status').text, '', 'Successful empty response clears failure');
check(element('#invoice-list-status').attributes['data-state'], undefined, 'State reset on recovery');
const tabs = ['#invoice-tab-consulting', '#invoice-tab-other', '#invoice-tab-invoiced'];
function key(selector, value) { let prevented = false; element(selector).handlers.keydown.call(element(selector), {key: value, preventDefault() { prevented = true; }}); return prevented; }
check(key(tabs[0], 'ArrowRight'), true, 'Arrow key handled');
check(focused, tabs[1], 'Right arrow focuses second tab');
check(element(tabs[1]).attributes['aria-selected'], 'true', 'Second tab selected');
check(element(tabs[0]).attributes.tabindex, '-1', 'Inactive tab out of tab order');
check(element('.col_1').visible, false, 'Consulting panel hidden');
check(element('.col_2').visible, true, 'Other panel visible');
const other = tableOptions.get('#table_penawaran_non_konsultasi');
check(other.ajax.url, '/invoicing/get_data_quotation_non_konsultasi', 'Other endpoint preserved');
check(other.columns.length, 10, 'All other columns retained');
assert.match(other.language.emptyTable, /non konsultasi/); checks++;
check(key(tabs[1], 'ArrowRight'), true, 'Right arrow to third tab');
check(focused, tabs[2], 'Third tab focused');
check(element('.col_3').visible, true, 'Invoiced panel visible');
check(element('.col_2').visible, false, 'Other panel hidden when invoiced shown');
check(element(tabs[2]).attributes['aria-selected'], 'true', 'Invoiced tab selected');
const invoiced = tableOptions.get('#table_invoiced');
check(invoiced.ajax.url, '/invoicing/get_data_spk', 'Invoiced tab reuses consulting endpoint');
check(invoiced.columns.length, 8, 'Invoiced table has eight columns');
check(invoiced.columns[2].data, 'tanggal_invoice', 'Invoice date column');
check(invoiced.columns[2].render('05/10/2026', 'display'), '05/10/2026', 'Invoice date displayed');
check(invoiced.columns[2].render('<b>x</b>', 'display').includes('<b>'), false, 'Invoice date escaped');
check(invoiced.stateLoadParams({}, {columns: Array(7)}), false, 'Stale seven-column state rejected for invoiced');
element('#filter_tahun_inv').value = '2026'; element('#filter_bulan_inv').value = '10'; element('#filter_company_inv').value = '4';
const invoicedRequest = {}; invoiced.ajax.data(invoicedRequest);
check(invoicedRequest, {filter_status: 'invoiced', filter_tahun: '2026', filter_bulan: '10', filter_company: '4'}, 'Invoiced filter request contract');
let opened; context.window.open = url => { opened = url; };
delegated.get('click .btn_download_excel_invoiced')({preventDefault() {}});
check(opened, '/invoicing/download_excel?filter_status=invoiced&filter_tahun=2026&filter_bulan=10&filter_company=4', 'Invoiced export carries filters');
delegated.get('click .btn_download_excel')({preventDefault() {}});
check(opened, '/invoicing/download_excel?filter_status=uninvoiced&filter_tahun=2025', 'Consulting export is uninvoiced only');
check(key(tabs[2], 'ArrowRight'), true, 'Right arrow wraps');
check(focused, tabs[0], 'Wrapped to first tab');
key(tabs[0], 'ArrowLeft'); check(focused, tabs[2], 'Left arrow wraps');
key(tabs[2], 'Home'); check(focused, tabs[0], 'Home selects first');
key(tabs[0], 'End'); check(focused, tabs[2], 'End selects last');
check(key(tabs[2], 'Tab'), false, 'Tab key keeps native focus behavior');
tableEvent('#table_penawaran_non_konsultasi', 'preXhr.dt');
check(element('#invoice-other-status').attributes['data-state'], 'loading', 'Other list independent loading state');
check(element('#table_penawaran_non_konsultasi').attributes['aria-busy'], 'true', 'Other list busy table state');
check(tableEvent('#table_penawaran_non_konsultasi', 'xhr.dt', null), true, 'Other list AJAX error handled without alert');
check(element('#invoice-other-status').attributes['data-state'], 'error', 'Other list error state');
check(element('#table_penawaran_non_konsultasi').attributes['aria-busy'], 'false', 'Other list busy cleared on error');
tableEvent('#table_penawaran_non_konsultasi', 'xhr.dt', {data: []});
check(element('#invoice-other-status').text, '', 'Other list successful response clears failure');
check(element('#invoice-other-status').attributes['data-state'], undefined, 'Other list state reset on recovery');
element('#filter_tahun_non_kons').value = '2025'; const otherRequest = {}; other.ajax.data(otherRequest);
check(otherRequest, {filter_tahun: '2025'}, 'Other list filter request contract');
const enhancedCells = []; let menuCloses = 0;
context.window.InvoiceActionMenu = {enhance(cell) { enhancedCells.push(cell); }, close() { menuCloses++; }};
const consultingActionCell = element('consulting-action-cell'), otherActionCell = element('other-action-cell');
consulting.columns[6].createdCell(consultingActionCell);
other.columns[9].createdCell(otherActionCell);
check(enhancedCells, [consultingActionCell, otherActionCell], 'Both action columns enhance existing server actions into dropdowns');
delegated.get('preDraw.dt #table_penawaran, #table_penawaran_non_konsultasi, #table_invoiced')();
check(menuCloses, 1, 'Delegated redraw lifecycle closes open menus for all tables');
console.log('UI list client PASS: ' + checks + ' checks');
