<?php
$ENABLE_ADD     = has_permission('Master_Karyawan.Add');
$ENABLE_MANAGE  = has_permission('Master_Karyawan.Manage');
$ENABLE_VIEW    = has_permission('Master_Karyawan.View');
$ENABLE_DELETE  = has_permission('Master_Karyawan.Delete');
?>

<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
	<div class="box-header">
    <span class="pull-left">
			<?php if($ENABLE_ADD) : ?>
					<a class="btn btn-success btn-sm" href="<?= base_url('master_employee/add') ?>" title="Add"> <i class="fa fa-plus">&nbsp;</i>Add</a>
			<?php endif; ?>
      <!-- <a class="btn btn-warning btn-sm" href="<?= base_url('master_employee/excel_download') ?>" target='_blank' title="Download Excel"> <i class="fa fa-file-excel-o">&nbsp;</i>&nbsp;Download Excel</a> -->

		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
      <thead>
          <th class="text-center">#</th>
          <th class="text-left">Employee Name</th>
          <th class="text-left">Gender</th>
          <th class="text-left">Department</th>
          <th class="text-left">Telepon</th>
          <th class="text-left">Email</th>
          <th class="text-center">Status</th>
          <th class="text-center">Option</th>
      </thead>
		  <tbody>
      <?php 
        $numb = 0;
        foreach($result AS $record){ $numb++;
			$status = 'Active';
			$status_ = 'green';
			if($record->status == 'N'){
				$status = 'Non-Active';
				$status_ = 'red';
			}

			$gender = 'Laki-Laki';
			if($record->gender == 'P'){
				$gender = 'Perempuan';
			}
          ?>
          <tr>
            <td class="text-center"><?= $numb; ?></td>
            <td><?= ucwords(strtolower($record->nm_karyawan)) ?></td>
            <td><?= $gender ?></td>
            <td><?= ucwords(strtolower(get_name('ms_department','nama','id',$record->department))) ?></td>
            <td><?= strtoupper($record->no_ponsel) ?></td>
            <td><?= strtolower($record->email) ?></td>
            <td class="text-center"><span class='badge bg-<?=$status_;?>'><?=$status;?></span></td>
            <td class="text-center">
              <a href='<?=base_url('master_employee/add/'.$record->id.'/view');?>' class="btn btn-warning btn-sm" title="Detail"><i class="fa fa-eye"></i></a>
              <?php if($ENABLE_MANAGE) : ?>
                <a href='<?=base_url('master_employee/add/'.$record->id);?>' class="btn btn-primary btn-sm" title="Edit"><i class="fa fa-edit"></i></a>
              <?php endif; ?>
              <?php if($ENABLE_DELETE) : ?>
                <button type='button' class="btn btn-danger btn-sm delete" title="Delete" data-id="<?=$record->id?>"><i class="fa fa-trash"></i></a>
              <?php endif; ?>
            </td>
          </tr>
        <?php } ?>
      </tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<style>
  .box-primary {

    border: 1px solid #ddd;
  }
</style>
<script type="text/javascript">
  $(document).ready(function() {
      var table = $('#example1').DataTable( {
	        orderCellsTop: true,
	        fixedHeader: true
	    } );
  });

  $(document).on('click', '.delete', function(e){
		e.preventDefault()
		var id = $(this).data('id');
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data akan di hapus!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Yes",
		  cancelButtonText: "No",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+active_controller+'/delete',
			  dataType : "json",
			  data:{'id':id},
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
</script>
