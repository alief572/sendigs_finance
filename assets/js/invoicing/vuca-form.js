(function (root, factory) {
    'use strict';
    if (typeof module === 'object' && module.exports) module.exports = factory;
    else root.VucaInvoiceForm = factory(root);
}(typeof window === 'object' ? window : this, function (root) {
    'use strict';
    function mount(form, config) {
        var sales = form.querySelector('[name="sales_role"]');
        var journals = form.querySelector('#vuca-journals');
        var empty = form.querySelector('#vuca-journal-empty');
        var button = form.querySelector('#vuca-save');
        var message = form.querySelector('#vuca-message');
        function refresh() {
            var account = Object.prototype.hasOwnProperty.call(config.accounts, sales.value) ? config.accounts[sales.value] : null;
            journals.hidden = !account;
            empty.hidden = !!account;
            form.querySelector('[data-vuca-sales-code]').textContent = account ? account.code : '';
            form.querySelector('[data-vuca-sales-name]').textContent = account ? account.name : '';
            form.querySelector('[data-vuca-code-input]').value = account ? account.code : '';
            form.querySelector('[data-vuca-name-input]').value = account ? account.name : '';
            form.querySelector('#vuca-debit').textContent = account ? config.total : '0.00';
            form.querySelector('#vuca-credit').textContent = account ? config.total : '0.00';
        }
        sales.addEventListener('change', refresh);
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (form.getAttribute('aria-busy') === 'true') return;
            if (!Object.prototype.hasOwnProperty.call(config.accounts, sales.value)) {
                message.textContent = 'Pilih COA penjualan sebelum menyimpan invoice.';
                sales.focus();
                return;
            }
            root.InvoiceSaveFeedback.submit({
                form: form, button: button, message: message,
                request: function () { return root.jQuery.ajax({type: 'POST', url: form.action, data: root.jQuery(form).serialize(), dataType: 'json'}); },
                successText: 'Invoice Vuca berhasil disimpan.',
                unconfirmedText: 'Penyimpanan belum dapat dikonfirmasi. Periksa daftar invoice sebelum mencoba kembali.',
                onSuccess: function () { root.location.href = config.redirect; }
            });
        });
        refresh();
        button.disabled = false;
    }
    return {mount: mount};
}));
