(function (root) {
    'use strict';
    function cents(value) {
        value = String(value == null ? '' : value).trim();
        if (!value) return 0;
        if (!/^-?(?:\d+|\d{1,3}(?:\.\d{3})+)(?:,\d{1,2})?$/.test(value)) throw new Error('Format nominal tidak valid.');
        var negative = value.charAt(0) === '-';
        var parts = value.replace(/\./g, '').replace('-', '').split(',');
        if (parts[0].length > 13) throw new Error('Nominal terlalu besar.');
        var amount = Number(parts[0]) * 100 + Number(((parts[1] || '') + '00').slice(0, 2));
        return negative ? -amount : amount;
    }
    function format(amount) {
        var absolute = Math.abs(amount);
        return (amount < 0 ? '-' : '') + Math.floor(absolute / 100).toLocaleString('id-ID') + ',' + String(absolute % 100).padStart(2, '0');
    }
    function calculate(balance, pph, choice, receipt, admin) {
        if (['Y', 'N'].indexOf(choice) < 0 || Math.min(balance, pph, receipt, admin) < 0) throw new Error('Pilihan PPh atau nominal tidak valid.');
        var adjustment = choice === 'N' ? Math.min(pph, receipt) : 0;
        var reduction = receipt + admin - adjustment;
        if (reduction > balance) throw new Error('Penerimaan dan biaya admin melebihi saldo piutang.');
        return {adjustment: adjustment, reduction: reduction, remaining: balance - reduction};
    }
    var api = {cents: cents, format: format, calculate: calculate};
    if (typeof module !== 'undefined' && module.exports) module.exports = api;
    else root.ReceiptCalculation = api;
}(typeof window !== 'undefined' ? window : this));
