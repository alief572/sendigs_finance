
<div class="box-body"> 
	<div class='form-group row'>		 	 
		<label class='label-control col-sm-2'><b>Supplier Name</b></label>
		<div class='col-sm-4'>              
			<select id='id_supplier' name='id_supplier[]' class='form-control input-sm chosen-select' multiple>
				<?php
					foreach($supplierList AS $val => $valx){
						$sel3 = '';
						if(!empty($supplierChecked)){
							$sel3 = (isset($supplierChecked) && in_array($valx['id_supplier'], $supplierChecked))?'selected':'';
						}
						echo "<option value='".$valx['id_supplier']."' ".$sel3.">".strtoupper($valx['nm_supplier'])."</option>";
					}
				?>
			</select>
		</div>
	</div><br>
	<input type="hidden" name='no_rfq' value='<?=$no_rfq;?>'>
	<input type="hidden" name='category' value='<?=$result[0]['category'];?>'>
	<table id="my-grid" class="table table-striped table-bordered table-hover table-condensed" width="100%">
		<thead id='head_table'>
			<tr class='bg-blue'>
				<th class="text-center no-sort" width='5%'>#</th>
				<th class="text-center" width='10%'>No PR</th> 
				<th class="text-center" width='10%'>Tanggal PR</th>
				<th class="text-center">Nama Barang</th>
				<th class="text-center" width='10%'>Qty</th>
				<th class="text-center" width='10%'>Tgl Dibutuhkan</th>
				<th class="text-center" width='15%'>Spec</th>
				<th class="text-center no-sort" width='7%'>#</th>
			</tr>
		</thead>
		<tbody>
			<?php
            $No=0;
			foreach($result AS $val => $valx){
                $No++; 
				
				echo "<tr>";
                    echo "<td align='center'>".$No."</td>";
                    echo "<td align='center'>".$valx['no_pr']."</td>";
					echo "<td align='center'>".date('d-M-Y', strtotime($valx['tgl_pr']))."</td>";
					echo "<td align='left'>".strtoupper($valx['nm_barang'])."</td>";
					echo "<td align='center'>".number_format($valx['qty'])."</td>";
					echo "<td align='center'>".date('d-M-Y', strtotime($valx['tgl_dibutuhkan']))."
							<input type='hidden' name='check[".$No."]' value='".$valx['no_pr']."'>
						  </td>";
					echo "<td align='left'><input type='text' class='form-control changeSpec' data-no_pr='".$valx['no_pr']."' data-no_rfq='".$valx['no_rfq']."' value='".$valx['spec']."'></td>";
					echo "<td align='center'><button type='button' class='btn btn-sm btn-danger delMat' title='Delete' data-no_pr='".$valx['no_pr']."' data-no_rfq='".$valx['no_rfq']."' data-id_barang='".$valx['id_barang']."'>Delete</button></td>";
				echo "</tr>";
			}
			?>
		</tbody>
	</table>
	<?php
        echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-success','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Update','content'=>'Update','id'=>'updatePur')).' ';
    ?>
</div>
<style>
	.chosen-container{
		width: 100% !important;
		text-align : left !important;
	}
</style>
<script>
	$(document).ready(function(){
		swal.close();
		$('.chosen-select').chosen();
	});

</script>