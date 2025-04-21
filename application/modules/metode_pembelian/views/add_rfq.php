<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.dataTables.min.css">
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete='off'>
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
		</div>
		<!-- /.box-header -->
		<div class="box-body">
			<br>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Metode Pembelian <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<select id='jenis_pembelian' name='jenis_pembelian' class='form-control input-sm '>
						<option value='0'>Select Type</option>
						<option value='po'>PO</option>
						<option value='non po'>NON PO</option>
					</select>
				</div>
			</div>
			<div class='form-group row'>
				<label class='label-control col-sm-2'><b>Category <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>
					<select id='category' name='category' class='form-control input-sm '>
						<option value='0'>Select Category</option>
						<option value='material'>MATERIAL</option>
						<option value='stok'>STOK</option>
						<option value='departemen'>DEPARTEMEN</option>
						<option value="asset">ASSET</option>
					</select>
				</div>
			</div>
			<!-- <div class='form-group row group-po'>		 	 
				<label class='label-control col-sm-2'><b>Supplier Name <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>              
					<select id='id_supplier' name='id_supplier[]' class='form-control input-sm chosen-select' multiple>
						
					</select>
				</div>
			</div>
			<div class='form-group row group-nonpo'>		 	 
				<label class='label-control col-sm-2'><b>PIC <span class='text-red'>*</span></b></label>
				<div class='col-sm-4'>              
					
				</div>
			</div>
			<div class='form-group row group-nonpo'>		 	 
				<label class='label-control col-sm-2'><b>Keterangan</b></label>
				<div class='col-sm-4'>              
					
				</div>
			</div> -->
			<?php
			echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-danger', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 0px;', 'content' => 'Back', 'id' => 'back')) . ' ';
			echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-success', 'style' => 'min-width:100px; float:right; margin: 5px 5px 5px 0px;', 'value' => 'Create PO', 'content' => 'Create', 'id' => 'save_rfq')) . ' ';

			?>
			<br><br>
			<div class="box box-success">
				<br>
				<table class="table table-bordered table-striped" id="my-grid3" width='100%'>
					<thead>
						<tr class='bg-blue'>
							<th class="text-center no-sort" width='4%'>#</th>
							<th class="text-center" width='7%'>No PR</th>
							<th class="text-center" width='10%'>Tanggal PR</th>
							<th class="text-center">Material Name</th>
							<th class="text-center" width='10%'>Category</th>
							<!-- <th class="text-center" width='7%'>Qty</th> -->
							<th class="text-center" width='10%'>Dibutuhkan</th>
							<th class="text-center" width='9%'>Request By</th>
							<th class="text-center" width='10%'>Request Date</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</form>
<style>
	.chosen-container-active .chosen-single {
		border: none;
		box-shadow: none;
	}

	.chosen-container-single .chosen-single {
		height: 34px;
		border: 1px solid #d2d6de;
		border-radius: 0px;
		background: none;
		box-shadow: none;
		color: #444;
		line-height: 32px;
	}

	.chosen-container-single .chosen-single div {
		top: 5px;
	}
</style>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
	$(document).ready(function() {
		$('.chosen-select').chosen();
		$('.group-po').hide();
		$('.group-nonpo').hide();


		var category = $('#category').val();
		DataTables3(category);

		$(document).on('change', '#category', function(e) {
			e.preventDefault();
			var category = $('#category').val();
			DataTables3(category);
		});

		$(document).on('change', '#jenis_pembelian', function(e) {
			e.preventDefault();
			var jenis_pembelian = $('#jenis_pembelian').val();
			if (jenis_pembelian == 'po') {
				$('.group-po').show();
				$('.group-nonpo').hide();
			} else {
				$('.group-po').hide();
				$('.group-nonpo').show();
			}
		});


	});

	$(document).on('click', '#back', function() {
		window.location.href = base_url + active_controller + 'pr';
	});

	$(document).on('click', '#save_rfq', function(e) {
		e.preventDefault();

		var jenis_pembelian = $('#jenis_pembelian').val();
		var category = $('#category').val();
		var id_supplier = $('#id_supplier').val();
		var pic = $('#pic').val();

		if (jenis_pembelian == '0') {
			swal({
				title: "Error Message!",
				text: 'Jenis Pembelian Not Select, please input first ...',
				type: "warning"
			});
			$('#save_rfq').prop('disabled', false);
			return false;
		}

		if (category == '0') {
			swal({
				title: "Error Message!",
				text: 'Category Not Select, please input first ...',
				type: "warning"
			});
			$('#save_rfq').prop('disabled', false);
			return false;
		}

		if (jenis_pembelian == 'po') {
			// if (id_supplier == null) {
			// 	swal({
			// 		title: "Error Message!",
			// 		text: 'Supplier Not Select, please input first ...',
			// 		type: "warning"
			// 	});
			// 	$('#save_rfq').prop('disabled', false);
			// 	return false;
			// }
		}

		if (jenis_pembelian == 'non po') {
			// if (pic == '' || pic == '-') {
			// 	swal({
			// 		title: "Error Message!",
			// 		text: 'PIC is empty, please input first ...',
			// 		type: "warning"
			// 	});
			// 	$('#save_rfq').prop('disabled', false);
			// 	return false;
			// }
		}

		if ($('input[type=checkbox]:checked').length == 0) {
			swal({
				title: "Error Message!",
				text: 'Checklist Minimal One Component',
				type: "warning"
			});
			$('#save_rfq').prop('disabled', false);
			return false;
		}

		// alert('Tahan');
		// return false;

		swal({
				title: "Are you sure?",
				text: "You will be able to process again this data!",
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
					// loading_spinner();
					var formData = new FormData($('#form_proses_bro')[0]);
					$.ajax({
						url: base_url + active_controller + '/save_rfq',
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
								window.location.href = base_url + active_controller + '/pr';
							} else if (data.status == 0) {
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

	$(document).on('click', '.changeCheckList', function() {
		let id = $(this).val();
		let flag
		if ($(this).is(':checked')) {
			console.log('check')
			flag = 1
		} else {
			console.log('uncheck')
			flag = 0
		}
		$.ajax({
			url: base_url + active_controller + '/save_checked_rfq',
			type: "POST",
			data: {
				"id": id,
				"flag": flag
			},
			cache: false,
			dataType: 'json',
			success: function(data) {
				console.log(data.pesan)
			},
			error: function() {
				console.log('error connection serve !')
			}
		});
	});



	function DataTables3(category = null) {
		var dataTable = $('#my-grid3').DataTable({
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
				url: base_url + active_controller + '/server_side_list_pr',
				type: "post",
				data: function(d) {
					d.category = category
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