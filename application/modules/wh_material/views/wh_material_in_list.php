<?php
$ENABLE_ADD     = has_permission('Wh_material_in.Add');
$ENABLE_MANAGE  = has_permission('Wh_material_in.Manage');
$ENABLE_VIEW    = has_permission('Wh_material_in.View');
$ENABLE_DELETE  = has_permission('Wh_material_in.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<a class="btn btn-success btn-sm" href="javascript:void(0)" title="Tambah" onclick="data_add()"><i class="fa fa-plus">&nbsp;</i>Tambah</a>
		<?php endif; ?>
	</div>
	<!-- /.box-header -->
	<div class="box-body"><div class="table-responsive">
		<table id="mytabledata" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="5">#</th>
			<th>No Dokumen</th>
			<th>Referensi</th>
			<th>Gudang</th>
			<th>Tanggal</th>
			<th>Status</th>
			<th>PIC</th>
			<th>Keterangan</th>
			<th width="90">
			<?php if($ENABLE_MANAGE) : ?>
				Action
			<?php endif; ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		if(!empty($results)){
			$numb=0; foreach($results AS $record){ $numb++; ?>
		<tr>
		    <td><?= $numb; ?></td>
			<td><?= $record->doc_no ?></td>
			<td><?= $record->reference_no ?></td>
			<td><?= $record->wh_code ?></td>
			<td><?= $record->trans_date ?></td>
			<td><?= $status[$record->status] ?></td>
			<td><?= $record->pic ?></td>
			<td><?= $record->info ?></td>
			<td>
			<?php if($ENABLE_VIEW) : ?>
				<a class="btn btn-warning btn-sm view" href="javascript:void(0)" title="View" onclick="data_view('<?=$record->id?>')"><i class="fa fa-eye"></i></a>
			<?php endif;
			if($ENABLE_MANAGE) :
				if ($record->status==0) {?>
				<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit('<?=$record->id?>')"><i class="fa fa-edit"></i></a>
				<?php }
				endif;
			if($ENABLE_DELETE) :
				if ($record->status==0) {?>
				<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Hapus" onclick="data_delete('<?=$record->id?>')"><i class="fa fa-trash"></i></a>
				<?php }
				endif; ?>
			</td>
		</tr>
		<?php
			}
		}  ?>
		</tbody>
		</table>
		</div>
	</div>
	<!-- /.box-body -->
</div>
<div id="form-data"></div>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<!-- page script -->
<script type="text/javascript">
	var url_add = siteurl+'wh_material/create_in/';
	var url_edit = siteurl+'wh_material/edit_in/';
	var url_delete = siteurl+'wh_material/delete/';
	var url_view = siteurl+'wh_material/view_in/';
</script>
<script src="<?= base_url('assets/js/basic.js')?>"></script>
