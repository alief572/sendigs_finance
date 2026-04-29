<?php
$ENABLE_ADD     = has_permission('Pembayaran_Periodik.Add');
$ENABLE_MANAGE  = has_permission('Pembayaran_Periodik.Manage');
$ENABLE_VIEW    = has_permission('Pembayaran_Periodik.View');
$ENABLE_DELETE  = has_permission('Pembayaran_Periodik.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<div class="col-md-3">
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
							<?php
							foreach ($datdept as $key => $val) {
								echo '<li class="dept-item"><a href="javascript:void(0)" onclick="new_data(\'' . $key . '\')"><i class="fa fa-university"></i>&nbsp; ' . $val . '</a></li>';
							}
							?>
							<li class="dept-no-result" style="display:none;">
								<span><i class="fa fa-info-circle"></i>&nbsp; Tidak ada hasil</span>
							</li>
						</div>
					</ul>
				</div>
			</div>
		<?php endif; ?>

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

			#dept_list::-webkit-scrollbar {
				width: 5px;
			}

			#dept_list::-webkit-scrollbar-track {
				background: #f1f1f1;
			}

			#dept_list::-webkit-scrollbar-thumb {
				background: #c1c1c1;
				border-radius: 3px;
			}

			.dept-item>a {
				display: block;
				padding: 8px 16px;
				font-size: 13px;
				color: #333;
				white-space: nowrap;
				overflow: hidden;
				text-overflow: ellipsis;
				transition: background 0.15s;
			}

			.dept-item>a:hover {
				background: #eaf6ea;
				color: #3c763d;
				text-decoration: none;
			}

			.dept-item>a i {
				color: #5cb85c;
				margin-right: 4px;
			}

			.dept-no-result span {
				display: block;
				padding: 10px 16px;
				color: #999;
				font-size: 13px;
			}
		</style>
		<div class="col-md-2">
			<?php if ($ENABLE_MANAGE) : ?>
				<a class="btn btn-info" href="javascript:void(0)" title="Proses" onclick="data_proses()">Proses</a>
			<?php endif; ?>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="table-responsive">
			<table id="mytabledata" class="table table-bordered table-striped">
				<thead>
					<tr>
						<th width="5">#</th>
						<th>Penanggung Jawab</th>
						<th width="100">
							Action
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (!empty($results)) {
						$numb = 0;
						foreach ($results as $record) {
							$numb++; ?>
							<tr>
								<td><?= $numb; ?></td>
								<td><?= strtoupper($datdept[$record->departement]) ?></td>
								<td>
									<?php if ($ENABLE_VIEW) : ?>
										<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="new_data('<?= $record->departement ?>')"><i class="fa fa-eye"></i></a>
									<?php endif;
									if ($ENABLE_MANAGE) : ?>
										<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit('<?= $record->departement ?>')"><i class="fa fa-edit"></i></a>
									<?php endif; ?>
								</td>
							</tr>
					<?php
						}
					}  ?>
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
<!-- page script -->
<script type="text/javascript">
	var url_add = "";
	var url_add_def = siteurl + 'budget_periodik/create/';
	var url_edit = siteurl + 'budget_periodik/edit/';
	var url_delete = siteurl + 'budget_periodik/hapus_data/';
	var url_view = siteurl + 'budget_periodik/view/';

	function new_data(key) {
		url_add = url_add_def + key;
		data_add();
	}

	function data_proses() {
		swal({
				title: "Anda Yakin?",
				text: "Data Akan Proses!",
				type: "info",
				showCancelButton: true,
				confirmButtonText: "Ya!",
				cancelButtonText: "Tidak!",
				closeOnConfirm: false,
				closeOnCancel: true
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						dataType: "json",
						url: siteurl + 'budget_periodik/proses_budget_periodik',
						type: 'POST',
						success: function(msg) {
							if (msg['save'] == '1') {
								swal({
									title: "Sukses!",
									text: "Data Berhasil Di Proses",
									type: "success",
									timer: 1500,
									showConfirmButton: false
								});
								window.location.reload();
							} else {
								swal({
									title: "Gagal!",
									text: "Data Gagal Di Proses",
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
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>
<script>
	// Search filter untuk dropdown department
	$(document).on('keyup', '#dept_search', function() {
		var keyword = $(this).val().toLowerCase();
		var found = 0;
		$('#dept_list .dept-item').each(function() {
			var text = $(this).text().toLowerCase();
			if (text.indexOf(keyword) > -1) {
				$(this).show();
				found++;
			} else {
				$(this).hide();
			}
		});
		$('.dept-no-result').toggle(found === 0);
	});

	// Jangan tutup dropdown saat klik search box
	$(document).on('click', '#dept_search', function(e) {
		e.stopPropagation();
	});

	// Reset search saat dropdown ditutup
	$(document).on('hidden.bs.dropdown', '.dropdown', function() {
		$('#dept_search').val('');
		$('#dept_list .dept-item').show();
		$('.dept-no-result').hide();
	});

	// Fokus ke search saat dropdown dibuka
	$(document).on('shown.bs.dropdown', '.dropdown', function() {
		$('#dept_search').focus();
	});
</script>