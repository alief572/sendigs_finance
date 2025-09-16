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
					<label for="customer">Costcenter</label>
				</div>
				<div class="col-md-4">
          <select id="costcenter" name="costcenter" class="form-control input-md chosen-select" <?=$dist;?>>
						<option value="0">Select An Option</option>
						<?php foreach ($results['plan'] as $plan){
              $selected = ($costcenter == $plan->id_costcenter)?'selected':'';
						?>
						<option value="<?= $plan->id_costcenter;?>" <?=$selected;?>><?= strtoupper($plan->nama_costcenter);?></option>
						<?php } ?>
					</select>
				</div>
      </div>
			<div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Production Planning</label>
				</div>
				<div class="col-md-4">
          <input type="hidden" name="no_plan" id="no_plan" value="<?=$no_plan;?>">
          <input type="hidden" name="kolom" id="kolom" >
          <input type="text" name="date_produksi_plan" id="date_produksi_plan"  class="form-control input-md datepicker" placeholder="Select Date" <?=$dist;?> value='<?=$date_akhir;?>'>
          <input type="hidden" name="date_awal" id="date_awal" class="form-control input-md" value='<?=$date_awal;?>'>
          <?php
          if(!empty($no_plan)){
            ?>
            <input type="hidden" name="costcenter" id="costcenter" readonly value='<?=$costcenter;?>'>
            <input type="hidden" name="date_produksi_plan" id="date_produksi_plan" readonly value='<?=$date_akhir;?>'>
            <?php
          }
           ?>
				</div>
      </div>
      <br>
      <div id="tmp_plan">

      </div>
      <span id='notive' style='color:red;font-weight:bold;float:right;font-size:18px;'></span><br><br>
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
    $(".datepicker").datepicker({
      dateFormat: "DD, d MM yy",
      changeMonth: true,
      changeYear: true,
      // minDate: +1,
      maxDate: +30
    });
    //edit
    var no_plan = $("#no_plan").val();
    if(no_plan != ''){
      var date_akhirx = $("#date_produksi_plan").val();
      var date_awal   = $("#date_awal").val();
      var costcenter  = $("#costcenter").val();
      var date_akhir  = moment(date_akhirx).format('YYYY-MM-DD');
      var no_plan     = $("#no_plan").val();

			$.ajax({
				url: base_url+'index.php/'+active_controller+'/get_planning/'+date_akhir+'/'+costcenter+'/'+date_awal+'/'+no_plan,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#tmp_plan").html(data.header);
          $("#kolom").val(data.total);
					$('.chosen_select').select2({width: '100%'});
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

    }

    //end

    $(document).on('keyup', '.get_tot_ct', function(){
		    var get_id  = $(this).attr('id');
        var det_id	= get_id.split('_');
        var row     = det_id[1];
        var col     = det_id[2];
        get_total_ct(row, col)
		});

    //add part
		$(document).on('click', '.addPart', function(){
			// loading_spinner();
      var colspan 		= $(this).data('colspan');
      var colspan2 		= $(this).data('colspan2');
      var tanggal 		= $(this).data('tanggal');
			var get_id 		  = $(this).parent().parent().attr('id');
			var split_id	  = get_id.split('_');
			var id 		      = parseInt(split_id[1])+1;
			var id_bef 	    = split_id[1];

			$.ajax({
				url: base_url+active_controller+'/get_product/'+id+'/'+colspan+'/'+tanggal+'/'+colspan2,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#add_"+id_bef).before(data.header);
					$("#add_"+id_bef).remove();
					$('.chosen_select').select2({width: '100%'});
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

    //get product
    $(document).on('change', '.product', function(){
			// loading_spinner();
      var no 		     = $(this).data('no');
      var product 	 = $(this).val();
      var costcenter = $("#costcenter").val();
      var date_awal  = $("#date_awal").val();
      var date_akhir = $(this).data('tgl_akhir');

			$.ajax({
				url: base_url+active_controller+'/get_product_data/'+no+'/'+product+'/'+costcenter+'/'+date_awal+'/'+date_akhir,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$(".product_"+data.no).val(data.product);
          $(".stock_"+data.no).val(data.stock);
          $(".qty_order_"+data.no).val(data.qty_order);
          $(".shortages_"+data.no).val(data.shortages);
          $(".queue_"+data.no).val(data.queue);
          $(".mp_"+data.no).val(data.mp);
          $(".ct_"+data.no).val(data.ct);

          $("#html_stock_"+data.no).html(data.stock);
          $("#html_qty_order_"+data.no).html(data.qty_order);
          $("#html_shortages_"+data.no).html(data.shortages);
          $("#html_queue_"+data.no).html(data.queue);

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

    //back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller +'/production_planning';
		});

    $(document).on('change', '#costcenter', function(e){
			// loading_spinner();
      e.preventDefault();
      var costcenter		        = $('#costcenter').val();
			$.ajax({
				url: base_url+'index.php/'+active_controller+'/get_maxdate/'+costcenter,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
          $(".datepicker").datepicker("destroy");
          $(".datepicker").val("");
          $("#tmp_plan").html("");
          $("#date_awal").val(data.min_date);
          $("#date_akhir").val(data.max_date);
          $(".datepicker").datepicker({
            dateFormat: "DD, d MM yy",
            changeMonth: true,
            changeYear: true,
            minDate: new Date(data.min_date),
            maxDate: new Date(data.max_date)
          });
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

    $(document).on('change', '#date_produksi_plan', function(e){
			// loading_spinner();
      e.preventDefault();
      var costcenter		        = $('#costcenter').val();

      if(costcenter == '0' ){
        swal({
          title	: "Error Message!",
          text	: 'Costcenter name empty, select first ...',
          type	: "warning"
        });
        $(this).val('');
        return false;
      }

			var plan_date = new Date($(this).val());
      var date      = moment(plan_date).format('YYYY-MM-DD');

			$.ajax({
				url: base_url+'index.php/'+active_controller+'/get_planning/'+date+'/'+costcenter,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
					$("#tmp_plan").html(data.header);
          $("#kolom").val(data.total);
					$('.chosen_select').select2({width: '100%'});
					$('.maskM').maskMoney();
					swal.close();
          // var a;
          // var SUM;
          // var ORDER;
          // var stock;
          // var suggest;
          // for(a=1; a<=data.total; a++){
          //   SUM = 0;
          //   $(".material_"+a ).each(function() {
          //     stock = Number($("#stock_"+a).val());
        	// 		SUM += Number($(this).val());
          //     ORDER = SUM * 3;
          //     suggest = ORDER - stock;
          //   });
          //   $(".sum_"+a).html(SUM.toFixed(2));
          //   $(".order_"+a).html(ORDER.toFixed(2));
          //   $(".suggest_"+a).html(suggest.toFixed(2));
          //
          //   $(".sum_"+a).val(SUM.toFixed(2));
          //   $(".order_"+a).val(ORDER.toFixed(2));
          //   $(".suggest_"+a).val(suggest.toFixed(2));
          //   $(".maskM").maskMoney();
          // }

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
      var plan_date = $("#plan_date").val();
      if(plan_date == '0' ){
				swal({title	: "Error Message!",text	: 'Plan Planning empty, select first ...',type	: "warning"});
				$('#save').prop('disabled',false); return false;
			}

      var kolom = $("#kolom").val();
      var a;
      for(a=0; a<kolom; a++){
        var man =  getNum($("#tot_ct_"+a).val().split(",").join(""));
        var max =  getNum($("#man_ct_"+a).val().split(",").join(""));
        if(man > max){
          $("#save").hide();
          swal({title	: "Error Message!",text	: 'Man minutes melebihi availibility, check again ...',type	: "warning"});
          return false;
        }
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
						var baseurl=siteurl+'produksi/add_production_planning';
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
									window.location.href = base_url + active_controller+'/production_planning';
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

function get_total_ct(row, col){
  var qtyp_   = getNum($("#qtyp_"+row+"_"+col).val().split(",").join(""));
  var mp_     = getNum($("#mp_"+row+"_"+col).val().split(",").join(""));
  var ct_     = getNum($("#ct_"+row+"_"+col).val().split(",").join(""));
  var tot_ct_ = qtyp_ * ct_;
  if(isNaN(tot_ct_) || tot_ct_ == ''){
    var tot_ct_ = 0;
  }

  $("#tot_ct_"+row+"_"+col).val(getNum(tot_ct_));

  var SUM = 0;
  $(".tot_ct_"+col ).each(function() {
		SUM += getNum(Number($(this).val().split(",").join("")));
    // console.log(getNum(Number($(this).val().split(",").join(""))));
  });
  // console.log(tot_ct_);
  // console.log(SUM);
  $("#tot_ct_"+col).val(SUM);

  var kolom = $("#kolom").val();
  var a;
  for(a=0; a<kolom; a++){
    var man =  getNum($("#tot_ct_"+a).val().split(",").join(""));
    var max =  getNum($("#man_ct_"+a).val().split(",").join(""));
    if(man <= max){
      $("#save").show();
      $("#notive").html("");
      // return false;
    }
    if(man > max){
      $("#save").hide();
      $("#notive").html("Man minutes melebihi Availability man minutes");
      // swal({title	: "Error Message!",text	: 'Man minutes melebihi availibility, check again ...',type	: "warning"});
      return false;
    }

  }

}

function getNum(val) {
   if (isNaN(val) || val == '') {
     return 0;
   }
   return parseFloat(val);
}


</script>
