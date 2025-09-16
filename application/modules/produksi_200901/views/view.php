<?php
// print_r($header);
?>
<div class="box box-primary">
	<!-- /.box-header -->
	<div class="box-body">
		<h5 class="box-title"><b>Detail Production</b></h5>
		<table id="example1" border='0' width='100%' class="table table-striped table-bordered table-hover table-condensed">
			<thead id='head_table'>
				<tr class='bg-blue'>
					<th class='text-center' style='width: 4%;'>#</th>
					<th class='text-center'>Product Name</th>
					<th class='text-center'>Process Name</th>
					<th class='text-center'>Daycode</th>
					<th class='text-center'>Status</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$nox = 0;
				  foreach($header AS $val2 => $val2x){ $nox++;
				  echo "<tr>";
					echo "<td align='center'>".$nox."</td>";
					echo "<td align='left'>".get_name('ms_inventory_category1', 'nama', 'id_category1', $val2x['id_category1'])." - ".get_name('ms_inventory_category2', 'nama', 'id_category2', $val2x['id_product'])."</td>";
					echo "<td align='left'>".get_name('ms_process', 'nm_process', 'id', $val2x['id_process'])."</td>";
					echo "<td align='center'>".$val2x['code']."</td>";
					echo "<td align='center'>".$val2x['ket']."</td>";
				  echo "</tr>";
				  }
				  ?>
			</tbody>
		</table>
	</div>
</div>
