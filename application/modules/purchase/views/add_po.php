<?php
// print_r($results['supList']);
 ?>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
<div class="box box-primary">
    <div class="box-body">
        <br>
        <div class='form-group row'>
            <label class='label-control col-sm-2'><b>Supplier Name</b></label>
            <div class='col-sm-4'>
                <select id='id_supplier' name='id_supplier' class='form-control input-sm chosen-select' style='min-width:200px;'>
                    <option value='0'>Select Supplier</option>
                    <?php
                        foreach($results['supList'] AS $val => $valx){
                            echo "<option value='".$valx['id_supplier']."'>".strtoupper($valx['name_supplier'])."</option>";
                        }
                    ?>
                </select>
            </div>
        </div>
        <?php
			echo form_button(array('type'=>'button','class'=>'btn btn-sm btn-success','style'=>'min-width:100px; float:right; margin: 5px 0px 5px 0px;','value'=>'Create PO','content'=>'Create PO','id'=>'savePO')).' ';
		?>
        <br><br>
        <div class="box box-success">
            <br>
            <table class="table table-bordered table-striped" id="my-grid3" width='100%'>
                <thead>
                    <tr>
                        <th class="text-center no-sort" width='5%'>#</th>
                        <th class="text-center" width='10%'>No Plan</th>
                        <th class="text-center" width='15%'>Id Material</th>
                        <th class="text-center">Material Name</th>
                        <th class="text-center" width='8%'>Qty Packing</th>
                        <th class="text-center" width='8%'>Qty Material</th>
                        <th class="text-center" width='8%'>Purchase Packing</th>
                        <th class="text-center" width='8%'>Purchase Material</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
	</div>
</div>
</form>
<style>
	.chosen-container-active .chosen-single {
	     border: none;
	     box-shadow: none;
	}
	.chosen-container-single .chosen-single {
		height: 34px;
	    border: 1px solid #d2d6de;
	    border-radius: 0px;
	     background: none;
	    box-shadow: none;
	    color: #444;
	    line-height: 32px;
	}
	.chosen-container-single .chosen-single div{
		top: 5px;
	}
</style>
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>
<script src="<?= base_url('assets/js/jquery.maskMoney.js')?>"></script>
<script src="<?= base_url('assets/js/autoNumeric.js')?>"></script>
<script>
    // swal.close();
  $(document).ready(function(){
      DataTables3();
      $('.chosen-select').select2();
      $('.maskM').maskMoney();

      $(document).on('keyup', '.qty', function(e){
  			var no       = $(this).data('no');
        var konversi = getNum($(this).data('konversi'));
        var value    = getNum($(this).val());
        var hasil    = konversi * value;
        $("#qty_"+no).val(hasil.toFixed(2));
  		});

      $(document).on('click', '#savePO', function(){
        var id_supplier = $('#id_supplier').val();
        if( id_supplier == '0'){
          swal({
            title	: "Error Message!",
            text	: 'Supplier Not Select, please input first ...',
            type	: "warning"
          });
          $('#savePO').prop('disabled',false);
          return false;
        }

        if($('input[type=checkbox]:checked').length == 0){
          swal({
            title	: "Error Message!",
            text	: 'Checklist Minimal One Component',
            type	: "warning"
          });
          $('#savePO').prop('disabled',false);
          return false;
        }

        swal({
          title: "Are you sure?",
          text: "You will booking material planning be able to process again this data!",
          type: "warning",
          showCancelButton: true,
          confirmButtonClass: "btn-danger",
          confirmButtonText: "Yes, Process it!",
          cancelButtonText: "No, cancel process!",
          closeOnConfirm: false,
          closeOnCancel: false
        },
        function(isConfirm) {
          if (isConfirm) {
            // loading_spinner();
            var formData  	= new FormData($('#form_proses_bro')[0]);
            $.ajax({
              url			: base_url+'purchase/createPO',
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
                  window.location.href = base_url + active_controller+'/add_po';
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

  function DataTables3(){
    var dataTable = $('#my-grid3').DataTable({
      "processing": true,
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
      "aaSorting": [[ 2, "asc" ]],
      "columnDefs": [ {
        "targets": 'no-sort',
        "orderable": false,
      }],
      "sPaginationType": "simple_numbers",
      "iDisplayLength": 10,
      "aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
      "ajax":{
        url : base_url +'index.php/'+active_controller+'/data_side_request',
        type: "post",
        data: function(d){
          // d.kode_partner = $('#kode_partner').val()
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

  function getNum(val) {
    if (isNaN(val) || val == '') {
        return 0;
    }
    return parseFloat(val);
  }

</script>
