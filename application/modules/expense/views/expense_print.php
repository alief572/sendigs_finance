<html>

<head>
	<title> EXPENSES REPORT BENSIN & TOL </title>
</head>

<body>
	<style>
		body {
			font-family: sans-serif;
		}

		table.garis {
			border-collapse: collapse;
			font-size: 0.9em;
			font-family: sans-serif;
		}
	</style>
	<table cellpadding=2 cellspacing=0 border=0 width=650>
		<tr>
			<th colspan=8>Form Permintaan Pembelian Barang dan Jasa</th>
		</tr>
		<tr>
			<td colspan=8>
				<table cellpadding=2 cellspacing=0 border=1 width=650 class="garis">
					<tr>
						<th nowrap>No</th>
						<th nowrap>Tgl Pengajuan</th>
						<th nowrap>Nama Barang</th>
						<th nowrap>Spesifikasi</th>
						<th nowrap>Jml</th>
						<th nowrap>Tgl Dibutuhkan</th>
						<th nowrap>Perkiraan Biaya<br />Satuan</th>
						<th nowrap>Total Biaya</th>
					</tr>
					<?php $total_expense = 0;
					$total_tol = 0;
					$total_parkir = 0;
					$total_kasbon = 0;
					$idd = 1;
					$total_km = 0;
					$grand_total = 0;
					$i = 0;
					$gambar = "";
					if (!empty($data_detail)) {
						foreach ($data_detail as $record) {
							$i++; ?>
							<tr>
								<td><?= $i; ?></td>
								<td><?= date('d F Y', strtotime($data->created_on)); ?></td>
								<td><?= $record->deskripsi; ?></td>
								<td><?= $record->keterangan; ?></td>
								<td align="right"><?= number_format($record->qty); ?></td>
								<td><?= date('d F Y', strtotime($record->tanggal)); ?></td>
								<td align="right"><?= number_format($record->harga); ?></td>
								<td align="right"><?= number_format($record->expense); ?></td>
							</tr>
					<?php
							if ($record->doc_file != '') {
								if (strpos($record->doc_file, 'pdf', 0) > 1) {
									$gambar .= '<iframe src="' . base_url('assets/expense/' . $record->doc_file) . '#toolbar=0&navpanes=0" title="PDF" style="width:600px; height:500px;" frameborder="0"></iframe><br /><br />';
								} else {
									$gambar .= '<img src="' . base_url() . 'assets/expense/' . $record->doc_file . '" width="500"><br />';
								}
							}
							$total_expense = ($total_expense + ($record->expense));
							$idd++;
						}
					}
					$grand_total = ($total_expense);
					for ($x = 0; $x < (5 - $i); $x++) {
						echo '
		<tr>
			<td>&nbsp;</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	';
					}
					?>
					<tr>
						<td colspan=7 align=center><strong>Total</strong></td>
						<td align="right"><?= number_format($grand_total); ?></td>
					</tr>
				</table>
			</td>
		</tr>

		<?php
		$pelapor = $this->db->query("SELECT b.nm_karyawan as name FROM users a left join employee b on a.employee_id=b.id WHERE a.username='" . $data->created_by . "'")->row();
		$mengetahui = $this->db->query("SELECT b.nm_karyawan as name FROM users a left join employee b on a.employee_id=b.id WHERE a.username='" . $data->approved_by . "'")->row();
		?>

		<tr>
			<td colspan=2 align=center>Mengajukan</td>
			<td></td>
			<td align=center colspan=3>Mengetahui</td>
			<td></td>
			<td align=center>Menyetujui</td>
		</tr>
		<tr>
			<td colspan=8>&nbsp;</td>
		</tr>
		<tr height=120>
			<td colspan=2 align=center nowrap valign="bottom" width=100><?php
																		// if ($pelapor->ttd != '') {
																		// 	echo '<img src="https://sentral.dutastudy.com/hrsentral/assets/profile/' . $pelapor->ttd . '" height=120><br>';
																		// } else {
																		echo '<br><br><br>';
																		// }
																		?>

			</td>
			<td width=25>&nbsp;</td>
			<td colspan=3 align=center nowrap valign="bottom" width=120><?php
																		if (!empty($mengetahui)) {
																			// if ($mengetahui->ttd != '') {
																			// 	echo '<img src="https://sentral.dutastudy.com/hrsentral/assets/profile/' . $mengetahui->ttd . '" height=120><br>';
																			// } else {
																			echo '<br><br><br>';
																			// }
																		}
																		?>

			</td>
			<td>&nbsp;</td>
			<td align=center nowrap valign="bottom"><u>&nbsp; &nbsp; </u><br /></td>
		</tr>
	</table>
	<em>STM/FR02/09/01/00</em>

	<br />
	<?= $gambar ?>
</body>

</html>