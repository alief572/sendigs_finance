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
			<th width="100">
			<?php if($ENABLE_MANAGE) : ?>
			Action
			<?php endif; ?>
			</th>
			<th>No PO</th>
			<th>No PR</th>
			<th>Tanggal PO</th>
			<th>Terbayar</th>
			<!-- <th>Status</th> -->
		</tr>
		</thead>

		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ $numb++; ?>
		<tr>
			<td>
			<?php if($ENABLE_MANAGE) : ?>
				<a class="text-green" target="_blank" href="print_po/<?=$record->id?>" title="Print PO" ><i class="fa fa-print"></i> Print PO</a><br>
				<a class="text-blue" href="javascript:void(0)" title="Pemeriksaan Barang" onclick="edit_data('<?=$record->id?>')"><i class="fa fa-file-text-o"></i> Penerimaan</a>
			<?php endif; ?>
			</td>
			<td><?= $record->no_po ?></td>
			<td><?= $record->no_pr ?></td>
			<td><?= $record->tgl_po?></td>
			<td><?= number_format($record->terbayar)?></td>
			<!-- <td><?php
				if($record->status=='0') echo 'Edit';
				if($record->status=='1') echo 'Menunggu persetujuan';
				if($record->status=='2') {
						echo 'Menunggu persetujuan AP';
				}
				if($record->status=='5') echo 'Selesai';
				if($record->status=='10') echo 'Ditolak';

			?></td> -->
		</tr>
		<?php }
		}  ?>
		</tbody>
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
			var url = 'po_aset/release_po/'+id;
			$(".box").hide();
			$("#form-data").show();
			$("#form-data").load(siteurl+url);
		    $("#title").focus();
		}
	}
</script>
