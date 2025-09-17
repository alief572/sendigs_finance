<style type="text/css">
	thead input {
		width: 100%;
	}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
	<div class="box-header">
		<!-- <a class="btn btn-primary btn-sm" style='float:right;' href="<?= base_url('plan_mixing/reprint_spk') ?>" title="Re-Print SPK">Re-Print SPK</a> -->
		<br>
		<div class="form-group row" hidden>
			<div class="col-md-10"></div>
			<div class="col-md-2">
				<select name='sales_order' id='sales_order' class='form-control input-sm chosen-select'>
					<option value='0'>All Sales Order</option>
					<?php
					foreach ($listSO as $key => $value) {
						echo "<option value='" . $value['no_so'] . "'>" . $value['no_so'] . "</option>";
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
    			<th class='text-center'>#</th>
    			<th class='text-center'>SPK Delivery</th>
    			<th class='text-center'>Surat Jalan</th>
    			<th class='text-center'>Tgl. SPK</th>
    			<th class='text-center'>Nama Customer</th>
				<th class='text-center'>Project</th>
				<th class='text-center'>Created</th>
				<th class='text-center'>Dated</th>
				<th class='text-center no-sort'>Status</th>
				<th class='text-center no-sort'>Reason</th>
				<!-- <th class='text-center no-sort'>Option</th> -->
    		</tr>
  		</thead>
  		<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" style='width:90%; '>
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
			</div>
			<div class="modal-body" id="ModalView">
				...
			</div>
		</div>
	</div>

	<!-- DataTables -->
	<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
	<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
	<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>

	<!-- page script -->
	<script type="text/javascript">
		$(document).ready(function() {
			var sales_order = $("#sales_order").val();
			DataTables(sales_order);

			$(document).on('change', '#sales_order', function() {
				var sales_order = $("#sales_order").val();
				DataTables(sales_order);
			});
		});

		$(document).on('keyup', '.changeQty', function(e) {
			e.preventDefault();

			let id = $(this).data('id')
			let qty_sisa = getNum($('#sisa_' + id).text().split(",").join(""))
			let qty = getNum($(this).val().split(",").join(""))

			if (qty > qty_sisa) {
				$(this).val(qty_sisa)
			}

		});

		$(document).on('click', '.release', function(e) {
			e.preventDefault();

			let no_delivery = $(this).data('id')

			swal({
					title: "Are you sure?",
					text: "Ready To Deliver !",
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
						var baseurl = base_url + active_controller + '/deliver_to_customer';
						$.ajax({
							url: baseurl,
							type: "POST",
							data: {
								no_delivery: no_delivery
							},
							cache: false,
							dataType: 'json',
							success: function(data) {
								window.location.href = base_url + active_controller
							},
							error: function() {
								swal({
									title: "Error Message !",
									text: 'An Error Occured During Process. Please try again..',
									type: "warning",
									timer: 3000
								});
							}
						});
					} else {
						swal("Cancelled", "Data can be process again :)", "error");
						return false;
					}
				});
		});

		function DataTables(sales_order = null) {
			var dataTable = $('#example1').DataTable({
				"processing": true,
				"serverSide": true,
				"stateSave": true,
				"autoWidth": false,
				"destroy": true,
				"searching": true,
				"responsive": true,
				"aaSorting": [
					[1, "desc"]
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
					url: siteurl + active_controller + 'data_side_spk_material',
					type: "post",
					data: function(d) {
						d.sales_order = sales_order
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