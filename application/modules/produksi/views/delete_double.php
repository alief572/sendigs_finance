
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
<div class="box">
	<div class="box-header">
    <div class="box-tool pull-left">

    </div>
	</div>
	<!-- /.box-header -->
	<div class="box-body table-responsive">
		<table id="example1" class="table table-bordered table-striped table-responsive" width='100%'>
		<thead>
		<tr>
			<th class='no-sort'>#</th>
      <th class='no-sort'>Id Produksi</th>
			<th class='no-sort'>Id Produksi Detail</th>
      <th class='no-sort'>Production Date</th>
			<th class='no-sort'>Costcenter Name</th>
      <th class='no-sort'>Created By</th>
      <th class='no-sort'>Created Date</th>
      <th class='no-sort'>#</th>
		</tr>
		</thead>

		<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>

  <div class="modal fade" id="ModalView">
  	<div class="modal-dialog" style='width:80%; '>
  		<div class="modal-content">
  		<div class="modal-header">
  			<h4 class="modal-title" id='head_title'>Default Modal</h4>
  			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
  			<span aria-hidden="true">&times;</span>
  			</button>
  		</div>
  		<div class="modal-body" id="view">

  		</div>
  		<div class="modal-footer justify-content-between">
  			<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
  		</div>
  		</div>
  		<!-- /.modal-content -->
  	</div>
  	<!-- /.modal-dialog -->
  </div>

</form>
<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">

$(document).ready(function(){
  DataTables();

  $(document).on('click','.detail', function(e){
			e.preventDefault();
			var id		= $(this).data('id');

			$("#head_title").html("Detail Input Produksi");
			$.ajax({
				type:'POST',
				url: base_url + active_controller+'/delete_double_modal/'+id,
				success:function(data){
					$("#ModalView").modal();
					$("#view").html(data);

				},
				error: function() {
					swal({
					title				: "Error Message !",
					text				: 'Connection Timed Out ...',
					type				: "warning",
					timer				: 5000,
					showCancelButton	: false,
					showConfirmButton	: false,
					allowOutsideClick	: false
					});
				}
			});
		});

    $(document).on('click', '#repeat_custom', function(e){
      e.preventDefault();

      if($('.check_part:checked').length == 0){
  			swal({
          title	: "Error Message!",
          text	: 'Checklist minimal 1 part ...',
          type	: "warning"
        });
  			$('#repeat_custom').prop('disabled',false);
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
            var formData 	=new FormData($('#form_proses_bro')[0]);
            var baseurl=siteurl+'produksi/delete_check_daycode';
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
                  window.location.href = siteurl +'produksi/delete_double'; 
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
				  url:siteurl+'produksi/delete_daycode_double_qc_new',
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
								// window.location.reload(true);
	              window.location.href = siteurl +'produksi/delete_double';
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

});

//tanggal = null, bulan = null, tahun = null, range = null, costcenter = null, product = null
  function DataTables(){
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
        url : siteurl +'produksi/data_side_delete_double',
        type: "post",
        // data: function(d){
        //   d.tanggal = $('#tanggal').val(),
        //   d.bulan = $('#bulan').val(),
        //   d.tahun = $('#tahun').val(),
        //   d.range = range,
        //   d.costcenter = costcenter,
        //   d.product = product
        // },
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
