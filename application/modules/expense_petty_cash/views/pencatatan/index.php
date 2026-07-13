<link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.css') ?>">

<div class="box">
    <div class="box-header">
        <?php if ($has_add) : ?>
            <button class="btn btn-success btn-sm" type="button" onclick="showPilihPettyCash()">
                <i class="fa fa-plus"></i> Tambah Pencatatan
            </button>
        <?php endif; ?>
        <button class="btn btn-primary btn-sm" type="button" id="btn-buat-pelaporan" disabled>
            <i class="fa fa-file-text-o"></i> Buat Pelaporan
        </button>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div class="table-responsive">
            <table id="table-pencatatan" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th width="30"><input type="checkbox" id="check-all"></th>
                        <th width="30">No</th>
                        <th>No Pencatatan</th>
                        <th>Tanggal</th>
                        <th>Company</th>
                        <th>Request By</th>
                        <th>Keterangan</th>
                        <th>Nominal</th>
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

<!-- Modal Pilih Petty Cash -->
<div class="modal fade" id="modal-pilih-petty-cash" tabindex="-1" role="dialog" aria-labelledby="modal-pilih-petty-cash-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #00a65a, #00c0ef); color: #fff; border-radius: 4px 4px 0 0;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.8;"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modal-pilih-petty-cash-label"><i class="fa fa-money"></i> Pilih Master Petty Cash</h4>
            </div>
            <div class="modal-body" style="padding: 20px 25px;">
                <p class="text-muted" style="margin-bottom: 15px; font-size: 13px;">
                    <i class="fa fa-info-circle"></i> Pilih petty cash yang akan digunakan untuk pencatatan pengeluaran:
                </p>
                <div id="petty-cash-list-container">
                    <div class="text-center" style="padding: 30px;"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i></div>
                </div>
            </div>
            <div class="modal-footer" style="background: #f7f7f7; border-top: 1px solid #eee;">
                <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
            </div>
        </div>
    </div>
</div>

<style>
    .petty-cash-card {
        display: block;
        padding: 15px 18px;
        margin-bottom: 10px;
        border: 1px solid #e0e0e0;
        border-left: 4px solid #00a65a;
        border-radius: 4px;
        background: #fff;
        text-decoration: none !important;
        color: #333 !important;
        transition: all 0.2s ease;
        position: relative;
    }

    .petty-cash-card:hover {
        border-left-color: #00c0ef;
        background: #f0faff;
        box-shadow: 0 2px 8px rgba(0, 192, 239, 0.15);
        transform: translateY(-1px);
    }

    .petty-cash-card:active {
        transform: translateY(0);
        box-shadow: 0 1px 4px rgba(0, 192, 239, 0.1);
    }

    .petty-cash-card .pc-icon {
        float: left;
        width: 42px;
        height: 42px;
        line-height: 42px;
        text-align: center;
        background: linear-gradient(135deg, #00a65a, #3c8dbc);
        color: #fff;
        border-radius: 50%;
        font-size: 18px;
        margin-right: 14px;
    }

    .petty-cash-card .pc-info {
        overflow: hidden;
    }

    .petty-cash-card .pc-name {
        font-size: 15px;
        font-weight: 600;
        margin: 0 0 3px 0;
        color: #333;
    }

    .petty-cash-card .pc-desc {
        font-size: 12px;
        color: #999;
        margin: 0;
    }

    .petty-cash-card .pc-budget {
        display: inline-block;
        margin-top: 8px;
        padding: 3px 10px;
        background: #e8f5e9;
        color: #2e7d32;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .petty-cash-card:hover .pc-budget {
        background: #c8e6c9;
    }

    .petty-cash-card .pc-arrow {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #ccc;
        font-size: 16px;
        transition: all 0.2s ease;
    }

    .petty-cash-card:hover .pc-arrow {
        color: #00c0ef;
        right: 12px;
    }
</style>

<script>
    var PERMISSIONS = {
        has_add: <?= json_encode($has_add) ?>,
        has_manage: <?= json_encode($has_manage) ?>,
        has_delete: <?= json_encode($has_delete) ?>
    };
    var BASE_URL = '<?= site_url('expense_petty_cash/') ?>';
</script>

<!-- DataTables -->
<script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables/dataTables.bootstrap.min.js') ?>"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    /**
     * Show modal and load list of petty cash masters from server
     */
    function showPilihPettyCash() {
        $('#petty-cash-list-container').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
        $('#modal-pilih-petty-cash').modal('show');

        $.ajax({
            type: 'GET',
            url: BASE_URL + 'get_petty_cash_list',
            dataType: 'json',
            success: function(response) {
                if (response.status == 1 && response.data.length > 0) {
                    var html = '';
                    $.each(response.data, function(i, item) {
                        html += '<a href="' + BASE_URL + 'create/' + item.id + '" class="petty-cash-card">';
                        html += '<div class="pc-icon"><i class="fa fa-money"></i></div>';
                        html += '<div class="pc-info">';
                        html += '<p class="pc-name">' + item.nama + '</p>';
                        html += '<p class="pc-desc">' + (item.keterangan || 'Tidak ada keterangan') + '</p>';
                        html += '<span class="pc-budget"><i class="fa fa-wallet"></i> Budget: Rp ' + item.total_budget + '</span>';
                        html += '</div>';
                        html += '<span class="pc-arrow"><i class="fa fa-chevron-right"></i></span>';
                        html += '</a>';
                    });
                    $('#petty-cash-list-container').html(html);
                } else {
                    $('#petty-cash-list-container').html('<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Tidak ada data Master Petty Cash. Silakan buat terlebih dahulu.</div>');
                }
            },
            error: function() {
                $('#petty-cash-list-container').html('<div class="alert alert-danger"><i class="fa fa-times"></i> Gagal memuat data dari server.</div>');
            }
        });
    }
</script>

<script src="<?= base_url('assets/js/modules/expense_petty_cash/pencatatan.js') ?>"></script>