/**
 * Filter Company multi-pilih (Select2 multiple). Tidak ada yang dipilih = Semua Company.
 * Nilai dikirim ke server sebagai string dipisah koma ("STM,VUCA"); string kosong = semua.
 * Select2 dimuat global oleh tema admin (assets/plugins/select2).
 */
(function (root) {
    'use strict';

    function toList(value) {
        if (value === null || value === undefined) return [];
        var items = Array.isArray(value) ? value : String(value).split(',');
        var out = [];
        items.forEach(function (item) {
            item = String(item).trim();
            if (item !== '' && out.indexOf(item) === -1) out.push(item);
        });
        return out;
    }

    var CompanyMulti = {
        toList: toList,

        /** Nilai terpilih sebagai string koma ('' bila kosong). */
        val: function (selector) {
            var el = root.jQuery ? root.jQuery(selector) : null;
            return el && el.length ? toList(el.val()).join(',') : '';
        },

        /** Isi pilihan dari string koma / array; tanpa argumen = kosongkan. */
        set: function (selector, value) {
            var el = root.jQuery(selector);
            el.val(toList(value)).trigger('change');
            return el;
        },

        /** Ubah <select> jadi multiple + Select2; aman dipanggil ulang. */
        init: function (selector, options) {
            var $ = root.jQuery;
            var el = $(selector);
            if (!el.length) return el;
            el.prop('multiple', true);
            el.find('option[value=""]').remove();
            if (typeof el.select2 === 'function') {
                if (el.hasClass('select2-hidden-accessible')) el.select2('destroy');
                el.select2($.extend({
                    placeholder: 'Semua Company',
                    allowClear: true,
                    width: '100%',
                    closeOnSelect: false
                }, options || {}));
            }
            return el;
        }
    };

    root.CompanyMulti = CompanyMulti;
    if (typeof module !== 'undefined' && module.exports) module.exports = CompanyMulti;
})(typeof window !== 'undefined' ? window : globalThis);
