 <div class="box box-primary">
    <div class="box-body"><br>
		<form id="data-form" method="post">
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Produk Name <span class='text-danger'>*</span></label>
				</div>
				<div class="col-md-10">
					<select id="produk" name="produk" class="form-control input-md chosen-select" required>
						<option value="0">Select An Option</option>
						<?php 
						foreach ($results['product'] as $material){
							// if (!in_array($material->code_lv4, $results['ArrProductCT'])) {
								?>
								<option value="<?= $material->code_lv4;?>"><?= strtoupper(strtolower($material->nama))?></option>
								<?php 
							// }
							} ?>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">BOM <span class="text-red">*</span></label>
				</div>
				<div class="col-md-10">
					<select id="no_bom" name="no_bom" class="form-control input-md chosen-select">
						<option value="0">List Empty</option>
					</select>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Total Cycletime Setting</label>
				</div>
				<div class="col-md-2">
					<input type="text" name="total_ct_setting" id="total_ct_setting" class='form-control input-md text-right autoNumeric4' readonly>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Total Cycletime Production</label>
				</div>
				<div class="col-md-2">
					<input type="text" name="total_ct_produksi" id="total_ct_produksi" class='form-control input-md text-right autoNumeric4' readonly>
				</div>
			</div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">MOQ <span class="text-red">*</span></label>
				</div>
				<div class="col-md-2">
					<input type="text" name="moq" id='moq' class='form-control input-md text-right autoNumeric4' readonly>
				</div>
			</div>

			<br>
			<div class='box box-info'>
				<div class='box-header'>
					<h3 class='box-title'>Detail Product</h3>
					<div class='box-tool pull-right'>
						<!--<button type='button' data-id='frp_".$a."' class='btn btn-md btn-info panelSH'>SHOW</button>-->
					</div>
				</div>
				<div class='box-body hide_header'>
					<table class='table table-striped table-bordered table-hover table-condensed' width='100%'>
						<thead>
							<tr class='bg-blue'>
								<th class='text-center' style='width: 4%;'>#</th>
								<th class='text-center' style='width: 30%;'>Cost Center</th>
								<th class='text-center' style='width: 15%;'></th>
               					<th class='text-center' style='width: 15%;'></th>
								<th class='text-center'></th>
								<th class='text-center'></th>
								<th class='text-center' style='width: 4%;'>#</th>
							</tr>
						</thead>
						<tbody>
							<tr id='add_0'>
								<td align='center'></td>
								<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add Costcenter'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add Costcenter</button></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
								<td align='center'></td>
							</tr>
						</tbody>
					</table>
					<br>
          			<button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
					<button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>
				</div>
			</div>
		</form>
	</div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2({width: '100%'});

		$(document).on('change','#produk',function(){
			var id_product = $("#produk").val();

			$.ajax({
				url:siteurl+active_controller+'/get_list_bom',
				method : "POST",
				data : {id_product:id_product},
				dataType : 'json',
				success: function(data){
					$('#no_bom').html(data.option);
				}
			});
		});
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
					$('.chosen-select').select2({width: '100%'});
					$('.maskM').autoNumeric();
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

		//add part
		$(document).on('click', '.addSubPart', function(){
			// loading_spinner();
			var get_id 		= $(this).parent().parent().attr('id');
			// console.log(get_id);
			var split_id	= get_id.split('_');
			var id 			= split_id[1];
			var id2 		= parseInt(split_id[2])+1;
			var id_bef 		= split_id[2];

			$.ajax({
				url: base_url+active_controller+'/get_add_sub/'+id+'/'+id2,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#add_"+id+"_"+id_bef).before(data.header);
					$("#add_"+id+"_"+id_bef).remove();
					$('.chosen-select').select2({width:'100%'});
					$('.maskM').autoNumeric();
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

		$(document).on('click', '.delSubPart', function(){
			var get_id 		= $(this).parent().parent('tr').html();
			$(this).parent().parent('tr').remove();
		});

    //add part
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller;
		});

		$(document).on('keyup', '.cycletime', function(){
			let SumTotalSet = 0
			let SumTotalPro = 0
			let cycletime
			let tipe
			$('.cycletime').each(function(){
				tipe = $(this).parent().parent().find('.tipe:checked').val()
				cycletime = getNum($(this).val().split(',').join(''))

				if(tipe == 'setting'){
					SumTotalSet += cycletime
				}
				else{
					SumTotalPro += cycletime
				}
			})

			// console.log(SumTotal)
			$('#total_ct_setting').val(number_format(SumTotalSet,2))
			$('#total_ct_produksi').val(number_format(SumTotalPro,2))

			let moq = SumTotalSet * 9 / SumTotalPro
			$('#moq').val(Math.round(moq))
		});

		$(document).on('click', '.tipe', function(){
			let SumTotalSet = 0
			let SumTotalPro = 0
			let cycletime
			let tipe
			$('.cycletime').each(function(){
				tipe = $(this).parent().parent().find('.tipe:checked').val()
				cycletime = getNum($(this).val().split(',').join(''))
				// console.log(tipe)
				if(tipe == 'setting'){
					SumTotalSet += cycletime
				}
				else{
					SumTotalPro += cycletime
				}
			})

			// console.log(SumTotal)
			$('#total_ct_setting').val(number_format(SumTotalSet,2))
			$('#total_ct_produksi').val(number_format(SumTotalPro,2))

			let moq = SumTotalSet * 9 / SumTotalPro
			$('#moq').val(Math.round(moq))
		});



		$('#save').click(function(e){
			e.preventDefault();
			var produk		= $('#produk').val();
			var no_bom		= $('#no_bom').val();
			var moq			= $('#moq').val();
			var costcenter	= $('.costcenter').val();
			var process		= $('.process').val();
			// console.log(moq)
			if(produk == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Product name empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
			if(no_bom == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'No BOM name empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
			if(costcenter == '0' ){
				swal({
					title	: "Error Message!",
					text	: 'Costcenter empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
			if(process == '' ){
				swal({
					title	: "Error Message!",
					text	: 'Process name empty, select first ...',
					type	: "warning"
				});

				$('#save').prop('disabled',false);
				return false;
			}
			// if(moq == '0' || moq == 'Infinity' ){
			// 	swal({
			// 		title	: "Error Message!",
			// 		text	: 'MOQ Tidak Boleh NOL !',
			// 		type	: "warning"
			// 	});

			// 	$('#save').prop('disabled',false);
			// 	return false;
			// }

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
						var baseurl=siteurl+'cycletime/save_cycletime';
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
										  timer	: 7000,
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
										  timer	: 7000,
										  showCancelButton	: false,
										  showConfirmButton	: false,
										  allowOutsideClick	: false
										});
									}else{
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
