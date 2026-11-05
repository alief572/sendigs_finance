'use strict';
const assert = require('node:assert/strict');
const feedback = require('../../../../assets/js/invoicing/save-feedback.js');
let checks = 0;
function check(actual, expected, message) { checks++; assert.deepEqual(actual, expected, message); }
function fixture() {
    const attrs = {}, calls = [], callbacks = {}, redirects = [];
    const button = {disabled: false, textContent: 'Simpan invoice'};
    const message = {setAttribute(key, value) { attrs[key] = value; }};
    const form = {reportValidity() { return true; }, setAttribute(key, value) { attrs[key] = value; }};
    const request = {};
    ['done', 'fail', 'always'].forEach(key => { request[key] = cb => { callbacks[key] = cb; return request; }; });
    let confirm, loading = 0, requests = 0;
    const swal = {fire(config) {
        calls.push(config);
        if (config.didOpen) config.didOpen();
        return {then(callback) { confirm = callback; }};
    }, showLoading() { loading++; }};
    const options = {form, button, message, swal,
        request() { requests++; return request; },
        successText: 'Invoice berhasil disimpan.', unconfirmedText: 'Penyimpanan tidak dapat dikonfirmasi.',
        onSuccess() { redirects.push('/invoicing'); }};
    return {options, attrs, calls, button, message, form, redirects,
        resolve(result) { callbacks.done(result); callbacks.always(); },
        reject() { callbacks.fail(); callbacks.always(); },
        confirm() { confirm(); }, loading: () => loading, requests: () => requests};
}
const success = fixture(); feedback.submit(success.options);
check(success.loading(), 1, 'Spinner opened');
check(success.calls[0].showConfirmButton, false, 'No confirmation while writing');
check(success.calls[0].allowEscapeKey, false, 'Loading cannot be escaped');
check(success.calls[0].allowOutsideClick, false, 'Loading cannot be dismissed outside');
check(success.button.disabled, true, 'Button locked');
check(success.attrs['aria-busy'], 'true', 'Busy announced');
feedback.submit(success.options);
check(success.requests(), 1, 'Duplicate request blocked');
success.resolve({status: 1, msg: 'Invoice diperbarui <img src=x onerror=alert(1)>'});
check(success.calls[1].icon, 'success', 'Success only after server acknowledgment');
check(success.calls[1].text, 'Invoice diperbarui <img src=x onerror=alert(1)>', 'Server message literal text');
check(success.calls[1].html, undefined, 'No HTML injection');
check(success.redirects.length, 0, 'No redirect before OK');
check(success.button.disabled, true, 'Lock retained after successful write');
feedback.submit(success.options);
check(success.requests(), 1, 'No resubmit while awaiting success confirmation');
success.confirm();
check(success.redirects, ['/invoicing'], 'Redirect after OK');
for (const result of [null, {status: 0, msg: 'Form kedaluwarsa'}, {status: '1'}, {status: 0, msg: {invalid: true}}]) {
    const failed = fixture(); feedback.submit(failed.options); failed.resolve(result);
    check(failed.calls[1].icon, 'error', 'Invalid or rejected response fails');
    check(failed.button.disabled, false, 'Failure unlocks retry');
    check(failed.attrs['aria-busy'], 'false', 'Failure clears busy');
    check(failed.redirects.length, 0, 'Failure never redirects');
    check(typeof failed.calls[1].text, 'string', 'Error text normalized');
    if (result && result.msg === 'Form kedaluwarsa') check(failed.message.textContent, result.msg, 'Validation preserved');
}
const network = fixture(); feedback.submit(network.options); network.reject();
check(network.calls[1].text, network.options.unconfirmedText, 'Network failure avoids false saved claim');
check(network.button.textContent, 'Simpan invoice', 'Original label restored');
const invalid = fixture(); invalid.form.reportValidity = () => false; feedback.submit(invalid.options);
check(invalid.requests(), 0, 'Invalid form never submits');
check(invalid.calls.length, 0, 'Invalid form leaves native validation available');
const fallback = fixture(); fallback.options.swal = {}; feedback.submit(fallback.options); fallback.resolve({status: 1});
check(fallback.redirects.length, 1, 'Missing modal uses fallback navigation');
check(fallback.message.textContent, fallback.options.successText, 'Inline success retained');
const broken = fixture(); broken.options.swal.fire = () => { throw new Error('modal unavailable'); };
feedback.submit(broken.options); broken.reject();
check(broken.button.disabled, false, 'Broken modal still restores button');
check(broken.message.textContent, broken.options.unconfirmedText, 'Broken modal inline fallback');
const thrown = fixture(); thrown.options.request = () => { throw new Error('request unavailable'); };
feedback.submit(thrown.options);
check(thrown.calls[1].icon, 'error', 'Synchronous request exception handled');
check(thrown.button.disabled, false, 'Request exception unlocks');
const mapping = fixture(); mapping.button.textContent = 'Simpan mapping';
mapping.options.successText = 'Mapping COA berhasil disimpan.';
feedback.submit(mapping.options); mapping.resolve({status: 1});
check(mapping.calls[1].text, 'Mapping COA berhasil disimpan.', 'Mapping uses its success copy');
check(mapping.redirects.length, 0, 'Mapping waits before reload callback'); mapping.confirm();
check(mapping.redirects.length, 1, 'Mapping reload callback after confirmation');
console.log('Save feedback PASS: ' + checks + ' checks');
