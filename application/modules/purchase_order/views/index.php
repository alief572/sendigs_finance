<?php
$ENABLE_ADD     = has_permission('Purchase_Order.Add');
$ENABLE_MANAGE  = has_permission('Purchase_Order.Manage');
$ENABLE_VIEW    = has_permission('Purchase_Order.View');
$ENABLE_DELETE  = has_permission('Purchase_Order.Delete');

?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>

<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
	<div class="box-header">
		<span class="pull-left">
			<?php if ($ENABLE_ADD) : ?>
				<a class="btn btn-success btn-sm" href="<?= base_url('/purchase_order/addPurchaseorder/') ?>" title="Add"><i class="fa fa-plus"></i>&nbsp;Create PO</a>
			<?php endif; ?>
		</span>
	</div>
	<!-- /.box-header -->
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th width="5">#</th>
					<th>No PO</th>
					<th>No PR</th>
					<th>No Incoming</th>
					<th>Tanggal PO</th>
					<th>Progress PO</th>
					<th>PO</th>
					<th>Vendor</th>
					<th>Harga PO</th>
					<th>Revisi</th>
					<th>Reject Reason</th>
					<?php if ($ENABLE_MANAGE) : ?>
						<th>Action</th>
					<?php endif; ?>
				</tr>
			</thead>

			<tbody>
				<?php if (empty($results)) {
				} else {

					$numb = 0;
					foreach ($results as $record) {
						$valid_edit = 1;
						$numb++;

						$no_pr = [];
						$get_no_pr = $this->db->query("
							SELECT
								b.no_pr as no_pr
							FROM
								material_planning_base_on_produksi_detail a
								JOIN material_planning_base_on_produksi b ON b.so_number = a.so_number
							WHERE
								a.id IN (SELECT aa.idpr FROM dt_trans_po aa WHERE aa.no_po = '" . $record->no_po . "' AND (aa.tipe IS NULL OR aa.tipe = ''))
							GROUP BY b.no_pr

							UNION ALL 

							SELECT
								b.no_pr as no_pr
							FROM
								rutin_non_planning_detail a
								JOIN rutin_non_planning_header b ON b.no_pengajuan = a.no_pengajuan
							WHERE
								a.id IN (SELECT aa.idpr FROM dt_trans_po aa WHERE aa.no_po = '" . $record->no_po . "' AND aa.tipe = 'pr depart')
							GROUP BY b.no_pr

							UNION ALL

							SELECT
								a.no_pr as no_pr
							FROM
								asset_planning a
							WHERE
								a.id IN (SELECT aa.idpr FROM dt_trans_po aa WHERE aa.no_po = '" . $record->no_po . "' AND aa.tipe = 'pr asset')
							GROUP BY a.no_pr

							UNION ALL

							SELECT
								a.id as no_pr
							FROM
								" . DBCNL . ".kons_tr_kasbon_project_header a 
							WHERE
								a.id IN (SELECT aa.idpr FROM dt_trans_po aa WHERE aa.no_po = '" . $record->no_po . "' AND aa.tipe = 'project consultant')
							GROUP BY a.id
						")->result();
						foreach ($get_no_pr as $item_pr) {
							$no_pr[] = $item_pr->no_pr;
						}

						$no_pr = implode(', ', $no_pr);

				?>
						<tr>
							<td><?= $numb; ?></td>
							<td><?= $record->no_surat ?></td>
							<td><?= $no_pr ?></td>
							<td><?= $list_no_incoming[$record->no_po] ?></td>
							<td><?= date('d-M-Y', strtotime($record->tanggal)) ?></td>
							<?php
							$ttl_qty_incoming = 0;
							if ($record->status == '1') {
								if ($record->reject_reason !== null && $record->reject_reason !== '') {
									echo "<td class='text-center'><span class='badge bg-red'>Reject</span></td>";
								} else {
									echo "<td class='text-center'><span class='badge bg-blue'>Waiting</span></td>";
								}
							} elseif ($record->status == '2') {
								echo "<td class='text-center'><span class='badge bg-green'>Approved</span></td>";
							} else {
								echo "<td class='text-center'><span class='badge bg-red'>Closed</span></td>";
							}

							if ($record->status == '1') {
								echo "<td class='text-center'><span class='badge bg-red'>Waiting Approval</span></td>";
							} else {
								$sts = "<td class='text-center'><span class='badge bg-orange'>Waiting In</span></td>";

								$get_qty_incoming = $this->db->select('IF(SUM(qty_oke) IS NUll, 0, SUM(qty_oke)) as ttl_qty_incoming')->get_where('tr_checked_incoming_detail', ['no_ipp' => $record->no_po])->row();
								$ttl_qty_incoming = $get_qty_incoming->ttl_qty_incoming;

								$get_ttl_qty_po = $this->db->select('IF(SUM(qty) IS NULL, 0, SUM(qty)) AS ttl_qty_po')->get_where('dt_trans_po', ['no_po' => $record->no_po])->row();
								$ttl_qty_po = $get_ttl_qty_po->ttl_qty_po;

								if ($ttl_qty_incoming < 1) {
									$ttl_qty_incoming = 0;
									$this->db->select('b.qty_oke');
									$this->db->from('warehouse_adjustment a');
									$this->db->join('warehouse_adjustment_detail b', 'b.kode_trans = a.kode_trans');
									$this->db->where('a.no_ipp', $record->no_po);
									$get_ttl_incoming = $this->db->get()->result();

									foreach ($get_ttl_incoming as $item_ttl_incoming) {
										$ttl_qty_incoming += $item_ttl_incoming->qty_oke;
									}
								}

								if ($ttl_qty_incoming > 0) {
									$valid_edit = 0;
									if ($ttl_qty_incoming >= $ttl_qty_po) {
										$sts = "<td class='text-center'><span class='badge bg-green'>Complete</span></td>";
									} else {

										$sts = "<td class='text-center'><span class='badge bg-purple'>Partial</span></td>";
									}
								}

								echo $sts;
							}
							?>
							<td><?= $record->nm_supplier ?></td>
							<td class="text-right"><?= number_format($record->total_barang - $record->nilai_disc + $record->total_ppn + $record->taxtotal) ?></td>
							<td class="text-center"><?= $record->revisi ?></td>
							<td><?= $record->reject_reason ?></td>
							<td style="padding-left:20px">
								<?php if ($ENABLE_VIEW) : ?>
									<a class="btn btn-warning btn-sm" href="<?= base_url('/purchase_order/view_po/' . $record->no_po) ?>" title="View" data-no_po="<?= $record->no_po ?>"><i class="fa fa-eye"></i></a>

									<a class="btn btn-primary btn-sm" href="<?= base_url('/purchase_order/PrintH2/' . $record->no_po) ?>" target="_blank" title="Print"><i class="fa fa-print"></i></a>
								<?php endif; ?>
								<?php if ($ENABLE_MANAGE && $valid_edit > 0) :
								?>
									<a class="btn btn-info btn-sm" href="<?= base_url('/purchase_order/edit/' . $record->no_po) ?>" title="Edit"><i class="fa fa-edit"></i></a>

								<?php
								endif; ?>

								<?php
								if ($ENABLE_DELETE) {
									echo '<button type="button" class="btn btn-sm btn-danger close_po_modal" data-no_po="' . $record->no_po . '" title="Close PO"><i class="fa fa-close"></i></button>';
								}
								?>

							</td>

						</tr>
				<?php }
				}  ?>
			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-primary" id="dialog-rekap" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-file-pdf-o"></span>&nbsp;Rekap Data Customer</h4>
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

<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Data Penawaran</h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">
					<span class="glyphicon glyphicon-remove"></span> Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal modal-default fade" id="dialog-popupCP" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">Closing PO</h4>
			</div>
			<form action="" method="post" id="CP-frm-data">
				<div class="modal-body" id="ModalViewCP">
					...
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">
						<span class="glyphicon glyphicon-remove"></span> Cancel
					</button>
					<button type="submit" class="btn btn-sm btn-danger">Close It!</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js') ?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		$('#example1').dataTable();
	});

	$(document).on('click', '.close_po_modal', function() {
		var no_po = $(this).data('no_po');

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'close_po_modal',
			data: {
				'no_po': no_po
			},
			cache: false,
			success: function(result) {
				$('#ModalViewCP').html(result);
				$('#dialog-popupCP').modal('show');
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

	$(document).on('submit', '#CP-frm-data', function() {
		var data = new FormData($('#CP-frm-data')[0]);

		$.ajax({
			type: 'post',
			url: siteurl + active_controller + 'close_po',
			data: data,
			cache: false,
			dataType: 'json',
			processData: false,
			contentType: false,
			success: function(result) {
				if (result.status == 1) {
					swal({
						title: 'Success !',
						text: 'PO has been closed',
						type: 'success'
					});
				} else {
					swal({
						title: 'Failed !',
						text: 'PO has not been closed',
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

	$(document).on('click', '.edit', function(e) {
		var id = $(this).data('no_penawaran');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'penawaran/EditHeader/' + id,
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.cetak', function(e) {
		var id = $(this).data('no_penawaran');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'xtes/cetak' + id,
			success: function(data) {

			}
		})
	});

	$(document).on('click', '.view', function() {
		var id = $(this).data('no_po');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'purchase_order/Lihat/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});
	$(document).on('click', '.add', function() {
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Tambah Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'penawaran/addHeader',
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});


	// DELETE DATA
	$(document).on('click', '.Approve', function(e) {
		e.preventDefault()
		var id = $(this).data('no_po');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "PO. Akan Di Approve.",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Approve!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'purchase_order/Approved',
					dataType: "json",
					data: {
						'id': id
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "P.R Approved.",
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: "Data error. Gagal Approve data",
								type: "error"
							})

						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "Data error. Gagal request Ajax",
							type: "error"
						})
					}
				})
			});

	})

	$(function() {
		// $('#example1 thead tr').clone(true).appendTo( '#example1 thead' );
		// $('#example1 thead tr:eq(1) th').each( function (i) {
		// var title = $(this).text();
		//alert(title);
		// if (title == "#" || title =="Action" ) {
		// $(this).html( '' );
		// }else{
		// $(this).html( '<input type="text" />' );
		// }

		// $( 'input', this ).on( 'keyup change', function () {
		// if ( table.column(i).search() !== this.value ) {
		// table
		// .column(i)
		// .search( this.value )
		// .draw();
		// }else{
		// table
		// .column(i)
		// .search( this.value )
		// .draw();
		// }
		// } );
		// } );


		$("#form-area").hide();
	});


	//Delete

	function PreviewPdf(id) {
		param = id;
		tujuan = 'customer/print_request/' + param;

		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="570" height="400"></iframe>');
	}

	function PreviewRekap() {
		tujuan = 'customer/rekap_pdf';
		$(".modal-body").html('<iframe src="' + tujuan + '" frameborder="no" width="100%" height="400"></iframe>');
	}
</script>