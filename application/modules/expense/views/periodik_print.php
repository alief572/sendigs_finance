<?php
function formatDate($date)
{
	if (empty($date) || $date == '0000-00-00') return '-';
	return date('d-M-y', strtotime($date));
}
?>
<!DOCTYPE html>
<html>

<head>
	<title><?= $title ?></title>
	<link rel="stylesheet" href="<?= base_url('assets/AdminLTE/bootstrap/css/bootstrap.min.css') ?>">
	<style>
		body {
			font-family: sans-serif;
			font-size: 11px;
			padding: 15px;
			margin: 0;
		}

		.document-header {
			text-align: center;
			padding: 8px 0;
			border-top: 2px solid #333;
			border-bottom: 1px solid #333;
			margin-bottom: 10px;
		}

		.document-header h4 {
			margin: 0 0 3px 0;
			font-size: 13px;
			font-weight: bold;
		}

		.document-header p {
			margin: 0;
			font-size: 11px;
		}

		table.info-table {
			width: 100%;
			border-collapse: collapse;
		}

		table.info-table td {
			padding: 2px 5px;
			vertical-align: top;
			font-size: 11px;
		}

		table.detail-table {
			border-collapse: collapse;
			width: 100%;
			font-size: 11px;
		}

		table.detail-table th,
		table.detail-table td {
			border: 1px solid #333;
			padding: 4px 6px;
		}

		table.detail-table th {
			text-align: center;
			font-weight: bold;
			font-style: italic;
		}

		.section-title {
			font-weight: bold;
			margin: 10px 0 3px 0;
			font-size: 11px;
		}

		.signature-table td {
			text-align: center;
			padding: 5px 20px;
			vertical-align: top;
			font-size: 11px;
		}

		.bank-signature-wrapper {
			display: table;
			width: 100%;
			margin-top: 8px;
		}

		.bank-section {
			display: table-cell;
			vertical-align: top;
			width: 35%;
		}

		.signature-section {
			display: table-cell;
			vertical-align: top;
			width: 65%;
			text-align: center;
		}

		.attachment-separator {
			border-top: 3px solid #4CAF50;
			margin: 20px 0 15px 0;
		}

		.attachment-img {
			max-width: 100%;
			margin: 10px 0;
		}

		@media print {
			.attachment-separator {
				page-break-before: always;
				border-top: 3px solid #4CAF50;
			}

			body {
				padding: 10px;
			}
		}
	</style>
</head>

<body>

	<!-- Document Header -->
	<div class="document-header">
		<h4>Pengajuan Periodik</h4>
		<p><?= $data->no_doc ?></p>
	</div>

	<!-- Informasi Pengajuan -->
	<div class="section-title">Informasi Pengajuan</div>
	<table class="info-table">
		<tr>
			<td width="120">Department</td>
			<td width="5">:</td>
			<td width="150"><?= !empty($dept_name) ? $dept_name : '-' ?></td>
			<td width="40"></td>
			<td width="130">Approval Management</td>
			<td width="5">:</td>
			<td><?= formatDate($data->approved_date ?? '') ?></td>
		</tr>
		<tr>
			<td>Tanggal Pengajuan</td>
			<td>:</td>
			<td><?= formatDate($data->tanggal_doc) ?></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>

	<!-- Detail Pengajuan -->
	<div class="section-title">Detail Pengajuan</div>
	<table class="detail-table">
		<thead>
			<tr>
				<th width="25">No</th>
				<th>COA</th>
				<th>Deskripsi</th>
				<th width="80">Jadwal Pembayaran</th>
				<th width="90">Budget</th>
				<th width="90">Perkiraan Biaya</th>
				<th>Keterangan</th>
				<th width="120">Bank/No Rek/Nama</th>
			</tr>
		</thead>
		<tbody>
			<?php $total_nilai = 0; ?>
			<?php foreach ($detail as $i => $item): ?>
				<?php $total_nilai += (float)$item->nilai; ?>
				<tr>
					<td style="text-align: center;"><?= $i + 1 ?></td>
					<td><?= $item->coa_display ?></td>
					<td><?= $item->nama ?></td>
					<td style="text-align: center;"><?= formatDate($item->tanggal) ?></td>
					<td style="text-align: right;">Rp <?= number_format($item->budget, 0, ',', '.') ?></td>
					<td style="text-align: right;">Rp <?= number_format($item->nilai, 0, ',', '.') ?></td>
					<td><?= !empty($item->keterangan) ? $item->keterangan : '' ?></td>
					<td style="padding:0;"><?php
											if (!empty($item->bank_id) || !empty($item->accnumber) || !empty($item->accname)) {
												echo '<table style="width:100%;border-collapse:collapse;">';
												echo '<tr><td style="padding:4px 6px;border-bottom:1px solid #333;">' . $item->bank_id . '</td></tr>';
												echo '<tr><td style="padding:4px 6px;border-bottom:1px solid #333;">' . $item->accname . '</td></tr>';
												echo '<tr><td style="padding:4px 6px;">' . $item->accnumber . '</td></tr>';
												echo '</table>';
											} else {
												echo '<div style="padding:4px 6px;">-</div>';
											}
											?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="5" style="text-align: right; font-weight: bold;">Total</td>
				<td style="text-align: right; font-weight: bold;">Rp <?= number_format($total_nilai, 0, ',', '.') ?></td>
				<td colspan="2"></td>
			</tr>
		</tfoot>
	</table>

	<!-- Signature Section -->
	<div class="signature-section" style="text-align: center; margin-top: 15px;">
		<table class="signature-table" style="margin: 0 auto;">
			<tr>
				<td style="width: 150px;"><strong>Finance</strong></td>
				<td style="width: 150px;"><strong>Management</strong></td>
			</tr>
			<tr>
				<td style="height: 50px;"></td>
				<td style="height: 50px;"></td>
			</tr>
			<tr>
				<td>
					<u>Fikri</u><br>
					<small><?= (!empty($data->approved_date) && $data->approved_date != '0000-00-00') ? formatDate($data->approved_date) : '' ?></small>
				</td>
				<td>
					<u>Imanuel Iman</u><br>
					<small><?= (!empty($data->approved_date) && $data->approved_date != '0000-00-00') ? formatDate($data->approved_date) : '' ?></small>
				</td>
			</tr>
		</table>
	</div>

	<!-- Attachments -->
	<?php
	$has_attachments = false;
	foreach ($detail as $item) {
		if (!empty($item->doc_file)) {
			$has_attachments = true;
			break;
		}
	}
	?>
	<?php if ($has_attachments): ?>
		<div class="attachment-separator"></div>
		<?php foreach ($detail as $item): ?>
			<?php if (!empty($item->doc_file)):
				$ext = strtolower(pathinfo($item->doc_file, PATHINFO_EXTENSION));
			?>
				<?php if ($ext == 'pdf'): ?>
					<iframe src="<?= base_url('assets/bayar_rutin/' . $item->doc_file) ?>" width="100%" height="600px" style="border: none;"></iframe>
				<?php elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
					<img src="<?= base_url('assets/bayar_rutin/' . $item->doc_file) ?>" class="attachment-img">
				<?php endif; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<script>
		window.print();
	</script>
</body>

</html>