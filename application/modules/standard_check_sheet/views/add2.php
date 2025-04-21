<?php
$id 		= (!empty($header[0]['id']))?$header[0]['id']:'';
$code_lv4 	= (!empty($header[0]['code_lv4']))?$header[0]['code_lv4']:'';
$id_mesin 	= (!empty($header[0]['id_mesin']))?$header[0]['id_mesin']:'';
$GET_VALUE 	= getValueChecksheet($code_lv4);
// echo "<pre>";
// print_r($GET_VALUE);
// exit;
?>
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Product <span class="text-red">*</span></label>
				</div>
				<div class="col-md-10">
					<select id="id_product" name="id_product" class="form-control input-md chosen-select">
							<?php 
							if(empty($id)){
								echo "<option value='0'>Select An Product</option>";
								foreach (get_list_inventory_lv4('product') AS $val => $valx){ 
									if (!in_array($valx['code_lv4'], $ArrProductCT)) {
									?>
									<option value="<?= $valx['code_lv4'];?>"><?= strtoupper($valx['nama']);?></option>
									<?php 
									}
								} 
							}
							else{
								foreach (get_list_inventory_lv4('product') AS $val => $valx){ 
									if ($valx['code_lv4'] == $code_lv4) {
									?>
									<option value="<?= $valx['code_lv4'];?>"><?= strtoupper($valx['nama']);?></option>
									<?php 
									}
								} 
							}
							?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Machine <span class="text-red">*</span></label>
				</div>
				<div class="col-md-3">
					<select id="id_mesin" name="id_mesin" class="form-control input-md chosen-select">
						<option value="0">Pilih Machine</option>
						<?php
                        foreach ($listMachine AS $val => $valx){
							$selected = ($valx['id'] == $id_mesin)?'selected':'';
                            ?>
                            <option value="<?= $valx['id'];?>" <?=$selected;?>><?= strtoupper($valx['nm_asset']);?></option>
                            <?php
                        } 
                        ?>
					</select>
				</div>
			</div>
            <div class="form-group row">
				<div class="col-md-6">
                    <h4>Surfacing Veil</h4>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' width='50%'>#</th>
								<th class='text-center'>Atas</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if(!empty($listSurface)){
								foreach($listSurface AS $val => $valx){ 
                                    $val++;
									$idValue = (!empty($GET_VALUE[$valx['id']]['id']))?$GET_VALUE[$valx['id']]['id']:'';
									$atasValue = (!empty($GET_VALUE[$valx['id']]['surface']))?$GET_VALUE[$valx['id']]['surface']:'';
									echo "<tr>";
                                        echo "<td align='center'>".$valx['nama']."
												<input type='hidden' name='DetailSurface[".$val."][id]' value='".$idValue."'>
												<input type='hidden' name='DetailSurface[".$val."][id_checksheet]' value='".$valx['id']."'>
												</td>";
                                        echo "<td align='center'><input type='text' class='form-control input-sm' name='DetailSurface[".$val."][atas]' value='".$atasValue."'></td>";
                                  echo "</tr>";
								}
							}
							?>
						</tbody>
					</table>
				</div>
                <div class="col-md-6">
                    <h4>Rooving</h4>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' width='50%'>#</th>
								<th class='text-center'>Pemakaia Aktual</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if(!empty($listRooving)){
								foreach($listRooving AS $val => $valx){ 
                                    $val++;

									$idValue = (!empty($GET_VALUE[$valx['id']]['id']))?$GET_VALUE[$valx['id']]['id']:'';
									$pemakaianValue = (!empty($GET_VALUE[$valx['id']]['rooving']))?$GET_VALUE[$valx['id']]['rooving']:'';

									echo "<tr>";
                                        echo "<td align='center'>".$valx['nama']."
												<input type='hidden' name='DetailRooving[".$val."][id]' value='".$idValue."'>
												<input type='hidden' name='DetailRooving[".$val."][id_checksheet]' value='".$valx['id']."'>
												</td>";
                                        echo "<td align='center'><input type='text' class='form-control input-sm' name='DetailRooving[".$val."][pemakaian]' value='".$pemakaianValue."'></td>";
                                  echo "</tr>";
								}
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
            <div class="form-group row">
				<div class="col-md-12">
                    <h4>Matt</h4>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' width='25%'>#</th>
								<th class='text-center'>Atas</th>
								<th class='text-center'>Bawah</th>
								<th class='text-center'>Kiri</th>
								<th class='text-center'>Kanan</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if(!empty($listMatt)){
								foreach($listMatt AS $val => $valx){ 
                                    $val++;

									$idValue = (!empty($GET_VALUE[$valx['id']]['id']))?$GET_VALUE[$valx['id']]['id']:'';
									$matt_atas = (!empty($GET_VALUE[$valx['id']]['matt_atas']))?$GET_VALUE[$valx['id']]['matt_atas']:'';
									$matt_bawah = (!empty($GET_VALUE[$valx['id']]['matt_bawah']))?$GET_VALUE[$valx['id']]['matt_bawah']:'';
									$matt_kiri = (!empty($GET_VALUE[$valx['id']]['matt_kiri']))?$GET_VALUE[$valx['id']]['matt_kiri']:'';
									$matt_kanan = (!empty($GET_VALUE[$valx['id']]['matt_kanan']))?$GET_VALUE[$valx['id']]['matt_kanan']:'';

									$matt_atasYes = ($matt_atas == 'Yes')?'selected':'';
									$matt_bawahYes = ($matt_bawah == 'Yes')?'selected':'';
									$matt_kiriYes = ($matt_kiri == 'Yes')?'selected':'';
									$matt_kananYes = ($matt_kanan == 'Yes')?'selected':'';

									$matt_atasNo = ($matt_atas == 'No')?'selected':'';
									$matt_bawahNo = ($matt_bawah == 'No')?'selected':'';
									$matt_kiriNo = ($matt_kiri == 'No')?'selected':'';
									$matt_kananNo = ($matt_kanan == 'No')?'selected':'';

									echo "<tr>";
                                        echo "<td align='center'>".$valx['nama'];
											echo "<input type='hidden' name='DetailMatt[".$val."][id]' value='".$idValue."'>";
											echo "<input type='hidden' name='DetailMatt[".$val."][id_checksheet]' value='".$valx['id']."'>";
										echo "</td>";
										if($valx['id'] == 6){
											echo "<td align='center'><select class='form-control input-sm' name='DetailMatt[".$val."][atas]'><option value='No' ".$matt_atasNo.">No</option><option value='Yes' ".$matt_atasYes.">Yes</option></select></td>";
											echo "<td align='center'><select class='form-control input-sm' name='DetailMatt[".$val."][bawah]'><option value='No' ".$matt_bawahNo.">No</option><option value='Yes' ".$matt_bawahYes.">Yes</option></select></select></td>";
											echo "<td align='center'><select class='form-control input-sm' name='DetailMatt[".$val."][kiri]'><option value='No' ".$matt_kiriNo.">No</option><option value='Yes' ".$matt_kiriYes.">Yes</option></select></select></td>";
											echo "<td align='center'><select class='form-control input-sm' name='DetailMatt[".$val."][kanan]'><option value='No' ".$matt_kananNo.">No</option><option value='Yes' ".$matt_kananYes.">Yes</option></select></select></td>";
										}
										else{
											echo "<td align='center'><input type='text' class='form-control input-sm' name='DetailMatt[".$val."][atas]' value='".$matt_atas."'></td>";
											echo "<td align='center'><input type='text' class='form-control input-sm' name='DetailMatt[".$val."][bawah]' value='".$matt_bawah."'></td>";
											echo "<td align='center'><input type='text' class='form-control input-sm' name='DetailMatt[".$val."][kiri]' value='".$matt_kiri."'></td>";
											echo "<td align='center'><input type='text' class='form-control input-sm' name='DetailMatt[".$val."][kanan]' value='".$matt_kanan."'></td>";
										}
                                   echo "</tr>";
								}
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-12">
                    <h4>Checksheet Suhu dan Speed</h4>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' width='25%'></th>
								<th class='text-center' colspan='3'>Display Temperature (^Celsius)</th>
								<th class='text-center' colspan='3'>Dies Temperature (^Celsius)</th>
								<th class='text-center'>Speed Hidrolik (cm/menit)</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if(!empty($listSuhuSpeed)){
								foreach($listSuhuSpeed AS $val => $valx){ 
                                    $val++;

									$idValue = (!empty($GET_VALUE[$valx['id']]['id']))?$GET_VALUE[$valx['id']]['id']:'';
									$display1 = (!empty($GET_VALUE[$valx['id']]['display1']))?$GET_VALUE[$valx['id']]['display1']:'';
									$display2 = (!empty($GET_VALUE[$valx['id']]['display2']))?$GET_VALUE[$valx['id']]['display2']:'';
									$display3 = (!empty($GET_VALUE[$valx['id']]['display3']))?$GET_VALUE[$valx['id']]['display3']:'';
									$dies1 = (!empty($GET_VALUE[$valx['id']]['dies1']))?$GET_VALUE[$valx['id']]['dies1']:'';
									$dies2 = (!empty($GET_VALUE[$valx['id']]['dies2']))?$GET_VALUE[$valx['id']]['dies2']:'';
									$dies3 = (!empty($GET_VALUE[$valx['id']]['dies3']))?$GET_VALUE[$valx['id']]['dies3']:'';
									$speed = (!empty($GET_VALUE[$valx['id']]['speed']))?$GET_VALUE[$valx['id']]['speed']:'';

									echo "<tr>";
                                        echo "<td align='center'>".$valx['nama']."
												<input type='hidden' name='DetailSuhuSpeed[".$val."][id]' value='".$idValue."'>
												<input type='hidden' name='DetailSuhuSpeed[".$val."][id_checksheet]' value='".$valx['id']."'>
												</td>";
                                        echo "<td align='center'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][display1]' value='".$display1."'></td>";
                                        echo "<td align='center'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][display2]' value='".$display2."'></td>";
                                        echo "<td align='center'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][display3]' value='".$display3."'></td>";
                                        echo "<td align='center'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][dies1]' value='".$dies1."'></td>";
                                        echo "<td align='center'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][dies2]' value='".$dies2."'></td>";
                                        echo "<td align='center'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][dies3]' value='".$dies3."'></td>";
                                        echo "<td align='center'><input type='text' class='form-control input-sm' name='DetailSuhuSpeed[".$val."][speed]' value='".$speed."'></td>";
									echo "</tr>";
								}
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
            
			<div class="form-group row">
				<div class="col-md-6">
					<input type="hidden" name='id' value='<?=$id;?>'>
					<button type="button" class="btn btn-primary" name="save" id="save">Save</button>
					<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
				</div>
			</div>
		</form>
	</div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2();
    	$('.autoNumeric0').autoNumeric('init', {mDec: '0', aPad: false})

    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
		});

		$('#save').click(function(e){
			e.preventDefault();
			var id_product = $("#id_product").val();
			var id_frequency = $("#id_frequency").val();

      		if(id_product == '0' ){
				swal({title	: "Error Message!",text	: 'Product empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
			if(id_frequency == '0' ){
				swal({title	: "Error Message!",text	: 'Frequency empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
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
						var baseurl=siteurl+active_controller+'/add2';
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
										  timer	: 7000
										});
									window.location.href = base_url + active_controller
								}else{

									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 7000
										});
									}

								}
							},
							error: function() {

								swal({
								  title				: "Error Message !",
								  text				: 'An Error Occured During Process. Please try again..',
								  type				: "warning",
								  timer				: 7000
								});
							}
						});
				  } else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				  }
			});
		});

		$(document).on('click', '.addPart', function(){
			// loading_spinner();
			var get_id 		= $(this).parent().parent().attr('id');
			// console.log(get_id);
			var split_id	= get_id.split('_');
			var id 		= parseInt(split_id[1])+1;
			var id_bef 	= split_id[1];

			$.ajax({
				url: base_url+active_controller+'/get_add_checksheet/'+id,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#addchecksheet_"+id_bef).before(data.header);
					$("#addchecksheet_"+id_bef).remove();
					// $('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false});
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

	});



</script>
