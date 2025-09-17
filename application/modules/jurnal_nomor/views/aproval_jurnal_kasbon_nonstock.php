<?php
    $ENABLE_ADD     = has_permission('ApprovalPembelianNonStock.Add');
    $ENABLE_MANAGE  = has_permission('ApprovalPembelianNonStock.Manage');
    $ENABLE_VIEW    = has_permission('ApprovalPembelianNonStock.View');
    $ENABLE_DELETE  = has_permission('ApprovalPembelianNonStock.Delete');
?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<style>
.modal-dialog{
width:90%;
}
</style>

<div class="box">
	<div class="box-header">
	</div>
	
	<!-- /.box-header -->
	<div class="box-body">
		<table id="mytabledata" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="150">
			<?php if($ENABLE_MANAGE) : ?>
			Action
			<?php endif; ?>
			</th>
			<th>No Cash Advance</th>
			<th>No PR</th>
			<th>Tanggal Cash Advance</th>
			<th>PIC</th>
			<th>Nilai Cash Advance</th>
			<th>Nilai Aktual</th>
			<th>Selisih</th>
		</tr>
		</thead>
		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ $numb++; ?>
		<tr>
			<td>
		    <?php if($ENABLE_MANAGE) : 
			if ($record->sts_buk == 0){
			?>
			
			   <!-- <a class="btn btn-success btn-sm view3" href="javascript:void(0)" title="View Jurnal Penerimaan" data-id_material="<?=$record->no_pr?>"><i class="fa fa-eye"></i>
				</a>
				<a class="btn btn-primary btn-sm view" href="javascript:void(0)" title="View Jurnal Permintaan Bayar" data-id_material="<?=$record->no_pr?>"><i class="fa fa-eye"></i>
				</a> -->
				<a class="btn btn-warning btn-sm view2" href="javascript:void(0)" title="View Jurnal Pembayaran" data-id_material="<?=$record->no_pr?>"><i class="fa fa-eye"></i>
				</a>
			<?php  }
			endif; ?>
			</td>
			<td><?= $record->no_kasbon ?></td>
			<td><?= $record->no_pr ?></td>
			<td><?= $record->tgl_kasbon?></td>
			<td><?= $record->pic?></td>
			<td><?= number_format($record->nilai_kasbon) ?></td>
			<td><?= number_format($record->nilai_aktual) ?></td>
			<td><?= number_format($record->nilai_kasbon-$record->nilai_aktual) ?></td>
		</tr>
		<?php }
		}  ?>
		</tbody>
		</table>
	</div>
	<!-- /.box-body -->
	
	
	
	
	<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="overflow:hidden;">
	  <div class="modal-dialog modal-lg">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
			<h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Jurnal</h4>
		  </div>
		  <div class="modal-body" id="ModalView">
			...
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-danger" data-dismiss="modal">
			<span class="glyphicon glyphicon-remove"></span>  Close</button>
			 
		 </div>
	    </div>
	  </div>
	
	</div>

</div>


<div id="form-data">
</div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

  $(document).on('click', '.view', function(){
		var id = $(this).data('id_material');
		var pp = 'pp';
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Jurnal</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'jurnal_nomor/view_jurnal_approval/'+id+'/'+pp,
			data:{'id':id},
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
				
			}
		})
	});
	
	$(document).on('click', '.view2', function(){
		var id = $(this).data('id_material');
		var pp = 'kasbonnonstok';
		var akses = 'approval_jurnal_kasbon_nonstok';
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Jurnal</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'jurnal_nomor/view_jurnal_buk/'+id+'/'+pp+'/'+akses,
			data:{'id':id},
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
				
			}
		})
	});
	
	$(document).on('click', '.view3', function(){
		var id = $(this).data('id_material');
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Jurnal</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'jurnal_nomor/view_jurnal_penerimaan/'+id,
			data:{'id':id},
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
				
			}
		})
	});

  	$(function() {
    	$("#mytabledata").DataTable({
		  "order": []
		});
    	$("#form-data").hide();

		// Daterange Picker
		$(".tgl").datepicker({
			todayHighlight: true,
			format : "yyyy-mm-dd",
			showInputs: true,
			autoclose:true
		});
  	});

  	function edit_data(id){
		if(id!=""){
			var url = 'po_nonstock/po_payment/'+id;
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(siteurl+url);
		    $("#title").focus();
		}
	}
</script>
