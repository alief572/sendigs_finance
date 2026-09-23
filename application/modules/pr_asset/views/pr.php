<?php
$ENABLE_VIEW = has_permission('PR_Asset.View');
$ENABLE_ADD = has_permission('PR_Asset.Add');
$ENABLE_MANAGE = has_permission('PR_Asset.Manage');
$ENABLE_DELETE = has_permission('PR_Asset.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
			<div class="box-tool pull-right">
				<?php
				if ($ENABLE_ADD && $tanda == '') {
					echo '
						<a href="' . site_url("pr_asset/add_pr") . '" class="btn btn-sm btn-success" id="btn-add">
							<i class="fa fa-plus"></i> &nbsp;&nbsp;Add PR
						</a>';
				}
				?>
			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body table-responsive">
			<input type='hidden' id='tanda' value='<?= $tanda; ?>'>

			<?php if (empty($tanda)) : ?>
				<!-- FLOWMAP CARD -->
				<div class="flowmap">
					<b>Alur PR Asset:</b>
					<div class="flow-trunk"><code>PR — Direktur</code> <span class="branch-note">(1 level saja, tanpa Finance)</span> &rarr; <code>Metode Pembelian</code></div>
					<div class="flow-branches">
						<div class="branch-row"><span class="branch-tag">jika Direct Payment</span> &rarr; <code>Request Payment</code><span class="branch-note">(langsung, tanpa approval tambahan — tahap final)</span></div>
						<div class="branch-row"><span class="branch-tag">jika Kasbon</span> &rarr; <code>Kasbon — Finance</code> &rarr; <code>Kasbon — Direktur</code> &rarr; <code>Request Payment</code><span class="branch-note">(setelah 2 level kasbon approve — tahap final)</span></div>
					</div>
				</div>

				<!-- LEGEND -->
				<div class="legend">
					<span><span class="dot dot-pending"></span> Belum sampai tahap ini</span>
					<span><span class="dot dot-active"></span> Sedang berjalan / menunggu</span>
					<span><span class="dot dot-done"></span> Selesai tahap ini</span>
					<span><span class="dot dot-reject"></span> Ditolak</span>
				</div>
			<?php endif; ?>

			<table class="table table-bordered table-striped" id="my-grid" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center" style="width: 40px;">#</th>
						<th class="text-center" style="width: 120px;">No PR</th>
						<th class="text-center">Nama Barang</th>
						<th class="text-center" style="width: 200px;">Departemen</th>
						<th class="text-center" style="width: 130px;">Request By</th>
						<th class="text-center" style="width: 140px;">Tanggal PR Dibuat</th>
						<th class="text-center no-sort" style="min-width: 320px;">Progress PR</th>
						<th class="text-center no-sort" style="width: 100px;">Option Action</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->
	<!-- modal -->
	<div class="modal fade" id="ModalView2" style='overflow-y: auto;'>
		<div class="modal-dialog" style='width:80%; '>
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="head_title2"></h4>
				</div>
				<div class="modal-body" id="view2">
				</div>
				<div class="modal-footer">
					<!--<button type="button" class="btn btn-primary">Save</button>-->
					<button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<!-- modal -->
</form>
<!-- <script src="<?php echo base_url('application/views/Component/general.js'); ?>"></script> -->
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<style>
	.chosen-container-active .chosen-single {
		border: none;
		box-shadow: none;
	}

	.chosen-container-single .chosen-single {
		height: 34px;
		border: 1px solid #d2d6de;
		border-radius: 0px;
		background: none;
		box-shadow: none;
		color: #444;
		line-height: 32px;
	}

	.chosen-container-single .chosen-single div {
		top: 5px;
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

	.pic {
		color: #555;
		font-size: 12px;
	}

	.progress-cell {
		min-width: 280px;
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
		flex-shrink: 0;
		transition: transform 0.15s ease;
	}

	.step-dot.done {
		background: #1f7a45;
	}

	.step-dot.active {
		background: #a1670d;
		box-shadow: 0 0 0 3px rgba(161, 103, 13, 0.2);
	}

	.step-dot.pending {
		background: #cbd2d9;
	}

	.step-dot.reject {
		background: #b32b2b;
		box-shadow: 0 0 0 3px rgba(179, 43, 43, 0.2);
	}

	.step-line {
		flex: 1;
		height: 2px;
		background: #e2e6ea;
	}

	.step-line.done {
		background: #1f7a45;
	}

	.step-labels {
		display: flex;
		justify-content: space-between;
		font-size: 9.5px;
		color: #8c96a3;
		margin-bottom: 6px;
		line-height: 1.2;
		gap: 2px;
	}

	.step-lbl {
		flex: 1;
		text-align: center;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.step-lbl:first-child {
		text-align: left;
	}

	.step-lbl:last-child {
		text-align: right;
	}

	.step-lbl.cur {
		color: #1f2937;
		font-weight: 700;
	}

	.step-lbl.rej {
		color: #b32b2b;
		font-weight: 700;
	}

	.status-badge {
		display: inline-block;
		padding: 2px 8px;
		border-radius: 12px;
		font-size: 11px;
		font-weight: 600;
		line-height: 1.4;
	}

	.st-wait {
		background: #fdf1d9;
		color: #a1670d;
	}

	.st-final {
		background: #e2f6ea;
		color: #1f7a45;
	}

	.st-reject {
		background: #fbe4e4;
		color: #b32b2b;
	}

	.doc-meta {
		font-size: 11px;
		color: #6b7280;
		margin-top: 3px;
	}

	.reject-reason {
		font-size: 11px;
		color: #b32b2b;
		background: #fff5f5;
		border-left: 3px solid #b32b2b;
		padding: 3px 6px;
		margin-top: 4px;
		border-radius: 2px;
		line-height: 1.35;
	}

	.opts {
		display: flex;
		gap: 6px;
	}

	.opt-btn {
		width: 26px;
		height: 26px;
		border-radius: 5px;
		border: none;
		color: #fff !important;
		font-size: 12px;
		display: flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		text-decoration: none !important;
	}

	.b-view {
		background: #e0a530;
	}

	.b-edit {
		background: #3f7ac9;
	}

	.b-print {
		background: #3fa85a;
	}

	.b-del {
		background: #d9534f;
	}

	.stage-label {
		font-size: 12px;
		font-weight: 700;
		color: #1f2937;
		margin-bottom: 3px;
	}

	.flowmap {
		background: #fff;
		border: 1px solid #d2d6de;
		border-left: 4px solid #3c8dbc;
		border-radius: 4px;
		padding: 12px 16px;
		margin-bottom: 14px;
		font-size: 12px;
		color: #555;
	}

	.flowmap b {
		color: #1f2937;
	}

	.flowmap code {
		background: #eef1f5;
		padding: 2px 6px;
		border-radius: 4px;
		color: #1f2937;
		font-size: 11.5px;
	}

	.flow-trunk {
		font-size: 12.5px;
		color: #1f2937;
		margin-top: 4px;
		padding-bottom: 2px;
	}

	.flow-branches {
		margin-top: 8px;
		padding-left: 14px;
		border-left: 2px dashed #d2d6de;
	}

	.branch-row {
		margin: 5px 0;
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 4px;
	}

	.branch-tag {
		display: inline-block;
		padding: 1px 8px;
		border-radius: 12px;
		font-size: 11px;
		font-weight: 700;
		background: #fdf1d9;
		color: #a1670d;
	}

	.branch-note {
		color: #777;
		font-size: 11.5px;
		margin-left: 4px;
	}

	.legend {
		display: flex;
		gap: 16px;
		flex-wrap: wrap;
		margin-bottom: 14px;
		font-size: 12px;
		color: #666;
	}

	.legend span {
		display: inline-flex;
		align-items: center;
		gap: 6px;
	}

	.dot {
		width: 9px;
		height: 9px;
		border-radius: 50%;
		display: inline-block;
	}

	.dot-pending {
		background: #cbd2d9;
	}

	.dot-active {
		background: #a1670d;
	}

	.dot-done {
		background: #1f7a45;
	}

	.dot-reject {
		background: #b32b2b;
	}
</style>
<script>
	$(document).ready(function() {
		var tanda = $('#tanda').val();
		DataTables(tanda);

		$(document).on('click', '.look_hide', function() {
			var idOfParent = $(this).data('id');
			$('.child-' + idOfParent).toggle('slow');
		});
	});

	$(document).on('click', '.print_pr', function(e) {
		e.preventDefault();
		var Link = base_url + active_controller + 'print_pr_asset/' + $(this).data('no_pr');
		window.open(Link)
	});

	$(document).on('click', '.detail', function(e) {
		e.preventDefault();

		$("#head_title2").html("<b>DETAIL BUDGET RUTIN [" + $(this).data('code') + "]</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + 'detail_rutin/' + $(this).data('code'),
			success: function(data) {
				$("#ModalView2").modal();
				$("#view2").html(data);

			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Timed Out ...',
					type: "warning",
					timer: 5000,
					showCancelButton: false,
					showConfirmButton: false,
					allowOutsideClick: false
				});
			}
		});
	});

	$(document).on('click', '.approve', function() {
		var nomor = $(this).data('id');
		var tipe_approve = $(this).data('tipe_approve');
		var no_pr = $('#no_pr_' + nomor).val().split(",").join("");
		var action = $('#action_' + nomor).val().split(",").join("");
		var reason = $('#reason_' + nomor).val();

		var link_after = '';
		if (tipe_approve == 'approval_head') {
			var link_after = base_url + active_controller + 'pr/approval_head';
		}
		if (tipe_approve == 'approval_cost_control') {
			var link_after = base_url + active_controller + 'pr/approval_cost_control';
		}
		if (tipe_approve == 'approval_management') {
			var link_after = base_url + active_controller + 'pr/approval_management';
		}

		alert(tipe_approve);

		if (action == 'N') {
			if (reason == '') {
				swal({
					title: "Error Message!",
					text: 'Reason action is empty, please input first ...',
					type: "warning"
				});
				return false;
			}
		}

		swal({
				title: "Are you sure?",
				text: "You will not be able to process again this data!",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Yes, Process it!",
				cancelButtonText: "No, cancel process!",
				closeOnConfirm: false,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {

					$.ajax({
						url: base_url + active_controller + 'approve_pr',
						type: "POST",
						data: {
							"no_pr": no_pr,
							"action": action,
							"reason": reason,
							"tipe_approve": tipe_approve
						},
						cache: false,
						dataType: 'json',
						success: function(data) {
							if (data.status == 1) {
								swal({
									title: "Save Success!",
									text: data.pesan,
									type: "success",
									timer: 7000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
								window.location.href = link_after;
							} else if (data.status == 0) {
								swal({
									title: "Save Failed!",
									text: data.pesan,
									type: "warning",
									timer: 7000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
							}
						},
						error: function() {
							swal({
								title: "Error Message !",
								text: 'An Error Occured During Process. Please try again..',
								type: "warning",
								timer: 7000,
								showCancelButton: false,
								showConfirmButton: false,
								allowOutsideClick: false
							});
						}
					});
				} else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				}
			});
	});


	function DataTables(tanda = null) {
		var dataTable = $('#my-grid').DataTable({
			"serverSide": true,
			"stateSave": true,
			"bAutoWidth": true,
			"destroy": true,
			"processing": true,
			"responsive": true,
			"fixedHeader": {
				"header": true,
				"footer": true
			},
			"aaSorting": [
				[5, "desc"]
			],
			"columnDefs": [{
				"targets": 'no-sort',
				"orderable": false,
			}],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			"ajax": {
				url: base_url + active_controller + 'server_side_pr_asset',
				type: "post",
				data: function(d) {
					d.tanda = tanda
				},
				cache: false,
				error: function() {
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display", "none");
				}
			}
		});
	}
</script>