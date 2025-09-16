<!DOCTYPE html>
<html>
<head>
  <title>PR Rutin</title>
  <style>
  body { font-family: Calibri,Arial; font-size:14px; }
        table { border-collapse:collapse;
				table-layout:fixed;}
	.border-all{border: 1px solid black; padding:3}
	.border-rl{border-left: 1px solid;border-right: 1px solid;}
	.border-rlb{border-left: 1px solid;border-right: 1px solid;border-bottom: 1px solid;}

  </style>
</head>
<body>
	<table width="1000" cellspacing="0" cellpadding="2">
		<thead>
			<tr>
				<td colspan=8 valign=top>No PR : <?php echo (isset($data->pr_no) ? $data->pr_no: ""); ?><br />
				Tanggal PR : <?php echo (isset($data->pr_date) ? $data->pr_date: ""); ?><br />
				Tanggal Dibutuhkan : <?php echo (isset($data->tgl_dibutuhkan) ? $data->tgl_dibutuhkan: ""); ?><br />
				</td>
			</tr>
			<tr>
				<td colspan=8>&nbsp;</td>
			</tr>
			<tr>
				<th class="border-all">No</th>
				<th class="border-all">Nama Barang</th>
				<th class="border-all">Spesifikasi</th>
				<th class="border-all">Tipe</th>
				<th class="border-all" width="60">Qty Stok</th>
				<th class="border-all" width="60">Satuan</th>
				<th class="border-all" width="60">Qty Kebutuhan</th>
				<th class="border-all" width="50">Qty Permintaan</th>
				<th class="border-all" width="80">Harga</th>
				<th class="border-all" width="90">Total</th>
			</tr>
		</thead>
		<tbody>
		<?php $total=0;
		if(!empty($data_material)){
			$idd=0;$total=0;
			foreach($data_material AS $record){
				$idd++;$total=$total+($record->material_price_ref*$record->material_order);?>
			<tr>
				<td class="border-all" valign=top align="center"><?=$idd;?></td>
				<td class="border-all" valign=top><?= $record->nama_barang ?></td>
				<td class="border-all" valign=top><?= $record->spec1 ?></td>
				<td class="border-all" valign=top><?= $record->nama_jenis ?></td>
				<td class="border-all" valign=top align="center"><?= number_format($record->material_stock);?></td>
				<td class="border-all" valign=top><?= $record->material_unit;?></td>
				<td class="border-all" valign=top align="center"><?= number_format($record->material_qty);?></td>
				<td class="border-all" valign=top align="center"><?= number_format($record->material_order);?></td>
				<td class="border-all" valign=top align="right"><?= number_format($record->material_price_ref);?></td>
				<td class="border-all" valign=top align="right"><?= number_format($record->material_price_ref*$record->material_order);?></td>
			</tr>
			<?php 
			}
		}?>
		<tr>
			<td colspan="8" class="border-all">&nbsp;</td>
			<td class="border-all">Total</td>
			<td align="right" class="border-all"><?=number_format($total)?></td>
		</tr>
		<?php for($i=1;$i<(18-$idd);$i++){ ?>
		<tr>
			<td colspan=10>&nbsp;</td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan=10>&nbsp;</td>
		</tr>
		<tr>
			<td colspan=4 align="center">Mengajukan<br /><br /><br /><br /><br /><br /><br />Purchasing</td>
			<td colspan=4 align="center">Menyetujui<br /><?php 
			if($data->status>0) {
				echo (isset($data->modified_on) ? date("d-m-Y",strtotime($data->modified_on)): "");
			} ?><br /><br /><br /><br /><br /><br />Imanuel Iman</td>
		</tr>
		</tbody>
	</table>
</body>
</html>