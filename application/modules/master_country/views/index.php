<?php
    $ENABLE_ADD     = has_permission('Supplier.Add');
    $ENABLE_MANAGE  = has_permission('Supplier.Manage');
    $ENABLE_VIEW    = has_permission('Supplier.View');
    $ENABLE_DELETE  = has_permission('Supplier.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<div class="box box-primary">
    <div class="box-header">
      <div style="display:inline-block;width:100%;">
        <a class="btn btn-sm btn-success" href="javascript:void(0)" title="Add" onclick="add_data()" style="float:left;margin-right:8px"><i class="fa fa-plus">&nbsp;</i>New</a>
        <a class="btn btn-sm btn-danger pdf" id="pdf-report" style="float:right;margin:8px 8px 0 0"><i class="fa fa-file"></i> PDF</a>
        <a class="btn btn-sm btn-success excel" id="excel-report" style="float:right;margin:8px 8px 0 0"><i class="fa fa-table"></i> Excel</a>
      </div>

    </div>
    <div class="box-body">

      <table id="tableset" class="table table-bordered table-striped">
        <thead>
          <tr>
            <th width="5">#</th>
      			<th>Country ID</th>
      			<th>Country Name</th>
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
          <th>Country ID</th>
          <th>Country Name</th>
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
    <div class="modal-dialog"  style='width:55%; '>
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="head_title"></h4>
        </div>
        <div class="modal-body" id="view">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default " data-dismiss="modal">Close</button>
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
            <button type="button" class="btn btn-default close2" data-dismiss="modal">Close</button>
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
<style>
  .box-primary{

    border: 1px solid #ddd;
  }
</style>
<script type="text/javascript">
  $(document).ready(function() {
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

    $('#tableset tbody').on('click', 'a.sunting', function () {
      var id = $(this).data('id_barang');
      $("#head_title").html("<b>EDIT ITEM</b>");
      $("#view").load(siteurl+active_controller+'modal_Process/edit/'+id);
      $("#ModalView").modal();
    });

    $(document).on('click', '.delete', function(e){
      var formdata = $(this).data('id_country');
      $.ajax({
        url: siteurl+active_controller+"deleteCountry/"+formdata,
        dataType : "json",
        type: 'POST',
        data: formdata,
        success: function(result){
          //console.log(result['msg']);
          if(result.status=='1'){
            swal({
              title: "Sukses!",
              text: result['pesan'],
              type: "success",
              timer: 1500,
              showConfirmButton: false
            });
            setTimeout(function(){
              DataTables('set');
            },1600);
          } else {
            swal({
              title: "Gagal!",
              text: result['pesan'],
              type: "error",
              timer: 1500,
              showConfirmButton: false
            });
          };
        },
        error: function (request, error) {
          console.log(arguments);
          alert(" Can't do because: " + error);
        }
      });
      //$("#ModalView").modal('hide');
    });

    $(document).on('click', '#selCountry', function(e){
			selCountry();
		});
    $(document).on('click', '#addCountry', function(e){
			addCountry();
		});
    $(document).on('click', '.edit', function(e){
      var id = $(this).data('id_country');
      $("#head_title").html("<b>EDIT BRAND</b>");
      $("#view").load(siteurl+active_controller+'modal_Process/edit/'+id);
      $("#ModalView").modal();
		});

  });
  function DataTables(set=null){
    console.log(set);
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
          d.activation 	= 'active'
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
  function add_data(){

    $("#head_title").html("<b>ADD BRAND</b>");
    $("#view").load(siteurl+active_controller+'modal_Process/add/');
    $("#ModalView").modal();

  }

  function selCountry(){
    var id_sup = $(this).data('id_supplier');
    $("#head_title2").html("<b>ADD SUPPLIER</b>");
    $("#view2").load(siteurl+active_controller+'modal_Helper/selcountry/'+id_sup);
    $("#ModalView2").modal();

  }

</script>
