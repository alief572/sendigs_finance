<?php
$ENABLE_ADD     = has_permission('PR_Product.Add');
$ENABLE_MANAGE  = has_permission('PR_Product.Manage');
$ENABLE_VIEW    = has_permission('PR_Product.View');
$ENABLE_DELETE  = has_permission('PR_Product.Delete');
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
		<?php if ($ENABLE_ADD) : ?>
			<!-- <a class="btn btn-info btn-sm" style='float:right; margin-left:5px;' href="<?= base_url('stock_origa/download_excel'); ?>" target='_blank' title="Download"><i class="fa fa-excel">&nbsp;</i>Excel</a> -->
			<a class="btn btn-success btn-md" style='float:right;' href="<?= base_url('pr_product/add') ?>" title="Add">Add</a>
		<?php endif; ?>
		<br>
		<div class="form-group row" hidden>
			<div class="col-md-1">
				<b>Product Type</b>
			</div>
			<div class="col-md-3">
				<select name='product' id='product' class='form-control input-sm chosen-select'>
					<option value='0'>All Product Type</option>
					<?php
					foreach (get_list_inventory_lv1('product') as $val => $valx) {
						echo "<option value='" . $valx['code_lv1'] . "'>" . strtoupper($valx['nama']) . "</option>";
					}
					?>
				</select>
			</div>
		</div>
		<div class="form-group row" hidden>
			<div class="col-md-1">
				<b>Costcenter</b>
			</div>
			<div class="col-md-3">
				<select name='costcenter' id='costcenter' class='form-control input-sm chosen-select'>
					<option value='0'>All Costcenter</option>
					<?php
					foreach (get_costcenter() as $val => $valx) {
						echo "<option value='" . $valx['id_costcenter'] . "'>" . strtoupper($valx['nama_costcenter']) . "</option>";
					}
					?>
				</select>
			</div>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped" width='100%'>
			<thead>
				<tr>
					<th class="text-center">#</th>
					<th class="text-center">No. PR</th>
					<th class="text-center">Nama Barang</th>
					<th class="text-center">Qty</th>
					<th class="text-center">Dibutuhkan</th>
					<th class="text-center">Status</th>
					<th class="text-center">Request By</th>
					<th class="text-center">Request Date</th>
					<th class="text-center">Option</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$nomor = 1;
				foreach ($list_pr as $row) {
					echo '<tr>';
					$list_barang = [];
					$list_qty_barang = [];
					$this->db->select('a.propose_purchase, a.qty_order, b.nama as nm_barang, c.code as satuan');
					$this->db->from('material_planning_base_on_produksi_detail a');
					$this->db->join('new_inventory_4 b', 'b.code_lv4 = a.id_material', 'left');
					$this->db->join('ms_satuan c', 'c.id = b.id_unit', 'left');
					$this->db->where('a.so_number', $row['so_number']);
					$this->db->where('b.nama <>', null);

					$get_barang = $this->db->get()->result();
					foreach ($get_barang as $item) {
						$list_barang[] = $item->nm_barang;
						if ($item->propose_purchase == null || $item->propose_purchase <= 0) {
							$list_qty_barang[] = number_format($item->qty_order, 2) . ' ' . strtoupper($item->satuan);
						} else {
							$list_qty_barang[] = number_format($item->propose_purchase, 2) . ' ' . strtoupper($item->satuan);
						}
					}
					$list_barang = implode('<br><br>', $list_barang);
					$list_qty_barang = implode('<br><br>', $list_qty_barang);

					$nestedData   = array();
					echo "<td align='center'>" . $nomor . "</td>";
					echo "<td align'center'>" . $row['no_pr'] . "</td>";
					echo "<td align'center'>" . $list_barang . "</td>";
					echo "<td align'center'>" . $list_qty_barang . "</td>";
					echo "<td align'center'>" . date('d F Y', strtotime($row['tgl_dibutuhkan'])) . "</td>";


					$getCheck = $this->db->get_where('material_planning_base_on_produksi_detail', array('so_number' => $row['so_number'], 'status_app' => 'N'))->result();

					if (($row['sts_reject1'] !== null || $row['sts_reject2'] !== null || $row['sts_reject3'] !== null) && $row['rejected'] == 1) {
						if ($row['sts_reject1'] == "1") :
							$warna = "red";
							$sts = "Rejected By Head";
						elseif ($row['sts_reject2'] == "1") :
							$warna = "red";
							$sts = "Rejected By Cost Control";
						elseif ($row['sts_reject3'] == "1") :
							$warna = "red";
							$sts = "Rejected By Management";
						endif;
					} else {
						if ($row['app_1'] == null && $row['app_2'] == null && $row['app_3'] == null) :
							$warna = "blue";
							$sts = "Waiting Approval Head";
						elseif ($row['app_1'] !== null && $row['app_2'] == null && $row['app_3'] == null) :
							$warna = "blue";
							$sts = "Waiting Approval Cost Control";
						elseif ($row['app_1'] !== null && $row['app_2'] !== null && $row['app_3'] == null) :
							$warna = "blue";
							$sts = "Waiting Approval Management";
						else :
							if ($row['sts_app'] == "Y") :
								$warna = "green";
								$sts = "Approved";
							else :
								$warna = "blue";
								$sts = "Waiting Approval Head";
							endif;
						endif;
					}
					if (COUNT($getCheck) <= 0) {
						$sts = 'Approved';
						$warna = 'green';
					}

					echo "<td align='left'><span class='badge' style='background-color: " . $warna . ";'>" . $sts . "</span></td>";
					echo "<td align='center'>" . $row['request_by'] . "</td>";
					echo "<td align='center'>" . $row['request_date'] . "</td>";

					$print  = "<a href='" . base_url($this->uri->segment(1)) . '/print_new/' . $row['so_number'] . "' target='_blank' class='btn btn-sm btn-primary' blank='_blank'><i class='fa fa-print'></i></a>";
					$view   = "<a href='" . site_url($this->uri->segment(1)) . '/detail_planning/' . $row['so_number'] . "' class='btn btn-sm btn-warning' title='Detail PR' data-role='qtip'><i class='fa fa-eye'></i></a>";
					$edit   = "";
					if ($ENABLE_MANAGE) {
						if (COUNT($getCheck) > 0 || $row['reject_status'] == '1') {
							$edit   = "<a href='" . site_url($this->uri->segment(1)) . '/edit_planning/' . $row['so_number'] . "' class='btn btn-sm btn-info' title='Edit PR' data-role='qtip'><i class='fa fa-edit'></i></a>";
						}
					}

					$close = '';
					if ($ENABLE_DELETE) {
						$close = '<button type="button" class="btn btn-sm btn-danger close_pr_modal" data-so_number="' . $row['so_number'] . '" title="Close PR"><i class="fa fa-close"></i></button>';
					}


					if ($row['reject_status'] == '1') {
						echo "<td align='left'>" . $view . " " . $edit . " " . $close . "</td>";
					} else {
						echo "<td align='left'>" . $view . " " . $edit . " " . $print . " " . $close . "</td>";
					}
					$data[] = $nestedData;
					echo '</tr>';
					$nomor++;
				}
				?>
			</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>


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

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).on('click', '.close_pr_modal', function() {
		var so_number = $(this).data('so_number');

		$.ajax({
			type: 'POST',
			url: siteurl + active_controller + 'close_pr_modal',
			data: {
				'so_number': so_number
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
		var so_number = $(this).data('so_number');

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
						'so_number': so_number
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

	$(document).on('click', '.detail', function() {
		var so_number = $(this).data('so_number');
		// alert(id);
		$("#head_title").html("<b>Detail>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + 'detail',
			data: {
				'so_number': so_number,
			},
			success: function(data) {
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	// DELETE DATA
	$(document).on('click', '.booking', function(e) {
		e.preventDefault()
		var so_number = $(this).data('so_number');
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "Process Booking Material & PR !",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: base_url + active_controller + 'process_booking',
					dataType: "json",
					data: {
						'so_number': so_number
					},
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: result.pesan,
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: result.pesan,
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

	$(document).ready(function() {
		var product = $("#product").val();
		var costcenter = $("#costcenter").val();
		DataTables(costcenter, product);

		$(document).on('change', '#costcenter', function() {
			var costcenter = $("#costcenter").val();
			var product = $("#product").val();
			DataTables(costcenter, product);
		});

		$(document).on('change', '#product', function() {
			var costcenter = $("#costcenter").val();
			var product = $("#product").val();
			DataTables(costcenter, product);
		});
	});


	function DataTables(costcenter = null, product = null) {
		var dataTable = $('#example1').DataTable();
	}
</script>