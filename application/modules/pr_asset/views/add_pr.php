<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
			<div class="box-tool pull-right">

			</div>
		</div>
		<div class="box-body">
			<table class="table table-bordered table-striped" id="my-grid" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center" width='5%'>#</th>
						<th class="text-center">Nama Barang</th>
						<th class="text-center" width='15%'>Department</th>
						<th class="text-center" width='15%'>Costcenter</th>
						<th class="text-center" width='8%'>Qty</th>
						<th class="text-center" width='13%'>Created By</th>
						<th class="text-center" width='13%'>Created Date</th>
						<th class="text-center no-sort" width='7%'>Option</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
			<?php
			echo form_button(array('type' => 'button', 'class' => 'btn btn-md btn-danger', 'style' => 'min-width:100px; float:right; margin: 5px 0px 5px 0px;', 'content' => 'Back', 'id' => 'back')) . ' ';
			?>
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
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
	$(document).ready(function() {
		DataTables();

		$('.maskM').autoNumeric('init');

		$(document).on('click', '.look_hide', function() {
			var idOfParent = $(this).data('id');
			$('.child-' + idOfParent).toggle('slow');
		});	

		$(document).on('click', '#back', function(e) {
			window.location.href = base_url + active_controller + 'pr';
		});

		$(document).on('click', '.add_pr', function() {
			var nomor = $(this).data('id');
			var qty_rev = $('#qty_rev_' + nomor).val().split(",").join("");
			var nil_pr = $('#nil_pr_' + nomor).val().split(",").join("");
			var tgl_butuh = $('#tgl_butuh_' + nomor).val();
			var code_plan = $('#code_plan_' + nomor).val();

			if (qty_rev == '' || qty_rev == '0') {
				swal({
					title: "Error Message!",
					text: 'Qty is empty, please input first ...',
					type: "warning"
				});
				return false;
			}

			if (nil_pr == '' || nil_pr == '0') {
				swal({
					title: "Error Message!",
					text: 'Nilai PR is empty, please input first ...',
					type: "warning"
				});
				return false;
			}

			if (tgl_butuh == '') {
				swal({
					title: "Error Message!",
					text: 'Date digunakan is empty, please input first ...',
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
					closeOnConfirm: false,
					closeOnCancel: false
				},
				function(isConfirm) {
					if (isConfirm) {
						
						$.ajax({
							url: base_url + active_controller + 'add_pr',
							type: "POST",
							data: {
								"code_plan": code_plan,
								"qty_rev": qty_rev,
								"nil_pr": nil_pr,
								"tgl_butuh": tgl_butuh
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
									window.location.href = base_url + active_controller + 'add_pr';
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

	});

	function DataTables() {
		var dataTable = $('#my-grid').DataTable({
			"serverSide": true,
			"stateSave": true,
			"bAutoWidth": true,
			"destroy": true,
			"processing": true,
			"responsive": true,
			"fixedHeader": {
				"header": true,
				"footer": true
			},
			"oLanguage": {
				"sSearch": "<b>Live Search : </b>",
				"sLengthMenu": "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
				"sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
				"sInfoFiltered": "(filtered from _MAX_ total entries)",
				"sZeroRecords": "No matching records found",
				"sEmptyTable": "No data available in table",
				"sLoadingRecords": "Please wait - loading...",
				"oPaginate": {
					"sPrevious": "Prev",
					"sNext": "Next"
				}
			},
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
				url: base_url + active_controller + 'server_side_add_pr_asset',
				type: "post",
				// data: function(d){
				// d.tanda = tanda
				// },
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