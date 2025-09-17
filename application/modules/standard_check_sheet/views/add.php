<?php
$id 				= (!empty($header[0]['id']))?$header[0]['id']:'';
$code_lv4 			= (!empty($header[0]['code_lv4']))?$header[0]['code_lv4']:'';
$frequency_check 	= (!empty($header[0]['frequency_check']))?$header[0]['frequency_check']:'';

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
					<label for="customer">Frequency Check <span class="text-red">*</span></label>
				</div>
				<div class="col-md-3">
					<select id="id_frequency" name="id_frequency" class="form-control input-md chosen-select">
						<option value="0">Pilih Frequency</option>
						<option value="hourly" <?=($frequency_check == 'hourly')?'selected':'';?>>Hourly</option>
						<option value="day" <?=($frequency_check == 'day')?'selected':'';?>>Day</option>
						<option value="week" <?=($frequency_check == 'week')?'selected':'';?>>Week</option>
						<option value="month" <?=($frequency_check == 'month')?'selected':'';?>>Month</option>
						<option value="year" <?=($frequency_check == 'year')?'selected':'';?>>Year</option>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-12">
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center'>Items</th>
								<th class='text-center' style='width: 25%;'>Standard</th>
								<th class='text-center' style='width: 25%;'>Type</th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$val = 0;
							if(!empty($detail)){
								foreach($detail AS $val => $valx){ $val++;
									$checked1 = ($valx['tipe'] == '1')?'checked':'';
									$checked2 = ($valx['tipe'] == '2')?'checked':'';
									echo "<tr class='headerchecksheet_".$val."'>";
									echo "<td align='center' style='vertical-align:middle;'>".$val."</td>";
									echo "<td align='left' style='vertical-align:middle;'>";
									echo "<input type='hidden' name='DetailEdit[".$val."][id]' value='".$valx['id']."'>";
									echo "<input type='text' name='DetailEdit[".$val."][items]' class='form-control input-md text-center autoNumeric4 length changeEnd' placeholder='Items' value='".$valx['items']."'>";
									echo "</td>";
									echo "<td align='left' style='vertical-align:middle;'>";
									echo "<input type='text' name='DetailEdit[".$val."][standard]' class='form-control input-md text-center autoNumeric4 width changeEnd' placeholder='Standard' value='".$valx['standard']."'>";
									echo "</td>";
									echo "<td align='left'>";
									echo "<div class='form-group'>";
									echo "<div class='radio'>";
									  echo "<label>";
										echo "<input type='radio' name='DetailEdit[".$val."][tipe]' id='optionsRadios1' value='1' ".$checked1.">";
										echo "Yes/No";
									  echo "</label>";
									echo "</div>";
									echo "<div class='radio'>";
									  echo "<label>";
										echo "<input type='radio' name='DetailEdit[".$val."][tipe]' id='optionsRadios2' value='2' ".$checked2.">";
										echo "Input Text";
									  echo "</label>";
									echo "</div>";
								  echo "</div>";
									echo "</td>";
									echo "<td align='left' style='vertical-align:middle;'>";
									echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
									echo "</td>";
									echo "</tr>";
								}
							}
							?>
							<tr id='addchecksheet_<?=$val?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-success addPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
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
						var baseurl=siteurl+active_controller+'/add';
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
