<?php
    $ENABLE_ADD     = has_permission('Material_History.Add');
    $ENABLE_MANAGE  = has_permission('Material_History.Manage');
    $ENABLE_VIEW    = has_permission('Material_History.View');
    $ENABLE_DELETE  = has_permission('Material_History.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
	<div class="box-header"><br>
    <div class="form-group row">
      <div class="col-md-1">
        <b>Warehouse</b>
      </div>
      <div class="col-md-3">
        <select name='kd_gudang' id='kd_gudang' class='form-control input-sm chosen-select'>
          <option value='0'>All Warehouse</option>
          <?php
          foreach(get_warehouse() AS $val => $valx){
            echo "<option value='".$valx['id']."'>".strtoupper($valx['nm_gudang'])."</option>";
          }
          ?>
        </select>
      </div>
      <div class="col-md-1">
        <b>Range Date</b>
      </div>
      <div class="col-md-3">
        <input type="text" name="date_range" id="date_range"  class="form-control input-md datepicker" placeholder="Select Date" autocomplete="off" readonly>
      </div>
    </div>
    <div class="form-group row">
      <div class="col-md-1">
        <b>Material</b>
      </div>
      <div class="col-md-7">
        <select name='material' id='material' class='form-control input-sm chosen-select'>
          <option value='0'>All Material</option>
          <?php
          foreach(get_list_inventory_lv4('material') AS $val => $valx){
            echo "<option value='".$valx['code_lv4']."'>".strtoupper($valx['nama'])."</option>";
          }
          ?>
        </select>
      </div>
    </div>
    <div class="form-group row">
      <div class="col-md-1">
        <b></b>
      </div>
     
      <div class="col-md-3">
        <button type="button" class="btn btn-sm btn-success" style='min-width:100px;' name="print" id="print">Download Excel</button>
      </div>
    </div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
      <th class="text-center">#</th>
			<th class="text-center">Material</th>
      <th class="text-center">Tanggal</th>
      <th class="text-center">Gudang</th>
      <th class="text-center">Weight</th>
      <th class="text-center">Dari</th>
      <th class="text-center">Ke</th>
      <th class="text-center">Stock Awal</th>
      <th class="text-center">Stock Akhir</th>
      <th class="text-center">Update By</th>
      <th class="text-center">Update Date</th>
		</tr>
		</thead>
		<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

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

    var kd_gudang     = $("#kd_gudang").val();
    var material  = $("#material").val();
    var range		    = $('#date_range').val();
    var tgl_awal 	= '0';
    var tgl_akhir 	= '0';
    if(range != ''){
      var sPLT 		= range.split(' - ');
      var tgl_awal 	= sPLT[0];
      var tgl_akhir 	= sPLT[1];
    }
    DataTables(material, kd_gudang, tgl_awal, tgl_akhir);

    $(document).on('change','#material, #kd_gudang', function(){
      var material  = $("#material").val();
      var kd_gudang     = $("#kd_gudang").val();
      var range		    = $('#date_range').val();
      var tgl_awal 	= '0';
      var tgl_akhir 	= '0';
      if(range != ''){
      	var sPLT 		= range.split(' - ');
      	var tgl_awal 	= sPLT[0];
      	var tgl_akhir 	= sPLT[1];
      }
        DataTables(material, kd_gudang, tgl_awal, tgl_akhir);
    });

    $('.datepicker').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
      var material  = $("#material").val();
      var kd_gudang     = $("#kd_gudang").val();
      var range		    = $('#date_range').val();
      var tgl_awal 	= '0';
      var tgl_akhir 	= '0';
      if(range != ''){
      	var sPLT 		= range.split(' - ');
      	var tgl_awal 	= sPLT[0];
      	var tgl_akhir 	= sPLT[1];
      }
      DataTables(material, kd_gudang, tgl_awal, tgl_akhir);

    });

    $('.datepicker').on('cancel.daterangepicker', function(ev, picker) {
      $(this).val('');
      var material  = $("#material").val();
      var kd_gudang     = $("#kd_gudang").val();
      var range		    = $('#date_range').val();
      var tgl_awal 	= '0';
      var tgl_akhir 	= '0';
      if(range != ''){
      	var sPLT 		= range.split(' - ');
      	var tgl_awal 	= sPLT[0];
      	var tgl_akhir 	= sPLT[1];
      }
      DataTables(material, kd_gudang, tgl_awal, tgl_akhir);
    });
  });

  $(document).on('click', '#print', function(e){
    e.preventDefault();
    var material    = $("#material").val();
    var kd_gudang   = $("#kd_gudang").val();
    var range		    = $('#date_range').val();

    var tgl_awal 	= '0';
    var tgl_akhir 	= '0';
    if(range != ''){
      var sPLT 		= range.split(' - ');
      var tgl_awal 	= sPLT[0];
      var tgl_akhir 	= sPLT[1];
    }

    var Link	= base_url + active_controller +'excel_history/'+material+'/'+kd_gudang+'/'+tgl_awal+'/'+tgl_akhir;
    window.open(Link);

  });

    $(document).on('click', '.hist', function(e){
      e.preventDefault();
      var gudang    = $(this).data('gudang');
      var material  = $(this).data('material');

      $("#head_title").html("<b>HISTORY</b>");
      $.ajax({
        type:'POST',
        url: base_url + active_controller+'/modal_history',
        data: {
  				"gudang"      : gudang,
  				"material" 		: material
  			},
        success:function(data){
          $("#ModalView").modal();
          $("#view").html(data);

        },
        error: function() {
          swal({
            title				: "Error Message !",
            text				: 'Connection Timed Out ...',
            type				: "warning",
            timer				: 5000,
            showCancelButton	: false,
            showConfirmButton	: false,
            allowOutsideClick	: false
          });
        }
      });
    });

    function DataTables(material=null, kd_gudang=null, tgl_awal=null, tgl_akhir=null){
  		var dataTable = $('#example1').DataTable({
  			"processing" : true,
  			"serverSide": true,
  			"stateSave" : true,
  			"bAutoWidth": true,
  			"destroy": true,
  			"responsive": true,
  			"aaSorting": [[ 0, "asc" ]],
  			"columnDefs": [ {
  				"targets": 'no-sort',
  				"orderable": false,
  			}],
  			"sPaginationType": "simple_numbers",
  			"iDisplayLength": 10,
  			"aLengthMenu": [[10, 25, 50, 100, 250, 500], [10, 25, 50, 100, 250, 500]],
  			"ajax":{
  				url : siteurl+'warehouse_material/data_side_history',
  				type: "post",
          data: function(d){
            d.material = material,
            d.kd_gudang = kd_gudang,
            d.tgl_awal = tgl_awal,
            d.tgl_akhir = tgl_akhir
  				},
  				cache: false,
  				error: function(){
  					$(".my-grid-error").html("");
  					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
  					$("#my-grid_processing").css("display","none");
  				}
  			}
  		});
  	}
</script>
