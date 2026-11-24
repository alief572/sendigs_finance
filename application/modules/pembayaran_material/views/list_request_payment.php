<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <div class="box-header">
        <a href="<?= base_url('pembayaran_material/payment_list') ?>" class="btn btn-sm btn-danger"><i class="fa fa-arrow-left"></i> Back</a>
        <button type="button" class="btn btn-sm btn-warning clear_choosed_payment"><i class="fa fa-refresh"></i> Clear Checked Payment</button>
        <button type="button" class="btn btn-sm btn-success proses_payment"><i class="fa fa-check"></i> Proses</button>
    </div>
    <div class="box-body">
        <table class="table table-bordered" id="table_list_req_payment" data-jenis-payment="<?= htmlspecialchars((string) $jenis_payment, ENT_QUOTES, 'UTF-8') ?>">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th class="text-center">No. Dokumen</th>
                    <th class="text-center">Tgl</th>
                    <th class="text-center">Company</th>
                    <th class="text-center">Keperluan</th>
                    <th class="text-center">Currency</th>
                    <th class="text-center">Total Invoice</th>
                    <th class="text-center">Requestor</th>
                    <th class="text-center">Option</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>

<script src="<?= base_url('assets/js/pembayaran_material/payment_selection.js') ?>"></script>
<?php if (!empty($transfers)): ?>
	<?php $rp_tutorial_page = 'queue'; include dirname(__DIR__, 2) . '/request_payment/views/transfer_tutorial.php'; ?>
	<form class="box box-primary" id="rp-payment-selection">
		<div class="box-header"><h3 class="box-title">List Request Payment</h3></div>
		<div class="box-body">
			<p class="text-muted">Pilih satu transfer dari pengajuan yang sudah disetujui.</p>
			<div class="table-responsive"><table class="table table-bordered table-striped">
				<thead><tr class="bg-blue"><th>PILIH</th><th>NO. PENGAJUAN / TRANSFER</th><th>COMPANY</th><th>DOKUMEN</th><th>TRANSFER KE</th><th>ADMIN</th><th>PAYMENT BANK</th></tr></thead>
				<tbody><?php foreach ($transfers as $row): ?>
					<?php $total = Rp_transfer::totals(Rp_transfer::cents($row['net']), Rp_transfer::cents($row['admin']), $row['bearer']); ?>
					<tr>
						<td class="text-center"><input type="radio" name="transfer" required aria-label="Pilih <?= html_escape($row['no_pengajuan']) ?> Transfer <?= (int) $row['urutan'] ?>" value="<?= (int) $row['id'] ?>"></td>
						<td><b><?= html_escape($row['no_pengajuan']) ?></b><br>Transfer <?= (int) $row['urutan'] ?></td>
						<td><?= html_escape($row['company_nama']) ?></td><td><?= html_escape($row['numbers']) ?><br><small class="text-muted"><?= html_escape($row['categories']) ?></small></td>
						<td><b><?= html_escape($row['nama']) ?></b><br><?= html_escape($row['bank'] . ' - ' . $row['no_rek']) ?></td>
						<td><?= (float) $row['admin'] ? html_escape('Rp ' . number_format($row['admin'], 2, ',', '.') . ' (' . ($row['bearer'] === 'company' ? 'Perusahaan' : 'Penerima') . ')') : '-' ?></td>
						<td class="text-right">Rp <?= number_format($total['bank'] / 100, 2, ',', '.') ?></td>
					</tr>
				<?php endforeach; ?></tbody>
			</table></div>
			<button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Proses</button>
		</div>
	</form>
	<script>
		document.getElementById('rp-payment-selection').addEventListener('submit', function (event) {
			event.preventDefault();
			window.location.href = <?= json_encode(site_url('pembayaran_material/transfer_payment')) ?> + '/' + this.elements.transfer.value;
		});
	</script>
<?php endif; ?>
