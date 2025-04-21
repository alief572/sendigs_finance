
<div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
      	<div class="form-group row">
        	<div class="col-md-2">
				<label for="customer">Product <span class="text-red">*</span></label>
			</div>
			<div class="col-md-10">
          		<select id="id_product" name="id_product" class="form-control input-md chosen-select">
					<option value="0">Select An Product</option>
            			<?php 
							foreach (get_list_inventory_lv4('product') AS $val => $valx){ 
								// if (!in_array($valx['code_lv4'], $ArrProductCT)) {
								?>
								<option value="<?= $valx['code_lv4'];?>"><?= strtoupper($valx['nama']);?></option>
								<?php 
								// }
							} 
							?>
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
				<label for="customer">Stock</label>
			</div>
			<div class="col-md-2">
          		<input type="text" name='stock' id='stock' class='form-control text-center autoNumeric0'>
        	</div>
        </div>
		<div class="form-group row">
        	<div class="col-md-2">
				<label for="customer"></label>
			</div>
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

		$(document).on('change','#id_product',function(){
			var id_product = $("#id_product").val();

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


		$('#save').click(function(e){
			e.preventDefault();
			var id_product = $("#id_product").val();
			var no_bom = $("#no_bom").val();

      		if(id_product == '0' ){
				swal({title	: "Error Message!",text	: 'Product empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
			if(no_bom == '0' ){
				swal({title	: "Error Message!",text	: 'No BOM empty, select first ...',type	: "warning"
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
