(function (root) {
    'use strict';
    function cents(value) {
        var text = String(value == null ? '' : value).trim();
        if (text === '') return 0;
        if (!/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d{1,2})?$/.test(text)) throw new Error('Format nominal tidak valid.');
        var stripped = text.replace(/,/g, '');
        var negative = stripped.charAt(0) === '-';
        var parts = stripped.replace('-', '').split('.');
        if (parts[0].length > 13) throw new Error('Nominal terlalu besar.');
        var amount = Number(parts[0]) * 100 + Number(((parts[1] || '') + '00').slice(0, 2));
        return negative ? -amount : amount;
    }
    function decimal(amount) {
        if (!Number.isSafeInteger(amount)) throw new Error('Nominal terlalu besar atau tidak valid.');
        var absolute = Math.abs(amount);
        return (amount < 0 ? '-' : '') + Math.floor(absolute / 100) + '.' + String(absolute % 100).padStart(2, '0');
    }
    function normalizeFields(fields) {
        return fields.map(function (field) {
            return {name: field.name, value: /^(?:(?:penerimaan|pph_actual|biaya_admin|sisa_piutang|dep|lebih_bayar)_\d+|deposit_amount|nominal_deposit_dipakai)$/.test(field.name) ? decimal(cents(field.value)) : field.value};
        });
    }
    var api = {cents: cents, decimal: decimal, normalizeFields: normalizeFields};
    if (typeof module !== 'undefined' && module.exports) module.exports = api;
    else root.ConsultingReceiptMoney = api;
}(typeof window !== 'undefined' ? window : this));
