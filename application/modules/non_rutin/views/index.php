<?php
$ENABLE_ADD     = has_permission('PR_Departemen.Add');
$ENABLE_MANAGE  = has_permission('PR_Departemen.Manage');
$ENABLE_VIEW    = has_permission('PR_Departemen.View');
$ENABLE_DELETE  = has_permission('PR_Departemen.Delete');
?>
<style>
	:root {
		--state-neutral-bg: #eef1f5;
		--state-neutral-fg: #475569;
		--state-wait-bg: #fdf1d9;
		--state-wait-fg: #a1670d;
		--state-final-bg: #e2f6ea;
		--state-final-fg: #1f7a45;
		--state-reject-bg: #fbe4e4;
		--state-reject-fg: #b32b2b;

		--dot-done: #1f7a45;
		--dot-active: #a1670d;
		--dot-pending: #cbd2d9;
		--dot-reject: #b32b2b;
	}

	.section-title {
		font-size: 13px;
		font-weight: 700;
		color: #3c8dbc;
		text-transform: uppercase;
		letter-spacing: 0.5px;
		border-bottom: 2px solid #3c8dbc;
		padding-bottom: 6px;
		margin-bottom: 15px;
	}

	#my-grid thead tr th {
		background-color: #3c8dbc;
		color: #fff;
		font-size: 12px;
		border-color: #357ca5;
		vertical-align: middle;
	}

	#my-grid tbody td {
		vertical-align: top;
		font-size: 13px;
	}

	.breadcrumb {
		background: none;
		padding: 0;
		font-size: 12px;
		margin-bottom: 5px;
	}

	.no-pr {
		color: #b3261e;
		font-weight: 600;
	}

	.proj {
		max-width: 250px;
		word-break: break-word;
	}

	.dept {
		color: #555;
		font-size: 12px;
	}

	.badge-urgent {
		background-color: #fbe4e4 !important;
		color: #b3261e !important;
		font-weight: 600;
		padding: 3px 8px;
		border-radius: 12px;
	}

	.badge-normal {
		background-color: #e8f4fd !important;
		color: #1e6bb8 !important;
		font-weight: 600;
		padding: 3px 8px;
		border-radius: 12px;
	}

	.progress-cell {
		min-width: 160px;
	}

	.steps {
		display: flex;
		align-items: center;
		gap: 0;
		margin-bottom: 6px;
	}

	.step-dot {
		width: 10px;
		height: 10px;
		border-radius: 50%;
		background: var(--dot-pending);
		flex: none;
	}

	.step-dot.done {
		background: var(--dot-done);
	}

	.step-dot.active {
		background: var(--dot-active);
		box-shadow: 0 0 0 3px rgba(161, 103, 13, 0.22);
	}

	.step-dot.reject {
		background: var(--dot-reject);
	}

	.step-line {
		flex: 1;
		height: 2px;
		background: var(--dot-pending);
		min-width: 8px;
	}

	.step-line.done {
		background: var(--dot-done);
	}

	.stage-label {
		font-size: 12px;
		font-weight: 700;
		margin-bottom: 2px;
		color: #333;
	}

	.status-badge {
		display: inline-block;
		padding: 2px 8px;
		border-radius: 4px;
		font-size: 11px;
		font-weight: 600;
		margin-bottom: 4px;
	}

	.st-neutral {
		background: var(--state-neutral-bg);
		color: var(--state-neutral-fg);
	}

	.st-wait {
		background: var(--state-wait-bg);
		color: var(--state-wait-fg);
	}

	.st-final {
		background: var(--state-final-bg);
		color: var(--state-final-fg);
	}

	.st-reject {
		background: var(--state-reject-bg);
		color: var(--state-reject-fg);
	}

	.doc-meta {
		font-size: 11px;
		color: #777;
		line-height: 1.35;
	}

	.opts {
		display: flex;
		gap: 4px;
		flex-wrap: wrap;
	}

	.opt-btn {
		width: 28px;
		height: 28px;
		border-radius: 4px;
		border: none;
		color: #fff !important;
		font-size: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		text-decoration: none !important;
	}

	.b-view {
		background: #f39c12;
	}

	.b-view:hover {
		background: #e08e0b;
	}

	.b-edit {
		background: #3c8dbc;
	}

	.b-edit:hover {
		background: #357ca5;
	}

	.b-print {
		background: #00a65a;
	}

	.b-print:hover {
		background: #008d4c;
	}

	.b-del {
		background: #dd4b39;
	}

	.b-del:hover {
		background: #d73925;
	}
</style>

<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete="off">
	<div class="box box-primary">
		<div class="box-header with-border">
			<div>
				<ol class="breadcrumb">
					<li><i class="fa fa-shopping-cart"></i> Procurement</li>
					<li class="active">PR Non-Rutin</li>
				</ol>
				<h3 class="box-title" style="font-size:16px; font-weight:700;"><?php echo $title; ?></h3>
			</div>
			<div class="box-tools pull-right">
				<?php if ($ENABLE_ADD) { ?>
					<a href="<?php echo site_url('non_rutin/add') ?>" class="btn btn-sm btn-success" id="btn-add">
						<i class="fa fa-plus"></i>&nbsp; Tambah PR
					</a>
				<?php } ?>
			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body table-responsive">
			<input type="hidden" id="tanda" value="<?= $tanda; ?>">
			<!-- <div class="col-md-4">
                <select name="" id="" class="form-control form-control-sm search_depart" style="margin-top: 5px;">
                    <?php
					if ($this->auth->user_id() == '7') {
						echo '<option value="">- Department -</option>';
					}
					foreach ($list_department as $item) {
						echo '<option value="' . $item->id . '">' . strtoupper($item->name) . ' - ' . strtoupper($item->nm_company) . '</option>';
					}
					?>
                </select>
                <button type="button" class="btn btn-sm btn-primary search_btn" style=""><i class="fa fa-search"></i> Cari</button>
            </div> -->
			<div class="col-12 col_table">
				<table class="table table-bordered table-striped" id="my-grid" width="100%">
					<thead>
						<tr>
							<th class="text-center" width="3%">#</th>
							<th class="text-center" width="10%">No PR</th>
							<th class="text-center no-sort" width="16%">Keterangan PR</th>
							<th class="text-center" width="13%">Departemen</th>
							<th class="text-center no-sort" width="7%">Tingkat PR</th>
							<th class="text-center no-sort" width="8%">Request By</th>
							<th class="text-center no-sort" width="9%">Tanggal PR Dibuat</th>
							<th class="text-center no-sort" width="19%">Progress PR</th>
							<th class="text-center no-sort" width="15%">Option Action</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->

	<!-- Modal View PR -->
	<div class="modal fade" id="ModalView2" style="overflow-y: auto;">
		<div class="modal-dialog" style="width:80%;">
			<div class="modal-content">
				<div class="modal-header" style="background:#3c8dbc; color:#fff; border-radius:3px 3px 0 0;">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff; opacity:1;">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title" id="head_title2"></h4>
				</div>
				<div class="modal-body" id="view2"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<!-- /modal -->
</form>

<!-- Modal Closing PR -->
<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header" style="background:#dd4b39; color:#fff; border-radius:3px 3px 0 0;">
				<button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:1;">
					<span aria-hidden="true">&times;</span>
					<span class="sr-only">Close</span>
				</button>
				<h4 class="modal-title" id="myModalLabel"><i class="fa fa-times-circle"></i>&nbsp; Closing PR</h4>
			</div>
			<form action="" method="post" id="frm-data">
				<div class="modal-body" id="ModalView">
					...
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-sm btn-default" onclick="$('#dialog-popup').modal('hide')">
						<i class="fa fa-times"></i> Cancel
					</button>
					<button type="submit" class="btn btn-sm btn-danger">
						<i class="fa fa-lock"></i> Close PR
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
	$(document).ready(function() {
		$('.maskM').autoNumeric();

		DataTables();

		if ($.fn.chosen) {
			$('.search_depart').chosen({
				width: '250px',
			});
		}
	});

	$(document).on('click', '.close_pr_modal', function() {
		var no_pengajuan = $(this).data('no_pengajuan');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'close_pr_modal',
			data: {
				'no_pengajuan': no_pengajuan
			},
			cache: false,
			success: function(result) {
				$('#ModalView').html(result);
				$('#dialog-popup').modal('show');
			},
			error: function(result) {
				Swal.fire({
					title: 'Error !',
					text: 'Please try again later !',
					icon: 'error'
				});
			}
		});
	});

	$(document).on('click', '.close_pr', function() {
		var no_pengajuan = $(this).data('no_pengajuan');

		Swal.fire({
			title: 'Are you sure to close this PR ?',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonText: 'Close',
			confirmButtonColor: '#d33',
			cancelButtonText: 'Batal'
		}).then((result) => {
			if (result.isConfirmed) {
				$.ajax({
					type: 'POST',
					url: siteurl + active_controller + 'close_pr',
					data: {
						'no_pengajuan': no_pengajuan
					},
					cache: false,
					dataType: 'json',
					success: function(result) {
						if (result.status == '1') {
							Swal.fire({
								title: 'Success !',
								text: 'PR has been closed',
								icon: 'success'
							}).then(() => {
								location.reload(true);
							});
						} else {
							Swal.fire({
								title: 'Failed !',
								text: 'PR has not been closed',
								icon: 'warning'
							});
						}
					},
					error: function(result) {
						Swal.fire({
							title: 'Error !',
							text: 'Please try again later !',
							icon: 'error'
						});
					}
				});
			}
		});
	});

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();

		var data = new FormData($('#frm-data')[0]);
		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'close_pr',
			data: data,
			cache: false,
			dataType: 'json',
			processData: false,
			contentType: false,
			success: function(result) {
				if (result.status == '1') {
					Swal.fire({
						title: 'Success !',
						text: 'PR has been closed',
						icon: 'success'
					}).then(() => {
						location.reload(true);
					});
				} else {
					Swal.fire({
						title: 'Failed !',
						text: 'PR has not been closed',
						icon: 'warning'
					});
				}
			},
			error: function(result) {
				Swal.fire({
					title: 'Error !',
					text: 'Please try again later !',
					icon: 'error'
				});
			}
		});
	});

	$(document).on('click', '.search_btn', function() {
		var search_depart = $('.search_depart').val();

		$.ajax({
			url: siteurl + active_controller + 'search_by_depart',
			type: 'POST',
			data: {
				'depart': search_depart
			},
			cache: false,
			success: function(result) {
				$('.col_table').html(result);
				DataTables();
			},
			error: function(result) {
				Swal.fire({
					title: 'Error !',
					text: 'Please try again later !',
					icon: 'error'
				});
			}
		});
	});

	function DataTables() {
		var dataTable = $('#my-grid').DataTable({
			serverSide: true,
			processing: true,
			destroy: true,
			paging: true,
			ajax: {
				type: 'get',
				url: siteurl + active_controller + 'get_data_non_rutin'
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'no_pr'
				},
				{
					data: 'keterangan'
				},
				{
					data: 'departemen'
				},
				{
					data: 'tingkat_pr'
				},
				{
					data: 'pic'
				},
				{
					data: 'created_date'
				},
				{
					data: 'progress_pr'
				},
				{
					data: 'option'
				}
			]
		});
	}
</script>