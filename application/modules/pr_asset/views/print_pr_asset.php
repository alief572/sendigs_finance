<html>

<head>
	<style type="text/css">
		@media print {

			table,
			div {
				break-inside: avoid;
			}
		}

		thead {
			display: table-result-group;
		}

		tfoot {
			display: table-row-group;
		}

		tr {
			page-break-after: always !important;
			page-break-before: always !important;
			page-break-inside: auto !important;
			width: 100%;
		}

		.result_style_company {
			padding: 15px;
			color: black;
			font-size: 20px;
			vertical-align: bottom;
		}

		.result_style_company2 {
			padding: 15px;
			color: black;
			font-size: 15px;
			vertical-align: top;
		}

		.result_style_alamat {
			padding: 10px;
			color: black;
			font-size: 10px;
		}

		table.default {
			font-family: arial, sans-serif;
			font-size: 9px;
			padding: 0px;
		}

		p {
			font-family: arial, sans-serif;
			font-size: 14px;
		}

		.font {
			font-family: arial, sans-serif;
			font-size: 14px;
		}

		table {
			width: 100%;
		}

		table.gridtable {
			font-family: arial, sans-serif;
			font-size: 11px;
			color: #333333;
			border: 1px solid #808080;
			border-collapse: collapse;
			width: 100% !important;
		}

		table.gridtable th {
			padding: 6px;
			background-color: #f7f7f7;
			color: black;
			border-color: #808080;
			border-style: solid;
			border-width: 1px;
		}

		table.gridtable th.head {
			padding: 6px;
			background-color: #f7f7f7;
			color: black;
			border-color: #808080;
			border-style: solid;
			border-width: 1px;
		}

		table.gridtable td {
			border-width: 1px;
			padding: 6px;
			border-style: solid;
			border-color: #808080;
		}

		table.gridtable td.cols {
			border-width: 1px;
			padding: 6px;
			border-style: solid;
			border-color: #808080;
		}


		table.gridtable2 {
			font-family: arial, sans-serif;
			font-size: 12px;
			color: #333333;
			border-width: 1px;
			border-color: #666666;
			border-collapse: collapse;
		}

		table.gridtable2 td {
			border-width: 1px;
			padding: 1px;
			border-style: none;
			border-color: #666666;
			background-color: #ffffff;
		}

		table.gridtable2 td.cols {
			border-width: 1px;
			padding: 1px;
			border-style: none;
			border-color: #666666;
			background-color: #ffffff;
		}

		table.gridtableX {
			font-family: arial, sans-serif;
			font-size: 12px;
			color: #333333;
			border: none;
			border-collapse: collapse;
		}

		table.gridtableX td {
			border-width: 1px;
			padding: 6px;
		}

		table.gridtableX td.cols {
			border-width: 1px;
			padding: 6px;
		}

		table.gridtableX2 {
			font-family: arial, sans-serif;
			font-size: 12px;
			color: #333333;
			border: none;
			border-collapse: collapse;
		}

		table.gridtableX2 td {
			border-width: 1px;
			padding: 2px;
		}

		table.gridtableX2 td.cols {
			border-width: 1px;
			padding: 2px;
		}

		#testtable {
			width: 100%;
		}
	</style>
</head>

<body>

	<table class="gridtable2" border="0">
		<tr>
			<td style="text-align:left;">
				<img src='<?= 'assets/images/ori_logo2.png'; ?>' alt="" width="75" height="95">
			</td>
			<td align="right" width="630">
				<br>
				Jl. Pembangunan II <br>
				Kel. Batusari, <br>
				Kec. Batuceper, <br>
				Kota Tangerang Postal <br>
				Code 15122 <br>
				Indonesia

			</td>
		</tr>
	</table>
	<hr>
	<div style='display:block; border-color:none; background-color:#c2c2c2;' align='center'>
		<h3>PR ASSET</h3>
	</div>
	<br>

	<table class="gridtableX" width="100%">
		<tr>
			<td width="50">No. PR</td>
			<td align="center">:</td>
			<td width="100" align="right"><?= $result[0]['no_pr'] ?></td>
			<td width="220"></td>
			<td width="50">PR Date</td>
			<td align="center">:</td>
			<td width="100" align="right"><?= date('d F Y', strtotime($result_header['tgl_pr'])) ?></td>
		</tr>
	</table>

	<table class='gridtable' id="" width="100%" cellpadding='0' cellspacing='0' style='vertical-align:top;min-width: 400px !important; max-width: 750px !important;'>
		<tr>
			<th align="center" style="font-size: 11px;">No.</th>
			<th align="center" width="144" style="font-size: 11px;">Nama Asset</th>
			<th align="center" width="144" style="font-size: 11px;">Qty Asset</th>
			<th align="center" width="144" style="font-size: 11px;">Nilai Asset / Unit</th>
			<th align="center" width="144" style="font-size: 11px;">Tgl dibutuhkan</th>
		</tr>
		<tr>
			<?php
			$no = 1;
			foreach ($result as $item) {
				echo '<tr>';
				echo '<td align="center" style="font-size: 11px;">' . $no . '</td>';
				echo '<td width="144" style="font-size: 11px;">' . ucfirst($item['nm_barang']) . '</td>';
				echo '<td width="144" align="center" style="font-size: 11px;">' . number_format($result[0]['qty']) . ' Pcs</td>';
				echo '<td width="144" class="text-right" align="right" style="font-size: 11px;">' . number_format($item['nilai_pr'], 2) . '</td>';
				echo '<td width="144" style="font-size: 11px;" align="center">' . date('d F Y', strtotime($result[0]['tgl_dibutuhkan'])) . '</td>';
				echo '</tr>';
				$no++;
			}
			?>
		</tr>
	</table>
	<br>
	<br>
	<br>
	<table class='gridtable' id="" width="100%" cellpadding='0' cellspacing='0' style='vertical-align:top;min-width: 400px !important; max-width: 750px !important;'>
		<tr>
			<th align="center" width="210">Dibuat Oleh,</th>
			<th align="center" width="210">Diperiksa Oleh,</th>
			<th align="center" width="210">Diketahui Oleh,</th>
		</tr>
		<tr>
			<td align="center" width="210" height="80" valign="bottom">........................................</td>
			<td align="center" width="210" height="80" valign="bottom">........................................</td>
			<td align="center" width="210" height="80" valign="bottom">........................................</td>
		</tr>
	</table>
</body>

</html>