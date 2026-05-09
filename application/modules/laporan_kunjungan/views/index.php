<?php
$ENABLE_ADD     = has_permission('Laporan_Kunjungan.Add');
$ENABLE_MANAGE  = has_permission('Laporan_Kunjungan.Manage');
$ENABLE_VIEW    = has_permission('Laporan_Kunjungan.View');
$ENABLE_DELETE  = has_permission('Laporan_Kunjungan.Delete');
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">

<style>
    .btn {
        border-radius: 10px;
    }
</style>

<div class="box">
    <div class="box-header">
        <h3 class="box-title">Daftar Project SPK</h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <table id="table_laporan_kunjungan" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th align="center">No</th>
                    <th align="center">No SPK</th>
                    <th align="center">Perusahaan</th>
                    <th align="center">Project</th>
                    <th align="center">Project Leader</th>
                    <th align="center">Konsultan</th>
                    <th align="center">Target Selesai</th>
                    <th align="center">Action</th>
                </tr>
            </thead>
        </table>
    </div>
    <!-- /.box-body -->
</div>

<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        DataTables();
    });

    function DataTables() {
        var dataTables = $('#table_laporan_kunjungan').dataTable({
            ajax: {
                url: '<?php echo base_url('laporan_kunjungan/get_data_spk'); ?>',
                type: "POST",
                dataType: "JSON"
            },
            columns: [
                { data: 'no' },
                { data: 'id_spk_budgeting' },
                { data: 'nm_customer' },
                { data: 'nm_project' },
                { data: 'nm_project_leader' },
                { data: 'nama_konsultan' },
                { data: 'target_selesai' },
                { data: 'option' }
            ],
            responsive: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            destroy: true,
            paging: true,
            language: {
                emptyTable: "Tidak ada data project SPK yang tersedia.",
                zeroRecords: "Tidak ada data yang cocok dengan pencarian."
            }
        });
    }
</script>
