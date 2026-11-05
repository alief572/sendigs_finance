/* Komponen UI bersama Pembayaran Periodik (mengikuti specs/input/Periodik.html): toast, dialog, dropdown
   departemen, peringatan meninggalkan halaman, pengiriman AJAX. Semua markup dibuat di dalam elemen .pjx. */
(function (w, d) {
	'use strict';
	var PJ = w.PJ = {};
	var dirtyFn = null, toastTimer = null;

	PJ.esc = function (s) {
		return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
			return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
		});
	};
	PJ.fmt = function (n) { return new Intl.NumberFormat('id-ID').format(Math.round(Number(n) || 0)); };
	PJ.root = function () { return d.querySelector('.pjx'); };

	// BEGIN money
	// Nominal rupiah: pemisah ribuan titik muncul saat mengetik; hanya angka yang diterima, posisi kursor dijaga.
	// shortcut: hanya rupiah bulat (desimal ikut terhapus), ubah bila ada nilai pecahan.
	PJ.money = function (n) { return String(Math.round(Number(n) || 0)).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); };
	PJ.maskMoney = function (el) {
		var digitsBeforeCaret = el.value.slice(0, el.selectionStart).replace(/\D/g, '').length;
		var digits = el.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
		el.value = digits === '' ? '' : PJ.money(digits);
		var pos = 0, seen = 0;
		while (pos < el.value.length && seen < digitsBeforeCaret) { if (/\d/.test(el.value.charAt(pos))) seen++; pos++; }
		el.setSelectionRange(pos, pos);
		return Number(digits) || 0;
	};
	PJ.moneyValue = function (text) { return Number(String(text).replace(/\D/g, '')) || 0; };
	// END money

	PJ.toast = function (text) {
		var el = d.getElementById('pjToast');
		if (!el) { el = d.createElement('div'); el.id = 'pjToast'; el.className = 'toast'; el.setAttribute('role', 'status'); PJ.root().appendChild(el); }
		el.textContent = text;
		el.classList.add('show');
		clearTimeout(toastTimer);
		toastTimer = setTimeout(function () { el.classList.remove('show'); }, 3600);
	};

	function modalEl() {
		var m = d.getElementById('pjModal');
		if (!m) {
			m = d.createElement('div');
			m.id = 'pjModal';
			m.className = 'pjm';
			m.setAttribute('role', 'dialog');
			m.setAttribute('aria-modal', 'true');
			m.innerHTML = '<div class="panel hl"><div id="pjModalBody"></div></div>';
			m.addEventListener('mousedown', function (e) { if (e.target === m) PJ.closeModal(); });
			PJ.root().appendChild(m);
		}
		return m;
	}
	PJ.openModal = function (html) { var m = modalEl(); d.getElementById('pjModalBody').innerHTML = html; m.classList.add('show'); return m; };
	PJ.closeModal = function () { var m = d.getElementById('pjModal'); if (m) m.classList.remove('show'); };
	d.addEventListener('keydown', function (e) { if (e.key === 'Escape') PJ.closeModal(); });

	/* ask({title, body(html), ok, okCls, cancel, onOk}) - dialog konfirmasi. onOk boleh mengembalikan false agar dialog tetap terbuka. */
	PJ.ask = function (o) {
		PJ.openModal('<div class="mhead"><h2>' + PJ.esc(o.title) + '</h2></div>' + (o.body || '') +
			'<div class="bar" style="justify-content:flex-end;margin:14px 0 0"><button type="button" class="btn g" data-cancel>' + PJ.esc(o.cancel || 'Batal') +
			'</button><button type="button" class="btn ' + (o.okCls || '') + '" data-ok>' + PJ.esc(o.ok || 'Ya') + '</button></div>');
		var body = d.getElementById('pjModalBody');
		body.querySelector('[data-cancel]').onclick = PJ.closeModal;
		body.querySelector('[data-ok]').onclick = function () { if (o.onOk && o.onOk() === false) return; PJ.closeModal(); };
		var first = body.querySelector('textarea,input,[data-ok]'); if (first) first.focus();
	};

	/* Peringatan meninggalkan halaman dengan isian belum disimpan. fn() mengembalikan true bila ada perubahan. */
	PJ.guard = function (fn) { dirtyFn = fn; };
	PJ.clean = function () { dirtyFn = null; };
	PJ.leave = function (url) {
		if (!dirtyFn || !dirtyFn()) { w.location.href = url; return; }
		PJ.ask({title: 'Tinggalkan halaman ini?', body: '<p class="hint">Isian yang belum disimpan akan hilang.</p>', cancel: 'Tetap di sini', ok: 'Ya, tinggalkan', okCls: 'r',
			onOk: function () { dirtyFn = null; w.location.href = url; }});
	};
	d.addEventListener('click', function (e) {
		var a = e.target.closest && e.target.closest('.main-sidebar a[href], .sidebar-menu a[href], .navbar a[href]');
		if (!a || !dirtyFn || !dirtyFn()) return;
		var href = a.getAttribute('href');
		if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return;
		e.preventDefault(); e.stopPropagation(); PJ.leave(a.href);
	}, true);
	w.addEventListener('beforeunload', function (e) { if (dirtyFn && dirtyFn()) { e.preventDefault(); e.returnValue = ''; } });

	/* post(url, FormData|object) -> Promise<json>. Galat jaringan/HTTP menjadi pesan netral. */
	PJ.post = function (url, data) {
		var isFd = data instanceof FormData;
		return new Promise(function (resolve, reject) {
			$.ajax({url: url, type: 'POST', data: data, dataType: 'json', processData: !isFd, contentType: isFd ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
				success: resolve, error: function () { reject(new Error('Terjadi kesalahan. Silakan coba lagi.')); }});
		});
	};

	/* Dropdown pilih departemen pada tombol + New. items: [{id, name, sub, disabled}] */
	PJ.dropdown = function (btn, items, onPick) {
		var wrap = btn.parentNode, menu = null;
		function close() { if (menu) { menu.remove(); menu = null; btn.setAttribute('aria-expanded', 'false'); } }
		function render(q) {
			var list = menu.querySelector('.ddlist'), shown = items.filter(function (i) { return i.name.toLowerCase().indexOf(q.toLowerCase()) >= 0; });
			list.innerHTML = shown.length ? shown.map(function (i) {
				return '<button type="button" data-id="' + PJ.esc(i.id) + '"' + (i.disabled ? ' disabled' : '') + '>' + PJ.esc(i.name) +
					(i.sub ? '<span class="sub">' + PJ.esc(i.sub) + '</span>' : '') + '</button>';
			}).join('') : '<div class="ddnone">Tidak ada hasil</div>';
		}
		btn.addEventListener('click', function (e) {
			e.stopPropagation();
			if (menu) { close(); return; }
			menu = d.createElement('div');
			menu.className = 'ddmenu';
			menu.innerHTML = '<div class="ddsearch"><input type="text" placeholder="Cari department..." autocomplete="off" aria-label="Cari department"></div><div class="ddhead">Departemen</div><div class="ddlist"></div>';
			wrap.appendChild(menu); btn.setAttribute('aria-expanded', 'true'); render('');
			var inp = menu.querySelector('input'); inp.focus();
			inp.addEventListener('input', function () { render(inp.value); });
			inp.addEventListener('keydown', function (ev) { if (ev.key === 'Enter') { var f = menu.querySelector('.ddlist button:not([disabled])'); if (f) f.click(); } if (ev.key === 'Escape') close(); });
			menu.addEventListener('click', function (ev) { var b = ev.target.closest('button[data-id]'); if (b && !b.disabled) { close(); onPick(b.getAttribute('data-id')); } });
		});
		d.addEventListener('click', function (e) { if (menu && !wrap.contains(e.target)) close(); });
	};
})(window, document);
