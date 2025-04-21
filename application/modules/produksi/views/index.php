<?php
    $ENABLE_ADD     = has_permission('Input_Produksi.Add');
    $ENABLE_MANAGE  = has_permission('Input_Produksi.Manage');
    $ENABLE_VIEW    = has_permission('Input_Produksi.View');
    $ENABLE_DELETE  = has_permission('Input_Produksi.Delete');
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
    <div class="box-tool pull-left">
        <label>Search : &nbsp;&nbsp;&nbsp;</label>
        <input type="text" name="date_range" id="date_range" class="form-control input-md datepicker" style='margin-bottom:5px;' readonly="readonly" placeholder="Select Date">
        <select id='tanggal' name='tanggal' class='form-control input-sm chosen-select' style='width:100px;'>
          <option value='0'>All Date</option>
          <?php
          for($a=1; $a <= 31; $a++){
            echo "<option value='".$a."'>".$a."</option>";
          }
          ?>
        </select>
        <select id='bulan' name='bulan' class='form-control input-sm chosen-select' style='width:120px;'>
          <option value='0'>All Month</option>
                  <option value='1'>January</option>
                  <option value='2'>February</option>
                  <option value='3'>March</option>
                  <option value='4'>April</option>
                  <option value='5'>May</option>
                  <option value='6'>June</option>
                  <option value='7'>July</option>
                  <option value='8'>August</option>
                  <option value='9'>September</option>
                  <option value='10'>October</option>
                  <option value='11'>November</option>
                  <option value='12'>December</option>
        </select>
        <select id='tahun' name='tahun' class='form-control input-sm chosen-select' style='width:100px;'>
          <option value='0'>All Year</option>
          <?php
          $date = date('Y') + 5;
          for($a=2019; $a < $date; $a++){
            echo "<option value='".$a."'>".$a."</option>";
          }
          ?>
        </select><br>
        <button type='button'class="btn btn-sm btn-success" id='excel_report' style='margin-top:5px;width:100px;'>
          <i class="fa fa-print"></i> Print Excel
        </button>
    </div>

    <div class="box-tool pull-left" id='label_daycode_cek' style='margin-left: 30px;'>
        <label style='color:green;'>Check Double Daycode :<br></label>
        <?php
        if(!empty($double_daycode)){
          foreach($double_daycode AS $val => $valx){ $val++;
            $jml_doub = $valx['jumlah_double'] - 1;
            $ketX = ($valx['ket'] == 'good')?"<span style='color:red;'>SUDAH MELAWATI QC</span>":"<span style='color:green;'>BELUM MELEWATI QC</span>";
            echo "<span style='color:red;'><b><br>".$val.") ".strtoupper(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $valx['id_costcenter'])).", ".strtoupper(get_name('ms_inventory_category2', 'nama', 'id_category2', $valx['id_product']))." [".$valx['daycode']."], JUMLAH DOUBLE ".$jml_doub." ".$ketX."</b> <button type='button' id='deldaycode' data-id='".$valx['id']."'>Hapus</button></span>";
          }
        }
        else{
          echo "<br><label style='color:green;'><u>TIDAK ADA DAYCODE YANG DOUBLE.</u></label>";
        }
         ?>
    </div>

    <span class="pull-right">
      <a class="btn btn-danger btn-sm" href="<?= base_url('produksi/delete_double') ?>" target='_blank' title="Delete Checklist"> <i class="fa fa-trash">&nbsp;</i>Delete New Double Daycode</a>
      <a class="btn btn-primary btn-sm" href="<?= base_url('produksi/checked_product') ?>" target='_blank' title="Production Input Checklist"> <i class="fa fa-check">&nbsp;</i>Checklist Produksi Input</a>

			<?php if($ENABLE_ADD) : ?>
					<!--<a class="btn btn-success btn-sm" href="<?= base_url('produksi/add2') ?>" title="Add"> <i class="fa fa-plus">&nbsp;</i>Add</a>-->
          <a class="btn btn-success btn-sm" href="<?= base_url('produksi/add2_new') ?>" title="Add"> <i class="fa fa-plus">&nbsp;</i><b>Add New</b></a>
          <?php endif; ?>
    </span>
    <br><br>
    <br><br>
    <br><br>
    <span class="box-tool pull-right">
      <select name='costcenter' id='costcenter' class='form-control input-sm chosen-select'>
				<option value='0'>All Costcenter</option>
				<?php
				foreach(get_costcenter() AS $val => $valx){
					echo "<option value='".$valx['id_costcenter']."'>".strtoupper($valx['nama_costcenter'])."</option>";
				}
				?>
			</select>
      <select name='product' id='product' class='form-control input-sm chosen-select'>
				<option value='0'>All Product</option>
				<?php
				foreach(get_product() AS $val => $valx){
					echo "<option value='".$valx['id_category2']."'>".strtoupper($valx['nama'])."</option>";
				}
				?>
			</select>
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body table-responsive">
		<table id="example1" class="table table-bordered table-striped table-responsive" width='100%'>
  		<thead>
    		<tr>
    			<th width="5">#</th>
          <th >Production Date</th>
    			<th >Costcenter Name</th>
          <th class='no-sort'>Project</th>
          <th class='no-sort'>Product</th>
          <th class='no-sort'>Daycode</th>
          <th class='no-sort'>Remarks</th>
          <th class='no-sort'>Created By</th>
          <th class='no-sort'>Created Date</th>
          <th class='no-sort'>#</th>
    		</tr>
  		</thead>
  		<tbody></tbody>
		</table>
	</div>
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
$(document).ready(function(){
  $('.chosen-select').select2();
  $('#spinnerx').hide();
  $('.datepicker').daterangepicker({
    showDropdowns: true,
    autoUpdateInput: false,
    locale: {
      cancelLabel: 'Clear'
    }
  });

  var range 	= $('#date_range').val();
  var tanggal = $('#tanggal').val();
  var bulan 	= $('#bulan').val();
  var tahun 	= $('#tahun').val();
  var costcenter  = $('#costcenter').val();
  var product     = $('#product').val();
  DataTables(tanggal, bulan, tahun, range, costcenter, product);

  $.ajax({
    url			: siteurl+'produksi/insert_select_double_daycode',
    type		: "POST",
    cache		: false,
    dataType	: 'json',
    processData	: false,
    contentType	: false
  });

  $('.datepicker').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
      var range = $(this).val();
      var tanggal = $('#tanggal').val();
      var bulan 	= $('#bulan').val();
      var tahun 	= $('#tahun').val();
      var costcenter  = $('#costcenter').val();
      var product     = $('#product').val();
      DataTables(tanggal, bulan, tahun, range, costcenter, product);
  });

  $('.datepicker').on('cancel.daterangepicker', function(ev, picker) {
      $(this).val('');
      var range = $(this).val();
      var tanggal = $('#tanggal').val();
      var bulan 	= $('#bulan').val();
      var tahun 	= $('#tahun').val();
      var costcenter  = $('#costcenter').val();
      var product     = $('#product').val();
      DataTables(tanggal, bulan, tahun, range, costcenter, product);
  });

  $(document).on('change', '#tanggal, #bulan, #tahun, #costcenter, #product', function(e){
    var range 	= $('#date_range').val();
    var tanggal = $('#tanggal').val();
    var bulan 	= $('#bulan').val();
    var tahun 	= $('#tahun').val();
    var costcenter  = $('#costcenter').val();
    var product     = $('#product').val();
    DataTables(tanggal, bulan, tahun, range, costcenter, product);
  });


  $(document).on('click', '#excel_report', function(e){
    // loading_spinner();
  var range 	= $('#date_range').val();
  var tanggal = $('#tanggal').val();
  var bulan 	= $('#bulan').val();
  var tahun 	= $('#tahun').val();

  var tgl_awal 	= '0';
  var tgl_akhir 	= '0';
  if(range != ''){
    var sPLT 		= range.split(' - ');
    var tgl_awal 	= sPLT[0];
    var tgl_akhir 	= sPLT[1];
  }


  var Link	= base_url + active_controller +'/excel_report/'+tanggal+'/'+bulan+'/'+tahun+'/'+tgl_awal+'/'+tgl_akhir;
    window.open(Link);
  });
});

  //edit code
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

  $(document).on('click', '.delete', function(e){
		e.preventDefault()
		var id = $(this).data('id');
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data akan di hapus ?",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Ya, Hapus!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+'produksi/delete_daycode',
			  dataType : "json",
			  data:{'id':id},
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses",
						  text : "Data berhasil dihapus.",
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
	
	$(document).on('click', '#deldaycode', function(e){
		e.preventDefault()
		var id = $(this).data('id');
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data akan di hapus ?",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Ya, Hapus!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+'produksi/delete_daycode_double_lewat',
			  dataType : "json",
			  data:{'id':id},
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses",
						  text : "Data berhasil dihapus.",
						  type : "success"
						},
						function (){
							$('#label_daycode_cek').html(result.new_label);
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

	$(document).on('click', '.delete_qc', function(e){
		e.preventDefault()
		var id = $(this).data('id');
		// alert(id);
		swal({
		  title: "Anda Yakin?",
		  text: "Data akan di hapus ?",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-info",
		  confirmButtonText: "Ya, Hapus!",
		  cancelButtonText: "Batal",
		  closeOnConfirm: false
		},
		function(){
		  $.ajax({
			  type:'POST',
			  url:siteurl+'produksi/delete_daycode_double_qc',
			  dataType : "json",
			  data:{'id':id},
			  success:function(result){
				  if(result.status == '1'){
					 swal({
						  title: "Sukses",
						  text : "Data berhasil dihapus.",
						  type : "success"
						},
						function (){
							window.location.reload(true);
              // window.location.href = siteurl +'produksi/insert_select_double_daycode';
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

  $(document).on('click', '#update_cost', function(){
		swal({
		  title: "Update Double Daycode ?",
		  text: "Tunggu sampai selesai ... ",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonClass: "btn-danger",
		  confirmButtonText: "Ya, Update!",
		  cancelButtonText: "Tidak, Batalkan!",
		  closeOnConfirm: true,
		  closeOnCancel: false
		},
		function(isConfirm) {
			if (isConfirm) {
				// loading_spinner();
				$('#spinnerx').show();
				$.ajax({
					url			: siteurl+'produksi/insert_select_double_daycode',
					type		: "POST",
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
							$('#spinnerx').hide();
							// window.location.href = siteurl +'produksi';
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
							$('#spinnerx').hide();
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
						$('#spinnerx').hide();
					}
				});
			} else {
			swal("Cancelled", "Data can be process again :)", "error");
			return false;
			}
		});
	});

  function DataTables(tanggal = null, bulan = null, tahun = null, range = null, costcenter = null, product = null){
    var dataTable = $('#example1').DataTable({
      "serverSide": true,
      "processing": true,
      "stateSave" : true,
      "bAutoWidth": true,
      "destroy": true,
      "responsive": true,
      "fixedHeader": {
        "headerOffset": 50,
        "header": true,
        "footer": true
      },
      "oLanguage": {
        "sSearch": "<b>Search only daycode : </b>",
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
      "aaSorting"		: [[ 1, "desc" ]],
      "columnDefs"	: [ {
        "targets"	: 'no-sort',
        "orderable"	: false,
        }
      ],
      "sPaginationType": "simple_numbers",
      "iDisplayLength": 10,
      "aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
      "ajax":{
        url : siteurl +'produksi/data_side_input_produksi',
        type: "post",
        data: function(d){
          d.tanggal = $('#tanggal').val(),
          d.bulan = $('#bulan').val(),
          d.tahun = $('#tahun').val(),
          d.range = range,
          d.costcenter = costcenter,
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
