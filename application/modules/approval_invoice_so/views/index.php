<?php
$ENABLE_ADD     = has_permission('Approval_Invoice_SO.Add');
$ENABLE_MANAGE  = has_permission('Approval_Invoice_SO.Manage');
$ENABLE_VIEW    = has_permission('Approval_Invoice_SO.View');
$ENABLE_DELETE  = has_permission('Approval_Invoice_SO.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.2/css/dataTables.dataTables.min.css">
<div class="box">
	<div class="box-header">
		<span class="pull-right">
		</span>
	</div>
	<!-- /.box-header -->
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th class="text-center">No.</th>
					<th class="text-center">No. Invoice</th>
					<th class="text-center">No. SO</th>
					<th class="text-center">No. Penawaran</th>
					<th class="text-center">Customer Name</th>
					<th class="text-center">Currency</th>
					<th class="text-center">Tipe Invoice</th>
					<th class="text-center">Value Invoice</th>
					<?php
					if ($ENABLE_MANAGE) {
						echo '<th class="text-center">Action</th>';
					}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
				$no = 1;
				foreach ($results as $item) {

					echo '<tr>';
					echo '<td class="text-center">' . $no . '</td>';
					echo '<td class="text-center">' . $item->id_invoice . '</td>';
					echo '<td class="text-center">' . $item->id_so . '</td>';
					echo '<td class="text-center">' . $item->id_penawaran . '</td>';
					echo '<td class="text-left">' . $item->nm_customer . '</td>';
					echo '<td class="text-center">' . $item->currency . '</td>';
					echo '<td class="text-center">' . strtoupper($item->tipe_billing) . '</td>';
					echo '<td class="text-right">' . number_format($item->nilai_invoice, 2) . '</td>';
					if ($ENABLE_MANAGE) {
						$btn_approve = '<button type="button" class="btn btn-sm btn-success btn_app_invoice" title="Approve Invoice" data-id_invoice="' . $item->id_invoice . '">
							<i class="fa fa-check"></i>
						</button>';

						$btn_reject = '<button type="button" class="btn btn-sm btn-danger btn_rej_invoice" title="Reject Invoice" data-id_invoice="' . $item->id_invoice . '">
							<i class="fa fa-close"></i>
						</button>';

						echo '<td>' . $btn_approve . ' ' . $btn_reject . '</td>';
					}
					echo '</tr>';

					$no++;
				}
				?>
			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-primary" id="dialog-rekap" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><i class="fa fa-money"></i> Billing Plan</h4>
			</div>
			<div class="modal-body" id="MyModalBody">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">Billing Plan</h4>
			</div>
			<form action="" id="frm-data">
				<div class="modal-body" id="ModalView">
					...
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">
						<span class="glyphicon glyphicon-remove"></span> Close
					</button>
					<button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- modal -->
<div class="modal modal-default fade" id="ModalViewX" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id='head_title'>Closing Penawaran</h4>
			</div>
			<div class="modal-body" id="viewX">

			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-dismiss="modal" id='close_penawaran'>Save</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>

<div class="modal modal-default fade" id="ModalPrintQuote" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id='head_title'>Print Quotation</h4>
			</div>
			<form action="" id="print_quotation_form">
				<div class="modal-body" id="viewX">
					<input type="hidden" name="no_penawaran" class="no_penawaran">
					<div class="form-group">
						<label for="">Show PPN / Hide PPN</label>
						<select name="show_hide_ppn" id="" class="form-control form-control-sm show_hide_ppn">
							<option value="1">Show PPN</option>
							<option value="0">Hide PPN</option>
						</select>
					</div>
					<div class="form-group">
						<label for="">Show Discount / Hide Discount</label>
						<select name="show_hide_disc" id="" class="form-control form-control-sm show_hide_disc">
							<option value="1">Show Discount</option>
							<option value="0">Hide Discount</option>
						</select>
					</div>

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>

<div class="modal modal-default fade" id="ModalAddQuote" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id='head_title'>Create Quotation</h4>
			</div>
			<form action="" id="add_quotation_form">
				<div class="modal-body" id="viewX">
					<div class="form-group">
						<label for="">Quotation Type</label>
						<select name="currency" id="" class="form-control form-control-sm currency" required>
							<option value="IDR">Lokal</option>
							<option value="USD">Export</option>
						</select>
					</div>

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Save</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</form>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.1.2/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>


<!-- page script -->
<script type="text/javascript">
	function loadmod() {
		$('.auto_num').autoNumeric('init');
	}
	$(document).ready(function() {
		loadmod();

		$('#example1').dataTable();
	});

	$(document).on('click', '.btn_app_invoice', function() {
		var id_invoice = $(this).data('id_invoice');

		swal({
			title: "Are you sure?",
			text: "You will approve this Invoice, and can't be undone !",
			type: "warning",
			showCancelButton: true,
			confirmButtonText: "Approve",
			buttonstyling: false
		}, function(confirm) {
			if (confirm) {
				$.ajax({
					type: "POST",
					url: siteurl + active_controller + 'approve_invoice',
					data: {
						'id_invoice': id_invoice
					},
					cache: false,
					dataType: "json",
					success: function(result) {
						if (result.status = 1) {
							swal({
								title: "Success !",
								text: "Invoice has been Approved !",
								type: "success"
							}, function(success) {
								location.reload();
							});
						} else {
							swal({
								title: "Failed !",
								text: "Invoice has not been Approved !",
								type: "danger"
							});
						}
					},
					error: function(result) {
						swal({
							title: "Error !",
							text: "Please try again later !",
							type: "danger"
						});
					}
				});
			}
		});
	});

	$(document).on('click', '.btn_rej_invoice', function() {
		var id_invoice = $(this).data('id_invoice');

		swal({
			title: "Are you sure?",
			text: "You will reject this Invoice, and can't be undone !",
			type: "warning",
			showCancelButton: true,
			confirmButtonText: "Reject",
			confirmButtonColor: "red",
			input: "text"
		}, function(confirm) {
			if (confirm) {
				$.ajax({
					type: "POST",
					url: siteurl + active_controller + 'reject_invoice',
					data: {
						'id_invoice': id_invoice
					},
					cache: false,
					dataType: "json",
					success: function(result) {
						if (result.status = 1) {
							swal({
								title: "Success !",
								text: "Invoice has been Rejected !",
								type: "success"
							}, function(success) {
								location.reload();
							});
						} else {
							swal({
								title: "Failed !",
								text: "Invoice has not been Approved !",
								type: "danger"
							});
						}
					},
					error: function(result) {
						swal({
							title: "Error !",
							text: "Please try again later !",
							type: "danger"
						});
					}
				});
			}
		});
	});
</script>