<?php
    $ENABLE_ADD     = has_permission('Adjustment_Stock.Add');
    $ENABLE_MANAGE  = has_permission('Adjustment_Stock.Manage');
    $ENABLE_VIEW    = has_permission('Adjustment_Stock.View');
    $ENABLE_DELETE  = has_permission('Adjustment_Stock.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
	<div class="box-header">
		<span class="pull-right">
			<?php if($ENABLE_ADD) : ?>
                <a class="btn btn-success btn-sm" href="<?= base_url('adjustment_stock/add') ?>" title="Add"> <i class="fa fa-plus">&nbsp;</i>Add</a>
            <?php endif; ?>
            <!-- <a class="btn btn-info btn-sm" href="<?=base_url('adjustment_stock/download_excel');?>" target='_blank' title="Download"><i class="fa fa-excel">&nbsp;</i>Excel</a> -->
		</span>
		<div class="form-group row">
            <div class="col-md-2">
                <select name='type' id='type' class='form-control select2'>
                    <option value='0'>ALL ADJUSTMENT</option>
                    <option value='plus'>PLUS</option>
                    <option value='minus'>MINUS</option>
                    <!-- <option value='mutasi'>MUTASI</option> -->
                </select>
            </div>
			<div class="col-md-5">
				<select name="code_lv4" id="code_lv4" class='form-control select2'>
					<option value="0">ALL BARANG STOCK</option>
					<?php
					foreach ($material as $key => $value) {
						echo "<option value='".$value['id']."'>".strtoupper($value['stock_name'])."</option>";
					}
					?>
				</select>
			</div>
			
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
            <th class="text-center">#</th> 
            <th class="text-center">No Trans</th>
            <th class="text-center">Type</th>
            <!-- <th class="text-center">Gudang Dari</th>
            <th class="text-center">Gudang Ke</th> -->
            <th class="text-center">Nama Barang</th>
            <th class="text-center">Qty</th>
            <th class="text-center">PIC</th>
            <th class="text-center">No BA</th>
            <th class="text-center">Ket</th>
            <th class="text-center">By</th>
            <th class="text-center">Dated</th>
		</tr>
		</thead>

		<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style='width:70%; '>
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
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

	$(document).on('click', '.detail', function(){
		var id = $(this).data('id');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Accessories</b>");
		$.ajax({
			type:'POST',
			url:siteurl+ active_controller +'/detail/'+id,
			data:{'id':id},
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	// DELETE DATA
	$(document).on('click', '.delete', function(e){
		e.preventDefault()
		var id = $(this).data('id');
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data akan di hapus.",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Ya, Hapus!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+ active_controller+'/hapus',
			  dataType : "json",
			  data:{'id':id},
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses",
						  text : result.pesan,
						  type : "success"
						},
						function (){
							window.location.reload(true);
						})
				  } else {
					swal({
					  title : "Error",
					  text  : result.pesan,
					  type  : "error"
					})

				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Error Process !",
					  type  : "error"
					})
			  }
		  })
		});

	})

  	$(function() {
		var code_lv4 = $('#code_lv4').val()
		var type = $('#type').val()
		DataTables(type,code_lv4);

		$('.select2').select2({width: '100%'});

  	});

	$(document).on('change','#type, #code_lv4',function(){
		var code_lv4 = $('#code_lv4').val()
		var type = $('#type').val()
		DataTables(type,code_lv4);
	});


    function DataTables(type=null,code_lv4=null){
  		var dataTable = $('#example1').DataTable({
  			"processing" : true,
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
  				url : siteurl+active_controller+'/data_side_adjustment',
  				type: "post",
  				data: function(d){
  					d.type = type,
  					d.code_lv4 = code_lv4
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
