<?php
/* Form keputusan approval satu batch per item (apprDetail di specs/input/Periodik.html).
   Variabel: $batch, $dept_name, $chain (no_item => [{no, note}]). */
$e   = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$fmt = function ($n) { return number_format((float) $n, 0, ',', '.'); };
$ro = function ($v, $cls = '') use ($e) { return '<div class="ro ' . $cls . '">' . ($v === '' || $v === null ? '&nbsp;' : $e($v)) . '</div>'; };
?>
<link rel="stylesheet" href="<?= base_url('assets/css/periodik_ui.css') ?>">
<div class="pjx">
<button type="button" class="back" id="pjBack">&#8592; Kembali ke daftar approval</button>
<h1 class="page-title">Approval Pengajuan Periodik <small><?= $e($batch['no']) ?></small></h1>
<div class="panel hl">
	<div class="grid2 apphead">
		<div><span>Departement</span><select disabled><option><?= $e($dept_name) ?></option></select></div>
		<div><span>No. Batch</span><?= $ro($batch['no']) ?></div>
		<div><span>Tanggal</span><?= $ro(substr((string) $batch['date'], 0, 10)) ?></div>
	</div>
	<div class="scroll"><table class="apptbl">
		<thead><tr><th>#</th><th>No. Item</th><th>Deskripsi</th><th>Jadwal Pembayaran</th><th class="num">Budget</th><th class="num">Perkiraan Biaya</th><th>Keterangan</th><th>Dokumen</th><th>Bank / No Rek / Nama</th></tr></thead>
		<tbody>
		<?php foreach ($batch['items'] as $ix => $i) : $ch = isset($chain[$i['no_item']]) ? $chain[$i['no_item']] : array(); $open = $i['status'] === 'W'; ?>
			<tr data-row="<?= (int) $i['id'] ?>">
				<td><?= $ix + 1 ?></td>
				<td class="no"><?= $e($i['no_item']) ?><?php if ($i['ref'] !== '') : ?><span class="ref" style="display:block">Revisi dari <?= $e($i['ref']) ?></span><?php endif; ?></td>
				<td style="min-width:170px"><?= $ro($i['desc']) ?>
					<?php if ($ch) : ?><span class="sub note">Ditolak sebelumnya: <?= implode('; ', array_map(function ($c) use ($e) { return $e($c['no']) . ': ' . $e($c['note']); }, $ch)) ?></span><?php endif; ?></td>
				<td style="min-width:110px"><?= $ro(periodik_schedule_text($i['m_tipe'], $i['tanggal'])) ?></td>
				<td class="num"><?= $ro($fmt($i['budget']), 'r') ?></td>
				<td class="num"><?= $ro($fmt($i['nominal']), 'r') ?></td>
				<td style="min-width:140px"><?= $ro($i['ket'], 'tall') ?></td>
				<td style="white-space:nowrap"><?php if ($i['doc_file'] !== '') : ?>
					<a class="btn bl sm" download style="text-decoration:none;display:inline-block" href="<?= $e(periodik_doc_url($i['doc_file'], true)) ?>" title="<?= $e($i['doc_file']) ?>"><i class="fa fa-file-o" aria-hidden="true"></i> Download</a>
				<?php else : ?><span class="sub">Tidak ada file</span><?php endif; ?></td>
				<td style="min-width:150px"><?= $ro($i['bank_id']) ?><?= $ro($i['accnumber']) ?><?= $ro($i['accname']) ?></td>
			</tr>
			<tr class="decrow" data-dec-row="<?= (int) $i['id'] ?>">
				<td></td>
				<td colspan="8">
				<?php if ($open) : ?>
					<div class="decbox"><span class="declbl">Keputusan</span>
						<div class="seg" role="group" aria-label="Keputusan <?= $e($i['no_item']) ?>">
							<button type="button" class="on A" data-dec="<?= (int) $i['id'] ?>" data-v="A" aria-pressed="true">Approve</button>
							<button type="button" data-dec="<?= (int) $i['id'] ?>" data-v="R" aria-pressed="false">Reject</button>
						</div>
						<div class="decnote" data-notebox="<?= (int) $i['id'] ?>" hidden>
							<textarea rows="2" data-note="<?= (int) $i['id'] ?>" placeholder="Wajib diisi"></textarea>
							<div class="err" hidden>Isi alasan untuk item ini, atau isi alasan reject umum.</div>
						</div>
					</div>
				<?php else : ?>
					<div class="decbox"><span class="declbl">Keputusan</span><span class="pill <?= $e($i['status']) ?>"><?= $i['status'] === 'A' ? 'Approved' : 'Rejected' ?></span></div>
				<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		<tr class="totrow"><td colspan="5"><b>TOTAL</b></td><td class="num mono"><b><?= $fmt(array_sum(array_column($batch['items'], 'nominal'))) ?></b></td><td colspan="3"></td></tr>
		</tbody>
	</table></div>
	<div class="genwrap" id="genWrap" hidden>
		<label for="genNote"><b>Alasan reject umum</b></label><span class="sub">Satu alasan untuk semua item yang di-reject. Di mode per item, dipakai bila kolom alasan item kosong.</span>
		<textarea id="genNote" rows="4"></textarea>
		<div class="err" id="genErr" hidden>Isi alasan reject umum.</div>
	</div>
	<div class="actbar">
		<div class="dsum">
			<div><span class="sub">Disetujui</span><b id="sumA">0 item</b> <span class="mono" id="sumAv"></span></div>
			<div><span class="sub">Ditolak</span><b id="sumR">0 item</b> <span class="mono" id="sumRv"></span></div>
			<div><span class="sub">Status batch</span><span class="pill" id="sumStat"></span></div>
		</div>
		<span class="sp"></span>
		<button type="button" class="btn g" id="allA">Approve semua</button>
		<button type="button" class="btn g" id="allR">Reject semua</button>
		<button type="button" class="btn o" id="pjCancel">Batal</button>
		<button type="button" class="btn" id="pjSave">Simpan keputusan</button>
	</div>
</div>
</div>

<script src="<?= base_url('assets/js/periodik_ui.js') ?>"></script>
<script>
(function () {
	var batchId = <?= (int) $batch['id'] ?>, batchNo = <?= json_encode($batch['no'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
	var items = <?= json_encode(array_values(array_map(function ($i) { return array('no' => (int) $i['id'], 'nominal' => (float) $i['nominal'], 'open' => $i['status'] === 'W', 'status' => $i['status']); }, $batch['items'])), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
	var dec = {}, tried = false, saving = false, bulk = false, root = document.querySelector('.pjx');
	items.forEach(function (i) { if (i.open) dec[i.no] = 'A'; });
	var listUrl = siteurl + 'pengajuan_rutin/app_list';

	function gen() { return document.getElementById('genNote').value.trim(); }
	function note(no) { return root.querySelector('[data-note="' + no + '"]').value.trim(); }
	function dirty() {
		return !saving && (Object.keys(dec).some(function (k) { return dec[k] === 'R' || note(k) !== ''; }) || gen() !== '');
	}
	PJ.guard(dirty);

	function render() {
		var a = 0, r = 0, va = 0, vr = 0, g = gen();
		items.forEach(function (i) {
			var d = i.open ? dec[i.no] : i.status;
			if (d === 'A') { a++; va += i.nominal; } else { r++; vr += i.nominal; }
			if (!i.open) return;
			root.querySelectorAll('[data-dec="' + i.no + '"]').forEach(function (b) { var on = b.dataset.v === d; b.className = on ? 'on ' + d : ''; b.setAttribute('aria-pressed', on); });
			var box = root.querySelector('[data-notebox="' + i.no + '"]'), ta = box.querySelector('textarea'), bad = tried && !bulk && d === 'R' && !ta.value.trim() && !g;
			box.hidden = d !== 'R' || bulk; ta.placeholder = 'Opsional bila alasan reject umum diisi'; box.querySelector('.err').hidden = !bad;
			root.querySelectorAll('[data-row="' + i.no + '"],[data-dec-row="' + i.no + '"]').forEach(function (tr) { tr.classList.toggle('rej', d === 'R'); });
		});
		var anyRej = items.some(function (i) { return i.open ? dec[i.no] === 'R' : false; });
		document.getElementById('genWrap').hidden = !anyRej;
		document.getElementById('genErr').hidden = !(tried && bulk && anyRej && !g);
		document.getElementById('sumA').textContent = a + ' item'; document.getElementById('sumAv').textContent = '(' + PJ.fmt(va) + ')';
		document.getElementById('sumR').textContent = r + ' item'; document.getElementById('sumRv').textContent = '(' + PJ.fmt(vr) + ')';
		var st = r === 0 ? ['A', 'Approved'] : a === 0 ? ['R', 'Rejected'] : ['P', 'Partially Approved'], el = document.getElementById('sumStat');
		el.className = 'pill ' + st[0]; el.textContent = st[1];
		document.getElementById('allA').disabled = !items.some(function (i) { return i.open && dec[i.no] === 'R'; });
		document.getElementById('allR').disabled = !items.some(function (i) { return i.open && dec[i.no] === 'A'; });
	}
	root.addEventListener('click', function (e) {
		var b = e.target.closest('[data-dec]'); if (b) { dec[b.dataset.dec] = b.dataset.v; bulk = false; render(); }
	});
	root.addEventListener('input', function (e) { if (e.target.matches('[data-note],#genNote')) render(); });
	document.getElementById('allA').onclick = function () { Object.keys(dec).forEach(function (k) { dec[k] = 'A'; }); bulk = false; render(); };
	document.getElementById('allR').onclick = function () { Object.keys(dec).forEach(function (k) { dec[k] = 'R'; }); bulk = true; render();
			var gn = document.getElementById('genNote'); gn.focus(); if (gn.scrollIntoView) gn.scrollIntoView({block: 'center'});
		};
	document.getElementById('pjBack').onclick = document.getElementById('pjCancel').onclick = function () { PJ.leave(listUrl); };

	document.getElementById('pjSave').onclick = function () {
		tried = true;
		var miss = items.filter(function (i) { return i.open && dec[i.no] === 'R' && !gen() && (bulk || !note(i.no)); });
		if (miss.length) {
			render(); var el = bulk ? document.getElementById('genNote') : root.querySelector('[data-note="' + miss[0].no + '"]'); if (el) { el.focus(); if (el.scrollIntoView) el.scrollIntoView({block: 'center'}); }
			PJ.toast(bulk ? 'Isi alasan reject umum.' : 'Ada item yang di-reject tanpa alasan. Isi alasan per item atau alasan reject umum.'); return;
		}
		var nA = items.filter(function (i) { return i.open && dec[i.no] === 'A'; }), nR = items.filter(function (i) { return i.open && dec[i.no] === 'R'; });
		PJ.ask({title: 'Simpan keputusan ' + batchNo + '?', ok: 'Simpan keputusan',
			body: '<p><b>' + nA.length + ' item disetujui</b>' + (nA.length ? ' (' + PJ.fmt(nA.reduce(function (s, i) { return s + i.nominal; }, 0)) + '), langsung masuk Request Payment' : '') +
				'.<br><b>' + nR.length + ' item ditolak</b>' + (nR.length ? ', kembali ke form pengajuan dengan alasan reject' : '') + '.</p><p class="hint">Keputusan tidak bisa diubah setelah disimpan.</p>',
			onOk: commit});
	};
	function commit() {
		var data = {id: batchId, general_note: gen()};
		items.forEach(function (i) { if (i.open) { data['decision[' + i.no + ']'] = dec[i.no]; data['note[' + i.no + ']'] = dec[i.no] === 'R' && !bulk ? note(i.no) : ''; } });
		PJ.post(siteurl + 'pengajuan_rutin/save_decision', data).then(function (r) {
			if (!r.save) { PJ.toast(r.msg || 'Terjadi kesalahan. Silakan coba lagi.'); return; }
			saving = true;
			try { sessionStorage.setItem('pjToast', r.no_doc + ' disimpan sebagai ' + r.status + '.' + (r.approved ? ' ' + r.approved + ' item masuk Request Payment.' : '')); } catch (x) {}
			location.href = listUrl;
		}, function (err) { PJ.toast(err.message); });
	}
	render();
})();
</script>
