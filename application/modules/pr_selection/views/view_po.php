<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data"> 
<div class="box box-primary" style='margin-right: 17px;'>

	<div class="box-body">		
		<?php
		if(empty($results)){
		}else{
		$no = 0;
		foreach($results AS $val => $valx){ $no++;

			$sql_supplier	= "SELECT a.* FROM master_supplier a where id_supplier='".$valx['id_supplier']."' LIMIT 1";
			$query = $this->db->query($sql_supplier)->result();
			$data_supplier 	= (empty($query))?false:$query[0];
			$sel_local 	= ($data_supplier->supplier_location == 'local')?'selected':'';
			$sel_import = ($data_supplier->supplier_location != 'local')?'selected':'';

			if(!empty($valx['lokasi'])){
				$sel_local 	= ($valx['lokasi'] == 'local')?'selected':'';
				$sel_import = ($valx['lokasi'] == 'import')?'selected':'';
			}

			$alamatSUP = (!empty($valx['alamat_supplier']))?$valx['alamat_supplier']:$data_supplier->address_office;
			$nopo = $valx['no_po'];

			$query 	= "	SELECT 
							a.*
							FROM 
							tran_material_po_detail a 
						WHERE 
							a.no_po='".$valx['no_po']."'
						";
			$res 	= $this->db->query($query)->result_array();
			
			$query_top 	= "	SELECT 
							a.*
							FROM 
							billing_top a 
						WHERE 
							a.no_ipp='$nopo'
						";
			$res_top 	= $this->db->query($query_top)->result_array();
			?>
			<div class='form-group row'>
	        <label for="tgl_po" class="col-sm-2 control-label">NO PO</label>
            <div class='col-sm-2'>			
			<input type='text' name='no_po' class='form-control input-sm' value='<?=$this->uri->segment(3);?>' readonly>	
			</div>
			</div>
			<div class='form-group row'>	
				<label for="tgl_po" class="col-sm-2 control-label">SUPPLIER</label>			
				<div class='col-sm-2'>
				<input type='text' name='supplier' class='form-control input-sm' value='<?=$valx['nm_supplier'];?>' readonly>	
				</div>
				<div class='col-sm-2'>
					<select id='lokasi_<?=$no;?>' name='Header[<?=$no;?>][lokasi]' class='form-control input-sm chosen-select' readonly>
						<option value='local' <?=$sel_local;?>>LOCAL</option>
						<option value='import' <?=$sel_import;?>>IMPORT</option>
					</select>
				</div>
				<div class='col-sm-4'>
					<textarea id='alamat_<?=$no;?>' class='form-control input-md' name='Header[<?=$no;?>][alamat]' rows='3' placeholder='Supplier Address' readonly><?=strtoupper($alamatSUP);?></textarea>
					<input type='hidden' name='Header[<?=$no;?>][id]' class='form-control input-sm' value='<?=$valx['id'];?>'>
				</div>
			</div>
			<?php
			if(empty($res_top)){
			?>
			<div class='form-group row'>
	        <label for="tgl_po" class="col-sm-2 control-label">TOP</label>
            <div class='col-sm-2'>			
			<input type='text' name='progress' readonly class='form-control input-sm' placeholder='PROGRESS' value='<?//=$val3['progress'];?>'>	
			</div>
			<div class='col-sm-2'>			
			<input type='text' name='value' readonly class='form-control input-sm' placeholder='VALUE' value='<?//=$val3['value'];?>'>	
			</div>
			<div class='col-sm-2'>			
			<input type='text' name='jatuh_tempo' class='form-control input-sm' placeholder='JATUH TEMPO' value='<?//=$val3['jatuh_tempo'];?>' readonly>	
			</div>
			<div class='col-sm-4'>			
			<input type='text' name='syarat' class='form-control input-sm' placeholder='KETERANGAN' value='<?//=$val3['syarat'];?>' readonly>	
			</div>
			</div>	
			
			<?php	
			}else{
			$no3 = 0;
			foreach($res_top AS $val3 => $valx3){ $no3++;
			?>
			
			<div class='form-group row'>
	        <label for="tgl_po" class="col-sm-2 control-label">TOP</label>
            <div class='col-sm-2'>			
			<input type='text' name='progress' readonly class='form-control input-sm' placeholder='PROGRESS' value='<?=$valx3['progress'];?>' readonly>	
			</div>
			<div class='col-sm-2'>			
			<input type='text' name='value' readonly class='form-control input-sm' placeholder='VALUE' value='<?=$valx3['value'];?>' readonly>	
			</div>
			<div class='col-sm-2'>			
			<input type='text' name='jatuh_tempo' readonly class='form-control input-sm' placeholder='JATUH TEMPO' value='<?=$valx3['jatuh_tempo'];?>' readonly>	
			</div>
			<div class='col-sm-4'>			
			<input type='text' name='syarat' readonly class='form-control input-sm' placeholder='KETERANGAN' value='<?=$valx3['syarat'];?>' readonly>	
			</div>
			</div>
			<?php
			
			}
			
			}
			?>
			
			
			<div class='form-group row'>		 	 
				<div class='col-sm-12'>
					<table class="table table-bordered table-striped" width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class="text-center" width='50%'>Nama Aset</th>
								<th class="text-center" width='10%'>Price From Supplier</th>
								<th class="text-center" width='10%'>Qty Purchase </th>
								<th class="text-center" width='10%'>Total </th>
								<!-- <th class="text-center" width='10%'>Tanggal Dibutuhkan</th>								
								// <th class="text-center" width='10%'>Moq </th>
								// <th class="text-center" width='10%'>Top </th>
								// <th class="text-center">Keterangan </th>-->
							
							</tr>
						</thead>
					<tbody>
						<?php
						$no2 = 0;
						foreach($res AS $val2 => $valx2){ $no2++;
						
						    $subtotal = $valx2['qty_purchase'] * $valx2['price_ref_sup'];
							echo "<tr>";
								echo "<td><input type='hidden' name='Detail[".$no."][detail][".$no2."][id]' class='form-control text-right input-sm maskM' value='".$valx2['id']."'>
								".$valx2['nm_material']."</td>";
								echo "<td align='right'><input type='text' name='Detail[".$no."][detail][".$no2."][price_ref_sup]' class='form-control text-right input-sm maskM' value='".$valx2['price_ref_sup']."' readonly>
								<input type='hidden' id='harga_".$no2."' name='Detail[".$no."][detail][".$no2."][price_ref_sup2]' class='form-control text-right input-sm' value='".$valx2['price_ref_sup']."' readonly></td>";
                                echo "<td align='right'><input type='text' id='qty_".$no2."' name='Detail[".$no."][detail][".$no2."][qty_purchase]' class='form-control text-right input-sm' value='".$valx2['qty_purchase']."' onkeyup='qty(".$no2.")' readonly></td>";
								echo "<td align='right'><input type='text' id='hargatotal_".$no2."' name='Detail[".$no."][detail][".$no2."][total]' class='form-control text-right input-sm maskM' value='".$subtotal."' readonly></td>";
                                
								// echo "<td align='center'>
								// <input type='text' name='Detail[".$no."][detail][".$no2."][tgl_dibutuhkan]' class='form-control input-sm' value='".$valx2['tgl_dibutuhkan']."'>
								// <input type='hidden' name='Detail[".$no."][detail][".$no2."][id]' class='form-control input-sm' value='".$valx2['id']."'>
												
										// </td>";
								// echo "<td align='left'><input type='text' name='Detail[".$no."][detail][".$no2."][moq]' class='form-control text-right input-sm' value='".$valx2['moq']."'></td>";
								// echo "<td align='left'><input type='text' name='Detail[".$no."][detail][".$no2."][top]' class='form-control text-right input-sm' value='".$valx2['top']."'></td>";
								// echo "<td align='left'><input type='text' name='Detail[".$no."][detail][".$no2."][keterangan]' class='form-control text-right input-sm' value='".$valx2['keterangan']."'></td>";
								
								
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
				</div>
			</div>
		<?php } 
		}?>
		<?php
			echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-danger','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','content'=>'Back','id'=>'back')).' ';
			// echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-success','style'=>'min-width:100px; float:right; margin: 5px 5px 5px 0px;','value'=>'Create','content'=>'Save','id'=>'save')).' ';
		?>
	</div>
 </div>
  <!-- /.box -->

</form>

<style>
	.chosen-container-active .chosen-single {
	     border: none;
	     box-shadow: none;
	}
	.chosen-container-single .chosen-single {
		height: 34px;
	    border: 1px solid #d2d6de;
	    border-radius: 0px;
	     background: none;
	    box-shadow: none;
	    color: #444;
	    line-height: 32px;
	}
	.chosen-container-single .chosen-single div{
		top: 5px;
	}
</style>
<script>

    $(document).ready(function() {
//      $(".maskM").divide();
    });

	$(document).on('click', '#back', function(e){
		window.location.href = siteurl+"pr_selection/purchase_order";
	});

</script>
