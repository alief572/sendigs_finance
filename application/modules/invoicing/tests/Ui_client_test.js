'use strict';
const assert = require('node:assert/strict');
const client = require('../../../../assets/js/invoicing/consulting-form.js');
class Element {
    constructor() { this.children = []; this.attributes = {}; this.handlers = {}; this.value = ''; this._text = ''; }
    set textContent(value) { this._text = value; this.children = []; }
    get textContent() { return this._text; }
    appendChild(child) { this.children.push(child); }
    setAttribute(key, value) { this.attributes[key] = value; }
    addEventListener(name, callback) { this.handlers[name] = callback; }
    fire(name) { this.handlers[name]({preventDefault() {}}); }
}
function deferred() {
    const callbacks = {};
    const api = {};
    ['done', 'fail', 'always'].forEach(name => { api[name] = callback => { callbacks[name] = callback; return api; }; });
    api.resolve = result => { callbacks.done(result); callbacks.always(); };
    api.reject = () => { callbacks.fail(); callbacks.always(); };
    return api;
}
const selectors = ['#consulting-tax', '#consulting-sales', '#consulting-date', '#consulting-journals', '#consulting-message', '#consulting-save', '#consulting-debit', '#consulting-credit'];
['sales', 'other', 'ppn', 'gross', 'receivable'].forEach(key => selectors.push('[data-total="' + key + '"]'));
const nodes = Object.fromEntries(selectors.map(selector => [selector, new Element()]));
const form = new Element();
form.ownerDocument = {createElement() { return new Element(); }};
form.querySelector = selector => { assert.ok(nodes[selector], selector); return nodes[selector]; };
let valid = true;
form.reportValidity = () => valid;
const accounts = {};
['receivable', 'vat_domestic', 'vat_wapu_prepaid', 'vat_wapu_payable', 'sales_consulting'].forEach((key, i) => { accounts[key] = {code: '4101-01-0' + i, name: key + '<script>'}; });
const data = {amount: '1000.55', accounts, company: 'STM<script>', number: '001/STM/2026', spk: 'SPK-UI', redirect: '/invoicing'};
const requests = [], redirects = [];
nodes['#consulting-date'].value = '2026-10-18';
client.mount(form, data, {request() { const request = deferred(); requests.push(request); return request; }, redirect(url) { redirects.push(url); }});
let checks = 0;
function check(actual, expected, message) { checks++; assert.deepEqual(actual, expected, message); }
function total(key) { return nodes['[data-total="' + key + '"]'].textContent; }
function change(selector, value) { nodes[selector].value = value; nodes[selector].fire('change'); }
const body = nodes['#consulting-journals'];
check(total('sales'), '1,000.55', 'TOP shown initially');
check(total('gross'), '-', 'Tax required for gross');
check(nodes['#consulting-message'].textContent, '', 'First-use state is guidance, not error');
check(body.children[0].children[0].colSpan, 7, 'Empty journal spans columns');
change('#consulting-tax', 'PKP');
check(total('gross'), '1,110.61', 'Half-up tax summary');
check(total('receivable'), '1,110.61', 'PKP gross receivable');
check(nodes['#consulting-debit'].textContent, '0.00', 'No stale debit before account');
change('#consulting-sales', 'sales_consulting');
check(body.children.length, 3, 'PKP journal');
check(nodes['#consulting-debit'].textContent, nodes['#consulting-credit'].textContent, 'Preview balanced');
check(body.children[0].children[1].textContent, 'STM<script>', 'Company rendered as text');
change('#consulting-tax', 'WAPU');
check(body.children.length, 4, 'WAPU journal');
check(total('receivable'), '1,000.55', 'WAPU sales receivable');
check(total('gross'), '1,110.61', 'WAPU displayed gross retained');
change('#consulting-tax', 'KB');
check(body.children.length, 2, 'Bonded-area journal');
check(total('receivable'), '1,000.55', 'Bonded-area sales receivable');
assert.match(body.children[0].children[4].textContent, /Kawasan Berikat/); checks++;
change('#consulting-date', '2026-10-19');
check(body.children[0].children[0].textContent, '2026-10-19', 'Date reflected in preview');
change('#consulting-sales', '');
check(nodes['#consulting-debit'].textContent, '0.00', 'Clearing account clears prior balancing');
check(body.children.length, 1, 'Only guidance after clearing account');
change('#consulting-tax', '');
check(total('gross'), '-', 'Clearing tax clears stale gross');
check(total('receivable'), '-', 'Clearing tax clears stale receivable');
change('#consulting-tax', 'INVALID');
check(nodes['#consulting-message'].attributes['data-state'], 'error', 'Invalid tax error state');
check(nodes['#consulting-debit'].textContent, '0.00', 'Invalid state no stale balancing');
change('#consulting-tax', 'PKP'); change('#consulting-sales', 'sales_consulting');
valid = false; form.fire('submit'); check(requests.length, 0, 'Invalid form no request');
valid = true; form.fire('submit');
check(nodes['#consulting-save'].disabled, true, 'Save disabled while pending');
check(form.attributes['aria-busy'], 'true', 'Busy state announced');
form.fire('submit'); check(requests.length, 1, 'Duplicate pending submit blocked');
requests[0].resolve({status: 0, msg: 'Form kedaluwarsa'});
check(nodes['#consulting-message'].textContent, 'Form kedaluwarsa', 'Server validation preserved');
check(nodes['#consulting-message'].attributes['data-state'], 'error', 'Server error state');
check(nodes['#consulting-save'].disabled, false, 'Save restored after server rejection');
check(form.attributes['aria-busy'], 'false', 'Busy cleared after rejection');
form.fire('submit'); requests[1].reject();
assert.match(nodes['#consulting-message'].textContent, /tidak dapat dikonfirmasi/); checks++;
check(nodes['#consulting-save'].disabled, false, 'Save restored after network failure');
form.fire('submit'); requests[2].resolve(null);
check(nodes['#consulting-message'].attributes['data-state'], 'error', 'Malformed response handled');
check(nodes['#consulting-save'].disabled, false, 'Malformed response restores button');
form.fire('submit'); requests[3].resolve({status: 1});
check(redirects, ['/invoicing'], 'Success redirects to invoice list');
check(nodes['#consulting-message'].attributes['data-state'], 'success', 'Success announced');
check(nodes['#consulting-save'].textContent, 'Simpan invoice', 'Label restored');
console.log('UI client PASS: ' + checks + ' checks');
