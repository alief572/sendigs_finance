<?php
    $ENABLE_ADD     = has_permission('Stock_Material.Add');
    $ENABLE_MANAGE  = has_permission('Stock_Material.Manage');
    $ENABLE_VIEW    = has_permission('Stock_Material.View');
    $ENABLE_DELETE  = has_permission('Stock_Material.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
	<div class="box-header">
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
      <th width='3%' class="text-center" rowspan='2'>#</th>
			<th width='10%' class="text-center" rowspan='2'>Id Material</th>
			<th class="text-center" rowspan='2'>Material</th>
      <th width='4%' class="text-center" rowspan='2'>Konversi</th>
      <th width='4%' class="text-center" rowspan='2'>Unit</th>
      <th width='4%' class="text-center" rowspan='2'>Kelompok</th>
      <th class="text-center" colspan='3'>Stock Pusat</th>
      <th class="text-center" colspan='3'>Stock Transisi</th>
      <th class="text-center" colspan='3'>Stock Produksi</th>
		</tr>
    <tr>
      <th width='6%' class="text-center no-sort">Unit</th>
      <th width='6%' class="text-center no-sort">Pack</th>
      <th width='4%' class="text-center no-sort">Hist</th>
      <th width='6%' class="text-center no-sort">Unit</th>
      <th width='6%' class="text-center no-sort">Pack</th>
      <th width='4%' class="text-center no-sort">Hist</th>
      <th width='6%' class="text-center no-sort">Unit</th>
      <th width='6%' class="text-center no-sort">Pack</th>
      <th width='4%' class="text-center no-sort">Hist</th>
		</tr>
		</thead>

		<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- modal -->
<div class="modal fade" id="ModalView" style='overflow-y: auto;'>
  <div class="modal-dialog"  style='width:95%; '>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="head_title"></h4>
        </div>
        <div class="modal-body" id="view">
        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- modal -->

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

  	$(function() {
      DataTables();
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

    function DataTables(){
  		var dataTable = $('#example1').DataTable({
  			// "scrollX": true,
  			"scrollY": "500",
  			"scrollCollapse" : true,
  			"processing" : true,
  			"serverSide": true,
  			"stateSave" : true,
  			"bAutoWidth": true,
  			"destroy": true,
  			"responsive": true,
  			"oLanguage": {
  				"sSearch": "<b>Live Search : </b>",
  				"sLengthMenu": "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
  				"sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
  				"sInfoFiltered": "(filtered from _MAX_ total entries)",
  				"sZeroRecords": "No matching records found",
  				"sEmptyTable": "No data available in table",
  				"sLoadingRecords": "Please wait - loading...",
  				"oPaginate": {
  					"sPrevious": "Prev",
  					"sNext": "Next"
  				}
  			},
  			"aaSorting": [[ 1, "asc" ]],
  			"columnDefs": [ {
  				"targets": 'no-sort',
  				"orderable": false,
  			}],
  			"sPaginationType": "simple_numbers",
  			"iDisplayLength": 10,
  			"aLengthMenu": [[10, 25, 50, 100, 250, 500], [10, 25, 50, 100, 250, 500]],
  			"ajax":{
  				url : siteurl+'warehouse_material/data_side_stock',
  				type: "post",
  				data: function(d){
  					// d.kode_partner = $('#kode_partner').val()
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
