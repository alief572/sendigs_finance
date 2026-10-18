(function (root, factory) {
    'use strict';
    if (typeof module === 'object' && module.exports) module.exports = factory(require('./consulting.js'), root);
    else root.ConsultingInvoiceForm = factory(root.ConsultingInvoice, root);
}(typeof window === 'object' ? window : this, function (calculator, root) {
    'use strict';
    function money(cents) {
        return (cents / 100).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    function mount(form, data, options) {
        options = options || {};
        var document = form.ownerDocument;
        var tax = form.querySelector('#consulting-tax'), sales = form.querySelector('#consulting-sales');
        var date = form.querySelector('#consulting-date'), body = form.querySelector('#consulting-journals');
        var message = form.querySelector('#consulting-message'), button = form.querySelector('#consulting-save');
        var pending = false;
        function feedback(text, state) {
            message.textContent = text;
            message.setAttribute('data-state', state || 'info');
        }
        function emptyJournal(text) {
            var row = document.createElement('tr'), cell = document.createElement('td');
            cell.colSpan = 7; cell.className = 'invoice-empty'; cell.textContent = text;
            row.appendChild(cell); body.appendChild(row);
        }
        function render() {
            body.textContent = '';
            if (!pending) feedback('');
            form.querySelector('#consulting-debit').textContent = money(0);
            form.querySelector('#consulting-credit').textContent = money(0);
            ['sales', 'other', 'ppn', 'gross', 'receivable'].forEach(function (key) {
                form.querySelector('[data-total="' + key + '"]').textContent = '-';
            });
            try {
                form.querySelector('[data-total="sales"]').textContent = money(calculator.cents(data.amount));
                if (!tax.value) { emptyJournal('Pilih perlakuan PPN dan COA penjualan untuk menampilkan jurnal.'); return; }
                var totals = calculator.totals(data.amount, tax.value);
                ['sales', 'other', 'ppn', 'gross', 'receivable'].forEach(function (key) {
                    form.querySelector('[data-total="' + key + '"]').textContent = money(totals[key]);
                });
                if (!sales.value) { emptyJournal('Pilih COA penjualan untuk melengkapi jurnal invoice.'); return; }
                var lines = calculator.journal(totals, data.accounts, sales.value, data.number, data.spk), debit = 0, credit = 0;
                lines.forEach(function (line) {
                    var row = document.createElement('tr');
                    [date.value, data.company, line.coa, line.name, line.description, money(line.debit), money(line.credit)].forEach(function (value, index) {
                        var cell = document.createElement('td'); cell.textContent = value;
                        if (index > 4) cell.className = 'text-right';
                        row.appendChild(cell);
                    });
                    body.appendChild(row); debit += line.debit; credit += line.credit;
                });
                form.querySelector('#consulting-debit').textContent = money(debit);
                form.querySelector('#consulting-credit').textContent = money(credit);
            } catch (error) {
                body.textContent = ''; emptyJournal('Jurnal belum dapat ditampilkan. Periksa pilihan PPN dan akun penjualan.');
                feedback(error.message, 'error');
            }
        }
        [tax, sales, date].forEach(function (input) { input.addEventListener('change', render); });
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            if (pending || !form.reportValidity()) return;
            pending = true; button.disabled = true; button.textContent = 'Menyimpan…';
            form.setAttribute('aria-busy', 'true'); feedback('Menyimpan invoice…', 'loading');
            var request = options.request || function () {
                return root.jQuery.ajax({url: form.action, type: 'POST', data: root.jQuery(form).serialize(), dataType: 'json'});
            };
            request(form).done(function (result) {
                if (result && result.status === 1) {
                    feedback('Invoice berhasil disimpan. Membuka daftar invoice…', 'success');
                    if (options.redirect) options.redirect(data.redirect);
                    else root.location.href = data.redirect;
                } else feedback(result && result.msg ? result.msg : 'Invoice belum tersimpan. Periksa isian dan coba kembali.', 'error');
            }).fail(function () {
                feedback('Penyimpanan tidak dapat dikonfirmasi. Muat ulang daftar sebelum mencoba kembali.', 'error');
            }).always(function () {
                pending = false; button.disabled = false; button.textContent = 'Simpan invoice';
                form.setAttribute('aria-busy', 'false');
            });
        });
        render();
    }
    return {mount: mount};
}));
