<?php
    $ENABLE_ADD     = has_permission('Cycletime.Add');
    $ENABLE_MANAGE  = has_permission('Cycletime.Manage');
    $ENABLE_VIEW    = has_permission('Cycletime.View');
    $ENABLE_DELETE  = has_permission('Cycletime.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
	<div class="box-header">
			<?php if($ENABLE_ADD) : ?>
					<a class="btn btn-success btn-sm" href="<?= base_url('produksi/add') ?>" title="Add"> <i class="fa fa-plus">&nbsp;</i>Add</a>
			<?php endif; ?>

		<span class="pull-right">
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="5">#</th>
      <th >Production Date</th>
			<th >Costcenter Name</th>
      <th >Product</th>
			<!-- <th >Process</th> -->
      <th >Daycode</th>
			<!-- <th >Information</th> -->
      <th >Remarks</th>
      <th >Created By</th>
      <th >Created Date</th>
		</tr>
		</thead>

		<tbody>
		<?php if(empty($results)){
		}else{
			$numb=0; foreach($results AS $record){ $numb++;
        $remak = (!empty($record->remarks))?$record->remarks:'-';
        ?>
      <tr>
        <td><?= $numb; ?></td>
        <td><?= date('d-F-Y', strtotime($record->tanggal_produksi)) ?></td>
        <td><?= ucwords(strtolower($record->nama_costcenter)) ?></td>
        <td><?= ucwords(strtolower($record->nm_product)) ?></td>
        <!-- <td><?= ucfirst(strtolower($record->nm_process)) ?></td> -->
        <?php if($record->ket == 'not yet'){ ?>
        <td><a class='code' data-id='<?=$record->id;?>' style='cursor:pointer;'><?=strtoupper(strtolower($record->code)) ?> Edit</a></td>
        <?php } ?>

        <?php if($record->ket <> 'not yet'){ ?>
        <td><?= strtoupper(strtolower($record->code)) ?></td>
        <?php } ?>
        <!-- <td><?= ucfirst(strtolower($record->ket)) ?></td> -->
        <td><?= strtoupper(strtolower($remak)) ?></td>
        <td><?= ucfirst(strtolower(get_name('users', 'username', 'id_user',$record->created_by))) ?></td>
        <td><?= $record->created_date ?></td>

      </tr>
		<?php } }  ?>
		</tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style='width:40%; '>
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"><span class="fa fa-users"></span>&nbsp;Detail Data</h4>
      </div>
      <div class="modal-body" id="ModalView">
		...
      </div>
  </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

	$(document).on('click', '.code', function(){
    var code = $(this).data('id');
		$.ajax({
			type:'POST',
			url:siteurl+'produksi/change_daycode/'+code,
			success:function(data){
        $("#myModalLabel").html('Edit Daycode');
				$("#dialog-popup").modal();
				$("#ModalView").html(data);
        $('.chosen-select').select2({'width':'100%'});
			}
		})
	});

  $(document).on('click', '#save_daycode', function(e){
    e.preventDefault();
    var daycode_new	= $('#daycode_new').val();

    if(daycode_new == '0' ){
      swal({
        title	: "Error Message!",
        text	: 'Daycode New is empty, select first ...',
        type	: "warning"
      });

      $('#save_daycode').prop('disabled',false);
      return false;
    }

    swal({
        title: "Are you sure?",
        text: "You will not be able to process again this data!",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "Yes, Process it!",
        cancelButtonText: "No, cancel process!",
        closeOnConfirm: true,
        closeOnCancel: false
      },
      function(isConfirm) {
        if (isConfirm) {
          var formData 	=new FormData($('#data-form')[0]);
          var baseurl=siteurl+'produksi/change_daycode';
          $.ajax({
            url			: baseurl,
            type		: "POST",
            data		: formData,
            cache		: false,
            dataType	: 'json',
            processData	: false,
            contentType	: false,
            success		: function(data){
              if(data.status == 1){
                swal({
                    title	: "Save Success!",
                    text	: data.pesan,
                    type	: "success",
                    timer	: 7000,
                    showCancelButton	: false,
                    showConfirmButton	: false,
                    allowOutsideClick	: false
                  });
                window.location.href = base_url + active_controller;
              }else{

                if(data.status == 2){
                  swal({
                    title	: "Save Failed!",
                    text	: data.pesan,
                    type	: "warning",
                    timer	: 7000,
                    showCancelButton	: false,
                    showConfirmButton	: false,
                    allowOutsideClick	: false
                  });
                }else{
                  swal({
                    title	: "Save Failed!",
                    text	: data.pesan,
                    type	: "warning",
                    timer	: 7000,
                    showCancelButton	: false,
                    showConfirmButton	: false,
                    allowOutsideClick	: false
                  });
                }

              }
            },
            error: function() {

              swal({
                title				: "Error Message !",
                text				: 'An Error Occured During Process. Please try again..',
                type				: "warning",
                timer				: 7000,
                showCancelButton	: false,
                showConfirmButton	: false,
                allowOutsideClick	: false
              });
            }
          });
        } else {
        swal("Cancelled", "Data can be process again :)", "error");
        return false;
        }
    });
  });


  	$(function() {

	    var table = $('#example1').DataTable( {
	        orderCellsTop: true,
	        fixedHeader: true
	    } );
    	$("#form-area").hide();
  	});

</script>
