<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>

<body>


	<style type="text/css">
		@page {
			margin-top: 1cm;
			margin-left: 0.5cm;
			margin-right: 0.5cm;
			margin-bottom: 1cm;
		}

		.mid {
			vertical-align: middle !important;
		}

		table.gridtable {
			font-family: verdana, arial, sans-serif;
			font-size: 9px;
			color: #333333;
			border-width: 1px;
			border-color: #666666;
			border-collapse: collapse;
		}

		table.gridtable th {
			border-width: 1px;
			padding: 8px;
			border-style: solid;
			border-color: #666666;
			background-color: #f2f2f2;
		}

		table.gridtable th.head {
			border-width: 1px;
			padding: 8px;
			border-style: solid;
			border-color: #666666;
			background-color: #7f7f7f;
			color: #ffffff;
		}

		table.gridtable td {
			border-width: 1px;
			padding: 3px;
			border-style: solid;
			border-color: #666666;
			background-color: #ffffff;
		}

		table.gridtable td.cols {
			border-width: 1px;
			padding: 3px;
			border-style: solid;
			border-color: #666666;
			background-color: #ffffff;
		}

		table.gridtable2 {
			font-family: verdana, arial, sans-serif;
			font-size: 10px;
			color: #333333;
			border-width: 1px;
			border-color: #666666;
			border-collapse: collapse;
		}

		table.gridtable2 th {
			border-width: 1px;
			padding: 3px;
			border-style: none;
			border-color: #666666;
			background-color: #f2f2f2;
		}

		table.gridtable2 th.head {
			border-width: 1px;
			padding: 3px;
			border-style: none;
			border-color: #666666;
			background-color: #7f7f7f;
			color: #ffffff;
		}

		table.gridtable2 td {
			border-width: 1px;
			padding: 3px;
			border-style: none;
			border-color: #666666;
			background-color: #ffffff;
		}

		table.gridtable2 td.cols {
			border-width: 1px;
			padding: 3px;
			border-style: none;
			border-color: #666666;
			background-color: #ffffff;
		}
	</style>
	<table class="gridtable2" border='1' width='100%' cellpadding='2'>
		<tr>
			<td align='center'><b>PT ORIGA MULIA FRP</b></td>
		</tr>
		<tr>
			<td align='center'><b>
					<h2>TANDA TERIMA BARANG</h2>
				</b></td>
		</tr>
	</table>
	<br>
	<br>
	<table class="gridtable2" width="100%" border='0'>
		<thead>
			<tr>
				<td class="mid">No Transaksi</td>
				<td class="mid">:</td>
				<td class="mid" colspan='4'><?= $kode_trans; ?></td>
			</tr>
			<tr>
				<td class="mid" width='15%'>No PO</td>
				<td class="mid" width='2%'>:</td>
				<td class="mid" width='33%'><?= $rest_data[0]['no_surat']; ?></td>
				<td class="mid" width='15%'></td>
				<td class="mid" width='2%'></td>
				<td class="mid" width='33%'></td>
			</tr>
			<tr>
				<td class="mid">Tanggal Terima</td>
				<td class="mid">:</td>
				<td class="mid"><?= date('d F Y', strtotime($rest_data[0]['created_date'])); ?></td>
				<td class="mid"></td>
				<td class="mid"></td>
				<td class="mid"></td>
			</tr>
		</thead>
	</table><br>
	<table class="gridtable" width='100%' border='1' cellpadding='2'>
		<thead align='center'>
			<tr>
				<th class="text-center">No.</th>
				<th class="text-center">No. PO</th>
				<th class="text-center">Material</th>
				<th class="text-center">Incoming</th>
				<th class="text-center">Unit</th>
				<th class="text-center">Konversi</th>
				<th class="text-center">Packing</th>
				<th class="text-center">Qty Pack</th>
				<th class="text-center">Qty NG</th>
				<th class="text-center">Qty Oke</th>
				<th class="text-center">Qty Pack</th>
				<th class="text-center">Expired Date</th>
				<th class="text-center">Lot Description</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$No = 0;
			foreach ($rest_d as $val => $valx) {
				$No++;

				echo '<tr>';
				echo '<td class="text-center">' . $No . '</td>';
				echo '<td class="text-center">' . $valx['no_surat'] . '</td>';
				echo '<td class="text-center">' . $valx['nm_material'] . '</td>';
				echo '<td class="text-center">' . number_format($valx['qty_order'], 2) . '</td>';
				echo '<td class="text-center">' . strtoupper($valx['satuan']) . '</td>';
				echo '<td class="text-center">' . number_format($valx['konversi'], 2) . '</td>';
				echo '<td class="text-center">' . number_format($packing, 2) . '</td>';
				echo '<td class="text-center">' . $valx['packing'] . '</td>';
				echo '<td class="text-center">-</td>';
				echo '<td class="text-center">-</td>';
				echo '<td class="text-center">-</td>';
				echo '<td class="text-center">-</td>';
				echo '<td class="text-center">-</td>';
				echo '</tr>';

				$get_checked_incoming = $this->db->get_where('tr_checked_incoming_detail', ['kode_trans' => $valx['kode_trans'], 'id_detail' => $valx['id'], 'id_material' => $valx['id_material']])->result_array();
				foreach ($get_checked_incoming as $checked_incoming) :



					echo '<tr>';
					echo '<td colspan="8"></td>';
					echo '<td class="text-center">' . number_format($checked_incoming['qty_ng'], 2) . '</td>';
					echo '<td class="text-center">' . number_format($checked_incoming['qty_oke'], 2) . '</td>';
					echo '<td class="text-center">' . number_format($checked_incoming['qty_pack'], 2) . '</td>';
					echo '<td class="text-center">' . date('d F Y', strtotime($checked_incoming['expired_date'])) . '</td>';
					echo '<td>' . $checked_incoming['lot_description'] . '</td>';
					echo '</tr>';
				endforeach;
			}
			?>
		</tbody>
	</table><br><br><br>
	<table class="gridtable2" width='100%' border='0' cellpadding='2'>
		<tr>
			<td width='65%'></td>
			<td align='center'></td>
			<td></td>
			<td width='5%'></td>
			<td align='center'>Ttd,</td>
			<td></td>
		</tr>
		<tr>
			<td height='45px'></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td align='center'></td>
			<td></td>
			<td></td>
			<td align='center'>QC Inspector</td>
			<td></td>
		</tr>
	</table>



</body>

</html>