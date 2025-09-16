<?php
    $ENABLE_ADD = has_permission('Master_Daycode.Add');
    $ENABLE_MANAGE = has_permission('Master_Daycode.Manage');
    $ENABLE_VIEW = has_permission('Master_Daycode.View');
    $ENABLE_DELETE = has_permission('Master_Daycode.Delete');
?>
<style type="text/css">
thead input {
	width: 100%;
}
</style>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css'); ?>">
<form action="#" method="POST" id="form_proses_bro" enctype="multipart/form-data">
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
		</div>
		<!-- /.box-header -->
		<div class="box-body">
			<table id="example1" class="table table-bordered table-striped" width='100%'>
				<thead>
					<tr class='bg-blue' >
						<th class="text-center">No</th>
						<th class="text-center">Daycode</th>
						<th class="text-center">Date</th>
						<th class="text-center">Sort</th>
						<th class="text-center">Created By</th>
						<th class="text-center">Created Date</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
		<!-- /.box-body -->
	</div>

 <!-- modal -->


</form>
<!-- DataTables -->

<style>
	.chosen-container{
		width: 100% !important;
		text-align : left !important;
	}

</style>

<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js')?>"></script>

<!-- page script -->
<script type="text/javascript">
	$(document).ready(function(){
    $('.chosen-select').select2();
    $('.datepicker').daterangepicker({
			showDropdowns: true,
			autoUpdateInput: false,
			locale: {
				cancelLabel: 'Clear'
			}
		});

    $('.datepicker').on('apply.daterangepicker', function(ev, picker) {
  			$(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
  			var range = $(this).val();
  			var tanggal = $('#tanggal').val();
        var bulan 	= $('#bulan').val();
        var tahun 	= $('#tahun').val();
  		  DataTables(tanggal, bulan, tahun, range);
		});

		$('.datepicker').on('cancel.daterangepicker', function(ev, picker) {
    		$(this).val('');
    		var range = $(this).val();
    		var tanggal = $('#tanggal').val();
        var bulan 	= $('#bulan').val();
        var tahun 	= $('#tahun').val();
    	  DataTables(tanggal, bulan, tahun, range);
		});

    var range 	= $('#date_range').val();
		var tanggal = $('#tanggal').val();
    var bulan 	= $('#bulan').val();
    var tahun 	= $('#tahun').val();
		DataTables(tanggal, bulan, tahun, range);

		$(document).on('change', '#tanggal', function(e){
    	var range 	= $('#date_range').val();
    	var tanggal = $('#tanggal').val();
    	var bulan 	= $('#bulan').val();
    	var tahun 	= $('#tahun').val();
    	DataTables(tanggal, bulan, tahun, range);
    });

    $(document).on('change', '#bulan', function(e){
  		var range 	= $('#date_range').val();
  		var tanggal = $('#tanggal').val();
  		var bulan 	= $('#bulan').val();
  		var tahun 	= $('#tahun').val();
  		DataTables(tanggal, bulan, tahun, range);
    });

    $(document).on('change', '#tahun', function(e){
			var range 	= $('#date_range').val();
			var tanggal = $('#tanggal').val();
			var bulan 	= $('#bulan').val();
			var tahun 	= $('#tahun').val();
			DataTables(tanggal, bulan, tahun, range);
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

	function DataTables(tanggal = null, bulan = null, tahun = null, range = null){
		var dataTable = $('#example1').DataTable({
      "scrollCollapse" : true,
			"serverSide": true,
			"processing": true,
			"stateSave" : true,
			"bAutoWidth": true,
			"destroy": true,
			"responsive": true,
			"fixedHeader": {
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
			"aaSorting"		: [[ 1, "asc" ]],
			"columnDefs"	: [ {
				"targets"	: 'no-sort',
				"orderable"	: false,
				}
			],
			"sPaginationType": "simple_numbers",
			"iDisplayLength": 10,
			"aLengthMenu": [[10, 20, 50, 100, 150], [10, 20, 50, 100, 150]],
			"ajax":{
				url : siteurl +'daycode/data_side',
				type: "post",
        data: function(d){
					d.tanggal = $('#tanggal').val(),
					d.bulan = $('#bulan').val(),
          d.tahun = $('#tahun').val(),
					d.range = range
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
