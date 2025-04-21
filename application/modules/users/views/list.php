<?php 
    $ENABLE_ADD     = has_permission('Users.Add');
    $ENABLE_MANAGE  = has_permission('Users.Manage');
    $ENABLE_DELETE  = has_permission('Users.Delete');
	//echo "ENABLE_ADD=".$ENABLE_ADD."<BR>";
	//echo "ENABLE_MANAGE=".$ENABLE_MANAGE."<BR>";
	//echo $this->uri->uri_string()."ENABLE_DELETE=".$ENABLE_DELETE."<BR>";
?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<a href="<?= site_url('users/setting/create') ?>" class="btn btn-success" title="<?= lang('users_btn_new') ?>"><?= lang('users_btn_new') ?></a>
		<?php endif; ?>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="50">#</th>
			<th><?= lang('users_username') ?></th>
			<th><?= lang('users_email') ?></th>
			<th><?= lang('users_nm_lengkap') ?></th>
			<th><?= lang('users_alamat') ?></th>
			<th><?= lang('users_kota') ?></th>
			<th><?= lang('users_hp') ?></th>
			<th><?= lang('users_st_aktif') ?></th>
			<?php if($ENABLE_MANAGE) : ?>
			<th width="80"></th>
			<?php endif; ?>			
		</tr>
		</thead>
        
		<tbody>
		<?php foreach ($results as $record) : ?>
		<tr>                        
			<td><?= $numb; ?></td>
			<td><?= $record->username ?></td>
			<td><?= $record->email ?></td>
			<td><?= $record->nm_lengkap ?></td>
			<td><?= $record->alamat ?></td>
			<td><?= $record->kota ?></td>
			<td><?= $record->hp ?></td>
			<td><?= ($record->st_aktif == 0) ? "<label class='label label-danger'>".lang('users_td_aktif')."</label>" : "<label class='label label-success'>".lang('users_aktif')."</label>" ?></td>
			<?php if($ENABLE_MANAGE) : ?>
			<td style="padding-right:20px">
			<a class="text-black" href="<?= site_url('users/setting/edit/' . $record->id_user); ?>" data-toggle="tooltip" data-placement="left" title="Edit Data"><i class="fa fa-pencil"></i></a>
			<?php if($record->id_user != 1) : ?>
			&nbsp;|&nbsp;
			<a class="text-black" href="<?= site_url('users/setting/permission/' . $record->id_user); ?>" data-toggle="tooltip" data-placement="left" title="Edit Hak Akses"><i class="fa fa-shield"></i></a>
			<?php endif; ?>
			</td>
			<?php endif; ?>
			</tr>
			<?php $numb++; endforeach; ?>        
		</tbody>
		
		<tfoot>
		<tr>
			<th width="50">#</th>
			<th><?= lang('users_username') ?></th>
			<th><?= lang('users_email') ?></th>
			<th><?= lang('users_nm_lengkap') ?></th>
			<th><?= lang('users_alamat') ?></th>
			<th><?= lang('users_kota') ?></th>
			<th><?= lang('users_hp') ?></th>
			<th><?= lang('users_st_aktif') ?></th>
			<?php if($ENABLE_MANAGE) : ?>
			<th width="80"></th>
			<?php endif; ?>		        
		</tr>
		</tfoot>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script>
  $(function () {
    $("#example1").DataTable();    
  });
</script>


