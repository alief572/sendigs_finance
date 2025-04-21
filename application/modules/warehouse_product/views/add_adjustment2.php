
 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
      <div class="form-group row">
        <div class="col-md-1">
					<label for="customer">Product <span class="text-red">*</span></label>
				</div>
				<div class="col-md-4">
          <select id="id_product" name="id_product" class="form-control input-md chosen-select">
						<option value="0">Select An Product</option>
            <?php foreach (get_product() AS $val => $valx){ ?>
						        <option value="<?= $valx['id_category2'];?>"><?= strtoupper($valx['nama']);?></option>
						<?php } ?>
					</select>
        </div>

        <div class="col-md-1">
					<label for="customer">Costcenter <span class="text-red">*</span></label>
				</div>
				<div class="col-md-2">
          <select id="costcenter" name="costcenter" class="form-control input-md chosen-select" >
						<option value="0">List Empty</option>
					</select>
				</div>

        <div class="col-md-1">
					<label for="customer">Stock Awal</label>
				</div>
				<div class="col-md-1">
          <input type="text" name="stock_awal" id="stock_awal"  class="form-control input-md" placeholder="Stock Awal" readonly>
        </div>

        <div class="col-md-1">
					<label for="customer">Stock Akhir</label>
				</div>
				<div class="col-md-1">
          <input type="text" name="stock_akhir" id="stock_akhir"  class="form-control input-md" placeholder="Stock Akhir" readonly>
        </div>
      </div>

      <div class="form-group row">
				<div class="col-md-1">
					<label for="customer">Daycode <span class="text-red">*</span></label>
				</div>
				<div class="col-md-4">
          <select id="daycode" name="daycode" class="form-control input-md chosen-select">
						<option value="0">List Empty</option>
					</select>
				</div>
        <div class="col-md-1">
          <label for="customer">Reason <span class="text-red">*</span></label>
        </div>
        <div class="col-md-6">
          <input type="text" name="reason" id="reason"  class="form-control input-md" placeholder="Reason">
        </div>
      </div>

      <button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
      <button type="button" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>

		</form>
	</div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style media="screen">
  /* JUST COMMON TABLE STYLES... */
  .table { border-collapse: collapse; width: 100%; }
  .td { background: #fff; padding: 8px 16px; }


  .tableFixHead {
    overflow: auto;
    height: 300px;
  }

  .tableFixHead .thead .th {
    position: sticky;
    top: 0;
  	background: #0073b7;
  }
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
    $('.maskM').maskMoney();
    //back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller +'/adjustment';
		});

    $(document).on('change', '#id_product', function(e){
      e.preventDefault();
      var id_product		  = $('#id_product').val();

      $.ajax({
        url: base_url+active_controller+'/get_daycode_adjust/'+id_product,
        cache: false,
        type: "POST",
        dataType: "json",
        success: function(data){
          $("#daycode").html(data.option).trigger("chosen:updated");
          $("#costcenter").html("<option value='0'>List Empty<option>").trigger("chosen:updated");
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

    $(document).on('change', '#daycode', function(e){
      e.preventDefault();
      var id_product		  = $('#id_product').val();
      var daycode		  = $('#daycode').val();

      $.ajax({
        url: base_url+active_controller+'/get_costcenteradjust/'+id_product,
        cache: false,
        type: "POST",
        data: {
					"daycode" 	: daycode
				},
        dataType: "json",
        success: function(data){
          $("#costcenter").html(data.option).trigger("chosen:updated");
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

    $(document).on('change', '#costcenter', function(e){
        var id_product		  = $('#id_product').val();
        var costcenter		  = $('#costcenter').val();

        $.ajax({
          url: base_url+active_controller+'/get_stock/'+costcenter+'/'+id_product,
          cache: false,
          type: "POST",
          dataType: "json",
          success: function(data){
            $("#stock_awal").val(data.stock);
            $("#stock_akhir").val(data.stock - 1);
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
      var costcenter = $("#costcenter").val();
      var id_product = $("#id_product").val();
      var daycode   = $("#daycode").val();
      var reason = $("#reason").val();

      if(id_product == '0' ){
				swal({title	: "Error Message!",text	: 'Product empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
      if(daycode == '0' ){
				swal({title	: "Error Message!",text	: 'Daycode empty, input first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
      if(costcenter == '0' ){
				swal({title	: "Error Message!",text	: 'Costcenter empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
      if(reason == '' ){
				swal({title	: "Error Message!",text	: 'Reason empty, input first ...',type	: "warning"
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
						var baseurl=siteurl+'warehouse_product/add_adjustment2';
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
									window.location.href = base_url + active_controller+'/adjustment';
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

});



</script>
