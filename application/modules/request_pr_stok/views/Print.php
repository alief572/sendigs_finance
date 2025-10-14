<html>

<head>
	<style type="text/css">
		.header_style_company {
			padding: 15px;
			color: black;
			font-size: 20px;
			vertical-align: bottom;
		}

		.header_style_company2 {
			padding: 15px;
			color: black;
			font-size: 15px;
			vertical-align: top;
		}

		.header_style_alamat {
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

		table.gridtable {
			font-family: arial, sans-serif;
			font-size: 11px;
			color: #333333;
			border: 1px solid #808080;
			border-collapse: collapse;
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

	<table width="50%">
		<tr>
			<td width="45%">No. PR </td>
			<td style="text-align: center;">:</td>
			<td><?= $header->no_pr ?></td>
		</tr>
		<tr>
			<td width="45%">Tanggal PR</td>
			<td style="text-align: center;">:</td>
			<td><?= date('d F Y', strtotime($header->tgl_so)) ?></td>
		</tr>
		<tr>
			<td width="45%">Tanggal Dibutuhkan</td>
			<td style="text-align: center;">:</td>
			<td><?= date('d F Y', strtotime($header->tgl_dibutuhkan)) ?></td>
		</tr>
	</table>

	<br><br>

	<table width="100%" border="1" s>
		<thead>
			<tr>
				<th style="text-align: center;">No.</th>
				<th style="text-align: center;">Nama Barang</th>
				<th style="text-align: center;">Spesifikasi</th>
				<th style="text-align: center;">Tipe</th>
				<th style="text-align: center;">Qty Stok</th>
				<th style="text-align: center;">Satuan</th>
				<th style="text-align: center;">Qty Kebutuhan</th>
				<th style="text-align: center;">Qty Permintaan</th>
				<th style="text-align: center;">Harga</th>
				<th style="text-align: center;">Total</th>
			</tr>
		</thead>
		<tbody>
			<?php
            $no = 0;

            $grand_total = 0;

			foreach ($detail as $row) {
				$no++;
				echo '<tr>';
				echo '<td style="text-align: center;">' . $no . '</td>';
				echo '<td style="text-align: left;">' . $row->stock_name . '</td>';
				echo '<td style="text-align: left;">' . $row->spec . '</td>';
				echo '<td style="text-align: left;">' . $row->nm_category . '</td>';
				echo '<td style="text-align: right;">' . number_format(($row->qty_stock / $row->konversi)) . '</td>';
				echo '<td style="text-align: center;">' . $row->satuan . '</td>';
				echo '<td style="text-align: right;">' . number_format(($row->qty_kebutuhan / $row->konversi)) . '</td>';
				echo '<td style="text-align: right;">' . number_format(($row->propose_purchase / $row->konversi)) . '</td>';
				echo '<td style="text-align: right;">' . number_format($row->price_ref, 2) . '</td>';
				echo '<td style="text-align: right;">' . number_format(($row->price_ref * $row->propose_purchase), 2) . '</td>';
                echo '</tr>';

                $grand_total += (($row->price_ref * $row->propose_purchase));
			}
			?>
        </tbody>
        <tfooter>
            <tr>
                <th style="text-align: center;" colspan="9">Grand Total</th>
                <th style="text-align: right;"><?= number_format($grand_total, 2) ?></th>
            </tr>
        </tfooter>
	</table>

	<br><br>

	<table width="100%">
		<tr>
			<td style="text-align: center;">Mengajukan</td>
			<td style="text-align: center;">Menyetujui <br> <?= date('d-m-Y') ?></td>
		</tr>
		<tr>
			<td style="text-align: center; height: 200px;">Purchasing</td>
			<td style="text-align: center; height: 200px;">Imanuel Iman</td>
		</tr>
	</table>

</body>

</html>

<script>
    window.print();
</script>
