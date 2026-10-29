const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../views/user_permissions.php'), 'utf8');
const script = source.match(/<script>([\s\S]*?)<\/script>/)[1];
const elements = [];
const make = (id, value = '', classes = [], disabled = false) => {
  const element = { id, value, classes, disabled, checked: disabled, handlers: {}, data: {} };
  elements.push(element);
  return element;
};
const headers = {};
for (const action of ['view', 'add', 'manage', 'delete', 'all']) headers[action] = make(`chk_${action}`);
const rows = [make('row282', '', ['all_baris']), make('row279', '', ['all_baris'])];
rows[0].data.id = 282;
rows[1].data.id = 279;
const fields = {};
for (const menu of [282, 279]) {
  fields[menu] = ['View', 'Add', 'Manage', 'Delete'].map((action, index) => {
    const field = make(`${menu}-${action}`, String(955 + index), [`det${action}`, `menuID${menu}`]);
    field.name = 'id_permissions[]';
    return field;
  });
}
const role = make('role', '1200', ['detView', 'menuID999'], true);
role.name = 'id_permissions[]';
class Selection {
  constructor(items) { this.items = items; }
  on(event, callback) { this.items.forEach(item => { item.handlers[event] = callback; }); return this; }
  click(callback) { return this.on('click', callback); }
  filter(callback) { return new Selection(this.items.filter((item, index) => callback.call(item, index, item))); }
  not(value) { return new Selection(this.items.filter(item => value === ':disabled' ? !item.disabled : item !== value)); }
  prop(name, value) { this.items.forEach(item => { item[name] = value; }); return this; }
  data(key) { return this.items[0].data[key]; }
  trigger(event) { this.items.forEach(item => { if (item.handlers[event]) item.handlers[event].call(item); }); return this; }
}
const document = {};
function $(selector) {
  if (selector === document) return { ready: callback => callback() };
  if (typeof selector !== 'string') return new Selection([selector]);
  return new Selection(elements.filter(item => {
    if (selector === 'input:checkbox') return true;
    if (selector === 'input[name="id_permissions[]"]') return item.name === 'id_permissions[]';
    if (selector.startsWith('#')) return item.id === selector.slice(1);
    if (selector.startsWith('.')) return item.classes.includes(selector.slice(1));
    throw new Error(`Unexpected selector ${selector}`);
  }));
}
vm.runInNewContext(script, { $, document });
let checks = 0;
function check(actual, expected, message) { checks++; assert.equal(actual, expected, message); }
function click(element, checked) { element.checked = checked; $(element).trigger('click'); }
fields[282][0].checked = true;
$(fields[282][0]).trigger('change');
check(fields[279][0].checked, true, 'Selecting parent View updates leaf alias');
fields[279][0].checked = false;
$(fields[279][0]).trigger('change');
check(fields[282][0].checked, false, 'Unselecting leaf View updates parent alias');
click(rows[0], true);
for (const field of fields[279]) check(field.checked, true, 'Row Check All selects matching alias');
click(rows[1], false);
for (const field of fields[282]) check(field.checked, false, 'Row deselection clears matching alias');
for (const action of ['view', 'add', 'manage', 'delete']) {
  click(headers[action], true);
  const index = ['view', 'add', 'manage', 'delete'].indexOf(action);
  check(fields[279][index].checked && fields[282][index].checked, true, 'Column selection stays synchronized');
  click(headers[action], false);
  check(fields[279][index].checked || fields[282][index].checked, false, 'Column deselection stays synchronized');
  check(role.checked, true, 'Role permission is immutable');
}
click(headers.all, true);
for (const field of fields[279]) check(field.checked, true, 'Global Check All includes Expense actions');
click(headers.all, false);
for (const field of fields[282]) check(field.checked, false, 'Global deselection clears Expense actions');
check(role.checked, true, 'Global deselection preserves disabled role permission');
console.log(`PASS Permission menu client: ${checks} checks`);
