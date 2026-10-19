(function (root, factory) {
    'use strict';
    var api = factory();
    if (typeof module === 'object' && module.exports) module.exports = api;
    else root.ConsultingInvoice = api;
}(typeof window === 'object' ? window : this, function () {
    'use strict';
    function cents(value) {
        if (!/^\d+(?:\.\d{1,2})?$/.test(String(value))) throw new Error('Nominal tidak valid.');
        var p = String(value).split('.');
        var n = Number(p[0]) * 100 + Number(((p[1] || '') + '00').slice(0, 2));
        if (!Number.isSafeInteger(n) || n > 999999999999999) throw new Error('Nominal terlalu besar.');
        return n;
    }
    function ratio(n, a, b) { return Math.floor(n / b) * a + Math.floor(((n % b) * a + Math.floor(b / 2)) / b); }
    function totals(amount, type) {
        if (['PKP', 'WAPU', 'KB'].indexOf(type) < 0) throw new Error('Pilih perlakuan PPN.');
        var sales = cents(amount);
        if (!sales) throw new Error('Nominal TOP harus positif.');
        var other = ratio(sales, 11, 12), ppn = ratio(other, 12, 100);
        return {sales: sales, other: other, ppn: ppn, gross: sales + ppn, receivable: type === 'PKP' ? sales + ppn : sales, type: type};
    }
    function journal(t, accounts, role, number, spk) {
        var lines = [];
        if (t.type === 'WAPU') lines.push(['vat_wapu_prepaid', t.ppn, 0]);
        lines.push(['receivable', t.receivable, 0]);
        if (t.type === 'PKP') lines.push(['vat_domestic', 0, t.ppn]);
        if (t.type === 'WAPU') lines.push(['vat_wapu_payable', 0, t.ppn]);
        lines.push([role, 0, t.sales]);
        return lines.map(function (line) {
            var a = accounts[line[0]];
            if (!a) throw new Error('Pilih COA penjualan.');
            return {coa: a.code, name: a.name, debit: line[1], credit: line[2], description: a.name + ' - ' + number + ' - ' + spk + (t.type === 'KB' ? ' - Kawasan Berikat' : '')};
        });
    }
    function receipt(balance, cash, admin, pph) {
        if (typeof balance !== 'number' || typeof cash !== 'number' || typeof admin !== 'number' || typeof pph !== 'number' || !Number.isFinite(balance) || !Number.isFinite(cash) || !Number.isFinite(admin) || !Number.isFinite(pph)) {
            throw new Error('Penerimaan, admin, dan PPh harus angka valid.');
        }
        var reduction = cash + admin + pph;
        if (Math.min(balance, cash, admin, pph) < 0 || reduction <= 0 || reduction > balance) throw new Error('Penerimaan melebihi saldo piutang atau tidak valid.');
        return {reduction: reduction, remaining: balance - reduction, adjustment: 0};
    }
    return {cents: cents, totals: totals, journal: journal, receipt: receipt};
}));
