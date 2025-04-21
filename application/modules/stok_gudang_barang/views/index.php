<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
  <div class="box-header">
    <div class='form-group row'>
      <div class='col-sm-3'></div>
      <div class='col-sm-3'>
        <select name="id_warehouse" id="id_warehouse" class='form-control select2'>
          <?php
          foreach ($warehouse as $key => $value) {
            echo "<option value='" . $value['id'] . "'>" . strtoupper($value['nm_gudang']) . "</option>";
          }
          ?>
        </select>
      </div>
      <div class='col-sm-3'>
        <select name="id_category" id="id_category" class='form-control select2'>
          <option value="0">ALL CATEGORY</option>
          <?php
          foreach ($category as $key => $value) {
            echo "<option value='" . $value['id'] . "'>" . strtoupper($value['nm_category']) . "</option>";
          }
          ?>
        </select>
      </div>
      <div class='col-sm-2'>
        <input type="text" name='date_filter' id='date_filter' class='form-control datepicker text-center' data-role="datepicker2" readonly placeholder='Change Date'>
      </div>
      <div class='col-sm-1'>
        <button type='button' class='btn btn-sm btn-success' id='download_excel'><i class='fa fa-file-excel-o'></i> Download</button>
      </div>
    </div>
  </div>
  <!-- /.box-header -->
  <div class="box-body">
    <table id="example1" class="table table-bordered table-striped">
      <thead>
        <tr>
          <th class="text-center">#</th>
          <th class="text-center">Category</th>
          <th class="text-center">Code</th>
          <th class="text-center">Nama Barang</th>
          <th class="text-center">Warehouse</th>
          <th class="text-center no-sort">Unit Packing</th>
          <th class="text-center no-sort">Stok Packing</th>
          <th class="text-center no-sort">Convertion</th>
          <th class="text-center no-sort">Stok Unit</th>
          <!-- <th class="text-center no-sort">Booking</th>
            <th class="text-center no-sort">Available</th>
            <th class="text-center no-sort">Expired</th> -->
          <th class="text-center no-sort">History</th>
        </tr>
      </thead>

      <tbody></tbody>
    </table>
  </div>
  <!-- /.box-body -->
</div>

<!-- modal -->
<div class="modal fade" id="ModalView" style='overflow-y: auto;'>
  <div class="modal-dialog" style='width:95%; '>
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
<!-- modal -->

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<style>
  .datepicker {
    cursor: pointer;
  }
</style>
<!-- page script -->
<script type="text/javascript">
  $(function() {
    $('.select2').select2()
    var date_filter = $('#date_filter').val();
    var id_category = $('#id_category').val();
    var id_warehouse = $('#id_warehouse').val();
    DataTables(date_filter, id_category, id_warehouse);

    $('input[type="text"][data-role="datepicker2"]').datepicker({
      dateFormat: 'yy-mm-dd',
      changeMonth: true,
      changeYear: true,
      maxDate: '-1d',
      minDate: '2023-12-21',
      showButtonPanel: true,
      closeText: 'Clear',
      onClose: function(dateText, inst) {
        if ($(window.event.srcElement).hasClass('ui-datepicker-close')) {
          document.getElementById(this.id).value = '';
          var date_filter = $('#date_filter').val();
          var id_category = $('#id_category').val();
          var id_warehouse = $('#id_warehouse').val();
          DataTables(date_filter, id_category, id_warehouse);
        }
      }
    });
  });

  $(document).on('change', '#date_filter, #id_category, #id_warehouse', function(e) {
    e.preventDefault();
    var date_filter = $('#date_filter').val();
    var id_category = $('#id_category').val();
    var id_warehouse = $('#id_warehouse').val();
    DataTables(date_filter, id_category, id_warehouse);
  });

  $(document).on('click', '.hist', function(e) {
    e.preventDefault();
    var gudang = $(this).data('gudang');
    var material = $(this).data('material');

    $("#head_title").html("<b>HISTORY</b>");
    $.ajax({
      type: 'POST',
      url: base_url + active_controller + '/modal_history',
      data: {
        "gudang": gudang,
        "material": material
      },
      success: function(data) {
        $("#ModalView").modal();
        $("#view").html(data);

      },
      error: function() {
        swal({
          title: "Error Message !",
          text: 'Connection Timed Out ...',
          type: "warning",
          timer: 5000,
          showCancelButton: false,
          showConfirmButton: false,
          allowOutsideClick: false
        });
      }
    });
  });

  $(document).on('click', '.hist_excel', function(e) {
    e.preventDefault();
    var id_warehouse = $(this).data('gudang');
    var id_barang = $(this).data('material');

    var Links = base_url + active_controller + 'download_excel_history/' + id_warehouse + '/' + id_barang;
    window.open(Links, '_blank');
  });

  $(document).on('click', '#download_excel', function(e) {
    e.preventDefault();
    var id_warehouse = $("#id_warehouse").val();
    var id_category = $("#id_category").val();
    var date_filter = $("#date_filter").val();
    var tanggal = '0';
    if (date_filter != '') {
      var tanggal = date_filter
    }
    var Links = base_url + active_controller + 'download_excel/' + id_warehouse + '/' + id_category + '/' + date_filter;
    window.open(Links, '_blank');
  });

  function DataTables(date_filter = null, id_category = null, id_warehouse = null) {
    var dataTable = $('#example1').DataTable({
      "processing": true,
      "serverSide": true,
      "stateSave": true,
      "bAutoWidth": true,
      "destroy": true,
      "responsive": true,
      "aaSorting": [
        [1, "asc"]
      ],
      "columnDefs": [{
        "targets": 'no-sort',
        "orderable": false,
      }],
      "sPaginationType": "simple_numbers",
      "iDisplayLength": 10,
      "aLengthMenu": [
        [10, 25, 50, 100, 250, 500],
        [10, 25, 50, 100, 250, 500]
      ],
      "ajax": {
        url: siteurl + active_controller + 'data_side_stock',
        type: "post",
        data: function(d) {
          d.date_filter = date_filter,
            d.id_category = id_category,
            d.id_warehouse = id_warehouse
        },
        cache: false,
        error: function() {
          $(".my-grid-error").html("");
          $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
          $("#my-grid_processing").css("display", "none");
        }
      }
    });
  }
</script>