<?php
    $ENABLE_ADD     = has_permission('PembelianAset.Add');
    $ENABLE_MANAGE  = has_permission('PembelianAset.Manage');
    $ENABLE_VIEW    = has_permission('PembelianAset.View');
    $ENABLE_DELETE  = has_permission('PembelianAset.Delete');
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
			<th>
			<?php if($ENABLE_MANAGE) : ?>
			Action
			<?php endif; ?>
			</th>
			<th>No PO</th>
			<th>No PR</th>
			<th>Tgl PO</th>
			<th>Nilai PO</th>
			<th>PIC</th>
		</tr>
		</thead>

		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ $numb++; ?>
		<tr>
			<td style="padding-left:20px">
			<?php if($ENABLE_MANAGE) { ?>
				<?php if($record->status==2){?>
				<a class="text-green" href="javascript:void(0)" title="Print" onclick="edit_data('<?=$record->id?>')"><i class="fa fa-print"></i></a>
				<a class="text-green" href="javascript:void(0)" title="Pemeriksaan Barang" onclick="edit_data('<?=$record->id?>')"><i class="fa fa-file-text-o"></i></a>
				<?php } ?>
			<?php }?>
			</td>
			<td><?= $record->no_po ?></td>
			<td><?= $record->no_pr ?></td>
			<td><?= $record->tgl_po?></td>
			<td><?= number_format($record->nilai_po)?></td>
			<td><?= $record->pic?></td>
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
			<th>Tgl PO</th>
			<th>Nilai PO</th>
			<th>PIC</th>
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
			var url = 'po_stock/receive_po/'+id;
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(siteurl+url);
		    $("#title").focus();
		}
	}
</script>
