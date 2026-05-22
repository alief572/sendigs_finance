<?php
$ENABLE_ADD     = has_permission('Report_Piutang.Add');
$ENABLE_MANAGE  = has_permission('Report_Piutang.Manage');
$ENABLE_VIEW    = has_permission('Report_Piutang.View');
$ENABLE_DELETE  = has_permission('Report_Piutang.Delete');
?>

<style>
    .btn {
        border-radius: 10px;
    }

    .form-control {
        border-radius: 10px;
    }

    .btn {
        font-weight: bold;
    }

    #table_piutang th,
    #table_piutang td {
        vertical-align: middle;
        text-align: center;
        font-size: 12px;
    }

    #table_piutang td.text-right {
        text-align: right;
    }

    #table_piutang td.text-left {
        text-align: left;
    }

    .total-piutang-box {
        background: #f4f4f4;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px 15px;
        margin-bottom: 15px;
    }

    .total-piutang-box h4 {
        margin: 0;
        font-weight: bold;
    }

    .total-piutang-box .total-value {
        font-size: 20px;
        font-weight: bold;
        color: #3c8dbc;
    }
</style>

<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tanggal Dari</label>
                    <input type="text" name="tgl_dari" id="tgl_dari" class="form-control form-control-sm" placeholder="dd/mm/yyyy" value="<?= isset($tgl_dari) ? $tgl_dari : '' ?>" readonly>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tanggal Sampai</label>
                    <input type="text" name="tgl_sampai" id="tgl_sampai" class="form-control form-control-sm" placeholder="dd/mm/yyyy" value="<?= isset($tgl_sampai) ? $tgl_sampai : '' ?>" readonly>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>&nbsp;</label><br>
                    <button type="button" class="btn btn-sm btn-primary" id="btn_tampilkan"><i class="fa fa-search"></i> Tampilkan</button>
                    <button type="button" class="btn btn-sm btn-danger" id="btn_print"><i class="fa fa-print"></i> Print</button>
                    <button type="button" class="btn btn-sm btn-success" id="btn_excel"><i class="fa fa-file-excel-o"></i> Download Excel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-header -->

    <div class="box-body">
        <!-- Total Piutang Summary -->
        <div class="total-piutang-box">
            <div class="row">
                <div class="col-md-6">
                    <h4>Total Piutang</h4>
                </div>
                <div class="col-md-6 text-right">
                    <span class="total-value" id="total_piutang">0</span>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table id="table_piutang" class="table table-bordered">
                <thead>
                    <tr class="bg-blue">
                        <th width="30px">No</th>
                        <th width="120px">No SPK</th>
                        <th width="150px">Customer</th>
                        <th width="120px">Nilai Kontrak</th>
                        <th width="100px">No Invoice</th>
                        <th width="100px">Tanggal Invoice</th>
                        <th width="120px">Nilai Invoice</th>
                        <th width="100px">No Penerimaan</th>
                        <th width="100px">Tanggal Penerimaan</th>
                        <th width="120px">Nilai Penerimaan</th>
                        <th width="120px">Saldo Piutang</th>
                    </tr>
                </thead>
                <tbody id="tbody_piutang">
                    <tr>
                        <td colspan="11" class="text-center">Klik "Tampilkan" untuk menampilkan data</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.box-body -->
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Initialize jQuery UI datepicker with dd/mm/yyyy format
        $('#tgl_dari, #tgl_sampai').datepicker({
            dateFormat: 'dd/mm/yy',
            changeMonth: true,
            changeYear: true
        });

        // Tampilkan button click
        $('#btn_tampilkan').on('click', function() {
            loadData();
        });

        // Print button click
        $('#btn_print').on('click', function() {
            var tgl_dari = $('#tgl_dari').val();
            var tgl_sampai = $('#tgl_sampai').val();
            window.open(siteurl + active_controller + 'print_pdf?tgl_dari=' + encodeURIComponent(tgl_dari) + '&tgl_sampai=' + encodeURIComponent(tgl_sampai));
        });

        // Download Excel button click
        $('#btn_excel').on('click', function() {
            var tgl_dari = $('#tgl_dari').val();
            var tgl_sampai = $('#tgl_sampai').val();
            window.location.href = siteurl + active_controller + 'export_excel?tgl_dari=' + encodeURIComponent(tgl_dari) + '&tgl_sampai=' + encodeURIComponent(tgl_sampai);
        });
    });

    /**
     * Format number with dot (.) as thousands separator.
     * e.g. 4062561808 => "4.062.561.808"
     */
    function formatNumber(num) {
        if (num === null || num === undefined || num === '') return '0';
        var number = parseFloat(num);
        if (isNaN(number)) return '0';
        var isNegative = number < 0;
        number = Math.abs(Math.round(number));
        var result = number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return isNegative ? '-' + result : result;
    }

    /**
     * Format date from yyyy-mm-dd to dd-mm-yyyy.
     */
    function formatDate(dateStr) {
        if (!dateStr || dateStr === '' || dateStr === null) return '-';
        var parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        return parts[2] + '-' + parts[1] + '-' + parts[0];
    }

    /**
     * Load data via AJAX and render the hierarchical table.
     */
    function loadData() {
        var tgl_dari = $('#tgl_dari').val();
        var tgl_sampai = $('#tgl_sampai').val();

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'get_data',
            data: {
                tgl_dari: tgl_dari,
                tgl_sampai: tgl_sampai
            },
            dataType: 'json',
            beforeSend: function() {
                $('#tbody_piutang').html('<tr><td colspan="11" class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat data...</td></tr>');
            },
            success: function(response) {
                if (response.status === false) {
                    swal('Perhatian', response.message, 'warning');
                    $('#tbody_piutang').html('<tr><td colspan="11" class="text-center">Tidak ada data</td></tr>');
                    $('#total_piutang').text('0');
                    return;
                }

                // Update total piutang
                $('#total_piutang').text(formatNumber(response.total_piutang));

                // Render table
                renderTable(response.data);
            },
            error: function(xhr, status, error) {
                swal('Error', 'Gagal memuat data. Silakan coba lagi.', 'error');
                $('#tbody_piutang').html('<tr><td colspan="11" class="text-center text-red">Gagal memuat data</td></tr>');
            }
        });
    }

    /**
     * Render hierarchical table with rowspan for SPK and Invoice groups.
     */
    function renderTable(data) {
        var tbody = $('#tbody_piutang');
        tbody.empty();

        if (!data || data.length === 0) {
            tbody.html('<tr><td colspan="11" class="text-center">Tidak ada data</td></tr>');
            return;
        }

        var no = 1;

        for (var i = 0; i < data.length; i++) {
            var spk = data[i];
            var invoices = spk.invoices || [];

            // Calculate total rows for this SPK (for rowspan)
            var spkRowCount = 0;
            for (var j = 0; j < invoices.length; j++) {
                var payments = invoices[j].payments || [];
                spkRowCount += payments.length > 0 ? payments.length : 1;
            }
            if (spkRowCount === 0) spkRowCount = 1;

            var firstSpkRow = true;

            if (invoices.length === 0) {
                // SPK without invoices
                var row = '<tr>';
                row += '<td rowspan="1">' + no + '</td>';
                row += '<td rowspan="1" class="text-left">' + (spk.no_spk || '-') + '</td>';
                row += '<td rowspan="1" class="text-left">' + (spk.nm_customer || '-') + '</td>';
                row += '<td rowspan="1" class="text-right">' + formatNumber(spk.nilai_kontrak) + '</td>';
                row += '<td>-</td>';
                row += '<td>-</td>';
                row += '<td>-</td>';
                row += '<td>-</td>';
                row += '<td>-</td>';
                row += '<td>-</td>';
                row += '<td>-</td>';
                row += '</tr>';
                tbody.append(row);
                no++;
            } else {
                for (var j = 0; j < invoices.length; j++) {
                    var invoice = invoices[j];
                    var payments = invoice.payments || [];
                    var invoiceRowCount = payments.length > 0 ? payments.length : 1;

                    var firstInvoiceRow = true;

                    if (payments.length === 0) {
                        // Invoice without payments
                        var row = '<tr>';
                        if (firstSpkRow) {
                            row += '<td rowspan="' + spkRowCount + '">' + no + '</td>';
                            row += '<td rowspan="' + spkRowCount + '" class="text-left">' + (spk.no_spk || '-') + '</td>';
                            row += '<td rowspan="' + spkRowCount + '" class="text-left">' + (spk.nm_customer || '-') + '</td>';
                            row += '<td rowspan="' + spkRowCount + '" class="text-right">' + formatNumber(spk.nilai_kontrak) + '</td>';
                            firstSpkRow = false;
                        }
                        row += '<td rowspan="' + invoiceRowCount + '">' + (invoice.no_invoice || '-') + '</td>';
                        row += '<td rowspan="' + invoiceRowCount + '">' + formatDate(invoice.tanggal_invoice) + '</td>';
                        row += '<td rowspan="' + invoiceRowCount + '" class="text-right">' + formatNumber(invoice.nilai_invoice) + '</td>';
                        row += '<td>-</td>';
                        row += '<td>-</td>';
                        row += '<td>-</td>';
                        row += '<td rowspan="' + invoiceRowCount + '" class="text-right">' + formatNumber(invoice.saldo_piutang) + '</td>';
                        row += '</tr>';
                        tbody.append(row);
                    } else {
                        for (var k = 0; k < payments.length; k++) {
                            var payment = payments[k];
                            var row = '<tr>';

                            if (firstSpkRow) {
                                row += '<td rowspan="' + spkRowCount + '">' + no + '</td>';
                                row += '<td rowspan="' + spkRowCount + '" class="text-left">' + (spk.no_spk || '-') + '</td>';
                                row += '<td rowspan="' + spkRowCount + '" class="text-left">' + (spk.nm_customer || '-') + '</td>';
                                row += '<td rowspan="' + spkRowCount + '" class="text-right">' + formatNumber(spk.nilai_kontrak) + '</td>';
                                firstSpkRow = false;
                            }

                            if (firstInvoiceRow) {
                                row += '<td rowspan="' + invoiceRowCount + '">' + (invoice.no_invoice || '-') + '</td>';
                                row += '<td rowspan="' + invoiceRowCount + '">' + formatDate(invoice.tanggal_invoice) + '</td>';
                                row += '<td rowspan="' + invoiceRowCount + '" class="text-right">' + formatNumber(invoice.nilai_invoice) + '</td>';
                                firstInvoiceRow = false;
                            }

                            row += '<td>' + (payment.no_penerimaan || '-') + '</td>';
                            row += '<td>' + formatDate(payment.tanggal_penerimaan) + '</td>';
                            row += '<td class="text-right">' + formatNumber(payment.nilai_penerimaan) + '</td>';

                            if (k === 0) {
                                row += '<td rowspan="' + invoiceRowCount + '" class="text-right">' + formatNumber(invoice.saldo_piutang) + '</td>';
                            }

                            row += '</tr>';
                            tbody.append(row);
                        }
                    }
                }
                no++;
            }
        }
    }
</script>