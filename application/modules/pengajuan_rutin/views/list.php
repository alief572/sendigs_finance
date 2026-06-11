<?php
$ENABLE_ADD     = has_permission('Pengajuan_Pembayaran_Rutin.Add');
$ENABLE_MANAGE  = has_permission('Pengajuan_Pembayaran_Rutin.Manage');
$ENABLE_VIEW    = has_permission('Pengajuan_Pembayaran_Rutin.View');
$ENABLE_DELETE  = has_permission('Pengajuan_Pembayaran_Rutin.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<div class="dropdown">
				<button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
					<i class="fa fa-plus">&nbsp;</i> New
				</button>
				<ul class="dropdown-menu dept-dropdown-menu" aria-labelledby="dropdownMenu1">
					<li class="dept-search-wrap">
						<span class="dept-search-icon"><i class="fa fa-search"></i></span>
						<input type="text" id="dept_search" class="dept-search-input" placeholder="Cari department..." autocomplete="off">
					</li>
					<li class="dept-header">
						<i class="fa fa-university"></i>&nbsp; DEPARTEMEN
					</li>
					<div id="dept_list">
						<?php foreach ($datdept as $key => $val) : ?>
							<li class="dept-item"><a href="javascript:void(0)" onclick="new_data('<?= $key ?>')"><i class="fa fa-university"></i>&nbsp; <?= $val ?></a></li>
						<?php endforeach; ?>
						<li class="dept-no-result" style="display:none;">
							<span><i class="fa fa-info-circle"></i>&nbsp; Tidak ada hasil</span>
						</li>
					</div>
				</ul>
			</div>
		<?php endif; ?>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive col-md-12">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>Departement</th>
						<th>Nomor</th>
						<th>Nominal</th>
						<th>Tanggal</th>
						<th>Status</th>
						<th>Keterangan Reject</th>
						<th width="150">
							Action
						</th>
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
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<!-- page script -->
<script type="text/javascript">
	var url_add = "";
	var url_add_def = siteurl + 'pengajuan_rutin/create/';
	var url_edit = siteurl + 'pengajuan_rutin/edit/';
	var url_delete = siteurl + 'pengajuan_rutin/hapus_data/';
	var url_view = siteurl + 'pengajuan_rutin/view/';

	$(document).ready(function() {
		datatables();
	})

	function new_data(key) {
		url_add = url_add_def + key;
		data_add();
	}
	// $("#mytabledata2").DataTable({
	// 	dom: "<'row'<'col-sm-2'B><'col-sm-4'l><'col-sm-6'f>>rtip",
	// 	buttons: [
	// 		'excel'
	// 	]
	// });

	function datatables() {
		var datatables = $('#mytabledata').dataTable({
			serverSide: true,
			processing: true,
			paging: true,
			destroy: true,
			stateSave: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_pengajuan_periodik',
				cache: false,
				dataType: 'json'
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'department'
				},
				{
					data: 'nomor'
				},
				{
					data: 'nominal'
				},
				{
					data: 'tanggal'
				},
				{
					data: 'status'
				},
				{
					data: 'keterangan_reject'
				},
				{
					data: 'action'
				}
			]
		});
	}
</script>
<style>
	.dept-dropdown-menu {
		padding: 0;
		min-width: 360px;
		border-radius: 4px;
		box-shadow: 0 4px 16px rgba(0, 0, 0, 0.18);
		overflow: hidden;
	}
	.dept-search-wrap {
		display: flex;
		align-items: center;
		padding: 8px 10px;
		background: #f8f9fa;
		border-bottom: 1px solid #e0e0e0;
		position: sticky;
		top: 0;
		z-index: 10;
	}
	.dept-search-icon {
		color: #aaa;
		margin-right: 8px;
		font-size: 13px;
	}
	.dept-search-input {
		border: 1px solid #ddd;
		border-radius: 3px;
		padding: 5px 10px;
		font-size: 13px;
		width: 100%;
		outline: none;
		background: #fff;
	}
	.dept-search-input:focus {
		border-color: #5cb85c;
		box-shadow: 0 0 0 2px rgba(92, 184, 92, 0.15);
	}
	.dept-header {
		padding: 7px 14px;
		font-size: 11px;
		font-weight: 700;
		color: #888;
		letter-spacing: 0.5px;
		text-transform: uppercase;
		background: #f0f0f0;
		border-bottom: 1px solid #e0e0e0;
		cursor: default;
	}
	#dept_list {
		max-height: 280px;
		overflow-y: auto;
	}
	#dept_list::-webkit-scrollbar { width: 5px; }
	#dept_list::-webkit-scrollbar-track { background: #f1f1f1; }
	#dept_list::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
	.dept-item > a {
		display: block;
		padding: 8px 16px;
		font-size: 13px;
		color: #333;
		white-space: nowrap;
		overflow: hidden;
		text-overflow: ellipsis;
		transition: background 0.15s;
	}
	.dept-item > a:hover {
		background: #eaf6ea;
		color: #3c763d;
		text-decoration: none;
	}
	.dept-item > a i { color: #5cb85c; margin-right: 4px; }
	.dept-no-result span {
		display: block;
		padding: 10px 16px;
		color: #999;
		font-size: 13px;
	}
</style>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>
<script>
	$(document).on('keyup', '#dept_search', function() {
		var keyword = $(this).val().toLowerCase();
		var found = 0;
		$('#dept_list .dept-item').each(function() {
			var text = $(this).text().toLowerCase();
			if (text.indexOf(keyword) > -1) { $(this).show(); found++; }
			else { $(this).hide(); }
		});
		$('.dept-no-result').toggle(found === 0);
	});
	$(document).on('click', '#dept_search', function(e) { e.stopPropagation(); });
	$(document).on('hidden.bs.dropdown', '.dropdown', function() {
		$('#dept_search').val('');
		$('#dept_list .dept-item').show();
		$('.dept-no-result').hide();
	});
	$(document).on('shown.bs.dropdown', '.dropdown', function() { $('#dept_search').focus(); });
</script>