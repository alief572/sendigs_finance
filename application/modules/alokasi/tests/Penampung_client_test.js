// Run: node application/modules/alokasi/tests/Penampung_client_test.js
const assert = require('assert');
const fs = require('fs');
const vm = require('vm');
const view = fs.readFileSync(__dirname + '/../views/penampung.php', 'utf8');
const script = view.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/<\?= [\s\S]*?\?>/g, "'/get_alokasi'");
const fields = {}, handlers = {}, elements = {};
let options, request, success, failure, errorText, hidden, reloads = 0, search;
const table = {ajax: {reload() { reloads++; }}, search(value) { search = value; return this; }};
function $(selector) {
    if (typeof selector === 'function') return selector();
    return elements[selector] || (elements[selector] = {
        val() { return fields[selector] || ''; },
        text(value) { this.value = String(value); if (selector === '#allocation-error') errorText = value; return this; },
        html() { return this.value.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); },
        prop(name, value) { this[name] = value; if (selector === '#allocation-error' && name === 'hidden') hidden = value; return this; },
        attr(name, value) { this[name] = value; return this; },
        on(event, callback) { handlers[event] = callback; return this; },
        DataTable(config) { options = config; return table; }
    });
}
$.extend = Object.assign;
$.ajax = config => {
    request = config;
    return {done(callback) { success = callback; return this; }, fail(callback) { failure = callback; return this; }};
};
vm.runInNewContext(script, {$});
assert.strictEqual(options.serverSide, true);
assert.strictEqual(options.ordering, false);
assert.strictEqual(options.columns.length, 6);
assert.strictEqual(options.language.search, 'Cari alokasi');
assert.ok(options.language.emptyTable.includes('menu Alokasi'));
assert.ok(options.language.zeroRecords.includes('Reset'));
assert.strictEqual(options.searchDelay, 350);
options.initComplete();
assert.strictEqual(elements['#allocation-list_wrapper .allocation-table-scroll'].tabindex, '0');
assert.strictEqual(elements['#allocation-list_wrapper .allocation-table-scroll'].role, 'region');
assert.ok(elements['#allocation-list_filter input']['aria-label'].includes('referensi'));
for (const col of options.columns) assert.strictEqual(col.render('<img src=x onerror=alert(1)>'), '&lt;img src=x onerror=alert(1)&gt;');
fields['#allocation-start'] = '2026-11-01'; fields['#allocation-end'] = '2026-11-21'; fields['#allocation-bank'] = '7';
let prevented = false;
handlers.submit({preventDefault() { prevented = true; }});
assert.ok(prevented);
let response;
options.ajax({draw: 8}, json => { response = json; });
assert.strictEqual(elements['#allocation-data']['aria-busy'], 'true');
assert.strictEqual(elements['#allocation-count'].value, 'Memuat alokasi...');
assert.strictEqual(request.type, 'POST');
assert.strictEqual(request.data.bank, '7');
assert.strictEqual(request.data.startDate, '2026-11-01');
assert.strictEqual(request.data.endDate, '2026-11-21');
success({draw: 8, recordsTotal: 1200, recordsFiltered: 94, data: []});
assert.strictEqual(hidden, true);
assert.strictEqual(elements['#allocation-data']['aria-busy'], 'false');
assert.strictEqual(elements['#allocation-count'].value, '94 alokasi');
failure({responseJSON: {error: 'Tanggal tidak valid.'}});
assert.strictEqual(errorText, 'Tanggal tidak valid.');
assert.strictEqual(response.draw, 8);
assert.strictEqual(response.data.length, 0);
assert.strictEqual(elements['#allocation-count'].value, 'Data belum dimuat');
assert.ok(elements['#allocation-list .dataTables_empty'].value.includes('Terapkan filter'));
assert.strictEqual(elements['#allocation-data']['aria-busy'], 'false');
assert.strictEqual(reloads, 1, 'Failed request must not trigger retry loop');
failure({});
assert.ok(errorText.includes('Gagal memuat'));
handlers.reset();
assert.strictEqual(search, '');
options.ajax({draw: 9}, () => {});
assert.strictEqual(request.data.bank, undefined);
const oldSuccess = success, oldFailure = failure;
options.ajax({draw: 10}, json => { response = json; });
success({draw: 10, recordsTotal: 1200, recordsFiltered: 1200, data: []});
oldSuccess({draw: 9, recordsTotal: 1, recordsFiltered: 1, data: []});
oldFailure({responseJSON: {error: 'Old request failed'}});
assert.strictEqual(elements['#allocation-count'].value, '1.200 alokasi');
assert.strictEqual(hidden, true, 'Stale response cannot overwrite newest result or error state');
assert.ok(view.includes('html_escape($bank[\'id\'])'));
assert.ok(view.includes('html_escape($bank[\'nama_bank\']'));
assert.ok(view.includes('role="alert"'));
assert.ok(view.includes('aria-live="polite"'));
assert.ok(view.includes('class="allocation-workspace"'));
assert.ok(view.includes('scope="col"'));
assert.ok(view.includes('class="sr-only"'));
assert.ok(view.includes('Terapkan filter'));
assert.ok(!view.includes('upload_rekening_koran'));
const css = fs.readFileSync(__dirname + '/../../../../assets/css/alokasi-penampung.css', 'utf8');
for (const selectorBlock of css.matchAll(/([^{}]+)\{/g)) {
    const selectors = selectorBlock[1].trim();
    if (!selectors.startsWith('@')) {
        for (const selector of selectors.split(',')) assert.ok(selector.trim().startsWith('.allocation-workspace'), 'CSS must stay scoped: ' + selector);
    }
}
assert.ok(css.includes('@media (max-width: 767px)'));
assert.ok(css.includes('@media (max-width: 479px)'));
assert.ok(css.includes('overflow-x: auto'));
assert.ok(css.includes('font-variant-numeric: tabular-nums'));
assert.ok(css.includes('outline: 2px solid'));
assert.ok(!/animation:|@keyframes|linear-gradient|backdrop-filter/.test(css));
console.log('PASS: allocation UI labels/states, text escaping, filters/reset, counts, loading/error and stale responses');
