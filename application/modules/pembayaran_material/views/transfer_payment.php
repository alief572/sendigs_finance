<?php
$amount = static function ($value) { return number_format((float) $value, 2, '.', ','); };
$rp_tutorial_page = 'form';
include dirname(__DIR__, 2) . '/request_payment/views/transfer_tutorial.php';
?>
<style>
	#rp-pay .rp-row { display:grid; grid-template-columns:minmax(120px,1fr) 14px minmax(0,1.8fr); gap:8px; align-items:start; margin-bottom:12px }
	#rp-pay .rp-row > label, #rp-pay .rp-row > .rp-label, #rp-pay .rp-row > .rp-colon { margin:0; padding-top:6px; font-weight:400 }
	#rp-pay .rp-box { background:#eee; border:1px solid #d2d6de; padding:6px 12px; min-height:34px; overflow-wrap:anywhere }
	#rp-pay .help-block { margin:4px 0 0; font-size:12px }
	#rp-pay .rp-blue > th, #rp-pay .rp-blue > td { background:#3c8dbc; color:#fff; text-align:center; border-color:#357ca5; vertical-align:middle }
	#rp-pay .rp-sum th { text-align:right; font-weight:400; vertical-align:middle }
	#rp-pay .rp-sum td { text-align:right; vertical-align:middle; min-width:150px }
	#rp-pay .rp-sum label { margin:0; font-weight:400 }
	#rp-pay .rp-sum .rp-hint { display:block; color:#6b7681; font-size:12px }
	#rp-pay .rp-drop { position:relative; max-width:805px; border:2px dashed #3c8dbc; border-radius:6px; background:#f9fbfd; text-align:center; padding:22px 12px }
	#rp-pay .rp-drop:focus-within { outline:3px solid #f39c12; outline-offset:2px }
	/* Input file asli menutupi kotak: klik, keyboard dan tarik-letakkan ditangani browser. */
	#rp-pay .rp-drop input[type=file] { position:absolute; top:0; right:0; bottom:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer }
	#rp-pay .rp-drop .fa-cloud-upload { font-size:36px; color:#3c8dbc }
	@media (max-width:600px) { #rp-pay .rp-row { grid-template-columns:1fr; gap:2px } #rp-pay .rp-colon { display:none } }
</style>
<form class="box box-primary" id="rp-pay" enctype="multipart/form-data">
	<div class="box-body">
		<div class="alert alert-info"><i class="fa fa-info-circle"></i> <b><?= (float) $transfer['admin'] ? 'Admin ditanggung ' . ($transfer['bearer'] === 'recipient' ? 'penerima' : 'perusahaan') . ' &ndash; Rp ' . $amount($transfer['admin']) : 'Tanpa admin charge' ?></b>
			<small>&middot; dari <?= html_escape($transfer['no_pengajuan']) ?> / Transfer <?= (int) $transfer['urutan'] ?></small></div>
		<div class="row">
			<div class="col-md-6">
				<div class="rp-row"><label for="rp-pay-date">Tgl Bayar</label><span class="rp-colon">:</span><input id="rp-pay-date" class="form-control input-sm" type="date" name="tgl_bayar" required value="<?= date('Y-m-d') ?>"></div>
				<div class="rp-row"><label for="rp-pay-note">Keterangan Pembayaran <span class="text-danger" id="rp-pay-note-mark" hidden>*</span></label><span class="rp-colon">:</span><textarea id="rp-pay-note" class="form-control input-sm" name="keterangan_pembayaran" rows="1" maxlength="2000"></textarea></div>
			</div>
			<div class="col-md-6">
				<div class="rp-row"><label for="rp-pay-supplier">Request By / Supplier</label><span class="rp-colon">:</span><input id="rp-pay-supplier" class="form-control input-sm" disabled value="<?= html_escape($transfer['nama']) ?>"></div>
				<div class="rp-row"><label for="rp-pay-bank">Pilih Bank</label><span class="rp-colon">:</span>
					<select class="form-control input-sm" id="rp-pay-bank" name="bank" required>
						<option value="">- Pilih Bank -</option>
						<?php foreach ($banks as $bank): ?>
							<option value="<?= html_escape($bank['id']) ?>"><?= html_escape($bank['nama_bank'] . ' - ' . $bank['rekening'] . ' a/n ' . $bank['nama']) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="rp-row"><span class="rp-label">Transfer ke</span><span class="rp-colon">:</span><div class="rp-box"><?= html_escape($transfer['bank'] . ' ' . $transfer['no_rek'] . ' a/n ' . $transfer['nama']) ?> &middot; <span id="rp-pay-received"></span></div></div>
				<?php if ((float) $transfer['admin']): ?>
					<div class="rp-row"><label for="rp-pay-mode">Admin aktual sesuai bukti bayar</label><span class="rp-colon">:</span>
						<select class="form-control input-sm" id="rp-pay-mode" name="actual_bearer">
							<option value="">Sesuai pengajuan (<?= $transfer['bearer'] === 'recipient' ? 'ditanggung penerima' : 'ditanggung perusahaan' ?>)</option>
							<option value="company">Ditanggung perusahaan</option>
							<option value="recipient">Ditanggung penerima</option>
							<option value="free">Gratis (bank tidak memotong admin)</option>
						</select>
					</div>
				<?php endif; ?>
				<div class="rp-row"><label for="rp-pay-bank-amount">Payment Bank</label><span class="rp-colon">:</span><div>
					<input class="form-control input-sm text-right" id="rp-pay-bank-amount" type="text" inputmode="decimal" autocomplete="off" data-money="payment_bank" required value="<?= $amount($planned['transfer'] / 100) ?>">
					<input type="hidden" name="payment_bank" value="<?= Rp_transfer::money($planned['transfer']) ?>">
					<p class="help-block">Terisi otomatis = nominal transfer ke penerima (baris TRF di jurnal). Ubah hanya kalau bukti bayar beda.</p></div></div>
			</div>
		</div>
		<?php
		$totalPpn = $totalPph = 0;
		foreach ($transfer['docs'] as $doc) {
			$totalPpn += (float) $doc['nilai_ppn'];
			$totalPph += $doc['flag_pph23'] || $doc['flag_pph21'] ? (float) $doc['nilai_pph'] : 0;
		}
		?>
		<div class="table-responsive"><table class="table table-bordered table-striped">
			<thead><tr class="rp-blue"><th>Request By / Supplier</th><th>Nomor Dokumen</th><th>DPP</th><th>PPN</th><th>PPH 23</th><th>PPH 21</th><th>DIBAYARKAN</th></tr></thead>
			<tbody>
				<?php foreach ($transfer['docs'] as $doc): ?>
					<tr><td><?= html_escape($transfer['nama']) ?><br><small class="text-muted">Request by: <?= html_escape($doc['request_by']) ?></small></td><td class="text-center"><?= html_escape($doc['no_dokumen']) ?></td>
						<td class="text-right"><?= $amount($doc['dpp']) ?></td><td class="text-right"><?= (float) $doc['nilai_ppn'] ? $amount($doc['nilai_ppn']) : '-' ?></td>
						<td class="text-right"><?= $doc['flag_pph23'] ? $amount($doc['nilai_pph']) : '-' ?></td>
						<td class="text-right"><?= $doc['flag_pph21'] ? $amount($doc['nilai_pph']) : '-' ?></td>
						<td class="text-right"><?= $amount($doc['dibayarkan']) ?></td></tr>
				<?php endforeach; ?>
				<tr><td colspan="7" class="text-muted"><small><i class="fa fa-lock"></i> Kolom dan angka sama dengan Request Payment (DPP + PPN &minus; PPh = DIBAYARKAN), terkunci dari pengajuan.</small></td></tr>
				<tr class="rp-sum"><th colspan="6">Total Payment</th><td><?= $amount($planned['net'] / 100) ?></td></tr>
				<tr class="rp-sum"><th colspan="6">Transfer ke penerima <small>(= Payment Bank)</small></th><td><span id="rp-pay-transfer"></span><div class="text-warning" id="rp-pay-transfer-diff"></div></td></tr>
				<tr class="rp-sum"><th colspan="6"><label for="rp-pay-charge">Bank Charge</label> <small>(biaya transfer yang dipotong bank)</small><span class="rp-hint" id="rp-pay-charge-hint"></span></th>
					<td><input class="form-control input-sm text-right" id="rp-pay-charge" type="text" inputmode="decimal" autocomplete="off" data-money="bank_charge" value="<?= $amount($transfer['admin']) ?>"><input type="hidden" name="bank_charge" value="<?= html_escape($transfer['admin']) ?>"></td></tr>
				<tr class="rp-sum"><th colspan="6">Total keluar bank <small>(transfer + bank charge)</small></th><td id="rp-pay-out"></td></tr>
				<tr class="rp-sum"><th colspan="6">Administrasi Bank <small id="rp-pay-admin-hint"></small></th><td id="rp-pay-admin"></td></tr>
				<tr class="rp-sum"><th colspan="6">PPh</th><td><?= $amount($totalPph) ?></td></tr>
				<tr class="rp-sum"><th colspan="6">PPn</th><td><?= $amount($totalPpn) ?></td></tr>
			</tbody>
		</table></div>
		<div class="text-right" role="status" aria-live="polite" id="rp-pay-notice" style="margin-bottom:12px"></div>
		<p style="margin-bottom:6px"><b>Upload Bukti Bayar</b> <span class="text-danger">*</span></p>
		<div class="rp-drop">
			<i class="fa fa-cloud-upload" aria-hidden="true"></i>
			<p style="margin:8px 0 4px"><b>Tarik &amp; letakkan file bukti bayar di sini, atau <span class="text-primary" style="text-decoration:underline">pilih file</span></b></p>
			<small class="text-muted">Mendukung JPG, JPEG, PNG, PDF, DOCX, XLSX (maks. 10 file, 5 MB per file)</small>
			<input type="file" id="rp-pay-proof" name="upload_doc[]" aria-label="Upload Bukti Bayar" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" multiple required>
		</div>
		<ul class="list-group" id="rp-pay-files" style="max-width:805px;margin:8px 0 0"></ul>
		<div class="text-danger" id="rp-pay-files-error" role="alert"></div>
		<h4 style="margin-top:20px">Jurnal</h4>
		<p>No Transaksi (No Payment): ditetapkan saat disimpan &middot; <b>1 nomor</b> untuk seluruh baris jurnal payment ini. Keterangan baris dokumen memakai <b>nomor dokumen</b>.</p>
		<div class="table-responsive"><table class="table table-bordered table-striped" id="rp-pay-journal">
			<thead><tr class="rp-blue"><th>Tanggal Jurnal</th><th>Nama Company</th><th>Department</th><th>COA</th><th>Nama Account</th><th>Keterangan</th><th>Debit</th><th>Kredit</th></tr></thead>
			<tbody></tbody>
			<tfoot><tr class="rp-blue"><th colspan="6">Balancing</th><th class="text-right" id="rp-pay-debit"></th><th class="text-right" id="rp-pay-credit"></th></tr></tfoot>
		</table></div>
		<p class="text-muted" style="font-size:12px">Dijurnal per dokumen walau transfernya digabung. Bank dikredit 2 baris per transfer: TRF (Payment Bank) dan BIAYA TRANSFER (Bank Charge). Baris bernilai 0 tidak diposting. Posting lewat Jurnal All.</p>
		<button class="btn btn-success btn-sm" type="submit" disabled><i class="fa fa-save"></i> Submit</button>
		<a href="<?= site_url('pembayaran_material/list_request_payment/2') ?>" class="btn btn-danger btn-sm"><i class="fa fa-reply"></i> Back</a>
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
	// Format sama dengan angka lain di halaman ini: 12,478,944.00. Yang dikirim ke server tanpa koma.
	const plain = value => {
		const parts = String(value).replace(/[^\d.]/g, '').split('.');
		const whole = parts[0].replace(/^0+(?=\d)/, '').slice(0, 13);
		return parts.length > 1 ? (whole || '0') + '.' + parts.slice(1).join('').slice(0, 2) : whole;
	};
	const grouped = value => {
		const parts = plain(value).split('.');
		parts[0] = parts[0].replace(/\B(?=(\d{3})+$)/g, ',');
		return parts.join('.');
	};
	// Tempel angka gaya Indonesia (12.478.944,00) jangan sampai terbaca 12.47.
	const fromPaste = text => /^(\d{1,3}(\.\d{3})+|\d+)(,\d{1,2})?$/.test(text) && /,|\.\d{3}/.test(text) ? text.replace(/\./g, '').replace(',', '.') : text;
	const show = (name, value) => {
		input[name].value = value;
		form.querySelector('[data-money="' + name + '"]').value = grouped(value);
	};
	form.querySelectorAll('[data-money]').forEach(field => {
		field.addEventListener('input', () => {
			// Kursor tetap setelah digit/titik ke-n.
			let left = field.value.slice(0, field.selectionEnd).replace(/[^\d.]/g, '').length;
			field.value = grouped(field.value);
			let caret = 0;
			while (left && caret < field.value.length) if (/[\d.]/.test(field.value[caret++])) left--;
			field.setSelectionRange(caret, caret);
			input[field.dataset.money].value = plain(field.value).replace(/\.$/, '');
		});
		field.addEventListener('paste', event => {
			event.preventDefault();
			field.setRangeText(fromPaste(event.clipboardData.getData('text').trim()), field.selectionStart, field.selectionEnd, 'end');
			field.dispatchEvent(new Event('input', {bubbles: true}));
		});
	});
	const mode = () => (input.actual_bearer && input.actual_bearer.value) || plan.bearer;
	const received = () => plan.net - (mode() === 'recipient' ? plan.admin : 0);
	const defaultCharge = () => mode() === 'free' ? 0 : plan.admin;
	const charge = () => input.bank_charge.value === '' ? defaultCharge() : cents(input.bank_charge.value);
	const bearerText = {company: 'ditanggung perusahaan', recipient: 'ditanggung penerima'};
	const message = (text, error) => {
		notice.textContent = ' ' + text;
		notice.className = 'text-right ' + (error ? 'text-danger' : 'text-success');
		const icon = document.createElement('i');
		icon.className = 'fa ' + (error ? 'fa-warning' : 'fa-check-circle');
		notice.prepend(icon);
	};
	const longDate = value => value ? new Date(value + 'T00:00:00').toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : '';
	function refresh() {
		// RP-R-36: Administrasi Bank = Payment Bank + Bank Charge - Total Payment (Dr beban perusahaan, Cr admin dipotong tapi bank tidak memotong).
		const token = ++sequence, bank = cents(input.payment_bank.value), out = bank + charge(), adminBank = out - plan.net;
		const transferDiff = bank - received();
		const needsNote = mode() !== plan.bearer || charge() !== plan.admin || bank !== plan.transfer;
		document.getElementById('rp-pay-received').textContent = 'Rp ' + (bank / 100).toLocaleString('id-ID', {maximumFractionDigits: 2});
		document.getElementById('rp-pay-transfer').textContent = fmt(bank);
		document.getElementById('rp-pay-transfer-diff').textContent = transferDiff ? 'beda Rp ' + fmt(transferDiff) + ' dari transfer seharusnya (Rp ' + fmt(received()) + ')' : '';
		document.getElementById('rp-pay-charge-hint').textContent = 'Default Rp ' + fmt(defaultCharge()) + ' = ' + (mode() === 'free'
			? 'gratis, bank tidak memotong biaya' : 'admin ' + (input.actual_bearer && input.actual_bearer.value ? 'aktual' : 'dari Request Payment') + ' (' + bearerText[mode()] + ')')
			+ '. Dijurnal Cr Bank terpisah dari transfer. Bisa diedit sesuai bukti bayar.';
		document.getElementById('rp-pay-out').textContent = fmt(out);
		document.getElementById('rp-pay-admin').textContent = fmt(adminBank);
		document.getElementById('rp-pay-admin-hint').textContent = '(otomatis: ' + (adminBank > 0 ? 'beban perusahaan, Dr' : adminBank < 0
			? 'admin dipotong dari penerima tapi bank tidak memotong, Cr' : (plan.admin && mode() === 'recipient' ? 'ditanggung penerima, ' : '') + 'tidak ada beban') + ')';
		input.keterangan_pembayaran.required = needsNote;
		document.getElementById('rp-pay-note-mark').hidden = !needsNote;
		submit.disabled = true;
		clearTimeout(timer);
		if (!(bank > 0) || charge() < 0 || !input.tgl_bayar.value) {
			message(!(bank > 0) ? 'Payment Bank harus lebih dari 0.' : charge() < 0 ? 'Bank Charge tidak boleh minus.' : 'Isi tanggal bayar.', true);
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
						const money = key === 'debit' || key === 'kredit';
						cell.textContent = money ? fmt(cents(row[key])) : key === 'tgl_jurnal' ? longDate(row[key])
							: key === 'nm_coa' && row.coa === '(pilih bank)' ? 'Bank' : (row[key] || '');
						if (money) cell.className = 'text-right';
					});
					return [sum[0] + cents(row.debit), sum[1] + cents(row.kredit)];
				}, [0, 0]);
				document.getElementById('rp-pay-debit').textContent = fmt(totals[0]);
				document.getElementById('rp-pay-credit').textContent = fmt(totals[1]);
				const missing = [];
				if (!input.bank.value) missing.push('Pilih Bank.');
				if (needsNote && !input.keterangan_pembayaran.value.trim()) missing.push('Keterangan Pembayaran wajib diisi karena ada selisih.');
				if (!document.getElementById('rp-pay-proof').files.length) missing.push('Bukti bayar belum diupload.');
				if (totals[0] !== totals[1]) missing.push('Jurnal tidak balance.');
				if (missing.length) return message(missing.join(' '), true);
				message('Siap disubmit.', false);
				submit.disabled = false;
			}, 'json').fail(() => { if (token === sequence) message('Preview jurnal gagal dimuat.', true); });
		}, 250);
	}
	if (input.actual_bearer) {
		input.actual_bearer.addEventListener('change', () => {
			show('bank_charge', (defaultCharge() / 100).toFixed(2));
			show('payment_bank', (received() / 100).toFixed(2));
		});
	}
	// Pilih/tarik berkali-kali menambah daftar; tiap file bisa dihapus. Batas sama dengan server.
	const proof = document.getElementById('rp-pay-proof');
	let chosen = [];
	const fileKey = file => [file.name, file.size, file.lastModified].join('|');
	function addFiles(current, incoming) {
		const files = current.slice(), rejected = [];
		Array.from(incoming).forEach(file => {
			if (files.some(other => fileKey(other) === fileKey(file))) return;
			if (!/\.(pdf|jpe?g|png|docx|xlsx)$/i.test(file.name)) return rejected.push(file.name + ': tipe file tidak didukung');
			if (file.size < 1 || file.size > 5242880) return rejected.push(file.name + ': ukuran harus 1 byte sampai 5 MB');
			if (files.length >= 10) return rejected.push(file.name + ': maksimal 10 file');
			files.push(file);
		});
		return {files, rejected};
	}
	function syncFiles() {
		const transfer = new DataTransfer();
		chosen.forEach(file => transfer.items.add(file));
		proof.files = transfer.files;
		const list = document.getElementById('rp-pay-files');
		list.textContent = '';
		chosen.forEach((file, index) => {
			const item = document.createElement('li');
			item.className = 'list-group-item clearfix';
			item.textContent = file.name;
			const remove = document.createElement('button');
			remove.type = 'button';
			remove.className = 'btn btn-danger btn-xs pull-right';
			remove.textContent = 'Hapus';
			remove.setAttribute('aria-label', 'Hapus ' + file.name);
			remove.onclick = () => {
				chosen.splice(index, 1);
				syncFiles();
				refresh();
			};
			item.appendChild(remove);
			list.appendChild(item);
		});
	}
	proof.addEventListener('change', () => {
		const result = addFiles(chosen, proof.files);
		chosen = result.files;
		document.getElementById('rp-pay-files-error').textContent = result.rejected.join('. ');
		syncFiles();
	});
	form.addEventListener('input', refresh);
	form.addEventListener('change', refresh);
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
