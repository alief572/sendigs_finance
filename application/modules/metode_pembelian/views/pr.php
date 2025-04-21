<?php
$this->load->view('include/side_menu');
?>
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
	<div class="box box-primary">
		<div class="box-header">
			<h3 class="box-title"><?php echo $title; ?></h3>
			<div class="box-tool pull-right">
				<?php
				if ($akses_menu['create'] == '1') {
				?>
					<a href="<?php echo site_url('pembelian/add_rfq') ?>" class="btn btn-sm btn-success" style='float:right;' id='btn-add'>
						<i class="fa fa-plus"></i> &nbsp;&nbsp;Add
					</a>
				<?php
				}
				?><br><br>
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
						<th class="text-center" width='5%'>#</th>
						<th class="text-center" width='9%'>No PR</th>
						<th class="text-center" width='9%'>Tgl PR</th>
						<th class="text-center" width='13%'>Departemen</th>
						<th class="text-center" width='10%'>Category</th>
						<th class="text-center">Nama Barang</th>
						<th class="text-center" width='13%'>Status</th>
						<th class="text-center" width='6%'>#</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
		<!-- /.box-body -->
	</div>
	<!-- /.box -->
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
</form>
<?php $this->load->view('include/footer'); ?>
<script>
	$(document).ready(function() {
		var category = $('#category').val();
		DataTables(category);

		$(document).on('change', '#category', function(e) {
			e.preventDefault();
			var category = $('#category').val();
			DataTables(category);
		});

		$(document).on('click', '.look_hide', function() {
			var idOfParent = $(this).parents('tr').attr('id');
			$('.child-' + idOfParent).toggle('slow');
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