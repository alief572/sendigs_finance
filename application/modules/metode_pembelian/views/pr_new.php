<?php 
$ENABLE_ADD     = has_permission('Metode_Pembelian.Add');
$ENABLE_MANAGE  = has_permission('Metode_Pembelian.Manage');
$ENABLE_VIEW    = has_permission('Metode_Pembelian.View');
$ENABLE_DELETE  = has_permission('Metode_Pembelian.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.5/css/dataTables.dataTables.min.css">
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
			<div class="box-tool pull-right">
				<?php
				if ($ENABLE_ADD) {
				?>
					<a href="<?php echo site_url('metode_pembelian/add_rfq') ?>" class="btn btn-sm btn-success" style='float:right;' id='btn-add'>
						<i class="fa fa-plus"></i> &nbsp;&nbsp;Add
					</a>
				<?php
				}
				?><br><br>
				<select id='category' name='category' class='form-control input-sm' style='min-width:200px;'>
					<option value='0'>All Category</option>
					<option value='material'>Material</option>
					<option value='stok'>Stok</option>
					<option value='departemen'>Departemen</option>
				</select>
			</div>
		</div>
		<!-- /.box-header -->
		<div class="box-body">
			<table class="table table-bordered table-striped" id="my-grid" width='100%'>
				<thead>
					<tr class='bg-blue'>
						<th class="text-center">#</th>
						<th class="text-center">No PR</th>
						<th class="text-center">Tgl PR</th>
						<th class="text-center">Departemen</th>
						<th class="text-center">Category</th>
						<th class="text-center">By</th>
						<th class="text-center">Date</th>
						<th class="text-center">#</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->
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
</form>

<script src="https://cdn.datatables.net/2.0.5/js/dataTables.min.js"></script>

<script>
	$(document).ready(function() {
		var category = $('#category').val();
		DataTables(category);

		$(document).on('change', '#category', function(e) {
			e.preventDefault();
			var category = $('#category').val();
			DataTables(category);
		});

		$(document).on('click', '.detail_pr', function(e) {
			e.preventDefault();
			// loading_spinner();
			$("#head_title2").html("<b>DETAIL</b>");
			$.ajax({
				type: 'POST',
				url: base_url + active_controller + '/modal_detail_pr/',
				data : {
					'no_pr_group': $(this).data('no_pr_group'),
					'tipe_pr': $(this).data('tipe_pr')
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
	});

	function DataTables(category = null) {
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
			"aaSorting": [
				[2, "desc"]
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
				url: base_url + active_controller + '/server_side_progress_pr',
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