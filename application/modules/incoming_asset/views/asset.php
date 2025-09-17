<?php
$ENABLE_ADD     = has_permission('Incoming_Asset.Add');
$ENABLE_MANAGE  = has_permission('Incoming_Asset.Manage');
$ENABLE_VIEW    = has_permission('Incoming_Asset.View');
$ENABLE_DELETE  = has_permission('Incoming_Asset.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.min.css">
<link href="vendor/select2/dist/css/select2.min.css" rel="stylesheet" />
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete='off'>
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
			<div class="box-tool pull-right">
			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body">
			<div class="box box-success">
				<div class="box-body">
					<br>
					<input type="hidden" id='tandax' name='tandax'>
					<div class='in_ipp'>
						<div class='form-group row'>
							<label class='label-control col-sm-2'><b>Nomor PO / NON-PO <span class='text-red'>*</span></b></label>
							<div class='col-sm-4'>
								<select id='no_ipp' name='no_ipp' class='form-control input-sm' style='min-width:200px;'>
									<option value='0'>Nomor PO / NON-PO</option>
									<?php
									foreach ($no_po as $val) {
										echo "<option value='" . $val->no_po . "'>" . strtoupper($val->ket . ' - ' . $val->no_po . ' - ' . $val->nm_supplier) . "</option>";
									}
									?>
								</select>
							</div>
							<label class='label-control col-sm-2'><b>PIC <span class='text-red'>*</span></b></label>
							<div class='col-sm-4'>
								<?php
								echo form_input(array('id' => 'pic', 'name' => 'pic', 'class' => 'form-control input-md', 'placeholder' => 'PIC'));
								?>
							</div>
						</div>
					</div>
					<div class='form-group row'>
						<label class='label-control col-sm-2'><b>Department <span class='text-red'>*</span></b></label>
						<div class='col-sm-4'>
							<select name='id_dept' id='id_dept' class='form-control input-md'>
								<option value='0'>Select An Department</option>
								<?php
								$dept = '';
								foreach ($list_department as $item_department) {
									// $dept = ($valx['id'] == $id_dept)?'selected':'';
									echo "<option value='" . $item_department->id . "'>" . strtoupper($item_department->nama) . "</option>";
								}
								?>
							</select>
						</div>
						<label class='label-control col-sm-2'><b>Note</b></label>
						<div class='col-sm-4'>
							<?php
							echo form_textarea(array('id' => 'note', 'name' => 'note', 'class' => 'form-control input-md', 'rows' => '2', 'cols' => '75', 'placeholder' => 'Note'));
							?>
						</div>
					</div>
					<div class='form-group row'>
						<label class='label-control col-sm-2'><b>Cost Center</b></label>
						<div class='col-sm-4'>
							<select name='id_costcenter' id='id_costcenter' class='form-control input-md'>
								<option value='0'>Select An Cost Center</option>
								<?php
								$cc = '';
								foreach ($list_costcenter as $item_costcenter) {
									//$cc = ($valx['id_costcenter'] == $id_costcenter)?'selected':'';
									echo "<option value='" . $item_costcenter->id_costcenter . "' " . $cc . ">" . strtoupper($item_costcenter->nama_costcenter) . "</option>";
								}
								?>
							</select>
						</div>
						<label class='label-control col-sm-2'><b>Date Transaksi</b></label>
						<div class='col-sm-4'>
							<input type="text" name="tanggal_trans" id="tanggal_trans" class='form-control input-sm' data-role="datepicker_lost" readonly value='<?= date('Y-m-d'); ?>'>
						</div>
					</div>

					<?php
					if ($ENABLE_ADD) {
						echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-success', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 0px;', 'value' => 'Process', 'content' => 'Process', 'id' => 'modalDetail')) . ' ';
					}
					?>
				</div>
			</div>

			<br><br>
			<table class="table table-bordered table-striped" id="my-grid" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center">#</th>
						<th class="text-center">No Trans</th>
						<th class="text-center">Incoming Date</th>
						<th class="text-center">No PO/Non-PO</th>
						<th class="text-center no-sort">Department</th>
						<th class="text-center no-sort">PIC</th>
						<th class="text-center no-sort">Note</th>
						<th class="text-center no-sort">Receiver</th>
						<th class="text-center no-sort">Incoming Date</th>
						<th class="text-center no-sort">Document</th>
						<th class="text-center no-sort">Detail</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->
	<!-- modal -->
	<div class="modal fade" id="ModalView2" style='overflow-y: auto;'>
		<div class="modal-dialog" style='width:90%; '>
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

<style>
	#tanggal_trans {
		cursor: pointer;
	}
</style>

<script src="https://cdn.datatables.net/2.1.5/js/dataTables.min.js"></script>
<script src="vendor/select2/dist/js/select2.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
	$(document).ready(function() {
		$('.maskM').autoNumeric('init');

		$('#id_dept').select2();
		$('#id_costcenter').select2();
		$('#no_ipp').select2();

		var no_po = $('#no_po').val();
		var gudang = $('#gudang').val();
		DataTables(no_po, gudang);

		$(document).on('change', '#gudang, #no_po', function(e) {
			e.preventDefault();
			var no_po = $('#no_po').val();
			var gudang = $('#gudang').val();
			DataTables(no_po, gudang);
		});
	});

	$(document).on('click', '.detailAjust', function(e) {
		e.preventDefault();

		$("#head_title2").html("<b>DETAIL INCOMING</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + 'modal_detail/' + $(this).data('kode_trans'),
			success: function(data) {
				$("#ModalView2").modal();
				$("#view2").html(data);

			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Timed Out ...',
					type: "warning",
					timer: 5000
				});
			}
		});
	});

	$(document).on('click', '#modalDetail', function(e) {
		e.preventDefault();
		var no_ipp = $('#no_ipp').val();
		var pic = $('#pic').val();
		var note = $('#note').val();
		var id_dept = $('#id_dept').val();
		var id_costcenter = $('#id_costcenter').val();
		var tanggal_trans = $('#tanggal_trans').val()

		if (no_ipp == '0') {
			swal({
				title: "Error Message!",
				text: 'PO Number Not Select, please input first ...',
				type: "warning"
			});
			$('#modalDetail').prop('disabled', false);
			return false;
		}

		if (pic == '') {
			swal({
				title: "Error Message!",
				text: 'PIC is empty, please input first ...',
				type: "warning"
			});
			$('#modalDetail').prop('disabled', false);
			return false;
		}

		$("#head_title2").html("<b>INCOMING ASSETS</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + 'modal_incoming_asset',
			data: {
				"id_dept": id_dept,
				"id_costcenter": id_costcenter,
				"no_po": no_ipp,
				"pic": pic,
				"note": note,
				'tanggal_trans': tanggal_trans
			},
			success: function(data) {
				$("#ModalView2").modal();
				$("#view2").html(data);

			},
			error: function() {
				swal({
					title: "Error Message !",
					text: 'Connection Timed Out ...',
					type: "warning",
					timer: 5000
				});
			}
		});
	});

	$(document).on('click', '#saveINMaterial', function() {

		swal({
				title: "Are you sure?",
				text: "You will not be able to process again this data!",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-danger",
				confirmButtonText: "Yes, Process it!",
				cancelButtonText: "No, cancel process!",
				closeOnConfirm: false,
				closeOnCancel: false
			},
			function(isConfirm) {
				if (isConfirm) {

					// var formData = $('#form_adjustment').serialize();
					var formData = new FormData($('#form_adjustment')[0]);
					$.ajax({
						url: base_url + active_controller + 'process_incoming_asset',
						type: "POST",
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						success: function(data) {
							if (data == 1) {
								swal({
									title: "Save Success!",
									text: "Save process success. Thanks ...",
									type: "success",
									timer: 7000
								});
								window.location.href = base_url + active_controller + 'asset';
							} else if (data == 0) {
								swal({
									title: "Save Failed!",
									text: "Save process failed. Please try again later ...",
									type: "warning",
									timer: 7000
								});
							}
						},
						error: function() {
							swal({
								title: "Error Message !",
								text: 'An Error Occured During Process. Please try again..',
								type: "warning",
								timer: 7000
							});
						}
					});
				} else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				}
			});
	});

	function DataTables(no_po = null, gudang = null) {
		var dataTable = $('#my-grid').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave": true,
			"bAutoWidth": true,
			"destroy": true,
			"responsive": true,
			"aaSorting": [
				[1, "asc"]
			],
			"columnDefs": [{
				"targets": 'no-sort',
				"orderable": false,
			}],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			"ajax": {
				url: base_url + active_controller + 'server_side_incoming_asset',
				type: "post",
				data: function(d) {
					d.no_po = no_po,
						d.gudang = gudang
				},
				cache: false,
				error: function() {
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display", "none");
				}
			}
		});
	}

	function DataTables2(gudang1 = null) {
		var dataTable = $('#my-grid2').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave": true,
			"bAutoWidth": true,
			"destroy": true,
			"responsive": true,
			"aaSorting": [
				[1, "asc"]
			],
			"columnDefs": [{
				"targets": 'no-sort',
				"orderable": false,
			}],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [
				[10, 20, 50, 100, 150],
				[10, 20, 50, 100, 150]
			],
			"ajax": {
				url: base_url + active_controller + 'server_side_move_gudang',
				type: "post",
				data: function(d) {
					d.gudang1 = gudang1
				},
				cache: false,
				error: function() {
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display", "none");
				}
			}
		});
	}
</script>