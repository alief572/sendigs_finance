<?php

$no_plan        = (!empty($header[0]->no_plan))?$header[0]->no_plan:'';
$costcenter     = (!empty($header[0]->no_plan))?$header[0]->costcenter:'';
$date_awal      = (!empty($header[0]->no_plan))?$header[0]->date_awal:'';
$date_akhir     = (!empty($header[0]->no_plan))?$header[0]->date_akhir:'';

$dist        = (!empty($header[0]->no_plan))?'disabled':'readonly';
// print_r($header);
?>

 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post"><br>
      <div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Delivery Date</label>
				</div>
				<div class="col-md-4">
          <select id="delivery" name="delivery" class="form-control input-md chosen-select" <?=$dist;?>>
						<option value="0">Select An Option</option>
						<?php foreach (get_date_delivery() AS $val => $valx){ ?>
						        <option value="<?= $valx['no_so'];?>"><?= $valx['no_so'];?> - <?= date('d F Y',strtotime($valx['delivery_date']))." - ".strtoupper(get_name_field('master_customer', 'name_customer', 'id_customer', $valx['code_cust']));?></option>
						<?php } ?>
					</select>
				</div>

        <div class="col-md-2">
					<label for="customer">Plan Delivery</label>
				</div>
				<div class="col-md-4">
          <input type="text" name="plan_delivery" id="plan_delivery"  class="form-control input-md" placeholder="Plan Delivery" readonly>
        </div>
      </div>

      <br>
      <div id="tmp_plan">

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

    //back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller +'/delivery';
		});

    $("#tags").select2({
        maximumSelectionLength: 3
    })

    $(document).on('change', '.selMax', function(){
      var no = $(this).data('no');
      // var max = getNum($('#deliv_'+no).val().split(",").join(""));
      // $(this).select2({
      //     maximumSelectionLength: max
      // });
      var before  = getNum($("#qty_kurang2_"+no).val().split(",").join(""));
      var jum_sel = $("option:selected", this).length;
      var dum = (jum_sel != 0)?jum_sel:'';
      $('#deliv_'+no).val(dum);
       $("#qty_kurang_"+no).val(before-dum);
      // var max = getNum($('#deliv_'+no).val().split(",").join(""));

      // if(max != jum_sel){
      //   $('#save').prop('disabled',true);
      // }
      // else{
      //   $('#save').prop('disabled',false);
      // }
    });

    $(document).on('change', '#delivery', function(e){
      // loading_spinner();
      e.preventDefault();
      var delivery		        = $('#delivery').val();

      if(delivery == '0' ){
        swal({
          title	: "Error Message!",
          text	: 'Costcenter name empty, select first ...',
          type	: "warning"
        });
        return false;
      }

      $.ajax({
        url: base_url+active_controller+'/get_delivery/'+delivery,
        cache: false,
        type: "POST",
        dataType: "json",
        success: function(data){
          $("#tmp_plan").html(data.header);
          $("#plan_delivery").val(data.plan_delivery);
          $('.chosen_select').select2({width: '100%'});
          $('.datepicker').datepicker();
          $('.maskM').maskMoney();
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

    $(document).on('click', '#save', function(e){
			e.preventDefault();
      var delivery = $("#delivery").val();
      if(delivery == '0' ){
				swal({title	: "Error Message!",text	: 'Delivery date empty, select first ...',type	: "warning"
				});
				// $('#save').prop('disabled',false);
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
						var baseurl=siteurl+'warehouse_product/add_delivery';
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
									window.location.href = base_url + active_controller+'/delivery';
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


    // $(document).on('keyup','.qty_delivery',function(){
    //   var no      = $(this).data('no');
    //   var inx     = getNum($(this).val().split(",").join(""));
    //   var before  = getNum($("#qty_kurang2_"+no).val().split(",").join(""));
    //   var stock   = getNum($("#qty_stock_"+no).val().split(",").join(""));
    //   var jum_sel = $("#daycod_"+no+"option:selected").length;
    //
    //   $("#daycod_"+no).select2({
    //       maximumSelectionLength: inx
    //   });
    //
    //   $("#qty_kurang_"+no).val(before-inx);
    //
    //   if(inx > before || inx > stock || inx != jum_sel){
    //     $('#save').prop('disabled',true);
    //   }
    //   else{
    //     $('#save').prop('disabled',false);
    //   }
    // });

});



</script>
