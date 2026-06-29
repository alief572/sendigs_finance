<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-check-circle"></i>&nbsp;Approval Pelaporan Petty Cash</h3>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table id="table-approval" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>No Pelaporan</th>
                        <th>Periode</th>
                        <th>Company</th>
                        <th class="text-center">Jumlah Pencatatan</th>
                        <th class="text-right">Grand Total</th>
                        <th width="80" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.box-body -->
</div>

<script>
    var BASE_URL = '<?= site_url('expense_petty_cash/') ?>';
</script>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="<?= base_url('assets/js/modules/expense_petty_cash/approval.js') ?>"></script>