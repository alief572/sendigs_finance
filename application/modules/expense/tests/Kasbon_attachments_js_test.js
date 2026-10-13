const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

class Element {
    constructor(tag) { this.tag = tag; this.children = []; this.attributes = {}; this.content = ''; }
    empty() { this.children = []; return this; }
    append(child) { this.children.push(child); return this; }
    attr(values) { this.attributes = values; return this; }
    text(value) { this.content = value; return this; }
    addClass(value) { this.className = value; return this; }
}
const list = new Element('list');
const context = { $: (value) => value === '.pr-attachment-list' ? list : new Element(value) };
vm.createContext(context);
vm.runInContext(fs.readFileSync(path.resolve(__dirname, '../../../../assets/js/kasbon-attachments.js'), 'utf8'), context);
context.kasbonPrAttachments({ attachments: [
    { name: '<script>alert(1)</script>.pdf', url: 'https://test.local/file.pdf' },
    { name: 'missing.png', url: null }
], message: 'Lampiran PR perlu diperiksa' });
assert.equal(list.children.length, 3);
assert.equal(list.children[0].children[0].content, '<script>alert(1)</script>.pdf');
assert.equal(list.children[0].children[0].attributes.rel, 'noopener');
assert.equal(list.children[1].content, 'Lampiran tidak tersedia: missing.png');
assert.equal(list.children[2].content, 'Lampiran PR perlu diperiksa');
context.kasbonPrAttachments({ attachments: [] });
assert.equal(list.children.length, 0);
console.log('Kasbon PR attachment JS rendering tests passed');
