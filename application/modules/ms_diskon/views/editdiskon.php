<?php
$ENABLE_ADD     = has_permission('Diskon.Add');
$ENABLE_MANAGE  = has_permission('Diskon.Manage');
$ENABLE_VIEW    = has_permission('Diskon.View');
$ENABLE_DELETE  = has_permission('Diskon.Delete');

foreach ($results['diskon'] as $diskon) {
}

?>
<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">
<div class="box box-primary">
	<!-- /.box-header -->
	<div class="box-body">
		<form id="data_form">
			<input type="hidden" name="id_diskon" id="id_diskon" value='<?= $diskon->id ?>'>
			<div class="row">
				<div class="col-md-12">
					<legend>Data Diskon</legend>
				</div>
				<div class="col-md-6">
					<div class="form-group row">
						<div class="col-md-3">
							<label for="inventory_1">Purpose Produk (LV I)</label>
						</div>
						<div class="col-md-9">
							<select id="level1" name="level1" class="form-control select" required>
								<option value="">-- Pilih --</option>
								<?php foreach ($results['lvl1'] as $lvl1) {
									$select = $diskon->id_type == $lvl1->id_type ? 'selected' : '';
								?>
									<option value="<?= $lvl1->id_type ?>" <?= $select ?>><?= $lvl1->nama ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-md-3">
							<label for="top">Term Of Payment</label>
						</div>
						<div class="col-md-9">
							<select id="top" name="top" class="form-control select" required>
								<option value="">-- Pilih --</option>
								<?php foreach ($results['lvl2'] as $lvl2) {
									$select = $diskon->id_top == $lvl2->id_top ? 'selected' : '';
								?>
									<option value="<?= $lvl2->id_top ?>" <?= $select ?>><?= $lvl2->nama_top ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="form-group row">
						<div class="col-md-3">
							<label for="">Nilai</label>
						</div>
						<div class="col-md-9">
							<input type="text" class="form-control" id="nilai" required name="nilai" placeholder="nilai" value="<?= $diskon->nilai_diskon ?>">
						</div>
					</div>

				</div>
			</div>
			<hr>
			<button type="submit" class="btn btn-primary" name="save" id="save"><i class="fa fa-save"></i> Save</button>
		</form>
	</div>
	<!-- /.box-body -->
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script type="text/javascript">
	$(function() {
		$('.select2').select2();
	});


	// ADD CUSTOMER 

	$(document).on('submit', '#data_form', function(e) {
		e.preventDefault()
		var data = $('#data_form').serialize();
		var id = $('#id_diskon').val();
		// alert(id);
		swal({
				title: "Anda Yakin?",
				text: "Data Inventory akan di simpan.",
				type: "warning",
				showCancelButton: true,
				confirmButtonClass: "btn-info",
				confirmButtonText: "Ya, Simpan!",
				cancelButtonText: "Batal",
				closeOnConfirm: false
			},
			function() {
				$.ajax({
					type: 'POST',
					url: siteurl + 'ms_diskon/saveEditDiskon',
					dataType: "json",
					data: data,
					success: function(result) {
						if (result.status == '1') {
							swal({
									title: "Sukses",
									text: "Data berhasil disimpan.",
									type: "success"
								},
								function() {
									window.location.reload(true);
								})
						} else {
							swal({
								title: "Error",
								text: "Data error. Gagal insert data",
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