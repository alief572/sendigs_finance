<?php
$ENABLE_ADD     = has_permission('Pengajuan_Transportasi_Approval.Add');
$ENABLE_MANAGE  = has_permission('Pengajuan_Transportasi_Approval.Manage');
$ENABLE_VIEW    = has_permission('Pengajuan_Transportasi_Approval.View');
$ENABLE_DELETE  = has_permission('Pengajuan_Transportasi_Approval.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<div class="box">
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>No</th>
						<th>Tanggal</th>
						<th>Nama</th>
						<th>Status</th>
						<th width="120">Action</th>
					</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>
<div id="form-data"></div>
<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<!-- page script -->
<script type="text/javascript">
	var url_view = siteurl + 'expense/transport_req_view/';
	var url_approve = siteurl + 'expense/transport_req_approve/';

	datatables();

	function data_approve(id) {
		swal({
				title: "Anda Yakin?",
				text: "Data Akan Disetujui!",
				type: "info",
				showCancelButton: true,
				confirmButtonText: "Ya, setuju!",
				cancelButtonText: "Tidak!",
				closeOnConfirm: false,
				closeOnCancel: true
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						url: url_approve + id + '/1',
						dataType: "json",
						type: 'POST',
						success: function(msg) {
							if (msg['save'] == '1') {
								swal({
									title: "Sukses!",
									text: "Data Berhasil Di Setujui",
									type: "success",
									timer: 1500,
									showConfirmButton: false
								});
								window.location.reload();
							} else {
								swal({
									title: "Gagal!",
									text: "Data Gagal Di Setujui",
									type: "error",
									timer: 1500,
									showConfirmButton: false
								});
							};
							console.log(msg);
						},
						error: function(msg) {
							swal({
								title: "Gagal!",
								text: "Ajax Data Gagal Di Proses",
								type: "error",
								timer: 1500,
								showConfirmButton: false
							});
							console.log(msg);
						}
					});
				}
			});
	}

	function datatables() {
		var datatables = $('#mytabledata').dataTable({
			serverSide: true,
			processing: true,
			stateSave: true,
			paging: true,
			destroy: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_transport_req_fin_list',
				cache: false,
				dataType: 'json',
				data: function(d) {

				}
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'no_transport'
				},
				{
					data: 'tanggal'
				},
				{
					data: 'nama'
				},
				{
					data: 'status'
				},
				{
					data: 'action'
				}
			]
		})
	}
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>