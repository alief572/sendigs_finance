
<div class="box-body"> 
    <input type="hidden" name='kode_trans' id='kode_trans' value='<?=$getData[0]['kode_trans'];?>'>
	<table width="100%" border='0'>
		<thead>
			<tr>
				<td class="text-left" style='vertical-align:middle;' width='15%'>No Transaksi</td>
				<td class="text-left" style='vertical-align:middle;' width='2%'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?=$getData[0]['kode_trans'];?></td> 
			</tr>
			<tr>
				<td class="text-left" style='vertical-align:middle;'>Tanggal Request</td>
				<td class="text-left" style='vertical-align:middle;'>:</td>
				<td class="text-left" style='vertical-align:middle;'><?=tgl_indo($getData[0]['tanggal']);?></td>
			</tr>
			<tr>
				<td>Ke Costcenter </td>
				<td>: </td>
				<td>
					<?php
						echo $costcenter;
					?>
				</td>
			</tr>
		</thead>
	</table><br>
	<input type="hidden" name='id_costcenter' id='id_costcenter' value='<?=$getData[0]['id_gudang_ke'];?>'>
	<table class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead>
			<tr>
                <th class='text-center' width='3%'>#</th>
                <th class='text-center'>Code</th>
                <th class='text-center'>Material Name</th>
                <th class='text-left'>Costcenter</th>
                <th class='text-center' width='5%'>Unit</th>
                <th class='text-center' width='10%'>Qty</th>
                <th class='text-center' width='10%'>Keterangan</th> 
				<?php
				if($tanda != 'edit'){
					?>
						<th class='text-center' width='8%'>Confirm</th>
                		<th class='text-center' width='9%'>Keterangan</th>
					<?php
				}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
			if(!empty($getDataDetail)){
                $No=0;
                foreach($getDataDetail AS $key => $value){
                    $No++;
                    $id_material 	= $value['id_material'];
                    $nm_material    = (!empty($GET_MATERIAL[$id_material]['nama']))?$GET_MATERIAL[$id_material]['nama']:0;
                    $code_material  = (!empty($GET_MATERIAL[$id_material]['code']))?$GET_MATERIAL[$id_material]['code']:0;
                    $id_packing     = (!empty($GET_MATERIAL[$id_material]['id_packing']))?$GET_MATERIAL[$id_material]['id_packing']:0;
					$id_unit     	= (!empty($GET_MATERIAL[$id_material]['id_unit']))?$GET_MATERIAL[$id_material]['id_unit']:0;
                    $konversi       = (!empty($GET_MATERIAL[$id_material]['konversi']))?$GET_MATERIAL[$id_material]['konversi']:0;
                    $packing        = (!empty($GET_SATUAN[$id_packing]['code']))?$GET_SATUAN[$id_packing]['code']:0;
					$unit        	= (!empty($GET_SATUAN[$id_unit]['code']))?$GET_SATUAN[$id_unit]['code']:0;
					
					$berat_req		= $value['qty_order'];
                    
                    echo "<tr>";
                        echo "<td align='center'>".$No."</td>";
                        echo "<td>".strtoupper($code_material)."</td>";
                        echo "<td>".strtoupper($nm_material)."</td>";
                        echo "<td>".strtoupper($costcenter)."</td>";
                        echo "<td align='center'>".strtoupper($unit)."</td>";
                        if($tanda == 'edit'){
                        echo "<td align='center'>
                                <input type='hidden' name='detail[".$No."][id]' value='".$value['id']."'>
                                <input type='hidden' name='detail[".$No."][id_so]' value='".$value['no_ipp']."'>
                                <input type='hidden' name='detail[".$No."][id_material]' value='".$id_material."'>
                                <input type='hidden' name='detail[".$No."][edit_qty_bef]' data-no='$No' class='form-control input-md text-center autoNumeric2' value='".$berat_req."'>
                                <input type='text' name='detail[".$No."][edit_qty]' data-no='$No' class='form-control input-md text-center autoNumeric2' value='".$berat_req."'>
                            </td>";
                        echo "<td align='center'><input type='text' name='detail[".$No."][keterangan]' data-no='$No' class='form-control input-md text-left' value='".$value['keterangan']."'></td>";
                        }
                        else{
							$berat_con		= $value['check_qty_oke'];
                            echo "<td align='center'>".number_format($berat_req,4)."</td>";
                            echo "<td align='left'>".$value['keterangan']."</td>";
							echo "<td align='center'>".number_format($berat_con,4)."</td>";
                            echo "<td align='left'>".$value['check_keterangan']."</td>";
                        }
                    echo "</tr>";
                }
                ?>
                <?php 
			}
			else{
				echo "<tr>";
					echo "<td colspan='6'><b>Tidak ada data yang ditampilkan !</b></td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
    <?php
		if($tanda == 'edit'){
			echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-primary','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Save','content'=>'Update Request','id'=>'edit_material'));
		}
	?>
</div>
<style>
	.tanggal{
		cursor: pointer;
	}
</style> 
<script>
	$(document).ready(function(){
        swal.close();
		$('.chosen-select').select2({'width':'200px'});
		$('.autoNumeric2').autoNumeric('init', {mDec: '4', aPad: false});
		$('.tanggal').datepicker({
			dateFormat : 'yy-mm-dd',
			changeMonth: true,
			changeYear: true,
			minDate: 0
		});
    });

	function number_format (number, decimals, dec_point, thousands_sep) {
		// Strip all characters but numerical ones.
		number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
		var n = !isFinite(+number) ? 0 : +number,
			prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
			sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
			dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
			s = '',
			toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
		if (s[0].length > 3) {
			s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[1] || '').length < prec) {
			s[1] = s[1] || '';
			s[1] += new Array(prec - s[1].length + 1).join('0');
		}
		return s.join(dec);
	}

</script>