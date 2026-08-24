<?php
$ENABLE_ADD     = has_permission('PR_Departemen.Add');
$ENABLE_MANAGE  = has_permission('PR_Departemen.Manage');
$ENABLE_VIEW    = has_permission('PR_Departemen.View');
$ENABLE_DELETE  = has_permission('PR_Departemen.Delete');
?>
<style>
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
	}

	.breadcrumb {
		background: none;
		padding: 0;
		font-size: 12px;
		margin-bottom: 5px;
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
							<th class="text-center">#</th>
							<th class="text-center">No PR</th>
							<th class="text-center no-sort">No. Dokumen</th>
							<th class="text-center no-sort">Status Dokumen</th>
							<th class="text-center no-sort">Tgl. Diproses</th>
							<th class="text-center">Departemen</th>
							<th class="text-center no-sort">Keterangan Project</th>
							<th class="text-center no-sort">Tingkat PR</th>
							<th class="text-center no-sort">PIC</th>
							<th class="text-center no-sort">Created Date</th>
							<th class="text-center no-sort">Status PR</th>
							<th class="text-center no-sort" width="13%">Option</th>
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
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
				})
			}
		});
	});

	$(document).on('click', '.close_pr', function() {
		var no_pengajuan = $(this).data('no_pengajuan');

		swal({
			title: 'Are you sure to close this PR ?',
			showCancelButton: true,
			confirmButtonText: 'Close',
			confirmButtonColor: 'red',
			type: 'warning'
		}, function(onConfirm) {
			if (onConfirm) {
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
							swal({
								title: 'Success !',
								text: 'PR has been closed',
								type: 'success'
							}, function(onConfirm) {
								location.reload(true);
							});
						} else {
							swal({
								title: 'Failed !',
								text: 'PR has not been closed',
								type: 'warning'
							});
						}
					},
					error: function(result) {
						swal({
							title: 'Error !',
							text: 'Please try again later !',
							type: 'error'
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
					swal({
						title: 'Success !',
						text: 'PR has been closed',
						type: 'success'
					}, function(onConfirm) {
						location.reload(true);
					});
				} else {
					swal({
						title: 'Failed !',
						text: 'PR has not been closed',
						type: 'warning'
					});
				}
			},
			error: function(result) {
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
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
				swal({
					title: 'Error !',
					text: 'Please try again later !',
					type: 'error'
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
					data: 'no_dokumen'
				},
				{
					data: 'status_dokumen'
				},
				{
					data: 'tgl_diproses'
				},
				{
					data: 'departemen'
				},
				{
					data: 'keterangan'
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
					data: 'status'
				},
				{
					data: 'option'
				}
			]
		});
	}
</script>