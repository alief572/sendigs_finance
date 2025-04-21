<?php
$ENABLE_ADD     = has_permission('Master_Department.Add');
$ENABLE_MANAGE  = has_permission('Master_Department.Manage');
$ENABLE_VIEW    = has_permission('Master_Department.View');
$ENABLE_DELETE  = has_permission('Master_Department.Delete');
?>
<form action="#" method="POST" id="form_proses_bro">
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?= $title; ?></h3>
		</div>
		<!-- /.box-header -->
		<div class="box-body">
			<div class='form-group row'>
				<label class='label-control col-sm-2' readonly><b>Departement Code <span class='text-red'>*</span></b> </label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						echo form_input(array('readonly' => 'readonly', 'id' => 'id', 'name' => 'id', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Departement Code'), $row[0]->id);
						?>
					</div>

				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Companies Name<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-user"></i></span>
						<?php
						$data_companies[0]	= 'Select An Option';
						echo form_dropdown('company_id', $data_companies, $row[0]->company_id, array('id' => 'company_id', 'class' => 'form-control input-sm'));
						?>
					</div>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Divisions Name<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-user"></i></span>
						<?php
						$data_companies[0]	= 'Select An Option';
						echo form_dropdown('division_id', $data_divisions, $row[0]->division_id, array('id' => 'division_id', 'class' => 'form-control input-sm'));
						?>
					</div>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Departement Name<span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<div class="input-group">
						<span class="input-group-addon"><i class="fa fa-file"></i></span>
						<?php
						echo form_input(array('id' => 'name', 'name' => 'name', 'class' => 'form-control input-sm', 'autocomplete' => 'off', 'placeholder' => 'Departement Name'), $row[0]->name);
						?>
					</div>

				</div>
			</div>

		</div>
		<div class='box-footer'>
			<a href="<?= base_url('departements') ?>" class="btn btn-md btn-danger">Back</a>
			
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->
</form>
<script>
	$(document).ready(function() {
		$('select').attr('disabled', true);
		$('input').attr('disabled', true);
		$('#simpan-bro').click(function() {
			var nama = $('#name').val();
			var company = $('#company_id').val();
			var divisi = $('#division_id').val();
			if (nama == '' || nama == null) {
				swal({
					title: "Error Message!",
					text: 'Empty Departement name, please input Departement name first.....',
					type: "warning"
				});

				return false;

			}

			if (company == '' || company == null || company == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty Company name, please input Company name first.....',
					type: "warning"
				});

				return false;
			}
			if (divisi == '' || divisi == null || divisi == '0') {
				swal({
					title: "Error Message!",
					text: 'Empty Division name, please input Division name first.....',
					type: "warning"
				});

				return false;
			}
			swal({
					title: "Are you sure?",
					text: "You will not be able to process again this data!",
					type: "warning",
					showCancelButton: true,
					confirmButtonClass: "btn-danger",
					confirmButtonText: "Yes, Process it!",
					cancelButtonText: "No, cancel process!",
					closeOnConfirm: true,
					closeOnCancel: false
				},
				function(isConfirm) {
					if (isConfirm) {
						loading_spinner();
						var formData = new FormData($('#form_proses_bro')[0]);
						var baseurl = base_url + active_controller + '/edit';
						$.ajax({
							url: baseurl,
							type: "POST",
							data: formData,
							cache: false,
							dataType: 'json',
							processData: false,
							contentType: false,
							success: function(data) {
								if (data.status == 1) {
									swal({
										title: "Save Success!",
										text: data.pesan,
										type: "success",
										timer: 7000,
										showCancelButton: false,
										showConfirmButton: false,
										allowOutsideClick: false
									});
									window.location.href = base_url + active_controller;
								} else {

									if (data.status == 2) {
										swal({
											title: "Save Failed!",
											text: data.pesan,
											type: "warning",
											timer: 7000,
											showCancelButton: false,
											showConfirmButton: false,
											allowOutsideClick: false
										});
									} else {
										swal({
											title: "Save Failed!",
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
		});
		$('#company_id').change(function() {
			var comp = $('#company_id').val();
			if (comp == '' || comp == '0' || comp == null) {
				var Template = '<option value="">Empty List</option>';
				$('#division_id').html(Template).trigger('chosen:updated');
			} else {
				var baseurl = base_url + active_controller + '/getDetail/' + comp;
				$.ajax({
					url: baseurl,
					type: "GET",
					success: function(data) {
						var datas = $.parseJSON(data);
						if ($.isEmptyObject(datas) == true) {
							var Template = '<option value="">Empty List</option>';
						} else {
							var Template = '<option value="">Select An Option</option>';
							$.each(datas, function(kode, nilai) {
								Template += '<option value="' + kode + '">' + nilai + '</option>';
							});
						}
						$('#division_id').html(Template).trigger('chosen:updated');
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
			}

		});
	});
</script>