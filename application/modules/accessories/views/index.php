<?php
    $ENABLE_ADD     = has_permission('Master_Indirect.Add');
    $ENABLE_MANAGE  = has_permission('Master_Indirect.Manage');
    $ENABLE_VIEW    = has_permission('Master_Indirect.View');
    $ENABLE_DELETE  = has_permission('Master_Indirect.Delete');
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
		<span class="pull-right">
			<?php if($ENABLE_ADD) : ?>
					<a class="btn btn-success btn-sm" href="<?= base_url('accessories/add') ?>" title="Add"> <i class="fa fa-plus">&nbsp;</i>Add</a>
					<?php endif; ?>
					<a class="btn btn-info btn-sm" href="<?=base_url('accessories/download_excel');?>" target='_blank' title="Download"><i class="fa fa-excel">&nbsp;</i>Excel</a>
		</span>
		<div class="form-group row">
			<div class="col-md-3">
				<select name="id_category" id="id_category" class='form-control select2'>
					<option value="0">ALL CATEGORY</option>
					<?php
					foreach ($category as $key => $value) {
						echo "<option value='".$value['id']."'>".strtoupper($value['nm_category'])."</option>";
					}
					?>
				</select>
			</div>
			<div class="col-md-9">

			</div>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th>#</th>
			<th>Item Code</th>
			<th>Stok Name</th>
			<th>Stok Category</th>
			<th>Trade Name</th>
			<th>Brand</th>
			<th>Spec</th>
			<th>Status</th>
			<th>Last By</th>
			<th>Last Date</th>
			<th>Action</th>
		</tr>
		</thead>

		<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style='width:70%; '>
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
		var id = $(this).data('id');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Accessories</b>");
		$.ajax({
			type:'POST',
			url:siteurl+ active_controller +'/detail/'+id,
			data:{'id':id},
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	// DELETE DATA
	$(document).on('click', '.delete', function(e){
		e.preventDefault()
		var id = $(this).data('id');
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data akan di hapus.",
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
			  url:siteurl+ active_controller+'/hapus',
			  dataType : "json",
			  data:{'id':id},
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses",
						  text : result.pesan,
						  type : "success"
						},
						function (){
							window.location.reload(true);
						})
				  } else {
					swal({
					  title : "Error",
					  text  : result.pesan,
					  type  : "error"
					})

				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Error Process !",
					  type  : "error"
					})
			  }
		  })
		});

	})

  	$(function() {
		var id_category = $('#id_category').val()
		DataTables(id_category);

		$('.select2').select2({width: '100%'});

  	});

	$(document).on('change','#id_category',function(){
		var id_category = $('#id_category').val()
		DataTables(id_category);
	});


    function DataTables(id_category=null){
  		var dataTable = $('#example1').DataTable({
  			"processing" : true,
  			"serverSide": true,
  			"stateSave" : true,
  			"bAutoWidth": true,
  			"destroy": true,
  			"responsive": true,
  			"aaSorting": [[ 1, "asc" ]],
  			"columnDefs": [ {
  				"targets": 'no-sort',
  				"orderable": false,
  			}],
  			"sPaginationType": "simple_numbers",
  			"iDisplayLength": 10,
  			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
  			"ajax":{
  				url : siteurl+active_controller+'/data_side_accessories',
  				type: "post",
  				data: function(d){
  					d.id_category = id_category
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
