(function (root, factory) {
    'use strict';
    if (typeof module === 'object' && module.exports) module.exports = factory(root);
    else root.InvoiceSaveFeedback = factory(root);
}(typeof window === 'object' ? window : this, function (root) {
    'use strict';
    function submit(options) {
        var form = options.form, button = options.button, message = options.message;
        if (button.disabled || !form.reportValidity()) return;
        var swal = options.swal || root.Swal;
        var saved = false, waiting = false, label = button.textContent;
        function feedback(text, state) {
            message.textContent = text;
            message.setAttribute('data-state', state);
        }
        function release() {
            button.disabled = false; button.textContent = label;
            form.setAttribute('aria-busy', 'false');
        }
        function alert(config) {
            try { return swal && typeof swal.fire === 'function' ? swal.fire(config) : null; }
            catch (error) { return null; }
        }
        function failure(text) {
            feedback(text, 'error');
            alert({icon: 'error', title: 'Belum berhasil disimpan', text: text, confirmButtonText: 'OK'});
        }
        button.disabled = true; button.textContent = 'Menyimpan…';
        form.setAttribute('aria-busy', 'true'); feedback('Menyimpan…', 'loading');
        alert({title: 'Menyimpan…', text: 'Mohon tunggu sampai proses selesai.',
            allowOutsideClick: false, allowEscapeKey: false, showConfirmButton: false,
            didOpen: function () { if (typeof swal.showLoading === 'function') swal.showLoading(); }});
        var request;
        try {
            request = options.request();
        } catch (error) {
            failure(options.unconfirmedText); release(); return;
        }
        request.done(function (result) {
            if (!result || result.status !== 1) {
                failure(result && typeof result.msg === 'string' && result.msg ? result.msg : 'Data belum tersimpan. Periksa isian dan coba kembali.');
                return;
            }
            saved = true;
            var text = typeof result.msg === 'string' && result.msg ? result.msg : options.successText;
            feedback(text, 'success');
            var modal = alert({icon: 'success', title: 'Berhasil disimpan', text: text,
                allowOutsideClick: false, allowEscapeKey: false, confirmButtonText: 'OK'});
            if (modal && typeof modal.then === 'function') {
                waiting = true;
                modal.then(options.onSuccess, options.onSuccess);
            } else options.onSuccess();
        }).fail(function () {
            failure(options.unconfirmedText);
        }).always(function () {
            if (!saved || !waiting) release();
        });
    }
    return {submit: submit};
}));
