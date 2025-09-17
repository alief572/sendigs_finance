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
			<th width="30">
			<?php if($ENABLE_MANAGE) : ?>
			Action
			<?php endif; ?>
			</th>
			<th>No PO</th>
			<th>No PR</th>
			<th>Tanggal PR</th>
			<th>Tanggal PO</th>
			<th>Tanggal PP</th> 			
			<th>Nilai Pembayaran</th>
			<th>Terbayar</th>
			<th>Supplier</th>
		</tr>
		</thead>

		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ $numb++; 
			
			$tglpr  = $this->db->query("SELECT * FROM tr_pr_stok WHERE no_pr='$record->no_pr'")->row();
			$tglpo  = $this->db->query("SELECT * FROM tr_pr_po_stok WHERE no_po='$record->no_po'")->row();
			$vendor  = $this->db->query("SELECT * FROM ms_vendor WHERE id_vendor='$tglpo->vendor_id'")->row();
			
			?>
		<tr>
			<td style="padding-left:20px">
			<?php if($ENABLE_MANAGE) : 
			if ($record->sts_trm == 1){
			?>
			    <a class="btn btn-success btn-sm view3" href="javascript:void(0)" title="View Jurnal Penerimaan" data-id_material="<?=$record->no_po?>"><i class="fa fa-eye"></i>
				</a>
			<?php 
			}
			if ($record->sts_apr == 1){
			?>
				<a class="btn btn-primary btn-sm view" href="javascript:void(0)" title="View Jurnal Permintaan Bayar" data-id_material="<?=$record->no_po?>" data-id_total="<?=$record->request_payment + $record->ppn_request?>" data-id_vendor="<?=$record->vendor_id?>" data-nm_vendor="<?=$record->nama?>"><i class="fa fa-eye"></i>
				</a>
			<?php 
			}
			if ($record->sts_buk == 1){
			?>
				<a class="btn btn-warning btn-sm view2" href="javascript:void(0)" title="View Jurnal Pembayaran" data-id_material="<?=$record->no_po?>" data-id_total="<?=$record->terbayar + $record->nilai_ppn?>" data-id_vendor="<?=$record->vendor_id?>" data-nm_vendor="<?=$record->nama?>"><i class="fa fa-eye"></i>
				</a>
			<?php 
			}
			endif; ?>
			</td>
			<td><?= $record->no_po ?></td>
			<td><?= $record->no_pr ?></td>
			<td><?= $tglpr->tgl_pr?></td>
			<td><?= $tglpo->tgl_po?></td>
			<td><?= $record->tgl_periksa?></td>
			<td><?= number_format($record->request_payment)?></td>
			<td><?= number_format($record->terbayar)?></td>
			<td><?= $record->nama?></td>
		</tr>
		<?php } 
		}  ?>
		</tbody>
		<tfoot>
		<tr>
			<th>
			<?php if($ENABLE_MANAGE) : ?>
			Action
			<?php endif; ?>
			</th>
			<th>No PO</th>
			<th>No PR</th>
			<th>Tanggal PR</th>
			<th>Tanggal PO</th>
			<th>Tanggal PP</th> 	
			<th>Nilai Pembayaran</th>
			<th>Terbayar</th>
			<th>Supplier</th>
		</tr>
		</tfoot>
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
		var total = $(this).data('id_total');
		var id_vendor = $(this).data('id_vendor');
		var nm_vendor = $(this).data('nm_vendor');
		var pp = 'postok';
		var akses = 'approval_jurnal_po_stok';
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Jurnal</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'jurnal_nomor/view_jurnal_approval/'+id+'/'+pp+'/'+akses+'/'+total+'/'+id_vendor+'/'+nm_vendor,
			data:{'id':id},
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
				
			}
		})
	});
	
	$(document).on('click', '.view2', function(){
		var id = $(this).data('id_material');
		var total = $(this).data('id_total');
		var id_vendor = $(this).data('id_vendor');
		var nm_vendor = $(this).data('nm_vendor');
		var pp = 'postok';
		var akses = 'approval_jurnal_po_stok';
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Jurnal</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'jurnal_nomor/view_jurnal_buk/'+id+'/'+pp+'/'+akses+'/'+total+'/'+id_vendor+'/'+nm_vendor,
			data:{'id':id},
			success:function(data){
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
				
			}
		})
	});
	
	$(document).on('click', '.view3', function(){
		var id = $(this).data('id_material');
	    var pp = 'postok';
		var akses = 'approval_jurnal_po_stok';
		// alert(id);
		$("#head_title").html("<i class='fa fa-list-alt'></i><b>Detail Jurnal</b>");
		$.ajax({
			type:'POST',
			url:siteurl+'jurnal_nomor/view_jurnal_penerimaan/'+id+'/'+pp+'/'+akses,
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
