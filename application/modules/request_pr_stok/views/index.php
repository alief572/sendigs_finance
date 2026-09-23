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

	.no-pr {
		color: #b3261e;
		font-weight: 600;
	}

	.kat {
		color: #555;
		font-size: 12px;
	}

	.pic {
		color: #555;
		font-size: 12px;
	}

	.item-line {
		line-height: 1.6;
	}

	.qty-line {
		line-height: 1.6;
		text-align: right;
	}

	.progress-cell {
		min-width: 280px;
	}

	.steps {
		display: flex;
		align-items: center;
		gap: 0;
		margin-bottom: 6px;
	}

	.step-dot {
		width: 10px;
		height: 10px;
		border-radius: 50%;
		flex-shrink: 0;
		transition: transform 0.15s ease;
	}

	.step-dot.done {
		background: #1f7a45;
	}

	.step-dot.active {
		background: #a1670d;
		box-shadow: 0 0 0 3px rgba(161, 103, 13, 0.2);
	}

	.step-dot.pending {
		background: #cbd2d9;
	}

	.step-dot.reject {
		background: #b32b2b;
		box-shadow: 0 0 0 3px rgba(179, 43, 43, 0.2);
	}

	.step-line {
		flex: 1;
		height: 2px;
		background: #e2e6ea;
	}

	.step-line.done {
		background: #1f7a45;
	}

	.step-labels {
		display: flex;
		justify-content: space-between;
		font-size: 9.5px;
		color: #8c96a3;
		margin-bottom: 6px;
		line-height: 1.2;
		gap: 2px;
	}

	.step-lbl {
		flex: 1;
		text-align: center;
		overflow: hidden;
		text-overflow: ellipsis;
		white-space: nowrap;
	}

	.step-lbl:first-child {
		text-align: left;
	}

	.step-lbl:last-child {
		text-align: right;
	}

	.step-lbl.cur {
		color: #1f2937;
		font-weight: 700;
	}

	.step-lbl.rej {
		color: #b32b2b;
		font-weight: 700;
	}

	.status-badge {
		display: inline-block;
		padding: 2px 8px;
		border-radius: 12px;
		font-size: 11px;
		font-weight: 600;
		line-height: 1.4;
	}

	.st-wait {
		background: #fdf1d9;
		color: #a1670d;
	}

	.st-final {
		background: #e2f6ea;
		color: #1f7a45;
	}

	.st-reject {
		background: #fbe4e4;
		color: #b32b2b;
	}

	.doc-meta {
		font-size: 11px;
		color: #6b7280;
		margin-top: 3px;
	}

	.reject-reason {
		font-size: 11px;
		color: #b32b2b;
		background: #fff5f5;
		border-left: 3px solid #b32b2b;
		padding: 3px 6px;
		margin-top: 4px;
		border-radius: 2px;
		line-height: 1.35;
	}

	.opts {
		display: flex;
		gap: 6px;
	}

	.opt-btn {
		width: 26px;
		height: 26px;
		border-radius: 5px;
		border: none;
		color: #fff !important;
		font-size: 12px;
		display: flex;
		align-items: center;
		justify-content: center;
		cursor: pointer;
		text-decoration: none !important;
	}

	.b-view {
		background: #e0a530;
	}

	.b-edit {
		background: #3f7ac9;
	}

	.b-print {
		background: #3fa85a;
	}

	.b-del {
		background: #d9534f;
	}

	.stage-label {
		font-size: 12px;
		font-weight: 700;
		color: #1f2937;
		margin-bottom: 3px;
	}

	.flowmap {
		background: #fff;
		border: 1px solid #d2d6de;
		border-left: 4px solid #3c8dbc;
		border-radius: 4px;
		padding: 12px 16px;
		margin-bottom: 14px;
		font-size: 12px;
		color: #555;
	}

	.flowmap b {
		color: #1f2937;
	}

	.flowmap code {
		background: #eef1f5;
		padding: 2px 6px;
		border-radius: 4px;
		color: #1f2937;
		font-size: 11.5px;
	}

	.flow-trunk {
		font-size: 12.5px;
		color: #1f2937;
		margin-top: 4px;
		padding-bottom: 2px;
	}

	.flow-branches {
		margin-top: 8px;
		padding-left: 14px;
		border-left: 2px dashed #d2d6de;
	}

	.branch-row {
		margin: 5px 0;
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 4px;
	}

	.branch-tag {
		display: inline-block;
		padding: 1px 8px;
		border-radius: 12px;
		font-size: 11px;
		font-weight: 700;
		background: #fdf1d9;
		color: #a1670d;
	}

	.branch-note {
		color: #777;
		font-size: 11.5px;
		margin-left: 4px;
	}

	.legend {
		display: flex;
		gap: 16px;
		flex-wrap: wrap;
		margin-bottom: 14px;
		font-size: 12px;
		color: #666;
	}

	.legend span {
		display: inline-flex;
		align-items: center;
		gap: 6px;
	}

	.dot {
		width: 9px;
		height: 9px;
		border-radius: 50%;
		display: inline-block;
	}

	.dot-pending {
		background: #cbd2d9;
	}

	.dot-active {
		background: #a1670d;
	}

	.dot-done {
		background: #1f7a45;
	}

	.dot-reject {
		background: #b32b2b;
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
		<!-- FLOWMAP CARD -->
		<div class="flowmap">
			<b>Alur PR Stock:</b>
			<div class="flow-trunk"><code>PR — Direktur</code> <span class="branch-note">(1 level saja, tanpa Finance)</span> &rarr; <code>Metode Pembelian</code></div>
			<div class="flow-branches">
				<div class="branch-row"><span class="branch-tag">jika Direct Payment</span> &rarr; <code>Request Payment</code><span class="branch-note">(langsung, tanpa approval tambahan — tahap final)</span></div>
				<div class="branch-row"><span class="branch-tag">jika Kasbon</span> &rarr; <code>Kasbon — Finance</code> &rarr; <code>Kasbon — Direktur</code> &rarr; <code>Request Payment</code><span class="branch-note">(setelah 2 level kasbon approve — tahap final)</span></div>
			</div>
		</div>

		<!-- LEGEND -->
		<div class="legend">
			<span><span class="dot dot-pending"></span> Belum sampai tahap ini</span>
			<span><span class="dot dot-active"></span> Sedang berjalan / menunggu</span>
			<span><span class="dot dot-done"></span> Selesai tahap ini</span>
			<span><span class="dot dot-reject"></span> Ditolak</span>
		</div>

		<table id="example1" class="table table-bordered table-striped" width='100%'>
			<thead>
				<tr class="bg-blue">
					<th class="text-center" style="width: 35px;">#</th>
					<th class="text-center" style="width: 110px;">No. PR</th>
					<th class="text-center" style="width: 140px;">Kategori PR</th>
					<th class="text-center">Nama Barang</th>
					<th class="text-center" style="min-width: 90px;">Qty (Pack)</th>
					<th class="text-center" style="width: 110px;">Dibutuhkan</th>
					<th class="text-center" style="width: 110px;">Request By</th>
					<th class="text-center" style="width: 110px;">Request Date</th>
					<th class="text-center no-sort" style="min-width: 300px;">Progress PR</th>
					<th class="text-center no-sort" style="width: 110px;">Option</th>
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
						$this->db->select('a.stock_name, b.code');
						$this->db->from('accessories a');
						$this->db->join('ms_satuan b', 'b.id = a.id_unit_gudang', 'left');
						$this->db->where('a.id', $item->id_material);
						$get_stok_data = $this->db->get()->row();

						if (!empty($get_stok_data)) {
							$nm_detail = $nm_detail . '<div class="item-line">' . $get_stok_data->stock_name . '</div>';
							$qty_detail = $qty_detail . '<div class="qty-line">' . number_format($item->propose_purchase, 2) . ' ' . ucfirst($get_stok_data->code) . '</div>';
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
					echo '<td><span class="no-pr">' . strtoupper($row->no_pr) . '</span></td>';
					echo '<td><span class="kat">' . strtoupper($kategori_pr) . '</span></td>';
					echo '<td>' . $nm_detail . '</td>';
					echo '<td>' . $qty_detail . '</td>';
					echo '<td>' . date('d F Y', strtotime($row->tgl_dibutuhkan)) . '</td>';
					echo '<td class="text-center pic">' . ($row->request_by ?? '-') . '</td>';
					echo '<td class="text-center">' . $row->request_date . '</td>';
					echo '<td class="progress-cell">' . render_pr_progress_cell('stock', $row) . '</td>';

					$view  = "<a href='" . site_url($this->uri->segment(1)) . '/detail_planning/' . $row->so_number . "' class='opt-btn b-view' title='Detail PR' data-role='qtip' style='display:inline-flex;'><i class='fa fa-eye'></i></a>";
					$print = '<a href="' . site_url($this->uri->segment(1)) . '/PrintH2/' . $row->so_number . '" class="opt-btn b-print" title="Print PR" target="_blank" style="display:inline-flex;"><i class="fa fa-download"></i></a>';

					$close = '';
					if ($ENABLE_DELETE) {
						$close = '<button type="button" class="opt-btn b-del close_pr_modal" data-so_number="' . $row->so_number . '" title="Close PR" style="display:inline-flex;"><i class="fa fa-close"></i></button>';
					}

					echo '<td><div class="opts" style="justify-content:center;">' . $view . ' ' . $print . ' ' . $close . '</div></td>';
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
		var dataTable = $('#example1').DataTable({
			"bAutoWidth": false,
			"destroy": true,
			"order": [
				[7, "desc"]
			],
			"columnDefs": [{
				"targets": 'no-sort',
				"orderable": false,
			}]
		});
	}
</script>