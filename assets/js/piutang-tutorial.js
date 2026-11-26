/*
 * Tutorial Follow Up Piutang / Monitoring Piutang / Dashboard Plan Bayar (mockup specs/docs/monitoring-piutang, TOUR_CFG).
 * Memakai driver.js lokal (assets/plugins/driver). Tutorial tidak menyimpan data: hanya pindah tab / membuka form.
 *
 * PiutangTour.mount(host, {page: [step], flows: [{title, desc, steps: [step]}], cleanup: fn})
 *   step = {el: selector, t: judul, d: deskripsi (HTML tepercaya dari kode), pg: nama halaman, go: fn sebelum langkah tampil, wait: ms}
 *   Langkah tanpa `go` memakai halaman langkah sebelumnya; elemen yang tidak ada / tersembunyi tampil sebagai popover di tengah.
 */
(function (window, document) {
    'use strict';
    var WAIT = 450, active = null;

    function visible(sel) {
        var el = sel ? document.querySelector(sel) : null;
        return el && el.getClientRects().length ? el : undefined;
    }

    function norm(steps) {
        var lastPg = '';
        return steps.map(function (s) {
            var o = Object.assign({}, s);
            if (o.pg) { lastPg = o.pg; } else { o.pg = lastPg; }
            return o;
        });
    }

    function run(title, steps, cleanup) {
        if (!(window.driver && window.driver.js)) { window.alert('Tutorial belum termuat. Muat ulang halaman lalu coba lagi.'); return; }
        if (active) { try { active.destroy(); } catch (e) { /* sudah tertutup */ } }
        steps = norm(steps);
        var n = steps.length;
        if (!n) { return; }
        // Sebelum langkah i tampil, jalankan `go` terdekat (langkah i atau sebelumnya) lalu tunggu halaman / form siap.
        var prepare = function (i, cb) {
            var j = i;
            while (j > 0 && !steps[j].go) { j--; }
            var s = steps[j];
            if (s && s.go) {
                try { s.go(); } catch (e) { if (window.console) { window.console.warn('tutorial', e); } }
                setTimeout(cb, s.wait != null ? s.wait : WAIT);
            } else { cb(); }
        };
        // driver.js 1.3.1 hanya menerima selector/elemen: elemen dicari ulang setiap kali langkah ditampilkan (setelah `go`),
        // elemen yang tidak ada / tersembunyi diganti popover di tengah layar.
        var build = function () {
            return steps.map(function (s) {
                return {element: visible(s.el), popover: {title: s.t, description: '<div class="tour-fl">' + title + '</div>' + (s.pg ? '<span class="tour-pg">' + s.pg + '</span><br>' : '') + s.d}};
            });
        };
        var goTo = function (i) { prepare(i, function () { drv.setSteps(build()); drv.moveTo(i); }); };
        var drv = window.driver.js.driver({
            showProgress: true, progressText: '{{current}} dari {{total}}', nextBtnText: 'Lanjut &rarr;', prevBtnText: '&larr; Kembali', doneBtnText: 'Selesai',
            overlayOpacity: 0.45, stagePadding: 6, popoverClass: 'piutang-tour', steps: build(),
            onNextClick: function () { var i = drv.getActiveIndex(); if (i >= n - 1) { drv.destroy(); } else { goTo(i + 1); } },
            onPrevClick: function () { var i = drv.getActiveIndex(); if (i) { goTo(i - 1); } },
            onDestroyed: function () { active = null; if (cleanup) { cleanup(); } }
        });
        active = drv;
        prepare(0, function () { drv.setSteps(build()); drv.drive(0); });
    }

    function style() {
        if (document.getElementById('piutang-tour-css')) { return; }
        var st = document.createElement('style');
        st.id = 'piutang-tour-css';
        // Tampilan tombol dan menu mengikuti mockup "Tutorial v2"; menu sengaja tetap terang di tema apa pun.
        st.textContent = '.piutang-tour-bar{display:flex;justify-content:flex-end;margin:0 0 8px}' +
            '.piutang-tour-bar .tour-btn{display:inline-flex;align-items:center;gap:7px;background:#00a65a;color:#fff;border:0;border-radius:18px;padding:7px 16px 7px 12px;font-size:13.5px;font-weight:600;box-shadow:0 2px 6px rgba(0,0,0,.18)}' +
            '.piutang-tour-bar .tour-btn:hover{background:#008d4c}.piutang-tour-bar .tour-btn:focus-visible{outline:2px solid #00c0ef;outline-offset:2px}.piutang-tour-bar .tb-ic{font-size:16px;line-height:1}' +
            '.piutang-tour-bar .dropdown-menu{width:380px;max-width:calc(100vw - 32px);max-height:70vh;overflow:auto;padding:0;margin-top:6px;background:#fff;border:1px solid #ccc;border-radius:4px;box-shadow:0 8px 24px rgba(0,0,0,.22);font-size:13px}' +
            '.piutang-tour-bar .pt-h{padding:8px 14px;font-size:12px;font-weight:700;letter-spacing:.3px;text-transform:uppercase;color:#5f5f5f;background:#f4f4f4;border-bottom:1px solid #e3e3e3}' +
            '.piutang-tour-bar [data-tour]{display:block;width:100%;text-align:left;white-space:normal;border:0;border-bottom:1px solid #eee;background:#fff;padding:9px 14px;color:#333}' +
            '.piutang-tour-bar [data-tour]:hover,.piutang-tour-bar [data-tour]:focus{background:#eef6fb}.piutang-tour-bar [data-tour] b{display:block}.piutang-tour-bar [data-tour] small{display:block;color:#6c6c6c;margin-top:2px;font-size:12px}' +
            '.driver-popover.piutang-tour{max-width:380px}.piutang-tour .tour-fl{font-size:11.5px;text-transform:uppercase;letter-spacing:.3px;color:#5b6670;margin-bottom:4px}' +
            '.piutang-tour .tour-pg{display:inline-block;font-size:12px;font-weight:600;color:#1d5f93;margin-bottom:2px}';
        document.head.appendChild(st);
    }

    function mount(host, cfg) {
        if (!host) { return; }
        style();
        var flows = cfg.flows || [];
        var item = function (key, t, d) { return '<li><button type="button" data-tour="' + key + '"><b>' + t + '</b><small>' + d + '</small></button></li>'; };
        host.innerHTML = '<div class="dropdown piutang-tour-bar">' +
            '<button type="button" class="tour-btn dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
            '<span class="tb-ic" aria-hidden="true">&#10067;</span> Tutorial</button>' +
            '<ul class="dropdown-menu dropdown-menu-right">' +
            '<li class="pt-h">Halaman ini</li>' + item('page', 'Tutorial halaman ini', 'Penjelasan bagian-bagian halaman yang sedang dibuka.') +
            (flows.length ? '<li class="pt-h">Alur proses (pindah halaman otomatis)</li>' : '') +
            flows.map(function (f, i) { return item(i, f.title, (f.desc ? f.desc + ' · ' : '') + f.steps.length + ' langkah'); }).join('') +
            '</ul></div>';
        host.addEventListener('click', function (e) {
            var b = e.target.closest('[data-tour]');
            if (!b) { return; }
            var k = b.getAttribute('data-tour');
            if (k === 'page') { run(cfg.title || 'Halaman ini', cfg.page || [], cfg.cleanup); } else { run(flows[+k].title, flows[+k].steps, cfg.cleanup); }
        });
    }

    window.PiutangTour = {mount: mount, run: run};
})(window, document);
