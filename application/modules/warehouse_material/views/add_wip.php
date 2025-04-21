

 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post"><br>
      <div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Planning Date</label>
				</div>
				<div class="col-md-4">
          <select id="plandate" name="plandate" class="form-control input-md chosen-select" <?=$dist;?>>
						<option value="0">Select An Option</option>
						<?php foreach (get_data_planning() as $val => $valx){
						?>
						<option value="<?= $valx['no_plan'];?>"><?= date('d F Y', strtotime($valx['date_awal'])).' - '.date('d F Y', strtotime($valx['date_akhir']));?></option>
						<?php } ?>
					</select>
				</div>
      </div>
      <br>
      <input type="hidden" id="num">
      <div id="tmp_plan">

      </div>
      <button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
      <button type="button" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>

		</form>
	</div>
</div>
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
<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
	var base_url			    = '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
    $(".maskM").maskMoney();

    $(document).on('keyup', '.inputPacking', function(){
		    var get_id    = $(this).attr('id');
        var det_id	  = get_id.split('_');
        var no        = det_id[1];
        var konversi  = getNum($(this).data('konversi'));
        var value     = getNum($(this).val());
        var hasil     = konversi * value;

        $("#qty_"+no).val(hasil.toFixed(2));

		});


    $(document).on('change', '#plandate', function(e){
			// loading_spinner();
      e.preventDefault();
      var plan_date = $(this).val();

			$.ajax({
				url: base_url+'index.php/'+active_controller+'/get_wip_produksi/'+plan_date,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
          $("#tmp_plan").html(data.header);
          $("#num").val(data.num);
          $(".maskM").maskMoney();
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


    //back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller +'/wip';
		});

		$('#save').click(function(e){
			e.preventDefault();
      var plandate		        = $('#plandate').val();
      var num = $("#num").val();
      if( num == '0'){
  			swal({
  			  title	: "Error Message!",
  			  text	: 'Empty data, please input first ...',
  			  type	: "warning"
  			});
  			$('#save').prop('disabled',false);
  			return false;
  		}

      if(plandate == '0' ){
        swal({
          title	: "Error Message!",
          text	: 'Plan Date name empty, select first ...',
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
						var baseurl=siteurl+'warehouse_material/add_wip';
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
									window.location.href = base_url + active_controller+'/wip';
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
