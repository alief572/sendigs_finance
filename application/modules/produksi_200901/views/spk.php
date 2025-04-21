<?php
    $ENABLE_ADD     = has_permission('Cycletime.Add');
    $ENABLE_MANAGE  = has_permission('Cycletime.Manage');
    $ENABLE_VIEW    = has_permission('Cycletime.View');
    $ENABLE_DELETE  = has_permission('Cycletime.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<?php
// echo get_name_field('ms_costcenter', 'nama_costcenter', 'id_costcenter', get_last_costcenter_warehouse('I2000040'));
?>
<form id="form_prosee" method="post"><br>
  <div class="box">
  	<div class="box-header">
      <br>
      <div class="form-group row">
        <div class="col-md-1">
          <b>Costcenter</b>
        </div>
        <div class="col-md-3">
          <select name='costcenter' id='costcenter' class='form-control input-sm chosen-select'>
  					<option value='0'>All Costcenter</option>
  					<?php
  					foreach(get_costcenter() AS $val => $valx){
  						echo "<option value='".$valx['id_costcenter']."'>".strtoupper($valx['nama_costcenter'])."</option>";
  					}
  					?>
  				</select>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-1">
          <b>Select Date</b>
        </div>
        <div class="col-md-3">
          <input type="text" name="date_range" id="date_range"  class="form-control input-md datepicker" placeholder="Select Date" autocomplete="off" readonly>
        </div>
      </div>

      <div class="form-group row">
        <div class="col-md-1"></div>
        <div class="col-md-11">
          <button type="button" class="btn btn-success" style='min-width:100px;' name="print" id="print"><i class="fa fa-print"></i> &nbsp;Download SPK</button>
        </div>
      </div>
  	</div>
  	<!-- /.box-header -->

  	<!-- /.box-body -->
  </div>
</form>



<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<style media="screen">
.datepicker{
  cursor: pointer;
}
</style>
<!-- page script -->
<script type="text/javascript">

  $(document).ready(function(){
    $('.datepicker').daterangepicker({
			showDropdowns: true,
			autoUpdateInput: false,
			locale: {
				cancelLabel: 'Clear'
			}
		});

		$('.datepicker').on('apply.daterangepicker', function(ev, picker) {
			$(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
		});

		$('.datepicker').on('cancel.daterangepicker', function(ev, picker) {
			$(this).val('');
		});

    $(document).on('click', '#print', function(e){
      e.preventDefault();
      var costcenter	= $('#costcenter').val();
      var range		    = $('#date_range').val();
      if(costcenter == '0' ){
        swal({
          title	: "Error Message!",
          text	: 'Costcenter is empty, select first ...',
          type	: "warning"
        });
        return false;
      }

      if(range == '' ){
        swal({
          title	: "Error Message!",
          text	: 'Date is empty, select first ...',
          type	: "warning"
        });
        return false;
      }

      var tgl_awal 	= '0';
      var tgl_akhir 	= '0';
      if(range != ''){
      	var sPLT 		= range.split(' - ');
      	var tgl_awal 	= sPLT[0];
      	var tgl_akhir 	= sPLT[1];
      }

      var Link	= base_url + active_controller +'print_plan_custom/'+costcenter+'/'+tgl_awal+'/'+tgl_akhir;
		  window.open(Link);

		});

  });
</script>
