<?php
$code_lv4 = $getData[0]['code_lv4'];
$nama_product = $getData[0]['nama'];

$actual_stock = (!empty($getStockProduct[$code_lv4]))?$getStockProduct[$code_lv4]:0;
$booking_stock = 0;
$free_stock = $actual_stock - $booking_stock;

$sisa_stock = $free_stock;
$on_process_spk = 0;
$on_process_fg = $sisa_stock + $on_process_spk;

$min_stock 	= (!empty($getProductLv4[$code_lv4]['min_stock']))?$getProductLv4[$code_lv4]['min_stock']:0;
$moq 		= (!empty($getProductLv4[$code_lv4]['moq']))?$getProductLv4[$code_lv4]['moq']:0;

$propose	= 0;
if($on_process_fg >= $min_stock){
	$propose = $moq - $on_process_fg;
}
else{
	$propose = $moq - ($min_stock - $on_process_fg) +($min_stock - $on_process_fg);
}
?>
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
      	<div class="form-group row">
        	<div class="col-md-2">
				<label for="customer">Product Name</label>
			</div>
			<div class="col-md-6">
                <input type="hidden" name='code_lv4' id='code_lv4' value='<?=$code_lv4;?>'>
                <input type="text" name='nama_product' id='nama_product' class='form-control' value='<?=$nama_product;?>' disabled>
        	</div>
        </div>
		<div class="form-group row">
        	<div class="col-md-2"></div>
			<div class="col-md-10">
          		<table class='table'>
                    <tr>
                        <th class='text-center' width='20%'>Actual Stock</th>
                        <th class='text-center' width='20%'>Booking Stock</th>
                        <th class='text-center' width='20%'>Free Stock</th>
                        <th class='text-center' width='20%'>Use/Booking Stock</th>
                        <th class='text-center' width='20%'>Sisa Stock</th>
                    </tr>
                    <tr>
                        <td><input type="text" name='actual_stock' id='actual_stock' class='form-control text-center autoNumeric0' readonly value='<?=$actual_stock;?>'></td>
                        <td><input type="text" name='booking_stock' id='booking_stock' class='form-control text-center autoNumeric0' readonly value='<?=$booking_stock;?>'></td>
                        <td><input type="text" name='free_stock' id='free_stock' class='form-control text-center autoNumeric0' readonly value='<?=$free_stock;?>'></td>
                        <td><input type="text" name='use_stock' id='use_stock' class='form-control text-center autoNumeric0'></td>
                        <td><input type="text" name='sisa_stock' id='sisa_stock' class='form-control text-center autoNumeric0' readonly value='<?=$sisa_stock;?>'></td>
                    </tr>
					<tr>
                        <th class='text-center'>On Process SPK</th>
                        <th class='text-center'>On Process FG</th>
                        <th class='text-center'>Min Stock</th>
                        <th class='text-center'>MOQ</th>
                        <th class='text-center'>Propose</th>
                    </tr>
                    <tr>
                        <td><input type="text" name='on_process_spk' id='on_process_spk' class='form-control text-center autoNumeric0' readonly value='<?=$on_process_spk;?>'></td>
                        <td><input type="text" name='on_process_fg' id='on_process_fg' class='form-control text-center autoNumeric0' readonly value='<?=$on_process_fg;?>'></td>
                        <td><input type="text" name='min_stock' id='min_stock' class='form-control text-center autoNumeric0' readonly value='<?=$min_stock;?>'></td>
                        <td><input type="text" name='moq' id='moq' class='form-control text-center autoNumeric0' readonly value='<?=$moq;?>'></td>
                        <td><input type="text" name='propose' id='propose' class='form-control text-center autoNumeric0' value='<?=$propose;?>'></td>
                    </tr>
                </table>
        	</div>
        </div>
		<div class="form-group row">
        	<div class="col-md-2">
				<label for="customer"></label>
			</div>
			<div class="col-md-6">
				<!-- <button type="button" class="btn btn-primary" name="save" id="save">Create SPK</button> -->
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

		$(document).on('keyup', '#use_stock', function(){
		    let free_stock 	= getNum($('#free_stock').val().split(",").join(""))
		    let use_stock 	= getNum($('#use_stock').val().split(",").join(""))
		
		    let sisa_stock 	= free_stock - use_stock

			let on_process_spk 	= getNum($('#on_process_spk').val().split(",").join(""))
		    let on_process_fg 	= sisa_stock + on_process_spk

			let min_stock 	= getNum($('#min_stock').val().split(",").join(""))
			let moq 	= getNum($('#moq').val().split(",").join(""))

			let propose
			if(on_process_fg >= min_stock){
				propose = moq - on_process_fg
			}
			else{
				propose = moq - (min_stock - on_process_fg) + (min_stock - on_process_fg)
			}

			$('#sisa_stock').val(sisa_stock)
			$('#on_process_fg').val(on_process_fg)
			$('#propose').val(propose)
		});


		$('#save').click(function(e){
			e.preventDefault();
			var id_product = $("#id_product").val();

      		if(id_product == '0' ){
				swal({title	: "Error Message!",text	: 'Product empty, select first ...',type	: "warning"
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

});



</script>
