<?php
    $ENABLE_ADD     = has_permission('Set_bottom_price.Add');
    $ENABLE_MANAGE  = has_permission('Set_bottom_price.Manage');
    $ENABLE_VIEW    = has_permission('Set_bottom_price.View');
    $ENABLE_DELETE  = has_permission('Set_bottom_price.Delete');
?>

<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
    
          <div class="box box-primary">
              <div class="box-header">
                <div style="display:inline-block;width:100%;">
                  <a class="btn btn-success" href="<?= base_url('set_bottom_price/add_proses_cost') ?>" title="Add" style="float:left;margin-right:8px"><i class="fa fa-plus">&nbsp;</i>New</a>
                  <!--<a class="btn btn-sm btn-danger pdf" id="pdf-report" style="float:right;margin:8px 8px 0 0"><i class="fa fa-file"></i> PDF</a>
                  <a class="btn btn-sm btn-success excel" id="excel-report" style="float:right;margin:8px 8px 0 0"><i class="fa fa-table"></i> Excel</a> -->
                </div>

              </div>
              <div class="box-body">
                <table id="tableset" class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th width="5">#</th>
                			<th>Progress Cost Id</th>
                			<th>Supplier Name</th>
                			<th>Country</th>
                			<th>Product</th>
                			<?php if ($ENABLE_MANAGE) : ?>
                			<th width="75">Action</th>
                			<?php endif; ?>
                    </tr>
                  </thead>

                <tbody id="tbody-detail">
                </tbody>

                <tfoot>
                  <tr>
                    <th width="5">#</th>
                    <th>Supplier ID</th>
                    <th>Supplier Name</th>
                    <th>Country</th>
                    <th>Product</th>
                    <?php if ($ENABLE_MANAGE) : ?>
                    <th width="75">Action</th>
                    <?php endif; ?>
                  </tr>
                </tfoot>
                </table>
              </div>
          </div>

<div id="form-area">

</div>
<form id="form-modal" action="" method="post">
  <div class="modal fade" id="ModalView">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="head_title"></h4>
        </div>
        <div class="modal-body" id="view">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger " data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal fade" id="ModalView2">
    <div class="modal-dialog"  style='width:30%; '>
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="head_title2"></h4>
          </div>
          <div class="modal-body" id="view2">
          </div>
          <div class="modal-footer">
            <!--<button type="button" class="btn btn-primary">Save</button>-->
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="ModalView3">
      <div class="modal-dialog"  style='width:30%; '>
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="head_title3"></h4>
            </div>
            <div class="modal-body" id="view3">
            </div>
            <div class="modal-footer">
              <!--<button type="button" class="btn btn-primary">Save</button>-->
              <button type="button" class="btn btn-default close3" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    <!-- modal -->
</form>
<!-- Modal Bidus-->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<!-- End Modal Bidus-->
<script type="text/javascript">

  $(document).ready(function() {
	  
	$(document).on('click', '.edit', function(e){
		var id = $(this).data('id_supplier');
		 window.location.href = siteurl+"master_supplier/edit/"+id;	
	});
	
	$(document).on('click', '.detail', function(e){
		var id = $(this).data('id_supplier');
		$("#head_title").html("<i class='fa fa-list-alt'></i><b> Detail Supplier</b>");
		$("#view").load(siteurl+active_controller+'/viewSupplier/'+id);
		$("#ModalView").modal();
	});
	
	$(document).on('click', '.delete', function(e){
		var id = $(this).data('id_supplier');
		 // window.location.href = siteurl+"master_supplier/edit/"+id;		 
		 swal({
		  title: "Anda Yakin?",
		  text: "Data Supplier akan di Hapus?.",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonText: "Ya, Hapus!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+'master_supplier/delete/'+id,
			  dataType : "json",
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses",
						  text : "Data Supllier berhasil dihapus.",
						  type : "success"
						},
						function (){
							window.location.reload(true);
						})
				  } else {
					swal({
					  title : "Error",
					  text  : "Data error. Gagal hapus data",
					  type  : "error"
					})
					
				  }
			  },
			  error : function(){
				swal({
					  title : "Error",
					  text  : "Data error. Gagal request Ajax",
					  type  : "error"
					})
			  }
		  })
		});
	});
	
		
    //var table = $('#example1').DataTable();
    DataTables('set');

    $("#excel-report").on('click', function() {
      if (document.getElementById("hide-click").classList.contains('hide_colly')) {
        window.location.href = siteurl+"barang_wset/downloadExcel/hide";
      }else {
        window.location.href = siteurl+"barang_wset/downloadExcel/unhide";
      }
    });

    $("#pdf-report").on('click', function() {
      if (document.getElementById("hide-click").classList.contains('hide_colly')) {
        window.open = siteurl+"barang_wset/print_rekap/hide";
      }else {
        window.open = siteurl+"barang_wset/print_rekap/unhide";
      }
    });
  });

  function DataTables(set=null){
    // console.log(set);
    var dataTable = $('#tableset').DataTable({
      "serverSide": true,
      "stateSave" : true,
      "bAutoWidth": true,
      "destroy": true,
      "responsive": true,
      "oLanguage": {
        "sSearch": "<b>Live Search : </b>",
        "sLengthMenu": "_MENU_ &nbsp;&nbsp;<b>Records Per Page</b>&nbsp;&nbsp;",
        "sInfo": "Showing _START_ to _END_ of _TOTAL_ entries",
        "sInfoFiltered": "(filtered from _MAX_ total entries)",
        "sZeroRecords": "No matching records found",
        "sEmptyTable": "No data available in table",
        "sLoadingRecords": "Please wait - loading...",
        "oPaginate": {
          "sPrevious": "Prev",
          "sNext": "Next"
        }
      },
      "aaSorting": [[ 1, "asc" ]],
      "columnDefs": [ {
        "targets": 'no-sort',
        "orderable": false,
      }],
      "sPaginationType": "simple_numbers",
      "iDisplayLength": 5,
      "aLengthMenu": [[5, 10, 20, 50, 100, 150], [5, 10, 20, 50, 100, 150]],
      "ajax":{
        url : siteurl + active_controller + 'getDataJSON',
        type: "post",
        data: function(d){
          d.activation 	= 'aktif'
        },
        cache: false,
        error: function(){
          $(".my-grid-error").html("");
          $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
          $("#my-grid_processing").css("display","none");
        }
      }
    });

  }

</script>
