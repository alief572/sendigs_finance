<html>
<head>
  <title>Cetak PDF</title>
<style>
    #tables td, th {
		border: 1px solid #000000;
        padding: 2 px;
		font-size: 11px;
		border-collapse: collapse;
    }
	.clearth{
		border: 0px;
		border-collapse: collapse;
	}
</style>
</head>
<body>
<?php
	foreach($head as $header){
	}
?>
<div id='wrapper'>
<table border="0" width='100%' align="center">
<tr>
	<td width="700" align="center">
		<h5 style="text-align: left;">PT METALSINDO PASIFIC</h5>
	</td>
</tr>
</table>
<table id="tables" border="0" width='100%' align="center">
<tr>
	<td width="700" align="left">
		<h3 style="text-align: center;">INCOMING</h3>
	</td>
</tr>
</table>

<table border="1px" cellspacing="0" width='100%' align="center">
<tr>
	<td width="380" align="center">
	<table width='380' align="center">
	<tr><td width='70' align="left">Supplier</td><td width='10' align="left">:</td><td width='300' align="left"><?= $header->name_suplier ?></td></tr>
	<tr><td width='70' align="left">Address</td><td width='10' align="left">:</td><td width='300' align="left"><?= $header->address_office ?></td></tr>
	<tr><td width='70' align="left">Phone</td><td width='10' align="left">:</td><td width='300' align="left"><?= $header->telephone ?></td></tr>
	<tr><td width='70' align="left">FAX</td><td width='10' align="left">:</td><td width='300' align="left"><?if(empty($header->fax)){echo"-";}else{echo"$header->fax";}  ?></td></tr>
	</table>
	</td>
</tr>
</table>
<br>
		<table id="tables" border="0" width='100%'  align="center" cellpadding="2" cellspacing="0">
			<thead>
			<tr class='bg-blue'>
			<th width='20%'>No PO</th>
			<th width='20%'>Material</th>
			<th width='7%'>Length</th>
			<th width='7%'>Width</th>
			<th width='7%'>Weight</th>
			<th width='7%'>Qty Order</th>
			<th width='7%'>Qty Receive</th>
			<th width='7%'>Width Receive</th>
			<th width='7%'>Lot. No</th>
			</tr>
			</thead>
			<tbody id="data_request">
			<?php
		       $loop=0;
			   foreach ($detail as $material){
				$loop++;
				echo "
				<tr id='trmaterial_$loop'>
				<th						>".substr($material->id_dt_po,0,8)."</th>
				<th						>".$material->nama_material."</th>
				<th						>".$material->length."</th>
				<th						>".$material->width."</th>
				<th						>".$material->weight."</th>
				<th						>".$material->qty_order."</th>
				<th						>".$material->qty_recive."</th>
				<th						>".$material->width_recive."</th>
				<th						>".$material->lotno."</th>
				</tr>
				";
				}
			?>
			</tbody>
			</table>
</div>