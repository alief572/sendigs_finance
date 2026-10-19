(function (root) {
    'use strict';
    const C = {
        cents(value, grouped = false) {
            let s = String(value).trim();
            if (grouped && /^\d{1,3}(?:,\d{3})+(?:\.\d{1,2})?$/.test(s)) s = s.replace(/,/g, '');
            if (!/^\d+(?:\.\d{1,2})?$/.test(s)) throw Error('Nominal tidak valid');
            const p = s.split('.');
            const result = Number(BigInt(p[0]) * 100n + BigInt((p[1] || '').padEnd(2, '0')));
            if (!Number.isSafeInteger(result) || result > 999999999999999) throw Error('Nominal terlalu besar');
            return result;
        },
        ratio(value, numerator, denominator) {
            return Number((BigInt(value) * BigInt(numerator) + BigInt(Math.floor(denominator / 2))) / BigInt(denominator));
        },
        totals(dpp, rate, vuca = false) {
            if (!Number.isSafeInteger(dpp) || dpp <= 0 || dpp > 999999999999999 || ![0, 12].includes(Number(rate)) || (vuca && Number(rate) !== 0)) throw Error('DPP atau PPN tidak valid');
            const other = C.ratio(dpp, 11, 12), ppn = C.ratio(other, Number(rate), 100), pph = C.ratio(dpp, vuca ? 5 : 20, 1000);
            return { dpp, other, ppn, pph, gross: dpp + ppn, net: dpp + ppn - pph };
        },
        item(quantity, price) {
            const q = String(quantity).trim();
            if (!/^\d+(?:\.\d{1,2})?$/.test(q)) throw Error('Quantity tidak valid');
            const p = q.split('.'), scale = 10n ** BigInt((p[1] || '').length), units = BigInt(p[0]) * scale + BigInt(p[1] || '0');
            if (units <= 0n || p[0].length > 6) throw Error('Quantity harus positif');
            const amount = BigInt(C.cents(price, true)) * units;
            if (amount > 9007199254740991n) throw Error('Total item terlalu besar');
            return Number((amount + scale / 2n) / scale);
        }
    };
    if (typeof module !== 'undefined') module.exports = C;
    root.InvoiceMath = C;
}(typeof globalThis !== 'undefined' ? globalThis : window));
