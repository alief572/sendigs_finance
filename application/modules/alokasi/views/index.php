<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    .form-inline {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    body {
        background-color: #f5f5f5;
        padding-top: 20px;
    }

    .main-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-bottom: 30px;
    }

    .form-inline .form-group {
        margin-right: 15px;
        margin-bottom: 10px;
    }

    .form-inline .form-group label {
        margin-right: 8px;
        font-weight: 600;
        color: #555;
    }

    .form-inline .form-control {
        border-radius: 4px;
        border: 1px solid #ddd;
        padding: 8px 12px;
        font-size: 14px;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-inline .form-control:focus {
        border-color: #66afe9;
        outline: 0;
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 8px rgba(102, 175, 233, .6);
    }

    .btn {
        border-radius: 6px;
        font-weight: 500;
        margin-right: 8px;
        transition: all 0.2s ease-in-out;
    }

    .btn-primary {
        background-color: #337ab7;
        border-color: #2e6da4;
    }

    .btn-primary:hover {
        background-color: #286090;
        border-color: #204d74;
    }

    .btn-success {
        background-color: #5cb85c;
        border-color: #4cae4c;
    }

    .btn-success:hover {
        background-color: #449d44;
        border-color: #398439;
    }

    .form-control-sm {
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
    }

    .search-section {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .search-section h4 {
        color: #495057;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .date-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .date-input-wrapper {
        display: flex;
        align-items: center;
        margin-right: 15px;
    }

    .date-input-wrapper label {
        margin-right: 8px;
        white-space: nowrap;
    }

    @media (max-width: 768px) {
        .form-inline .form-group {
            display: block;
            margin-bottom: 15px;
        }

        .date-group {
            flex-direction: column;
            align-items: stretch;
        }

        .date-input-wrapper {
            margin-bottom: 10px;
        }
    }
</style>

<div class="box">
    <div class="box-header">
        <div class="col-md-2">
            <div class="form-inline">
                <div class="form-group">
                    <label for="startDate2">Start:</label>
                    <input type="date" class="form-control form-control-sm" id="startDate2" name="startDate2" style="width: auto !important;">
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-inline">
                <div class="form-group">
                    <label for="endDate2">End:</label>
                    <input type="date" class="form-control form-control-sm" id="endDate2" name="endDate2" style="width: auto !important;">
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <select class="form-control form-control-sm search_bank">
                <option value="">- Bank -</option>
                <?php foreach ($data_bank as $bank) : ?>
                    <option value="<?= $bank['id'] ?>"><?= $bank['nama_bank'] . ' - ' . $bank['rekening'] . ' - ' . $bank['nama'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button type="button" class="btn btn-sm btn-primary search_data"><i class="fa fa-search"></i> Search</button>
            <button type="button" class="btn btn-sm btn-danger clear_data"><i class="fa fa-refresh"></i> Reset</button>
            <!-- <button type="button" class="btn btn-sm btn-warning btn_print"><i class="fa fa-print"></i> Print</button> -->
            <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#dialog-popup"><i class="fa fa-plus"></i> Upload Rekening Koran</button>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-bordered" id="table_list">
            <thead>
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center">Tanggal Transaksi Bank</th>
                    <th class="text-center">Bank</th>
                    <th class="text-center">Keterangan</th>
                    <th class="text-center">Total Debit</th>
                    <th class="text-center">Total Credit</th>
                    <th class="text-center">Saldo Akhir</th>
                    <th class="text-center">Status Alokasi</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>
</div>
<div class="modal" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-money"></span>&nbsp;Upload Rekening Koran</h4>
            </div>
            <form action="" id="frm_data" enctype="multipart/form-data">
                <div class="modal-body" id="MyModalBody">
                    <div class="form-group">
                        <label for="">Bank</label>
                        <select name="bank" id="" class="form-control form-control-sm bank" required>
                            <option value="">- Bank -</option>
                            <?php foreach ($data_bank as $bank) : ?>
                                <option value="<?= $bank['id'] ?>"><?= $bank['nama_bank'] . ' - ' . $bank['rekening'] . ' - ' . $bank['nama'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="jenis_bank">
                    </div>
                    <div class="form-group">
                        <label for="">Upload File CSV</label>
                        <input type="file" name="upload_csv" id="" class="form-control form-control-sm" accept=".csv" required>
                    </div>
                    <br><br>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center" width="16.7%">Tanggal Transaksi</th>
                                <th class="text-center" width="16.7%">Reference No</th>
                                <th class="text-center" width="16.7%">Description</th>
                                <th class="text-center" width="16.7%">Credit</th>
                                <th class="text-center" width="16.7%">Debit</th>
                                <th class="text-center" width="16.7%">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="list_alokasi_bank">

                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> Proses</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove"></span> Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal" id="dialog-popup-alokasi" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title"><span class="fa fa-money"></span>&nbsp;Alokasi Split Transaksi</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="split_transaction_id" name="id" value="">

                <!-- Transaction Detail Table (read-only) -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Tanggal Transaksi</th>
                                <th class="text-center">Reference No</th>
                                <th class="text-center">Description</th>
                                <th class="text-center">Credit</th>
                                <th class="text-center">Debit</th>
                                <th class="text-center">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center" id="split_tanggal" style="font-size: 12px;">-</td>
                                <td class="text-center" id="split_reference_no" style="font-size: 12px;">-</td>
                                <td class="text-center" id="split_deskripsi" style="font-size: 12px;">-</td>
                                <td class="text-right" id="split_credit" style="font-size: 12px;">Rp. 0.00</td>
                                <td class="text-right" id="split_debit" style="font-size: 12px;">Rp. 0.00</td>
                                <td class="text-right" id="split_balance" style="font-size: 12px;">Rp. 0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Dynamic Split Allocation Table -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="split-table">
                        <thead>
                            <tr>
                                <th class="text-center" width="50">No</th>
                                <th class="text-center" width="250">Jenis Alokasi</th>
                                <th class="text-center" width="200">Nominal</th>
                                <th class="text-center" width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="split-table-body">
                            <!-- Dynamic rows will be inserted here -->
                        </tbody>
                    </table>
                </div>

                <!-- Tambah Baris Button -->
                <button type="button" class="btn btn-sm btn-primary" id="btn-tambah-baris" onclick="addSplitRow()">
                    <i class="fa fa-plus"></i> Tambah Baris
                </button>

                <hr>

                <!-- Validation Indicator Section -->
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Total Dialokasikan</label>
                                <p id="split_total_allocated" class="form-control-static" style="font-weight: bold;">Rp. 0</p>
                            </div>
                            <div class="col-md-4">
                                <label>Sisa</label>
                                <p id="split_sisa" class="form-control-static" style="font-weight: bold;">Rp. 0</p>
                            </div>
                            <div class="col-md-4">
                                <label>Status</label>
                                <p id="split_status" class="form-control-static" style="font-weight: bold; color: red;">Tidak Sesuai</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="saveSplitAlokasi()"><i class="fa fa-check"></i> Simpan</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Batal</button>
            </div>
        </div>
    </div>
</div>

<!-- Template for Jenis Alokasi dropdown options (used by JavaScript) -->
<!-- Modal View Split Alokasi -->
<div class="modal" id="dialog-view-split" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title"><span class="fa fa-eye"></span>&nbsp;Detail Alokasi Split</h4>
            </div>
            <div class="modal-body">
                <!-- Transaction Detail Table -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Tanggal Transaksi</th>
                                <th class="text-center">Reference No</th>
                                <th class="text-center">Description</th>
                                <th class="text-center">Credit</th>
                                <th class="text-center">Debit</th>
                                <th class="text-center">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center" id="view_tanggal" style="font-size: 12px;">-</td>
                                <td class="text-center" id="view_reference_no" style="font-size: 12px;">-</td>
                                <td class="text-center" id="view_deskripsi" style="font-size: 12px;">-</td>
                                <td class="text-right" id="view_credit" style="font-size: 12px;">Rp. 0.00</td>
                                <td class="text-right" id="view_debit" style="font-size: 12px;">Rp. 0.00</td>
                                <td class="text-right" id="view_balance" style="font-size: 12px;">Rp. 0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr>
                <h5><strong>Detail Split Alokasi:</strong></h5>

                <!-- Split Detail Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="text-center" width="50">No</th>
                                <th class="text-center">Jenis Alokasi</th>
                                <th class="text-center">Nominal</th>
                            </tr>
                        </thead>
                        <tbody id="view-split-body">
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">Total:</th>
                                <th class="text-right" id="view_split_total">Rp. 0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> Tutup</button>
            </div>
        </div>
    </div>
</div>

<script type="text/html" id="template-jenis-alokasi">
    <option value="">-- Pilih Jenis Alokasi --</option>
    <option value="1">Penerimaan Piutang</option>
    <option value="2">Unlocated Penerimaan</option>
    <option value="3">Pengembalian Kasbon</option>
    <option value="4">Mutasi</option>
    <option value="5">Transaksi Bank</option>
    <option value="6">Pembayaran</option>
    <option value="7">Alokasi Kalibrasi</option>
</script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script>
    $(document).ready(function() {
        DataTables();

        $('.bank').chosen({
            width: '100%'
        });
        $('.search_bank').chosen({
            width: '450px'
        });

        // Event delegation: recalculate total when any .split-nominal input changes
        $('#split-table-body').on('keyup', '.split-nominal', function() {
            recalculateTotal();
        });
    });

    // View split alokasi click handler
    $(document).on('click', '.btn_view_split', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        viewSplitAlokasi(id);
    });

    /**
     * View split allocation detail in a read-only modal.
     */
    function viewSplitAlokasi(id) {
        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'get_view_split_alokasi',
            data: {
                'id': id
            },
            dataType: 'json',
            cache: false,
            success: function(result) {
                if (result.status == '1') {
                    // Populate transaction header
                    $('#view_tanggal').text(result.tanggal_transaksi);
                    $('#view_reference_no').text(result.reference_no || '-');
                    $('#view_deskripsi').text(result.keterangan);
                    $('#view_credit').text('Rp. ' + number_format(result.nominal_kredit, 2));
                    $('#view_debit').text('Rp. ' + number_format(result.nominal_debit, 2));
                    $('#view_balance').text('Rp. ' + number_format(result.saldo, 2));

                    // Populate split detail table
                    var rows = '';
                    var total = 0;
                    result.splits.forEach(function(split, index) {
                        total += split.nominal;
                        rows += '<tr>';
                        rows += '<td class="text-center">' + (index + 1) + '</td>';
                        rows += '<td>' + split.jenis_alokasi + '</td>';
                        rows += '<td class="text-right">Rp. ' + number_format(split.nominal, 2) + '</td>';
                        rows += '</tr>';
                    });
                    $('#view-split-body').html(rows);
                    $('#view_split_total').text('Rp. ' + number_format(total, 2));

                    // Show modal
                    $('#dialog-view-split').modal('show');
                } else {
                    swal({
                        type: 'warning',
                        title: 'Warning !',
                        text: result.msg
                    });
                }
            },
            error: function() {
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Please try again later !'
                });
            }
        });
    }

    $(document).on('submit', '#frm_data', function(e) {
        e.preventDefault();
        swal({
            type: 'warning',
            title: 'Warning !',
            text: 'File rekening akan di upload !',
            showCancelButton: true
        }, function(next) {
            if (next) {
                var formdata = new FormData($('#frm_data')[0]);

                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'upload_rekening_koran',
                    data: formdata,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    cache: false,
                    success: function(result) {
                        if (result.status == '1') {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.msg,
                                timer: 3000
                            }, function(lanjut) {
                                var enc_id = result.id_header.replace(/\//g, '-O-');
                                window.location.href = siteurl + active_controller + 'review_upload/' + enc_id;
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Warning !',
                                text: result.msg
                            });
                        }
                    },
                    error: function(result) {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Please try again later !'
                        });
                    }
                })
            }
        });
    });

    // Split alokasi form handler is now managed by saveSplitAlokasi() function

    $(document).on('change', '.bank', function() {
        var bank = $(this).val();

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'get_bank_sesuai_alokasi',
            data: {
                'bank': bank
            },
            cache: false,
            success: function(result) {
                $('.list_alokasi_bank').html(result);
            },
            error: function(result) {
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Please try again later !'
                });
            }
        });

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'get_jenis_bank',
            data: {
                'bank': bank
            },
            dataType: 'json',
            cache: false,
            success: function(result) {
                $('input[name="jenis_bank"]').val(result.jenis_bank);
            }
        });
    });

    $(document).on('click', '.btn_alokasi', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        openSplitModal(id);
    });

    $(document).on('click', '.search_data', function() {
        var startDate = $('#startDate2').val();
        var endDate = $('#endDate2').val();
        var bank = $('.search_bank').val();

        DataTables(startDate, endDate, bank);
    });

    $(document).on('click', '.clear_data', function() {
        $('#startDate2').val('');
        $('#endDate2').val('');
        $('.search_bank').val('');

        $('.search_bank').trigger('chosen:updated');

        DataTables();
    });

    $(document).on('click', '.btn_print', function() {
        var start_date = $('#startDate2').val();
        var end_date = $('#endDate2').val();
        var bank = $('.search_bank').val();

        window.open(siteurl + active_controller + 'printAlokasi?start=' + start_date + '&end' + end_date + '&bank=' + bank, '_blank');
    });

    function DataTables(startDate = null, endDate = null, bank = null) {
        var DataTables = $('#table_list').dataTable({
            serverSide: true,
            process: true,
            stateSave: true,
            destroy: true,
            paging: true,
            ajax: {
                type: 'post',
                url: siteurl + active_controller + 'get_alokasi',
                dataType: 'json',
                data: function(d) {
                    d.startDate = startDate;
                    d.endDate = endDate;
                    d.bank = bank;
                }
            },
            columns: [{
                    data: 'no'
                },
                {
                    data: 'tanggal_transaksi'
                },
                {
                    data: 'bank'
                },
                {
                    data: 'keterangan'
                },
                {
                    data: 'debit'
                },
                {
                    data: 'kredit'
                },
                {
                    data: 'saldo'
                },
                {
                    data: 'status_alokasi'
                },
                {
                    data: 'action'
                }
            ]
        });
    }

    /**
     * Initialize autoNumeric on a given input element with Indonesian format
     * (dot as thousands separator, comma as decimal separator, no currency sign)
     */
    function initAutoNumeric(el) {
        $(el).autoNumeric('init', {
            aDec: ',',
            aSep: '.',
            aSign: '',
            mDec: '2'
        });
    }

    /**
     * Format a numeric value as Indonesian currency string: Rp. X.XXX.XXX,XX
     */
    function formatRupiah(value) {
        var num = parseFloat(value) || 0;
        var parts = num.toFixed(2).split('.');
        var intPart = parts[0];
        var decPart = parts[1];
        var formatted = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return 'Rp. ' + formatted + ',' + decPart;
    }

    /**
     * Format number with thousands separator (dot) and decimal (comma).
     * Returns string like "30,000,000.00"
     */
    function number_format(value, decimals) {
        var num = parseFloat(value) || 0;
        var parts = num.toFixed(decimals).split('.');
        var intPart = parts[0];
        var decPart = parts[1] || '';
        var formatted = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return formatted + '.' + decPart;
    }

    /**
     * Add a new split allocation row to the table.
     * Appends a row with empty dropdown and nominal = 0,
     * initializes autoNumeric, shows delete buttons on all rows,
     * and recalculates the total.
     */
    function addSplitRow() {
        var rowCount = $('#split-table-body tr.split-row').length;
        var newRowNum = rowCount + 1;
        var optionsHtml = $('#template-jenis-alokasi').html();

        var row = '<tr class="split-row">';
        row += '<td class="text-center">' + newRowNum + '</td>';
        row += '<td><select class="form-control split-jenis-alokasi">' + optionsHtml + '</select></td>';
        row += '<td><input type="text" class="form-control split-nominal text-right" value=""></td>';
        row += '<td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-remove-split" onclick="removeSplitRow(this)"><i class="fa fa-trash"></i></button></td>';
        row += '</tr>';

        $('#split-table-body').append(row);

        // Initialize autoNumeric on the new nominal input and set value to 0
        var newInput = $('#split-table-body tr.split-row').last().find('.split-nominal');
        initAutoNumeric(newInput);
        newInput.autoNumeric('set', 0);

        // Show delete buttons on all rows (since more than 1 row exists)
        $('#split-table-body .btn-remove-split').show();

        // Recalculate totals
        recalculateTotal();
    }

    /**
     * Remove a split allocation row from the table.
     * Re-numbers remaining rows, hides delete button if only 1 row remains,
     * and recalculates the total.
     *
     * @param {HTMLElement} btn - The delete button element that was clicked
     */
    function removeSplitRow(btn) {
        // Find the parent <tr> of the clicked button and remove it
        $(btn).closest('tr.split-row').remove();

        // Re-number the remaining rows
        $('#split-table-body tr.split-row').each(function(index) {
            $(this).find('td').first().text(index + 1);
        });

        // If only 1 row remains, hide its delete button
        var remainingRows = $('#split-table-body tr.split-row');
        if (remainingRows.length === 1) {
            remainingRows.find('.btn-remove-split').hide();
        }

        // Recalculate totals
        recalculateTotal();
    }

    /**
     * Recalculate the total allocated amount and update validation indicators.
     * Loops through all .split-nominal inputs, sums their unformatted values,
     * and updates the Total Dialokasikan, Sisa, and Status displays.
     */
    function recalculateTotal() {
        var sum = 0;

        // Loop through all split-nominal inputs and sum their unformatted values
        $('#split-table-body .split-nominal').each(function() {
            var val = parseFloat($(this).autoNumeric('get')) || 0;
            sum += val;
        });

        // Get the total transaction amount
        var total = window.splitTotalTransaksi || 0;

        // Calculate remaining (sisa)
        var sisa = total - sum;

        // Update displays with formatted values
        $('#split_total_allocated').text(formatRupiah(sum));
        $('#split_sisa').text(formatRupiah(sisa));

        // Update status indicator
        if (Math.abs(sum - total) < 0.01) {
            $('#split_status').text('Sesuai \u2713').css('color', 'green');
        } else {
            $('#split_status').text('Tidak Sesuai (selisih: ' + formatRupiah(Math.abs(sisa)) + ')').css('color', 'red');
        }
    }

    /**
     * Validate the split allocation form before saving.
     * Checks: all nominals > 0, all jenis_alokasi selected, total matches transaction total.
     * Shows SweetAlert warning on validation failure.
     *
     * @return {boolean} true if form is valid, false otherwise
     */
    function validateSplitForm() {
        // 1. Check all nominal values > 0
        var allNominalsValid = true;
        $('#split-table-body .split-nominal').each(function() {
            var val = parseFloat($(this).autoNumeric('get')) || 0;
            if (val <= 0) {
                allNominalsValid = false;
                return false; // break out of .each()
            }
        });
        if (!allNominalsValid) {
            swal({
                type: 'warning',
                title: 'Peringatan',
                text: 'Nominal harus lebih besar dari 0'
            });
            return false;
        }

        // 2. Check all jenis_alokasi dropdowns have a selected value
        var allJenisSelected = true;
        $('#split-table-body .split-jenis-alokasi').each(function() {
            if ($(this).val() === '' || $(this).val() === null) {
                allJenisSelected = false;
                return false; // break out of .each()
            }
        });
        if (!allJenisSelected) {
            swal({
                type: 'warning',
                title: 'Peringatan',
                text: 'Semua baris harus memiliki jenis alokasi'
            });
            return false;
        }

        // 3. Check total nominal equals total transaksi
        var sum = 0;
        $('#split-table-body .split-nominal').each(function() {
            var val = parseFloat($(this).autoNumeric('get')) || 0;
            sum += val;
        });
        var total = window.splitTotalTransaksi || 0;
        if (Math.abs(sum - total) >= 0.01) {
            swal({
                type: 'warning',
                title: 'Peringatan',
                text: 'Total nominal harus sama dengan total transaksi'
            });
            return false;
        }

        return true;
    }

    /**
     * Save split allocation data to the server.
     * Validates form, shows confirmation, collects data, and POSTs to save_split_alokasi.
     * On success: closes modal and reloads DataTable.
     * On error: shows error SweetAlert with server message.
     */
    function saveSplitAlokasi() {
        // 1. Validate form first - abort if invalid
        if (!validateSplitForm()) {
            return;
        }

        // 2. Count rows for confirmation message
        var rowCount = $('#split-table-body tr.split-row').length;

        // 3. Show SweetAlert confirmation
        swal({
            title: 'Konfirmasi',
            text: 'Simpan ' + rowCount + ' baris alokasi?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }, function(isConfirm) {
            if (isConfirm) {
                // 4. Collect data from all rows
                var splits = [];
                $('#split-table-body tr.split-row').each(function() {
                    var jenisAlokasi = $(this).find('.split-jenis-alokasi').val();
                    var nominal = $(this).find('.split-nominal').autoNumeric('get');
                    splits.push({
                        jenis_alokasi: jenisAlokasi,
                        nominal: nominal
                    });
                });

                // Get transaction ID
                var id = $('#split_transaction_id').val();

                // 5. AJAX POST to save
                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + 'save_split_alokasi',
                    data: {
                        id: id,
                        splits: splits
                    },
                    dataType: 'json',
                    cache: false,
                    success: function(result) {
                        if (result.status == '1') {
                            swal({
                                type: 'success',
                                title: 'Success !',
                                text: result.msg,
                                timer: 3000
                            }, function() {
                                $('#dialog-popup-alokasi').modal('hide');
                                DataTables();
                            });
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Warning !',
                                text: result.msg
                            });
                        }
                    },
                    error: function(result) {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Please try again later !'
                        });
                    }
                });
            }
        });
    }

    /**
     * Open the split allocation modal for a given transaction ID.
     * Makes AJAX POST to get_alokasi_split_detail, populates header,
     * initializes first allocation row, and shows the modal.
     */
    function openSplitModal(id) {
        $.ajax({
            type: 'post',
            url: siteurl + active_controller + 'get_alokasi_split_detail',
            data: {
                'id': id
            },
            dataType: 'json',
            cache: false,
            success: function(result) {
                if (result.status == '1') {
                    // Set hidden transaction ID
                    $('#split_transaction_id').val(result.id);

                    // Populate transaction detail table
                    $('#split_tanggal').text(result.tanggal_transaksi);
                    $('#split_reference_no').text(result.reference_no || '-');
                    $('#split_deskripsi').text(result.keterangan);
                    $('#split_credit').text('Rp. ' + number_format(result.nominal_kredit, 2));
                    $('#split_debit').text('Rp. ' + number_format(result.nominal_debit, 2));
                    $('#split_balance').text('Rp. ' + number_format(result.saldo, 2));

                    // Determine total (use kredit, fallback to debit if kredit is 0)
                    var total = parseFloat(result.nominal_kredit) || 0;
                    if (total === 0) {
                        total = parseFloat(result.nominal_debit) || 0;
                    }

                    // Store total globally for validation
                    window.splitTotalTransaksi = total;

                    // Clear existing rows
                    $('#split-table-body').html('');

                    // Add first allocation row with nominal = total
                    var rowNum = 1;
                    var optionsHtml = $('#template-jenis-alokasi').html();
                    var row = '<tr class="split-row">';
                    row += '<td class="text-center">' + rowNum + '</td>';
                    row += '<td><select class="form-control split-jenis-alokasi">' + optionsHtml + '</select></td>';
                    row += '<td><input type="text" class="form-control split-nominal text-right" value=""></td>';
                    row += '<td class="text-center"><button type="button" class="btn btn-xs btn-danger btn-remove-split" onclick="removeSplitRow(this)" style="display:none;"><i class="fa fa-trash"></i></button></td>';
                    row += '</tr>';

                    $('#split-table-body').html(row);

                    // Initialize autoNumeric on the nominal input and set value
                    var nominalInput = $('#split-table-body .split-nominal').first();
                    initAutoNumeric(nominalInput);
                    nominalInput.autoNumeric('set', total);

                    // Show the modal
                    $('#dialog-popup-alokasi').modal('show');

                    // Recalculate totals (will show "Sesuai" since first row = total)
                    if (typeof recalculateTotal === 'function') {
                        recalculateTotal();
                    }
                } else {
                    swal({
                        type: 'warning',
                        title: 'Warning !',
                        text: result.msg
                    });
                }
            },
            error: function(result) {
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Please try again later !'
                });
            }
        });
    }
</script>