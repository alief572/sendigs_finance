<?php

/**
 * Transport Request Print View - Manual Verification Checklist
 * 
 * Test Scenarios:
 * 1. Valid ID: Load /expense/transport_req_print/{valid_id} — all 5 sections render correctly
 * 2. Empty Detail: Test with a transport request with no detail records — empty tbody, totals show Rp 0
 * 3. Multiple Attachments: Test with mixed PDF and image attachments — PDFs in iframes, images inline
 * 4. Print Preview: Use browser Ctrl+P — page break before attachments, clean layout
 * 5. Long Text: Test with long keperluan/rute values — no overflow issues
 * 6. Empty Bank Info: Test with empty bank fields — shows "-" placeholder
 * 7. Null Dates: Test with 0000-00-00 dates — shows "-" via formatDate()
 */
?>
<?php
function formatDate($date)
{
	if (empty($date) || $date == '0000-00-00') return '-';
	return date('d-M-y', strtotime($date));
}
?>
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
		<h4>Pengajuan Transportasi</h4>
		<p><?= $data->no_doc ?></p>
	</div>

	<!-- Informasi Pengajuan -->
	<div class="section-title">Informasi Pengajuan</div>
	<table class="info-table">
		<tr>
			<td width="120">Department</td>
			<td width="5">:</td>
			<td width="150"><?= $dept_name ?></td>
			<td width="40"></td>
			<td width="110">Keterangan</td>
			<td width="5">:</td>
			<td>Transportasi <?= date('d/m/Y', strtotime($data->date1)) ?> - <?= date('d/m/Y', strtotime($data->date2)) ?></td>
		</tr>
		<tr>
			<td>Request By</td>
			<td>:</td>
			<td><?= $request_by ?></td>
			<td></td>
			<td>COA</td>
			<td>:</td>
			<td><?= $coa_display ?></td>
		</tr>
		<tr>
			<td>Tanggal Pengajuan</td>
			<td>:</td>
			<td><?= formatDate($data->tgl_doc) ?></td>
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
				<th width="30">No</th>
				<th width="70">Tanggal</th>
				<th>Keperluan</th>
				<th>Rute</th>
				<th width="90">Bensin</th>
				<th width="80">Tol</th>
				<th width="80">Parkir</th>
				<th width="90">Lain-lain</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$total_bensin = 0;
			$total_tol = 0;
			$total_parkir = 0;
			$total_lainnya = 0;
			$no = 1;
			if (!empty($data_detail)) :
				foreach ($data_detail as $record) :
					$total_bensin += $record->bensin;
					$total_tol += $record->tol;
					$total_parkir += $record->parkir;
					$total_lainnya += $record->lainnya;
			?>
					<tr>
						<td style="text-align: center;"><?= $no++ ?></td>
						<td style="text-align: center;"><?= formatDate($record->tgl_doc) ?></td>
						<td><?= $record->keperluan ?></td>
						<td><?= $record->rute ?></td>
						<td style="text-align: right;"><?= 'Rp ' . number_format($record->bensin, 0, ',', '.') ?></td>
						<td style="text-align: right;"><?= 'Rp ' . number_format($record->tol, 0, ',', '.') ?></td>
						<td style="text-align: right;"><?= 'Rp ' . number_format($record->parkir, 0, ',', '.') ?></td>
						<td style="text-align: right;"><?= 'Rp ' . number_format($record->lainnya, 0, ',', '.') ?></td>
					</tr>
			<?php
				endforeach;
			endif;
			?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4" style="text-align: right; font-weight: bold;">Subtotal</td>
				<td style="text-align: right;"><?= 'Rp ' . number_format($total_bensin, 0, ',', '.') ?></td>
				<td style="text-align: right;"><?= 'Rp ' . number_format($total_tol, 0, ',', '.') ?></td>
				<td style="text-align: right;"><?= 'Rp ' . number_format($total_parkir, 0, ',', '.') ?></td>
				<td style="text-align: right;"><?= 'Rp ' . number_format($total_lainnya, 0, ',', '.') ?></td>
			</tr>
			<tr>
				<td colspan="4" style="text-align: right; font-weight: bold;">Total</td>
				<td colspan="4" style="text-align: right; font-weight: bold;"><?= 'Rp ' . number_format($total_bensin + $total_tol + $total_parkir + $total_lainnya, 0, ',', '.') ?></td>
			</tr>
		</tfoot>
	</table>

	<!-- Informasi Bank + Signature -->
	<div class="bank-signature-wrapper">
		<div class="bank-section">
			<div class="section-title">Informasi Bank</div>
			<table class="info-table">
				<tr>
					<td width="90">Bank</td>
					<td width="5">:</td>
					<td><?= !empty($data->bank_id) ? $data->bank_id : '-' ?></td>
				</tr>
				<tr>
					<td>No Rekening</td>
					<td>:</td>
					<td><?= !empty($data->accnumber) ? $data->accnumber : '-' ?></td>
				</tr>
				<tr>
					<td>Nama Rekening</td>
					<td>:</td>
					<td><?= !empty($data->accname) ? $data->accname : '-' ?></td>
				</tr>
			</table>
		</div>
		<div class="signature-section">
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
					<td><u>Fikri</u><br><small><?= !empty($data->approved_on) ? date('d-M-Y', strtotime($data->approved_on)) : '' ?></small></td>
					<td><u>Imanuel Iman</u><br><small><?= !empty($data->approved_on) ? date('d-M-Y', strtotime($data->approved_on)) : '' ?></small></td>
				</tr>
			</table>
		</div>
	</div>

	<!-- Attachments -->
	<?php
	$attachments = [];
	if (!empty($data_detail)) {
		foreach ($data_detail as $record) {
			if (!empty($record->doc_file)) {
				$attachments[] = $record->doc_file;
			}
		}
	}
	?>
	<?php if (!empty($attachments)) : ?>
		<div class="attachment-separator"></div>
		<?php foreach ($attachments as $doc_file) : ?>
			<?php if (strtolower(pathinfo($doc_file, PATHINFO_EXTENSION)) == 'pdf') : ?>
				<iframe src="<?= base_url('assets/expense/' . $doc_file) ?>" width="100%" height="600px" style="border: none;"></iframe>
			<?php else : ?>
				<img src="<?= base_url('assets/expense/' . $doc_file) ?>" class="attachment-img">
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<script>
		window.print();
	</script>
</body>

</html>