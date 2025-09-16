
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<form action="#" method="POST" id="form_proses" enctype="multipart/form-data"> 
<div class="box">
	<div class="box-header">
		<!-- <a class="btn btn-primary btn-sm" style='float:right;' href="<?= base_url('plan_mixing/reprint_spk') ?>" title="Re-Print SPK">Re-Print SPK</a> -->
    	<!-- <br> -->
      	<div class="form-group row" hidden>
			<div class="col-md-9"></div>
			<div class="col-md-3">
				<select name='sales_order' id='sales_order' class='form-control input-sm chosen-select'>
					<option value='0'>All Sales Order</option>
				</select>
			</div>
      	</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="tab-pane active" id="nonmixing" role="tabpanel" aria-labelledby="nonmixing-tab"><br>
			<table id="example1" class="table table-bordered table-striped" width='100%'>
				<thead>
					<tr>
						<th class='text-center'>#</th>
						<th class='text-center'>No Trans</th>
						<th class='text-center'>No SO</th>
						<th class='text-center'>No SPK</th>
						<th class='text-center'>Gudang Dari</th>
						<th class='text-center'>Costcenter</th>
						<th class='text-center'>Berat (Kg)</th>
						<th class='text-center'>By</th>
						<th class='text-center'>Dated</th>
						<th class='text-center no-sort'>Status</th>
						<th class='text-center no-sort'>Option</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>

<!-- modal -->
<div class="modal fade" id="ModalView2" style='overflow-y: auto;'>
	<div class="modal-dialog"  style='width:90%; '>
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

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

	$(document).ready(function(){
		var sales_order     = $("#sales_order").val();
		DataTables(sales_order);

		$(document).on('change','#sales_order', function(){
			var sales_order     = $("#sales_order").val();
			DataTables(sales_order);
		});
	});

	$(document).on('keyup', '.changeQty', function(e){
		e.preventDefault();

		let id 			= $(this).data('id')
		let qty_sisa 	= getNum($('#sisa_'+id).text().split(",").join(""))
		let qty 		= getNum($(this).val().split(",").join(""))

		if(qty > qty_sisa){
			$(this).val(qty_sisa)
		}

	});

    function DataTables(sales_order = null){
  		var dataTable = $('#example1').DataTable({
  			"processing" : true,
  			"serverSide": true,
  			"stateSave" : true,
  			"autoWidth": false,
  			"destroy": true,
			"searching": true,
  			"responsive": true,
  			"aaSorting": [[ 1, "desc" ]],
  			"columnDefs": [ {
  				"targets": 'no-sort',
  				"orderable": false,
  			}],
  			"sPaginationType": "simple_numbers",
  			"iDisplayLength": 10,
  			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
  			"ajax":{
  				url : siteurl+active_controller+'data_side_spk_material',
  				type: "post",
          	data: function(d){
           	 	d.sales_order = sales_order
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
