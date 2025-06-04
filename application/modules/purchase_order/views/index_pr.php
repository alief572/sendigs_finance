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


		<div class="text-right">
			<div class="box active">
				<div class="box-body">
					<div class="col-md-4">
						<select class="form-control" name="" id="filter_status">
							<option value="">- Filter Status -</option>
							<option value="1" <?= (isset($filter_status) && $filter_status == '1') ? 'selected' : null ?>>Outstanding</option>
							<option value="2" <?= (isset($filter_status) && $filter_status == '2') ? 'selected' : null ?>>Partial</option>
						</select>
					</div>
					<input type="hidden" name="cekppn" id="cekppn" class="form-control input-sm" placeholder="">
					<input type="hidden" name="cekcus" id="cekcus" class="form-control input-sm">
					<input type="hidden" id="cekcustomer" class="form-control input-sm">
					<button onclick="clear_checked_pr()" class="btn btn-danger" id="btn-clear-checked-pr" type="button"> Clear Checked PR </button>
					<button onclick="proses_do()" class="btn btn-primary" id="btn-proses-do" type="button"> Proses PO</button>
				</div>
			</div>
		</div>
	</div>
	<!-- /.box-header -->
	<!-- /.box-header -->

	<div class="box-body">
		<table id="example" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th class="text-center">#</th>
					<th class="text-center">No PR</th>
					<th class="text-center">Tgl PR</th>
					<th class="text-center">Requestor</th>
					<th class="text-center">Status</th>
					<?php if ($ENABLE_MANAGE) : ?>
						<th class="text-center">Action</th>
					<?php endif; ?>
				</tr>
			</thead>

			<tbody>
				<?php if (empty($results)) {
				} else {

					$numb = 0;
					foreach ($results as $record) {

						$no_materil = 0;
						$no_materil_po = 0;

						if ($record->tipe_pr == 'pr depart') {

							$get_materil = $this->db->get_where('rutin_non_planning_detail', ['no_pengajuan' => $record->so_number])->result();

							foreach ($get_materil as $materil) {
								$no_materil += $materil->qty;

								$get_po_materil = $this->db->get_where('dt_trans_po', ['idpr' => $materil->id, 'tipe' => 'pr depart'])->num_rows();
								if ($get_po_materil > 0) {
									$get_po_materil_qty = $this->db->query("SELECT IF(SUM(a.qty) IS NOT NULL, SUM(a.qty), 0) AS qty_materil FROM dt_trans_po a WHERE a.idpr = '" . $materil->id . "' AND a.tipe = 'pr depart'")->row();
									$no_materil_po += $get_po_materil_qty->qty_materil;
								}
							}
						} else if ($record->tipe_pr == 'pr asset') {
							$get_materil = $this->db->get_where('asset_planning', ['no_pr' => $record->no_pr])->result();

							$no_materil = 1;
							foreach ($get_materil as $materil) {

								$get_po_materil = $this->db->get_where('dt_trans_po', ['idpr' => $materil->id, 'tipe' => 'pr asset'])->num_rows();
								if ($get_po_materil > 0) {
									$get_po_materil_qty = $this->db->query("SELECT IF(SUM(a.qty) IS NOT NULL, SUM(a.qty), 0) AS qty_materil FROM dt_trans_po a WHERE a.idpr = '" . $materil->id . "' AND a.tipe = 'pr asset'")->row();
									$no_materil_po += $get_po_materil_qty->qty_materil;
								}
							}
						} else if ($record->tipe_pr == 'project consultant') {
							// $get_materil = $this->db->get_where(DBCNL . '.kons_tr_kasbon_project_header', ['no_pr' => $record->no_pr])->result();

							$no_materil = 1;

							$get_po_materil = $this->db->get_where('dt_trans_po', ['idpr' => $materil->id, 'tipe' => 'project consultant'])->num_rows();
							if ($get_po_materil > 0) {
								$no_materil_po += 1;
							}
						} else {
							$get_materil = $this->db->get_where('material_planning_base_on_produksi_detail', ['so_number' => $record->so_number, 'status_app' => 'Y'])->result();

							foreach ($get_materil as $materil) {
								$no_materil += $materil->propose_purchase;

								$get_po_materil = $this->db->query("SELECT a.id FROM dt_trans_po a WHERE a.idpr = '" . $materil->id . "' AND (a.tipe IS NULL OR a.tipe = '')")->num_rows();
								if ($get_po_materil > 0) {
									$get_po_materil_qty = $this->db->query("SELECT IF(SUM(a.qty) IS NOT NULL, SUM(a.qty), 0) AS qty_materil FROM dt_trans_po a WHERE a.idpr = '" . $materil->id . "' AND (a.tipe IS NULL OR a.tipe = '')")->row();
									$no_materil_po += $get_po_materil_qty->qty_materil;
								}
							}
						}

						// if ($record->so_number == 'P240300010') {
						// print_r($no_materil . ' ' . $no_materil_po);
						// exit;
						// }

						// print_r($no_materil . ' - ' . $no_materil_po);
						// exit;

						if (isset($filter_status)) {
							if ($no_materil > $no_materil_po) {
								$numb++;
								$stat = 1;
								$status = '<div class="badge bg-red">Outstanding</div>';
								// print_r($no_materil . ' - ' . $no_materil_po);
								// exit;
								if ($no_materil_po > 0 && $no_materil > $no_materil_po) {
									$stat = 2;
									$status = '<div class="badge bg-yellow">Partial</div>';
								}

								$get_checked_pr = $this->db->get_where('tr_po_checked_pr', ['no_pr' => $record->so_number, 'id_user' => $this->auth->user_id()])->result();

								$checked = '';
								if (count($get_checked_pr) > 0) {
									$checked = 'checked';
								}

								if ($stat == $filter_status) {
				?>

									<tr>
										<td class="text-center"><?= $numb; ?></td>
										<td class="text-center"><?= $record->no_pr ?></td>
										<td class="text-center"><?= $record->tgl_so ?></td>
										<td class="text-center"><?= $record->nama_user ?></td>
										<td class="text-center"><?= $status ?></td>
										<td class="text-center"><?php if ($ENABLE_MANAGE) : ?>
												<input <?php //echo $disabled
														?> type="checkbox" class="set_choose_do" name="set_choose_do" id="set_choose_do<?php echo $numb ?>" value="<?php echo $record->so_number ?>" onclick="cekcus('<?php echo $record->nama_user ?>','<?php echo $numb ?>','<?= isset($vso->ppn) ? $vso->ppn : ''; ?>','<?= isset($vso->nm_customer) ? $vso->nm_customer : ''; ?>','<?php echo 'set_choose_do' . $numb ?>')" data-tipe_pr="<?= $record->tipe_pr ?>" <?= $checked ?>>
											<?php endif; ?>
										</td>



									</tr>

								<?php
								}
							}
						} else {
							if (($no_materil > $no_materil_po && $record->tipe_pr !== 'project consultant') || ($record->tipe_pr == 'project consultant' && $no_materil_po < 1)) {
								$numb++;
								$stat = 1;
								$status = '<div class="badge bg-red">Outstanding</div>';
								// print_r($no_materil . ' - ' . $no_materil_po);
								// exit;
								if ($no_materil_po > 0 && $no_materil > $no_materil_po) {
									$stat = 2;
									$status = '<div class="badge bg-yellow">Partial</div>';
								}

								$get_checked_pr = $this->db->get_where('tr_po_checked_pr', ['no_pr' => $record->so_number, 'id_user' => $this->auth->user_id()])->result();

								$checked = '';
								if (count($get_checked_pr) > 0) {
									$checked = 'checked';
								}
								?>

								<tr>
									<td class="text-center"><?= $numb; ?></td>
									<td class="text-center"><?= $record->no_pr ?></td>
									<td class="text-center"><?= $record->tgl_so ?></td>
									<td class="text-center"><?= $record->nama_user ?></td>
									<td class="text-center"><?= $status ?></td>
									<td class="text-center"><?php if ($ENABLE_MANAGE) : ?>
											<input type="checkbox" class="set_choose_do" name="set_choose_do" id="set_choose_do<?php echo $numb ?>" value="<?php echo $record->so_number ?>" onclick="cekcus('<?php echo $record->nama_user ?>','<?php echo $numb ?>','<?= isset($vso->ppn) ? $vso->ppn : ''; ?>','<?= isset($vso->nm_customer) ? $vso->nm_customer : ''; ?>','<?php echo 'set_choose_do' . $numb ?>')" data-tipe_pr="<?= $record->tipe_pr ?>" <?= $checked ?>>
										<?php endif; ?>
									</td>



								</tr>

				<?php
							}
						}
					}
				}  ?>
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

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
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
<!-- /.modal -->

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		$('#example').dataTable();
		$("#idcustomer").select2({
			placeholder: "Pilih",
			allowClear: true
		});
	});

	function getcustomer() {
		var idcus = $('#idcustomer').val();
		window.location.href = siteurl + "wt_delivery_order/addSpkdelivery/" + idcus;
	}
	$(document).on('click', '.edit', function(e) {
		var id = $(this).data('no_penawaran');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Edit Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'wt_penawaran/editPenawaran/' + id,
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
		var id = $(this).data('no_penawaran');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Inventory</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'penawaran/ViewHeader/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});



	// CLOSE PENAWARAN
	$(document).on('click', '.close_penawaran', function(e) {
		e.preventDefault();
		var id = $(this).data('no_penawaran');

		$("#head_title").html("Closing Penawaran");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + '/modal_closing_penawaran/' + id,
			success: function(data) {
				$("#ModalViewX").modal();
				$("#viewX").html(data);

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

	$(document).on('change', '#filter_status', function() {
		var filter_status = $(this).val();

		window.location.href = siteurl + active_controller + 'addPurchaseorder/' + filter_status;
	});

	$(function() {

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




	function cekcus(idcus, no, ppn, id, set) {
		var table = $('#example1').DataTable();
		var cek = $('#' + set);
		//alert(cek.value);
		if (cek.is(":checked")) {
			table.column(2).search(id).draw();
		} else {
			table.column(2).search('').draw();
		}

		var customer = $('#cekcustomer').val();
		var cekppn = $('#cekppn').val();
		var reason = [];


		var jumcus = 0;
		$(".set_choose_do").each(function() {
			// reason.push($(this).val());
			// jumcus++;	
			var no_pr = $(this).val();
			var tipe_pr = $(this).data('tipe_pr');

			if ($(this).is(':checked')) {
				$.ajax({
					type: "POST",
					url: siteurl + active_controller + 'add_checked_pr',
					data: {
						'no_pr': no_pr,
						'tipe_pr': tipe_pr
					},
					cache: false,
					success: function(result) {

					},
					error: function(result) {
						swal({
							title: 'Error !',
							text: 'Please try again later !',
							type: 'error'
						});
					}
				});
			} else {
				$.ajax({
					type: "POST",
					url: siteurl + active_controller + 'del_checked_pr',
					data: {
						'no_pr': no_pr
					},
					cache: false,
					success: function(result) {

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
		// $('#cekcus').val(reason.join(';'));
	}

	function proses_do() {
		// var param = $('#cekcus').val();
		// var uri3 = '<?php echo $this->uri->segment(3) ?>';
		// window.location.href = siteurl + "purchase_order/proses/" + uri3 + "?param=" + param;

		swal({
				title: "Are you sure?",
				text: "You will be moved to next page with checked PR !",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Yes, process it!",
				cancelButtonText: "No, cancel process!",
				closeOnConfirm: true,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {
					$.ajax({
						url: siteurl + active_controller + 'process_do',
						type: "POST",
						data: '',
						cache: false,
						dataType: 'json',
						success: function(data) {
							if (data.list_id == "0") {
								swal("Error !", "Please at least select 1 PR !", "error");
							} else {
								$.ajax({
									url: siteurl + active_controller + 'clear_checked_pr',
									type: "POST",
									data: '',
									cache: false,
									dataType: 'json',
									success: function(result) {
										window.location.href = siteurl + "purchase_order/proses/?param=" + data.list_id;
									}
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
	}

	function clear_checked_pr() {
		swal({
				title: "Are you sure?",
				text: "All checked PR will be removed !",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Yes, Clear it!",
				cancelButtonText: "No, cancel process!",
				closeOnConfirm: true,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {

					$.ajax({
						url: siteurl + active_controller + 'clear_checked_pr',
						type: "POST",
						data: '',
						cache: false,
						dataType: 'json',
						success: function(data) {
							if (data.status == 1) {
								swal({
									title: "Clear Success!",
									text: data.pesan,
									type: "success",
									timer: 7000,
									showCancelButton: false,
									showConfirmButton: false,
									allowOutsideClick: false
								});
								location.reload(true);
							} else {

								if (data.status == 2) {
									swal({
										title: "Process Failed!",
										text: data.pesan,
										type: "warning",
										timer: 7000,
										showCancelButton: false,
										showConfirmButton: false,
										allowOutsideClick: false
									});
								} else {
									swal({
										title: "Process Failed!",
										text: data.pesan,
										type: "warning",
										timer: 7000,
										showCancelButton: false,
										showConfirmButton: false,
										allowOutsideClick: false
									});
								}

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
	}
</script>