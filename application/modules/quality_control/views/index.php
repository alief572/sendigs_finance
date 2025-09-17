<?php
    $ENABLE_ADD     = has_permission('Inspection_In_Process.Add');
    $ENABLE_MANAGE  = has_permission('Inspection_In_Process.Manage');
    $ENABLE_VIEW    = has_permission('Inspection_In_Process.View');
    $ENABLE_DELETE  = has_permission('Inspection_In_Process.Delete');
?>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.1.7/css/fixedHeader.dataTables.min.css">
<?php
// echo get_last_costcenter_warehouse('I2000134');
?>
<form id="form_prosee" method="post"><br>
  <div class="box">
  	<div class="box-header">
      <br>
      <div class="form-group row">
        <div class="col-md-1">
          <b>Costcenter</b>
        </div>
        <div class="col-md-3">
          <select name='costcenter' id='costcenter' class='form-control input-sm chosen-select'>
  					<option value='0'>All Costcenter</option>
  					<?php
  					foreach(get_costcenter() AS $val => $valx){
  						echo "<option value='".$valx['id_costcenter']."'>".strtoupper($valx['nama_costcenter'])."</option>";
  					}
  					?>
  				</select>
        </div>
      </div>
      <div class="form-group row">
        <div class="col-md-1">
          <b>Product</b>
        </div>
        <div class="col-md-3">
          <select name='product' id='product' class='form-control input-sm chosen-select'>
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

      <div class="form-group row">
        <div class="col-md-1"></div>
        <div class="col-md-11">
          <button type="button" class="btn btn-primary" style='min-width:100px;' name="approve" id="approve" value='good'><i class="fa fa-check"></i> Approve</button>
          <!--<button type="button" class="btn btn-danger" style='min-width:100px;' name="reject" id="reject" value='bad'><i class="fa fa-close"></i> Reject</button>-->
        </div>
      </div>
  	</div>
  	<!-- /.box-header -->
  	<div class="box-body">
  		<table id="example1" class="table table-bordered table-striped" width='100%'>
  		<thead>
  		<tr>
  			<th class='no-sort'><center><input type='checkbox' name='chk_all' id='chk_all'></center></th>
        <th>Costcenter</th>
  			<th>Product Name</th>
        <th>Daycode</th>
        <th>Remarks</th>
  		</tr>
  		</thead>

  		<tbody></tbody>
  		</table>
  	</div>
  	<!-- /.box-body -->
  </div>
</form>

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
<script src="https://cdn.datatables.net/fixedheader/3.1.7/js/dataTables.fixedHeader.min.js"></script>
<style media="screen">
.datepicker{
  cursor: pointer;
}
</style>
<!-- page script -->
<script type="text/javascript">

  $(document).ready(function(){
    var costcenter  = $("#costcenter").val();
    var product     = $("#product").val();
    var daycode     = $("#daycode").val();
    DataTables(costcenter, daycode, product);

    $(document).on('change','#costcenter, #product', function(){
      var costcenter  = $("#costcenter").val();
      var product     = $("#product").val();
      var daycode     = $("#daycode").val();
      DataTables(costcenter, daycode, product);
    });

    $(document).on('keyup','#daycode', function(){
      var costcenter  = $("#costcenter").val();
      var product     = $("#product").val();
      var daycode     = $("#daycode").val();
      DataTables(costcenter, daycode, product);
    });

    $("#chk_all").click(function(){
			$('input:checkbox').not(this).prop('checked', this.checked);
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
                text  : "Data error. Gagal proses data",
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

    $(document).on('click', '#approve, #reject', function(){
  		var ket				= $(this).val();
      var costcenter		        = $('#costcenter').val();

      if(costcenter == '0' ){
        swal({
          title	: "Error Message!",
          text	: 'Must be the same cost center, select first ...',
          type	: "warning"
        });
        return false;
      }

  		if($('input[type=checkbox]:checked').length == 0){
  			swal({
  			  title	: "Error Message!",
  			  text	: 'Checklist milimal satu terlebih dahulu',
  			  type	: "warning"
  			});
  			$('#approvedFD_All').prop('disabled',false);
  			return false;
  		}

  		// alert(ket);
  		// return false;

  		swal({
  		  title: "Apakah anda yakin ???",
  		  text: "Approve/Reject QC",
  		  type: "warning",
  		  showCancelButton: true,
  		  confirmButtonClass: "btn-danger",
  		  confirmButtonText: "Ya, Proses !",
  		  cancelButtonText: "Tidak, Batalkan !",
  		  closeOnConfirm: true,
  		  closeOnCancel: false
  		},
  		function(isConfirm) {
  			if (isConfirm) {
  				// loading_spinner();
  				var formData  	= new FormData($('#form_prosee')[0]);
  				$.ajax({
  					url			: base_url+active_controller+'/good_bad_action_check/'+ket,
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
  						}
  						else if(data.status == 0){
  							swal({
  							  title	: "Save Failed!",
  							  text	: data.pesan,
  							  type	: "warning",
  							  timer	: 7000,
  							  showCancelButton	: false,
  							  showConfirmButton	: false,
  							  allowOutsideClick	: false
  							});
  							window.location.href = base_url + active_controller;
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
  });

    function DataTables(costcenter = null, daycode = null, product = null){
  		var dataTable = $('#example1').DataTable({
        "serverSide": true,
  			"stateSave" : true,
  			"bAutoWidth": true,
  			"destroy": true,
  			"processing": true,
  			"responsive": true,
  			"fixedHeader": {
          "headerOffset": 50,
  				"header": true,
  				"footer": true
  			},
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
  			"aLengthMenu": [[10, 25, 50, 100, 250, 500, 750, 1000], [10, 25, 50, 100, 250, 500, 750, 1000]],
  			"ajax":{
  				url : siteurl+'quality_control/data_side_qc',
  				type: "post",
  				data: function(d){
  					d.costcenter = costcenter,
            d.daycode = daycode,
            d.product = product
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
