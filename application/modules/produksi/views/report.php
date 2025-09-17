<?php
    $ENABLE_ADD     = has_permission('Daily_Production_Report.Add');
    $ENABLE_MANAGE  = has_permission('Daily_Production_Report.Manage');
    $ENABLE_VIEW    = has_permission('Daily_Production_Report.View');
    $ENABLE_DELETE  = has_permission('Daily_Production_Report.Delete');

    echo get_before_costcenter_warehouse('I2000011', 'CC2000009');
?>

<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css')?>">

<div class="box">
	<div class="box-header">
		<span class="pull-right">
      <a class="btn btn-success btn-sm" href="<?= base_url('produksi/download_excel') ?>" title="Add"> <i class="fa fa-download">&nbsp;</i>Download Excel</a>
		</span>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<table id="example1" class="table table-bordered table-striped" width='100%'>
		<thead>
		<tr>
			<th width="5">#</th>
      <th>Production Date</th>
      <th>Costcenter</th>
      <th>Project Name</th>
			<th>Product Name</th>
      <th>Qty Plan</th>
      <th>Qty Oke</th>
      <!--<th>Qty Failed</th>-->
		</tr>
		</thead>

		<tbody></tbody>
		</table>
	</div>
	<!-- /.box-body -->
</div>


<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" style='width:80%; '>
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
    DataTables();
  });

    function DataTables(){
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
          url : siteurl +'produksi/data_side_daily_report_produksi',
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
