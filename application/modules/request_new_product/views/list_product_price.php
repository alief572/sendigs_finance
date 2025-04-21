<?php
$ENABLE_ADD     = has_permission('Quotation.Add');
$ENABLE_MANAGE  = has_permission('Quotation.Manage');
$ENABLE_VIEW    = has_permission('Quotation.View');
$ENABLE_DELETE  = has_permission('Quotation.Delete');

?>
<style type="text/css">
    thead input {
        width: 100%;
    }
</style>
<div id='alert_edit' class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<input type="hidden" id="no_surat_product_list" value="<?= $results['no_surat']; ?>">
<div class="box">
    <table id="example1" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Product Type</th>
                <th>Product Master</th>
                <th>Variant</th>
                <th>Price List (USD)</th>
                <th>Price List (IDR)</th>
                <?php if ($ENABLE_MANAGE) : ?>
                    <th>Action</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>
<!-- /.box-body -->
</div>

<!-- awal untuk modal dialog -->
<!-- Modal -->

<!-- /.modal -->

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>

<!-- page script -->
<script>
    status = null;
    var dataTable = $('#example1').DataTable({
        "processing": true,
        "serverSide": true,
        "stateSave": true,
        "autoWidth": true,
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
            [10, 20, 50, 100, 150],
            [10, 20, 50, 100, 150]
        ],
        "ajax": {
            url: base_url + active_controller + 'data_side_product_price',
            type: "post",
            data: function(d) {
                d.status = status
            },
            cache: false,
            error: function() {
                $(".my-grid-error").html("");
                $("#my-grid").append('<tbody class="my-grid-error"><tr><th colspan="3">No data found in the server</th></tr></tbody>');
                $("#my-grid_processing").css("display", "none");
            }
        }
    });
</script>