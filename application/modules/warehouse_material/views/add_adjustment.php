
 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post" autocomplete="off"><br>
      <div class="form-group row">
        <div class="col-md-1">
					<label for="customer">Warehouse <span class="text-red">*</span></label>
				</div>
				<div class="col-md-3">
          <select id="kd_gudang" name="kd_gudang" class="form-control input-md chosen-select">
						<option value="0">Select An Warehouse</option>
            <?php foreach (get_warehouse() AS $val => $valx){ ?>
						        <option value="<?= $valx['id'];?>"><?= strtoupper($valx['nm_gudang']);?></option>
						<?php } ?>
					</select>
        </div>

        <div class="col-md-1">
					<label for="customer">Material <span class="text-red">*</span></label>
				</div>
				<div class="col-md-3">
          <select id="material" name="material" class="form-control input-md chosen-select" >
            <option value='0'>All Material</option>
  					<?php
  					foreach(get_material() AS $val => $valx){
  						echo "<option value='".$valx['code_material']."'>".strtoupper($valx['nm_material'])."</option>";
  					}
  					?>
					</select>
				</div>

        <div class="col-md-1">
					<label for="customer">Stock Awal</label>
				</div>
				<div class="col-md-1">
          <input type="text" name="stock_awal" id="stock_awal"  class="form-control input-md" placeholder="Stock Awal" readonly>
        </div>
        <div class="col-md-2">
          <select id="unit" name="unit" class="form-control input-md chosen-select unit" >
						<option value="0">Empty</option>
					</select>
				</div>
      </div>

      <div class="form-group row">
				<div class="col-md-1">
					<label for="customer">Adjustment</label>
				</div>
				<div class="col-md-3">
          <select id="adjustment" name="adjustment" class="form-control input-md chosen-select">
						<option value="plus">PLUS</option>
            <option value="minus">MINUS</option>
					</select>
				</div>

        <div class="col-md-1">
					<label for="customer">Qty <span id='tandax'>Plus</span> <span class="text-red">*</span></label>
				</div>
				<div class="col-md-3">
          <input type="text" name="qty" id="qty"  class="form-control input-md maskM" placeholder="Qty" data-decimal="." data-thousand="" data-precision="0" data-allow-zero="">
          <input type="hidden" name="tanda" id="tanda"  class="form-control input-md" >
        </div>

        <div class="col-md-1">
					<label for="customer">Stock Akhir</label>
				</div>
				<div class="col-md-1">
          <input type="text" name="stock_akhir" id="stock_akhir"  class="form-control input-md" placeholder="Stock Akhir" readonly>
        </div>
        <div class="col-md-2">
          <select id="unitx" name="unitx" class="form-control input-md chosen-select unit" disabled>
						<option value="0">Empty</option>
					</select>
				</div>
      </div>

      <div class="form-group row">
				<div class="col-md-1">
					<label for="customer">No Surat Jalan <span class="text-red">*</span></label>
				</div>
        <div class="col-md-3">
					<input type="text" name="surat_jalan" id="surat_jalan"  class="form-control input-md" placeholder="No. Surat Jalan">
				</div>

        <div class="col-md-1">
					<label for="customer">Reason</label>
				</div>
				<div class="col-md-7">
          <textarea type="text" name="reason" id="reason" rows="3"  class="form-control input-md" placeholder="Reason"></textarea>
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
    $('.maskM').maskMoney();

    //back
		$(document).on('click', '#back', function(){
		    window.location.href = base_url + active_controller +'/adjustment_material';
		});

    $(document).on('change', '#material, #kd_gudang', function(e){
      // loading_spinner();
      e.preventDefault();
      var material		  = $('#material').val();
      var kd_gudang		  = $('#kd_gudang').val();
      var qty		  = getNum($('#qty').val());

      if(material == '0' ){
        return false;
      }

      if(kd_gudang == '0' ){
        return false;
      }

      $.ajax({
        url: base_url+active_controller+'/get_stock/'+material+'/'+kd_gudang,
        cache: false,
        type: "POST",
        dataType: "json",
        success: function(data){
          $("#stock_awal").val(data.stock);
          $("#tanda").val(data.tanda);
          $(".unit").html(data.option).trigger("chosen:updated");
          get_stock_akhir();
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

    $(document).on('change', '#adjustment', function(e){
      var adjustment = $(this).val();
      if(adjustment == 'plus'){
        $("#tandax").html("Plus");
      }
      if(adjustment == 'minus'){
        $("#tandax").html("Minus");
      }
      get_stock_akhir();
    });

    $(document).on('change', '#unit', function(e){
      var unit = $(this).val();
      if(unit == 'kg' || unit == 'pcs'){
        $("#tanda").val("unit");
      }
      if(unit != 'kg' && unit != 'pcs'){
        $("#tanda").val("packing");
      }
      $("#unitx").html("<option value='"+unit+"'>"+unit.toUpperCase()+"</option>").trigger("chosen:updated");

      var material		  = $('#material').val();
      var kd_gudang		  = $('#kd_gudang').val();

      if(material == '0' ){
        return false;
      }

      if(kd_gudang == '0' ){
        return false;
      }

      $.ajax({
        url: base_url+active_controller+'/get_stock/'+material+'/'+kd_gudang+'/'+unit,
        cache: false,
        type: "POST",
        dataType: "json",
        success: function(data){
          $("#stock_awal").val(data.stock);
          get_stock_akhir();
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

    $(document).on('keyup', '#qty', function(e){
      get_stock_akhir();
    });



		$('#save').click(function(e){
			e.preventDefault();
      var material = $("#material").val();
      var kd_gudang = $("#kd_gudang").val();
      var qty = $("#qty").val();
      var reason = $("#reason").val();

      if(material == '0' ){
				swal({title	: "Error Message!",text	: 'material empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
      if(kd_gudang == '0' ){
				swal({title	: "Error Message!",text	: 'Product empty, select first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
      if(qty == '' ){
				swal({title	: "Error Message!",text	: 'Qty empty, input first ...',type	: "warning"
				});
				$('#save').prop('disabled',false); return false;
			}
      // if(reason == '' ){
			// 	swal({title	: "Error Message!",text	: 'Reason empty, input first ...',type	: "warning"
			// 	});
			// 	$('#save').prop('disabled',false); return false;
			// }

      // alert('Proses belum dapat dilakukan');
      // return false;
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
						var baseurl=siteurl + active_controller+'/add_adjustment';
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
									window.location.href = base_url + active_controller+'/adjustment_material';
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

function get_stock_akhir(){
  var adjustment  = $("#adjustment").val();
  var stock_awal  = getNum($("#stock_awal").val().split(",").join(""));
  var qty         = getNum($("#qty").val().split(",").join(""));
  if(adjustment == 'plus'){
    var stock_akhir = stock_awal + qty;
  }
  if(adjustment == 'minus'){
    var stock_akhir = stock_awal - qty;
  }
  if(stock_akhir < 0){
    var stock_akhir = 0;
  }
  // console.log(stock_akhir);
  return $("#stock_akhir").val(stock_akhir.toFixed(2));
}

</script>
