<?php
$amount = static function ($value) { return number_format((float) $value, 2, '.', ','); };
$rp_tutorial_page = 'form';
include dirname(__DIR__, 2) . '/request_payment/views/transfer_tutorial.php';
?>
<form class="box box-primary" id="rp-pay" enctype="multipart/form-data">
	<div class="box-header"><h3 class="box-title">Form Payment</h3></div>
	<div class="box-body">
		<div class="alert alert-info"><b><?= (float) $transfer['admin'] ? 'Admin ditanggung ' . ($transfer['bearer'] === 'recipient' ? 'penerima' : 'perusahaan') . ' &ndash; Rp ' . $amount($transfer['admin']) : 'Tanpa admin charge' ?></b>
			&middot; dari <?= html_escape($transfer['no_pengajuan']) ?> / Transfer <?= (int) $transfer['urutan'] ?></div>
		<div class="row">
			<div class="col-sm-6">
				<div class="form-group"><label for="rp-pay-date">Tgl Bayar</label><input id="rp-pay-date" class="form-control input-sm" type="date" name="tgl_bayar" required value="<?= date('Y-m-d') ?>"></div>
				<div class="form-group"><label for="rp-pay-note">Keterangan Pembayaran</label><textarea id="rp-pay-note" class="form-control input-sm" name="keterangan_pembayaran" rows="2" maxlength="2000"></textarea></div>
				<div class="form-group"><label for="rp-pay-bank-amount">Payment Bank</label><input class="form-control input-sm text-right" id="rp-pay-bank-amount" name="payment_bank" type="number" min="0.01" step="0.01" required value="<?= Rp_transfer::money($planned['bank']) ?>"></div>
				<div class="form-group"><label for="rp-pay-proof">Bukti Bayar</label><input type="file" id="rp-pay-proof" name="upload_doc[]" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" multiple required></div>
			</div>
			<div class="col-sm-6">
				<div class="form-group"><label for="rp-pay-supplier">Request By / Supplier</label><input id="rp-pay-supplier" class="form-control input-sm" readonly value="<?= html_escape($transfer['nama']) ?>"></div>
				<div class="form-group"><label for="rp-pay-bank">Pilih Bank</label>
					<select class="form-control input-sm" id="rp-pay-bank" name="bank" required>
						<option value="">- Pilih Bank -</option>
						<?php foreach ($banks as $bank): ?>
							<option value="<?= html_escape($bank['id']) ?>"><?= html_escape($bank['nama_bank'] . ' - ' . $bank['rekening'] . ' a/n ' . $bank['nama']) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="form-group"><b>Transfer ke</b><p><?= html_escape($transfer['bank'] . ' ' . $transfer['no_rek'] . ' a/n ' . $transfer['nama']) ?> &middot; <span id="rp-pay-received"></span></p></div>
				<?php if ((float) $transfer['admin']): ?>
					<div class="form-group"><label for="rp-pay-mode">Admin aktual sesuai bukti bayar</label>
						<select class="form-control input-sm" id="rp-pay-mode" name="actual_bearer">
							<option value="">Sesuai pengajuan (<?= $transfer['bearer'] === 'recipient' ? 'ditanggung penerima' : 'ditanggung perusahaan' ?>)</option>
							<option value="company">Ditanggung perusahaan</option>
							<option value="recipient">Ditanggung penerima</option>
							<option value="free">Gratis (bank tidak memotong admin)</option>
						</select>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<div class="table-responsive"><table class="table table-bordered">
			<thead><tr class="bg-blue"><th>Request By / Supplier</th><th>Nomor Dokumen</th><th>DPP</th><th>PPN</th><th>PPH 23</th><th>PPH 21</th><th>DIBAYARKAN</th></tr></thead>
			<tbody>
				<?php foreach ($transfer['docs'] as $doc): ?>
					<tr><td><?= html_escape($doc['request_by']) ?></td><td><?= html_escape($doc['no_dokumen']) ?></td>
						<td class="text-right"><?= $amount($doc['dpp']) ?></td><td class="text-right"><?= (float) $doc['nilai_ppn'] ? $amount($doc['nilai_ppn']) : '-' ?></td>
						<td class="text-right"><?= $doc['flag_pph23'] ? $amount($doc['nilai_pph']) : '-' ?></td>
						<td class="text-right"><?= $doc['flag_pph21'] ? $amount($doc['nilai_pph']) : '-' ?></td>
						<td class="text-right"><?= $amount($doc['dibayarkan']) ?></td></tr>
				<?php endforeach; ?>
				<tr><td colspan="7" class="text-muted"><small><i class="fa fa-lock"></i> DPP + PPN − PPh = DIBAYARKAN. Nilai terkunci dari pengajuan.</small></td></tr>
				<tr><th colspan="6" class="text-right">Total Payment</th><th class="text-right"><?= $amount($planned['net'] / 100) ?></th></tr>
				<tr><th colspan="6" class="text-right"><label for="rp-pay-charge">Bank Charge</label><div class="text-muted"><small>Default dari pengajuan, bisa diedit sesuai bukti bayar.</small> <button class="btn btn-link btn-xs" type="button" id="rp-pay-autofill">Isi otomatis</button></div></th>
					<td><input class="form-control input-sm text-right" id="rp-pay-charge" name="bank_charge" type="number" step="0.01" value="<?= html_escape($transfer['admin']) ?>"></td></tr>
				<tr><th colspan="6" class="text-right">Kontrol</th><th class="text-right" id="rp-pay-control"></th></tr>
			</tbody>
		</table></div>
		<h3>Jurnal</h3>
		<p class="text-muted">Satu nomor BK untuk seluruh baris jurnal. Nomor ditetapkan saat pembayaran disimpan.</p>
		<div class="table-responsive"><table class="table table-bordered" id="rp-pay-journal">
			<thead><tr class="bg-blue"><th>Tanggal</th><th>Company</th><th>Departemen</th><th>No. COA</th><th>Nama COA</th><th>Keterangan</th><th>Debit</th><th>Kredit</th></tr></thead>
			<tbody></tbody><tfoot><tr><th colspan="6" class="text-right">Total</th><th class="text-right" id="rp-pay-debit"></th><th class="text-right" id="rp-pay-credit"></th></tr></tfoot>
		</table></div>
		<div role="status" aria-live="polite" id="rp-pay-notice"></div>
	</div>
	<div class="box-footer">
		<a href="<?= site_url('pembayaran_material/list_request_payment/2') ?>" class="btn btn-default">Kembali</a>
		<button class="btn btn-success pull-right" type="submit" disabled><i class="fa fa-save"></i> Submit</button>
	</div>
</form>
<script>
(() => {
	const form = document.getElementById('rp-pay');
	const input = form.elements;
	const id = <?= (int) $transfer['id'] ?>;
	const plan = <?= json_encode($planned + ['admin' => Rp_transfer::cents($transfer['admin']), 'bearer' => $transfer['bearer']]) ?>;
	const end = name => <?= json_encode(site_url('pembayaran_material')) ?> + '/' + name + '/' + id;
	const submit = form.querySelector('[type="submit"]');
	const notice = document.getElementById('rp-pay-notice');
	const cents = value => Math.round(Number(value) * 100);
	const fmt = value => (value / 100).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
	let sequence = 0, timer, saving = false;
	const mode = () => (input.actual_bearer && input.actual_bearer.value) || plan.bearer;
	const received = () => plan.net - (mode() === 'recipient' ? plan.admin : 0);
	const charge = () => input.bank_charge.value === '' ? (mode() === 'free' ? 0 : plan.admin) : cents(input.bank_charge.value);
	const message = (text, error) => { notice.textContent = text; notice.className = error ? 'text-danger' : 'text-success'; };
	function refresh() {
		const token = ++sequence, control = cents(input.payment_bank.value) - received() - charge();
		const needsNote = mode() !== plan.bearer || charge() !== plan.admin;
		document.getElementById('rp-pay-control').textContent = fmt(control);
		document.getElementById('rp-pay-received').textContent = 'Rp ' + fmt(received());
		input.keterangan_pembayaran.required = needsNote;
		submit.disabled = true;
		clearTimeout(timer);
		if (control !== 0 || !input.tgl_bayar.value) {
			message(control !== 0 ? 'Kontrol belum 0.' : 'Isi tanggal bayar.', true);
			return;
		}
		timer = setTimeout(() => {
			const preview = $(form).serializeArray().filter(field => field.name !== 'keterangan_pembayaran');
			$.post(end('preview_transfer_payment'), preview, response => {
				if (token !== sequence || saving) return;
				if (Number(response.status) !== 1) return message(response.pesan, true);
				const body = document.querySelector('#rp-pay-journal tbody');
				body.textContent = '';
				const totals = response.rows.reduce((sum, row) => {
					const tr = body.insertRow();
					['tgl_jurnal','nm_company','nm_divisi','coa','nm_coa','keterangan','debit','kredit'].forEach(key => {
						const cell = tr.insertCell();
						cell.textContent = key === 'debit' || key === 'kredit' ? fmt(cents(row[key])) : (row[key] || '');
					});
					return [sum[0] + cents(row.debit), sum[1] + cents(row.kredit)];
				}, [0, 0]);
				document.getElementById('rp-pay-debit').textContent = fmt(totals[0]);
				document.getElementById('rp-pay-credit').textContent = fmt(totals[1]);
				if (!input.bank.value) return message('Pilih Bank.', true);
				if (needsNote && !input.keterangan_pembayaran.value.trim()) return message('Keterangan Pembayaran wajib diisi karena ada selisih.', true);
				if (!document.getElementById('rp-pay-proof').files.length) return message('Bukti bayar belum diunggah.', true);
				if (totals[0] !== totals[1]) return message('Jurnal tidak balance.', true);
				message('Siap disubmit.', false);
				submit.disabled = false;
			}, 'json').fail(() => { if (token === sequence) message('Preview jurnal gagal dimuat.', true); });
		}, 250);
	}
	if (input.actual_bearer) {
		input.actual_bearer.addEventListener('change', () => {
			input.bank_charge.value = ((mode() === 'free' ? 0 : plan.admin) / 100).toFixed(2);
			input.payment_bank.value = ((received() + charge()) / 100).toFixed(2);
		});
	}
	form.addEventListener('input', refresh);
	form.addEventListener('change', refresh);
	document.getElementById('rp-pay-autofill').onclick = () => {
		input.bank_charge.value = ((cents(input.payment_bank.value) - received()) / 100).toFixed(2);
		refresh();
	};
	form.onsubmit = event => {
		event.preventDefault();
		if (saving || submit.disabled || !confirm('Submit payment dan simpan jurnal untuk posting?')) return;
		submit.disabled = saving = true;
		$.ajax({url: end('save_transfer_payment'), method: 'POST', data: new FormData(form), contentType: false, processData: false, dataType: 'json'})
			.done(response => {
				if (Number(response.status) === 1) {
					alert(response.pesan);
					window.location.href = <?= json_encode(site_url('pembayaran_material/payment_list')) ?>;
					return;
				}
				message(response.pesan, true);
				submit.disabled = saving = false;
			}).fail(() => { message('Pembayaran gagal dikirim. Periksa riwayat sebelum mencoba lagi.', true); submit.disabled = saving = false; });
	};
	refresh();
})();
</script>
