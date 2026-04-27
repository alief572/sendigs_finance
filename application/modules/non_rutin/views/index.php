<?php
$ENABLE_ADD     = has_permission('PR_Departemen.Add');
$ENABLE_MANAGE  = has_permission('PR_Departemen.Manage');
$ENABLE_VIEW    = has_permission('PR_Departemen.View');
$ENABLE_DELETE  = has_permission('PR_Departemen.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete='off'>
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
			<div class="box-tool pull-right">
				<?php
				if ($ENABLE_ADD) {
				?>
					<a href="<?php echo site_url('non_rutin/add') ?>" class="btn btn-sm btn-success" style='float:right;' id='btn-add'>
						<i class="fa fa-plus"></i> &nbsp;&nbsp;Add
					</a>
				<?php
				}
				?>
			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body table-responsive">
			<input type='hidden' id='tanda' value='<?= $tanda; ?>'>
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
				<button type="button" class="btn btn-sm btn-primary search_btn" style=''><i class="fa fa-search"></i> Cari</button>
			</div> -->
			<div class="col-12 col_table">
				<table class="table table-bordered table-striped" id="my-grid" width='100%'>
					<thead>
						<tr class='bg-blue'>
							<th class="text-center">#</th>
							<th class="text-center">No PR</th>
							<th class="text-center">Departemen</th>
							<th class="text-center no-sort">Keterangan Project</th>
							<th class="text-center no-sort">Tingkat PR</th>
							<th class="text-center no-sort">PIC</th>
							<th class="text-center no-sort">Created Date</th>
							<th class="text-center no-sort">Status</th>
							<th class="text-center no-sort" width='13%'>Option</th>
						</tr>
					</thead>
					<tbody>

					</tbody>
				</table>
			</div>
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

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">Closing PR</h4>
			</div>
			<form action="" method="post" id="frm-data">
				<div class="modal-body" id="ModalView">
					...
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-sm btn-secondary" onclick="$('#dialog-popup').modal('hide')">Cancel</button>
					<button type="submit" class="btn btn-sm btn-danger">Close PR</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script src="https://cdn.datatables.net/2.0.2/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
	$(document).ready(function() {
		$('.maskM').autoNumeric();

		DataTables();

		$('.search_depart').chosen({
			width: '250px',
		});
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