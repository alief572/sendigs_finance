<?php
$ENABLE_ADD     = has_permission('Price_Reference.Add');
$ENABLE_MANAGE  = has_permission('Price_Reference.Manage');
$ENABLE_VIEW    = has_permission('Price_Reference.View');
$ENABLE_DELETE  = has_permission('Price_Reference.Delete');
?>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
	<div class="dropdown">
	  <button class="btn btn-success btn-sm dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
		<i class="fa fa-plus">&nbsp;</i> Tambah
	  </button>
	  <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
	  <?php
		foreach ($data_tipe as $key=>$val){
			echo '<li><a href="javascript:new_data(\''.$key.'\')" title="'.$val.'"><i class="fa fa-calendar-o">&nbsp; </i> '.$val.'</a></li>';
		}
	  ?>
	  </ul>
	</div>
	<?php endif; ?>
	</div>
	<!-- /.box-header -->
	<div class="box-body"><div class="table-responsive">
		<table id="mytabledata" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="5">#</th>
			<th>Nama</th>
			<th>Spesifikasi</th>
			<th>Brand</th>
			<th>Kurs</th>
			<th>Price reference/unit</th>
			<th>Satuan</th>
			<th>Tipe</th>
			<th width="90">
			<?php if($ENABLE_MANAGE) : ?>
				Action
			<?php endif; ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ $numb++; ?>
		<tr>
		    <td><?= $numb; ?></td>
			<td><?= $record->nama ?></td>
			<td><?= $record->spec3 ?></td>
			<td><?= $record->spec2 ?></td>
			<td><?= $record->element_kurs ?></td>
			<td><?= number_format($record->element_cost) ?></td>
			<td><?= $record->element_unit ?></td>
			<td><?= $record->nama_tipe ?></td>
			<td>
			<?php if($ENABLE_MANAGE) : ?>
				<a class="btn btn-success btn-sm edit" href="javascript:void(0)" title="Edit" onclick="data_edit('<?=$record->id?>')"><i class="fa fa-edit"></i></a>
			<?php endif;
			if($ENABLE_DELETE) : ?>
				<a class="btn btn-danger btn-sm delete" href="javascript:void(0)" title="Hapus" onclick="data_delete('<?=$record->id?>')"><i class="fa fa-trash"></i></a>
			<?php endif; ?>
			</td>
		</tr>
		<?php }
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
	var url_add_default = siteurl+'price_ref/others_create/';
	var url_edit = siteurl+'price_ref/others_edit/';
	var url_delete = siteurl+'price_ref/others_delete/';
	var url_add='';
	function new_data(tipe){
		url_add=url_add_default+tipe;
		data_add();
	}
</script>
<script src="<?= base_url('assets/js/basic.js')?>"></script>
