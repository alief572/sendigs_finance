<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<link rel="stylesheet" href="<?= base_url('assets/css/petty-cash-pelaporan.css') ?>">

<div class="box petty-cash-pelaporan">
    <div class="pelaporan-intro">
        <h2>Daftar pelaporan</h2>
        <p>Tinjau pengeluaran per periode, status pelaporan, dan catatan penolakan.</p>
    </div>
    <div class="box-header">
        <div class="row pelaporan-filters">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filter-company">Company</label>
                    <select id="filter-company" class="form-control input-sm">
                        <option value="">Semua company</option>
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
                        <option value="">Semua status</option>
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
                        <i class="fa fa-filter" aria-hidden="true"></i> Terapkan filter
                    </button>
                    <button type="button" class="btn btn-sm btn-default" id="btn-reset-filter">
                        <i class="fa fa-refresh" aria-hidden="true"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive" tabindex="0" role="region" aria-label="Daftar pelaporan petty cash">
            <table id="table-pelaporan" class="table">
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>No Pelaporan</th>
                        <th>Periode</th>
                        <th>Company</th>
                        <th>Pencatatan</th>
                        <th>Total periode (Rp)</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
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
