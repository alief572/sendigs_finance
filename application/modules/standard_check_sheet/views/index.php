<?php
    $ENABLE_ADD     = has_permission('Product_Master.Add');
    $ENABLE_MANAGE  = has_permission('Product_Master.Manage');
    $ENABLE_VIEW    = has_permission('Product_Master.View');
    $ENABLE_DELETE  = has_permission('Product_Master.Delete');
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
				<a class="btn btn-primary btn-sm" href="<?=base_url('standard_check_sheet/add2');?>" title="Add"><i class="fa fa-plus">&nbsp;</i>Add</a>
				<!-- <a class="btn btn-info btn-sm" href="<?=base_url('standard_check_sheet/download_excel');?>" target='_blank' title="Download"><i class="fa fa-excel">&nbsp;</i>Excel</a> -->
			<?php endif; ?>
		</span>
		<div class="form-group row">
			<div class="col-md-2">
				<select name="level1" id="level1" class='form-control select2'>
					<option value="0">ALL PRODUCT TYPE</option>
					<?php
					foreach ($get_level_1 as $key => $value) {
						echo "<option value='".$value['code_lv1']."'>".strtoupper($value['nama'])."</option>";
					}
					?>
				</select>
			</div>
			<div class="col-md-2">
				<select name="level2" id="level2" class='form-control select2'>
					<option value="0">ALL PRODUCT CATEGORY</option>
					<?php
					foreach ($get_level_2 as $key => $value) {
						echo "<option value='".$value['code_lv2']."'>".strtoupper($value['nama'])."</option>";
					}
					?>
				</select>
			</div>
			<div class="col-md-4">
				<select name="level3" id="level3" class='form-control select2'>
					<option value="0">ALL PRODUCT JENIS</option>
					<?php
					foreach ($get_level_3 as $key => $value) {
						echo "<option value='".$value['code_lv3']."'>".strtoupper($value['nama'])."</option>";
					}
					?>
				</select>
			</div>
			<div class="col-md-4">

			</div>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
			<thead>
				<tr>
					<th>#</th>
					<th>Product Type</th>
					<th>Product Category</th>
					<th>Product Jenis</th>
					<th>Product Master</th>
					<!-- <th>Status</th> -->
					<th width='7%'>Action</th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

	$(document).on('click', '.delete', function(e){
		e.preventDefault()
		var id = $(this).data('id');
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data akan di hapus!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Yes",
		  cancelButtonText: "No",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+active_controller+'/delete',
			  dataType : "json",
			  data:{'id':id},
			  success:function(data){
				  if(data.status == '1'){
					 swal({
						  title: "Sukses",
						  text : data.pesan,
						  type : "success"
						},
						function (){
							window.location.reload(true);
						})
				  } else {
					swal({
					  title : "Error",
					  text  : data.pesan,
					  type  : "error"
					})

				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Error proccess !",
					  type  : "error"
					})
			  }
		  })
		});

	})

	$(document).on('change','#level1, #level2, #level3', function(){
		var level1 = $('#level1').val()
		var level2 = $('#level2').val()
		var level3 = $('#level3').val()
		DataTables(level1,level2,level3);
	})

  	$(function() {
		$('.select2').select2({width: '100%'});

		var level1 = $('#level1').val()
		var level2 = $('#level2').val()
		var level3 = $('#level3').val()
		DataTables(level1,level2,level3);
  	});

	function DataTables(level1,level2,level3){
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
				url : siteurl + active_controller +'/get_json_product_master',
				type: "post",
				data: {
					level1 : level1,
					level2 : level2,
					level3 : level3,
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

	function get_cub(){
		var l   = getNum($('#length').val().split(",").join(""));
		var w   = getNum($('#wide').val().split(",").join(""));
		var h   = getNum($('#high').val().split(",").join(""));
		var cub = (l * w * h) / 1000000000;

		$('#cub').val(cub.toFixed(7));
	}
</script>
