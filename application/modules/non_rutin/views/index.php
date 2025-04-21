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
			<div class="col-md-4">
				<select name="" id="" class="form-control form-control-sm search_depart" style="margin-top: 5px;">
					<option value="">- Department -</option>
					<?php
					foreach ($list_department as $item) {
						echo '<option value="' . $item->id . '">' . strtoupper($item->nama) . '</option>';
					}
					?>
				</select>
				<button type="button" class="btn btn-sm btn-primary search_btn" style=''><i class="fa fa-search"></i> Cari</button>
			</div>
			<div class="col-12 col_table">
				<table class="table table-bordered table-striped" id="my-grid" width='100%'>
					<thead>
						<tr class='bg-blue'>
							<th class="text-center">#</th>
							<th class="text-center">No PR</th>
							<th class="text-center">Departemen</th>
							<th class="text-center no-sort">Nama Barang/Jasa</th>
							<th class="text-center no-sort">Spec / Requirement</th>
							<th class="text-center no-sort" width='7%'>Qty</th>
							<th class="text-center no-sort">Dibutuhkan</th>
							<th class="text-center no-sort">Keterangan</th>
							<th class="text-center no-sort">Status</th>
							<th class="text-center no-sort" width='13%'>Option</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$no = 1;
						foreach ($result as $item) {
							echo '<tr>';
							echo '<td class="text-center">' . $no . '</td>';
							if (!empty($item->no_pr)) {
								echo '<td>' . $item->no_pr . '</td>';
							} else {
								echo '<td><span class="text-red">' . $item->no_pengajuan . '</span></td>';
							}
							echo '<td>' . strtoupper($item->nama) . '</td>';

							$list_barang    = $this->db->get_where('rutin_non_planning_detail', array('no_pengajuan' => $item->no_pengajuan))->result_array();
							$arr_nmbarang = array();
							$arr_spec = array();
							$arr_qty = array();
							$arr_tanggal = array();
							$arr_ket = array();
							foreach ($list_barang as $val => $valx) {
								$get_satuan = $this->db->get_where('ms_satuan', array('id' => $valx['satuan']))->result();
								$nm_satuan = (!empty($get_satuan)) ? strtolower($get_satuan[0]->code) : '';
								$arr_nmbarang[$val] = "&bull; " . strtoupper($valx['nm_barang']);
								$arr_spec[$val] = "&bull; " . strtoupper($valx['spec']);
								$arr_qty[$val] = "&bull; " . floatval($valx['qty']) . ' ' . $nm_satuan;
								$tgl_dibutuhkan = ($valx['tanggal'] <> '0000-00-00' and $valx['tanggal'] != NULL) ? date('d-M-Y', strtotime($valx['tanggal'])) : 'not set';
								$arr_tanggal[$val] = "&bull; " . $tgl_dibutuhkan;
								$arr_ket[$val] = "&bull; " . strtoupper($valx['keterangan']);
							}
							$dt_nama_barang    = implode("<br>", $arr_nmbarang);
							$dt_spec    = implode("<br>", $arr_spec);
							$dt_qty    = implode("<br>", $arr_qty);
							$dt_tanggal    = implode("<br>", $arr_tanggal);
							$dt_ket    = implode("<br>", $arr_ket);

							echo '<td>' . $dt_nama_barang . '</td>';
							echo '<td>' . $dt_spec . '</td>';
							echo '<td>' . $dt_qty . '</td>';
							echo '<td>' . $dt_tanggal . '</td>';
							echo '<td>' . $dt_ket . '</td>';

							$last_by     = (!empty($item->updated_by)) ? $item->updated_by : $item->created_by;
							$last_date = (!empty($item->updated_date)) ? $item->updated_date : $item->created_date;

							if ($item->sts_app == 'N') {
								$warna     = 'blue';
								$sts     = 'WAITING APPROVAL';
							} elseif ($item->sts_app == 'Y') {
								$warna     = 'green';
								$sts     = 'APPROVED';
							} else {
								$warna     = 'red';
								$sts     = 'REJECTED';
							}

							if (($item->sts_reject1 !== null || $item->sts_reject2 !== null || $item->sts_reject3 !== null) && $item->rejected == 1) {
								if ($item->sts_reject1 == "1") :
									$warna = "red";
									$sts = "Rejected By Head Department";
								elseif ($item->sts_reject2 == "1") :
									$warna = "red";
									$sts = "Rejected By Cost Control";
								elseif ($item->sts_reject3 == "1") :
									$warna = "red";
									$sts = "Rejected By Management";
								endif;
							} else {
								if ($item->app_1 == null && $item->app_2 == null && $item->app_3 == null) :
									$warna = "blue";
									$sts = "Waiting Approval Head Department";
								elseif ($item->app_1 !== null && $item->app_2 == null && $item->app_3 == null) :
									$warna = "blue";
									$sts = "Waiting Approval Cost Control";
								elseif ($item->app_1 !== null && $item->app_2 !== null && $item->app_3 == null) :
									$warna = "blue";
									$sts = "Waiting Approval Management";
								else :
									if ($item->sts_app == "Y") :
										$warna = "green";
										$sts = "Approved";
									else :
										$warna = "blue";
										$sts = "Waiting Approval Head Department";
									endif;
								endif;
							}

							echo '<td><span class="badge" style="background-color: ' . $warna . '">' . $sts . '</span></td>';

							$view        = "<a href='" . base_url('non_rutin/add/' . $item->no_pengajuan . '/view') . "' class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
							$edit        = "";
							$approve    = "";
							$cancel        = "";
							$print    = "&nbsp;<a href='" . base_url('non_rutin/print_pengajuan_non_rutin/' . $item->no_pengajuan) . "' target='_blank' class='btn btn-sm btn-success' title='Print'><i class='fa fa-print'></i></a>";

							if ($item->sts_app == 'N' || $item->sts_app == '') {
								$edit    = "&nbsp;<a href='" . base_url('non_rutin/add/' . $item->no_pengajuan) . "' class='btn btn-sm btn-primary' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></a>";
							}

							$close = '';
							if ($ENABLE_DELETE) {
								$close = '<button type="button" class="btn btn-sm btn-danger close_pr_modal" data-no_pengajuan="' . $item->no_pengajuan . '" title="Close PR"><i class="fa fa-close"></i></button>';
							}

							echo '<td>' . $view . ' ' . $edit . ' ' . $approve . ' ' . $cancel . ' ' . $print . ' ' . $close . '</td>';

							echo '</tr>';

							$no++;
						}
						?>
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

		var tanda = $('#tanda').val();
		DataTables(tanda);

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

	function DataTables(tanda = null, department = null) {
		var dataTable = $('#my-grid').DataTable();
	}
</script>