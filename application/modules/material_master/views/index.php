<?php
    $ENABLE_ADD     = has_permission('Material_Master.Add');
    $ENABLE_MANAGE  = has_permission('Material_Master.Manage');
    $ENABLE_VIEW    = has_permission('Material_Master.View');
    $ENABLE_DELETE  = has_permission('Material_Master.Delete');
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
				<a class="btn btn-success btn-sm add" href="javascript:void(0)" title="Add"><i class="fa fa-plus">&nbsp;</i>Add</a>
				<a class="btn btn-info btn-sm" href="<?=base_url('material_master/download_excel');?>" target='_blank' title="Download"><i class="fa fa-excel">&nbsp;</i>Excel</a>
			<?php endif; ?>
		</span>
		<div class="form-group row">
			<div class="col-md-2">
				<select name="level1" id="level1" class='form-control select2'>
					<option value="0">ALL MATERIAL TYPE</option>
					<?php
					foreach ($get_level_1 as $key => $value) {
						echo "<option value='".$value['code_lv1']."'>".strtoupper($value['nama'])."</option>";
					}
					?>
				</select>
			</div>
			<div class="col-md-2">
				<select name="level2" id="level2" class='form-control select2'>
					<option value="0">ALL MATERIAL CATEGORY</option>
					<?php
					foreach ($get_level_2 as $key => $value) {
						echo "<option value='".$value['code_lv2']."'>".strtoupper($value['nama'])."</option>";
					}
					?>
				</select>
			</div>
			<div class="col-md-4">
				<select name="level3" id="level3" class='form-control select2'>
					<option value="0">ALL MATERIAL JENIS</option>
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
			<th>Material Type</th>
			<th>Material Category</th>
			<th>Material Jenis</th>
			<th>Material Master</th>
			<th>Status</th>
			<th width='7%'>Action</th>
		</tr>
		</thead>

		<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->

<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="head_title">Default</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
  </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

	$(document).on('click', '.edit', function(e){
		var id = $(this).data('id');
		$("#head_title").html("<b>Material Master</b>");
		$.ajax({
			type:'POST',
			url:siteurl+active_controller+'/add/'+id,
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('click', '.add', function(){
		$("#head_title").html("<b>Material Master</b>");
		$.ajax({
			type:'POST',
			url:siteurl+active_controller+'/add/',
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('change','#code_lv1',function(){
		var code_lv1 = $("#code_lv1").val();

		$.ajax({
			url:siteurl+active_controller+'/get_list_level1',
			method : "POST",
			data : {code_lv1:code_lv1},
			dataType : 'json',
			success: function(data){
				$('#code_lv2').html(data.option);
				$('#code_lv3').html("<option value='0'>List Empty</option>");
			}
		});
	});

	$(document).on('change','#code_lv2',function(){
		var code_lv1 = $("#code_lv1").val();
		var code_lv2 = $("#code_lv2").val();

		$.ajax({
			url:siteurl+active_controller+'/get_list_level3',
			method : "POST",
			data : {code_lv1:code_lv1,code_lv2:code_lv2},
			dataType : 'json',
			success: function(data){
				$('#code_lv3').html(data.option);
			}
		});
	});

	$(document).on('change','#code_lv3',function(){
		var code_lv1 = $("#code_lv1").val();
		var code_lv2 = $("#code_lv2").val();
		var code_lv3 = $("#code_lv3").val();

		$.ajax({
			url:siteurl+active_controller+'/get_list_level4_name',
			method : "POST",
			data : {code_lv1:code_lv1,code_lv2:code_lv2,code_lv3:code_lv3},
			dataType : 'json',
			success: function(data){
				$('#nama').val(data.nama);
			}
		});
	});

	$(document).on('keyup','.getCub',function(){
		get_cub();
	});
	

	$(document).on('submit', '#data_form', function(e){
		e.preventDefault()
		
		var code_lv1 	= $('#code_lv1').val();
		var code_lv2 	= $('#code_lv2').val();
		var code_lv3 	= $('#code_lv3').val();
		var nama 		= $('#nama').val();

		var id_unit_packing = $('#id_unit_packing').val();
		var konversi 		= $('#konversi').val();
		var id_unit 		= $('#id_unit').val();
		var max_stok		= $('#max_stok').val();
		var min_stok 		= $('#min_stok').val();

		if(code_lv1 == '0' ){
			swal({title	: "Error Message!",text	: 'Material type not selected...',type	: "warning"
			});
			return false;
		}
		if(code_lv2 == '0' ){
			swal({title	: "Error Message!",text	: 'Material category not selected...',type	: "warning"
			});
			return false;
		}
		if(code_lv3 == '0' ){
			swal({title	: "Error Message!",text	: 'Material jenis not selected...',type	: "warning"
			});
			return false;
		}
		if(nama == '' ){
			swal({title	: "Error Message!",text	: 'Material Master is empty ...',type	: "warning"
			});
			return false;
		}

		if(id_unit_packing == '0' ){
			swal({title	: "Error Message!",text	: 'Packing Unit is empty ...',type	: "warning"
			});
			return false;
		}

		if(konversi == '' ){
			swal({title	: "Error Message!",text	: 'Conversion is empty ...',type	: "warning"
			});
			return false;
		}

		if(id_unit == '0' ){
			swal({title	: "Error Message!",text	: 'Unit Measurement is empty ...',type	: "warning"
			});
			return false;
		}

		if(max_stok == '' ){
			swal({title	: "Error Message!",text	: 'Maximum stok is empty ...',type	: "warning"
			});
			return false;
		}

		if(min_stok == '' ){
			swal({title	: "Error Message!",text	: 'Minimun stok is empty ...',type	: "warning"
			});
			return false;
		}
		// alert(data);

		swal({
		  title: "Anda Yakin?",
		  text: "Data akan diproses!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Yes",
		  cancelButtonText: "No",
		  closeOnConfirm: false
		},
		function(){
			// var form_data = $('#data_form').serialize();
			var form_data = new FormData($('#data_form')[0]);
		  $.ajax({
			  type:'POST',
			  url:siteurl + active_controller+'add',
			  dataType : "json",
			  data:form_data,
			  processData: false,
        		contentType: false,
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


	// DELETE DATA
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
