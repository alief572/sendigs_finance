<?php
$ENABLE_ADD = $addPermission;
?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .btn {
        border-radius: 10px;
    }

    .dropdown-menu {
        top: 100%;
        position: absolute;
        overflow: auto;
    }

    .filter-actions {
        margin-top: 25px;
    }

    .save_btn_modal:disabled {
        cursor: not-allowed;
        opacity: 0.65;
    }

    .balance-indicator-green {
        color: green !important;
        font-weight: bold;
    }

    .balance-indicator-red {
        color: red !important;
        font-weight: bold;
    }
</style>
<div id="alert_edit" class="alert alert-success alert-dismissable" style="padding: 15px; display: none;"></div>
<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="company_filter">Company</label>
                    <select name="company_filter" id="company_filter" class="form-control form-control-sm select2">
                        <option value="">- Semua Company -</option>
                        <?php if (!empty($companies)): ?>
                            <?php foreach ($companies as $row): ?>
                                <option value="<?= $row['id_company'] ?>"><?= $row['nm_company'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group filter-actions">
                    <button type="button" class="btn btn-sm btn-primary" onclick="search_jurnal()"><i class="fa fa-search"></i> Search</button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="reset_search_jurnal()"><i class="fa fa-refresh"></i> Reset</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <table id="table_jurnal_petty_cash" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center">Tanggal</th>
                    <th class="text-center">No Transaksi</th>
                    <th class="text-center">Company</th>
                    <th class="text-center">COA</th>
                    <th class="text-center">Nama COA</th>
                    <th class="text-center">Keterangan</th>
                    <th class="text-center">Debit</th>
                    <th class="text-center">Kredit</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
        </table>
    </div>
    <!-- /.box-body -->
</div>

<!-- Modal Detail Jurnal -->
<div class="modal modal-default fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 150vh;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-book"></span> Detail Jurnal Petty Cash</h4>
            </div>
            <form action="" method="post" id="frm-data">
                <div class="modal-body" id="ModalView">
                </div>
                <div class="modal-footer">
                    <?php if ($ENABLE_ADD): ?>
                        <button type="submit" class="btn btn-primary save_btn_modal"><i class="fa fa-save"></i> Posting Jurnal</button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fa fa-times"></i> Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DataTables -->
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- page script -->
<script type="text/javascript">
    $(document).ready(function() {
        initDataTable();
        select2();
    });

    function formatNumber(num) {
        if (!num || num == 0) return '0';
        return parseInt(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function initDataTable(company) {
        if ($.fn.DataTable.isDataTable('#table_jurnal_petty_cash')) {
            $('#table_jurnal_petty_cash').DataTable().destroy();
        }

        $('#table_jurnal_petty_cash').DataTable({
            ajax: {
                url: '<?= site_url("jurnal_payment_petty_cash/get_data_jurnal") ?>',
                type: "POST",
                dataType: "JSON",
                data: function(d) {
                    d.company = company || $('#company_filter').val();
                }
            },
            columns: [{
                    data: 'no'
                },
                {
                    data: 'tanggal'
                },
                {
                    data: 'no_transaksi'
                },
                {
                    data: 'company'
                },
                {
                    data: 'coa'
                },
                {
                    data: 'nm_coa'
                },
                {
                    data: 'keterangan'
                },
                {
                    data: 'debit',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'kredit',
                    render: function(data) {
                        return formatNumber(data);
                    }
                },
                {
                    data: 'action'
                }
            ],
            columnDefs: [{
                    targets: [0, 9],
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    targets: [1, 2, 3, 4, 5],
                    className: 'text-center'
                },
                {
                    targets: [7, 8],
                    className: 'text-right'
                }
            ],
            responsive: true,
            processing: true,
            serverSide: true,
            stateSave: true,
            paging: true,
            destroy: true,
            searchDelay: 500,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ],
            pageLength: 10,
            order: [
                [0, 'desc']
            ],
            language: {
                emptyTable: "No data available in table"
            }
        });
    }

    function search_jurnal() {
        var company = $('#company_filter').val();
        initDataTable(company);
    }

    function reset_search_jurnal() {
        $('#company_filter').val('').trigger('change');
        initDataTable();
    }

    /**
     * Client-side balance validation - redundant check on rendered modal HTML.
     * Parses footer totals from the modal table to verify debit == kredit.
     * Returns object: { isBalance: bool, totalDebit: number, totalKredit: number }
     */
    function validateBalanceFromModal() {
        var $tfoot = $('#ModalView table tfoot tr');
        if ($tfoot.length === 0) {
            return {
                isBalance: false,
                totalDebit: 0,
                totalKredit: 0
            };
        }

        var $cells = $tfoot.find('th.text-right');
        if ($cells.length < 2) {
            return {
                isBalance: false,
                totalDebit: 0,
                totalKredit: 0
            };
        }

        // Parse Indonesian formatted numbers (dot as thousand separator)
        var rawDebit = $cells.eq(0).text().trim().replace(/\./g, '');
        var rawKredit = $cells.eq(1).text().trim().replace(/\./g, '');

        var totalDebit = parseInt(rawDebit, 10) || 0;
        var totalKredit = parseInt(rawKredit, 10) || 0;

        return {
            isBalance: (totalDebit === totalKredit && (totalDebit + totalKredit) > 0),
            totalDebit: totalDebit,
            totalKredit: totalKredit
        };
    }

    /**
     * Apply balance visual indicator and button state on modal.
     * Green = balance, Red = tidak balance.
     */
    function applyBalanceIndicator(isBalance) {
        var $btn = $('.save_btn_modal');
        var $footerCells = $('#ModalView table tfoot tr th.text-right');

        // Remove previous indicator classes
        $footerCells.removeClass('balance-indicator-green balance-indicator-red');

        if (isBalance) {
            $btn.prop('disabled', false);
            $footerCells.addClass('balance-indicator-green');
        } else {
            $btn.prop('disabled', true);
            $footerCells.addClass('balance-indicator-red');
        }
    }

    function view_detail(id) {
        $.ajax({
            type: 'post',
            url: '<?= site_url("jurnal_payment_petty_cash/get_detail_jurnal") ?>',
            data: {
                'id': id
            },
            cache: false,
            dataType: 'json',
            success: function(result) {
                if (result && result.html) {
                    $('#ModalView').html(result.html);
                } else {
                    $('#ModalView').html('<p class="text-center text-muted">Tidak ada data jurnal</p>');
                }
                $('#dialog-popup').modal('show');

                // Server-side balance check
                if (result && result.is_balance === false) {
                    $('.save_btn_modal').prop('disabled', true);
                } else {
                    $('.save_btn_modal').prop('disabled', false);
                }

                // Client-side redundant balance validation (safety net)
                var balanceCheck = validateBalanceFromModal();
                applyBalanceIndicator(balanceCheck.isBalance);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal memuat detail jurnal. Silakan coba lagi.',
                    timer: 3000,
                    allowOutsideClick: false
                });
            }
        });
    }

    $(document).on('submit', '#frm-data', function(e) {
        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Konfirmasi Posting',
            text: 'Apakah Anda yakin ingin memposting jurnal ini?',
            showCancelButton: true,
            confirmButtonText: 'Ya, Posting',
            cancelButtonText: 'Batal',
            allowOutsideClick: false
        }).then(function(result) {
            if (result.isConfirmed) {
                var data = $('#frm-data').serialize();

                // Loading state
                var $saveBtn = $('.save_btn_modal');
                var originalBtnText = $saveBtn.html();
                $saveBtn.html('<i class="fa fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

                $.ajax({
                    type: 'post',
                    url: '<?= site_url("jurnal_payment_petty_cash/save_posting_jurnal") ?>',
                    data: data,
                    cache: false,
                    dataType: 'json',
                    timeout: 30000,
                    success: function(result) {
                        $saveBtn.html(originalBtnText).prop('disabled', false);

                        if (result.status == true || result.status == 1) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: result.msg || 'Jurnal berhasil diposting',
                                timer: 3000,
                                allowOutsideClick: false
                            }).then(function() {
                                $('#dialog-popup').modal('hide');
                                initDataTable();
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Gagal!',
                                text: result.msg || 'Proses posting gagal. Silakan coba lagi.',
                                allowOutsideClick: false
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $saveBtn.html(originalBtnText).prop('disabled', false);

                        var errorMsg = 'Terjadi kesalahan. Silakan coba lagi.';
                        if (status === 'timeout') {
                            errorMsg = 'Request timeout. Silakan periksa status jurnal sebelum mencoba kembali.';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMsg,
                            allowOutsideClick: false
                        });
                    }
                });
            }
        });
    });

    function select2() {
        $('.select2').select2({
            width: '100%'
        });
    }
</script>