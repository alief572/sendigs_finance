<?php
$ENABLE_ADD     = has_permission('List_Outstanding_PR.Add');
$ENABLE_MANAGE  = has_permission('List_Outstanding_PR.Manage');
$ENABLE_VIEW    = has_permission('List_Outstanding_PR.View');
$ENABLE_DELETE  = has_permission('List_Outstanding_PR.Delete');

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
			<select name="" id="" class="form-control form-control-sm filter_status">
				<option value="">- Filter Status -</option>
				<option value="1" <?= (isset($status_filter) && $status_filter == '1') ? 'selected' : null ?>>Close</option>
				<option value="2" <?= (isset($status_filter) && $status_filter == '2') ? 'selected' : null ?>>Partial</option>
				<option value="3" <?= (isset($status_filter) && $status_filter == '3') ? 'selected' : null ?>>Outstanding</option>
			</select>
		</span>
	</div>
	<!-- /.box-header -->
	<!-- /.box-header -->

	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th class="text-center" width="5">#</th>
					<th class="text-center">Asal Permintaan</th>
					<th class="text-center">Nomor Request</th>
					<th class="text-center">Untuk Kebutuhan</th>
					<th class="text-center">Request By</th>
					<th class="text-center">Request Date</th>
					<th class="text-center">Status</th>
					<?php if ($ENABLE_MANAGE) : ?>
						<th class="text-center" width="13%">Option</th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
				<?php
				$no = 1;
				foreach ($list_data as $record) :

					$nama_user = '';
					$get_user = $this->db->get_where('users', ['id_user' => $record->booking_by])->row();
					if ($get_user) {
						$nama_user = $get_user->nm_lengkap;
					} else {
						$nama_user = "User tidak ditemukan";
					}

					$ttl_detail_pr = 0;
					$ttl_detail_po = 0;

					$get_detail_pr = $this->db->get_where('material_planning_base_on_produksi_detail', ['so_number' => $record->no_pengajuan, 'status_app' => 'Y'])->result();
					foreach ($get_detail_pr as $item_detail_pr) {
						$ttl_detail_pr += $item_detail_pr->propose_purchase;
					}

					$get_detail_pr_depart = $this->db->get_where('rutin_non_planning_detail', ['no_pengajuan' => $record->no_pengajuan])->result();
					foreach ($get_detail_pr_depart as $item_detail_pr_depart) {
						$ttl_detail_pr += $item_detail_pr_depart->qty;
					}

					$get_ttl_detail_po = $this->db->query("
							SELECT
								a.qty
							FROM
								dt_trans_po a
								JOIN tr_purchase_order b ON b.no_po = a.no_po
							WHERE
								CASE
									WHEN (a.tipe IS NOT NULL AND a.tipe <> '') 
										THEN a.idpr IN (SELECT aa.id FROM rutin_non_planning_detail aa WHERE aa.no_pengajuan = '" . $record->no_pengajuan . "')
									ELSE
										a.idpr IN (SELECT aa.id FROM material_planning_base_on_produksi_detail aa WHERE aa.so_number = '" . $record->no_pengajuan . "')
								END;
						")
						->result();
					foreach ($get_ttl_detail_po as $item_ttl_detail_po) {
						$ttl_detail_po += $item_ttl_detail_po->qty;
					}



					// $get_ttl_detail_po = $this->db->query("
					// 		SELECT
					// 			a.qty
					// 		FROM
					// 			dt_trans_po a
					// 			JOIN tr_purchase_order b ON b.no_po = a.no_po
					// 		WHERE
					// 			a.idpr IN (SELECT aa.id FROM rutin_non_planning_detail aa WHERE aa.no_pengajuan = '" . $record->no_pengajuan . "')
					// 	")
					// 	->result();
					// foreach ($get_ttl_detail_po as $item_ttl_detail_po) {
					// 	$ttl_detail_po += $item_ttl_detail_po->qty;
					// }

					if ($record->pr_non_depart == '1') {
					} else {
					}

					$all_pr_po = 1;
					$po_stat = 0;

					// $get_barang_pr = $this->db->get_where('material_planning_base_on_produksi', ['so_number' => $record->no_pr])->result();
					// foreach ($get_barang_pr as $barang_pr) {
					// 	$this->db->select('IF(a.qty IS NULL, 0, a.qty) as qty_po, a.no_po');
					// 	$this->db->from('dt_trans_po a');
					// 	$this->db->where('a.idpr', $barang_pr->id);
					// 	$get_barang_po = $this->db->get()->row();

					// 	if ($all_pr_po > 0) {
					// 		if ($get_barang_po->qty_po < $barang_pr->propose_purchase) {
					// 			$all_pr_po = 0;
					// 		}
					// 	}

					// 	$this->db->select('IF(a.status IS NULL, 0, a.status) as status_po');
					// 	$this->db->from('tr_purchase_order a');
					// 	$this->db->where('a.no_po', $get_barang_po->no_po);
					// 	$get_po = $this->db->get()->row();

					// 	if ($po_stat < 1) {
					// 		$po_stat = $get_po->status_po;
					// 	}
					// }


					$status = '<div class="badge bg-red">Outstanding</div>';
					$stat = 3;
					if ($ttl_detail_po > 0) {
						$status = '<div class="badge bg-yellow">Partial</div>';
						$stat = 2;
						if ($ttl_detail_po >= $ttl_detail_pr) {
							$status = '<div class="badge bg-green">Close</div>';
							$stat = 1;
						}
					}
					// if ($jum_barang_po > 0) {
					// 	$status = '<div class="badge bg-yellow">Partial</div>';
					// 	$stat = 2;
					// }
					// if (($jum_barang_pr == $jum_barang_po) && $all_pr_po > 0) {
					// 	$status = '<div class="badge bg-green">Close</div>';
					// 	$stat = 1;
					// }

					$view_cls = 'view';
					if ($record->pr_depart == '1') {
						$view_cls = 'view_depart';
					}
					if ($record->pr_asset == '1') {
						$view_cls = 'view_asset';
					}

					$no_pr_print = $record->no_pr;
					$link_print = base_url("purchase_request/PrintH2/" . $record->no_pengajuan);
					if ($record->pr_depart == '1') {
						$no_pr_print = $record->no_pr;
						$link_print = base_url("non_rutin/print_pengajuan_non_rutin/" . $record->no_pengajuan);
					}
					if ($record->pr_asset == '1') {
						$no_pr_print = $record->no_pr;
						$link_print = base_url("pr_asset/print_pr_asset/" . $no_pr_print);
					}

					if (isset($status_filter)) {
						if ($status_filter == $stat) {
							echo '
								<tr>
									<td class="text-center">' . $no . '</td>
									';

							if ($record->pr_non_depart == '1') {
								echo '<td class="text-center">' . strtoupper('PR MATERIAL ' . $record->no_pr) . '</td>';
							} else if ($record->pr_asset == '1') {
								echo '<td class="text-center">' . strtoupper('PR ASSET ' . $record->no_pr) . '</td>';
							} else {
								echo '<td class="text-center">' . strtoupper('PR DEPARTMENT ' . $record->no_pr) . '</td>';
							}

							echo '
									<td class="text-center">' . strtoupper($record->no_pr) . '</td>
									<td class="text-center">' . strtoupper($record->project) . '</td>
									<td class="text-center">' . ucwords(strtolower($nama_user)) . ucwords(strtolower($record->booking_by_name)) . '</td>
									<td class="text-center">' . date('d-M-Y', strtotime($record->booking_date)) . '</td>
									<td class="text-center">' . $status . '</td>
									<td class="text-center">
										';

							if ($record->pr_asset == '1') {
								$no_planning = '';
								$get_planning = $this->db->get_where('asset_planning', ['no_pr' => $record->no_pr])->row();
								if (!empty($get_planning)) {
									$no_planning = $get_planning->code_plan;
								}

								echo '<a href="' . base_url('asset_planning/add_asset/' . $no_planning . '/view') . '" class="btn btn-sm btn-info" target="_blank"><i class="fa fa-eye"></i></a>';
							} else {
								echo '<button type="button" class="btn btn-sm btn-info ' . $view_cls . '" data-no_pr="' . $record->no_pr . '"><i class="fa fa-eye"></i></button>';
							}

							echo '
										<a href="' . $link_print . '" class="btn btn-sm btn-primary" target="_blank"><i class="fa fa-print"></i></a>
									</td>
								</tr>
							';
						}
					} else {
						echo '
							<tr>
								<td class="text-center">' . $no . '</td>
								';

						if ($record->pr_non_depart == '1') {
							echo '<td class="text-center">' . strtoupper('PR MATERIAL ' . $record->no_pr) . '</td>';
						} else if ($record->pr_asset == '1') {
							echo '<td class="text-center">' . strtoupper('PR ASSET ' . $record->no_pr) . '</td>';
						} else {
							echo '<td class="text-center">' . strtoupper('PR DEPARTMENT ' . $record->no_pr) . '</td>';
						}

						echo '
								<td class="text-center">' . strtoupper($record->no_pr) . '</td>
								<td class="text-center">' . strtoupper($record->project) . '</td>
								<td class="text-center">' . ucwords(strtolower($nama_user)) . ucwords(strtolower($record->booking_by_name)) . '</td>
								<td class="text-center">' . date('d-M-Y', strtotime($record->booking_date)) . '</td>
								<td class="text-center">' . $status . '</td>
								<td class="text-center">
									';

						if ($record->pr_asset == '1') {
							$no_planning = '';
							$get_planning = $this->db->get_where('asset_planning', ['no_pr' => $record->no_pr])->row();
							if (!empty($get_planning)) {
								$no_planning = $get_planning->code_plan;
							}

							echo '<a href="' . base_url('asset_planning/add_asset/' . $no_planning . '/view') . '" class="btn btn-sm btn-info" target="_blank"><i class="fa fa-eye"></i></a>';
						} else {
							echo '<button type="button" class="btn btn-sm btn-info ' . $view_cls . '" data-no_pr="' . $record->no_pr . '"><i class="fa fa-eye"></i></button>';
						}

						echo '
									<a href="' . $link_print . '" class="btn btn-sm btn-primary" target="_blank"><i class="fa fa-print"></i></a>
								</td>
							</tr>
						';
					}


					$no++;
				endforeach;
				?>
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
	<div class="modal-dialog modal-lg mx-wd-md-90p-force mx-wd-lg-90p-force">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><i class="fa fa-list"></i> Detail PR</h4>
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

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function() {
		$('#example1').dataTable();
	});
	$(document).on('change', '.filter_status', function() {
		var filter_status = $(this).val();
		window.location.href = siteurl + active_controller + 'index/' + filter_status;
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
		var id = $(this).data('no_pr');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>View PR</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'purchase_request/View/' + id,
			data: {
				'id': id
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
			}
		})
	});

	$(document).on('click', '.view_depart', function() {
		var id = $(this).data('no_pr');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>View PR</b>");
		$.ajax({
			type: 'POST',
			url: siteurl + 'purchase_request/view_depart/' + id,
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
	$(document).on('click', '.request', function(e) {
		e.preventDefault()
		var so_number = $(this).data('so_number');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "PR ini akan di approve !",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'purchase_request/approval',
					dataType: "json",
					data: {
						'so_number': so_number
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "PR telah di approve !",
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: "PR gagal di approve !",
								type: "error"
							})

						}
					},
					error: function() {
						swal({
							title: "Error",
							text: "PR gagal di approval !",
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