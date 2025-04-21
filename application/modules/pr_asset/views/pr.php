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
		<div class="box-body">
			<input type='hidden' id='tanda' value='<?= $tanda; ?>'>
			<table class="table table-bordered table-striped" id="my-grid" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center" width='5%'>#</th>
						<th class="text-center" width='10%'>No PR</th>
						<th class="text-center" width='15%'>Tanggal PR</th>
						<th class="text-center">Nama Barang</th>
						<th class="text-center" width='15%'>PR By</th>
						<th class="text-center" width='15%'>PR Date</th>
						<th class="text-center no-sort" width='15%'>Status</th>
						<th class="text-center no-sort" width='7%'>Option</th>
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
				[1, "asc"]
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