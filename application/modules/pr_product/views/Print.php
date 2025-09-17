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
	<?php
	foreach ($header as $header) {
	}
	// $detailsum = $this->db->query("SELECT SUM(width) as sumwidth, SUM(qty) as sumqty, SUM(totalwidth) as sumtotalwidth, SUM(jumlahharga) as sumjumlahharga, SUM(hargasatuan) as sumhargasatuan FROM dt_trans_po WHERE no_po = '" . $header->no_po . "' ")->result();
	// $jumlahdetail = $this->db->query("SELECT COUNT(no_po) as no_po FROM dt_trans_po WHERE no_po = '" . $header->no_po . "' ")->result();
	// $jumlahdata = $jumlahdetail[0]->no_po;
	// $tinggi = 300 / $jumlahdata;
	if (empty($header->negara)) {
		$cou = 'Indonesia';
	} else {
		// $findnegara = $this->db->query("SELECT * FROM negara WHERE id_negara = '" . $header->negara . "' ")->result();
		// $cou = $findnegara[0]->nm_negara;
	}
	// $findpic = $this->db->query("SELECT * FROM child_supplier_pic WHERE id_suplier = '" . $header->id_suplier . "' ")->result();
	// $namapic = $findpic[0]->name_pic;

	// print_r($_SERVER['DOCUMENT_ROOT'] . '/origa_live/assets/images/ori_logo.jpg');
	// exit;
	?>
	<table class="gridtable2" border="0">
		<tr>
			<td style="text-align:left;">
				<img src='<?= $_SERVER['DOCUMENT_ROOT'] . '/origa_live/assets/images/ori_logo2.png'; ?>' alt="" width="75" height="95">
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
		<h3>PURCHASE REQUEST </h3>
	</div>
	<br>
	<table class='gridtable2' width='100%' border='1' align='left' cellpadding='0' cellspacing='0'>
		<tr>
			<td width="300" align="center">
				<table width='300' align="center">
					<tr>
						<td width='50' align="left">Customer</td>
						<td width='10' align="left">:</td>
						<td width='250' align="left"><?= $header->nm_customer ?></td>
					</tr>
					<tr>
						<td width='50' align="left">Address</td>
						<td width='10' align="left">:</td>
						<td width='250' align="left"><?= $header->alamat ?></td>
					</tr>
					<tr>
						<td width='50' align="left">Country</td>
						<td width='10' align="left">:</td>
						<td width='250' align="left"><?= $header->country_name ?></td>
					</tr>
					<tr>
						<td width='50' align="left">PIC</td>
						<td width='10' align="left">:</td>
						<td width='250' align="left"><?= $header->nm_pic ?></td>
					</tr>
					<tr>
						<td width='50' align="left">Phone</td>
						<td width='10' align="left">:</td>
						<td width='250' align="left"><?= $header->hp ?></td>
					</tr>
					<tr>
						<td width='50' align="left">Fax</td>
						<td width='10' align="left">:</td>
						<td width='250' align="left"><?= $header->fax ?></td>
					</tr>
				</table>
			</td>
			<td width="300" align="center">
				<table width='300' align="center">
					<tr>
						<td width='50' align="left">No</td>
						<td width='10' align="left">:</td>
						<td width='250' align="left"><?= $header->so_number ?></td>
					</tr>
					<tr>
						<td width='50' align="left">Date</td>
						<td width='10' align="left">:</td>
						<td width='250' align="left"><?= date('d-F-Y', strtotime($header->tgl_so))  ?></td>
					</tr>
					<tr>
						<td width='50' align="left">Revision</td>
						<td width='10' align="left">:</td>
						<td width='250' align="left"></td>
					</tr>
					<tr>
						<td width='50' align="left">Page</td>
						<td width='10' align="left">:</td>
						<td width='250' align="left"></td>
					</tr>
					<tr>
						<td width='50' align="left">Ref PI</td>
						<td width='10' align="left">:</td>
						<td width='250' align="left"></td>
					</tr>
					<tr>
						<td width='50' align="left">&nbsp;</td>
						<td width='10' align="left"></td>
						<td width='250' align="left"></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<br>
	<?php
	$matauang = (!empty($header->matauang)) ? "<br>(" . strtoupper($header->matauang) . ")" : '';

	// if (strtolower($header->matauang) == 'usd') {
	// 	$kode = '$';
	// }
	// if (strtolower($header->matauang) == 'idr') {
	// 	$kode = 'Rp';
	// }
	?>
	<table class='gridtable' cellpadding='0' cellspacing='0' style='vertical-align:top;'>
		<tbody>
			<tr style='vertical-align:middle; background-color:#c2c2c2; font-weight:bold;'>
				<td align="center" width='50'>Code</td>
				<td align="center" width='330'>Description</td>
				<td align="center" width='40'>Qty</td>
			</tr>
			<?php
			$CIF = "<br><br><br><br><br>";
			$TOT_PPH = 0;
			foreach ($detail as $detail) {
				$kategory = $detail->idmaterial;
				$barang  = $this->db->query("SELECT * FROM new_inventory_4 WHERE code_lv4 = '$kategory' ")->row();

				$TOT_PPH += $detail->jumlahharga * $detail->pajak / 100;
				$HS = number_format($detail->hargasatuan, 2);
				$JH = number_format($detail->jumlahharga, 2);
				if (strtolower($header->loi) == 'lokal') {
					$HS = number_format($detail->hargasatuan, 2);
					$JH = number_format($detail->jumlahharga, 2);
				}

				if ($detail->qty_order > 0) {
					$qty = $detail->qty_order;
				} else {
					$qty = $detail->propose_purchase;
				}



				if ($jumlahdata <= '30') {
					echo "	
                    <tr >
                        <td width='50'>" . $barang->code . "</td>
                        <td width='330'>" . $detail->nama . "</td>
                        <td width='50'>" . number_format($qty) . "</td>
                        
                    </tr>";
					$CIF = "";
				} else {
					echo "	
                    <tr >
                        <td width='50'>" . $barang->code . "</td>
                        <td width='330'>" . $detail->nama . "</td>
                        <td width='50'>" . number_format($qty) . "</td>
                        
                    </tr>";
					$CIF = "";
				}
			} ?>

			<?php
			if ($header->loi == 'Lokal') {
			?>

			<?php
			}
			if ($header->loi == 'Import') {
				$TOT_PPH = 0;
			}

			$TOTHEAD = number_format($detailsum[0]->sumjumlahharga + $TOT_PPH, 2);
			if (strtolower($header->loi) == 'lokal') {
				$TOTHEAD = number_format($detailsum[0]->sumjumlahharga + $TOT_PPH, 2);
			}
			?>


		</tbody>
	</table>
	<br>

	<table border="0" width='100%' align="left">

		<tr>

			<td width="250" align="left"><br><br>
				<table>
					<tr>
						<td align='center'></td>
					</tr>
					<tr>
						<td align="center" width="70%" valign="top">
						</td>
					</tr>
					<tr>
						<td align='center'> </td>
					</tr>
					<tr>
						<td align='center'></td>
					</tr>
				</table>
			</td>
			<td width="250" align="left"><br><br>
				<table>
					<tr>
						<td align='center'></td>
					</tr>
					<tr>
						<td align="center" width="70%" valign="top">
						</td>
					</tr>
					<tr>
						<td align='center'></td>
					</tr>
					<tr>
						<td align='center'></td>
					</tr>
				</table>
			</td>
			<td width="250" align="left"><br><br>
				<table>
					<tr>
						<td align='center'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='right'></td>
					</tr>
					<tr>
						<td align='center'><u></u></td>
					</tr>
					<tr>
						<td align='center'>(<?= $this->auth->user_name() ?>)</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>



</body>

</html>