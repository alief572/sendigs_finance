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
    <br>
    <div class="form-group row">
      <div class="col-md-1">
        <b>Costcenter</b>
      </div>
      <div class="col-md-3">
        <select name='costcenter' id='costcenter' class='form-control input-sm chosen-select'>
					<option value='0'>All Product</option>
					<?php
					foreach(get_product() AS $val => $valx){
						echo "<option value='".$valx['id_category2']."'>".strtoupper($valx['nama'])."</option>";
					}
					?>
				</select>
      </div>
    </div>
    <div class="form-group row">
      <div class="col-md-1">
        <b>Daycode</b>
      </div>
      <div class="col-md-3">
        <input type="text" name="daycode" id="daycode"  class="form-control input-md" placeholder="Daycode" autocomplete="off">
      </div>
    </div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped">
		<thead>
		<tr>
			<th width="5">#</th>
			<th>Product Name</th>
      <th>Daycode</th>
      <th>Remarks</th>
      <th>Option</th>
		</tr>
		</thead>

		<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style='width:90%; '>
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
<style media="screen">
.datepicker{
  cursor: pointer;
}
</style>
<!-- page script -->
<script type="text/javascript">

  $(document).ready(function(){
    var costcenter  = $("#costcenter").val();
    var daycode     = $("#daycode").val();
    DataTables(costcenter, daycode);

    $(document).on('change','#costcenter', function(){
      var costcenter  = $("#costcenter").val();
      var daycode     = $("#daycode").val();
      DataTables(costcenter, daycode);
    });

    $(document).on('keyup','#daycode', function(){
      var costcenter  = $("#costcenter").val();
      var daycode     = $("#daycode").val();
      DataTables(costcenter, daycode);
    });

    //action
    $(document).on('click', '.approve,.reject', function(e){
      e.preventDefault()
      var id = $(this).data('id');
      var tanda = $(this).data('tanda');
      // alert(id);
      // alert(tanda);
      // return false;
      swal({
        title: "Anda Yakin?",
        text: "Data akan di process!",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-info",
        confirmButtonText: "Ya",
        cancelButtonText: "Batal",
        closeOnConfirm: false
      },
      function(){
        $.ajax({
          type:'POST',
          url:siteurl+'quality_control/good_bad_action/'+id+'/'+tanda,
          dataType : "json",
          data:{'id':id},
          success:function(result){
            if(result.status == '1'){
              swal({
              title: "Sukses",
              text : "Data berhasil diproses.",
              type : "success"
              },
              function (){
                window.location.reload(true);
              });
            } else {
              swal({
                title : "Error",
                text  : "Data error. Gagal diproses",
                type  : "error"
              });
            }
          },
          error : function(){
            swal({
            title : "Error",
            text  : "Data error. Gagal request Ajax",
            type  : "error"
          });
          }
        })
      });
    });

  });

    function DataTables(costcenter = null, daycode = null){
  		var dataTable = $('#example1').DataTable({
  			// "scrollX": true,
  			// "scrollY": "500",
  			"scrollCollapse" : true,
  			"processing" : true,
  			"serverSide": true,
  			"stateSave" : true,
  			"bAutoWidth": true,
  			"destroy": true,
  			"responsive": true,
        "bFilter": false,
  			"oLanguage": {
  				"sSearch": "<b>Search : </b>",
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
  			"iDisplayLength": 10,
  			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
  			"ajax":{
  				url : siteurl+'quality_control/data_side_qc_final',
  				type: "post",
  				data: function(d){
  					d.costcenter = costcenter,
            d.daycode = daycode
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
