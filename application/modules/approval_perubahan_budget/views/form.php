<?php
/* Form keputusan perubahan budget per baris (bApprDetail di specs/input/Periodik.html). Variabel: $bg, $dept_name. */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
require_once __DIR__ . '/_line_helpers.php';
?>
<link rel="stylesheet" href="<?= base_url('assets/css/periodik_ui.css') ?>">
<div class="pjx">
<button type="button" class="back" id="pjBack">&#8592; Kembali ke daftar approval</button>
<h1 class="page-title">Approval Perubahan Budget <small><?= $e($bg['no']) ?></small></h1>
<div class="panel hl">
	<div class="grid2 apphead">
		<div><span>Departement</span><select disabled><option><?= $e($dept_name) ?></option></select></div>
		<div><span>No. Perubahan</span><div class="ro"><?= $e($bg['no']) ?></div></div>
		<div><span>Diajukan</span><div class="ro"><?= $e(substr((string) $bg['at'], 0, 16)) ?> · <?= $e($bg['by']) ?></div></div>
	</div>
	<div class="scroll"><table class="apptbl">
		<thead><tr><th>#</th><th>Item</th><th>Jenis perubahan</th><th>Sebelum</th><th>Sesudah</th></tr></thead>
		<tbody>
		<?php foreach ($bg['lines'] as $ix => $l) : $k = Budget_logic::KINDS[$l['kind']]; $lid = (int) $l['id']; ?>
			<tr data-row="<?= $lid ?>">
				<td><?= $ix + 1 ?></td>
				<td><?= $e($l['kind'] === 'new' ? $l['after']['nama'] : $l['before']['nama']) ?><span class="sub"><?= $e($l['after']['coa']) ?></span></td>
				<td><span class="pill <?= $e($k[0]) ?>"><?= $e($k[1]) ?></span></td>
				<td><?= apb_line_html($l, 'before') ?></td>
				<td><?= apb_line_html($l, 'after') ?></td>
			</tr>
			<tr class="decrow" data-dec-row="<?= $lid ?>">
				<td></td>
				<td colspan="4">
					<div class="decbox"><span class="declbl">Keputusan</span>
						<div class="seg" role="group" aria-label="Keputusan perubahan <?= $ix + 1 ?>">
							<button type="button" class="on A" data-dec="<?= $lid ?>" data-v="A" aria-pressed="true">Approve</button>
							<button type="button" data-dec="<?= $lid ?>" data-v="R" aria-pressed="false">Reject</button>
						</div>
						<div class="decnote" data-notebox="<?= $lid ?>" hidden>
							<textarea rows="2" data-note="<?= $lid ?>" placeholder="Wajib diisi"></textarea>
							<div class="err" hidden>Isi alasan reject untuk perubahan ini.</div>
						</div>
					</div>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table></div>
	<div class="actbar">
		<div class="dsum">
			<div><span class="sub">Disetujui</span><b id="sumA">0 perubahan</b></div>
			<div><span class="sub">Ditolak</span><b id="sumR">0 perubahan</b></div>
		</div>
		<span class="sp"></span>
		<span class="hint" style="margin:0">Perubahan yang disetujui langsung berlaku di master dan form pengajuan.</span>
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
	var bgNo = <?= json_encode($bg['no'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
	var ids = <?= json_encode(array_map(function ($l) { return (int) $l['id']; }, $bg['lines'])) ?>;
	var dec = {}, tried = false, saving = false, root = document.querySelector('.pjx');
	ids.forEach(function (id) { dec[id] = 'A'; });
	var listUrl = siteurl + 'approval_perubahan_budget';
	function note(id) { return root.querySelector('[data-note="' + id + '"]').value.trim(); }
	PJ.guard(function () { return !saving && ids.some(function (id) { return dec[id] === 'R' || note(id) !== ''; }); });

	function render() {
		var a = 0, r = 0;
		ids.forEach(function (id) {
			var d = dec[id]; if (d === 'A') a++; else r++;
			root.querySelectorAll('[data-dec="' + id + '"]').forEach(function (b) { var on = b.dataset.v === d; b.className = on ? 'on ' + d : ''; b.setAttribute('aria-pressed', on); });
			var box = root.querySelector('[data-notebox="' + id + '"]'), ta = box.querySelector('textarea');
			box.hidden = d !== 'R'; box.querySelector('.err').hidden = !(tried && d === 'R' && !ta.value.trim());
			root.querySelectorAll('[data-row="' + id + '"],[data-dec-row="' + id + '"]').forEach(function (tr) { tr.classList.toggle('rej', d === 'R'); });
		});
		document.getElementById('sumA').textContent = a + ' perubahan'; document.getElementById('sumR').textContent = r + ' perubahan';
		document.getElementById('allA').disabled = r === 0; document.getElementById('allR').disabled = a === 0;
	}
	root.addEventListener('click', function (e) { var b = e.target.closest('[data-dec]'); if (b) { dec[b.dataset.dec] = b.dataset.v; render(); } });
	root.addEventListener('input', function (e) { if (e.target.matches('[data-note]')) render(); });
	document.getElementById('allA').onclick = function () { ids.forEach(function (id) { dec[id] = 'A'; }); render(); };
	document.getElementById('allR').onclick = function () { ids.forEach(function (id) { dec[id] = 'R'; }); render(); };
	document.getElementById('pjBack').onclick = document.getElementById('pjCancel').onclick = function () { PJ.leave(listUrl); };

	document.getElementById('pjSave').onclick = function () {
		tried = true;
		var miss = ids.filter(function (id) { return dec[id] === 'R' && !note(id); });
		if (miss.length) {
			render(); var el = root.querySelector('[data-note="' + miss[0] + '"]'); if (el) { el.focus(); if (el.scrollIntoView) el.scrollIntoView({block: 'center'}); }
			PJ.toast('Ada perubahan yang di-reject tanpa alasan.'); return;
		}
		var a = ids.filter(function (id) { return dec[id] === 'A'; }).length, r = ids.length - a;
		PJ.ask({title: 'Simpan keputusan ' + bgNo + '?', ok: 'Simpan keputusan',
			body: '<p><b>' + a + ' perubahan disetujui</b>' + (a ? ', langsung berlaku di master budget' : '') + '.<br><b>' + r + ' perubahan ditolak</b>.</p><p class="hint">Keputusan tidak bisa diubah setelah disimpan.</p>',
			onOk: function () {
				var data = {no_bg: bgNo};
				ids.forEach(function (id) { data['decision[' + id + ']'] = dec[id]; data['note[' + id + ']'] = dec[id] === 'R' ? note(id) : ''; });
				PJ.post(siteurl + 'approval_perubahan_budget/save_decision', data).then(function (res) {
					if (!res.save) { PJ.toast(res.msg || 'Terjadi kesalahan. Silakan coba lagi.'); return; }
					saving = true;
					try { sessionStorage.setItem('pjToast', bgNo + ' diputuskan: ' + res.approved + ' disetujui, ' + res.rejected + ' ditolak. Perubahan yang disetujui sudah berlaku.'); } catch (x) {}
					location.href = listUrl;
				}, function (err) { PJ.toast(err.message); });
			}});
	};
	render();
})();
</script>
