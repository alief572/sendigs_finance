

<div class="box-body">
	<br>
	<input type="hidden" name='no_po' value='<?=$this->uri->segment(3);?>'>
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center" style='vertical-align:middle;' width='5%'>#</th>
                <th class="text-center" style='vertical-align:middle;' width='20%'>Id Material</th>
				<th class="text-center" style='vertical-align:middle;'>Material Name</th>
				<th class="text-center" style='vertical-align:middle;' width='9%'>Qty Packing</th>
				<th class="text-center" style='vertical-align:middle;' width='9%'>Qty Material</th>
				<th class="text-center" style='vertical-align:middle;' width='8%'>#</th>
			</tr>
		</thead>
		<tbody>
			<?php
            $Total1 = 0;
            $No=0;
			foreach($qBQdetailRest AS $val => $valx){
                $No++;
				$Total1 += $valx['qty'];

				echo "<tr>";
          echo "<td style='vertical-align:middle;' align='center'>".$No."</td>";
          echo "<td style='vertical-align:middle;'>".$valx['idmaterial']."</td>";
					echo "<td style='vertical-align:middle;'>".strtoupper($valx['nm_material'])."</td>";
					echo "<td align='right'>
						<input type='text' style='text-align:right;' id='packing_".$No."' name='ListPur[".$No."][qty_packing]' data-no='".$No."' data-konversi='".$valx['konversi']."' class='form-control input-md numberOnly qty maskM' value='".floatval($valx['qty_packing'])."'>
					</td>";
					echo "<td align='right'>
						<input type='hidden' style='text-align:center;' name='ListPur[".$No."][id]' class='form-control input-sm' value='".$valx['id']."'>
						<input type='text' style='text-align:right;' id='qty_".$No."' name='ListPur[".$No."][qty]' class='form-control input-md numberOnly maskM' readonly value='".floatval($valx['qty'])."'>
					</td>";
					echo "<td align='center'><button type='button' class='btn btn-sm btn-danger delMat' title='Total Material Purchase' data-id='".$valx['id']."' data-no_po='".$valx['no_po']."' data-id_material='".$valx['id_material']."'>Delete</button></td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
	<?php
        echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-success','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Update','content'=>'Update','id'=>'updatePur')).' ';
    ?>
</div>


<script>
	$(document).ready(function(){
		swal.close();

		$(document).on('keyup', '.qty', function(e){
			var no       = $(this).data('no');
			var konversi = getNum($(this).data('konversi'));
			var value    = getNum($(this).val());
			var hasil    = konversi * value;
			$("#qty_"+no).val(hasil.toFixed(2));
		});
	});

</script>
