<?php
    $ENABLE_ADD     = has_permission('Master_Unit.Add');
    $ENABLE_MANAGE  = has_permission('Master_Unit.Manage');
    $ENABLE_VIEW    = has_permission('Master_Unit.View');
    $ENABLE_DELETE  = has_permission('Master_Unit.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<style type="text/css">
	.unit-card {
		background: #ffffff;
		border-radius: 8px;
		border: 1px solid #e5e9f2;
		box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
		margin-bottom: 25px;
		overflow: hidden;
	}
	.unit-card .card-header {
		padding: 16px 20px;
		background: #fafbfd;
		border-bottom: 1px solid #edf2f9;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}
	.unit-card .card-body {
		padding: 20px;
	}
	.btn-custom {
		border-radius: 6px;
		padding: 7px 14px;
		font-size: 13px;
		font-weight: 500;
		display: inline-flex;
		align-items: center;
		gap: 6px;
		transition: all 0.2s ease;
	}
	.btn-custom:hover {
		transform: translateY(-1px);
	}
	#example1 {
		width: 100% !important;
		border-collapse: separate;
		border-spacing: 0;
	}
	#example1 thead th {
		background-color: #f8fafc;
		color: #475569;
		font-size: 12px;
		font-weight: 600;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		border-bottom: 2px solid #e2e8f0;
		padding: 12px 10px;
	}
	#example1 tbody td {
		font-size: 13px;
		color: #334155;
		vertical-align: middle;
		padding: 10px;
		border-top: 1px solid #f1f5f9;
	}
	#example1 tbody tr:hover {
		background-color: #f8fafc;
	}
	.table-action-btns {
		display: inline-flex;
		align-items: center;
		gap: 4px;
	}
	.table-action-btns .btn {
		width: 30px;
		height: 30px;
		padding: 0;
		line-height: 28px;
		text-align: center;
		border-radius: 6px;
		font-size: 12px;
	}
</style>

<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>

<div class="unit-card">
	<div class="card-header">
		<div>
			<h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #1e293b;">
				<i class="fa fa-list-alt text-primary"></i> Master Unit / Satuan
			</h4>
		</div>
		<div>
			<?php if($ENABLE_ADD) : ?>
				<button type='button' class="btn btn-success btn-custom" id="add" title="Tambah Unit">
					<i class="fa fa-plus-circle"></i> Tambah Unit
				</button>
			<?php endif; ?>
		</div>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="example1" class="table table-hover">
				<thead>
					<tr>
						<th class="text-center" width="50">#</th>
						<th>Nama / Kode Unit</th>
						<th width="150">Last By</th>
						<th class="text-center" width="160">Last Date</th>
						<th class="text-center" width="120">Action</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>

<div class="modal fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="max-width: 550px; width: 90%;">
    <div class="modal-content" style="border-radius: 10px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
      <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 16px 20px;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="head_title" style="font-size: 15px; font-weight: 600; color: #1e293b;">
			<i class="fa fa-cubes text-primary"></i> Form Unit
		</h4>
      </div>
      <div class="modal-body" id="ModalView" style="padding: 20px;">
		...
      </div>
    </div>
  </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- Page Script -->
<script type="text/javascript">
	$(document).on('click', '#add', function(){
		$("#head_title").html("<i class='fa fa-plus-circle text-success'></i> <b>Tambah Unit Baru</b>");
		$("#dialog-popup").modal('show');
		$("#ModalView").html('<div class="text-center text-muted" style="padding: 25px 0;"><i class="fa fa-spinner fa-spin fa-2x"></i><p style="margin-top: 10px;">Memuat form...</p></div>');
		$.ajax({
			type:'POST',
			url:siteurl+'material/add_unit',
			success:function(data){
				$("#ModalView").html(data);
			}
		});
	});

	$(document).on('click', '.edit', function(){
		$("#head_title").html("<i class='fa fa-pencil text-primary'></i> <b>Edit Unit</b>");
		var id = $(this).data('id');
		$("#dialog-popup").modal('show');
		$("#ModalView").html('<div class="text-center text-muted" style="padding: 25px 0;"><i class="fa fa-spinner fa-spin fa-2x"></i><p style="margin-top: 10px;">Memuat form...</p></div>');
		$.ajax({
			type:'POST',
			url:siteurl+'material/add_unit/'+id,
			success:function(data){
				$("#ModalView").html(data);
			}
		});
	});

	// DELETE DATA
	$(document).on('click', '.delete', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		swal({
		  title: "Konfirmasi Hapus",
		  text: "Apakah Anda yakin ingin menghapus unit ini?",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-danger",
		  confirmButtonText: "Ya, Hapus!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+'material/hapus_unit',
			  dataType : "json",
			  data:{'id':id},
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses!",
						  text : "Data berhasil dihapus.",
						  type : "success"
						},
						function (){
							window.location.reload(true);
						});
				  } else {
					swal({
					  title : "Gagal!",
					  text  : "Gagal menghapus data unit.",
					  type  : "error"
					});
				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Gagal request ke server.",
					  type  : "error"
					});
			  }
		  });
		});
	});

	$(document).on('click', '#save_unit', function(e){
		e.preventDefault();
		var data = $('#data_form').serialize();

		swal({
		  title: "Konfirmasi Simpan",
		  text: "Apakah data unit sudah benar?",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-primary",
		  confirmButtonText: "Ya, Simpan!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+'material/add_unit',
			  dataType : "json",
			  data:data,
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses!",
						  text : "Data Unit berhasil disimpan.",
						  type : "success"
						},
						function (){
							window.location.reload(true);
						});
				  } else {
					swal({
					  title : "Gagal",
					  text  : "Gagal menyimpan data unit.",
					  type  : "error"
					});
				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Gagal request ke server.",
					  type  : "error"
					});
			  }
		  });
		});
	});

  	$(function() {
		DataTables();
  	});

    function DataTables(){
  		var dataTable = $('#example1').DataTable({
  			"processing" : true,
  			"serverSide": true,
  			"stateSave" : true,
  			"bAutoWidth": false,
  			"destroy": true,
  			"responsive": true,
  			"aaSorting": [[ 1, "asc" ]],
  			"columnDefs": [ 
				{ "targets": [0, 3, 4], "className": "text-center" },
				{ "targets": [4], "orderable": false }
			],
  			"sPaginationType": "simple_numbers",
  			"iDisplayLength": 10,
  			"aLengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
  			"ajax":{
  				url : siteurl+'material/data_side_unit',
  				type: "post",
  				data: function(d){},
  				cache: false,
  				error: function(){
  					$(".my-grid-error").html("");
  					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="5">Data tidak ditemukan di server</th></tr></tbody>');
  					$("#my-grid_processing").css("display","none");
  				}
  			}
  		});
  	}
</script>
