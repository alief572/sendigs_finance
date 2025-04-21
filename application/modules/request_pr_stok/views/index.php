<?php
$ENABLE_ADD     = has_permission('PR_Stok.Add');
$ENABLE_MANAGE  = has_permission('PR_Stok.Manage');
$ENABLE_VIEW    = has_permission('PR_Stok.View');
$ENABLE_DELETE  = has_permission('PR_Stok.Delete');
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
			<a class="btn btn-success btn-md" style='float:right;' href="<?= base_url('request_pr_stok/add_new') ?>" title="Add">Add</a>
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
					<th class="text-center">Kategori PR</th>
					<th class="text-center">Nama Barang</th>
					<th class="text-center" style="min-width: 8% !important;">Qty (Pack)</th>
					<th class="text-center">Dibutuhkan</th>
					<th class="text-center">Status</th>
					<th class="text-center">Request By</th>
					<th class="text-center">Request Date</th>
					<th class="text-center">Option</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$no = 1;
				foreach ($result as $row) {
					$get_detail_pr = $this->db->get_where('material_planning_base_on_produksi_detail', ['so_number' => $row->so_number])->result();

					$nm_detail = '';
					$qty_detail = '';
					foreach ($get_detail_pr as $item) {
						// $get_stok_data = $this->db->get_where('accessories', ['id' => $row->id_material])->row();
						$this->db->select('a.stock_name, b.code');
						$this->db->from('accessories a');
						$this->db->join('ms_satuan b', 'b.id = a.id_unit_gudang', 'left');
						$this->db->where('a.id', $item->id_material);
						$get_stok_data = $this->db->get()->row();

						if (!empty($get_stok_data)) {
							$nm_detail = $nm_detail . $get_stok_data->stock_name . '<br>';
							$qty_detail = $qty_detail . number_format($item->propose_purchase, 2) . ' ' . ucfirst($get_stok_data->code) . '<br>';
						}
					}

					$kategori_pr = [];
					$this->db->select('c.nm_category as kategori');
					$this->db->from('material_planning_base_on_produksi_detail a');
					$this->db->join('accessories b', 'b.id = a.id_material', 'left');
					$this->db->join('accessories_category c', 'c.id = b.id_category', 'left');
					$this->db->where('a.so_number', $row->so_number);
					$this->db->group_by('c.id');
					$get_kategori_pr = $this->db->get()->result();
					foreach ($get_kategori_pr as $item_kategori_pr) {
						$kategori_pr[] = $item_kategori_pr->kategori;
					}

					if (!empty($kategori_pr)) {
						$kategori_pr = implode(', ', $kategori_pr);
					} else {
						$kategori_pr = '';
					}

					echo '<tr>';
					echo '<td class="text-center">' . $no . '</td>';
					echo '<td>' . strtoupper($row->no_pr) . '</td>';
					echo '<td>' . strtoupper($kategori_pr) . '</td>';
					echo '<td>' . $nm_detail . '</td>';
					echo '<td class="text-right">' . $qty_detail . '</td>';
					echo '<td>' . date('d F Y', strtotime($row->tgl_dibutuhkan)) . '</td>';

					$getCheck = $this->db->get_where('material_planning_base_on_produksi_detail', array('so_number' => $row->so_number, 'status_app' => 'N'))->result();

					$valid_edit = 1;
					if (($row->sts_reject1 !== null || $row->sts_reject2 !== null || $row->sts_reject3 !== null) && $row->rejected == 1) {
						if ($row->sts_reject1 == "1") :
							$warna = "red";
							$sts = "Rejected By Head";
						elseif ($row->sts_reject2 == "1") :
							$warna = "red";
							$sts = "Rejected By Cost Control";
						elseif ($row->sts_reject3 == "1") :
							$warna = "red";
							$sts = "Rejected By Management";
						endif;
					} else {
						if ($row->app_1 == null && $row->app_2 == null && $row->app_3 == null) :
							$warna = "blue";
							$sts = "Waiting Approval Head";
						elseif ($row->app_1 !== null && $row->app_2 == null && $row->app_3 == null) :
							$warna = "blue";
							$sts = "Waiting Approval Cost Control";
						elseif ($row->app_1 !== null && $row->app_2 !== null && $row->app_3 == null) :
							$warna = "blue";
							$sts = "Waiting Approval Management";
						else :
							if ($row->sts_app == "Y") :
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

						$valid_edit = 0;
					}

					echo '<td><span class="badge" style="background-color: ' . $warna . '">' . $sts . '</span></td>';
					echo '<td class="text-center">' . $row->request_by . '</td>';
					echo '<td class="text-center">' . $row->request_date . '</td>';

					$approve  = "";
					$view  = "<a href='" . site_url($this->uri->segment(1)) . '/detail_planning/' . $row->so_number . "' class='btn btn-sm btn-warning' title='Detail PR' data-role='qtip'><i class='fa fa-eye'></i></a>";
					$edit   = "";
					if ($ENABLE_MANAGE && $valid_edit > 0) {
						$edit   = "<a href='" . site_url($this->uri->segment(1)) . '/edit_planning/' . $row->so_number . "' class='btn btn-sm btn-info' title='Edit PR' data-role='qtip'><i class='fa fa-edit'></i></a>";
					}

					$print = '<a href="' . site_url($this->uri->segment(1)) . '/PrintH2/' . $row->so_number . '" class="btn btn-sm btn-info" title="Print PR" target="_blank"><i class="fa fa-download"></i></a>';

					$close = '';
					if ($ENABLE_DELETE) {
						$close = '<button type="button" class="btn btn-sm btn-danger close_pr_modal" data-so_number="' . $row->so_number . '" title="Close PR"><i class="fa fa-close"></i></button>';
					}

					echo '<td>' . $view . ' ' . $edit . ' ' . $approve . ' ' . $print . ' ' . $close . '</td>';

					echo '</tr>';

					$no++;
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