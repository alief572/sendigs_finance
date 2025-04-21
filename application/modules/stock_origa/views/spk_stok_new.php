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
if($free_stock < $min_stock){
	$propose = $moq;
}
?>
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
      	<div class="form-group row">
        	<div class="col-md-2">
				<label for="customer">Product Name</label>
			</div>
			<div class="col-md-9">
                <input type="hidden" name='code_lv4' id='code_lv4' value='<?=$code_lv4;?>'>
                <input type="text" name='nama_product' id='nama_product' class='form-control' value='<?=$nama_product;?>' readonly>
        	</div>
        </div>
		<div class="form-group row">
        	<div class="col-md-2">
				<label for="customer">BOM <span class='text-red'>*</span></label>
			</div>
			<div class="col-md-9">
           	<select name="no_bom" id="no_bom" class='form-control chosen-select'>
				<?php
					if(!empty($getDataBOM)){
						// echo "<option value='0'>Pilih BOM</option>";
						foreach ($getDataBOM as $key => $value) {
							$namaBOM = (!empty($getNameBOMProduct[$value['no_bom']]))?$getNameBOMProduct[$value['no_bom']]:'';
							echo "<option value='".$value['no_bom']."'>".strtoupper($namaBOM)."</option>";
						}
					}
					else{
						echo "<option value='0'>BOM Belum dibuat !!!</option>";
					}
				?>
		   	</select>
			<span class='text-bold text-primary detail' data-id='<?=$value['no_bom'];?>' data-category='<?=$value['category'];?>' style='cursor:pointer;'>Tampilkan BOM</span>
            </div>
        </div>
        <div class="form-group row">
        	<div class="col-md-2">
				<label for="customer">Due Date <span class='text-red'>*</span></label>
			</div>
			<div class="col-md-2">
                <input type="text" name='due_date' id='due_date' class='form-control text-center datepicker' readonly>
        	</div>
        </div>
        <div class="form-group row">
        	<div class="col-md-2">
				<label for="customer">Propose <span class='text-red'>*</span></label>
			</div>
			<div class="col-md-2">
            <input type="text" name='propose' id='propose' class='form-control text-center autoNumeric0' value='<?=$propose;?>'>
            </div>
        </div>
		
		<div class="form-group row">
        	<div class="col-md-2">
				<label for="customer"></label>
			</div>
			<div class="col-md-10">
				<button type="button" class="btn btn-primary" name="save" id="save">Release SO</button>
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
    .datepicker{
        cursor: pointer;
    }
</style>

<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.datepicker').datepicker({ dateFormat: 'dd-M-yy' });
    	$('.autoNumeric0').autoNumeric('init', {mDec: '0', aPad: false})
    	$('.chosen-select').select2()

    	//back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller
		});

		$(document).on('click', '.detail', function(){
			var no_bom = $('#no_bom').val();
			var category = $(this).data('category');
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

		$('#save').click(function(e){
			e.preventDefault();
			var due_date = $("#due_date").val();
			var propose = $("#propose").val();
			var no_bom = $("#no_bom").val();

			if(no_bom == '0'){
				swal({title	: "Error Message!",text	: 'BOM belum dibuat...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}

      		if(due_date == '' ){
				swal({title	: "Error Message!",text	: 'Due date empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}

            if(propose == '' || propose <= 0 ){
				swal({title	: "Error Message!",text	: 'Propose empty, select first ...',type	: "warning"
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
						var baseurl=siteurl+active_controller+'/spk_stok';
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
