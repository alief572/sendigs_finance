<?php
    $ENABLE_ADD     = has_permission('PembelianNonStock.Add');
    $ENABLE_MANAGE  = has_permission('PembelianNonStock.Manage');
    $ENABLE_VIEW    = has_permission('PembelianNonStock.View');
    $ENABLE_DELETE  = has_permission('PembelianNonStock.Delete');
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
			<th>No PR</th>
			<th>Tanggal PO</th>
			<th>Nilai PO</th>
			<th>Supplier</th>
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
				<a class="text-green" href="javascript:void(0)" title="Edit" onclick="edit_data('<?=$record->id?>')"><i class="fa fa-pencil"></i></a>
			<?php endif; ?>
			</td>
			<td><?= $record->no_pr ?></td>
			<td><?= $record->tgl_po?></td>
			<td><?= number_format($record->harga_total)?></td>
			<td><?= $record->nama?></td>
			<td><?php
			if($record->status=='1'){
				if($record->edit_status=='1') echo 'Edit';
			}else{
				
			}?></td>
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
			<th>No PR</th>
			<th>Tanggal PO</th>
			<th>Nilai PO</th>
			<th>Supplier</th>
			<th>Status</th>
		</tr>
		</tfoot>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<div id="form-data">
</div>
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
			var url = 'po_aset/edit_po/'+id;
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(siteurl+url);
		    $("#title").focus();
		}
	}
</script>
