<?php
$this->load->view('include/side_menu');
// echo $tanda;
?>
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data" autocomplete='off'> 
<div class="box box-primary">
	<div class="box-header">
		<h3 class="box-title"><?php echo $title;?></h3>
		<div class="box-tool pull-right">
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
		<input type='hidden' id='tanda' value='<?=$tanda;?>'>
		<table class="table table-bordered table-striped" id="my-grid" width='100%'>
			<thead>
				<tr class='bg-blue'>
					<th class="text-center">#</th>
					<th class="text-center">No Non-PO</th>
					<th class="text-center">Category</th>
					<th class="text-center no-sort">Departemen</th> 
					<th class="text-center no-sort">Nama Barang/Jasa</th>
					<th class="text-center no-sort" width='7%'>Qty</th>
					<th class="text-center no-sort">Dibutuhkan</th>
					<th class="text-center no-sort">Status</th> 
					<th class="text-center no-sort" width='10%'>Option</th>
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
		<div class="modal-dialog"  style='width:80%; '>
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
<?php $this->load->view('include/footer'); ?>
<script>
	$(document).ready(function(){
		$('.maskM').maskMoney();
		
		var category = $('#category').val();
		var tanda = $('#tanda').val();
		DataTables(tanda, category);
		
		$(document).on('change','#category', function(e){
			e.preventDefault();
			var category = $('#category').val();
			var tanda = $('#tanda').val();
			DataTables(tanda, category);
		});
	});

	function DataTables(tanda = null, category = null){
		var dataTable = $('#my-grid').DataTable({
			"processing": true,
			"serverSide": true,
			"stateSave" : true,
			"bAutoWidth": true,
			"destroy": true,
			"responsive": true,
			"aaSorting": [[ 1, "asc" ]],
			"columnDefs": [ {
				"targets": 'no-sort',
				"orderable": false,
			}], 
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
			"ajax":{
				url : base_url + active_controller+'/server_side_app_non_po',
				type: "post",
				data: function(d){
					d.tanda = tanda,
					d.category = category
				},
				cache: false,
				error: function(){
					$(".my-grid-error").html("");
					$("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
					$("#my-grid_processing").css("display","none");
				}
			}
		});
	}

	
</script>
