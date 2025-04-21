<?php
$ENABLE_ADD     = has_permission('Master_Customer.Add');
$ENABLE_MANAGE  = has_permission('Master_Customer.Manage');
$ENABLE_VIEW    = has_permission('Master_Customer.View');
$ENABLE_DELETE  = has_permission('Master_Customer.Delete');
?>

<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
	<div class="box-header">
    <span class="pull-right">
			<?php if($ENABLE_ADD) : ?>
					<a class="btn btn-success btn-sm" href="<?= base_url('master_customer/add') ?>" title="Add"> <i class="fa fa-plus">&nbsp;</i>Add</a>
			<?php endif; ?>
      <!-- <a class="btn btn-warning btn-sm" href="<?= base_url('master_customer/excel_download') ?>" target='_blank' title="Download Excel"> <i class="fa fa-file-excel-o">&nbsp;</i>&nbsp;Download Excel</a> -->

		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
      <thead>
          <th class="text-center">#</th>
          <th class="text-center">Customer</th>
          <th class="text-center">Credibility</th>
          <th class="text-center">Product Jual</th>
          <th class="text-center">Country</th>
          <th class="text-center">Status</th>
          <th class="text-center">Option</th>
      </thead>
		  <tbody>
      <?php 
        $numb = 0;
        foreach($result AS $record){ $numb++;
					if($record->sts_aktif == 'N'){
						$status = 'Non-Active';
						$status_ = 'red';
					}
					else{
						$status = 'Active';
						$status_ = 'green';
					}
          ?>
          <tr>
            <td class="text-center"><?= $numb; ?></td>
            <td><?= strtoupper($record->nm_customer) ?></td>
            <td class="text-center"><?= strtoupper($record->kredibilitas) ?></td>
            <td><?= strtoupper($record->produk_jual) ?></td>
            <td class="text-center"><?= strtoupper($record->country_code) ?></td>
            <td class="text-center"><span class='badge bg-<?=$status_;?>'><?=$status;?></span></td>
            <td class="text-center">
              <a href='<?=base_url('master_customer/add/'.$record->id_customer.'/view');?>' class="btn btn-warning btn-sm" title="Detail"><i class="fa fa-eye"></i></a>
              <?php if($ENABLE_MANAGE) : ?>
                <a href='<?=base_url('master_customer/add/'.$record->id_customer);?>' class="btn btn-primary btn-sm" title="Edit"><i class="fa fa-edit"></i></a>
              <?php endif; ?>
              <?php if($ENABLE_DELETE) : ?>
                <button type='button' class="btn btn-danger btn-sm delete" title="Delete" data-id="<?=$record->id_customer?>"><i class="fa fa-trash"></i></a>
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
