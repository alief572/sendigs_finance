<?php
/* Form Pengajuan Pembayaran Periodik (pForm di specs/input/Periodik.html).
   Variabel: $type (add|edit), $dept, $dept_name, $periode, $period_options, $rows, $hidden, $batch. */
$e   = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$fmt = function ($n) { return number_format((float) $n, 0, ',', '.'); };
$isEdit = $type === 'edit' && $batch;
?>
<link rel="stylesheet" href="<?= base_url('assets/css/periodik_ui.css') ?>">
<div class="pjx">
<button type="button" class="back" id="pjBack">&#8592; Kembali ke daftar pengajuan</button>
<h1 class="page-title">Pengajuan Pembayaran Periodik <small><?= $isEdit ? 'Edit ' . $e($batch['no']) : 'Buat batch baru' ?></small></h1>
<div class="panel hl">
	<div class="grid2">
		<div><span>Departemen <b style="color:var(--red)">*</b></span><div class="ro"><?= $e($dept_name) ?></div>
			<span class="sub"><?= $isEdit ? 'Departemen batch tidak bisa diubah.' : 'Dipilih dari tombol + New di daftar pengajuan.' ?></span></div>
		<div><span>Periode pembayaran</span>
			<select id="perSel" <?= $isEdit ? 'disabled' : '' ?>>
				<?php foreach ($period_options as $v => $t) : ?><option value="<?= $e($v) ?>" <?= $v === $periode ? 'selected' : '' ?>><?= $e($t) ?></option><?php endforeach; ?>
			</select></div>
		<div><span>No. Batch</span><input type="text" value="<?= $isEdit ? $e($batch['no']) : 'Auto (BP-' . date('ym') . '-NNN)' ?>" disabled>
			<span class="sub">No. item dibuat otomatis per item (PI-<?= date('Y') ?>-NNNNN, dst.)</span></div>
		<div><span>Tanggal</span><input type="text" value="<?= $e($isEdit ? substr($batch['date'], 0, 10) : date('Y-m-d')) ?>" disabled></div>
	</div>
	<p class="hint">Setiap item yang diisi biayanya langsung mendapat nomor pengajuan item (PI-YYYY-NNNNN). Nomor final dipastikan saat Simpan. Item yang sudah diajukan di periode ini terkunci dengan nomor itemnya. Item yang ditolak terbuka lagi dan bisa diajukan ulang di batch baru. Item yang masa berlakunya sudah habis atau dinonaktifkan tidak ditampilkan.</p>

	<form id="pjForm" enctype="multipart/form-data" onsubmit="return false">
	<input type="hidden" name="departement" value="<?= $e($dept) ?>">
	<input type="hidden" name="periode" value="<?= $e($periode) ?>">
	<input type="hidden" name="id" value="<?= $isEdit ? (int) $batch['id'] : '' ?>">
	<div class="scroll"><table>
		<thead><tr><th>#</th><th>No. Item</th><th>Deskripsi</th><th>Jadwal</th><th class="num">Budget</th><th>Perkiraan biaya</th><th>Keterangan</th><th>Dokumen</th><th>Bank / No Rek / Nama</th></tr></thead>
		<tbody>
		<?php if (!$rows) : ?><tr><td colspan="9">Tidak ada item aktif untuk departemen ini.</td></tr><?php endif; ?>
		<?php foreach ($rows as $ix => $r) :
			$m = $r['m']; $st = $r['st']; $mine = $r['mine'];
			$lock = $st['state'] === 'lock' && !$mine;
			$lockItem = $lock && !empty($st['lock']) ? $st['lock'] : null;
			$back = $st['state'] === 'back' && !$mine ? $st['back'] : null;
			$mid = (int) $m['id'];
			$value = $mine ? $mine['nominal'] : ($back ? $back['nominal'] : 0);
			$userKet = $mine ? Periodik_logic::user_ket($mine['ket']) : '';
			?>
			<tr class="<?= $lock ? 'lockrow' : '' ?>" data-mid="<?= $mid ?>">
				<td><?= $ix + 1 ?></td>
				<td class="no" data-ino="<?= $mid ?>" <?= $mine ? 'data-keep="' . $e($mine['no_item']) . '"' : '' ?> style="min-width:170px"><?= $lockItem ? $e($lockItem['no_item']) : '' ?></td>
				<td style="min-width:260px"><?= $e($m['nama']) ?><?= $m['tipe'] === 'tahun' ? ' <span class="sub" style="display:inline">(tahunan)</span>' : '' ?>
					<?php if ($r['tag'] !== '') : ?><span class="tag cic"><?= $e($r['tag']) ?></span><?php endif; ?>
					<?php if ($lock && $lockItem && empty($st['last'])) : ?><span class="tag" <?= $lockItem['status'] === 'A' ? 'style="background:var(--green)"' : '' ?>><?= $lockItem['status'] === 'A' ? 'Sudah disetujui' : 'Sudah diajukan' ?> (<?= $e($lockItem['no_item']) ?>)</span><?php endif; ?>
					<?php if ($lock && $lockItem && !empty($st['last'])) : ?><span class="tag">Menunggu approval (<?= $e($lockItem['no_item']) ?>)</span><?php endif; ?>
					<?php if ($back) : ?>
						<span class="tag back">Ditolak di <?= $e($back['no_item']) ?></span><div class="note"><?= $e($back['note']) ?></div>
						<button type="button" class="btn g sm" style="margin-top:4px" data-closeitem="<?= $e($back['no_item']) ?>">Tidak diajukan ulang</button>
					<?php endif; ?>
				</td>
				<td style="white-space:nowrap"><?= $e(periodik_schedule_text($m['tipe'], $m['tanggal'])) ?></td>
				<td class="num mono"><?= $fmt($m['nilai']) ?></td>
				<td style="min-width:130px"><input type="text" inputmode="numeric" autocomplete="off" name="biaya[<?= $mid ?>]" data-biaya="<?= $mid ?>" data-max="<?= (float) $m['nilai'] ?>" value="<?= $e(number_format((float) $value, 0, ',', '.')) ?>" <?= $lock ? 'disabled' : '' ?> aria-label="Perkiraan biaya <?= $e($m['nama']) ?>"></td>
				<td style="min-width:180px"><textarea name="ket[<?= $mid ?>]" data-ket="<?= $mid ?>" <?= $lock ? 'disabled placeholder="Terkunci"' : '' ?>><?= $e($userKet) ?></textarea></td>
				<td style="min-width:190px"><?php if ($lock) : ?><span class="sub">-</span><?php else : ?>
					<input type="file" name="doc_<?= $mid ?>" data-docin="<?= $mid ?>" accept=".pdf,.jpg,.jpeg,.png" aria-label="Dokumen <?= $e($m['nama']) ?>">
					<span class="rpv" data-docname="<?= $mid ?>"><?= $mine && $mine['doc_file'] !== '' ? 'File: ' . $e($mine['doc_file']) : 'Belum ada file' ?></span>
				<?php endif; ?></td>
				<td style="min-width:170px">
					<?php foreach (array('bank_id' => 'Nama bank', 'accnumber' => 'No rekening', 'accname' => 'Nama pemilik') as $f => $ph) : ?>
						<input type="text" name="<?= $f ?>[<?= $mid ?>]" data-bank value="<?= $e($r['bank'][$f]) ?>" placeholder="<?= $ph ?>" <?= $lock ? 'disabled' : '' ?> aria-label="<?= $ph ?> <?= $e($m['nama']) ?>" style="margin-bottom:3px">
					<?php endforeach; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table></div>
	</form>

	<?php if ($hidden) : ?>
		<div class="hiddennote"><b><?= count($hidden) ?> item tidak ditampilkan:</b>
			<?= implode(', ', array_map(function ($r) use ($e) {
				$s = $r['st']['state'];
				$why = $s === 'done' ? 'selesai ' . $r['st']['used'] . '/' . $r['m']['masa_berlaku'] : ($s === 'closed' ? 'ditutup, tidak diajukan ulang' : 'dinonaktifkan');
				return $e($r['m']['nama']) . ' (' . $why . ')';
			}, $hidden)) ?>. <a href="#" id="pjGoMaster">Lihat di Budget Pembayaran Periodik</a></div>
	<?php endif; ?>

	<div class="actbar">
		<span>Total: <b class="mono" id="ptotal">0</b> dari <b id="pcount">0</b> item</span><span class="sp"></span>
		<button type="button" class="btn o" id="pjCancel">Batal</button>
		<button type="button" class="btn" id="pjSubmit"><?= $isEdit ? 'Simpan perubahan' : 'Simpan' ?></button>
	</div>
</div>
</div>

<script src="<?= base_url('assets/js/periodik_ui.js') ?>"></script>
<script>
(function () {
	var isEdit = <?= $isEdit ? 'true' : 'false' ?>;
	var deptName = <?= json_encode($dept_name, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
	var periodLabel = <?= json_encode(isset($period_options[$periode]) ? $period_options[$periode] : $periode, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
	var batchNo = <?= json_encode($isEdit ? $batch['no'] : '', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
	var listUrl = siteurl + 'pengajuan_rutin';
	var form = document.getElementById('pjForm'), root = document.querySelector('.pjx');
	var submitBtn = document.getElementById('pjSubmit'), saving = false;

	function snap() {
		var s = [];
		form.querySelectorAll('[data-biaya]:not([disabled]),[data-ket]:not([disabled]),[data-bank]:not([disabled])').forEach(function (el) { s.push(el.value); });
		form.querySelectorAll('[data-docin]').forEach(function (el) { s.push(el.files.length ? el.files[0].name : ''); });
		return s.join('\u0001');
	}
	var base = snap();
	PJ.guard(function () { return !saving && snap() !== base; });

	function rows() {
		return Array.prototype.map.call(form.querySelectorAll('[data-biaya]:not([disabled])'), function (inp) {
			var mid = inp.dataset.biaya, tr = inp.closest('tr'), cell = tr.querySelector('[data-ino]');
			return {mid: mid, tr: tr, cell: cell, keep: cell.dataset.keep || '', v: PJ.moneyValue(inp.value),
				name: tr.children[2].firstChild.textContent.trim(), ket: tr.querySelector('[data-ket]').value.trim(),
				doc: (tr.querySelector('[data-docin]').files[0] || {}).name || (tr.querySelector('[data-docname]').textContent.replace(/^File: /, '').replace(/^Belum ada file$/, ''))};
		});
	}
	function total() {
		var t = 0, c = 0;
		rows().forEach(function (r) {
			if (r.v > 0) {
				r.cell.innerHTML = r.keep ? PJ.esc(r.keep) + '<span class="sub">Nomor tetap</span>' : 'PI-' + new Date().getFullYear() + '-NNNNN<span class="sub">Nomor saat disimpan</span>';
				t += r.v; c++;
			} else {
				r.cell.innerHTML = r.keep ? '<s>' + PJ.esc(r.keep) + '</s><span class="sub note">Dikeluarkan dari batch</span>' : '<span class="sub">Otomatis saat biaya diisi</span>';
			}
		});
		document.getElementById('ptotal').textContent = PJ.fmt(t);
		document.getElementById('pcount').textContent = c;
		submitBtn.disabled = !c;
	}
	function over(inp) { return PJ.moneyValue(inp.value) > parseFloat(inp.dataset.max); }
	form.addEventListener('input', function (e) {
		if (e.target.matches('[data-biaya]')) { PJ.maskMoney(e.target); e.target.style.borderColor = over(e.target) ? '#d9534f' : ''; e.target.title = over(e.target) ? 'Melebihi budget' : ''; }
		total();
	});
	form.addEventListener('change', function (e) {
		var f = e.target.closest('[data-docin]');
		if (f) { root.querySelector('[data-docname="' + f.dataset.docin + '"]').textContent = f.files.length ? 'File: ' + f.files[0].name : 'Belum ada file'; }
		total();
	});
	total();

	document.getElementById('pjBack').onclick = document.getElementById('pjCancel').onclick = function () { PJ.leave(listUrl); };
	var go = document.getElementById('pjGoMaster');
	if (go) go.onclick = function (e) { e.preventDefault(); PJ.leave(siteurl + 'budget_periodik'); };

	var sel = document.getElementById('perSel');
	sel.addEventListener('change', function () {
		var target = siteurl + 'pengajuan_rutin/create/<?= $e(rawurlencode($dept)) ?>?periode=' + encodeURIComponent(sel.value);
		var prev = '<?= $e($periode) ?>';
		if (snap() === base) { location.href = target; return; }
		sel.value = prev;
		PJ.ask({title: 'Ganti periode?', body: '<p class="hint">Isian yang belum disimpan akan hilang.</p>', cancel: 'Tetap di sini', ok: 'Ya, ganti', okCls: 'r',
			onOk: function () { PJ.clean(); location.href = target; }});
	});

	root.addEventListener('click', function (e) {
		var b = e.target.closest('[data-closeitem]'); if (!b) return;
		var no = b.dataset.closeitem;
		PJ.ask({title: 'Tutup ' + no + '?', ok: 'Tutup item', okCls: 'r',
			body: '<p class="hint">Item ini tidak akan diajukan ulang untuk periode ini, misalnya tagihannya batal atau sudah dibayar lewat jalur lain. Item hilang dari form untuk periode ini dan statusnya tercatat di riwayat.</p>' +
				'<label for="closeNote"><b>Alasan</b> <b style="color:var(--red)">*</b></label><textarea id="closeNote" rows="3" style="width:100%"></textarea>' +
				'<div class="err" id="closeErr" hidden>Alasan wajib diisi untuk menutup item.</div>',
			onOk: function () {
				var ta = document.getElementById('closeNote'), note = ta.value.trim();
				if (!note) { document.getElementById('closeErr').hidden = false; ta.focus(); return false; }
				PJ.post(siteurl + 'pengajuan_rutin/close_item', {no_item: no, note: note}).then(function (r) {
					if (!r.save) { PJ.toast(r.msg || 'Terjadi kesalahan. Silakan coba lagi.'); return; }
					// isian item lain tetap ada: hanya baris ini yang dilepas dari form, tanpa memuat ulang halaman
					var tr = b.closest('tr'); tr.parentNode.removeChild(tr); base = snap(); total();
					PJ.toast(no + ' ditutup dan tidak muncul lagi di form periode ini.');
				}, function (err) { PJ.toast(err.message); });
			}});
	});

	submitBtn.onclick = function () {
		var picked = rows().filter(function (r) { return r.v > 0; });
		var bad = Array.prototype.filter.call(form.querySelectorAll('[data-biaya]:not([disabled])'), over);
		if (bad.length) { bad[0].focus(); PJ.toast('Perkiraan biaya tidak boleh melebihi budget (' + bad.length + ' item melebihi).'); return; }
		if (!picked.length) { PJ.toast(isEdit ? 'Minimal satu item harus diisi. Untuk membatalkan seluruh batch, pakai tombol Hapus di daftar.' : 'Isi perkiraan biaya minimal satu item sebelum menyimpan.'); return; }
		var out = rows().filter(function (r) { return r.v <= 0 && r.keep; });
		var sum = picked.reduce(function (s, r) { return s + r.v; }, 0);
		PJ.ask({title: isEdit ? 'Simpan perubahan ' + batchNo + '?' : 'Ajukan ' + picked.length + ' item untuk approval?', ok: isEdit ? 'Simpan perubahan' : 'Ajukan',
			body: '<p class="hint">' + PJ.esc(deptName) + ' · periode ' + PJ.esc(periodLabel) + (isEdit ? '' : ' · No. Batch BP-<?= date('ym') ?>-NNN') + '</p>' +
				'<div class="scroll"><table><thead><tr><th>No. Item</th><th>Deskripsi</th><th>Dokumen</th><th class="num">Perkiraan biaya</th></tr></thead><tbody>' +
				picked.map(function (r) {
					return '<tr><td class="no">' + (r.keep ? PJ.esc(r.keep) : 'Nomor saat disimpan' + (isEdit ? '<span class="sub">Baru</span>' : '')) + '</td><td>' + PJ.esc(r.name) + (r.ket ? '<span class="sub">' + PJ.esc(r.ket) + '</span>' : '') +
						'</td><td>' + (r.doc ? PJ.esc(r.doc) : '<span class="sub">Tidak ada file</span>') + '</td><td class="num mono">' + PJ.fmt(r.v) + '</td></tr>';
				}).join('') + '<tr><td colspan="3"><b>Total</b></td><td class="num mono"><b>' + PJ.fmt(sum) + '</b></td></tr></tbody></table></div>' +
				(out.length ? '<p class="note">Dikeluarkan dari batch: ' + out.map(function (r) { return PJ.esc(r.keep + ' ' + r.name); }).join(', ') + '</p>' : '') +
				'<p class="hint" style="margin-top:10px">Setelah disimpan, setiap item menunggu approval di menu Approval Pengajuan Periodik.</p>',
			onOk: function () { commit(); }});
	};

	function commit() {
		var fd = new FormData(form);
		form.querySelectorAll('[data-biaya]:not([disabled])').forEach(function (inp) { fd.set(inp.name, PJ.moneyValue(inp.value)); }); // server menerima angka murni, tanpa titik
		submitBtn.disabled = true;
		PJ.post(siteurl + 'pengajuan_rutin/save_data', fd).then(function (r) {
			if (!r.save) { submitBtn.disabled = false; PJ.toast(r.msg || 'Terjadi kesalahan. Silakan coba lagi.'); return; }
			saving = true;
			var msg = r.edit ? r.no_doc + ' diperbarui (' + r.count + ' item).' :
				r.no_doc + ' (periode ' + r.periode + ') tersimpan dengan ' + r.count + ' item (' + r.first + (r.count > 1 && r.last !== r.first ? ' s/d ' + r.last : '') + '), menunggu approval.';
			try { sessionStorage.setItem('pjToast', msg); sessionStorage.setItem('pjFlash', r.no_doc); } catch (x) {}
			location.href = listUrl;
		}, function (err) { submitBtn.disabled = false; PJ.toast(err.message); });
	}
})();
</script>
