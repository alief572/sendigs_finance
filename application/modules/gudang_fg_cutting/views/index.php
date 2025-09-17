<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
	<div class="box-header">
      
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="form-group row">
        <div class="col-md-1">
          <b>Product Type</b>
        </div>
        <div class="col-md-3">
          <select name='product' id='product' class='form-control input-sm chosen-select'>
  					<option value='0'>All Product Type</option>
  					<?php
  					foreach(get_list_inventory_lv1('product') AS $val => $valx){
						if($valx['code_lv1'] != 'P123000009'){
  							echo "<option value='".$valx['code_lv1']."'>".strtoupper($valx['nama'])."</option>";
						}
  					}
  					?>
  				</select>
        </div>
      </div>
      <div class="form-group row" hidden>
        <div class="col-md-1">
          <b>Costcenter</b>
        </div>
        <div class="col-md-3">
          <select name='costcenter' id='costcenter' class='form-control input-sm chosen-select'>
  					<option value='0'>All Costcenter</option>
  					<?php
  					foreach(get_costcenter() AS $val => $valx){
  						echo "<option value='".$valx['id_costcenter']."'>".strtoupper($valx['nama_costcenter'])."</option>";
  					}
  					?>
  				</select>
        </div>
      </div>
		<table id="example1" class="table table-bordered table-striped" width='100%'>
  		<thead>
    		<tr>
    			<th class='text-center'>#</th>
    			<th class='text-center'>Product</th>
				<th class='text-center'>Stock FG</th>
				<th class='text-center'>Ukuran</th>
    			<th class='text-center'>No SO</th>
				<th class='text-center'>No SPK</th>
    			<th class='text-center'>Customer</th>
    			<th class='text-center'>Project</th>
    			
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
        <h4 class="modal-title" id="myModalLabel">Quality Check Product</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
  </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

  $(document).ready(function(){
    var product     = $("#product").val();
    var costcenter  = $("#costcenter").val();
    DataTables(costcenter, product);

    $(document).on('change','#costcenter', function(){
      var costcenter  = $("#costcenter").val();
      var product     = $("#product").val();
      DataTables(costcenter, product);
    });

    $(document).on('change','#product', function(){
      var costcenter  = $("#costcenter").val();
      var product     = $("#product").val();
      DataTables(costcenter, product);
    });
  });

  $(document).on('click', '.detail', function(e){
	var id = $(this).data('id');
	var id_cut_plan = $(this).data('id_cut_plan');
	$("#head_title").html("<b>Process QC</b>");
	$.ajax({
		type:'POST',
		url:siteurl+active_controller+'/qc/'+id+'/'+id_cut_plan,
		success:function(data){
			$("#dialog-popup").modal();
			$("#ModalView").html(data);

		}
	})
});

$(document).on('click', '#cetakQR', function(e) {
	e.preventDefault();
	var idmilik = [];
	let size = $('input[name="size"]:checked').val()

	$('.chk_personal').each(function(i, obj) {
		if (this.checked) {
			idmilik.push($(this).val());
		}
	});
	console.log(idmilik);
	// '/print_qrcode/' + idmilik + "/" + logo + "/" + size;
	// console.log(idmilik.length);
	if (idmilik.length > 0) {
		idmilik = idmilik.join("-")
		var Links = base_url + active_controller + 'print_qrcode/' + idmilik + "/" + size;
		window.open(Links, '_blank');
	} else {
		swal({
			title: "Warning!",
			text: "Mohon pilih produk terlebih dahulu!",
			type: "warning",
			timer: 5000
		});
	}
});


function DataTables(costcenter = null, product = null){
	var dataTable = $('#example1').DataTable({
		// "scrollX": true,
		// "scrollCollapse" : true,
		// "scrollY": 500,
		"processing" : true,
		"serverSide": true,
		"stateSave" : true,
		"fixedHeader": true,
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
			url : siteurl+active_controller+'data_side_outstanding_qc',
			type: "post",
		data: function(d){
		d.costcenter = costcenter,
		d.product = product
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
