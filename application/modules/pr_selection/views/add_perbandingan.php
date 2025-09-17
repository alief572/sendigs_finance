<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data"> 
<div class="box box-primary" style='margin-right: 17px;'>
	
	<div class="box-body">
		<input type='hidden' name='no_rfq' class='form-control input-sm' value='<?=$this->uri->segment(3);?>'>
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
			$query 	= "	SELECT 
							a.*
							FROM 
							tran_material_rfq_detail a 
						WHERE 
							a.hub_rfq='".$valx['hub_rfq']."'
						GROUP BY a.id_material
							";
			$res 	= $this->db->query($query)->result_array();
			
			// print_r($res);
			// exit;
			
			?>
			<div class='form-group row'>		 	 
				<label class='label-control col-sm-6'><b style='font-size: 16px;'><?=$no.'. '.$valx['nm_supplier'];?></b></label>
			</div>
			<div class='form-group row'>		 	 
				<div class='col-sm-2'>
					<select id='lokasi_<?=$no;?>' name='Header[<?=$no;?>][lokasi]' class='form-control input-sm chosen-select'>
						<option value='local' <?=$sel_local;?>>LOCAL</option>
						<option value='import' <?=$sel_import;?>>IMPORT</option>
					</select>
				</div>
			</div>
			<div class='form-group row'>		 	 
				<div class='col-sm-6'>
					<textarea id='alamat_<?=$no;?>' class='form-control input-md' name='Header[<?=$no;?>][alamat]' rows='3' placeholder='Supplier Address'><?=strtoupper($alamatSUP);?></textarea>
					<input type='hidden' name='Header[<?=$no;?>][id]' class='form-control input-sm' value='<?=$valx['id'];?>'>
				</div>
			</div>
			<div class='form-group row'>		 	 
				<div class='col-sm-12'>
					<table class="table table-bordered table-striped" width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class="text-center">Nama Aset</th>
								<th class="text-center" width='10%'>Price From Supplier</th>
								<th class="text-center" width='10%'>Leadtime </th>
								<th class="text-center" width='10%'>Qty PR </th>
								<th class="text-center" width='10%'>Tanggal Dibutuhkan</th>
								<th class="text-center">Keterangan </th>
							
							</tr>
						</thead>
					<tbody>
						<?php
						$no2 = 0;
						foreach($res AS $val2 => $valx2){ $no2++;
							echo "<tr>";
								echo "<td>".$valx2['nm_material']."</td>";
								echo "<td align='right'><input type='text' name='Detail[".$no."][detail][".$no2."][price_ref_sup]' class='form-control text-right input-sm maskM' value='".$valx2['price_ref_sup']."'></td>";
								echo "<td align='right'><input type='text' name='Detail[".$no."][detail][".$no2."][leadtime]' class='form-control text-right input-sm' value='".$valx2['lead_time']."'></td>";
								echo "<td align='right'>".number_format($valx2['qty'])."</td>";
								echo "<td align='center'>
								<input type='text' name='Detail[".$no."][detail][".$no2."][tgl_dibutuhkan]' class='form-control input-sm' value='".$valx2['tgl_dibutuhkan']."'>
								<input type='hidden' name='Detail[".$no."][detail][".$no2."][id]' class='form-control input-sm' value='".$valx2['id']."'>
												
										</td>";
								echo "<td align='left'><input type='text' name='Detail[".$no."][detail][".$no2."][keterangan]' class='form-control text-right input-sm' value='".$valx2['keterangan']."'></td>";
								
								
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
			echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-success','style'=>'min-width:100px; float:right; margin: 5px 5px 5px 0px;','value'=>'Create','content'=>'Save','id'=>'save')).' ';
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
      $(".maskM").divide();
    });

	$(document).on('click', '#back', function(e){
		window.location.href = siteurl+"pr_selection";
	});
	$(document).on('click', '#save', function(e){
		e.preventDefault();

		swal({ 
			title: "Are you sure?",
			text: "You will save be able to process again this data!",
			type: "warning",
			showCancelButton: true,
			confirmButtonClass: "btn-danger",
			confirmButtonText: "Yes, Process it!",
			cancelButtonText: "No, cancel process!",
			closeOnConfirm: false,
			closeOnCancel: false
		},
		function(isConfirm) {
			if (isConfirm) {
				var formData  	= new FormData($('#form_proses_bro')[0]);
				$.ajax({
					url: siteurl+"pr_selection/save_perbandingan",
					type		: "POST",
					data		: formData,
					cache		: false,
					dataType	: 'json',
					processData	: false, 
					contentType	: false,				
					success		: function(data){								
						if(data.status == 1){											
							swal({
									title	: "Save Success!",
									text	: data.pesan,
									type	: "success",
									timer	: 7000,
									showCancelButton	: false,
									showConfirmButton	: false,
									allowOutsideClick	: false
								});
							window.location.href = siteurl+"pr_selection";
						}
						else if(data.status == 0){
							swal({
								title	: "Save Failed!",
								text	: data.pesan,
								type	: "warning",
								timer	: 7000,
								showCancelButton	: false,
								showConfirmButton	: false,
								allowOutsideClick	: false
							});
						}
					},
					error: function() {
						swal({
							title				: "Error Message !",
							text				: 'An Error Occured During Process. Please try again..',						
							type				: "warning",								  
							timer				: 7000,
							showCancelButton	: false,
							showConfirmButton	: false,
							allowOutsideClick	: false
						});
					}
				});
			} else {
			swal("Cancelled", "Data can be process again :)", "error");
			return false;
			}
		});
	});

</script>