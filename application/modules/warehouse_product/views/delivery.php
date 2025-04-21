<?php
    $ENABLE_ADD     = has_permission('Delivery.Add');
    $ENABLE_MANAGE  = has_permission('Delivery.Manage');
    $ENABLE_VIEW    = has_permission('Delivery.View');
    $ENABLE_DELETE  = has_permission('Delivery.Delete');
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
			<?php if($ENABLE_ADD) : ?>
					<a class="btn btn-success btn-sm" href="<?= base_url('warehouse_product/add_delivery') ?>" title="Add"> <i class="fa fa-plus">&nbsp;</i>Add</a>
			<?php endif; ?>

      <a class="btn btn-primary btn-sm" href="<?= base_url('warehouse_product/excel_report_delivery_all') ?>" target='_blank' title="Download Excel">
        <i class="fa fa-print"></i> Print Excel
      </a>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="5">#</th>
			<th>No Delivery</th>
      <th>SO / SO Manual</th>
			<th>Customer Name</th>
      <th>Delivery Date</th>
      <th>Shipping By</th>
      <th class="no-sort">Delivery Qty</th>
      <th class="no-sort">Last By</th>
			<th class="no-sort">Last Date</th>
			<th class="no-sort" width="13%">Action</th>
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

<!-- page script -->
<script type="text/javascript">

	$(document).on('click', '.detail', function(){
		var no_delivery = $(this).data('no_delivery');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Cycletime</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'warehouse_product/detail_delivery/'+no_delivery,
			data:{'no_delivery':no_delivery},
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});
		$(document).on('click', '.add', function(){
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Inventory</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'inventory_1/addInventory',
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});


	// DELETE DATA
	$(document).on('click', '.delete', function(e){
		e.preventDefault()
		var id = $(this).data('no_bom');
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data BOM akan di hapus !",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Ya, Hapus!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+'engine/hapus',
			  dataType : "json",
			  data:{'id':id},
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses",
						  text : "Data berhasil dihapus.",
						  type : "success"
						},
						function (){
							window.location.reload(true);
						})
				  } else {
					swal({
					  title : "Error",
					  text  : "Data error. Gagal hapus data",
					  type  : "error"
					})

				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Data error. Gagal request Ajax",
					  type  : "error"
					})
			  }
		  })
		});

	})

  	$(function() {
      DataTables();
  	});


    function DataTables(){
  		var dataTable = $('#example1').DataTable({
        "serverSide": true,
  			"stateSave" : true,
  			"bAutoWidth": true,
  			"destroy": true,
  			"processing": true,
  			"responsive": true,
  			"fixedHeader": {
  				"header": true,
  				"footer": true
  			},
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
  			"aaSorting": [[ 1, "desc" ]],
  			"columnDefs": [ {
  				"targets": 'no-sort',
  				"orderable": false,
  			}],
  			"sPaginationType": "simple_numbers",
  			"iDisplayLength": 10,
  			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
  			"ajax":{
  				url : siteurl+'warehouse_product/data_side_delivery',
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
