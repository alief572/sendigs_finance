'use strict';
const assert = require('assert');
const factory = require('../../../../assets/js/invoicing/vuca-form.js');
let checks = 0, submitted = null, requests = 0;
function check(ok) { checks++; assert.ok(ok); }
function node() { return {value: '', textContent: '', hidden: false, disabled: true, handlers: {}, addEventListener(type, fn) { this.handlers[type] = fn; }, focus() { this.focused = true; }}; }
const elements = {};
for (const selector of ['[name="sales_role"]', '#vuca-journals', '#vuca-journal-empty', '#vuca-save', '#vuca-message', '[data-vuca-sales-code]', '[data-vuca-sales-name]', '[data-vuca-code-input]', '[data-vuca-name-input]', '#vuca-debit', '#vuca-credit']) elements[selector] = node();
const form = node(); form.action = '/invoicing/save_invoice_vuca'; form.querySelector = selector => elements[selector]; form.getAttribute = key => form[key];
const root = {location: {}, InvoiceSaveFeedback: {submit(options) { submitted = options; }}, jQuery: Object.assign(() => ({serialize: () => 'id=A1&sales_role=sales_hse'}), {ajax(options) {requests++; assert.strictEqual(options.url, form.action); assert.strictEqual(options.type, 'POST'); return options; }})};
const accounts = {};
for (const [i, role] of ['sales_consulting', 'sales_inhouse', 'sales_public', 'sales_hse'].entries()) accounts[role] = {code: '4101-01-0' + (i + 1), name: '<script>Account ' + role + '</script>'};
factory(root).mount(form, {accounts, total: '1,000,000.00', redirect: '/invoicing'});
check(elements['#vuca-journals'].hidden && !elements['#vuca-journal-empty'].hidden);
check(!elements['#vuca-save'].disabled);
form.handlers.submit({preventDefault() {}}); check(submitted === null && elements['[name="sales_role"]'].focused);
for (const role of Object.keys(accounts)) {
    elements['[name="sales_role"]'].value = role;
    elements['[name="sales_role"]'].handlers.change();
    check(!elements['#vuca-journals'].hidden && elements['#vuca-journal-empty'].hidden);
    check(elements['[data-vuca-sales-code]'].textContent === accounts[role].code);
    check(elements['[data-vuca-sales-name]'].textContent === accounts[role].name);
    check(elements['[data-vuca-code-input]'].value === accounts[role].code);
    check(elements['#vuca-debit'].textContent === elements['#vuca-credit'].textContent && elements['#vuca-debit'].textContent === '1,000,000.00');
}
form['aria-busy'] = 'true'; form.handlers.submit({preventDefault() {}}); check(submitted === null);
form['aria-busy'] = 'false'; form.handlers.submit({preventDefault() {}}); check(submitted.form === form && submitted.button === elements['#vuca-save']);
submitted.request(); check(requests === 1); submitted.onSuccess(); check(root.location.href === '/invoicing');
for (const value of ['', 'invalid', '__proto__']) {
    elements['[name="sales_role"]'].value = value; elements['[name="sales_role"]'].handlers.change();
    check(elements['#vuca-journals'].hidden && elements['[data-vuca-code-input]'].value === '' && elements['#vuca-debit'].textContent === '0.00');
}
console.log('PASS: ' + checks + ' Vuca client checks');
