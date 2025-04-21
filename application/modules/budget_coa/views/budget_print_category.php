<html>
<head>
<style>
    table.table-data td,
    table.table-data th {
        padding: 2px;
        word-wrap: break-word;
        border: .1mm solid #444
    }
    table.table-data {
        border-collapse: collapse;
    }
</style>
</head>
<body>
<table width="700">
<tr><td align="center" colspan="4"><h1>Budget</h1></td></tr>
<tr>
	<td>Departemen : <?=$data[0]->nm_dept?></td>
	<td colspan=2></td>
	<td>Update On : <?=date("d-M-Y",strtotime($data[0]->created_on_dept))?></td>
</tr>
<tr>
	<td>Tahun : <?=$dataset['tahun']?></td>
	<td colspan=2></td>
	<td></td>
</tr>
<tr>
	<td>Kategori : <?=$dataset['kategori']?></td>
	<td colspan=2></td>
	<td></td>
</tr>

</table><br />
<table width="700" border=1 cellpadding=2 cellspacing=1 class="table-data">
	<tr>
		<th>No COA</th>
		<th>Deskripsi</th>
		<th>Budget/Tahun</th>
		<?php
		for($bln=1;$bln<=12;$bln++){
			echo '<th nowrap>'.date('F', mktime(0, 0, 0, $bln, 10)).'</th>';
		}
		?>
	</tr>
	<?php
	if(isset($data)){
	  if(!empty($data)){
		foreach($data AS $record){
			echo '<tr>
			<td>'.$record->no_perkiraan.'</td>
			<td>'.$record->nama_perkiraan.'</td>
			<td align=right>'.number_format($record->total).'</td>';
			for($bln=1;$bln<=12;$bln++){
				echo '<td align=right>'.number_format($record->{"bulan_".$bln}).'</td>';
			}
			echo '</tr>';
		}
	  }
	}
	?>
</table>
</body>
</html>
