<form class="box box-primary" method="post" action="<?= site_url('pembayaran_material/transfer_payment/0') ?>" autocomplete="off">
	<div class="box-body">
		<p>Dokumen ini diajukan sebelum alur transfer, jadi rekening tujuannya diisi di sini. Belum ada yang disimpan sampai Anda menekan <b>Submit</b> di form berikutnya.</p>
		<table class="table table-bordered" style="max-width:900px">
			<thead><tr><th>No. Dokumen</th><th>Keperluan</th><th class="text-right">Total Invoice</th></tr></thead>
			<tbody>
				<?php foreach ($documents as $doc): ?>
					<tr><td><?= html_escape($doc['no_doc']) ?></td><td><?= html_escape($doc['keperluan']) ?></td><td class="text-right"><?= number_format((float) $doc['jumlah'], 2, '.', ',') ?></td></tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<input type="hidden" name="draft[ids]" value="<?= html_escape($ids) ?>">
		<?php if (!empty($locked)): ?>
			<div class="alert alert-warning" style="max-width:900px">Admin Rp <?= number_format((float) $carried, 0, ',', '.') ?> sudah dipotong di pengajuan lama, jadi dikunci sebagai <b>ditanggung penerima</b>.</div>
		<?php endif; ?>
		<div class="row" style="max-width:900px">
			<div class="col-sm-4 form-group"><label for="draft-bank">Bank tujuan</label><input class="form-control" id="draft-bank" name="draft[bank]" maxlength="100" required></div>
			<div class="col-sm-4 form-group"><label for="draft-rek">No. Rekening</label><input class="form-control" id="draft-rek" name="draft[no_rek]" maxlength="80" pattern="[0-9 .\-]+" inputmode="numeric" required></div>
			<div class="col-sm-4 form-group"><label for="draft-nama">Nama Penerima</label><input class="form-control" id="draft-nama" name="draft[nama]" maxlength="160" required></div>
			<div class="col-sm-4 form-group"><label for="draft-admin">Biaya admin (Rp)</label><input class="form-control" id="draft-admin" name="draft[admin]" type="number" min="0" step="0.01" value="<?= !empty($locked) ? html_escape($carried) : '0' ?>"<?= !empty($locked) ? ' readonly' : '' ?> required></div>
			<div class="col-sm-4 form-group"><label for="draft-bearer">Admin ditanggung</label>
				<select class="form-control" id="draft-bearer" name="draft[bearer]"><?php if (empty($locked)): ?><option value="company">Perusahaan</option><?php endif; ?><option value="recipient">Penerima</option></select></div>
		</div>
		<button class="btn btn-success btn-sm" type="submit"><i class="fa fa-arrow-right"></i> Lanjut</button>
		<a class="btn btn-default btn-sm" href="<?= site_url('pembayaran_material/form_payment_new') . '/?id_payment=' . rawurlencode($ids) . '&admin_charge_bearer=company&jenis_payment=2' ?>">Bayar lewat form lama</a>
		<a class="btn btn-danger btn-sm" href="<?= site_url('pembayaran_material/list_request_payment/2') ?>"><i class="fa fa-reply"></i> Back</a>
	</div>
</form>
