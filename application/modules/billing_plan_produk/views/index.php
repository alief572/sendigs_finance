<?php
$ENABLE_ADD     = has_permission('Billing_Plan_Produk.Add');
$ENABLE_MANAGE  = has_permission('Billing_Plan_Produk.Manage');
$ENABLE_VIEW    = has_permission('Billing_Plan_Produk.View');
$ENABLE_DELETE  = has_permission('Billing_Plan_Produk.Delete');
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
					<th class="text-center">No. SO</th>
					<th class="text-center">Customer Name</th>
					<th class="text-center">Currency</th>
					<th class="text-center">SO</th>
					<th class="text-center">Invoiced</th>
					<th class="text-center">Outstanding Invoice</th>
					<?php
					if ($ENABLE_MANAGE) {
						echo '<th class="text-center">Action</th>';
					}
					?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($results as $item) {

					$nilai_so = 0;
					$get_nilai_so = $this->db->get_where('tr_sales_order_detail', ['no_so' => $item->no_so])->result();
					foreach ($get_nilai_so as $item_nilai_so) {
						$harga_satuan = $item_nilai_so->harga_satuan;
						$qty = $item_nilai_so->qty;
						$diskon_nilai = $item_nilai_so->diskon_nilai;

						$nilai_so += (($harga_satuan - $diskon_nilai) * $qty);
					}

					$get_other_cost = $this->db->select('SUM(total_nilai) AS ttl_other_cost')->get_where('tr_penawaran_other_cost', ['id_penawaran' => $item->no_penawaran])->row();
					if (!empty($get_other_cost)) {
						$nilai_so += $get_other_cost->ttl_other_cost;
					}
					$get_other_item = $this->db->select('SUM(total) as ttl_other_item')->get_where('tr_penawaran_other_item', ['id_penawaran' => $item->no_penawaran])->row();
					if (!empty($get_other_item)) {
						$nilai_so += $get_other_item->ttl_other_item;
					}

					$ppn = 0;
					$nilai_ppn = 0;
					$get_penawaran = $this->db->get_where('tr_penawaran', ['no_penawaran' => $item->no_penawaran])->row();
					if (!empty($get_penawaran)) {
						$ppn = $get_penawaran->ppn;
						$nilai_ppn = $get_penawaran->nilai_ppn;
					}

					$nilai_so = ($nilai_so + $nilai_ppn);

					$nilai_invoice = 0;
					$get_nilai_invoice = $this->db
						->select('nilai_invoice')
						->from('tr_invoice_sales')
						->where('id_so', $item->no_so)
						->get()
						->result();

					foreach ($get_nilai_invoice as $item_invoice) {
						$nilai_invoice += $item_invoice->nilai_invoice;
					}


					// $nilai_ppn = ($nilai_so * $ppn / 100);
					// $nilai_so += $nilai_ppn;

					$outstanding = ($nilai_so - $nilai_invoice);



					echo '<tr>';
					echo '<td class="text-center">' . $item->no_so . '</td>';
					echo '<td class="text-center">' . $item->nm_customer . '</td>';
					echo '<td class="text-center">' . $item->currency . '</td>';
					echo '<td class="text-right">' . number_format($nilai_so, 2) . '</td>';
					echo '<td class="text-right">' . number_format($nilai_invoice, 2) . '</td>';
					echo '<td class="text-right">' . number_format($outstanding, 2) . '</td>';
					if ($ENABLE_MANAGE) {
						$billing_btn = '<button type="button" class="btn btn-sm btn-success billing_plan" data-no_so="' . $item->no_so . '" data-currency="' . $item->currency . '" title="Billing Plan"><i class="fa fa-check"></i></button>';

						if ($outstanding <= 0) {
							$billing_btn = '';
						}

						$view = '<button type="button" class="btn btn-sm btn-info" title="View"><i class="fa fa-eye"></i></button>';
						echo '<td class="text-center">' . $billing_btn . ' 	</td>';
					}
					echo '</tr>';
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

	$(document).on('click', '.billing_plan', function() {
		var no_so = $(this).data('no_so');
		var currency = $(this).data('currency');

		$.ajax({
			type: "POST",
			url: siteurl + active_controller + 'modal_billing_plan',
			data: {
				'no_so': no_so,
				'currency': currency
			},
			cache: false,
			success: function(result) {
				$('#ModalView').html(result);
				$('#dialog-popup').modal('show');

				loadmod();
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

	$(document).on('submit', '#frm-data', function(e) {
		e.preventDefault();

		swal({
			title: 'Are you sure ?',
			text: 'Data will be processed !',
			type: 'warning',
			showCancelButton: true,
			confirmButtonColor: "#3085d6",
			cancelButtonColor: "#d33",
			confirmButtonText: "Process"
		}, function(onConfirm) {
			if (onConfirm) {
				var data = new FormData($('#frm-data')[0]);

				$.ajax({
					type: 'POST',
					url: siteurl + active_controller + 'save_billing_plan',
					data: data,
					cache: false,
					dataType: 'json',
					processData: false,
					contentType: false,
					success: function(result) {
						if (result.status == '1') {
							swal({
								title: 'Success !',
								text: 'Billing Plan has been created !',
								type: 'success'
							}, function(final) {
								location.reload(true);
							});
						} else {
							if (result.status == '2') {
								swal({
									title: 'Failed !',
									text: 'Maaf, SPK Delivery belum dibuat !',
									type: 'warning'
								});
							} else {
								swal({
									title: 'Failed !',
									text: 'Billing Plan has not been created !',
									type: 'warning'
								});
							}
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
</script>