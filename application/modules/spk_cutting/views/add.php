
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
		<div class="form-group row">
        	<div class="col-md-12">
				<table width='50%'>
					<tr>
						<td width='20%'>Sales Order </td>
						<td width='1%'>:</td>
						<td><?=$getData[0]['so_number'];?></td>
					</tr>
					<tr>
						<td>Project</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['project']);?></td>
					</tr>
                    <tr>
						<td>Product Custom</td>
						<td>:</td>
						<td><?=strtoupper($getDataReq[0]['nama_product']);?></td>
					</tr>
                    <tr>
						<td>Single Product</td>
						<td>:</td>
						<td><?=strtoupper($NamaProduct);?></td>
					</tr>
					<tr>
						<td>Qty</td>
						<td>:</td>
						<td><?=number_format($getData[0]['propose']);?></td>
					</tr>
                    <tr>
						<td>Sisa Request</td>
						<td>:</td>
						<td><?=number_format($qty);?></td>
					</tr>
					<tr>
						<td>Due Date</td>
						<td>:</td>
						<td><?=date('d-M-Y',strtotime($getData[0]['due_date']));?></td>
					</tr>
					<!-- <tr>
						<td>Detail BOM</td>
						<td>:</td>
						<td><span class='text-bold text-primary detail' data-id='<?=$getData[0]['no_bom'];?>' data-category='<?=$getDataProduct[0]['category'];?>' style='cursor:pointer;'>Tampilkan BOM</span></td>
					</tr> -->
					<!-- <tr>
						<td class='text-bold'>Tot. Cycletime/Hour</td>
						<td class='text-bold'>:</td>
						<td class='text-bold' id='total_cycletime'></td>
					</tr> -->
				</table>
				<input type="hidden" id='cycletime' name='cycletime' value='<?=$cycletime?>'>
				<input type="hidden" id='propose' name='propose' value='<?=$qty?>'>
				<input type="hidden" id='id' name='id' value='<?=$getData[0]['id']?>'>
				<input type="hidden" id='so_number' name='so_number' value='<?=$getData[0]['so_number']?>'>
				<input type="hidden" id='due_date' name='due_date' value='<?=$getData[0]['due_date']?>'>
				<input type="hidden" id='max_date' name='max_date' value='<?=$maxDate?>'>
			</div>
        </div>
		<h4>Schedule Detil</h4>
		<div class="form-group row">
        	<div class="col-md-12">
				<table class="table table-bordered table-striped" width='100%'>
					<tr>
						<th class='text-center' width='15%'>Plan Date</th>
						<th class='text-center' width='15%'>Est Finish</th>
						<th class='text-center' width='15%'>Qty SPK</th>
						<th class='text-center'>For Costcenter</th>
						<th class='text-center' width='5%'>Option</th>
					</tr>
					<tr id='add_0'>
						<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>
						<td align='center'></td>
						<td align='center'></td>
						<td align='center'></td>
						<td align='center'></td>
					</tr>
				</table>
			</div>
        </div>
		<div class="form-group row">
			<div class="col-md-6">
				<button type="button" class="btn btn-primary" name="save" id="save">Save</button>
				<button type="button" class="btn btn-danger" style='margin-left:5px;' name="back" id="back">Back</button>
			</div>
        </div>

      	
		</form>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style='width:70%;'>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
  </div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style>
    .datepicker, .datepicker2{
        cursor: pointer;
    }
</style>

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

		$(document).on('click', '.addPart', function(){
			// loading_spinner();
			var get_id 		= $(this).parent().parent().attr('id');
			var split_id	= get_id.split('_');
			var id 			= parseInt(split_id[1])+1;
			var id_bef 		= split_id[1];
			var due_date	= $('#due_date').val()
			var max_date	= $('#max_date').val()

			$.ajax({
				url: base_url+active_controller+'/get_add/'+id,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#add_"+id_bef).before(data.header);
					$("#add_"+id_bef).remove();
					$('.autoNumeric0').autoNumeric('init', {mDec: '0', aPad: false})
					$('.chosen-select').select2();
					$('.datepicker').datepicker({ dateFormat: 'dd-M-yy', maxDate:'+'+max_date+'d' });
					$('.datepicker2').datepicker({ dateFormat: 'dd-M-yy', maxDate:'+'+max_date+'d' });
					// $('.datepicker2').datepicker({ dateFormat: 'dd-M-yy', minDate:'+0d'});
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

		$(document).on('click', '.delPart', function(){
			var get_id 		= $(this).parent().parent().attr('class');
			$("."+get_id).remove();
		});

		$(document).on('keyup', '.qty_spk', function(){
			let cycletime = $('#cycletime').val()
			let SUM = 0
			$('.qty_spk').each(function(){
				qty = getNum($(this).val().split(",").join(""));
				
				SUM += qty
			});

			let totalCT = cycletime * SUM

			$('#total_cycletime').text(number_format(totalCT,2))

		});

		$('#save').click(function(e){
			e.preventDefault();
			let propose = $('#propose').val()
			let selectval;
			//plan date
			$('.datepicker').each(function(){
				selectval = $(this).val();
				
				if(selectval == ''){
					return false;
				}
			});
			if(selectval == ''){
				swal({
				title	: "Error Message!",
				text	: 'Plan date belum dipilih...',
				type	: "warning"
				});
				return false;
			}
			//qty_spk
			$('.qty_spk').each(function(){
				selectval = $(this).val();
				
				if(selectval == '' || selectval <= 0){
					return false;
				}
			});
			if(selectval == ''){
				swal({
				title	: "Error Message!",
				text	: 'Qty tidak boleh kosong / Nol...',
				type	: "warning"
				});
				return false;
			}
			//costcenter
			$('.costcenter').each(function(){
				selectval = $(this).val();
				
				if(selectval == '0'){
					return false;
				}
			});
			if(selectval == '0'){
				swal({
				title	: "Error Message!",
				text	: 'Costcenter belum dipilih...',
				type	: "warning"
				});
				return false;
			}

			//CHECK QTY
			let SUM = 0
			$('.qty_spk').each(function(){
				qty = getNum($(this).val().split(",").join(""));
				
				SUM += qty
			});

			if(SUM > propose){
				swal({
				title	: "Error Message!",
				text	: 'Jumlah Qty SPK Harus urang dari jumlah sisa request !',
				type	: "warning"
				});
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
										// window.open(base_url + active_controller+'/print_spk/'+data.kode,'_blank');
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

		$(document).on('click', '.detail', function(){
			var no_bom = $(this).data('id');
			var category = $(this).data('category');
			// console.log(category)
			let controller_category = '';
			if(category == 'standard'){
				controller_category = 'bom';
			}
			if(category == 'topping'){
				controller_category = 'bom_topping';
			}
			if(category == 'grid standard'){
				controller_category = 'bom_hi_grid_standard';
			}
			if(category == 'grid custom'){
				controller_category = 'bom_hi_grid_custom';
			}
			if(category == 'ftackel'){
				controller_category = 'bom_ftackel';
			}
			// alert(id);
			$("#head_title").html("<b>Detail Bill Of Material</b>");
			$.ajax({
				type:'POST',
				url: base_url + controller_category + '/detail/',
				data:{
					'no_bom':no_bom,
				},
				success:function(data){
					$("#dialog-popup").modal();
					$("#ModalView").html(data);

				}
			})
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
