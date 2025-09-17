
<form action="#" method="POST" id="form_adjustment" enctype="multipart/form-data" autocomplete='off'>
<div class="box-body">
	<br>
    <input type="hidden" name='gudang_before' id='gudang_before' value='<?= $gudang_before;?>'>
	<input type="hidden" name='gudang_after' id='gudang_after' value='<?= $gudang_after;?>'>
	<?php
		if(!empty($result)){
			echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Save','content'=>'Save','id'=>'request_material'));
		}
	?>
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center" style='vertical-align:middle;' width='5%'>No</th>
				<th class="text-center" style='vertical-align:middle;'>Material Name</th>
				<th class="text-center" style='vertical-align:middle;' width='10%'>Dipesan Project (Kg)</th>
                <th class="text-center" style='vertical-align:middle;' width='10%'>Request</th>
				<th class="text-center" style='vertical-align:middle;' width='15%'>Keterangan</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if(!empty($result)){
            $No=0;
			foreach($result AS $val => $valx){
                $No++;
               echo "<tr>";
                    echo "<td align='center'>".$No."
                        <input type='hidden' name='detail[$No][id]' value='".$valx['id']."'>
                    </td>";
                    echo "<td>".strtoupper($valx['nm_material'])."</td>";
					echo "<td align='right'>".number_format($valx['qty_booking'],2)."</td>";
                    echo "<td align='center'><input type='text' name='detail[$No][sudah_request]' data-no='$No' class='form-control input-sm text-right maskM'></td>";
					echo "<td align='center'><input type='text' name='detail[$No][ket_request]' data-no='$No' class='form-control input-sm text-left'></td>";
                echo "</tr>";
			}
			?>
			<?php
			}
			else{
				echo "<tr>";
					echo "<td colspan='5'><b>Material tidak berada di gudang yang dipilih, silahkan coba gudang yang lain.</b></td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
    <?php
		if(!empty($result)){
			echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Save','content'=>'Save','id'=>'request_material'));
		}
	?>
</div>
</form>
<style>
	.tanggal{
		cursor: pointer;
	}
</style>
<script>
	$(document).ready(function(){
        swal.close();
		$('.maskM').maskMoney();
		$('.tanggal').datepicker({
			dateFormat : 'yy-mm-dd',
			changeMonth: true,
			changeYear: true
		});
    });
</script>
