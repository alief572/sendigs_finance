
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
		<div class="form-group row">
        	<div class="col-md-12">
				<table width='50%'>
					<tr>
						<td width='20%'>Sales Order</td>
						<td width='1%'>:</td>
						<td><?=$getData[0]['so_number'];?></td>
					</tr>
					<tr>
						<td>Product Name</td>
						<td>:</td>
						<td><?=strtoupper($getData[0]['nama_product']);?></td>
					</tr>
					<tr>
						<td>Qty</td>
						<td>:</td>
						<td><?=number_format($qty).' dari total propose '.number_format($getData[0]['propose']);?></td>
					</tr>
					<tr>
						<td>Due Date</td>
						<td>:</td>
						<td><?=date('d-M-Y',strtotime($getData[0]['due_date']));?></td>
					</tr>
				</table>
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
						<th class='text-center' width='20%'>Date</th>
						<th class='text-center' width='20%'>Qty SPK</th>
						<th class='text-center'>For Costcenter</th>
						<th class='text-center' width='10%'>Option</th>
					</tr>
					<tr id='add_0'>
						<td align='left'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button type='button' class='btn btn-sm btn-warning addPart' title='Add'><i class='fa fa-plus'></i>&nbsp;&nbsp;Add</button></td>
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


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style>
    .datepicker{
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
					$('.datepicker').datepicker({ dateFormat: 'dd-M-yy', minDate:'+0d', maxDate:'+'+max_date+'d' });
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

			if(SUM != propose){
				swal({
				title	: "Error Message!",
				text	: 'Jumlah Qty SPK dan Propose Harus Sama !',
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
										window.open(base_url + active_controller+'/print_spk/'+data.kode,'_blank');
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

	});



</script>
