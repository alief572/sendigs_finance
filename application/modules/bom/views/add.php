<?php

$no_bom          = (!empty($header))?$header[0]->no_bom:'';
$id_product      = (!empty($header))?$header[0]->id_product:'';
$waste_product   = (!empty($header))?$header[0]->waste_product:'';
$waste_setting   = (!empty($header))?$header[0]->waste_setting:'';
$waste_setting_resin   = (!empty($header))?$header[0]->waste_setting_resin:'';
$waste_setting_glass   = (!empty($header))?$header[0]->waste_setting_glass:'';
$moq   = (!empty($header))?$header[0]->moq:'';
$variant_product   	= (!empty($header))?$header[0]->variant_product:'';
$keterangan   		= (!empty($header))?$header[0]->keterangan:'';
$color_product   	= (!empty($header))?$header[0]->color:'';
$width   			= (!empty($header))?$header[0]->width:'';
$length   			= (!empty($header))?$header[0]->length:'';
$waste_total   		= (!empty($header))?$header[0]->waste_total:'';
$kode   		= (!empty($header))?$header[0]->kode:'';

// print_r($header);
?>

 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post"><br>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Product Master <span class='text-red'>*</span></label>
				</div>
				<div class="col-md-4">
					<input type="hidden" name="no_bom" value="<?=$no_bom;?>">
					<select id="id_product" name="id_product" class="form-control input-md chosen-select" required>
						<option value="0">Select An Option</option>
						<?php foreach ($results['product'] as $product){
						$sel = ($product->code_lv4 == $id_product)?'selected':'';
						?>
						<option value="<?= $product->code_lv4;?>" <?=$sel;?> data-kode='<?= $product->code?>'><?= strtoupper(strtolower($product->nama))?></option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-2">
					<label for="customer">Kode</label>
				</div>
				<div class="col-md-4">
					<input type="text" name="kode" id="kode" class='form-control input-md' readonly value="<?=$kode;?>">
					<span style='cursor:pointer;' class='text-primary' id='updateManualCode'>Update Code Program</span>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Varian Product</label>
				</div>
				<div class="col-md-4">
					<!-- <input type="text" name="variant_product" class='form-control input-md' placeholder='Variant Product' value="<?=$variant_product;?>"> -->
					<select id="variant_product" name="variant_product" class="form-control input-md chosen-select" required>
						<option value="0">Select An Option</option>
						<?php foreach ($results['list_variant_product'] as $product){
						$sel = ($product->value == $variant_product)?'selected':'';
						?>
						<option value="<?= $product->value;?>" <?=$sel;?> data-kode='<?= $product->kode?>'><?= $product->view?> (<?= $product->kode?>)</option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-2">
					<label for="customer">Color Product</label>
				</div>
				<div class="col-md-4">
					<select id="color_product" name="color_product" class="form-control input-md chosen-select" required>
						<option value="0">Select An Option</option>
						<?php foreach ($results['list_color_product'] as $product){
						$sel = ($product->value == $color_product)?'selected':'';
						?>
						<option value="<?= $product->value;?>" <?=$sel;?> data-kode='<?= $product->kode?>'><?= $product->view?> (<?= $product->kode?>)</option>
						<?php } ?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Keterangan</label>
				</div>
				<div class="col-md-4">
					<textarea name="keterangan" class='form-control input-md' placeholder='Keterangan' rows='2'><?=$keterangan;?></textarea>
				</div>
				<div class="col-md-2">
					<label for="customer">MOQ</label>
				</div>
				<div class="col-md-4">
					<input type="text" name="moq" id="moq" class='form-control input-md autoNumeric4' value="<?=$moq;?>">
				</div>
			</div>
			<br>
			<div class='box box-info'>
				<div class='box-header'>
					<h3 class='box-title'>Detail Material</h3>
					<div class='box-tool pull-right'>
						<!--<button type='button' data-id='frp_".$a."' class='btn btn-md btn-info panelSH'>SHOW</button>-->
					</div><br><br>
					<div class="form-group row">
						<div class="col-md-2">
							<label for="customer">Copy From BOM</label>
						</div>
						<div class="col-md-9">
							<select id="bom_standard_list" name="bom_standard_list" class="form-control input-md chosen-select" required>
								<option value="0">Select An Option</option>
								<?php foreach ($results['bom_standard_list'] as $valx => $product){
								?>
								<option value="<?= $product['no_bom'];?>"><?= strtoupper($product['nama'].'; '.$product['variant_product'].'; '.$product['color'].'; '.$product['surface'])?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-1">
							<button type="button" class="btn btn-default" id="copyBOM">Copy</button>
						</div>
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
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody id='body_table'>
							<?php
								$val = 0;
								$SUM = 0;
								if(!empty($detail)){
									foreach($detail AS $val => $valx){ $val++;
										$weight = $valx['weight'];
										$SUM += $weight;
										echo "<tr class='header_".$val."'>";
											echo "<td align='center'>".$val."</td>";
											echo "<td align='left'>";
											echo "<select name='Detail[".$val."][code_material]' class='chosen-select form-control input-sm inline-blockd material'>";
											echo "<option value='0'>Select Material Name</option>";
											foreach($material AS $valx4){
												$sel2 = ($valx4->code_lv4 == $valx['code_material'])?'selected':'';
												echo "<option value='".$valx4->code_lv4."' ".$sel2.">".strtoupper($valx4->nama)."</option>";
											}
											echo 		"</select>";
											echo "</td>";
											echo "<td align='left'>";
											echo "<input type='text' name='Detail[".$val."][weight]' class='form-control input-md text-right autoNumeric4 qty' placeholder='Weight /kg' value='".$weight."'>";
											echo "</td>";
											echo "<td align='left'>";
											echo "<input type='text' name='Detail[".$val."][ket]' class='form-control input-md' placeholder='Keterangan' value='".$valx['ket']."'>";
											echo "</td>";
											echo "<td align='left'>";
											echo "&nbsp;<button type='button' class='btn btn-sm btn-danger delPart' title='Delete Part'><i class='fa fa-close'></i></button>";
											echo "</td>";
										echo "</tr>";
									}
								}
							?>
							<tr id='add_<?=$val?>'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Material'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Material</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<th></th>
								<th class='text-right font20'>Total Berat Material</th>
								<th id='total_material' class='text-right font20'><?=number_format($SUM,4);?></th>
								<th><input type="hidden" name="tot_material" id="tot_material" value="<?=$SUM;?>"></th>
							</tr>
						</tfoot>
					</table>
					<br>
					<div class="form-group row">
						<div class="col-md-2">
							<label for="customer">Waste Product (Kg)</label>
						</div>
						<div class="col-md-2">
							<input type="text" name="waste_product" id="waste_product" class='form-control input-md autoNumeric4' value="<?=$waste_product;?>">
						</div>
						<div class="col-md-2">
							<label for="customer">Width (m)</label>
						</div>
						<div class="col-md-2">
							<input type="text" name="width" class='form-control input-md autoNumeric4' value="<?=$width;?>">
						</div>
					</div>
					<div class="form-group row">
						<div class="col-md-2">
							<label for="customer">Waste Setting & Cleaning Resin (Kg)</label>
						</div>
						<div class="col-md-2">
							<input type="hidden" name="waste_setting" class='form-control input-md autoNumeric4' value="<?=$waste_setting;?>">
							<input type="text" name="waste_setting_resin" id="waste_setting_resin" class='form-control input-md autoNumeric4' value="<?=$waste_setting_resin;?>">
						</div>
						<div class="col-md-2">
							<label for="customer">Length (m)</label>
						</div>
						<div class="col-md-2">
							<input type="text" name="length" class='form-control input-md autoNumeric4' value="<?=$length;?>">
						</div>
					</div>
					<div class="form-group row">
						<div class="col-md-2">
							<label for="customer">Waste Setting & Cleaning Glass (Kg)</label>
						</div>
						<div class="col-md-2">
							<input type="text" name="waste_setting_glass" id="waste_setting_glass" class='form-control input-md autoNumeric4' value="<?=$waste_setting_glass;?>">
						</div>
						<div class="col-md-2">
							<label for="customer">Waste Total (%)</label>
						</div>
						<div class="col-md-2">
							<input type="text" name="waste_total" id="waste_total" class='form-control input-md autoNumeric4' value="<?=$waste_total;?>" readonly>
						</div>
					</div>
					<button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
					<button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>

				</div>
			</div>
		</form>
	</div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style media="screen">
  .datepicker{
    cursor: pointer;
    padding-left: 12px;
  }

  .font20{
	font-size:16px;
  }
</style>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2();
		$( ".datepicker" ).datepicker();
		$( ".autoNumeric4" ).autoNumeric('init', {mDec: '4', aPad: false});

		//add part
		$(document).on('click', '.addPart', function(){
			// loading_spinner();
			var get_id 		= $(this).parent().parent().attr('id');
			// console.log(get_id);
			var split_id	= get_id.split('_');
			var id 		= parseInt(split_id[1])+1;
			var id_bef 	= split_id[1];

			$.ajax({
				url: base_url+active_controller+'/get_add/'+id,
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

		$(document).on('change','#id_product, #variant_product, #color_product',function(){
			var id_product 		= ($("#id_product").val() != '0')?$("#id_product").find(':selected').data('kode'):'';
			var variant_product = ($("#variant_product").val() != '0')?$("#variant_product").find(':selected').data('kode'):'';
			var color_product 	= ($("#color_product").val() != '0')?$("#color_product").find(':selected').data('kode'):'';

			var newKode = id_product+'-'+variant_product+'-'+color_product;
			$('#kode').val(newKode)
		});

		$(document).on('click','#updateManualCode',function(){
			var id_product 		= ($("#id_product").val() != '0')?$("#id_product").find(':selected').data('kode'):'';
			var variant_product = ($("#variant_product").val() != '0')?$("#variant_product").find(':selected').data('kode'):'';
			var color_product 	= ($("#color_product").val() != '0')?$("#color_product").find(':selected').data('kode'):'';

			var newKode = id_product+'-'+variant_product+'-'+color_product;
			$('#kode').val(newKode)
		});

	   //delete part
		$(document).on('click', '.delPart', function(){
			var get_id 		= $(this).parent().parent().attr('class');
			$("."+get_id).remove();
			sumMaterial()
		});

    	//add part
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller;
		});

		$(document).on('keyup', '.qty, #waste_setting_resin, #waste_setting_glass, #moq, #waste_product', function(){
			let waste_product = getNum($('#waste_product').val().split(',').join(''))
			let waste_resin = getNum($('#waste_setting_resin').val().split(',').join(''))
			let waste_glass = getNum($('#waste_setting_glass').val().split(',').join(''))
			let moq = getNum($('#moq').val().split(',').join(''))

			let SumTotal = 0
			let qty
			$('.qty').each(function(){
				qty = getNum($(this).val().split(',').join(''))
				SumTotal += qty
			})

			// console.log(SumTotal)
			$('#total_material').text(number_format(SumTotal,4))
			$('#tot_material').val(SumTotal)

			let waste_total = (((waste_resin + waste_glass + (waste_product * moq))) / ((SumTotal + waste_product) * moq + (waste_resin + waste_glass))) * 100
			$('#waste_total').val(number_format(waste_total,4))

		});

		$(document).on('click', '#copyBOM', function(){
			var id 		= $('#bom_standard_list').val();

			$.ajax({
				url: base_url+active_controller+'/get_add_copy/'+id,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#body_table").html(data.header);
					$('.chosen_select').select2({width: '100%'});
					$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false});

					sumMaterial()
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

		$('#save').click(function(e){
			e.preventDefault();
			var id_product		  	= $('#id_product').val();
			var material	      	= $('.material').val();
			var qty		        	= $('.qty').val();

			if(id_product == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Product name empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
			if(material == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Material name empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
      		if(qty == '' ){
				swal({
					title	: "Error Message!",
					text	: 'Weight empty, select first ...',
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
										  timer	: 3000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									window.location.href = base_url + active_controller;
								}else{

									if(data.status == 2){
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 3000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}else{
										swal({
										  title	: "Save Failed!",
										  text	: data.pesan,
										  type	: "warning",
										  timer	: 3000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}

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

		function sumMaterial(){
			let SumTotal = 0
			let qty
			$('.qty').each(function(){
				qty = getNum($(this).val().split(',').join(''))
				SumTotal += qty
			})

			// console.log(SumTotal)
			$('#total_material').text(number_format(SumTotal,4))
			$('#tot_material').val(SumTotal)
		}

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

	});

</script>
