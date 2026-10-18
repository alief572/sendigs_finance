'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs'), path = require('node:path'), vm = require('node:vm');
const search = require('../../../assets/js/sidebar-search.js');
class Node {
    constructor(tag, text = '', classes = []) { this.tagName = tag.toUpperCase(); this._text = text; this.children = []; this.style = {display: ''}; this.hidden = false; this.attributes = {}; this.events = {}; this.classes = new Set(classes); this.classList = {contains: name => this.classes.has(name), toggle: (name, value) => value ? this.classes.add(name) : this.classes.delete(name)}; }
    append(child) { child.parentNode = this; this.children.push(child); return child; }
    removeChild(child) { this.children.splice(this.children.indexOf(child), 1); child.parentNode = null; }
    get textContent() { return this._text + this.children.map(child => child.textContent).join(''); }
    contains(other) { return this === other || this.children.some(child => child.contains(other)); }
    querySelectorAll(selector) { const selectors = selector.split(','); const result = []; function visit(node) { node.children.forEach(child => { if (selectors.some(item => item[0] === '.' ? child.classes.has(item.slice(1)) : child.tagName === item.toUpperCase())) result.push(child); visit(child); }); } visit(this); return result; }
    cloneNode() { const clone = new Node(this.tagName, this._text, [...this.classes]); clone.attributes = {...this.attributes}; this.children.forEach(child => clone.append(child.cloneNode(true))); return clone; }
    getAttribute(name) { return this.attributes[name]; }
    setAttribute(name, value) { this.attributes[name] = value; }
    addEventListener(name, fn) { (this.events[name] ||= []).push(fn); }
    fire(name, event = {}) { (this.events[name] || []).forEach(fn => fn(event)); }
    focus() { focused = this; }
}
let checks = 0, focused;
function check(actual, expected, reason) { checks++; assert.deepEqual(actual, expected, reason); }
function item(list, name, group = false) { const li = list.append(new Node('li', '', group ? ['treeview'] : [])); const link = li.append(new Node('a')); link.attributes.href = '/' + name; link.append(new Node('i', '', ['fa'])); link.append(new Node('span', name)); if (group) link.append(new Node('span', 'arrow decoration', ['pull-right-container'])); return li; }
const navigation = new Node('div'), main = navigation.append(new Node('ul'));
const header = main.append(new Node('li', '', ['header']));
const dashboard = item(main, 'Dashboard');
const finance = item(main, 'Finance', true), financeList = finance.append(new Node('ul', '', ['treeview-menu'])); financeList.style.display = 'none';
const invoice = item(financeList, '  Invoice   Konsultasi  '), receipt = item(financeList, 'Penerimaan');
const reports = item(financeList, 'Report', true), reportList = reports.append(new Node('ul', '', ['treeview-menu'])); reportList.style.display = 'none';
const ledger = item(reportList, 'Jurnal (A+B) <draft>'); ledger.classes.add('active');
const master = item(main, 'Master Data', true), masterList = master.append(new Node('ul', '', ['treeview-menu'])); masterList.style.display = 'none';
const customer = item(masterList, 'Customer'), hidden = item(main, 'Menu tersembunyi'); hidden.style.display = 'none';
const component = new Node('div'); component.hidden = true;
const input = new Node('input'), clear = new Node('button'), empty = new Node('p'), mini = new Node('button'); clear.hidden = empty.hidden = true;
const controls = {'#sidebar-menu-search': input, '#sidebar-search-clear': clear, '#sidebar-search-empty': empty, '#sidebar-search-mini-btn': mini}; component.querySelector = selector => controls[selector];
let clock = 0, nextId = 0, expanded = 0; const timers = new Map();
const originalCount = navigation.querySelectorAll('li').length;
function advance(ms) { clock += ms; for (const [id, entry] of [...timers]) if (entry.at <= clock) { timers.delete(id); entry.fn(); } }
search.mount(component, navigation, {schedule(fn, ms) { const id = ++nextId; timers.set(id, {at: clock + ms, fn}); return id; }, cancel(id) { timers.delete(id); }, expand() { expanded++; }});
function type(value) { input.value = value; input.fire('input'); }
function filter(value) { type(value); advance(50); }
check(component.hidden, false, 'Search visible only after mount');
check(financeList.style.display, 'none', 'Mount does not expand navigation');
finance.classes.add('menu-open'); financeList.style.display = 'block';
const originalLabel = invoice.children[0].textContent, originalLink = invoice.children[0], originalIcon = originalLink.children[0];
originalLink.addEventListener('click', () => { clicked++; }); let clicked = 0;
type(' INVOICE   konsultasi '); advance(49);
check(master.style.display, '', 'Debounce does not run early');
advance(1);
check(invoice.style.display, '', 'Whitespace and case normalized');
check(finance.style.display, '', 'Parent shown for matching child');
check(financeList.style.display, 'block', 'Parent group expanded');
check(receipt.style.display, 'none', 'Nonmatching sibling hidden');
check(master.style.display, 'none', 'Nonmatching group hidden');
check(header.style.display, 'none', 'Header hidden during filtering');
check(clear.hidden, false, 'Clear available');
check(empty.hidden, true, 'Results do not show empty message');
check(invoice.children[0], originalLink, 'Link node retained');
check(invoice.children[0].children[0], originalIcon, 'Icon node retained');
check(invoice.children[0].textContent, originalLabel, 'Label markup unchanged');
originalLink.fire('click'); check(clicked, 1, 'Existing event handler retained');
check(originalLink.attributes.href, '/  Invoice   Konsultasi  ', 'Href retained');
filter('jurnal (a+b) <draft>');
check(ledger.style.display, '', 'Special characters treated as literal text');
check(reports.style.display, '', 'Intermediate ancestor shown');
check(reportList.style.display, 'block', 'Nested group opened');
check(finance.style.display, '', 'All ancestors visible');
filter('finance');
check(invoice.style.display, '', 'Group match shows invoice descendant');
check(receipt.style.display, '', 'Group match shows sibling descendant');
check(ledger.style.display, '', 'Group match shows nested descendants');
filter('not rendered permission menu');
check(empty.hidden, false, 'No permission-only menu can be found');
check(navigation.querySelectorAll('li').length, originalCount, 'Search never creates additional menu nodes');
filter('menu tersembunyi'); check(empty.hidden, false, 'Explicitly hidden entry not exposed'); check(hidden.style.display, 'none', 'Hidden entry stays hidden');
type('invoice'); type('customer'); advance(50);
check(customer.style.display, '', 'Latest debounce input wins');
check(invoice.style.display, 'none', 'Cancelled query not applied');
let prevented = false, stopped = false;
input.fire('keydown', {key: 'Escape', preventDefault() { prevented = true; }, stopPropagation() { stopped = true; }});
check(prevented && stopped, true, 'Escape handled without closing mobile sidebar');
check(input.value, '', 'Escape clears input');
check(focused, input, 'Escape keeps focus');
check(financeList.style.display, 'block', 'Restores state at start of search, not page load');
check(finance.classes.has('menu-open'), true, 'Pre-search expanded group restored');
check(masterList.style.display, 'none', 'Pre-search closed group restored');
check(reportList.style.display, 'none', 'Nested state restored');
check(ledger.classes.has('active'), true, 'Active menu untouched');
check(master.style.display, '', 'All eligible menus restored');
check(hidden.style.display, 'none', 'Originally hidden menu remains hidden after reset');
check(header.style.display, '', 'Header restored');
check(clear.hidden && empty.hidden, true, 'Reset hides controls/status');
filter('invoice'); clear.fire('click'); check(input.value, '', 'Clear button resets'); check(focused, input, 'Clear button focuses input');
type('invoice'); clear.fire('click'); advance(100); check(master.style.display, '', 'Clear cancels pending filter');
mini.fire('click'); check(expanded, 1, 'Mini button invokes existing sidebar expansion'); check(focused, input, 'Mini button focuses input');
filter('invoice'); type('   '); advance(50); check(master.style.display, '', 'Whitespace-only query resets');
const listenerCount = input.events.input.length; search.mount(component, navigation); check(input.events.input.length, listenerCount, 'Repeated script initialization does not duplicate handlers');
const absent = new Node('div'); absent.querySelector = () => null; check(search.mount(absent, navigation), undefined, 'Missing markup is harmless');
const source = fs.readFileSync(path.join(__dirname, '../../../assets/js/sidebar-search.js'), 'utf8');
check(/\bfetch\s*\(|XMLHttpRequest|\$\.ajax/.test(source), false, 'Search has no network request implementation');
delete component.attributes['data-search-ready']; component.hidden = true;
[input, clear, empty, mini].forEach(control => { control.events = {}; });
const body = new Node('body', '', ['sidebar-collapse']); let toggleCount = 0, domReady;
const browserDocument = {readyState: 'loading', body, getElementById(id) { return id === 'sidebar-search' ? component : navigation; }, querySelector(selector) { assert.equal(selector, '[data-toggle="offcanvas"]'); return {click() { toggleCount++; body.classes.delete('sidebar-collapse'); }}; }, addEventListener(name, callback) { assert.equal(name, 'DOMContentLoaded'); domReady = callback; }};
vm.runInNewContext(source, {window: {document: browserDocument}, setTimeout, clearTimeout});
check(component.hidden, true, 'Browser waits for DOM readiness'); domReady(); check(component.hidden, false, 'Browser auto-mounts search');
mini.fire('click'); check(toggleCount, 1, 'Browser adapter uses existing AdminLTE toggle'); check(body.classes.has('sidebar-collapse'), false, 'Mini search expands sidebar'); check(focused, input, 'Browser adapter focuses search after expansion');
console.log('Sidebar search client PASS: ' + checks + ' checks');
