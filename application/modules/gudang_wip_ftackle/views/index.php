
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
				<!-- <div class="input-group">
					<div class="input-group-prepend">
						<span class="input-group-text">
							<i class="far fa-calendar-alt"></i>
						</span>
					</div>
					<input type="text" class="form-control float-right" id="range_picker" placeholder='Select range date' readonly value=''>
				</div> -->
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
				<!-- <th class='text-center no-sort'>IN</th>
				<th class='text-center no-sort'>OUT</th> -->
    		</tr>
  		</thead>
  		<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- modal -->
<div class="modal fade" id="ModalView2" style='overflow-y: auto;'>
	<div class="modal-dialog"  style='width:75%; '>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="head_title2"></h4>
				</div>
				<div class="modal-body" id="view2">
				</div>
				<div class="modal-footer">
				<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<!-- modal --> 
<style>
	.detInOut, #range_picker{
		cursor: pointer;
	}
</style>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

	$(document).ready(function(){
		// $('#range_picker').daterangepicker({
        //     locale: {
        //         format: 'DD-MM-YYYY'
        //     }
        // });

		// var sales_order     = $("#sales_order").val();
		// var range     = $("#range_picker").val();
		// var tgl_awal 	= '0';
		// var tgl_akhir 	= '0';
		// if(range != ''){
		// 	var sPLT 		= range.split(' - ');
		// 	var tgl_awal 	= sPLT[0];
		// 	var tgl_akhir 	= sPLT[1];
		// }
		// DataTables2(sales_order,tgl_awal,tgl_akhir);

		// $(document).on('change','#sales_order, #range_picker', function(){
		// 	var sales_order     = $("#sales_order").val();
		// 	var range     = $("#range_picker").val();
		// 	var tgl_awal 	= '0';
		// 	var tgl_akhir 	= '0';
		// 	if(range != ''){
		// 		var sPLT 		= range.split(' - ');
		// 		var tgl_awal 	= sPLT[0];
		// 		var tgl_akhir 	= sPLT[1];
		// 	}
		// 	DataTables2(sales_order,tgl_awal,tgl_akhir);
		// });

		// $(document).on('click', '.detInOut', function(){
		// 	var type 		= $(this).data('tipe');
		// 	var code_lv4 	= $(this).data('code_lv4');
        //     let sales_order = $('#sales_order').val();
        //     var tgl_awal 	= $(this).data('tanggal_awal');
        //     var tgl_akhir 	= $(this).data('tanggal_akhir');
		// 	$("#head_title2").html("<b>DETAIL IN OUT WIP</b>");
		// 	// loading_spinner();
		// 	$.ajax({
		// 		url			: base_url + active_controller+'/show_history_in_out_wip_detail',
		// 		type		: "POST",
		// 		data		: {
		// 			'tanda' 		: type,
		// 			'sales_order' 	: sales_order,
		// 			'code_lv4' 		: code_lv4,	
		// 			'tgl_awal' 		: tgl_awal,	
		// 			'tgl_akhir' 	: tgl_akhir
		// 		},
		// 		cache		: false,
		// 		dataType	: 'json',
		// 		success:function(data){
		// 			$("#ModalView2").modal();
		// 			$("#view2").html(data.data_html);
		// 		},
		// 		error: function() {
		// 			swal({
		// 			title	: "Error Message !",
		// 			text	: 'Connection Timed Out ...',
		// 			type	: "warning",
		// 			timer	: 5000,
		// 			});
		// 		}
		// 	})
		// });

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

	function DataTables2(sales_order = null, tgl_awal = null, tgl_akhir = null){
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
  				url : siteurl+active_controller+'data_side_gudang_wip_inout',
  				type: "post",
          	data: function(d){
           	 	d.sales_order = sales_order,
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
