const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const view = fs.readFileSync(path.join(__dirname, '../views/form_payment_new.php'), 'utf8');
const script = view.match(/<script>([\s\S]*)<\/script>/)[1].replace(/<\?[\s\S]*?\?>/g, 'false');
const values = new Map([
  ['.total_payment', '0'], ['.input_payment_bank', '980027.00'], ['.bank_charge', '0'],
  ['input[name="admin_charge_bearer"]', 'company'], ['.bank', '18'], ['.tgl_bayar', '2026-11-16'],
  ['.payment_bank_PAY-1', '1000028.0000'], ['.nilai_ppn_PAY-1', '0'],
  ['.nilai_pph_PAY-1', '999.44'], ['.tipe_pph_PAY-1', '23']
]);
const output = new Map(), requests = [], handlers = new Map(), readyCallbacks = [];
const ids = ['PAY-1'];
let emptyCoa = false, useAutoNumeric = false;
function $(selector) {
  let key = selector;
  if (typeof selector === 'object' && selector.field) key = selector.field + '_' + selector.id;
  if (typeof selector === 'string') {
    const type = selector.match(/^\.tipe_pph\[data-id="([^"]+)"\]$/);
    if (type) key = '.tipe_pph_' + type[1];
  }
  const api = {
    val(value) {
      if (arguments.length) { values.set(key, value); return api; }
      return typeof selector === 'object' && 'value' in selector ? selector.value : (values.get(key) ?? '0');
    },
    data(name) { return name === 'id' ? selector.id : (name === 'autoNumeric' && useAutoNumeric); },
    autoNumeric(action, value) { if (action === 'set') values.set(key, value); return api; },
    chosen() { return api; },
    prop(name, value) {
      const property = key + ':' + name;
      if (arguments.length === 2) { output.set(property, value); return api; }
      return output.get(property);
    },
    html(value) { output.set(key, value); return api; },
    text(value) { output.set(key, value); return api; },
    each(callback) {
      if (['.nilai_pph', '.nilai_ppn', '.tipe_pph'].includes(selector)) {
        for (const id of ids) callback.call({ field: selector, id });
      }
      if (emptyCoa && selector === '.tbody_jurnal input[name*="[coa]"]') callback.call({ value: '' });
      return api;
    },
    serialize() {
      const selectors = String(selector).split(', ');
      return JSON.stringify([...values].filter(([name]) => selectors.some(part => name === part || name.startsWith(part + '_'))));
    },
    on(event, target, callback) { handlers.set(event + ':' + target, callback); return api; },
    ready(callback) { readyCallbacks.push(callback); return api; },
    closest() { return api; }, after() { return api; }, remove() { return api; },
    addClass() { return api; }, removeClass() { return api; }, toggleClass() { return api; }
  };
  return api;
}
$.ajax = options => { requests.push(options); };
const warnings = [];
const context = vm.createContext({ $, document: { getElementById() { return null; } },
  window: {}, siteurl: '/', active_controller: 'pembayaran_material/',
  DataTransfer: class { constructor() { this.files = []; } }, swal: value => warnings.push(value) });
vm.runInContext(script, context);
for (const [value, expected] of [['14,000.56', 1400056], ['14000.5600', 1400056], ['0.01', 1], ['14000', 1400000]]) {
  assert.equal(context.paymentCents(value), expected);
}
for (const invalid of ['bad', '-1', '1,00', '1.001', '90071992547409999']) assert.ok(Number.isNaN(context.paymentCents(invalid)));
for (const [type, amount, ppn, expected] of [
  ['23', '1000028', '0', 20001], ['21', '1000028', '0', 25001],
  ['23', '1000000.50', '0', 20001], ['23', '1000049.50', '0', 20001],
  ['21', '1000000.40', '0', 25001], ['21', '1000039.60', '0', 25001],
  ['23', '999999.99', '0.01', 20000], ['21', '999999.99', '0.01', 25000],
  ['', '1000028', '', 0], ['23', '0', '0', 0]
]) assert.equal(context.calculatePPh(type, amount, ppn), expected);
assert.ok(Number.isNaN(context.calculatePPh('23', 'bad', '0')));
assert.ok(Number.isNaN(context.calculatePPh('bad', '100', '0')));
assert.ok(!view.includes('payment-rounding'));
assert.match(view, /class="[^"\n]*nilai_pph[^"\n]*"[^\n]*readonly/);

for (const callback of readyCallbacks) callback();
assert.equal(values.get('.nilai_pph_PAY-1'), '20001.00', 'Load recomputes approved PPh');
assert.equal(values.get('.total_payment'), '980027.00');
assert.equal(requests.at(-1).data.item_pph['PAY-1'], 20001);
const changed = handlers.get('change:.tipe_pph, .change_nilai_ppn');
useAutoNumeric = true;
values.set('.nilai_ppn_PAY-1', '110003.08');
changed();
assert.equal(values.get('.nilai_pph_PAY-1'), '22201.00', 'PPN change recalculates PPh');
assert.equal(values.get('.total_ppn'), '110003.08');
assert.equal(values.get('.total_payment'), '1087830.08', 'Fractional PPN remains fractional');
values.set('.nilai_ppn_PAY-1', '0');
values.set('.tipe_pph_PAY-1', '21');
changed();
assert.equal(values.get('.nilai_pph_PAY-1'), '25001.00', 'Type change recalculates PPh');
assert.equal(values.get('.total_payment'), '975027.00');
values.set('.tipe_pph_PAY-1', '');
changed();
assert.equal(values.get('.nilai_pph_PAY-1'), '0.00', 'Clearing type resets PPh');
assert.equal(values.get('.total_payment'), '1000028.00');
values.set('.tipe_pph_PAY-1', '23');
ids.push('PAY-2');
for (const [field, value] of [['payment_bank', '1000028'], ['nilai_ppn', '0'], ['nilai_pph', '999'], ['tipe_pph', '23']]) {
  values.set('.' + field + '_PAY-2', value);
}
changed();
assert.equal(values.get('.total_pph'), '40002.00', 'Round per item before sum');
assert.equal(values.get('.total_payment'), '1960054.00');
assert.equal(requests.at(-1).data.item_pph['PAY-2'], 20001);
ids.pop();
changed();

function ready() {
  context.journalReady = true;
  context.journalError = '';
  context.journalSnapshot = context.paymentPreviewKey();
  context.hitung_kontrol();
}
ready();
assert.equal(values.get('.kontrol'), 0);
assert.equal(output.get('#simpan-com:disabled'), false);
values.set('.bank_charge', '2500.25');
values.set('.input_payment_bank', '982527.25');
ready();
assert.equal(values.get('.kontrol'), 0);
values.set('input[name="admin_charge_bearer"]', 'recipient');
values.set('.input_payment_bank', '980027.00');
ready();
assert.equal(values.get('.kontrol'), 0);
values.set('.input_payment_bank', '980028.00');
ready();
assert.equal(values.get('.kontrol'), 1, 'No bank ceiling or tolerance');
assert.equal(output.get('#simpan-com:disabled'), true);
handlers.get('submit:#frm-data')({ preventDefault() {} });
assert.ok(warnings.at(-1).text.includes('kontrol 0'));
values.set('.input_payment_bank', '980027.00');
ready();
context.journalError = 'Nominal PPh tidak sesuai';
context.hitung_kontrol();
assert.equal(output.get('#simpan-com:disabled'), true);
assert.equal(output.get('.payment-preview-warning'), 'Nominal PPh tidak sesuai');
emptyCoa = true;
ready();
assert.equal(output.get('#simpan-com:disabled'), true);
emptyCoa = false;
ready();
values.set('.tgl_bayar', '2026-11-17');
context.hitung_kontrol();
assert.equal(output.get('#simpan-com:disabled'), true, 'Changed input invalidates previous preview');
context.set_jurnal();
const old = requests.at(-1);
context.set_jurnal();
const current = requests.at(-1);
const response = { hasil_jurnal: 'LATEST', is_all_petty_cash_hutang: false, is_refill_pettycash: false,
  preview_error: '', ttl_debit: 1000028, ttl_kredit: 1000028 };
current.success(response);
old.success({ ...response, hasil_jurnal: 'STALE' });
assert.equal(output.get('.tbody_jurnal'), 'LATEST');
assert.equal(context.journalReady, true);
old.error();
assert.equal(context.journalReady, true, 'Old failure cannot invalidate latest preview');
current.error();
assert.equal(context.journalReady, false);
assert.equal(output.get('#simpan-com:disabled'), true);
context.set_jurnal();
const beforeInvalid = requests.at(-1);
values.set('.nilai_ppn_PAY-1', '1.001');
changed();
beforeInvalid.success(response);
assert.equal(context.journalReady, false, 'Old success cannot clear invalid local input');
assert.equal(output.get('#simpan-com:disabled'), true);
assert.ok(context.journalError.includes('tidak valid'));
values.set('.nilai_ppn_PAY-1', '0');
context.is_refill_pettycash = true;
values.set('.payment_bank_PAY-1', '1000028.56');
values.set('.nilai_pph_PAY-1', '20000.56');
changed();
assert.equal(values.get('.nilai_pph_PAY-1'), '20000.56', 'Refill keeps existing PPh');
assert.equal(values.get('.total_payment'), '980028.00');
context.is_refill_pettycash = false;
context.is_all_petty_cash_hutang = true;
changed();
assert.equal(values.get('.nilai_pph_PAY-1'), '20000.56', 'Petty cash hutang keeps existing PPh');
context.is_all_petty_cash_hutang = false;
changed();
values.set('.input_payment_bank', '980027.56');
ready();
assert.equal(values.get('.kontrol'), 0, 'Fractional source has exact control without bank ceiling');
console.log('PASS PPh rounding client: load, type, PPN, multi-item, integer math, control, preview guards, special cash');
