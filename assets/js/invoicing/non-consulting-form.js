(function (root, factory) {
    'use strict';
    if (typeof module === 'object' && module.exports) module.exports = factory(require('./consulting.js'));
    else root.NonConsultingInvoiceForm = factory(root.ConsultingInvoice);
}(typeof window === 'object' ? window : this, function (calculator) {
    'use strict';
    function calculate(items, shipping) {
        if (!items.length) throw new Error('Item invoice wajib diisi.');
        var total = calculator.cents(shipping), lines = [];
        items.forEach(function (item) {
            var raw = String(item.qty).trim();
            if (!/^\d{1,6}(?:\.\d{1,2})?$/.test(raw)) throw new Error('Qty tidak valid.');
            var parts = raw.split('.'), scale = Math.pow(10, (parts[1] || '').length);
            var qty = Number(parts[0]) * scale + Number(parts[1] || 0), price = calculator.cents(item.harga);
            if (!qty || !Number.isSafeInteger(qty * price) || qty * price > 9007199254740991) throw new Error('Qty atau total item tidak valid.');
            var line = Math.floor((qty * price + Math.floor(scale / 2)) / scale);
            total += line; lines.push(line);
        });
        if (!Number.isSafeInteger(total) || total <= 0 || total > 999999999999999) throw new Error('Total penjualan tidak valid.');
        return {amount: (total / 100).toFixed(2), lines: lines};
    }
    function plain(value) { return String(value).replace(/,/g, ''); }
    function group(value) {
        var clean = plain(value).replace(/[^\d.]/g, ''), dot = clean.indexOf('.');
        var whole = dot < 0 ? clean : clean.slice(0, dot), dec = dot < 0 ? '' : clean.slice(dot + 1).replace(/\./g, '').slice(0, 2);
        whole = whole.replace(/^0+(?=\d)/, '').replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return dot < 0 ? whole : (whole || '0') + '.' + dec;
    }
    function bind(form) {
        var body = form.querySelector('[data-invoice-items]'), next = body.children.length, changed = function () {};
        function amount() {
            var rows = Array.from(body.querySelectorAll('[data-item]'));
            var result = calculate(rows.map(function (row) {
                return {qty: row.querySelector('[name$="[qty]"]').value, harga: plain(row.querySelector('[name$="[harga]"]').value)};
            }), form.querySelector('[name="biaya_kirim"]').value);
            rows.forEach(function (row, index) { row.querySelector('[data-item-total]').textContent = (result.lines[index] / 100).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}); });
            return result.amount;
        }
        form.addEventListener('input', function (event) {
            var el = event.target;
            if (body.contains(el) && el.hasAttribute && el.hasAttribute('data-money')) {
                var digits = el.value.slice(0, el.selectionStart || 0).replace(/[^\d.]/g, '').length, text = group(el.value), pos = 0;
                el.value = text;
                for (var seen = 0; pos < text.length && seen < digits; pos++) if (/[\d.]/.test(text.charAt(pos))) seen++;
                if (el.setSelectionRange) el.setSelectionRange(pos, pos);
            }
            if (body.contains(el)) changed();
        });
        form.querySelector('[data-add-item]').addEventListener('click', function () {
            var row = form.ownerDocument.createElement('tr'); row.setAttribute('data-item', '');
            row.innerHTML = '<td><textarea class="form-control" name="item[' + next + '][nama]" maxlength="1000" required aria-label="Deskripsi item"></textarea></td>' +
                '<td><input class="form-control" name="item[' + next + '][qty]" type="number" min="1" max="999999" step="1" value="1" required aria-label="Qty item"></td>' +
                '<td><input class="form-control text-right" name="item[' + next + '][harga]" type="text" inputmode="decimal" data-money autocomplete="off" value="0" required aria-label="Harga item"></td>' +
                '<td class="text-right" data-item-total>0.00</td><td><button type="button" class="btn btn-default" data-remove-item aria-label="Hapus item">Hapus</button></td>';
            next++; body.appendChild(row); row.querySelector('textarea').focus(); changed();
        });
        body.addEventListener('click', function (event) {
            var button = event.target.closest('[data-remove-item]');
            if (button && body.contains(button)) { button.closest('[data-item]').remove(); form.querySelector('[data-add-item]').focus(); changed(); }
        });
        return {amount: amount, onChange: function (callback) { changed = callback; }};
    }
    return {calculate: calculate, bind: bind};
}));
