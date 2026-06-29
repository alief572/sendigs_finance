<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<style>
    .filter-actions {
        margin-top: 25px;
    }
</style>

<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filter-company">Company</label>
                    <select id="filter-company" class="form-control input-sm">
                        <option value="">Semua</option>
                        <option value="STM">STM</option>
                        <option value="VUCA">VUCA</option>
                        <option value="SUSTAIN">SUSTAIN</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filter-status">Status</label>
                    <select id="filter-status" class="form-control input-sm">
                        <option value="">Semua</option>
                        <option value="draft">Draft</option>
                        <option value="waiting">Waiting</option>
                        <option value="approved">Approved</option>
                        <option value="reject">Reject</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group filter-actions">
                    <button type="button" class="btn btn-sm btn-primary" id="btn-filter">
                        <i class="fa fa-search"></i> Filter
                    </button>
                    <button type="button" class="btn btn-sm btn-default" id="btn-reset-filter">
                        <i class="fa fa-refresh"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table id="table-pelaporan" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>No Pelaporan</th>
                        <th>Periode</th>
                        <th>Company</th>
                        <th>Jumlah Pencatatan</th>
                        <th>Grand Total Periode</th>
                        <th>Status</th>
                        <th width="150">Action</th>
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
    var PERMISSIONS = {
        has_add: <?= json_encode($has_add) ?>,
        has_manage: <?= json_encode($has_manage) ?>
    };
    var BASE_URL = '<?= site_url('expense_petty_cash/') ?>';
</script>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="<?= base_url('assets/js/modules/expense_petty_cash/pelaporan.js') ?>"></script>