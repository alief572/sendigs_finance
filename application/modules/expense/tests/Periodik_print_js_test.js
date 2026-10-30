const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const root = path.resolve(__dirname, '../../../..');
const watchdog = setTimeout(() => {
    console.error('Periodik print tests timed out waiting for readiness');
    process.exit(1);
}, 5000);

class Element {
    constructor(tag) {
        this.tagName = tag;
        this.children = [];
        this.style = {};
        this.listeners = {};
        this.attributes = {};
        this.disabled = true;
    }
    appendChild(child) { this.children.push(child); return child; }
    setAttribute(key, value) { this.attributes[key] = value; }
    getAttribute(key) { return this.attributes[key]; }
    addEventListener(name, callback) { this.listeners[name] = callback; }
    remove() { this.removed = true; }
    getContext() { return {}; }
    querySelector() { return this.notice; }
    querySelectorAll() { return this.children.filter(child => child.className === 'periodik-pdf-page'); }
}

async function run({ documents = [], images = [], missingLibrary = false } = {}) {
    const events = [];
    const status = new Element('p');
    const button = new Element('button');
    const wrappers = documents.map((pages, index) => {
        const element = new Element('div');
        element.attributes['data-pdf-url'] = 'file-' + index + '.pdf';
        element.notice = new Element('p');
        return element;
    });
    const document = {
        readyState: 'complete',
        getElementById: id => id === 'periodik-print-button' ? button : status,
        querySelectorAll: selector => selector === '.periodik-pdf-attachment' ? wrappers : images,
        createElement: tag => new Element(tag),
        currentScript: { getAttribute: () => '/assets/js/pdfjs/pdf.worker.min.js' }
    };
    let printed = 0;
    let resolvePrint;
    const printedPromise = new Promise(resolve => { resolvePrint = resolve; });
    const context = {
        document,
        window: { print: () => { printed++; events.push('print'); resolvePrint(); } },
        requestAnimationFrame: callback => setImmediate(callback),
        pdfjsLib: missingLibrary ? undefined : {
            GlobalWorkerOptions: {},
            getDocument: url => ({ promise: (async () => {
                const index = Number(url.match(/file-(\d+)/)[1]);
                if (documents[index] instanceof Error) throw documents[index];
                return {
                    numPages: documents[index].length,
                    getPage: async pageNumber => {
                        const [width, height] = documents[index][pageNumber - 1];
                        return {
                            getViewport: ({ scale }) => ({ width: width * scale, height: height * scale }),
                            render: () => ({ promise: new Promise((resolve, reject) => setImmediate(() => {
                                events.push(index + ':' + pageNumber);
                                if (width === -1) reject(new Error('render failed')); else resolve();
                            })) }),
                            cleanup() {}
                        };
                    },
                    destroy: async () => { events.push('destroy:' + index); }
                };
            })() })
        }
    };
    vm.createContext(context);
    vm.runInContext(fs.readFileSync(path.join(root, 'assets/js/periodik-print.js'), 'utf8'), context);
    return { wrappers, status, button, events, context, printedPromise, printed: () => printed };
}

async function settle() {
    for (let i = 0; i < 30; i++) await new Promise(resolve => setImmediate(resolve));
}

(async () => {
    const view = fs.readFileSync(path.join(root, 'application/modules/expense/views/periodik_print.php'), 'utf8');
    assert.doesNotMatch(view, /<iframe/);
    assert.match(view, /size:\s*A4 portrait/);
    assert.match(view, /margin:\s*10mm/);
    assert.match(view, /break-before:\s*page/);
    assert.match(view, /break-inside:\s*avoid/);
    assert.match(view, /htmlspecialchars\(base_url\('assets\/bayar_rutin\//);
    assert.match(view, /assets\/js\/pdfjs\/pdf\.min\.js/);
    assert.match(view, /assets\/js\/periodik-print\.js/);

    const mixed = await run({ documents: [[[595, 842], [842, 595]], [[400, 1000]]] });
    assert.equal(mixed.printed(), 0, 'must wait for render');
    await mixed.printedPromise;
    assert.deepEqual(mixed.events.filter(event => /^\d/.test(event)), ['0:1', '0:2', '1:1']);
    assert.equal(mixed.wrappers[0].children.length, 2);
    for (const wrapper of mixed.wrappers) {
        for (const page of wrapper.children) {
            const canvas = page.children[0];
            const width = parseFloat(canvas.style.width);
            const height = parseFloat(canvas.style.height);
            assert.ok(width <= 190 && height <= 276);
            assert.ok(Math.abs(width / height - canvas.width / canvas.height) < 0.002);
        }
    }
    assert.equal(mixed.button.disabled, false);
    mixed.button.listeners.click();
    assert.equal(mixed.printed(), 2, 'manual reprint');

    const image = new Element('img');
    image.complete = false;
    const waiting = await run({ images: [image] });
    await settle();
    assert.equal(waiting.printed(), 0, 'must wait for images');
    image.naturalWidth = 600;
    image.listeners.load();
    await waiting.printedPromise;

    const brokenImage = new Element('img');
    const broken = await run({ images: [brokenImage] });
    brokenImage.listeners.error(new Error('404'));
    await settle();
    assert.equal(broken.printed(), 0);
    assert.match(broken.status.textContent, /gagal/i);

    const complete = await run({ images: [Object.assign(new Element('img'), { complete: true, naturalWidth: 800 })] });
    await complete.printedPromise;

    const empty = await run();
    await empty.printedPromise;
    assert.equal(empty.printed(), 1);

    for (const options of [
        { documents: [new Error('corrupt or password protected')] },
        { documents: [[[595, 842]]], missingLibrary: true },
        { documents: [[[595, 842], [-1, 842]]] },
        { images: [Object.assign(new Element('img'), { complete: true, naturalWidth: 0 })] }
    ]) {
        const failed = await run(options);
        await settle();
        assert.equal(failed.printed(), 0, 'failure must suppress automatic print');
        assert.equal(failed.button.disabled, true);
        assert.match(failed.status.textContent, /gagal/i);
        if (failed.wrappers.length) {
            assert.ok(failed.wrappers[0].notice.children.some(child => child.tagName === 'a'));
            assert.ok(failed.wrappers[0].children.every(child => child.removed), 'remove partial PDF pages');
        }
    }
    console.log('Periodik print tests passed: pagination, fit, ordering, readiness, reprint and failure handling');
})().catch(error => { console.error(error); process.exitCode = 1; }).finally(() => clearTimeout(watchdog));
