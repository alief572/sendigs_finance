const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const source = fs.readFileSync(path.join(__dirname, '../../../../assets/js/pembayaran_material/payment_selection.js'), 'utf8');
let checks = 0;
function test(ok, message) { assert(ok, message); checks++; }
function setup() {
    const handlers = new Map(), requests = [], alerts = [], reloads = [];
    const boxes = [box('A', false, false), box('B', false, true)];
    const buttons = {disabled: false};
    let config, confirm;
    function box(value, checked, incompatible) {
        return {value, checked, disabled: incompatible, incompatible,
            val() { return this.value; },
            prop(key, val) { if (val === undefined) return this[key]; this[key] = val; return this; },
            attr(key) { return key === 'data-selection-disabled' ? (this.incompatible ? '1' : '0') : null; }};
    }
    const api = {ajax: {reload(callback, reset) { reloads.push({callback, reset}); handlers.get('preXhr.dt')(); }}};
    const table = {
        on(event, handler) { handlers.set(event, handler); return this; },
        attr() { return '2'; },
        DataTable(options) { config = options; return api; }
    };
    const document = {};
    function $(arg) {
        if (arg === document) return {on(event, selector, handler) { handlers.set(selector, handler); return this; }};
        if (arg === '#table_list_req_payment') return table;
        if (typeof arg === 'object') return arg;
        if (arg === '.check_payment') return {each(fn) { boxes.forEach(b => fn.call(b)); return this; }};
        return {prop(key, value) { buttons[key] = value; return this; }};
    }
    $.ajax = options => {
        const deferred = {
            done(fn) { this.success = fn; return this; }, fail(fn) { this.failure = fn; return this; },
            always(fn) { this.complete = fn; return this; },
            resolve(data) { if (options.success) options.success(data); if (this.success) this.success(data); if (options.complete) options.complete(); if (this.complete) this.complete(); },
            reject() { if (options.error) options.error(); if (this.failure) this.failure(); if (options.complete) options.complete(); if (this.complete) this.complete(); }
        };
        requests.push({options, deferred}); return deferred;
    };
    function swal(options, callback) { alerts.push(options); if (callback) confirm = callback; }
    swal.close = () => {};
    const sandbox = {jQuery: $, document, swal, siteurl: '/', active_controller: 'pembayaran_material/', window: {location: {href: ''}}};
    vm.runInNewContext(source, sandbox);
    function draw() { handlers.get('draw.dt')(); }
    function click(selector, b = boxes[0]) { handlers.get(selector).call(b); }
    function reloadDone() { const r = reloads[reloads.length - 1]; draw(); if (r.callback) r.callback(); }
    return {boxes, buttons, requests, alerts, reloads, config, sandbox, draw, click, reloadDone,
        confirm(value) { confirm(value); }, handlers};
}
let s = setup(); s.draw();
test(!s.boxes[0].disabled && s.boxes[1].disabled, 'Draw preserves server compatibility');
s.boxes[0].checked = true; s.click('.check_payment');
test(s.requests[0].options.data.checked === 1 && s.requests[0].options.dataType === 'json', 'Posts checked state as JSON request');
test(s.buttons.disabled && s.boxes.every(b => b.disabled), 'Mutation locks controls');
s.click('.proses_payment'); s.click('.clear_choosed_payment'); s.click('.check_payment');
test(s.requests.length === 1 && !s.alerts.length, 'No overlapping actions');
s.requests[0].deferred.resolve({status: 1});
test(s.reloads.length === 1 && s.reloads[0].reset === false && s.buttons.disabled, 'Reload preserves page and keeps controls locked');
s.boxes[0].incompatible = false; s.boxes[1].incompatible = true; s.reloadDone();
test(!s.buttons.disabled && !s.boxes[0].disabled && s.boxes[1].disabled, 'Unlock after authoritative redraw');
for (const failure of ['business', 'network']) {
    s = setup(); s.draw(); s.boxes[0].checked = true; s.click('.check_payment');
    if (failure === 'business') s.requests[0].deferred.resolve({status: 0, pesan: 'Kelompok berbeda'});
    else s.requests[0].deferred.reject();
    test(!s.boxes[0].checked && s.alerts.length === 1 && s.reloads.length === 1, 'Restore checkbox and resync on ' + failure);
    s.reloadDone(); test(!s.buttons.disabled, 'Failure releases controls');
}
s = setup(); s.draw(); s.click('.clear_choosed_payment');
test(s.buttons.disabled, 'Confirmation locks selection'); s.confirm(false);
test(!s.buttons.disabled && !s.requests.length, 'Cancel clear restores controls');
s.click('.clear_choosed_payment'); s.confirm(true); s.requests[0].deferred.resolve({status: 0, pesan: 'Clear gagal'});
test(s.alerts.some(a => a.text === 'Clear gagal'), 'Clear failure visible'); s.reloadDone();
s.click('.clear_choosed_payment'); s.confirm(true); s.requests[1].deferred.resolve({status: 1});
s.boxes.forEach(b => { b.checked = false; b.incompatible = false; }); s.reloadDone();
test(s.boxes.every(b => !b.disabled), 'Successful clear unlocks all groups');
for (const result of [{status: 0, pesan: 'Campuran', count_choosed_payment: 2}, {status: 1, count_choosed_payment: 0}]) {
    s = setup(); s.draw(); s.click('.proses_payment'); s.requests[0].deferred.resolve(result);
    test(s.sandbox.window.location.href === '' && s.alerts.length === 1, 'Invalid or empty process cannot redirect');
}
s = setup(); s.draw(); s.click('.proses_payment');
s.requests[0].deferred.resolve({status: 1, count_choosed_payment: 2, arr_choosed_payment: 'A;B', has_admin_fee: true});
test(s.sandbox.window.location.href.includes('id_payment=A%3BB') && s.sandbox.window.location.href.includes('admin_charge_bearer=recipient'), 'Valid process retains admin behavior');
s = setup(); s.draw(); s.handlers.get('preXhr.dt')();
test(s.buttons.disabled && s.boxes.every(b => b.disabled), 'Paging/search locks stale rows'); s.draw();
test(!s.buttons.disabled && s.boxes[1].disabled, 'Paging redraw retains restrictions');
s.config.ajax.dataSrc({data: [], selection_error: 'Sumber hilang'});
test(s.alerts[0].text === 'Sumber hilang', 'Listing metadata failures visible');
s = setup(); s.draw(); s.click('.proses_payment'); s.requests[0].deferred.reject();
test(s.alerts.length === 1 && !s.buttons.disabled && !s.sandbox.window.location.href, 'Process network failure releases controls');
s = setup(); s.draw(); s.click('.clear_choosed_payment'); s.confirm(true); s.requests[0].deferred.reject();
test(s.alerts.length === 2 && s.reloads.length === 1, 'Clear network failure resyncs selection'); s.reloadDone();
test(!s.buttons.disabled, 'Clear network failure releases controls after redraw');
s = setup(); s.draw(); s.handlers.get('preXhr.dt')();
test(s.handlers.get('xhr.dt')(null, null, null) === true && !s.buttons.disabled && s.alerts.length === 1, 'Table error is handled and controls recover');
s = setup(); s.draw(); s.boxes[0].checked = false; s.click('.check_payment');
test(s.requests[0].options.data.checked === 0, 'Unchecking posts removal'); s.requests[0].deferred.resolve({status: 1});
s.boxes.forEach(b => { b.incompatible = false; }); s.reloadDone();
test(s.boxes.every(b => !b.disabled), 'Unchecking last row unlocks all groups on redraw');
s = setup(); s.draw(); s.click('.proses_payment');
s.requests[0].deferred.resolve({status: 1, count_choosed_payment: 1, arr_choosed_payment: 'A', has_admin_fee: false});
test(s.sandbox.window.location.href.includes('admin_charge_bearer=company'), 'No admin fee retains company bearer');
console.log('PASS Selection client: ' + checks + ' checks');
