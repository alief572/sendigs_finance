<?php
    $ENABLE_ADD     = has_permission('SPK.Add');
    $ENABLE_MANAGE  = has_permission('SPK.Manage');
    $ENABLE_VIEW    = has_permission('SPK.View');
    $ENABLE_DELETE  = has_permission('SPK.Delete');
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
  					<option value='0'>Select Costcenter</option>
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
          <b>Project</b>
        </div>
        <div class="col-md-3">
          <div class="input-group ">
            <span class="input-group-addon" id="basic-addon2">
              <input type="checkbox" name="check_all" class="checkbox" id="check_all">
            </span>
            <select name='project[]' id='project' class='form-control input-sm chosen-select' multiple data-select="false">
    					<?php
    					foreach(get_project() AS $val => $valx){
    						echo "<option value='".$valx['id_category1']."'>".strtoupper($valx['nama'])."</option>";
    					}
    					?>
    				</select>
          </div>
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

    $("#check_all").click(function(){
        if($("#check_all").is(':checked') ){
            $("#project > option").prop("selected","selected");// Select All Options
            $("#project").trigger("change");// Trigger change to select 2
        }else{
            $("#project > option").removeAttr("selected");
            $("#project").trigger("change");// Trigger change to select 2
         }
    });


    $(document).on('click', '#print', function(e){
      e.preventDefault();
      var costcenter	= $('#costcenter').val();
      var projectX	= $('#project').val();
      var project = projectX.toString().split(",").join("-");
      // console.log(proX);
      // return false;

      var range		    = $('#date_range').val();
      if(costcenter == '0' ){
        swal({
          title	: "Error Message!",
          text	: 'Costcenter is empty, select first ...',
          type	: "warning"
        });
        return false;
      }
      if(project == '0' ){
        swal({
          title	: "Error Message!",
          text	: 'Project is empty, select first ...',
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

      var Link	= base_url + active_controller +'print_plan_custom/'+costcenter+'/'+tgl_awal+'/'+tgl_akhir+'/'+project;
		  window.open(Link);

		});

  });
</script>
