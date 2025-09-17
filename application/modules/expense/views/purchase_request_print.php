<!DOCTYPE html>
<html>
<head>
  <title>Purchase Request</title>
  <style>
  body { font-family: Calibri,Arial; font-size:12px; }
        table { border-collapse:collapse;
				table-layout:fixed;}
	.border-all{border: 1px solid black; }
	.border-rl{border-left: 1px solid;border-right: 1px solid;}
	.border-rlb{border-left: 1px solid;border-right: 1px solid;border-bottom: 1px solid;}

  </style>
</head>
<body>
	<table width="900" cellspacing="0" cellpadding="2">
		<thead>
			<tr>
				<td colspan=8 valign=top>No Purchase Request : <?php echo (isset($data->pr_no) ? $data->pr_no: ""); ?><br />
				Tanggal Purchase Request : <?php echo (isset($data->pr_date) ? $data->pr_date: ""); ?><br />
				Keterangan : <?php echo (isset($data->pr_info) ? $data->pr_info: ""); ?>
				</td>
				<td colspan=3 valign=top>
				Inventory Type : <?php echo (isset($inventory_type) ? $inventory_type[$data->id_type]: ""); ?><br />
				Departemen / Divisi : <?php echo (isset($divisi) ? $divisi[$data->divisi]: ""); ?>
				</td>
			</tr>
			<tr>
				<td colspan=11>&nbsp;</td>
			</tr>
			<tr>
			<th class="border-all">No</th>
			<th class="border-all">Nama</th>
			<th class="border-all">Spesifikasi</th>
			<th class="border-all">Brand</th>
			<th class="border-all">Max Stok</th>
			<th class="border-all">Qty Stok</th>
			<th class="border-all">Satuan</th>
			<th class="border-all">Saran Qty Pembelian</th>
			<th class="border-all">Qty Permintaan</th>
			<th class="border-all">Price Reference</th>
			<th class="border-all">Total Price</th>
			</tr>
		</thead>
		<tbody>
		<?php $total=0;
		if(!empty($data_material)){
			$idd=0;
			foreach($data_material AS $record){
				$idd++;?>
			<tr>
				<td class="border-all" valign=top><?=$idd;?></td>
				<td class="border-all" valign=top><?= $record->nama ?></td>
				<td class="border-all" valign=top><?= $record->spec3 ?></td>
				<td class="border-all" valign=top><?= $record->spec2 ?></td>
				<td class="border-all" valign=top><?= $record->spec13 ?></td>
				<td class="border-all" align="center"><?=(($record->stock!='')?number_format($record->stock):'0');?></td>
				<td class="border-all" valign=top><?=$record->satuan;?></td>
				<td class="border-all" valign=top align="right"><?php echo number_format($record->spec13-$record->stock) ?></td>
				<td class="border-all" valign=top align="right"><?=number_format($record->material_qty);?></td>
				<td class="border-all" valign=top align="right"><?=number_format($record->material_price_ref);?></td>
				<td class="border-all" valign=top align="right"><?=number_format(($record->material_price_ref*$record->material_qty));?></td>
			</tr>
			<?php 
			$total=($total+($record->material_price_ref*$record->material_qty));
			}
		}?>
		<tr>
			<td colspan=10></td>
			<td class="border-all" align="right"><?php echo number_format($total);?></td>
		</tr>
		<?php for($i=1;$i<(23-$idd);$i++){ ?>
		<tr>
			<td colspan=11>&nbsp;</td>
		</tr>
		<?php } ?>
		<tr>
			<td colspan=5 align="center">Mengajukan<br /><br /><br /><br /><br /><br /><br />Purchasing
			</td>
			<td colspan=6 align="center">Menyetujui<br /><br /><br /><br /><br /><br /><br />Imanuel Iman
			</td>
		</tr>
		</tbody>
	</table>
</body>
</html>