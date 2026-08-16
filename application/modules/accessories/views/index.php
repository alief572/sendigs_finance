<?php
    $ENABLE_ADD     = has_permission('Master_Indirect.Add');
    $ENABLE_MANAGE  = has_permission('Master_Indirect.Manage');
    $ENABLE_VIEW    = has_permission('Master_Indirect.View');
    $ENABLE_DELETE  = has_permission('Master_Indirect.Delete');
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<style type="text/css">
	/* Modern Page & Box Card Styling */
	.accessories-card {
		background: #ffffff;
		border-radius: 8px;
		border: 1px solid #e5e9f2;
		box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
		margin-bottom: 25px;
		overflow: hidden;
	}
	.accessories-card .card-header {
		padding: 16px 20px;
		background: #fafbfd;
		border-bottom: 1px solid #edf2f9;
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
	}
	.accessories-card .card-body {
		padding: 20px;
	}
	
	/* Filter Toolbar */
	.filter-wrapper {
		display: flex;
		align-items: center;
		gap: 10px;
		min-width: 280px;
	}
	.filter-label {
		font-size: 13px;
		font-weight: 600;
		color: #5e6e82;
		margin-bottom: 0;
		white-space: nowrap;
		display: inline-flex;
		align-items: center;
		gap: 6px;
	}
	.filter-wrapper .select2-container {
		flex-grow: 1;
	}
	.filter-wrapper .select2-container .select2-choice {
		height: 35px;
		line-height: 33px;
		border-radius: 6px;
		border: 1px solid #d8e2ef;
		background: #ffffff;
	}

	/* Action Buttons */
	.action-toolbar {
		display: flex;
		align-items: center;
		gap: 8px;
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
		box-shadow: 0 1px 3px rgba(0,0,0,0.06);
	}
	.btn-custom:hover {
		transform: translateY(-1px);
		box-shadow: 0 3px 8px rgba(0,0,0,0.12);
	}

	/* Datatable Enhancements */
	#example1 {
		width: 100% !important;
		margin-top: 10px;
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
		vertical-align: middle;
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

	/* Modern Badges & Action Buttons inside Table */
	.status-badge {
		display: inline-block;
		padding: 4px 10px;
		font-size: 11px;
		font-weight: 600;
		border-radius: 50px;
		text-align: center;
		letter-spacing: 0.3px;
	}
	.status-badge.active {
		background-color: #dcfce7;
		color: #15803d;
		border: 1px solid #bbf7d0;
	}
	.status-badge.inactive {
		background-color: #fee2e2;
		color: #b91c1c;
		border: 1px solid #fecaca;
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
		transition: transform 0.15s ease;
	}
	.table-action-btns .btn:hover {
		transform: scale(1.08);
	}

	/* Modal Styling */
	.modal-content {
		border-radius: 10px;
		border: none;
		box-shadow: 0 10px 30px rgba(0,0,0,0.15);
		overflow: hidden;
	}
	.modal-header {
		background: #f8fafc;
		border-bottom: 1px solid #e2e8f0;
		padding: 16px 22px;
	}
	.modal-title {
		font-size: 16px;
		font-weight: 600;
		color: #1e293b;
		display: flex;
		align-items: center;
		gap: 8px;
	}
	.modal-body {
		padding: 24px;
	}
</style>

<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>

<div class="accessories-card">
	<div class="card-header">
		<!-- Filter Section -->
		<div class="filter-wrapper">
			<label class="filter-label" for="id_category">
				<i class="fa fa-filter text-primary"></i> Filter Kategori:
			</label>
			<select name="id_category" id="id_category" class='form-control select2'>
				<option value="0">Semua Kategori</option>
				<?php
				foreach ($category as $key => $value) {
					echo "<option value='".$value['id']."'>".strtoupper($value['nm_category'])."</option>";
				}
				?>
			</select>
		</div>

		<!-- Action Buttons -->
		<div class="action-toolbar">
			<?php if($ENABLE_ADD) : ?>
				<a class="btn btn-success btn-custom" href="<?= base_url('accessories/add') ?>" title="Tambah Barang Stok">
					<i class="fa fa-plus-circle"></i> Tambah Baru
				</a>
			<?php endif; ?>
			<a class="btn btn-info btn-custom" href="<?=base_url('accessories/download_excel');?>" target='_blank' title="Download Format Excel">
				<i class="fa fa-file-excel-o"></i> Unduh Excel
			</a>
		</div>
	</div>

	<!-- Card Body / Datatable -->
	<div class="card-body">
		<div class="table-responsive">
			<table id="example1" class="table table-hover">
				<thead>
					<tr>
						<th class="text-center" width="40">#</th>
						<th width="110">Item Code</th>
						<th>Stock Name</th>
						<th>Category</th>
						<th>Trade Name</th>
						<th>Brand</th>
						<th>Spec</th>
						<th class="text-center" width="85">Status</th>
						<th width="90">Last By</th>
						<th class="text-center" width="120">Last Date</th>
						<th class="text-center" width="100">Action</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style="max-width: 800px; width: 92%;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="head_title">
          <i class="fa fa-boxes text-primary"></i> Detail Accessories
        </h4>
      </div>
      <div class="modal-body" id="ModalView">
		<div class="text-center text-muted" style="padding: 30px 0;">
			<i class="fa fa-spinner fa-spin fa-2x"></i>
			<p style="margin-top: 10px;">Memuat data detail...</p>
		</div>
      </div>
    </div>
  </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- Page Script -->
<script type="text/javascript">
	$(document).on('click', '.detail', function(){
		var id = $(this).data('id');
		$("#head_title").html("<i class='fa fa-info-circle text-primary'></i> <b>Detail Accessories</b>");
		$("#ModalView").html('<div class="text-center text-muted" style="padding: 30px 0;"><i class="fa fa-spinner fa-spin fa-2x"></i><p style="margin-top: 10px;">Memuat data detail...</p></div>');
		$("#dialog-popup").modal('show');
		
		$.ajax({
			type:'POST',
			url:siteurl+ active_controller +'/detail/'+id,
			data:{'id':id},
			success:function(data){
				$("#ModalView").html(data);
			},
			error:function(){
				$("#ModalView").html('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Gagal memuat data detail.</div>');
			}
		});
	});

	// DELETE DATA
	$(document).on('click', '.delete', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		swal({
		  title: "Konfirmasi Hapus",
		  text: "Apakah Anda yakin ingin menghapus data barang stok ini?",
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
			  url:siteurl+ active_controller+'/hapus',
			  dataType : "json",
			  data:{'id':id},
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses!",
						  text : result.pesan,
						  type : "success"
						},
						function (){
							window.location.reload(true);
						});
				  } else {
					swal({
					  title : "Gagal!",
					  text  : result.pesan,
					  type  : "error"
					});
				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Terjadi kesalahan proses pada server.",
					  type  : "error"
					});
			  }
		  });
		});
	});

  	$(function() {
		var id_category = $('#id_category').val();
		DataTables(id_category);
		$('.select2').select2({width: '100%'});
  	});

	$(document).on('change','#id_category',function(){
		var id_category = $('#id_category').val();
		DataTables(id_category);
	});

    function DataTables(id_category=null){
  		var dataTable = $('#example1').DataTable({
  			"processing" : true,
  			"serverSide": true,
  			"stateSave" : true,
  			"bAutoWidth": false,
  			"destroy": true,
  			"responsive": true,
  			"aaSorting": [[ 1, "asc" ]],
  			"columnDefs": [ 
				{ "targets": [0, 7, 10], "className": "text-center" },
				{ "targets": [10], "orderable": false }
			],
  			"sPaginationType": "simple_numbers",
  			"iDisplayLength": 10,
  			"aLengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
  			"ajax":{
  				url : siteurl+active_controller+'/data_side_accessories',
  				type: "post",
  				data: function(d){
  					d.id_category = id_category;
  				},
  				cache: false,
  				error: function(){
  					$(".my-grid-error").html("");
  					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="11">Data tidak ditemukan di server</th></tr></tbody>');
  					$("#my-grid_processing").css("display","none");
  				}
  			}
  		});
  	}
</script>
