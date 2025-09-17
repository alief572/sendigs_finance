<?php

$no_bom          = (!empty($header))?$header[0]->no_bom:'';
$id_product      = (!empty($header))?$header[0]->id_product:'';
$waste_product   = (!empty($header))?$header[0]->waste_product:'';
$waste_setting   = (!empty($header))?$header[0]->waste_setting:'';
$variant_product   	= (!empty($header))?$header[0]->variant_product:'';
$width   			= (!empty($header))?$header[0]->width:'';
$length   			= (!empty($header))?$header[0]->length:'';

// print_r($header);
?>

 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post"><br>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Product Master <span class='text-red'>*</span></label>
				</div>
				<div class="col-md-6">
					<input type="hidden" name="no_bom" value="<?=$no_bom;?>">
					<select id="id_product" name="id_product" class="form-control input-md chosen-select" required>
						<option value="0">Select An Option</option>
						<?php foreach ($results['product'] as $product){
						$sel = ($product->code_lv4 == $id_product)?'selected':'';
						?>
						<option value="<?= $product->code_lv4;?>" <?=$sel;?>><?= strtoupper(strtolower($product->nama))?></option>
						<?php } ?>
					</select>
				</div>
			</div>
			<br>
			<div class='box box-info'>
				<div class='box-header'>
					<h3 class='box-title'>Detail Material</h3>
					<div class='box-tool pull-right'>
						<!--<button type='button' data-id='frp_".$a."' class='btn btn-md btn-info panelSH'>SHOW</button>-->
					</div>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 40%;'>Material Name</th>
								<th class='text-center'>Weight /kg</th>
								<th class='text-center'>Keterangan</th>
								<th class='text-center' style='width: 15%;'>SPK</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody id='body-detail'>
							<?php
							$no = 0;
							if(!empty($detail)){
								foreach ($detail as $key => $value) { 
									$no += 100; 
									$key++;
									echo "<tr>";
										echo "<td align='center'>".$key."</td>";
										echo "<td align='left' colspan='5'>".strtoupper($value['nm_process']);
											echo "<input type='hidden' name='Detail[".$no."][nm_process]' value='".$value['nm_process']."'>";
										echo "</td>";
									echo "</tr>";
									$BOM_DET = $no_bom.'-'.$no;
									$detailMaterial = $this->db->get_where('bom_detail',array('no_bom_detail' => $BOM_DET,'category' => 'default'))->result_array();
									
									$idsts = $no;
									$Numx = $idsts;
									foreach ($detailMaterial as $key2 => $value2) {
										$id = $Numx + 1;
										echo "<tr class='header_".$id."'>";
											echo "<td align='center'></td>";
											echo "<td align='left'>";
												echo "<select name='Detail[".$idsts."][detail][".$id."][code_material]' class='chosen_select form-control input-sm inline-blockd material'>";
												echo "<option value='0'>Select Material Name</option>";
												foreach($material AS $valx){
													$selected = ($valx->code_lv4 == $value2['code_material'])?'selected':'';
													echo "<option value='".$valx->code_lv4."' ".$selected.">".strtoupper($valx->nama)."</option>";
												}
												echo "</select>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<input type='text' name='Detail[".$idsts."][detail][".$id."][weight]' class='form-control input-md autoNumeric4 qty' placeholder='Weight' value='".$value2['weight']."'>";
											echo "</td>";
											echo "<td align='left'>";
												echo "<input type='text' name='Detail[".$idsts."][detail][".$id."][ket]' class='form-control input-md' placeholder='Keterangan' value='".$value2['ket']."'>";
											echo "</td>";
											echo "<td align='left'>";
												$mix = ('mixing' == $value2['spk'])?'selected':'';
												$nonmix = ('non-mixing' == $value2['spk'])?'selected':'';
												echo "<select name='Detail[".$idsts."][detail][".$id."][spk]' class='chosen_select form-control input-sm inline-blockd spkMaterial'>";
												echo "<option value='mixing' ".$mix.">Mixing</option>";
												echo "<option value='non-mixing' ".$nonmix.">Non-Mixing</option>";
											echo "</select>";
											echo "</td>";
											echo "<td align='center'>";
												echo "<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
											echo "</td>";
										echo "</tr>";

										$Numx = $id;
									}
						
									//add part
									echo "<tr id='add_".$Numx."'>";
										echo "<td align='center'></td>";
										echo "<td align='left'><button type='button' class='btn btn-sm btn-warning addPart' data-idsts='".$idsts."' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
										echo "<td align='center'></td>";
									echo "</tr>";
								}
							}
							else{
								?>
								<tr>
									<td colspan='6'>Pilih master product terlebih dahulu.</td>
								</tr>
								<?php
							}
							?>
							
						</tbody>
					</table>
				</div>
			</div>

			<div class='box box-warning'>
				<div class='box-header'>
					<h3 class='box-title'>Accessories</h3>
					<div class='box-tool pull-right'>
					</div>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 40%;'>Accessories Name</th>
								<th class='text-center'>Qty</th>
								<th class='text-center'>Keterangan</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if(!empty($detail_acc)){
								foreach($detail_acc AS $val => $valx){ $val++;
									echo "<tr class='headeraccessories_".$val."'>";
									echo "<td align='center'>".$val."</td>";
									echo "<td align='left'>";
									echo "<select name='DetailAcc[".$val."][code_material]' class='chosen-select form-control input-sm inline-blockd'>";
									echo "<option value='0'>Select Accessories</option>";
									foreach($accessories AS $valx4){
										$sel2 = ($valx4->id == $valx['code_material'])?'selected':'';
										echo "<option value='".$valx4->id."' ".$sel2.">".strtoupper($valx4->stock_name.' '.$valx4->brand.' '.$valx4->spec)."</option>";
									}
									echo 		"</select>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<input type='text' name='DetailAcc[".$val."][weight]' class='form-control input-md autoNumeric4 qty' placeholder='Qty' value='".$valx['weight']."'>";
									echo "</td>";
									echo "<td align='left'>";
										echo "<input type='text' name='DetailAcc[".$val."][ket]' class='form-control input-md' placeholder='Keterangan' value='".$valx['ket']."'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
									echo "</td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='addaccessories_<?=$val?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPartAcc' title='Add Accessories'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Accessories</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
			<button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>
		</form>
	</div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style media="screen">
  .datepicker{
    cursor: pointer;
    padding-left: 12px;
  }
</style>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen_select').select2();
		$( ".datepicker" ).datepicker();
		$( ".autoNumeric4" ).autoNumeric('init', {mDec: '4', aPad: false});


		$(document).on('change', '#id_product', function(){
			// loading_spinner();
			let id_product = $(this).val()

			if(id_product != '0'){
				$.ajax({
					url: base_url+active_controller+'/get_process/'+id_product,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data){
						$("#body-detail").html(data.header);
						swal.close();
					},
					error: function() {
						swal({
							title	: "Error Message !",
							text	: 'Connection Time Out. Please try again..',
							type	: "warning",
							timer	: 3000
						});
					}
				});
			}
			else{
				$("#body-detail").html('');
			}
		});

		//add part
		$(document).on('click', '.addPart', function(){
			// loading_spinner();
			var get_id 		= $(this).parent().parent().attr('id');
			var id_sts 		= $(this).data('idsts');
			// console.log(get_id);
			var split_id	= get_id.split('_');
			var id 			= parseInt(split_id[1])+1;
			var id_bef 		= split_id[1];

			console.log(get_id);
			console.log(id);
			console.log(id_sts);
			console.log(id_bef);

			$.ajax({
				url: base_url+active_controller+'/get_add/'+id+'/'+id_sts,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#add_"+id_bef).before(data.header);
					$("#add_"+id_bef).remove();
					$('.chosen_select').select2({width: '100%'});
					$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false});
					swal.close();
				},
				error: function() {
					swal({
						title				: "Error Message !",
						text				: 'Connection Time Out. Please try again..',
						type				: "warning",
						timer				: 3000,
						showCancelButton	: false,
						showConfirmButton	: false,
						allowOutsideClick	: false
					});
				}
			});
		});

		$(document).on('click', '.addPartAcc', function(){
			// loading_spinner();
			var get_id 		= $(this).parent().parent().attr('id');
			// console.log(get_id);
			var split_id	= get_id.split('_');
			var id 		= parseInt(split_id[1])+1;
			var id_bef 	= split_id[1];

			$.ajax({
				url: base_url+active_controller+'/get_add_accessories/'+id,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#addaccessories_"+id_bef).before(data.header);
					$("#addaccessories_"+id_bef).remove();
					$('.chosen_select').select2();
					$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false});
					swal.close();
				},
				error: function() {
					swal({
						title				: "Error Message !",
						text				: 'Connection Time Out. Please try again..',
						type				: "warning",
						timer				: 3000,
						showCancelButton	: false,
						showConfirmButton	: false,
						allowOutsideClick	: false
					});
				}
			});
		});

	   	//delete part
		$(document).on('click', '.delPart', function(){
			var get_id 		= $(this).parent().parent().attr('class');
			$("."+get_id).remove();
		});

    	//add part
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller;
		});

		$('#save').click(function(e){
			e.preventDefault();
			var id_product		  	= $('#id_product').val();
			if(id_product == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Product name empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}

			swal({
				  title: "Are you sure?",
				  text: "You will not be able to process again this data!",
				  type: "warning",
				  showCancelButton: true,
				  confirmButtonClass: "btn-danger",
				  confirmButtonText: "Yes, Process it!",
				  cancelButtonText: "No, cancel process!",
				  closeOnConfirm: true,
				  closeOnCancel: false
				},
				function(isConfirm) {
				  if (isConfirm) {
						var formData 	=new FormData($('#data-form')[0]);
						var baseurl = base_url+active_controller+'/add'
						$.ajax({
							url			: baseurl,
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
										timer	: 3000
									});
									window.location.href = base_url + active_controller;
								}
								else{
									if(data.status == 2){
										swal({
										  	title	: "Save Failed!",
										  	text	: data.pesan,
										  	type	: "warning",
										  	timer	: 3000
										});
									}else{
										swal({
										  	title	: "Save Failed!",
										  	text	: data.pesan,
										  	type	: "warning",
										  	timer	: 3000
										});
									}
								}
							},
							error: function() {
								swal({
								  	title	: "Error Message !",
								  	text	: 'An Error Occured During Process. Please try again..',
								  	type	: "warning",
								  	timer	: 7000
								});
							}
						});
				  } else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				  }
			});
		});

		$(document).on('change', '.materialChange', function(){
			// loading_spinner();
			let id_material = $(this).val()
			let htmlTable = $(this).parent().parent().find('.spkMaterial');
			// console.log(htmlTable)

			if(id_material != '0'){
				$.ajax({
					url: base_url+active_controller+'/get_detail_spk_material/'+id_material,
					cache: false,
					type: "POST",
					dataType: "json",
					success: function(data){
						htmlTable.html(data.header);
					},
					error: function() {
						swal({
							title	: "Error Message !",
							text	: 'Connection Time Out. Please try again..',
							type	: "warning",
							timer	: 3000
						});
					}
				});
			}
		});

});

</script>
