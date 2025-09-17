<?php
    $ENABLE_ADD     = has_permission('Master_Kurs.Add');
    $ENABLE_MANAGE  = has_permission('Master_Kurs.Manage');
    $ENABLE_VIEW    = has_permission('Master_Kurs.View');
    $ENABLE_DELETE  = has_permission('Master_Kurs.Delete');
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
			<?php if($ENABLE_ADD) : ?>
				<a class="btn btn-success btn-sm add" href="javascript:void(0)" title="Add"><i class="fa fa-plus">&nbsp;</i>Add</a>
			<?php endif; ?>

		<span class="pull-right">
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th class='text-center'>#</th>
			<th class='text-center'>Currency</th>
			<th class='text-center'>Date</th>
			<th class='text-center'>Rate</th>
			<th class='text-center'>Last By</th>
			<th class='text-center'>Last Date</th>
			<th class='text-center'>Update Price By</th>
			<th class='text-center'>Update Price Date</th>
			<th class='text-center'>Action</th>
		</tr>
		</thead>

		<tbody>
		<?php 
			$numb=0; 
			foreach($result AS $record){ $numb++;
				$last_by	= (!empty($record->updated_by))?$GET_USERNAME[$record->updated_by]['nama']:$GET_USERNAME[$record->created_by]['nama'];
				$last_date	= (!empty($record->updated_date))?$record->updated_date:$record->created_date;

				$last_by2	= (!empty($record->price_by))?$GET_USERNAME[$record->price_by]['nama']:'';
				$last_date2	= (!empty($record->price_date))?date('d-M-Y H:i',strtotime($record->price_date)):'';
			?>
			<tr>
				<td align='center'><?= $numb; ?></td>
				<td align='center'><?= strtoupper($record->kode_currency) ?>  --- To --- <?= strtoupper($record->to_currency) ?></td>
				<td align='center'><?= date('d-M-Y',strtotime($record->tanggal)) ?></td>
				<td align='right'><?= number_format($record->kurs,6) ?></td>
				<td align='center'><?= strtoupper($last_by) ?></td>
				<td align='center'><?= date('d-M-Y H:i',strtotime($last_date)) ?></td>
				<td align='center'><?= strtoupper($last_by2) ?></td>
				<td align='center'><?= $last_date2 ?></td>
				<td align='center'>
					<?php if($ENABLE_MANAGE) : ?>
						<a class="btn btn-primary btn-sm edit" href="javascript:void(0)" title="Edit" data-id="<?=$record->id?>"><i class="fa fa-edit"></i>
						</a>
					<?php endif; ?>

					<?php if($ENABLE_DELETE) : ?>
						<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Delete" data-id="<?=$record->id?>"><i class="fa fa-trash"></i>
						</a>
					<?php endif; ?>
					<?php if($ENABLE_MANAGE) : ?>
						<a class="btn btn-success btn-sm update" href="javascript:void(0)" title="Update kurs to All" data-id="<?=$record->id?>">Update Kurs</a>
					<?php endif; ?>
				</td>
			</tr>
		<?php }   ?>
		</tbody>
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
	$(document).on('change', '#kode_currency', function(e){
		var id = $(this).val()
		$('#cur_dari').text(id)
	});

	$(document).on('change', '#to_currency', function(e){
		var id = $(this).val()
		$('#cur_ke').text(id)
	});


	$(document).on('click', '.edit', function(e){
		var id = $(this).data('id');
		$("#head_title").html("<b>Master Kurs</b>");
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
		$("#head_title").html("<b>Master Kurs</b>");
		$.ajax({
			type:'POST',
			url:siteurl+active_controller+'/add/',
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});

	$(document).on('submit', '#data_form', function(e){
		e.preventDefault()
		var data = $('#data_form').serialize();
		var code_lv1 = $('#code_lv1').val();

		if(code_lv1 == '0' ){
			swal({title	: "Error Message!",text	: 'Material type not selected...',type	: "warning"
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
		  $.ajax({
			  type:'POST',
			  url:siteurl + active_controller+'add',
			  dataType : "json",
			  data:data,
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

	$(document).on('click', '.update', function(e){
		e.preventDefault()
		var id = $(this).data('id');
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Update kurs to all Price!",
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
			  url:siteurl+active_controller+'/update_kurs_to_all',
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

  	$(function() {
	    var table = $('#example1').DataTable( {
	        orderCellsTop: true,
	        fixedHeader: true
	    } );
    	$("#form-area").hide();
  	});
</script>
