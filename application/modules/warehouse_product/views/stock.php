<?php
    $ENABLE_ADD     = has_permission('Finish_Good_Product.Add');
    $ENABLE_MANAGE  = has_permission('Finish_Good_Product.Manage');
    $ENABLE_VIEW    = has_permission('Finish_Good_Product.View');
    $ENABLE_DELETE  = has_permission('Finish_Good_Product.Delete');
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
    <br>
    <div class="form-group row">
      <div class="col-md-1">
        <b>Product</b>
      </div>
      <div class="col-md-3">
        <select name='product' id='product' class='form-control input-sm chosen-select'>
					<option value='0'>All Product</option>
					<?php
					foreach(get_product() AS $val => $valx){
						echo "<option value='".$valx['id_category2']."'>".strtoupper($valx['nama'])."</option>";
					}
					?>
				</select>
      </div>
    </div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="3%">#</th>
			<th width="17%">Product Name</th>
      <th width="9%" align='center'>Qty Stock</th>
      <th width="9%" align='center'>Qty Daycode</th>
      <th align='center'>Daycode</th>
      <th width="7%" align='center'>Status</th>
		</tr>
		</thead>

		<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style='width:90%; '>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
  </div>
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
    var product     = $("#product").val();
    DataTables(product);

    $(document).on('change','#product', function(){
      var product     = $("#product").val();
      DataTables(product);
    });

  });

    function DataTables(product = null){
  		var dataTable = $('#example1').DataTable({
  			// "scrollX": true,
  			// "scrollY": "500",
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
  			"aLengthMenu": [[10, 20, 50, 100, 150, 250, 500], [10, 20, 50, 100, 150, 250, 500]],
  			"ajax":{
  				url : siteurl+'warehouse_product/data_side_stock',
  				type: "post",
  				data: function(d){
            d.product = product
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
