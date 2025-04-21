<?php
    $ENABLE_ADD     = has_permission('Stock_Produksi.Add');
    $ENABLE_MANAGE  = has_permission('Stock_Produksi.Manage');
    $ENABLE_VIEW    = has_permission('Stock_Produksi.View');
    $ENABLE_DELETE  = has_permission('Stock_Produksi.Delete');
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
      <th class="text-center">#</th>
			<th class="text-center">Id Material</th>
			<th class="text-center">Material</th>
      <th class="text-center">Incoming</th>
      <th class="text-center">Outgoing</th>
      <th class="text-center">Stock</th>
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

<!-- page script -->
<script type="text/javascript">

  	$(function() {
      DataTables();
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
  			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
  			"ajax":{
  				url : siteurl+'warehouse_material/data_side_stock_pro',
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
