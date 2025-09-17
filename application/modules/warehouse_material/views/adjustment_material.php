<?php
    $ENABLE_ADD     = has_permission('Adjustment.Add');
    $ENABLE_MANAGE  = has_permission('Adjustment.Manage');
    $ENABLE_VIEW    = has_permission('Adjustment.View');
    $ENABLE_DELETE  = has_permission('Adjustment.Delete');
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
					<a class="btn btn-success btn-md" style='float:right;' href="<?= base_url('warehouse_material/add_adjustment') ?>" title="Add"> <i class="fa fa-plus">&nbsp;</i>Add Adjustment</a>
			<?php endif; ?>
    <br>
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
      </div>
      <div class="form-group row">
        <div class="col-md-1">
          <b>Material</b>
        </div>
        <div class="col-md-3">
          <select name='material' id='material' class='form-control input-sm chosen-select'>
  					<option value='0'>All Material</option>
  					<?php
  					foreach(get_material() AS $val => $valx){
  						echo "<option value='".$valx['code_material']."'>".strtoupper($valx['nm_material'])."</option>";
  					}
  					?>
  				</select>
        </div>
      </div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped" width='100%'>
  		<thead>
    		<tr>
    			<th>#</th>
    			<th>Date</th>
    			<th>Warehouse</th>
          <th>Material</th>
          <th>Adjustment</th>
          <th>Qty Pack</th>
          <th>Qty Kg</th>
          <th>Awal Pack</th>
          <th>Awal Kg</th>
          <th>Akhir Pack</th>
          <th>Akhir Kg</th>
          <th class='no-sort'>Adjust By</th>
          <th>Surat Jalan</th>
          <th>Reason</th>
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

  $(document).ready(function(){
    var kd_gudang     = $("#kd_gudang").val();
    var material  = $("#material").val();
    DataTables(material, kd_gudang);

    $(document).on('change','#material, #kd_gudang', function(){
      var material  = $("#material").val();
      var kd_gudang     = $("#kd_gudang").val();
      DataTables(material, kd_gudang);
    });

  });


    function DataTables(material = null, kd_gudang = null){
  		var dataTable = $('#example1').DataTable({
  			// "scrollX": true,
        "scrollCollapse" : true,
  			"processing" : true,
  			"serverSide": true,
  			"stateSave" : true,
  			"bAutoWidth": true,
  			"destroy": true,
        "searching": false,
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
  			"aaSorting": [[ 1, "desc" ]],
  			"columnDefs": [ {
  				"targets": 'no-sort',
  				"orderable": false,
  			}],
  			"sPaginationType": "simple_numbers",
  			"iDisplayLength": 10,
  			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
  			"ajax":{
  				url : siteurl+'warehouse_material/data_side_adjustment',
  				type: "post",
          data: function(d){
            d.material = material,
            d.kd_gudang = kd_gudang
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
