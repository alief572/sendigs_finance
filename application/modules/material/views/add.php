<?php

$code_material  = (!empty($header))?$header[0]->code_material:'';
$code_company   = (!empty($header))?$header[0]->code_company:'';
$nm_material    = (!empty($header))?strtoupper($header[0]->nm_material):'';
$satuan_packing = (!empty($header))?$header[0]->satuan_packing:'';
$konversi       = (!empty($header))?$header[0]->konversi:'';
$unit           = (!empty($header))?$header[0]->unit:'';
$begin_balance  = (!empty($header))?$header[0]->begin_balance:'';
$kelompok       = (!empty($header))?$header[0]->kelompok:'';
// $incoming       = (!empty($header))?$header[0]->incoming:'';
// $outgoing       = (!empty($header))?$header[0]->outgoing:'';
// $ending_balance = (!empty($header))?$header[0]->ending_balance:'';
// $unit_fisik     = (!empty($header))?$header[0]->unit_fisik:'';

// print_r($header);
?>

 <div class="box box-primary">
    <div class="box-body"><br>
		<form id="data-form" method="post" autocomplete="off">
      <div class="form-group row">
        <div class="col-md-2">
					<label>Material Group <span class="text-red">*</span></label>
				</div>
				<div class="col-md-4">
					<select id="kelompok" name="kelompok" class="form-control input-md chosen-select" required>
						<option value="0">Select An Option</option>
            <?php foreach ($results['kelompok'] as $kel){
              $sel = ($kel->value == $kelompok)?'selected':'';
            ?>
            <option value="<?= $kel->value;?>" <?=$sel;?>><?= strtoupper(strtolower($kel->view))?></option>
						<?php } ?>
					</select>
				</div>
      </div>
			<div class="form-group row">
        <div class="col-md-2">
          <label>Code Material <span class="text-red">*</span></label>
        </div>
        <div class="col-md-4">
          <input type="text" id="code_company" name="code_company" class="form-control input-md" placeholder="Code Material" value='<?=$code_company;?>'>
          <input type="hidden" id="code_material" name="code_material" class="form-control input-md" readonly  value='<?=$code_material;?>'>
        </div>
        <div class="col-md-2">
          <label>Material Name <span class="text-red">*</span></label>
        </div>
        <div class="col-md-4">
          <input type="text" id="nm_material" name="nm_material" class="form-control input-md" placeholder="Material Name" value='<?=$nm_material;?>'>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-2">
					<label>Packing Unit <span class="text-red">*</span></label>
				</div>
				<div class="col-md-4">
					<select id="satuan_packing" name="satuan_packing" class="form-control input-md chosen-select" required>
						<option value="0">Select An Option</option>
            <?php foreach ($results['satuan_packing'] as $satuan){
              $sel = ($satuan->id == $satuan_packing)?'selected':'';
            ?>
            <option value="<?= $satuan->id;?>" <?=$sel;?>><?= strtoupper(strtolower($satuan->code))?></option>
						<?php } ?>
					</select>
				</div>
        <div class="col-md-2">
          <label>Conversion <span class="text-red">*</span></label>
        </div>
        <div class="col-md-4">
          <input type="text" id="konversi" name="konversi" class="form-control input-md maskM" placeholder="Conversion" value='<?=$konversi;?>'>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-2">
          <label>Unit</label>
        </div>
        <div class="col-md-4">
          <select id="unit" name="unit" class="form-control input-md chosen-select" required>
            <option value="0">Select An Option</option>
            <?php foreach ($results['satuan'] as $satuan){
              $sel = ($satuan->id == $unit)?'selected':'';
            ?>
            <option value="<?= $satuan->id;?>" <?=$sel;?>><?= strtoupper(strtolower($satuan->code))?></option>
            <?php } ?>
          </select>
        </div>
        <div class="col-md-2">
          <label>Begin Balance /kg <span class="text-red">*</span></label>
        </div>
        <div class="col-md-4">
          <input type="text" id="begin_balance" name="begin_balance" class="form-control input-md maskM" placeholder="Begin Balance /kg" value='<?=$begin_balance;?>'>
        </div>
      </div>
      <!-- <div class="form-group row">
        <div class="col-md-2">
          <label>Incoming /kg <span class="text-red">*</span></label>
        </div>
        <div class="col-md-4">
          <input type="text" id="incoming" name="incoming" class="form-control input-md maskM" placeholder="Incoming /kg" value='<?=$incoming;?>'>
        </div>
        <div class="col-md-2">
          <label>Outgoing /kg <span class="text-red">*</span></label>
        </div>
        <div class="col-md-4">
          <input type="text" id="outgoing" name="outgoing" class="form-control input-md maskM" placeholder="Outgoing /kg" value='<?=$outgoing;?>'>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-2">
          <label>Ending Balance /kg <span class="text-red">*</span></label>
        </div>
        <div class="col-md-4">
          <input type="text" id="ending_balance" name="ending_balance" class="form-control input-md maskM" placeholder="Ending Balance /kg" value='<?=$ending_balance;?>'>
        </div>
        <div class="col-md-2">
          <label>Unit Fisik</label>
        </div>
        <div class="col-md-4">
          <input type="text" id="unit_fisik" name="unit_fisik" class="form-control input-md" placeholder="Unit Fisik" readonly value='<?=$unit_fisik;?>'>
        </div>
      </div> -->
      <button type="button" class="btn btn-danger" style='float:right; margin-left:5px;' name="back" id="back"><i class="fa fa-reply"></i> Back</button>
      <button type="submit" class="btn btn-primary" style='float:right;' name="save" id="save"><i class="fa fa-save"></i> Save</button>

		</form>
	</div>
</div>


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
	var base_url			= '<?php echo base_url(); ?>';
	var active_controller	= '<?php echo($this->uri->segment(1)); ?>';

	$(document).ready(function(){
		$('.chosen-select').select2();
    $( ".datepicker" ).datepicker();
    $('.maskM').maskMoney();

    $(document).on('keyup','#konversi, #ending_balance', function(){
      get_unit_fisik();
    });

    $(document).on('change', '#kelompok', function(){
      // loading_spinner();
      var kelompok = $(this).val();
      $.ajax({
        url: base_url+'index.php/'+active_controller+'/get_unit/'+kelompok,
        cache: false,
        type: "POST",
        dataType: "json",
        success: function(data){
          $("#satuan_packing").html(data.option).trigger("chosen:updated");
          $("#unit").html(data.option).trigger("chosen:updated");
          $("#konversi").val(data.konversi);
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

    $(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller;
		});

		$(document).on('click','#save', function(e){
			e.preventDefault();
      // alert('tahan');
      // return false;
			var code_company		= $('#code_company').val();
      var nm_material	    = $('#nm_material').val();
      var satuan_packing	= $('#satuan_packing').val();
      var konversi		    = $('#konversi').val();
      var unit	          = $('#unit').val();
      // var begin_balance		= $('#begin_balance').val();
      // var incoming		    = $('#incoming').val();
      // var outgoing	      = $('#outgoing').val();
      // var ending_balance	= $('#ending_balance').val();

			if(code_company == '' ){
				swal({title	: "Error Message!",text	: 'Material code empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
      if(nm_material == '' ){
				swal({title	: "Error Message!",text	: 'Material name empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
      if(satuan_packing == '0' ){
				swal({title	: "Error Message!",text	: 'Packing Unit empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
      if(konversi == '' ){
				swal({title	: "Error Message!",text	: 'Convertion empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
      if(unit == '0' ){
				swal({title	: "Error Message!",text	: 'Unit empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
      if(begin_balance == '' ){
				swal({title	: "Error Message!",text	: 'Begin balance empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
      // if(incoming == '' ){
			// 	swal({title	: "Error Message!",text	: 'Incoming empty, select first ...',type	: "warning"
			// 	});
			// 	$('#save').prop('disabled',false); return false;
			// }
      // if(outgoing == '' ){
			// 	swal({title	: "Error Message!",text	: 'Outgoing empty, select first ...',type	: "warning"
			// 	});
			// 	$('#save').prop('disabled',false); return false;
			// }
      // if(ending_balance == '' ){
			// 	swal({title	: "Error Message!",text	: 'Ending Balance empty, select first ...',type	: "warning"
			// 	});
			// 	$('#save').prop('disabled',false); return false;
			// }


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
						var baseurl=siteurl+'material/add';
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
									window.location.href = base_url + active_controller;
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

function get_unit_fisik(){
  var konversix       = $("#konversi").val();
  var ending_balancex = $("#ending_balance").val();

  var konversi	      = parseFloat(konversix.split(',').join(''));
  var ending_balance	= parseFloat(ending_balancex.split(',').join(''));

  var unit_fisik	= (ending_balance / konversi);
  if(isNaN(unit_fisik)){
    var unit_fisik = 0;
  }
  $('#unit_fisik').val(unit_fisik.toFixed(2));

}

</script>
