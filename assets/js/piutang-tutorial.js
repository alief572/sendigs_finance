/*
 * Tutorial Follow Up Piutang / Monitoring Piutang / Dashboard Plan Bayar (mockup specs/docs/monitoring-piutang, TOUR_CFG).
 * Memakai driver.js lokal (assets/plugins/driver). Tutorial tidak menyimpan data: hanya pindah tab / membuka form.
 *
 * PiutangTour.mount(host, {page: [step], flows: [{title, steps: [step]}], cleanup: fn})
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
        st.textContent = '.piutang-tour-bar{display:flex;justify-content:flex-end;margin:0 0 8px}.piutang-tour-bar .dropdown-menu .btn-link{display:block;width:100%;text-align:left;padding:6px 14px;color:#333a40}' +
            '.driver-popover.piutang-tour{max-width:380px}.piutang-tour .tour-fl{font-size:11.5px;text-transform:uppercase;letter-spacing:.3px;color:#5b6670;margin-bottom:4px}' +
            '.piutang-tour .tour-pg{display:inline-block;font-size:12px;font-weight:600;color:#1d5f93;margin-bottom:2px}';
        document.head.appendChild(st);
    }

    function mount(host, cfg) {
        if (!host) { return; }
        style();
        var flows = cfg.flows || [];
        host.innerHTML = '<div class="dropdown piutang-tour-bar">' +
            '<button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
            '<i class="fa fa-question-circle" aria-hidden="true"></i> Tutorial <span class="caret"></span></button>' +
            '<ul class="dropdown-menu dropdown-menu-right">' +
            '<li><button type="button" class="btn btn-link" data-tour="page">Tutorial halaman ini</button></li>' +
            flows.map(function (f, i) { return '<li><button type="button" class="btn btn-link" data-tour="' + i + '">Alur proses: ' + f.title + '</button></li>'; }).join('') +
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
