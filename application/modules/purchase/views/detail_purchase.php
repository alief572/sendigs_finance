
<div class="box-body">
	<?php if($qBQdetailNum != 0){ ?>
	<!-- <a href="<?php echo site_url('cron/excel_material/'.$id_bq) ?>" target='_blank' class="btn btn-sm btn-success" id='btn-add' style='float:right;'>
	<i class="fa fa-file-excel-o">&nbsp;Excel Material Planning</i>
	</a> -->
	<?php } ?>
	<br>
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center" style='vertical-align:middle;' width='5%'>No</th>
        <th class="text-center" style='vertical-align:middle;' width='20%'>Id Material</th>
				<th class="text-center" style='vertical-align:middle;'>Material Name</th>
				<th class="text-center" style='vertical-align:middle;' width='9%'>Weigth Packing</th>
				<th class="text-center" style='vertical-align:middle;' width='9%'>Unit Packing</th>
				<th class="text-center" style='vertical-align:middle;' width='9%'>Weigth Material</th>
				<th class="text-center" style='vertical-align:middle;' width='9%'>Unit Material</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if($qBQdetailNum != 0){
            $Total1 = 0;
            $No=0;
			foreach($qBQdetailRest AS $val => $valx){
                $No++;
				$Total1 += $valx['qty'];

				echo "<tr>";
          echo "<td align='center'>".$No."</td>";
          echo "<td>".$valx['idmaterial']."</td>";
					echo "<td>".strtoupper($valx['nm_material'])."</td>";
					echo "<td align='right'>".number_format($valx['qty']/$valx['konversi'],2)."</td>";
					echo "<td align='center'>".strtoupper($valx['satuan_packing'])."</td>";
					echo "<td align='right'>".number_format($valx['qty'],2)."</td>";
					echo "<td align='center'>".strtoupper($valx['unit'])."</td>";
				echo "</tr>";
			}
			?>
			<tr>
				<td colspan='2'><b></b></td>
				<td colspan='3'><b>SUM TOTAL</b></td>
				<td align='right'><b><?= number_format($Total1, 2);?></b></td>
				<td><b></b></td>
			</tr>
			<?php
			}
			else{
				echo "<tr>";
					echo "<td colspan='3'>Data empty.</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
</div>


<script>
	$(document).ready(function(){
		swal.close();
	});

</script>
