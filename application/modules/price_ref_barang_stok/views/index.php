<?php
    $ENABLE_ADD     = has_permission('Price_Ref_Barang_Stok.Add');
    $ENABLE_MANAGE  = has_permission('Price_Ref_Barang_Stok.Manage');
    $ENABLE_VIEW    = has_permission('Price_Ref_Barang_Stok.View');
    $ENABLE_DELETE  = has_permission('Price_Ref_Barang_Stok.Delete');
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
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th>#</th>
			<th>Stok Code</th>
			<th>Stok Master</th>
			<th>Spec</th>
			<th>Price Ref IDR</th>
			<th>Price Ref USD</th>
			<th>Expired</th>
			<!-- <th>Price Pur</th>
			<th>Expired</th> -->
			<th>Status</th>
			<th>Action</th>
		</tr>
		</thead>

		<tbody>
		<?php if(empty($result)){
		}else{
			$numb=0; foreach($result AS $record){ $numb++;
				$date_now		= date('Y-m-d');

				$status = 'Not Set';
				$status_ = 'yellow';
				$status2 = '';

				$expired = '-';
				$expired_new = '-';
				if(!empty($record->price_ref_date_use)){
					$price_ref_date 	= date('Y-m-d', strtotime('+'.$record->price_ref_expired_use.' month', strtotime($record->price_ref_date_use)));
					$expired = date('d-M-Y',strtotime($price_ref_date));
					if($date_now > $price_ref_date){
						$status = 'Expired';
						$status_ = 'red';
					}
					else{
						$status = 'Oke';
						$status_ = 'green';
					}
				}
				if(!empty($record->price_ref_date)){
					$price_ref_date 	= date('Y-m-d', strtotime('+'.$record->price_ref_expired.' month', strtotime($record->price_ref_date)));
					$expired_new = date('d-M-Y',strtotime($price_ref_date));
					
				}
				if($record->status_app == 'Y'){
					$status2 = 'Waiting Approve';
					$status2_ = 'purple';
				}
			?>
		<tr>
		    <td><?= $numb; ?></td>
			<td><?= strtoupper($record->id_stock) ?></td>
			<td><?= strtoupper($record->stock_name) ?></td>
			<td><?= strtoupper($record->spec) ?></td>
			<td align='right'><?= number_format($record->price_ref_use) ?></td>
			<td align='right'><?= number_format($record->price_ref_use_usd,2) ?></td>
			<td align='center'><?=$expired;?></td>

			<!-- <td align='right'><?= number_format($record->price_ref,2) ?></td>
			<td align='center'><?=$expired_new;?></td> -->

			<td><span class='badge bg-<?=$status_;?>'><?=$status;?></span><br><span class='badge bg-<?=$status2_;?>'><?=$status2;?></span></td>


			<td align='left'>

			<?php if($ENABLE_MANAGE AND $record->status_app == 'Y') : ?>
				<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Approve" data-id="<?=$record->id?>"><i class="fa fa-check"></i></a>
			<?php endif; ?>

			<?php if(!empty($record->upload_file)) : ?>
				<a class="btn btn-warning btn-sm" href="<?=base_url($record->upload_file);?>" target='_blank' title="Download"><i class="fa fa-download"></i></a>
			<?php endif; ?>
			</td>

		</tr>
		<?php } }  ?>
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

	$(document).on('click', '.edit', function(e){
		var id = $(this).data('id');
		$("#head_title").html("<b>Stok Master</b>");
		$.ajax({
			type:'POST',
			url:siteurl+active_controller+'/add/'+id,
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);

			}
		})
	});
	

	$(document).on('submit', '#data_form', function(e){
		e.preventDefault()
		
		var price_ref_expired_use_after = $('#price_ref_expired_use_after').val();
		var action_app = $('#action_app').val();

		if(price_ref_expired_use_after == '0' && action_app == '1'){
			swal({title	: "Error Message!",text	: 'Expired not selected...',type	: "warning"
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

	$(document).on('keyup','#price_ref_use_after',function(){
		var price_ref 	= getNum($('#price_ref_use_after').val().split(",").join(""));
		var kurs 		= getNum($('#kurs').val().split(",").join(""));
		var price		= price_ref / kurs
		$('#price_ref_use_after_usd').val(price)
	})
	//input usd
	$(document).on('keyup','#price_ref_use_after_usd',function(){
		var price_ref 	= getNum($('#price_ref_use_after_usd').val().split(",").join(""));
		var kurs 		= getNum($('#kurs').val().split(",").join(""));
		var price		= price_ref * kurs
		$('#price_ref_use_after').val(price)
	})


  	$(function() {
	    var table = $('#example1').DataTable( {
	        orderCellsTop: true,
	        fixedHeader: true
	    } );
    	$("#form-area").hide();
  	});
</script>
