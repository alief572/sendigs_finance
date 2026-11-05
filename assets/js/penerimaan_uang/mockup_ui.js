/* Helper tampilan bersama (meniru fungsi mockup): toast, confirmBox, fdate/en/idn, pencarian hayOf/matchQ, pager.
   Dipakai penerimaan_uang, kwitansi_deposit, deposit_customer. Tanpa dependensi. */
(function (root) {
    'use strict';
    var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var BULAN_ID = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]; }); }
    function en(n, d) { d = d == null ? 0 : d; return Number(n || 0).toLocaleString('en-US', {minimumFractionDigits: d, maximumFractionDigits: d}); }
    function idn(n) { return Number(n || 0).toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
    function fdate(iso) {
        var m = /^(\d{4})-(\d{2})-(\d{2})/.exec(String(iso || ''));
        return m ? m[3] + '-' + MONTHS[+m[2] - 1] + '-' + m[1] : '';
    }

    function toast(msg) {
        var old = document.querySelectorAll('.mk-toast');
        for (var i = 0; i < old.length; i++) old[i].remove();
        var t = document.createElement('div');
        t.className = 'mk-toast';
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(function () { t.remove(); }, 2600);
    }

    function openModal(html, size) {
        var wrap = document.createElement('div');
        wrap.className = 'mk-modal-bg' + (size === 'xs' ? ' mk-center' : '');
        wrap.innerHTML = '<div class="mk-modal ' + (size || '') + '" role="dialog" aria-modal="true">' + html + '</div>';
        document.body.appendChild(wrap);
        wrap.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModal(wrap); });
        var f = wrap.querySelector('input,select,button');
        if (f) f.focus();
        return wrap;
    }
    function closeModal(w) { if (w && w.parentNode) w.parentNode.removeChild(w); }

    // confirmBox mockup (M:350): modal 380px, "!" oranye, Batal + Ya, Simpan.
    function confirmBox(title, msg, onYes) {
        var w = openModal('<div class="mk-mb"><div class="mk-bigq">!</div><div style="font-size:22px;margin-bottom:6px">' + esc(title) + '</div><div style="color:#777;margin-bottom:14px">' + esc(msg) + '</div><div class="mk" style="display:flex;gap:8px;justify-content:center"><button class="btn btn-default" data-no>Batal</button><button class="btn btn-info" data-yes>Ya, Simpan</button></div></div>', 'xs');
        w.querySelector('[data-no]').onclick = function () { closeModal(w); };
        w.querySelector('[data-yes]').onclick = function () { closeModal(w); onYes(); };
    }

    // Pencarian bebas (M:553-576): teks, angka (dengan/tanpa pemisah ribuan), tanggal dalam berbagai format.
    function hayOf(vals) {
        var parts = [];
        vals.forEach(function (v) {
            if (v === null || v === undefined || v === '') return;
            if (typeof v === 'number') { parts.push(String(v), en(v), en(v, 2), idn(v), Number(v).toLocaleString('id-ID')); return; }
            v = String(v);
            var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(v);
            if (m) { parts.push(v, fdate(v), m[3] + '/' + m[2] + '/' + m[1], m[3] + '-' + m[2] + '-' + m[1], (+m[3]) + ' ' + BULAN_ID[+m[2] - 1] + ' ' + m[1], MONTHS[+m[2] - 1]); return; }
            parts.push(v);
        });
        return parts.join(' | ').toLowerCase();
    }
    function matchQ(q, hay) {
        var toks = String(q || '').toLowerCase().trim().split(/\s+/).filter(Boolean);
        if (!toks.length) return true;
        var bare = hay.replace(/[.,]/g, '');
        return toks.every(function (tk) {
            if (hay.indexOf(tk) >= 0) return true;
            var tb = tk.replace(/[.,]/g, '');
            return /\d/.test(tk) && tb.length > 0 && bare.indexOf(tb) >= 0;
        });
    }

    // Pager mockup (M:414): ‹ 1 … n › dengan jendela ±2.
    function pager(page, pages) {
        var out = '<button data-page="' + Math.max(1, page - 1) + '"' + (page <= 1 ? ' disabled' : '') + ' aria-label="Sebelumnya">‹</button>';
        for (var p = 1; p <= pages; p++) {
            if (p === 1 || p === pages || Math.abs(p - page) <= 2) out += '<button data-page="' + p + '"' + (p === page ? ' class="on"' : '') + '>' + p + '</button>';
            else if (Math.abs(p - page) === 3) out += '<span class="muted">…</span>';
        }
        return out + '<button data-page="' + Math.min(pages, page + 1) + '"' + (page >= pages ? ' disabled' : '') + ' aria-label="Berikutnya">›</button>';
    }

    root.MockupUI = {esc: esc, en: en, idn: idn, fdate: fdate, toast: toast, openModal: openModal, closeModal: closeModal, confirmBox: confirmBox, hayOf: hayOf, matchQ: matchQ, pager: pager};
})(window);
