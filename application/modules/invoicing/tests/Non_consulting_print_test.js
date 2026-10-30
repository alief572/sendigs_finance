'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../views/print_invoice_non_konsultasi.php'), 'utf8');
const script = source.match(/<script>([\s\S]*?)<\/script>/)[1];

function render(heights, readyState = 'loading') {
    const events = {};
    let prints = 0;
    const rows = heights.map(height => ({
        height,
        cells: Array.from({ length: 5 }, () => ({ style: {} })),
        getBoundingClientRect() { return { height: this.height }; }
    }));
    vm.runInNewContext(script, {
        document: {
            readyState,
            querySelectorAll: () => rows
        },
        window: {
            addEventListener: (name, handler) => { events[name] = handler; },
            print: () => { prints++; }
        }
    });
    if (readyState === 'loading') {
        assert.equal(prints, 0, 'Wait for assets before measuring and printing');
        events.load();
    }
    assert.equal(prints, 1, 'Open print once');
    return { rows, events };
}

assert.doesNotThrow(() => render([]));
let result = render([27]);
assert.ok(result.rows[0].cells.every(cell => cell.style.height === '200px'));
result = render([27, 27, 27, 27]);
assert.ok(result.rows.slice(0, -1).every(row => row.cells.every(cell => cell.style.height === '')));
assert.ok(result.rows[3].cells.every(cell => cell.style.height === '119px'));
result.events.beforeprint();
assert.equal(result.rows[3].cells[0].style.height, '119px', 'Repeated print does not accumulate height');
result.rows[0].height = 54;
result.events.beforeprint();
assert.equal(result.rows[3].cells[0].style.height, '92px', 'Recalculate after text wraps at print width');
result = render([110, 115]);
assert.equal(result.rows[1].cells[0].style.height, '115px', 'Long content is not shrunk');
result = render([240], 'complete');
assert.equal(result.rows[0].cells[0].style.height, '240px');
console.log('PASS: empty, single, multiple, long, repeated print, changed wrapping, and loaded document');
