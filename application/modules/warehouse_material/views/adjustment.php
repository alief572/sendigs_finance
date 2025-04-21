
<form action="#" method="POST" id="form_adjustment" enctype="multipart/form-data" autocomplete='off'>
<div class="box-body">
	<br>
    <input type="hidden" name='no_po' id='no_po' value='<?= $results['no_po'];?>'>
    <input type="hidden" name='gudang' id='gudang' value='<?= $results['gudang'];?>'>
    <input type="hidden" name='adjustment' id='adjustment' value='IN'>
	<table class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr>
				<th class="text-center" style='vertical-align:middle;' width='5%'>#</th>
        <th class="text-center" style='vertical-align:middle;'>Material ID</th>
				<th class="text-center" style='vertical-align:middle;'>Material Name</th>
        <th class="text-center" style='vertical-align:middle;'>Qty Packing</th>
				<th class="text-center" style='vertical-align:middle;' width='9%'>Qty Material</th>
        <th class="text-center" style='vertical-align:middle;' width='9%'>Qty Oke Packing</th>
        <th class="text-center" style='vertical-align:middle;' width='9%'>Qty Oke</th>
        <!-- <th class="text-center" style='vertical-align:middle;' width='9%'>Receiving Oke All</th> -->
        <th class="text-center" style='vertical-align:middle;' width='9%'>Qty Damaged Packing</th>
        <th class="text-center" style='vertical-align:middle;' width='9%'>Qty Damaged</th>
        <th class="text-center" style='vertical-align:middle;' width='9%'>Tanda Terima</th>
				<th class="text-center" style='vertical-align:middle;' width='9%'>Surat Jalan</th>
			</tr>
		</thead>
		<tbody>
			<?php
			if($results['qBQdetailNum'] != 0){
            $Total1 = 0;
            $No=0;
			foreach($results['qBQdetailRest'] AS $val => $valx){
                $No++;
                $Total1 += $valx['qty'] - $valx['qty_in'];

                $totIn = $valx['qty'] - $valx['qty_in'];
                $read = ($valx['qty'] - $valx['qty_in'] < 1)?'readonly':'';
				echo "<tr>";
            echo "<td align='center'>".$No."
                <input type='hidden' name='addInMat[$No][no_po]' value='".$valx['no_po']."'>
                <input type='hidden' name='addInMat[$No][id]' value='".$valx['id']."'>
                <input type='hidden' name='addInMat[$No][qty]' value='".$valx['qty']."'>
            </td>";
            echo "<td>".$valx['idmaterial']."</td>";
            echo "<td>".strtoupper($valx['nm_material'])."</td>";
            echo "<td align='right'>".number_format($valx['qty_packing'] - $valx['qty_in_packing'],2)." ".ucfirst($valx['satuan_packing'])."</td>";
            echo "<td align='right'>".number_format($valx['qty'] - $valx['qty_in'],2)." ".ucfirst($valx['unit'])."</td>";
            echo "<td align='center'>
										<input type='text' name='addInMat[$No][qty_in_pack]' ".$read." data-no='$No' data-konversi='".$valx['konversi']."' class='form-control input-sm numberOnly qty_in'>
										<input type='hidden' name='addInMat[$No][qty_sebelum]' readonly data-no='$No' class='form-control input-sm numberOnly qty_sebelum_".$No."' value='".number_format($valx['qty_packing'] - $valx['qty_in_packing'],2)."'>
										</td>";
            echo "<td align='center'><input type='text' id='qty_in_".$No."' name='addInMat[$No][qty_in]' data-no='$No' class='form-control input-sm numberOnly' readonly></td>";
            // echo "<td align='center'><input type='checkbox' name='addInMat[$No][complete]' value='Y'></td>";
            echo "<td align='center'><input type='text' name='addInMat[$No][qty_rusak_pack]' ".$read." data-no='$No' data-konversi='".$valx['konversi']."' class='form-control input-sm numberOnly qty_rusak'></td>";
            echo "<td align='center'><input type='text' id='qty_rusak_".$No."' name='addInMat[$No][qty_rusak]' data-no='$No' class='form-control input-sm numberOnly' readonly></td>";
            echo "<td align='center'><input type='text' name='addInMat[$No][tanda_terima]' data-no='$No' class='form-control input-sm'></td>";
						echo "<td align='center'><input type='text' name='addInMat[$No][surat_jalan]' data-no='$No' class='form-control input-sm'></td>";

				echo "</tr>";
			}
			?>
			<!-- <tr>
				<td><b></b></td>
				<td colspan='3'><b>SUM TOTAL</b></td>
				<td align='right'><b><?= number_format($Total1, 2);?> Kg</b></td>
        <td colspan='4'><b></b></td>
			</tr> -->
			<?php
			}
			else{
				echo "<tr>";
					echo "<td colspan='3'>Data aktual belum di update, silahkan update data terlebih dahulu.</td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
	<input type="hidden" id="max_no" value="<?=$No;?>">
    <?php
		echo form_button(array('type'=>'button','class'=>'btn btn-md btn-success','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Save','content'=>'Save','id'=>'saveINMaterial')).' ';
	?>
</div>
</form>
<style>
    .numberOnly{
        text-align:right;
        width: 150px;
    }
</style>
<script>
	$(document).ready(function(){
    swal.close();

    $(".numberOnly").on("keypress keyup blur",function (event) {
        if ((event.which < 48 || event.which > 57 ) && (event.which < 46 || event.which > 46 )) {
            event.preventDefault();
        }
    });

  });

  function getNum(val) {
    if (isNaN(val) || val == '') {
        return 0;
    }
    return parseFloat(val);
  }
</script>
