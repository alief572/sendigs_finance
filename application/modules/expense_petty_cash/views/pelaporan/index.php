<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<<<<<<< HEAD
<style>
    .filter-actions {
        margin-top: 25px;
    }
</style>

<div class="box">
    <div class="box-header">
        <div class="row">
=======
<link rel="stylesheet" href="<?= base_url('assets/css/petty-cash-pelaporan.css') ?>">

<div class="box petty-cash-pelaporan">
    <div class="pelaporan-intro">
        <h2>Daftar pelaporan</h2>
        <p>Tinjau pengeluaran per periode, status pelaporan, dan catatan penolakan.</p>
    </div>
    <div class="box-header">
        <div class="row pelaporan-filters">
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
            <div class="col-md-3">
                <div class="form-group">
                    <label for="filter-company">Company</label>
                    <select id="filter-company" class="form-control input-sm">
<<<<<<< HEAD
                        <option value="">Semua</option>
=======
                        <option value="">Semua company</option>
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
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
<<<<<<< HEAD
                        <option value="">Semua</option>
=======
                        <option value="">Semua status</option>
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
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
<<<<<<< HEAD
                        <i class="fa fa-search"></i> Filter
                    </button>
                    <button type="button" class="btn btn-sm btn-default" id="btn-reset-filter">
                        <i class="fa fa-refresh"></i> Reset
=======
                        <i class="fa fa-filter" aria-hidden="true"></i> Terapkan filter
                    </button>
                    <button type="button" class="btn btn-sm btn-default" id="btn-reset-filter">
                        <i class="fa fa-refresh" aria-hidden="true"></i> Reset
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
<<<<<<< HEAD
        <div class="table-responsive">
            <table id="table-pelaporan" class="table table-bordered table-striped">
=======
        <div class="table-responsive" tabindex="0" role="region" aria-label="Daftar pelaporan petty cash">
            <table id="table-pelaporan" class="table">
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
                <thead>
                    <tr>
                        <th width="30">No</th>
                        <th>No Pelaporan</th>
                        <th>Periode</th>
                        <th>Company</th>
<<<<<<< HEAD
                        <th>Jumlah Pencatatan</th>
                        <th>Grand Total Periode</th>
                        <th>Status</th>
                        <th width="150">Action</th>
=======
                        <th>Pencatatan</th>
                        <th>Total periode (Rp)</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
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

<<<<<<< HEAD
<script src="<?= base_url('assets/js/modules/expense_petty_cash/pelaporan.js') ?>"></script>
=======
<script src="<?= base_url('assets/js/modules/expense_petty_cash/pelaporan.js') ?>"></script>
>>>>>>> b127c8e19d1f7bed84ac03533b27c503f3837a47
