<?php
/* Form Budget Pembayaran Periodik (mMaster di specs/input/Periodik.html). Baris dirender di browser dari $state (JSON):
   {dept, rows[], pending, rejected{}, coa{}, baseYm, nextBg}. Simpan tidak mengubah master; perubahan menjadi BG
   (BG-YYMM-NNN) yang menunggu Approval Perubahan Budget. Variabel: $state, $dept, $dept_name, $is_new_dept. */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
?>
<link rel="stylesheet" href="<?= base_url('assets/css/periodik_ui.css') ?>">
<div class="pjx">
<button type="button" class="back" id="pjBack">&#8592; Kembali ke daftar budget</button>
<h1 class="page-title">Budget Pembayaran Periodik <small><?= $is_new_dept ? 'Tambah budget' : 'Edit budget' ?></small></h1>
<div class="panel hl">
	<div id="pjBanner"></div>
	<div class="grid2">
		<div><span>Departement <b style="color:var(--red)">*</b></span><div class="ro"><?= $e($dept_name) ?></div><span class="sub">Dipilih dari tombol + New di daftar budget.</span></div>
		<div><span>Nomor perubahan</span><div class="ro" id="pjNo">&nbsp;</div><span class="sub">Dibuat saat perubahan diajukan.</span></div>
	</div>
	<?php if ($is_new_dept) : ?><p class="hint" id="pjNewHint">Departemen ini belum punya budget. Isi item pertama di baris yang sudah disiapkan, lalu klik <b>+ Tambah item</b> untuk item berikutnya.</p><?php endif; ?>
	<fieldset class="plain" id="pjFields">
		<div class="scroll"><table class="mtbl">
			<thead><tr><th>#</th><th>Post Pengeluaran</th><th>Nama Barang / Jasa</th><th>Bulanan / Tahunan</th><th>Jadwal Pembayaran</th><th class="num">Perkiraan Biaya</th><th style="min-width:230px">Masa berlaku</th><th>Status</th><th>Aksi</th></tr></thead>
			<tbody id="pjRows"></tbody>
		</table></div>
		<datalist id="masaList"><option value="3"><option value="6"><option value="12"><option value="24"><option value="36"></datalist>
		<div class="bar" style="margin-top:12px"><button type="button" class="btn g" id="pjAdd">+ Tambah item</button></div>
	</fieldset>
	<div class="actbar">
		<span class="hint" style="margin:0" id="pjFootNote">Perubahan berlaku setelah disetujui di Approval Perubahan Budget.</span><span class="sp"></span>
		<button type="button" class="btn o" id="pjCancel">Batalkan perubahan</button>
		<button type="button" class="btn" id="pjSave">Ajukan perubahan</button>
	</div>
</div>
</div>

<script src="<?= base_url('assets/js/periodik_ui.js') ?>"></script>
<script>
(function () {
	var S = <?= $state ?>;
	var FIELDS = ['coa', 'nama', 'tipe', 'tanggal', 'nilai', 'masa_berlaku', 'masa_awal', 'aktif'];
	var LABELS = {coa: 'Post pengeluaran', nama: 'Nama barang / jasa', tipe: 'Bulanan / Tahunan', tanggal: 'Jadwal pembayaran', nilai: 'Perkiraan biaya', masa_berlaku: 'Masa berlaku', masa_awal: 'Sudah dibayar sebelumnya', aktif: 'Status'};
	var KIND = {new: ['P', 'Item baru'], edit: ['W', 'Ubah'], off: ['R', 'Nonaktifkan'], on: ['A', 'Aktifkan']};
	var MON = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
	var root = document.querySelector('.pjx'), tbody = document.getElementById('pjRows');
	var tried = false, saving = false;

	function norm(r) {
		return {id: r.id == null || r.id === '' ? null : Number(r.id), coa: String(r.coa || '').trim(), nama: String(r.nama || '').trim(), tipe: r.tipe === 'tahun' ? 'tahun' : 'bulan',
			tanggal: String(r.tanggal || '').trim(), nilai: Number(r.nilai) || 0, masa_berlaku: Math.max(0, parseInt(r.masa_berlaku, 10) || 0),
			masa_awal: Math.max(0, parseInt(r.masa_awal, 10) || 0), aktif: r.aktif === undefined || Number(r.aktif) ? 1 : 0};
	}
	var orig = {};
	S.rows.forEach(function (r) { orig[r.id] = norm(r); });
	var draft = S.rows.map(function (r) { var c = norm(r); c.in_sys = Number(r.in_sys) || 0; c.pend = Number(r.pend) || 0; return c; });
	function blankRow() { return {id: null, _new: true, coa: '', nama: '', tipe: 'bulan', tanggal: '1', nilai: 0, masa_berlaku: 0, masa_awal: 0, aktif: 1, in_sys: 0, pend: 0}; }
	if (!draft.length && !S.pending) draft.push(blankRow());

	var money = PJ.money, maskMoney = PJ.maskMoney;
	function isBlank(r) { return r._new && !String(r.nama).trim() && !(Number(r.nilai) > 0); }
	function use(r) { var n = !r.id; var inSys = n ? 0 : r.in_sys; return {isNew: n, inSys: inSys, used: (Number(r.masa_awal) || 0) + inSys}; }

	function endOf(r, used) {
		if (!(r.masa_berlaku > 0)) return null;
		var rem = r.masa_berlaku - used; if (rem <= 0) return 'Selesai';
		var y = parseInt(S.baseYm.slice(0, 4), 10), m = parseInt(S.baseYm.slice(5, 7), 10) - 1;
		if (r.tipe === 'tahun') y += rem - 1; else { var t = m + rem - 1; y += Math.floor(t / 12); m = t % 12; }
		return MON[m] + ' ' + y + ' (sisa ' + rem + ' kali)';
	}
	function mfv(k, v) {
		if (k === 'nilai') return PJ.fmt(v);
		if (k === 'masa_berlaku') return Number(v) > 0 ? v + ' kali' : 'Selalu';
		if (k === 'tipe') return v === 'tahun' ? 'Tahunan' : 'Bulanan';
		if (k === 'aktif') return Number(v) ? 'Aktif' : 'Nonaktif';
		return PJ.esc(v === '' || v == null ? '-' : v);
	}
	// Jadwal tahunan tersimpan sebagai tanggal penuh MM/DD/YYYY; input type=date memakai YYYY-MM-DD.
	function usToIso(t) { var m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(t); return m ? m[3] + '-' + m[1] + '-' + m[2] : ''; }
	function isoToUs(v) { var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(v); return m ? m[2] + '/' + m[3] + '/' + m[1] : ''; }
	function validUs(t) { var m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(t); if (!m) return false; var d = new Date(+m[3], +m[1] - 1, +m[2]); return d.getMonth() === +m[1] - 1 && d.getDate() === +m[2]; }
	function schedText(r) {
		if (r.tipe === 'tahun') { var m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(r.tanggal); return m ? parseInt(m[2], 10) + ' ' + MON[parseInt(m[1], 10) - 1] + ' ' + m[3] : (r.tanggal || '-'); }
		return r.tanggal ? 'Tgl ' + parseInt(r.tanggal, 10) : '-';
	}
	// Ringkasan jadwal dalam bahasa biasa, tampil di bawah input supaya tidak bergantung pada locale browser (dd/mm vs mm/dd).
	function whenText(r) {
		if (r.tipe === 'tahun') { var m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(r.tanggal); return m ? 'Tiap tahun · ' + parseInt(m[2], 10) + ' ' + MON[parseInt(m[1], 10) - 1] : ''; }
		return /^\d+$/.test(r.tanggal) && Number(r.tanggal) >= 1 && Number(r.tanggal) <= 31 ? 'Tiap bulan · tanggal ' + parseInt(r.tanggal, 10) : '';
	}
	function whenHtml(r) { var t = whenText(r); return t ? '<i class="fa fa-calendar-o" aria-hidden="true"></i> ' + PJ.esc(t) : '<i class="fa fa-exclamation-circle" aria-hidden="true"></i> Jadwal belum diatur'; }

	function diff() {
		var out = [];
		draft.forEach(function (r) {
			if (isBlank(r)) return;
			var a = norm(r);
			if (r._new || !r.id) { out.push({kind: 'new', id: null, before: null, after: a, fields: []}); return; }
			var o = orig[r.id]; if (!o) return;
			var ch = FIELDS.filter(function (k) { return k === 'nilai' ? Math.abs(o[k] - a[k]) >= 0.005 : o[k] !== a[k]; });
			if (ch.length) out.push({kind: ch.length === 1 && ch[0] === 'aktif' ? (a.aktif ? 'on' : 'off') : 'edit', id: r.id, before: o, after: a, fields: ch});
		});
		return out;
	}

	function note(r, isNew) {
		if (isNew) return '<span class="tag chip new">Item baru, belum disimpan</span>';
		var p = S.pending, pl = p && p.lines.filter(function (x) { return Number(x.id) === Number(r.id); })[0];
		if (pl) return '<span class="tag cic">Menunggu approval ' + PJ.esc(p.no) + '</span><span class="sub">' + PJ.esc(KIND[pl.kind][1]) +
			(pl.kind === 'edit' ? ': ' + pl.fields.map(function (k) { return LABELS[k]; }).join(', ') : '') + '</span>';
		var o = orig[r.id], a = norm(r);
		if (o && FIELDS.some(function (k) { return String(o[k]) !== String(a[k]); })) return '<span class="tag chip mod">Diubah, belum disimpan</span>';
		var rej = S.rejected[r.id];
		if (rej) return '<span class="note" style="display:block;margin-top:4px">Perubahan ditolak di ' + PJ.esc(rej.no) + ': ' + PJ.esc(rej.note) + '</span>';
		return '';
	}
	function stat(r) {
		var u = use(r), n = norm(r), done = n.masa_berlaku > 0 && u.used >= n.masa_berlaku;
		var st = !n.aktif ? ['R', 'Nonaktif'] : done ? ['P', 'Selesai'] : ['A', 'Aktif'];
		return '<span class="pill ' + st[0] + '">' + st[1] + '</span>' + note(r, u.isNew);
	}
	function info(r) {
		var u = use(r), masa = Number(r.masa_berlaku) || 0, pct = masa > 0 ? Math.min(100, Math.round(u.used / masa * 100)) : 0;
		return (tried && masa > 0 && masa < u.used ? '<div class="err">Tidak boleh kurang dari ' + u.used + ' (sudah terpakai).</div>' : '') +
			'<div class="masainfo">' + (masa > 0 ? '<span class="sub">Perkiraan berakhir: <b>' + PJ.esc(endOf(norm(r), u.used)) + '</b></span><div class="prog"><i style="width:' + pct + '%"></i></div><b>' + u.used + ' / ' + masa + '</b> terpakai'
				: '<span class="sub">Tanpa batas</span><b>' + u.used + '</b> kali terbayar') +
			'<span class="sub">' + (Number(r.masa_awal) || 0) + ' sebelum sistem, ' + u.inSys + ' di sistem' + (r.pend ? ', ' + r.pend + ' menunggu approval' : '') + '</span></div>';
	}
	function coaOptions(sel) {
		var o = '<option value="">Pilih post pengeluaran</option>';
		Object.keys(S.coa).forEach(function (k) { o += '<option value="' + PJ.esc(k) + '"' + (k === sel ? ' selected' : '') + '>' + PJ.esc(S.coa[k]) + '</option>'; });
		if (sel && !S.coa[sel]) o += '<option value="' + PJ.esc(sel) + '" selected>' + PJ.esc(sel) + '</option>';
		return o;
	}
	function schedInput(r, ix) {
		var when = '<span class="when' + (whenText(r) ? '' : ' warn') + '" data-mwhen="' + ix + '">' + whenHtml(r) + '</span>';
		if (r.tipe === 'tahun') {
			return '<div class="sched"><input type="date" data-mf="tanggal" data-ix="' + ix + '" value="' + usToIso(r.tanggal) + '" aria-label="Tanggal pembayaran tahunan">' + when + '</div>';
		}
		return '<div class="sched"><div class="fld"><span class="pre">Tanggal</span><input type="number" min="1" max="31" inputmode="numeric" data-mf="tanggal" data-ix="' + ix + '" value="' + PJ.esc(r.tanggal) + '" aria-label="Tanggal pembayaran tiap bulan (1-31)"></div>' + when + '</div>';
	}
	function row(r, ix) {
		var u = use(r), masa = Number(r.masa_berlaku) || 0;
		return '<tr data-mrow="' + ix + '" class="' + (!Number(r.aktif) ? 'lockrow' : '') + '"><td>' + (ix + 1) + '</td>' +
			'<td style="min-width:150px;max-width:190px"><select data-mf="coa" data-ix="' + ix + '">' + coaOptions(r.coa) + '</select></td>' +
			'<td style="min-width:180px"><textarea data-mf="nama" data-ix="' + ix + '" rows="2" placeholder="Wajib diisi">' + PJ.esc(r.nama) + '</textarea></td>' +
			'<td style="min-width:110px"><select data-mf="tipe" data-ix="' + ix + '"><option value="bulan"' + (r.tipe === 'bulan' ? ' selected' : '') + '>Bulanan</option><option value="tahun"' + (r.tipe === 'tahun' ? ' selected' : '') + '>Tahunan</option></select></td>' +
			'<td style="min-width:190px" data-msched="' + ix + '">' + schedInput(r, ix) + '</td>' +
			'<td style="min-width:130px"><input type="text" inputmode="numeric" autocomplete="off" data-mf="nilai" data-ix="' + ix + '" value="' + (Number(r.nilai) > 0 ? money(r.nilai) : '') + '" placeholder="0"><span class="rpv" data-mrpv="' + ix + '">Rp ' + PJ.fmt(r.nilai) + '</span></td>' +
			'<td style="min-width:230px"><div class="masa"><select data-mf="mtype" data-ix="' + ix + '"><option value="0"' + (masa > 0 ? '' : ' selected') + '>Selalu</option><option value="1"' + (masa > 0 ? ' selected' : '') + '>Terbatas</option></select>' +
			(masa > 0 ? '<input type="number" min="1" list="masaList" class="mini" data-mf="masa_berlaku" data-ix="' + ix + '" value="' + masa + '" aria-label="Jumlah kali"><span>kali</span>' : '') + '</div>' +
			(u.isNew && masa > 0 ? '<div class="masa" style="margin-top:4px"><span class="sub" style="display:inline">Sudah dibayar sebelumnya</span><input type="number" min="0" class="mini" data-mf="masa_awal" data-ix="' + ix + '" value="' + (Number(r.masa_awal) || 0) + '"></div>' : '') +
			'<div data-minfo="' + ix + '">' + info(r) + '</div></td>' +
			'<td style="min-width:150px" data-mnote="' + ix + '">' + stat(r) + '</td>' +
			'<td style="white-space:nowrap">' + (u.isNew ? '<button type="button" class="btn r sm" data-mdel="' + ix + '">Hapus</button>'
				: '<button type="button" class="btn ' + (Number(r.aktif) ? 'r' : '') + ' sm" data-mtog="' + ix + '" style="min-width:96px">' + (Number(r.aktif) ? 'Nonaktifkan' : 'Aktifkan') + '</button>') + '</td></tr>';
	}
	function banner() {
		var p = S.pending, el = document.getElementById('pjBanner'); if (!p) { el.innerHTML = ''; return; }
		var nw = p.lines.filter(function (x) { return x.kind === 'new'; });
		el.innerHTML = '<div class="warn" style="margin:0 0 12px"><b>' + PJ.esc(p.no) + ' menunggu Approval Perubahan Budget</b> (' + p.lines.length + ' perubahan, diajukan ' + PJ.esc(String(p.at).slice(0, 16)) + '). ' +
			'Master di bawah masih nilai yang berlaku sekarang. Perubahan baru untuk departemen ini bisa diajukan setelah ' + PJ.esc(p.no) + ' diputuskan. ' +
			'<a href="' + siteurl + 'approval_perubahan_budget" id="pjGoAppr">Buka di Approval Perubahan Budget &#8594;</a>' +
			(nw.length ? '<span class="sub">Item baru menunggu approval: ' + nw.map(function (x) { return PJ.esc(x.nama); }).join(', ') + '</span>' : '') + '</div>';
	}
	function bar() {
		var n = diff().length, b = document.getElementById('pjSave');
		b.disabled = !n || !!S.pending; b.textContent = n ? 'Ajukan ' + n + ' perubahan' : 'Belum ada perubahan';
		document.getElementById('pjCancel').disabled = !n;
	}
	function render() {
		tbody.innerHTML = draft.length ? draft.map(row).join('') : '<tr><td colspan="9">' + (S.pending ? 'Belum ada item yang berlaku. Item baru di atas masih menunggu approval.' : 'Belum ada item. Klik + Tambah item.') + '</td></tr>';
		document.getElementById('pjFields').disabled = !!S.pending;
		document.getElementById('pjNo').innerHTML = PJ.esc(S.pending ? S.pending.no : 'Auto (' + S.nextBg + ')');
		document.getElementById('pjFootNote').textContent = S.pending ? 'Terkunci sampai ' + S.pending.no + ' diputuskan.' : 'Perubahan berlaku setelah disetujui di Approval Perubahan Budget.';
		var hint = document.getElementById('pjNewHint'); if (hint && S.pending) hint.hidden = true;
		banner(); bar();
	}
	function refresh(ix, full) {
		var r = draft[ix];
		if (full) { var tr = tbody.querySelector('tr[data-mrow="' + ix + '"]'); tr.outerHTML = row(r, ix); }
		else {
			tbody.querySelector('[data-minfo="' + ix + '"]').innerHTML = info(r); tbody.querySelector('[data-mnote="' + ix + '"]').innerHTML = stat(r);
			var rp = tbody.querySelector('[data-mrpv="' + ix + '"]'); if (rp) rp.textContent = 'Rp ' + PJ.fmt(r.nilai);
			var w = tbody.querySelector('[data-mwhen="' + ix + '"]'); if (w) { w.innerHTML = whenHtml(r); w.classList.toggle('warn', !whenText(r)); }
		}
		bar();
	}

	tbody.addEventListener('input', function (e) {
		var el = e.target.closest('[data-mf]'); if (!el) return;
		var ix = Number(el.dataset.ix), r = draft[ix], k = el.dataset.mf;
		if (k === 'mtype') return;
		if (k === 'tanggal') { r.tanggal = r.tipe === 'tahun' ? isoToUs(el.value) : String(el.value); }
		else if (k === 'nilai') { r.nilai = maskMoney(el); }
		else if (k === 'masa_berlaku' || k === 'masa_awal') { r[k] = Number(el.value) || 0; }
		else { r[k] = el.value; }
		refresh(ix, false);
	});
	tbody.addEventListener('change', function (e) {
		var el = e.target.closest('[data-mf]'); if (!el) return;
		var ix = Number(el.dataset.ix), r = draft[ix], k = el.dataset.mf;
		if (k === 'mtype') { r.masa_berlaku = el.value === '1' ? (r.masa_berlaku > 0 ? r.masa_berlaku : 12) : 0; refresh(ix, true); }
		else if (k === 'tipe') { r.tipe = el.value; r.tanggal = el.value === 'tahun' ? '01/01/' + S.baseYm.slice(0, 4) : '1'; refresh(ix, true); }
	});
	tbody.addEventListener('click', function (e) {
		var b = e.target.closest('button'); if (!b) return;
		if (b.dataset.mdel !== undefined) { draft.splice(Number(b.dataset.mdel), 1); render(); }
		else if (b.dataset.mtog !== undefined) { var r = draft[Number(b.dataset.mtog)]; r.aktif = Number(r.aktif) ? 0 : 1; refresh(Number(b.dataset.mtog), true); }
	});
	document.getElementById('pjAdd').onclick = function () { draft.push(blankRow()); render(); var t = tbody.querySelector('tr:last-child [data-mf="nama"]'); if (t) t.focus(); };

	PJ.guard(function () { return !saving && diff().length > 0; });
	document.getElementById('pjBack').onclick = function () { PJ.leave(siteurl + 'budget_periodik'); };
	document.getElementById('pjCancel').onclick = function () {
		var n = diff().length; if (!n) return;
		PJ.ask({title: 'Batalkan perubahan?', body: '<p class="hint">' + n + ' perubahan yang belum diajukan akan dikembalikan ke nilai master.</p>', cancel: 'Tidak', ok: 'Ya, batalkan', okCls: 'r',
			onOk: function () {
				draft = S.rows.map(function (r) { var c = norm(r); c.in_sys = Number(r.in_sys) || 0; c.pend = Number(r.pend) || 0; return c; });
				if (!draft.length) draft.push(blankRow());
				tried = false; render(); PJ.toast('Perubahan dibatalkan.');
			}});
	};

	function validate() {
		var rows = draft.filter(function (r) { return !isBlank(r); });
		if (!rows.length) return 'Isi minimal satu item budget sebelum mengajukan.';
		for (var i = 0; i < rows.length; i++) {
			var r = rows[i], n = norm(r), used = use(r).used;
			if (!n.nama) return 'Nama barang / jasa wajib diisi.';
			if (!(n.nilai > 0)) return 'Perkiraan biaya "' + n.nama + '" harus lebih dari 0.';
			if (n.masa_berlaku > 0 && n.masa_berlaku < used) return 'Masa berlaku "' + n.nama + '" tidak boleh kurang dari ' + used + ' kali.';
			if (!n.coa) return 'Post pengeluaran "' + n.nama + '" wajib dipilih.';
			var o = r.id ? orig[r.id] : null, schedChanged = !o || o.tipe !== n.tipe || o.tanggal !== n.tanggal; // data lama yang tak lazim tidak menghalangi perubahan lain
			var okSched = n.tipe === 'tahun' ? validUs(n.tanggal) : (/^\d+$/.test(n.tanggal) && Number(n.tanggal) >= 1 && Number(n.tanggal) <= 31);
			if (schedChanged && !okSched) return 'Jadwal pembayaran "' + n.nama + '" tidak valid.';
		}
		return '';
	}
	function lineText(x, side) {
		if (x.kind === 'new') return side === 'before' ? '<span class="sub">-</span>' : '<b>' + PJ.esc(x.after.nama) + '</b><span class="sub">' + mfv('tipe', x.after.tipe) + ' · jadwal ' + PJ.esc(schedText(x.after)) + ' · ' + mfv('nilai', x.after.nilai) + ' · ' + mfv('masa_berlaku', x.after.masa_berlaku) + '</span>';
		return x.fields.map(function (k) { return '<div><span class="sub" style="display:inline">' + LABELS[k] + ':</span> ' + mfv(k, x[side][k]) + '</div>'; }).join('');
	}
	document.getElementById('pjSave').onclick = function () {
		tried = true; var err = validate();
		if (err) { render(); PJ.toast(err); return; }
		if (S.pending) { PJ.toast('Masih ada perubahan ' + S.pending.no + ' yang menunggu Approval Perubahan Budget untuk departemen ini.'); return; }
		var lines = diff(); if (!lines.length) { PJ.toast('Tidak ada perubahan untuk disimpan.'); return; }
		PJ.ask({title: 'Ajukan ' + lines.length + ' perubahan budget?', ok: 'Ajukan ke Approval Perubahan Budget',
			body: '<p class="hint">' + PJ.esc(<?= json_encode($dept_name, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>) + ' · nomor perubahan ' + PJ.esc(S.nextBg) + '</p>' +
				'<div class="scroll"><table><thead><tr><th>Item</th><th>Perubahan</th><th>Sebelum</th><th>Sesudah</th></tr></thead><tbody>' +
				lines.map(function (x) {
					return '<tr><td>' + PJ.esc(x.kind === 'new' ? x.after.nama : x.before.nama) + '</td><td><span class="pill ' + KIND[x.kind][0] + '">' + KIND[x.kind][1] + '</span></td><td>' + lineText(x, 'before') + '</td><td>' + lineText(x, 'after') + '</td></tr>';
				}).join('') + '</tbody></table></div><p class="hint" style="margin-top:10px">Nilai master tidak berubah sampai perubahan ini disetujui.</p>',
			onOk: function () {
				var payload = draft.filter(function (r) { return !isBlank(r); }).map(norm);
				PJ.post(siteurl + 'budget_periodik/save_data', {departement: S.dept, rows: JSON.stringify(payload)}).then(function (r) {
					if (!r.save) { PJ.toast(r.msg || 'Terjadi kesalahan. Silakan coba lagi.'); return; }
					saving = true;
					try { sessionStorage.setItem('pjToast', r.count + ' perubahan dikirim ke Approval Perubahan Budget (' + r.no_bg + '). Berlaku setelah disetujui.'); sessionStorage.setItem('pjFlash', S.dept); } catch (x) {}
					location.href = siteurl + 'budget_periodik';
				}, function (err2) { PJ.toast(err2.message); });
			}});
	};
	var go = document.addEventListener('click', function (e) {
		var a = e.target.closest('#pjGoAppr'); if (!a) return; e.preventDefault(); PJ.leave(a.href);
	});
	render();
})();
</script>
