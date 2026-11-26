/* Tambahan untuk tabel DataTables yang kolomnya bisa digeser (ColReorder). Panggil PiutangKolom.init setelah DataTable dibuat.
 * 1. Judul grup: ColReorder 1.1.3 tidak mendukung header bertingkat (colspan/rowspan), jadi DataTables hanya memegang satu
 *    baris judul (th dengan data-grp) dan baris grup dibuat di <thead> terpisah di atasnya, disusun ulang tiap kolom dipindah.
 *    Sama dengan mockup (AG Grid): kolom berurutan satu grup digabung; anak yang dipindah keluar membelah grupnya.
 *    Class "...pin..." pada judul ikut disalin supaya sel grup di atas kolom kunci tetap terkunci.
 * 2. Ubah lebar: seret tepi kanan judul (kecuali No), minimal 70px (mockup). Saat pertama diseret lebar semua kolom dibekukan
 *    (table-layout: fixed + <colgroup>); lebar disimpan di th sehingga ikut pindah bersama kolomnya. Tidak disimpan ke browser.
 * 3. Kolom kunci kedua: posisinya (--kol-pin) = lebar kolom No yang sebenarnya, supaya tidak menimpa kolom No. */
window.PiutangKolom = {
    MIN: 70,
    init: function (table) {
        var $ = window.jQuery, $t = $(table), MIN = this.MIN;
        var $head = function () { return $t.children('thead').not('.kol-grp'); };
        var ths = function () { return $head().find('tr:first > th').toArray(); };

        if (!document.getElementById('kol-css')) {
            $('head').append('<style id="kol-css">' +
                'table th .kol-rz{position:absolute;top:0;right:0;bottom:0;width:7px;cursor:col-resize;border-right:2px solid transparent}' +
                'table th .kol-rz:hover{border-right-color:#3f8bba}' +
                'table th.kol-th{position:relative}' +
                'table.kol-fixed.kol-fixed{table-layout:fixed}' +
                'table.kol-fixed.kol-fixed th,table.kol-fixed.kol-fixed td{overflow:hidden;text-overflow:ellipsis;white-space:normal}' +
                'table.kol-fixed.kol-fixed td{overflow-wrap:break-word}' + // judul hanya patah di spasi
                'table.kol-fixed.kol-fixed th.sorting_disabled{padding-right:9px}' + // tanpa ikon urut
                'table.kol-fixed.kol-fixed td.num{white-space:nowrap}' +
                '</style>');
        }

        function group() {
            $t.children('thead.kol-grp').remove();
            var list = ths(), $tr = $('<tr>');
            if (!list.some(function (th) { return th.getAttribute('data-grp'); })) { return; }
            for (var i = 0, n; i < list.length; i += n) {
                var g = list[i].getAttribute('data-grp');
                n = 1;
                while (g && i + n < list.length && list[i + n].getAttribute('data-grp') === g) { n++; }
                if (g) {
                    $tr.append($('<th scope="colgroup">').attr('colspan', n).text(g));
                } else {
                    $tr.append($('<td>').addClass((list[i].className.match(/\S*pin\S*/g) || []).join(' ')));
                }
            }
            $head().before($('<thead class="kol-grp">').append($tr));
        }
        // <colgroup> mengikuti urutan judul saat ini; hanya ada setelah lebar pernah diubah.
        function widths() {
            if (!$t.hasClass('kol-fixed')) { return; }
            var total = 0, $cg = $('<colgroup class="kol-w">');
            ths().forEach(function (th) {
                var w = Number($(th).data('w'));
                total += w;
                $cg.append($('<col>').css('width', w + 'px'));
            });
            $t.children('colgroup.kol-w').remove();
            $t.prepend($cg).css({width: total + 'px', minWidth: 0});
        }
        function pin() {
            var no = ths().filter(function (th) { return /pinno/.test(th.className); })[0];
            if (no && no.offsetWidth) { $t[0].style.setProperty('--kol-pin', no.offsetWidth + 'px'); }
        }
        function freeze() {
            if ($t.hasClass('kol-fixed')) { return; }
            // ukur semua dulu baru diterapkan; lebar No mengikuti CSS supaya posisi kolom kunci kedua tidak bergeser
            var list = ths(), w = list.map(function (th) { return /pinno/.test(th.className) ? 52 : Math.max(th.offsetWidth, Math.ceil(th.getBoundingClientRect().width)); }); // bulatkan ke atas
            list.forEach(function (th, i) { $(th).data('w', w[i]); });
            $t.addClass('kol-fixed');
            widths();
            pin();
        }
        function start(e) {
            if (e.which !== 1) { return; }
            e.preventDefault();
            e.stopPropagation(); // jangan sampai ColReorder ikut menyeret kolomnya
            freeze();
            var th = this.parentNode, from = Number($(th).data('w')), x = e.pageX;
            $(document).on('mousemove.kolrz', function (m) {
                $(th).data('w', Math.max(MIN, from + m.pageX - x));
                widths();
            }).on('mouseup.kolrz', function () {
                $(document).off('.kolrz');
                // browser mengirim click setelah mouseup (bisa jatuh ke judul): tahan supaya tabel tidak ikut diurutkan
                var swallow = function (c) { c.stopPropagation(); c.preventDefault(); };
                document.addEventListener('click', swallow, true);
                setTimeout(function () { document.removeEventListener('click', swallow, true); }, 0);
            });
        }

        // tabel yang sama dipakai ulang antar tab: kembalikan lebar bawaan
        $t.removeClass('kol-fixed').css({width: '', minWidth: ''}).children('colgroup.kol-w').remove();
        ths().forEach(function (th) {
            if ($(th).text().trim() === 'No') { return; } // mockup: kolom No tidak bisa diubah lebarnya
            $(th).addClass('kol-th');
            $('<span class="kol-rz" aria-hidden="true">').appendTo(th).on('mousedown', start);
        });
        $t.off('column-reorder.kol draw.dt.kol')
            .on('column-reorder.kol', function () { group(); widths(); pin(); })
            .on('draw.dt.kol', pin); // lebar No bisa berubah mengikuti isi
        $(window).off('resize.kol-' + $t.attr('id')).on('resize.kol-' + $t.attr('id'), pin);
        group();
        pin();
    }
};
