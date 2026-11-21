const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const view = fs.readFileSync(path.join(__dirname, '../views/index.php'), 'utf8');
const script = view.match(/<script type="text\/javascript">([\s\S]*?)<\/script>/)[1]
    .replace(/<\?[\s\S]*?\?>/g, '/');

function boot(storage = new Map(), blocked = false) {
    const handlers = new Map();
    const elements = new Map();
    const requests = [];
    let ready, options, table, initializations = 0, active = '1', outcome = 'success';
    function element(selector) {
        if (elements.has(selector)) return elements.get(selector);
        const el = {
            value: selector === '#bulan' ? '1' : selector === '.inp_tahun' ? '2026' : '',
            length: selector === undefined ? 0 : 1, 0: {},
            val(value) { if (value === undefined) return this.value; this.value = String(value); return this; },
            ready(fn) { ready = fn; },
            on(event, target, fn) { handlers.set(event + ':' + target, fn); },
            data() { return this.value; },
            removeClass() { active = null; return this; },
            addClass() { active = this.value || (selector.match(/data-no="([^"]+)"/) || selector.match(/tab_(\d+)/) || [])[1]; return this; },
            modal() { return this; }, prop() { return this; }, append() { return this; }, remove() { return this; },
            DataTable(config) {
                if (!config) return table;
                initializations++;
                options = config;
                table = {
                    search: '', start: 0,
                    ajax: {reload(callback, reset) {
                        assert.strictEqual(reset, false, 'refresh must retain page');
                        request();
                    }}
                };
                request();
                return table;
            },
            dataTable(config) { return this.DataTable(config); }
        };
        elements.set(selector, el);
        return el;
    }
    function request() {
        const data = {search: {value: table.search}, start: table.start};
        options.ajax.data(data);
        requests.push(data);
    }
    const document = {};
    const $ = arg => typeof arg === 'object' && arg !== document ? arg : element(arg === document ? 'document' : arg);
    $.fn = {dataTable: {isDataTable: () => !!table}};
    $.ajax = config => outcome === 'error'
        ? config.error({responseText: '{"msg":"Failed"}'})
        : config.success({status: outcome === 'success' ? '1' : '0', msg: 'Result'});
    const context = {
        $, document, siteurl: '/', active_controller: 'actual_plan_tagih/',
        sessionStorage: {
            getItem(key) { if (blocked) throw Error('blocked'); return storage.get(key) || null; },
            setItem(key, value) { if (blocked) throw Error('blocked'); storage.set(key, String(value)); }
        },
        Swal: {fire: () => ({then: fn => fn({isConfirmed: true})}), close() {}},
        FormData: function() {}, location: {reload() { assert.fail('process must not reload page'); }}
    };
    vm.createContext(context);
    vm.runInContext(script, context);
    ready();
    return {
        context, storage, requests, element,
        set outcome(value) { outcome = value; },
        get table() { return table; }, get options() { return options; },
        get active() { return active; }, get initializations() { return initializations; },
        fire(event, selector, value) {
            const target = element(selector);
            if (value !== undefined) target.val(value);
            handlers.get(event + ':' + selector).call(target, {preventDefault() {}});
        }
    };
}

const app = boot();
app.table.search = 'Customer STM';
app.table.start = 20;
for (const month of ['6', '12', 'macet', '1']) {
    app.fire('click', '.tab_pin', month);
    assert.strictEqual(app.initializations, 1, 'month switch must not recreate table');
    assert.strictEqual(app.active, month, 'clicked month must stay active');
    assert.strictEqual(app.requests.at(-1).bulan, month);
    assert.strictEqual(app.requests.at(-1).search.value, 'Customer STM');
    assert.strictEqual(app.requests.at(-1).start, 20);
}
app.fire('change', '.inp_tahun', '2025');
app.fire('change', '#status', 'waiting');
assert.strictEqual(app.requests.at(-1).tahun, '2025');
assert.strictEqual(app.requests.at(-1).status, 'waiting');
for (const outcome of ['success', 'rejected', 'error']) {
    app.outcome = outcome;
    for (const form of ['#frm-data', '#frm-data-macet']) {
        const before = app.requests.length;
        app.fire('submit', form);
        assert.strictEqual(app.requests.length, before + (outcome === 'success' ? 1 : 0));
        assert.strictEqual(app.table.search, 'Customer STM');
        assert.strictEqual(app.table.start, 20);
        assert.strictEqual(app.initializations, 1, 'process must reuse table');
    }
}
app.outcome = 'success';
app.fire('click', '.btn_batch_process');
vm.runInContext('update_actual_plan_tagih()', app.context);
assert.strictEqual(app.initializations, 1);
assert.strictEqual(app.table.search, 'Customer STM');
assert.strictEqual(app.options.stateSave, true, 'DataTables saves search/page for navigation back');
assert.strictEqual(app.options.stateDuration, -1, 'saved table state belongs to browser session');
app.fire('click', '.tab_pin', 'macet');
const restored = boot(app.storage);
assert.strictEqual(restored.active, 'macet');
assert.strictEqual(restored.requests[0].bulan, 'macet');
app.table.search = '';
app.fire('click', '.tab_pin', '5');
assert.strictEqual(app.requests.at(-1).search.value, '', 'cleared search stays cleared');
assert.doesNotThrow(() => boot(new Map(), true).fire('click', '.tab_pin', '8'));
assert.strictEqual(boot(new Map([['apt_bulan', '"]invalid[']])).requests[0].bulan, '1');
console.log('PASS Actual Plan Tagih client state: tabs, search, paging, process, filters, session and blocked storage');
