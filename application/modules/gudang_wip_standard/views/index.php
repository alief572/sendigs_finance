
<style type="text/css">
thead input {
	width: 100%;
}
.datepicker{
		cursor:pointer;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
	<div class="box-header">
      	<div class="form-group row">
			<div class="col-md-8"></div>
			<div class="col-md-2">
				<select name='sales_order' id='sales_order' class='form-control input-sm chosen-select'>
					<option value='0'>All Sales Order</option>
					<?php
					foreach ($listSO as $key => $value) {
						echo "<option value='".$value['so_number']."'>".$value['so_number']."</option>";
					}
					?>
				</select>
			</div>
			<div class='col-sm-2'>
				<input type="text" name='date_filter' id='date_filter' class='form-control datepicker text-center' data-role="datepicker2" readonly placeholder='Change Date'>
			</div>
      	</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped" width='100%'>
  		<thead>
    		<tr>
    			<th class='text-center'>#</th>
    			<th class='text-center'>SO</th>
				<th class='text-center'>No SPK</th>
    			<th class='text-center'>Category</th>
				<th class='text-center'>Product</th>
				<th class='text-center'>Variant</th>
				<th class='text-center'>Qty WIP</th>
				<!-- <th class='text-center'>Qty Order Total</th> -->
				<!-- <th class='text-center'>Berat</th> -->
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
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

	$(document).ready(function(){
		var sales_order     = $("#sales_order").val();
		var date_filter     = $("#date_filter").val();
		DataTables(sales_order,date_filter);

		$(document).on('change','#sales_order, #date_filter', function(){
			var sales_order     = $("#sales_order").val();
			var date_filter     = $("#date_filter").val();
			DataTables(sales_order,date_filter);
		});

		$('input[type="text"][data-role="datepicker2"]').datepicker({
			dateFormat: 'yy-mm-dd',
			changeMonth:true,
			changeYear:true,
			maxDate:'-1d',
			minDate:'2023-12-21',
			showButtonPanel: true,
			closeText: 'Clear',
			onClose: function (dateText, inst) {
			if ($(window.event.srcElement).hasClass('ui-datepicker-close'))
			{
				document.getElementById(this.id).value = '';
				var sales_order     = $("#sales_order").val();
				var date_filter     = $("#date_filter").val();
				DataTables(sales_order,date_filter);
			}
			}
		});
	});
	
    function DataTables(sales_order = null, date_filter=null){
  		var dataTable = $('#example1').DataTable({
  			"processing" : true,
  			"serverSide": true,
  			"stateSave" : true,
  			"autoWidth": false,
  			"destroy": true,
			"searching": true,
  			"responsive": true,
  			"aaSorting": [[ 1, "desc" ]],
  			"columnDefs": [ {
  				"targets": 'no-sort',
  				"orderable": false,
  			}],
  			"sPaginationType": "simple_numbers",
  			"iDisplayLength": 10,
  			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
  			"ajax":{
  				url : siteurl+active_controller+'data_side_gudang_wip',
  				type: "post",
          	data: function(d){
           	 	d.sales_order = sales_order,
           	 	d.date_filter = date_filter
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
