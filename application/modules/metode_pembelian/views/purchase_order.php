<?php
$this->load->view('include/side_menu');
?>
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete='off'>
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
			<div class="box-tool pull-right">
				<?php
				if ($akses_menu['create'] == '1') {
					echo form_button(array('type' => 'button', 'class' => 'btn btn-sm btn-success', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 0px;', 'value' => 'Add PO', 'content' => 'Add PO', 'id' => 'addPO')) . ' ';
				}
				?>
				<br><br>
				<select id='category' name='category' class='form-control input-sm' style='min-width:200px;'>
					<option value='0'>ALL CATEGORY</option>
					<option value='asset'>ASSET</option>
					<option value='rutin'>STOK</option>
					<option value='non rutin'>DEPARTEMEN</option>
				</select>

			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body">
			<table class="table table-bordered table-striped" id="my-grid" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center">#</th>
						<th class="text-center">No PO</th>
						<th class="text-center">No PR</th>
						<th class="text-center">Category</th>
						<th class="text-center">Suppier</th>
						<th class="text-center">Material Name</th>
						<th class="text-center">Qty</th>
						<th class="text-center">Total PO</th>
						<th class="text-center">By</th>
						<th class="text-center">Dated</th>
						<th class="text-center">Status</th>
						<th class="text-center no-sort">Option</th>
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
		<div class="modal-dialog" style='width:95%; '>
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

	<!-- modal -->
	<div class="modal fade" id="ModalView" style='overflow-y: auto;'>
		<div class="modal-dialog" style='width:90%; '>
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="head_title"></h4>
				</div>
				<div class="modal-body" id="view">
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
<?php $this->load->view('include/footer'); ?>
<script>
	$(document).ready(function() {
		$('.maskM').maskMoney();
		var category = $('#category').val();
		DataTables(category);

		$(document).on('change', '#category', function(e) {
			e.preventDefault();
			var category = $('#category').val();
			DataTables(category);
		});
	});

	$(document).on('click', '.detailMat', function(e) {
		e.preventDefault();
		loading_spinner();
		$("#head_title2").html("<b>DETAIL PURCHASE ORDER [" + $(this).data('no_po') + "]</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + '/modal_detail_purchase_order/' + $(this).data('no_po'),
			success: function(data) {
				$("#ModalView2").modal();
				$("#view2").html(data);

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

	$(document).on('click', '.edit_po', function(e) {
		e.preventDefault();
		loading_spinner();
		$("#head_title").html("<b>EDIT PURCHASE ORDER [" + $(this).data('no_po') + "]</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + '/modal_edit_purchase_order/' + $(this).data('no_po'),
			success: function(data) {
				$("#ModalView").modal();
				$("#view").html(data);

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
	$(document).on('click', '#edit_po', function(e) {
		e.preventDefault();

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
					loading_spinner();
					var formData = new FormData($('#form_proses_bro')[0]);
					var baseurl = base_url + active_controller + '/modal_edit_purchase_order';
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
									timer: 7000
								});
								window.location.href = base_url + active_controller + '/purchase_order';
							} else {
								swal({
									title: "Save Failed!",
									text: data.pesan,
									type: "warning",
									timer: 7000
								});
							}
							$('#edit_po').prop('disabled', false);
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
							$('#edit_po').prop('disabled', false);
						}
					});
				} else {
					swal("Cancelled", "Data can be process again :)", "error");
					$('#edit_po').prop('disabled', false);
					return false;
				}
			});
	});

	//NEW PO
	$(document).on('click', '#addPO', function(e) {
		e.preventDefault();
		loading_spinner();
		$("#head_title2").html("<b>ADD PURCHASE ORDER</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + '/modal_po',
			success: function(data) {
				$("#ModalView2").modal();
				$("#view2").html(data);
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

	$(document).on('click', '#savePO', function() {
		var id_supplier = $('#id_supplier').val();
		var category = $('#category2').val();
		var tanggal_dibutuhkan = $('#tanggal_dibutuhkan').val()

		if (id_supplier == '0') {
			swal({
				title: "Error Message!",
				text: 'Supplier Not Select, please input first ...',
				type: "warning"
			});
			$('#savePO').prop('disabled', false);
			return false;
		}

		if (category == '0') {
			swal({
				title: "Error Message!",
				text: 'Category Not Select, please input first ...',
				type: "warning"
			});
			$('#savePO').prop('disabled', false);
			return false;
		}

		if (tanggal_dibutuhkan == '') {
			swal({
				title: "Error Message!",
				text: 'Tanggal Dibutuhkan kosong, please input first ...',
				type: "warning"
			});
			$('#savePO').prop('disabled', false);
			return false;
		}

		if ($('input[type=checkbox]:checked').length == 0) {
			swal({
				title: "Error Message!",
				text: 'Checklist Minimal One Component',
				type: "warning"
			});
			$('#savePO').prop('disabled', false);
			return false;
		}

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
					loading_spinner();
					var formData = new FormData($('#form_proses_bro')[0]);
					$.ajax({
						url: base_url + active_controller + '/modal_po',
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
								window.location.href = base_url + active_controller + '/purchase_order';
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

	//TAMBAHAN
	$(document).on('click', '.edit_po_qty', function(e) {
		e.preventDefault();
		loading_spinner();
		$("#head_title2").html("<b>EDIT PURCHASE ORDER [" + $(this).data('no_po') + "]</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + '/edit_po_qty/' + $(this).data('no_po'),
			success: function(data) {
				$("#ModalView2").modal();
				$("#view2").html(data);

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
	//UPDATE QTY
	$(document).on('click', '#update_qty', function(e) {
		e.preventDefault();

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
					loading_spinner();
					var formData = new FormData($('#form_edit_po')[0]);
					var baseurl = base_url + active_controller + '/edit_po_qty';
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
									timer: 7000
								});
								window.location.href = base_url + active_controller + '/purchase_order';
							} else {
								swal({
									title: "Save Failed!",
									text: data.pesan,
									type: "warning",
									timer: 7000
								});
							}
							$('#update_qty').prop('disabled', false);
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
							$('#update_qty').prop('disabled', false);
						}
					});
				} else {
					swal("Cancelled", "Data can be process again :)", "error");
					$('#update_qty').prop('disabled', false);
					return false;
				}
			});
	});
	//DELETE SEBAGIAN PO
	$(document).on('click', '#delete_qty', function(e) {
		e.preventDefault();

		if ($('.check_qty:checked').length == 0) {
			swal({
				title: "Error Message!",
				text: 'Checklist Minimal Satu',
				type: "warning"
			});
			$('#delete_qty').prop('disabled', false);
			return false;
		}

		swal({
				title: "Are you sure?",
				text: "Barang yang di checklist akan menjadi RFQ baru ...",
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
					loading_spinner();
					var formData = new FormData($('#form_edit_po')[0]);
					$.ajax({
						url: base_url + active_controller + '/delete_sebagian_po',
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
								window.location.href = base_url + active_controller + '/purchase_order';
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
	//DELETE PO
	$(document).on('click', '.delete_po', function(e) {
		e.preventDefault();
		let nomor_po = $(this).data('no_po');

		swal({
				title: "Are you sure?",
				text: "Semua barang akan menjadi PR baru ...",
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
					loading_spinner();
					var baseurl = base_url + active_controller + '/delete_semua_po';
					$.ajax({
						url: baseurl,
						type: "POST",
						data: {
							'no_po': nomor_po
						},
						dataType: 'json',
						success: function(data) {
							if (data.status == 1) {
								swal({
									title: "Save Success!",
									text: data.pesan,
									type: "success",
									timer: 7000
								});
								window.location.href = base_url + active_controller + '/purchase_order';
							} else {
								swal({
									title: "Save Failed!",
									text: data.pesan,
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

	//DELETE SEBAGIA PO NEW
	$(document).on('click', '.delete_sebagian_po', function(e) {
		e.preventDefault();

		let id = $(this).data('id');
		let no_po = $('#no_po').val();

		swal({
				title: "Are you sure?",
				text: "Barang yang di hapus akan menjadi PR baru ...",
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
					loading_spinner();
					$.ajax({
						url: base_url + active_controller + '/delete_sebagian_po_new',
						type: "POST",
						data: {
							'id': id,
							'no_po': no_po
						},
						cache: false,
						dataType: 'json',
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
								window.location.href = base_url + active_controller + '/purchase_order';
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

	//REPEAT PO
	$(document).on('click', '.repeat_po', function(e) {
		e.preventDefault();
		let nomor_po = $(this).data('no_po');

		swal({
				title: "Are you sure?",
				text: "Repeat-PO " + nomor_po + " !!!",
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
					loading_spinner();
					var baseurl = base_url + active_controller + '/repeat_po_process';
					$.ajax({
						url: baseurl,
						type: "POST",
						data: {
							'no_po': nomor_po
						},
						dataType: 'json',
						success: function(data) {
							if (data.status == 1) {
								swal({
									title: "Save Success!",
									text: data.pesan,
									type: "success",
									timer: 5000
								});
								window.location.href = base_url + active_controller + '/purchase_order';
							} else {
								swal({
									title: "Save Failed!",
									text: data.pesan,
									type: "warning",
									timer: 5000
								});
							}
						},
						error: function() {

							swal({
								title: "Error Message !",
								text: 'An Error Occured During Process. Please try again..',
								type: "warning",
								timer: 5000
							});
						}
					});
				} else {
					swal("Cancelled", "Data can be process again :)", "error");
					return false;
				}
			});
	});

	function DataTables(category = null) {
		var dataTable = $('#my-grid').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave": true,
			"autoWidth": false,
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
				url: base_url + active_controller + '/server_side_purchase_order',
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

	function DataTables3(id_supplier = null, category = null) {
		var dataTable = $('#my-grid3').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave": true,
			"autoWidth": false,
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
				url: base_url + active_controller + '/server_side_list_rfq',
				type: "post",
				data: function(d) {
					d.id_supplier = id_supplier,
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

	$(document).on('click', '.request_payment', function(e) {
		e.preventDefault();
		loading_spinner();
		$("#head_title").html("<b>REQUEST PAYMENT [" + $(this).data('no_po') + "]</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + '/po_top/' + $(this).data('no_po'),
			success: function(data) {
				$("#ModalView").modal();
				$("#view").html(data);
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
	$(document).on('click', '.close_po', function(e) {
		e.preventDefault();
		loading_spinner();
		$("#head_title").html("<b>CLOSE PO [" + $(this).data('no_po') + "]</b>");
		$.ajax({
			type: 'POST',
			url: base_url + active_controller + '/close_po/' + $(this).data('no_po'),
			success: function(data) {
				$("#ModalView").modal();
				$("#view").html(data);
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
	$(document).on('click', '#save_term', function() {
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
					loading_spinner();
					var formData = new FormData($('#form_proses_bro')[0]);
					$.ajax({
						url: base_url + active_controller + '/save_po_top',
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
								window.location.href = base_url + active_controller + '/purchase_order';
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
</script>