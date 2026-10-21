'use strict';
const assert = require('node:assert/strict');
const api = require('../../../../assets/js/jurnal-all.js');
let checks = 0;
function check(value, message) { checks++; assert.ok(value, message); }
class Element {
    constructor(dataset = {}) { this.dataset = dataset; this.events = {}; this.value = ''; this.hidden = false; this.options = []; this.attributes = {}; this.textContent = ''; this.innerHTML = ''; this.disabled = false; this.classes = new Set(); this.classList = { toggle: (key, on) => on ? this.classes.add(key) : this.classes.delete(key), contains: key => this.classes.has(key) }; }
    addEventListener(name, handler) { this.events[name] = handler; }
    setAttribute(key, value) { this.attributes[key] = value; }
    replaceChildren() { this.options = []; this.innerHTML = ''; }
    add(option) { this.options.push(option); }
    focus() { this.focused = true; }
    fire(name, data = {}) { this.events[name]({ preventDefault() {}, target: this, ...data }); }
    querySelector(selector) { return this.parts[selector]; }
}
const nodes = {};
const node = selector => nodes[selector] || (nodes[selector] = new Element());
const tabButtons = ['INV', 'RCV', 'PAY', 'EXP', 'PCS'].map(tab => { const el = node('#ja-tab-' + tab); el.dataset.tab = tab; el.dataset.title = 'Jurnal ' + tab; el.classes.add('ja-tab'); return el; });
const cards = tabButtons.map(tab => { const el = new Element({ tab: tab.dataset.tab }); el.classes.add('ja-card'); el.parts = Object.fromEntries(['[data-count]', '[data-amount]', '[data-age]'].map(key => [key, new Element()])); return el; });
const form = node('#ja-filters'); form.elements = Object.fromEntries(['year', 'month', 'company', 'category'].map(key => [key, new Element()]));
node('#ja-loading').hidden = true;
const workspace = new Element({ posted: '0', base: '/jurnal_all', tab: 'INV', manage: '1' });
workspace.querySelector = node;
workspace.querySelectorAll = selector => selector === '.ja-card' ? cards : selector === '.ja-tab' ? tabButtons : selector === '.ja-tab, .ja-card' ? [...tabButtons, ...cards] : [];
global.Option = class { constructor(label, value) { this.text = label; this.value = String(value); } };
let modal, metadataWarning = false, pending = false, actionCount = 0, assigned, releaseAction;
global.confirm = () => true;
global.location = { assign: value => { assigned = value; } };
global.jQuery = el => ({ modal: value => { modal = value; if (value === 'hide' && el.hideHandler) el.hideHandler({ preventDefault() { modal = 'blocked'; } }); }, on: (event, handler) => { el.hideHandler = handler; } });
const calls = [];
global.fetch = async (url, options) => {
    const values = Object.fromEntries(options.body || []); calls.push([url, values]);
    let payload;
    if (url.endsWith('get_filters')) payload = { years: [2026, 2025], companies: { 7: 'STM' }, categories: ['Kasbon', 'Expense'] };
    else if (url.endsWith('get_summary')) payload = Object.fromEntries(tabButtons.map(el => [el.dataset.tab, { count: 1, debit: '100.25', oldest: '2026-10-14', age: 1 }]));
    else if (url.endsWith('get_data_jurnal')) payload = { recordsFiltered: 11, data: [{ id: 1, source: 'PAY', tgl_jurnal: '2026-10-14', no_transaksi: 'INTERNAL-KEY', display_no_transaksi: '<script>INV/2026/1', no_referensi: 'SPK-1', category: 'Kasbon', keterangan: 'Safe', nm_company: 'STM', debit: '100.25', kredit: '100.25' }], totals: { debit: '100.25', kredit: '100.25' } };
    else if (url.endsWith('preview')) payload = { status: 1, html: '<p>detail</p>', can_post: true, can_revise: true };
    else { actionCount++; if (pending) await new Promise(resolve => { releaseAction = resolve; }); payload = { status: 1, ...(metadataWarning ? { warning: 'Metadata belum tersimpan, jangan posting ulang.' } : {}) }; }
    return { ok: true, json: async () => payload };
};
const flush = () => new Promise(resolve => setImmediate(resolve));
(async () => {
    check(api.columns('PAY', true).length === 12 && api.columns('EXP', false).length === 9, 'column count');
    check(api.escape('</textarea><script>') === '&lt;/textarea&gt;&lt;script&gt;', 'safe table escaping');
    check(api.money('100.25') === '100.25', 'cents formatting');
    check(api.money('90071992547409.25') === '90,071,992,547,409.25', 'display does not round through JavaScript Number');
    check(api.journalDate('2026-01-03') === '03 Januari 2026' && api.journalDate('2026-12-31') === '31 Desember 2026', 'date labels preserve padded day and full month at year boundaries');
    check(api.journalDate(null) === '—' && api.journalDate('0000-00-00') === '—', 'missing and zero date stay unavailable');
    api.mount(workspace);
    check(!node('#ja-loading').hidden && node('#ja-loading-text').textContent.includes('Memuat'), 'loading indicator appears synchronously before requests finish');
    check(node('#ja-apply').disabled && form.elements.year.disabled, 'filters temporarily disabled while loading');
    await flush();
    check(form.elements.year.options.some(option => option.value === '' && option.text === 'Semua Tahun') && !node('#ja-invoice-period-hint').hidden, 'invoice exposes all-years option and explains whole-transaction period matching');
    check(node('#ja-loading').hidden && !node('#ja-apply').disabled && !form.elements.year.disabled, 'successful load clears indicator and restores filters');
    check(node('#ja-table tbody').innerHTML.includes('&lt;script&gt;'), 'render actual response safely');
    check(node('#ja-table tbody').innerHTML.includes('14 Oktober 2026') && !node('#ja-table tbody').innerHTML.includes('2026-10-14'), 'journal table shows full Indonesian month without changing source data');
    check(node('#ja-table tbody').innerHTML.includes('INV/2026/1') && node('#ja-table tbody').innerHTML.includes('SPK-1') && !node('#ja-table tbody').innerHTML.includes('INTERNAL-KEY'), 'table renders invoice/SPK display without replacing the action identity');
    check(cards[0].parts['[data-count]'].textContent === '1' && cards[0].attributes['aria-pressed'] === 'true', 'summary numeric count and selected source remain accessible');
    check(node('#ja-section-title').textContent === 'Jurnal INV' && node('#ja-page').textContent === 'Halaman 1 / 2', 'source heading and pagination follow active dataset');
    check(node('#ja-table tbody').innerHTML.includes('+ Post'), 'pending manage action uses requested + Post label');
    check(cards.every(card => card.parts['[data-age]'].textContent === 'Tertua: 14/10/2026 (1 hari)'), 'oldest dates display dd/mm/YYYY while retaining age');
    node('#ja-table').fire('click', { target: { closest: selector => selector === '[data-order]' ? { dataset: { order: '6' } } : null } }); await flush();
    check(node('#ja-table thead').innerHTML.includes('aria-sort="ascending"') && calls.filter(call => call[0].endsWith('get_data_jurnal')).at(-1)[1]['order[0][column]'] === '6', 'sort indicator follows debit ordering sent to backend');
    node('#ja-table').fire('click', { target: { closest: selector => selector === '[data-order]' ? { dataset: { order: '6' } } : null } }); await flush();
    check(node('#ja-table thead').innerHTML.includes('aria-sort="descending"'), 'repeat sort updates direction indicator');
    check(node('#ja-next').disabled === false && node('#ja-prev').disabled === true, 'paging first page');
    form.elements.year.value = '2025'; form.elements.month.value = '2'; form.fire('submit'); await flush();
    check(calls.filter(call => call[0].endsWith('get_data_jurnal')).at(-1)[1].year === '2025', 'filter submit');
    tabButtons[2].fire('click'); await flush();
    check(form.elements.year.value === String(new Date().getFullYear()) && node('#ja-category').hidden === false, 'separate tab defaults');
    tabButtons[0].fire('click'); await flush();
    check(form.elements.year.value === '2025' && form.elements.month.value === '2', 'filter retained per tab');
    node('#ja-next').fire('click'); await flush();
    check(calls.filter(call => call[0].endsWith('get_data_jurnal')).at(-1)[1].start === '10', 'next page');
    node('#ja-export').fire('click');
    check(assigned.includes('year=2025') && assigned.includes('month=2'), 'export filters');
    node('#ja-reset').fire('click'); await flush();
    check(form.elements.year.value === String(new Date().getFullYear()) && form.elements.month.value === '', 'active reset');
    form.elements.year.value = ''; form.elements.month.value = '10'; form.fire('submit'); await flush();
    node('#ja-export').fire('click');
    check(assigned.includes('year=&') && assigned.includes('month=10'), 'all-years and month are preserved in export request');
    tabButtons[2].fire('click'); await flush();
    check(form.elements.year.options.some(option => option.value === '' && option.text === 'Semua Tahun') && node('#ja-invoice-period-hint').hidden, 'payment tab also exposes all years while invoice-only hint stays hidden');
    tabButtons[0].fire('click'); await flush();
    check(form.elements.year.value === '' && form.elements.month.value === '10', 'invoice all-years filter survives tab switches');
    node('#ja-reset').fire('click'); await flush();
    node('#ja-table').fire('click', { target: { closest: selector => selector === '[data-id]' ? { dataset: { id: '1', source: 'PAY' } } : null } }); await flush();
    check(modal === 'show' && !node('#ja-post').hidden && !node('#ja-reason-wrap').hidden, 'native action capability flags');
    pending = true; node('#ja-action-form').fire('submit'); node('#ja-action-form').fire('submit');
    check(actionCount === 1, 'double submit blocked');
    releaseAction(); await flush(); await flush();
    check(modal === 'hide', 'successful posting closes modal');
    check(calls.filter(call => call[0].endsWith('save_posting_jurnal')).every(call => call[1].id === '1' && call[1].source === 'PAY' && !('debit' in call[1])), 'no client nominal or company routing');
    pending = false; metadataWarning = true;
    node('#ja-table').fire('click', { target: { closest: selector => selector === '[data-id]' ? { dataset: { id: '1', source: 'PAY' } } : null } }); await flush();
    node('#ja-action-form').fire('submit'); await flush(); await flush();
    check(node('#ja-message').textContent.includes('jangan posting ulang') && node('#ja-message').classes.has('alert-warning'), 'committed posting metadata warning stays visible after list and summary reload');
    metadataWarning = false;

    // SweetAlert2 notification tests
    const swalCalls = [];
    global.Swal = {
        fire: async (opts) => {
            swalCalls.push(opts);
            if (opts.icon === 'question') return { isConfirmed: true };
            return { isConfirmed: true };
        }
    };
    node('#ja-table').fire('click', { target: { closest: selector => selector === '[data-id]' ? { dataset: { id: '1', source: 'PAY' } } : null } }); await flush();
    node('#ja-action-form').fire('submit'); await flush(); await flush();
    check(swalCalls.some(call => call.icon === 'question' && call.title === 'Konfirmasi Posting'), 'Swal confirmation dialog shown');
    check(swalCalls.some(call => call.icon === 'success' && call.title === 'Posting Berhasil!'), 'Swal success notification shown on successful posting');

    // SweetAlert2 error notification test
    const prevFetch = global.fetch;
    global.fetch = async (url, options) => {
        if (url.endsWith('save_posting_jurnal')) {
            return { ok: false, json: async () => ({ status: 0, msg: 'Saldo tidak mencukupi untuk posting.' }) };
        }
        return prevFetch(url, options);
    };
    node('#ja-table').fire('click', { target: { closest: selector => selector === '[data-id]' ? { dataset: { id: '1', source: 'PAY' } } : null } }); await flush();
    node('#ja-action-form').fire('submit'); await flush(); await flush();
    check(swalCalls.some(call => call.icon === 'error' && call.title === 'Posting Gagal!' && call.text === 'Saldo tidak mencukupi untuk posting.'), 'Swal error notification shown on failed posting');
    global.fetch = prevFetch;
    delete global.Swal;

    tabButtons[0].fire('keydown', { key: 'End' }); await flush();
    check(tabButtons[4].focused && tabButtons[4].attributes['aria-selected'] === 'true', 'keyboard tabs');
    const nativeFetch = global.fetch;
    let releaseStale, releaseActive;
    global.fetch = async (url, options) => {
        const response = await nativeFetch(url, options);
        if (url.endsWith('get_data_jurnal') && options.body.get('tab') === 'PAY') await new Promise(resolve => { releaseStale = resolve; });
        if (url.endsWith('get_data_jurnal') && options.body.get('tab') === 'RCV') await new Promise(resolve => { releaseActive = resolve; });
        return response;
    };
    tabButtons[2].fire('click');
    check(!node('#ja-loading').hidden && !node('#ja-table tbody').innerHTML.includes('data-id='), 'tab switch immediately hides stale actions behind loading state');
    check(node('#ja-prev').disabled && node('#ja-next').disabled && node('#ja-export').disabled, 'stale navigation/export disabled while loading');
    await flush();
    tabButtons[1].fire('click'); await flush();
    releaseStale(); await flush();
    check(!node('#ja-loading').hidden, 'older completed request cannot clear current tab loading indicator');
    releaseActive(); await flush();
    check(node('#ja-loading').hidden, 'active request completion clears loading indicator');
    check(node('#ja-category').hidden && !node('#ja-table thead').innerHTML.includes('Kategori Transaksi') && tabButtons[1].attributes['aria-selected'] === 'true', 'late response cannot overwrite active tab');
    global.fetch = async (url, options) => { if (url.endsWith('get_data_jurnal')) throw new Error('Koneksi fixture gagal'); return nativeFetch(url, options); };
    tabButtons[3].fire('click'); await flush();
    check(node('#ja-loading').hidden && !node('#ja-apply').disabled && node('#ja-message').textContent.includes('Koneksi fixture gagal'), 'failed load clears indicator, restores retry controls and shows error');
    global.fetch = async (url, options) => { if (url.endsWith('get_data_jurnal')) return { ok: true, json: async () => ({ recordsFiltered: 0, data: [], totals: { debit: '0', kredit: '0' } }) }; return nativeFetch(url, options); };
    node('#ja-reset').fire('click'); await flush();
    check(node('#ja-table tbody').innerHTML.includes('pilih Reset') && node('#ja-export').disabled && node('#ja-page').textContent === 'Halaman 1 / 1', 'empty results explain recovery and keep export disabled');
    global.fetch = nativeFetch;
    global.fetch = async (url, options) => {
        const result = await nativeFetch(url, options);
        if (!url.endsWith('get_data_jurnal')) return result;
        const page = await result.json();
        page.data[0].source_numbers = 'FINAL-JV-1'; page.data[0].posted_at = '2026-10-15 15:50:00'; page.data[0].posted_by = '<img src=x>';
        return { ok: true, json: async () => page };
    };
    workspace.dataset.posted = '1';
    const beforeReport = calls.length;
    api.mount(workspace);
    check(!node('#ja-loading').hidden, 'report workspace shares the loading indicator');
    await flush();
    check(node('#ja-loading').hidden && calls.slice(beforeReport).every(call => !call[0].endsWith('get_summary')), 'report load completes without pending-summary request');
    check(node('#ja-table tbody').innerHTML.includes('15 Oktober 2026') && node('#ja-table tbody').innerHTML.includes('FINAL-JV-1'), 'report shows confirmed number/clean date');
    global.fetch = async (url, options) => {
        const result = await nativeFetch(url, options);
        if (!url.endsWith('get_data_jurnal')) return result;
        const page = await result.json();
        Object.assign(page.data[0], { debt_group: true, keterangan: '<script>debt</script>', group_status: 'Posting sebagian <img src=x>' });
        return { ok: true, json: async () => page };
    };
    api.mount(workspace); await flush();
    check(node('#ja-table tbody').innerHTML.includes('Posting sebagian &lt;img src=x&gt;') && !node('#ja-table tbody').innerHTML.includes('<script>debt'), 'debt status and description remain escaped');
    console.log('PASS Jurnal All client: ' + checks + ' assertions');
})().catch(error => { console.error(error); process.exitCode = 1; });
