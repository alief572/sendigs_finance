<?php
$ENABLE_ADD     = has_permission('Master_Bank.Add');
$ENABLE_MANAGE  = has_permission('Master_Bank.Manage');
$ENABLE_VIEW    = has_permission('Master_Bank.View');
$ENABLE_DELETE  = has_permission('Master_Bank.Delete');
?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css">
<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<button class="btn btn-success btn-sm add" title="Add"><i class="fa fa-plus">&nbsp;</i>Add</button>
		<?php endif; ?>

		<span class="pull-right">
		</span>
	</div>
	<!-- /.box-header -->
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example2" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th>Bank</th>
					<th>COA Bank</th>
					<th>Account Number</th>
					<th>Account Name</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody>

			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="head_title">Master Bank</h4>
			</div>
			<form action="" id="frm-data">
				<input type="hidden" id="id" name="id">
				<div class="modal-body" id="ModalView">
					<div class="box-body">
						<!-- <form id="data_form" autocomplete="off"> -->
						<div class="form-group">
							<label for="bank">Bank <span class="text-red">*</span></label>
							<select class="form-control form-control-sm list_bank" name="bank" required>
								<option value="">- Pilih Bank -</option>
								<?php
								foreach ($list_bank as $item) :
									echo '<option value="' . $item->id . '">' . strtoupper($item->nama_bank) . '</option>';
								endforeach;
								?>
							</select>
						</div>
						<div class="form-group">
							<label for="no_rek">No. Rekening <span class="text-red">*</span></label>
							<input type="text" name="no_rek" id="no_rek" class="form-control form-control-sm" required>
						</div>
						<div class="form-group">
							<label for="nama_rek">Nama Rekening <span class="text-red">*</span></label>
							<input type="text" name="nama_rek" id="nama_rek" class="form-control form-control-sm" required>
						</div>
						<div class="form-group">
							<label for="coa_bank">COA Bank <span class="text-red">*</span></label>
							<select class="form-control form-control-sm" name="coa_bank" id="coa_bank" required>
								<option value="">- Pilih COA Bank</option>
								<?php
								foreach ($list_coa_bank as $item) {
									echo '<option value="' . $item['no_perkiraan'] . '">' . $item['no_perkiraan'] . ' - ' . $item['nama'] . '</option>';
								}
								?>
							</select>
						</div>
						<!-- </form> -->
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">
						<span class="glyphicon glyphicon-remove"></span> Close
					</button>
					<button type="submit" class="btn btn-success">
						<i class="fa fa-save"></i> Save
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		DataTables();

		$('.list_bank').chosen({
			width: '100%'
		});
		$('#coa_bank').chosen({
			width: '100%'
		});
	});

	$(document).on('click', '.add', function() {
		$('#id').val('');
		$('.list_bank').val('');
		$('#coa_bank').val('');
		$('#no_rek').val('');
		$('#nama_rek').val('');

		$('.list_bank').trigger('chosen:updated');
		$('#coa_bank').trigger('chosen:updated');
		$('#dialog-popup').modal('show');
	})

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();

		swal({
			type: 'warning',
			title: 'Warning !',
			text: 'Data akan tersimpan !',
			showCancelButton: true
		}, function(next) {
			if (next) {
				var data = new FormData($('#frm-data')[0]);

				$.ajax({
					type: 'post',
					url: 'master_bank/save_bank',
					data: data,
					dataType: 'json',
					cache: false,
					contentType: false,
					processData: false,
					success: function(result) {
						if (result.status == '1') {
							swal({
								type: 'success',
								title: 'Success !',
								text: result.msg,
								showConfirmButton: false,
								timer: 3000,
								timerProgressBar: true
							}, function(lanjut) {
								$('#dialog-popup').modal('hide');
								swal.close();
								DataTables();
							});
						} else {
							swal({
								type: 'warning',
								title: 'Failed !',
								text: result.msg,
								showConfirmButton: false,
								timer: 3000,
								timerProgressBar: true
							});
						}
					},
					error: function(result) {
						swal({
							type: 'error',
							title: 'Error !',
							text: 'Please try again !',
							showConfirmButton: false,
							timer: 3000,
							timerProgressBar: true
						});
					}
				})
			}
		});
	});

	function delBank(id) {
		swal({
			type: 'warning',
			title: 'Warning !',
			text: 'Bank ini akan terhapus !',
			showCancelButton: true
		}, function(result) {
			if (result) {
				$.ajax({
					type: 'post',
					url: siteurl + active_controller + 'del_bank',
					data: {
						'id': id
					},
					cache: false,
					dataType: 'json',
					success: function(result) {
						if (result.status == '1') {
							swal({
								type: 'success',
								title: 'Success !',
								text: result.msg,
								showConfirmButton: false,
								timer: 3000,
								timerProgressBar: true
							}, function(lanjut) {
								swal.close();
								DataTables();
							});
						} else {
							swal({
								type: 'warning',
								title: 'Failed !',
								text: result.msg,
								showConfirmButton: false,
								timer: 3000,
								timerProgressBar: true
							});
						}
					},
					error: function(result) {
						swal({
							type: 'error',
							title: 'Error !',
							text: 'Please try again !',
							showConfirmButton: false,
							timer: 3000,
							timerProgressBar: true
						});
					}
				});
			}
		});
	}

	function EditBank(id) {
		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'EditBank',
			data: {
				'id': id
			},
			dataType: 'json',
			cache: false,
			success: function(result) {
				$('#id').val(result.id);
				$('.list_bank').val(result.bank);
				$('#coa_bank').val(result.coa_bank);
				$('#no_rek').val(result.no_rek);
				$('#nama_rek').val(result.nama_rek);

				$('.list_bank').trigger('chosen:updated');
				$('#coa_bank').trigger('chosen:updated');

				$('#dialog-popup').modal('show');
			},
			error: function(result) {
				swal({
					type: 'error',
					title: 'Error !',
					text: 'Please try again !',
					showConfirmButton: false,
					timer: 3000,
					timerProgressBar: true
				});
			}
		});
	}

	function DataTables() {
		$('#example2').DataTable({
			serverSide: true,
			processing: true,
			paging: true,
			stateSave: true,
			destroy: true,
			ajax: {
				type: 'post',
				url: siteurl + active_controller + 'get_data_bank',
				dataType: 'json'
			},
			columns: [{
					data: 'no'
				},
				{
					data: 'bank'
				},
				{
					data: 'coa_bank'
				},
				{
					data: 'account_number'
				},
				{
					data: 'account_name'
				},
				{
					data: 'action'
				}
			]
		});
	}
</script>