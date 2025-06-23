<?php
$ENABLE_ADD     = has_permission('Plan_Tagih.Add');
$ENABLE_MANAGE  = has_permission('Plan_Tagih.Manage');
$ENABLE_VIEW    = has_permission('Plan_Tagih.View');
$ENABLE_DELETE  = has_permission('Plan_Tagih.Delete');
?>
<!-- <link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>"> -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">

<style>
    .btn {
        border-radius: 10px;
    }

    .dropdown-menu {
        top: 100%;
        position: absolute;
        overflow: auto;
    }
</style>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <div class="box-header">

    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <table id="table_penawaran" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center" width="5%">No.</th>
                    <th class="text-center" width="15%">Company</th>
                    <th class="text-center" width="15%">No. SPK</th>
                    <th class="text-center" width="20%">Customer</th>
                    <th class="text-center" width="15%">Project</th>
                    <th class="text-center" width="15%">Project Leader</th>
                    <th class="text-center" width="15%">Sales</th>
                    <th class="text-center" width="10%">Status</th>
                    <th class="text-center" width="15%">Action</th>
                </tr>
            </thead>

        </table>
    </div>
    <!-- /.box-body -->
</div>
<div id="form-data"></div>
<!-- DataTables -->
<!-- <script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script> -->

<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        DataTables();
    });

    function DataTables() {
        // var dataTables = $('#table_penawaran').dataTable();
        // dataTables.destroy();

        var dataTables = $('#table_penawaran').dataTable({
            ajax: {
                url: siteurl + active_controller + 'get_data_spk',
                type: "POST",
                dataType: "JSON",
                data: function(d) {

                }
            },
            columns: [{
                    data: 'no',
                },
                {
                    data: 'company'
                },
                {
                    data: 'no_spk'
                },
                {
                    data: 'customer'
                },
                {
                    data: 'project'
                },
                {
                    data: 'project_leader'
                },
                {
                    data: 'sales'
                },
                {
                    data: 'status'
                },
                {
                    data: 'option'
                }
            ],
            responsive: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            destroy: true,
            paging: true
        });
    }
</script>
<script src="<?= base_url('assets/js/basic.js') ?>"></script>