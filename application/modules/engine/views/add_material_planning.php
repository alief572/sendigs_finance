<?php

$no_plan        = (!empty($header))?$header[0]->no_plan:'';
$plan_date      = (!empty($header))?$header[0]->tahun."-".$header[0]->bulan:'';
$project        = (!empty($header))?$header[0]->project:'';
$dist        = (!empty($header[0]->no_plan))?'disabled':'readonly';
// print_r($header);
// echo $project;
?>

 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post"><br>
      <!--
      <div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Group Material</label>
				</div>
				<div class="col-md-4">
          <select id="kelompok" name="kelompok" class="form-control input-md chosen-select" <?=$dist;?>>
						<option value="0">Select An Group Material</option>
            <option value="import">Material Import</option>
            <option value="local">Material Local</option>
            <option value="fitting">Material Fitting</option>
					</select>
				</div>
      </div>
    -->
    <div class="form-group row">
      <div class="col-md-2">
        <label for="customer">Month Planning</label>
      </div>
      <div class="col-md-4">
        <input type="hidden" id="no_plan" name="no_plan" value="<?=$no_plan;?>">
        <select id="plan_date" name="plan_date" class="form-control input-md chosen-select" <?=$dist;?>>
          <option value="0">Select An Option</option>
          <?php foreach ($results['plan'] as $plan){
            $sel = ($plan->tahun.'-'.$plan->bulan == $plan_date)?'selected':'';
          ?>
          <option value="<?= $plan->tahun.'-'.$plan->bulan;?>" <?=$sel;?>><?= date('F Y',strtotime($plan->tahun.'-'.$plan->bulan.'-01'))?></option>
          <?php } ?>
        </select>
        <?php
        if(!empty($no_plan)){
          ?>
          <input type="hidden" name="plan_date" id="plan_date" readonly value='<?=$plan_date;?>'>
          <?php
        }
         ?>
      </div>
    </div>
      <div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Project Name</label>
				</div>
				<div class="col-md-4">
          <select id="project" name="project" class="form-control input-md chosen-select" <?=$dist;?>>
						<option value="0">Select An Option</option>
						<?php foreach (get_project() AS $val => $valx){
              $selx = ($project == $valx['id_category1'])?'selected':'';
						?>
						<option value="<?= $valx['id_category1'];?>" <?=$selx;?>><?= strtoupper($valx['nama']);?></option>
						<?php } ?>
					</select>
          <?php
          if(!empty($no_plan)){
            ?>
            <input type="hidden" name="project" id="project" readonly value='<?=$project;?>'>
            <?php
          }
           ?>
				</div>
      </div>

      <br>
      <div id="tmp_plan">

      </div>
      <button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
      <button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>

		</form>
	</div>
</div>
<style>

/* JUST COMMON TABLE STYLES... */
.table { border-collapse: collapse; width: 100%; }
.td { background: #fff; padding: 8px 16px; }


.tableFixHead {
  overflow: auto;
  height: 100px;
}

thead th {
  position: sticky;
  top: 0;
  z-index: 99999;
	background: #0073b7;
}

td:first-child{
  position:sticky;
  left:0;
  z-index: 9999;
  background-color:#e7dbdb;
  font-weight: bold;
}

th:first-child {
  position:sticky;
  left:0;
  z-index: 999999;
  background-color:#0073b7 ;
}

</style>

<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style media="screen">
  .datepicker{
    cursor: pointer;
    padding-left: 12px;
  }
</style>
<script type="text/javascript">
	//$('#input-kendaraan').hide();
  window.onload = function(){
    var tableCont = document.querySelector('#table-cont');
    /**
     * scroll handle
     * @param {event} e -- scroll event
     */
    function scrollHandle (e){
      var scrollTop = this.scrollTop;
      this.querySelector('thead').style.transform = 'translateY(' + scrollTop + 'px)';
    }

    tableCont.addEventListener('scroll',scrollHandle);
  }

	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
    $('.hide_now').hide();
		$('.chosen-select').select2();
    $( ".datepicker" ).datepicker({
      format: "mm-yyyy",
      viewMode: "months",
      minViewMode: "months",
      minDate: "0",
    });

    var no_plan = $("#no_plan").val();
    // var kelompok = $("#kelompok").val();
    var project = $("#project").val();
    if(no_plan != ''){
      var plan_date 		= $("#plan_date").val();

			$.ajax({
				url: base_url+active_controller+'/get_planning/'+plan_date+'/'+project+'/'+no_plan,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
          if(data.status == 1){
  					$("#tmp_plan").html(data.header);
  					$('.chosen_select').select2({width: '100%'});
  					$('.maskM').maskMoney();
  					swal.close();
            var a;
            var SUM;
            var ORDER;
            var stock;
            var suggest;
            for(a=1; a<=data.total; a++){
              SUM = 0;
              $(".material_"+a ).each(function() {
                stock = Number($("#stock_"+a).val());
          			SUM += Number($(this).val());
                ORDER = SUM * 3;
                suggest = ORDER - stock;
              });
              if(suggest < 0){
                suggest = 0;
              }
              $(".sum_"+a).html(SUM.toFixed(2));
              $(".order_"+a).html(ORDER.toFixed(2));
              $(".suggest_"+a).html(suggest.toFixed(2));

              $(".sum_"+a).val(SUM.toFixed(2));
              $(".order_"+a).val(ORDER.toFixed(2));
              $(".suggest_"+a).val(suggest.toFixed(2));
              $(".maskM").maskMoney();

              $('.hide_now').hide();
            }
          }else{
            swal({
              title	: "Save Failed!",
              text	: data.pesan,
              type	: "warning",
              timer	: 5000,
              showCancelButton	: false,
              showConfirmButton	: false,
              allowOutsideClick	: false
            });
          }

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



    //back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller +'/material_planning';
		});

    $(document).on('change', '#plan_date, #kelompok, #project', function(e){
			// loading_spinner();
      e.preventDefault();
			var plan_date 		= $("#plan_date").val();
      // var kelompok 		= $("#kelompok").val();
      var project 		= $("#project").val();
      if(project == '0' ){
				// swal({
				// 	title	: "Error Message!",
				// 	text	: 'Project is empty, select first ...',
				// 	type	: "warning"
				// });
        //
				// $('#search').prop('disabled',false);
				return false;
			}
      if(plan_date == '0' ){
				// swal({
				// 	title	: "Error Message!",
				// 	text	: 'Month is empty, select first ...',
				// 	type	: "warning"
				// });
        //
				// $('#search').prop('disabled',false);
				return false;
			}

			$.ajax({
				url: base_url+'index.php/'+active_controller+'/get_planning/'+plan_date+'/'+project,
				cache: false,
				type: "POST",
				dataType: "json",
				success: function(data){
          if(data.status == 1){
  					$("#tmp_plan").html(data.header);
  					$('.chosen_select').select2({width: '100%'});
  					$('.maskM').maskMoney();
  					swal.close();
            var a;
            var SUM;
            var ORDER;
            var stock;
            var suggest;
            for(a=1; a<=data.total; a++){
              SUM = 0;
              $(".material_"+a ).each(function() {
                stock = Number($("#stock_"+a).val());
          			SUM += Number($(this).val());
                ORDER = SUM * 3;
                suggest = ORDER - stock;
              });

              if(suggest < 0){
                suggest = 0;
              }

              $(".sum_"+a).html(SUM.toFixed(2));
              $(".order_"+a).html(ORDER.toFixed(2));
              $(".suggest_"+a).html(suggest.toFixed(2));

              $(".sum_"+a).val(SUM.toFixed(2));
              $(".order_"+a).val(ORDER.toFixed(2));
              $(".suggest_"+a).val(suggest.toFixed(2));
              $(".maskM").maskMoney();

              $('.hide_now').hide();
            }
          }else{
            swal({
              title	: "Save Failed!",
              text	: data.pesan,
              type	: "warning",
              timer	: 5000,
              showCancelButton	: false,
              showConfirmButton	: false,
              allowOutsideClick	: false
            });
          }

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
      var project 		= $("#project").val();
      if(project == '0' ){
        swal({
          title	: "Error Message!",
          text	: 'Project is empty, select first ...',
          type	: "warning"
        });

        $('#save').prop('disabled',false);
        return false;
      }
      if(plan_date == '0' ){
				swal({title	: "Error Message!",text	: 'Plan Planning empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false);
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
						var baseurl=siteurl+'engine/add_material_planning';
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
									window.location.href = base_url + active_controller+'/material_planning';
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
