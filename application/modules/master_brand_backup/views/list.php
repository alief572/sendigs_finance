<?php
    $ENABLE_ADD = has_permission('Supplier.Add');
    $ENABLE_MANAGE = has_permission('Supplier.Manage');
    $ENABLE_VIEW = has_permission('Supplier.View');
    $ENABLE_DELETE = has_permission('Supplier.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}	
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css'); ?>">

<div class="box">
	<div class="box-header">
		<?php if ($ENABLE_ADD) : ?>
			<a class="btn btn-success" href="javascript:void(0)" title="Add" onclick="add_data()"><i class="fa fa-plus">&nbsp;</i>New</a>
		<?php endif; ?>

		<span class="pull-right">
			<?php echo anchor(site_url('supplier/downloadExcel'), ' <i class="fa fa-download"></i> Excel ', 'class="btn btn-primary btn-sm"'); ?>
			<a class="btn btn-primary btn-sm" data-toggle="modal" href="#dialog-popup" title="Pdf" onclick="PreviewRekap()"><i class="fa fa-print">&nbsp;</i>PDF</a>	
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="5">#</th>
			<th>ID Supplier</th>
			<th>Nama Supplier</th>
			<th>Negara</th>
			<th>Alamat</th>
			<th>Telpon / Fax</th>
			<th>Kotak Person</th>
			<th>HP Kotak Person / WeChat ID</th>
			<th>Email</th>
			<th>Status</th>
			<?php if ($ENABLE_MANAGE) : ?>
			<th width="25">Action</th>
			<?php endif; ?>
		</tr>
		</thead>

		<tbody>
		<?php if (empty($results)) {
} else {
    $numb = 0;
    foreach ($results as $record) {
        ++$numb; ?>
		<tr>
		    <td><?= $numb; ?></td>
			<td><?= $record->id_supplier; ?></td>
			<td><?= $record->nm_supplier; ?></td>
			<td><?= strtoupper($record->nm_negara); ?></td>
			<td><?= $record->alamat; ?></td>
			<td><?= $record->telpon.' / '.$record->fax; ?></td>			
			<td><?= $record->cp; ?></td>
			<td><?= $record->hp_cp.' / '.$record->id_webchat; ?></td>
			<td><?= $record->email; ?></td>
			<td>
				<?php if ($record->sts_aktif == 'aktif') {
            ?>
					<label class="label label-success">Aktif</label>
				<?php
        } else {
            ?>
					<label class="label label-danger">Non Aktif</label>
				<?php
        } ?>
			</td>
			<td style="padding-left:20px">
			    <a href="<?= base_url('supplier/produk/'.$record->id_supplier); ?>" >
                    PRODUK
                </a>
			<?php if ($ENABLE_VIEW) : ?>
				<a href="#dialog-popup" data-toggle="modal" onclick="PreviewPdf('<?=$record->id_supplier; ?>')">
				<span class="glyphicon glyphicon-print"></span>
				</a>
			<?php endif; ?>

			<?php if ($ENABLE_MANAGE) : ?>
				<a class="text-green" href="javascript:void(0)" title="Edit" onclick="edit_data('<?=$record->id_supplier; ?>')"><i class="fa fa-pencil"></i>
				</a>
			<?php endif; ?>

			<?php if ($ENABLE_DELETE) : ?>
				<a class="text-red" href="javascript:void(0)" title="Delete" onclick="delete_data('<?=$record->id_supplier; ?>')"><i class="fa fa-trash"></i>
				</a>
			<?php endif; ?>
			</td>
		</tr>
		<?php
    }
}  ?>
		</tbody>

		<tfoot>
		<tr>
			<th width="5">#</th>
			<th>ID Supplier</th>
			<th>Nama Supplier</th>
			<th>Negara</th>
			<th>Alamat</th>
			<th>Telpon / Fax</th>
			<th>Kotak Person</th>
			<th>HP Kotak Person / WebChat ID</th>
			<th>Email</th>
			<th>Status</th>
			<?php if ($ENABLE_MANAGE) : ?>
			<th width="25">Action</th>
			<?php endif; ?>
		</tr>
		</tfoot>
		</table>
	</div>
	<!-- /.box-body -->
</div>

<div id="form-area">
<?php $this->load->view('supplier/supplier_form'); ?>
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->
<div class="modal modal-primary" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel"></h4>
      </div>
      <div class="modal-body" id="MyModalBody">
    ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">
        <span class="glyphicon glyphicon-remove"></span>  Tutup</button>
        </div>
    </div>
  </div>
</div>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js');?>"></script>

<!-- page script -->
<script type="text/javascript">
	$(function() {  	
    	$('#example1 thead tr').clone(true).appendTo( '#example1 thead' );
	    $('#example1 thead tr:eq(1) th').each( function (i) {
	        var title = $(this).text();
	        //alert(title);
	        if (title == "#" || title =="Action" ) {
	        	$(this).html( '' );
	        }else{
	        	$(this).html( '<input type="text" />' );        
	        }
	        
	        $( 'input', this ).on( 'keyup change', function () {
	            if ( table.column(i).search() !== this.value ) {
	                table
	                    .column(i)
	                    .search( this.value )
	                    .draw();
	            }else{
	            	table
	                    .column(i)
	                    .search( this.value )
	                    .draw();
	            }
	        } );
	    } );
	 
	    var table = $('#example1').DataTable( {
	        orderCellsTop: true,
	        fixedHeader: true
	    } );
    	$("#form-area").hide();   
  	});

  	function add_data(){
		var url = 'supplier/create/';
		$(".box").hide();
		$("#form-area").show();
		$("#form-area").load(siteurl+url);
		$("#title").focus();
	}

  	function edit_data(id_supplier){
		if(id_supplier!=""){
			var url = 'supplier/edit/'+id_supplier;
			$(".box").hide();
			$("#form-area").show();
			$("#form-area").load(siteurl+url);
		    $("#title").focus();
		}
	}

	//Delete
	function delete_data(id){
		//alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data Akan Terhapus secara Permanen!",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonColor: "#DD6B55",
		  confirmButtonText: "Ya, delete!",
		  cancelButtonText: "Tidak!",
		  closeOnConfirm: false,
		  closeOnCancel: true
		},
		function(isConfirm){
		  if (isConfirm) {
		  	$.ajax({
		            url: siteurl+'supplier/hapus_supplier/'+id,
		            dataType : "json",
		            type: 'POST',
		            success: function(msg){
		                if(msg['delete']=='1'){		                	
		                    swal({
		                      title: "Terhapus!",
		                      text: "Data berhasil dihapus",
		                      type: "success",
		                      timer: 1500,
		                      showConfirmButton: false
		                    });
		                    window.location.reload();
		                } else {
		                    swal({
		                      title: "Gagal!",
		                      text: "Data gagal dihapus",
		                      type: "error",
		                      timer: 1500,
		                      showConfirmButton: false
		                    });
		                };
		            },
		            error: function(){
		                swal({
	                      title: "Gagal!",
	                      text: "Gagal Eksekusi Ajax",
	                      type: "error",
	                      timer: 1500,
	                      showConfirmButton: false
	                    });
		            }
		        });
		  } else {
		    //cancel();
		  }
		});
	}

	function PreviewPdf(id)
	{
		param=id;
		tujuan = 'supplier/print_request/'+param;

	   	$(".modal-body").html('<iframe src="'+tujuan+'" frameborder="no" width="100%" height="400"></iframe>');
	}

	function PreviewRekap()
	{
		$('#myModalLabel').html('<span class="fa fa-file-pdf-o"></span> Rekap Supplier');      
		tujuan = 'supplier/print_rekap';	   	
	   	$(".modal-body").html('<iframe src="'+tujuan+'" frameborder="no" width="100%" height="400"></iframe>');
	}
</script>
