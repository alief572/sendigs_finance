
 <div class="box box-primary">
    <div class="box-body">
		<form id="data-form" method="post"><br>
      <div class="form-group row">
				<div class="col-md-2">
					<label for="customer">Select Date</label>
				</div>
				<div class="col-md-4">
          <input type="text" name="date_range" id="date_range" class="form-control input-md datepicker" style='margin-bottom:5px;' readonly="readonly" placeholder="Select Date">
        </div>
      </div>

      <br>
      <div id="tmp_plan">

      </div>
  </form>
	</div>
</div>


<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<style media="screen">
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
    $('.datepicker').daterangepicker({
      showDropdowns: true,
      autoUpdateInput: false,
      locale: {
        cancelLabel: 'Clear'
      }
    });

    $('.datepicker').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
        var range = $(this).val();

        var tgl_awal 	= '0';
        var tgl_akhir 	= '0';
        if(range != ''){
        	var sPLT 		= range.split(' - ');
        	var tgl_awal 	= sPLT[0];
        	var tgl_akhir 	= sPLT[1];
        }

        $.ajax({
  				url: base_url + active_controller+'/get_productivity/'+tgl_awal+'/'+tgl_akhir,
  				cache: false,
  				type: "POST",
  				dataType: "json",
  				success: function(data){
  					$("#tmp_plan").html(data.header);
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

  });

</script>
