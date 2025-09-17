<?php
    $ENABLE_ADD     = has_permission('ApprovalPembelianAset.Add');
    $ENABLE_MANAGE  = has_permission('ApprovalPembelianAset.Manage');
    $ENABLE_VIEW    = has_permission('ApprovalPembelianAset.View');
    $ENABLE_DELETE  = has_permission('ApprovalPembelianAset.Delete');
?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box">
	<div class="box-header">
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="mytabledata" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="25">
			<?php if($ENABLE_MANAGE) : ?>
			Action
			<?php endif; ?>
			</th>
			<th>No Cash Advance</th>
			<th>No PR</th>
			<th>Tanggal Cash Advance</th>
			<th>Nilai Cash Advance</th>
			<th>Status</th>
		</tr>
		</thead>

		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ $numb++; ?>
		<tr>
			<td style="padding-left:20px">
			<?php if($ENABLE_MANAGE) : ?>
				<a class="text-green" href="javascript:void(0)" title="Detail" onclick="edit_data('<?=$record->id?>')"><i class="fa fa-search"></i></a>
			<?php endif; ?>
			</td>
			<td><?= $record->no_kasbon ?></td>
			<td><?= $record->no_pr ?></td>
			<td><?= $record->tgl_kasbon?></td>
			<td><?= number_format($record->nilai_kasbon) ?></td>
			<td><?php if($record->edit_status=='1') echo 'Edit';?></td>			
		</tr>
		<?php } 
		}  ?>
		</tbody>
		<tfoot>
		<tr>
			<th width="25">
			<?php if($ENABLE_MANAGE) : ?>
			Action
			<?php endif; ?>
			</th>
			<th>No Cash Advance</th>
			<th>No PR</th>
			<th>Tanggal Cash Advance</th>
			<th>Nilai Cash Advance</th>
			<th>Status</th>
		</tr>
		</tfoot>
		</table>
	</div>
	<!-- /.box-body -->
</div>
<div id="form-data"></div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

  	$(function() {
    	$("#mytabledata").DataTable();
    	$("#form-data").hide();
  	});

  	function edit_data(id){
		if(id!=""){
			var url = 'po_aset/edit_kasbon/'+id;
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(siteurl+url);
		    $("#title").focus();
		}
	}
</script>
