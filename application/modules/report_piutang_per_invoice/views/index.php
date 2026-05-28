<style>
    .datepicker {
        cursor: pointer;
    }

    .nav-tabs>li>a {
        font-weight: bold;
    }

    .tab-content {
        padding-top: 15px;
    }

    .instruction-message {
        text-align: center;
        padding: 40px 20px;
        color: #888;
        font-size: 14px;
    }

    .instruction-message i {
        font-size: 40px;
        margin-bottom: 10px;
        display: block;
    }

    .table-report th {
        text-align: center;
        vertical-align: middle;
    }

    .table-report td {
        vertical-align: middle;
    }

    .summary-section {
        margin-bottom: 15px;
    }

    .summary-section table td {
        padding: 8px 12px;
    }

    .bg-formula {
        background-color: #d6eaf8 !important;
    }

    .bg-data {
        background-color: #f2f2f2 !important;
    }
</style>

<div class="box">
    <div class="box-header">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tanggal Filter</label>
                    <input type="text" name="filter_date" id="filter_date" class="form-control datepicker text-center" placeholder="dd-mm-yyyy" readonly autocomplete="off">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>&nbsp;</label><br>
                    <button type="button" class="btn btn-sm btn-primary" id="btn-search" onclick="searchReport();">
                        <i class="fa fa-search"></i> Search
                    </button>
                    <button type="button" class="btn btn-sm btn-success" id="btn-download" onclick="downloadExcel();">
                        <i class="fa fa-file-excel-o"></i> Download Excel
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" role="tablist" id="company-tabs">
            <li class="nav-item active" role="presentation">
                <a class="nav-link active" id="tab-stm" data-toggle="tab" href="#panel-stm" role="tab" aria-controls="panel-stm" aria-selected="true" data-company-codes="1,6,7">STM</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="tab-vuca" data-toggle="tab" href="#panel-vuca" role="tab" aria-controls="panel-vuca" aria-selected="false" data-company-codes="4">VUCA</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="tab-sustain" data-toggle="tab" href="#panel-sustain" role="tab" aria-controls="panel-sustain" aria-selected="false" data-company-codes="3">SUSTAIN</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- STM Tab -->
            <div class="tab-pane active" id="panel-stm" role="tabpanel" aria-labelledby="tab-stm">
                <!-- Instruction message (shown when no date selected) -->
                <div class="instruction-message" id="instruction-stm">
                    <i class="fa fa-calendar"></i>
                    <p>Silakan pilih tanggal filter terlebih dahulu untuk menampilkan data piutang.</p>
                </div>
                <!-- Table container (hidden until date selected) -->
                <div class="table-container" id="table-container-stm" style="display: none;">
                    <!-- Summary Section STM -->
                    <div class="summary-section" id="summary-stm" style="margin-bottom: 15px;">
                        <table class="table table-bordered">
                            <tbody>
                                <tr class="bg-formula">
                                    <td width="250"><strong>Summary Piutang Per Invoice</strong></td>
                                    <td class="text-right" id="summary-piutang-stm">-</td>
                                </tr>
                                <tr class="bg-formula">
                                    <td><strong>Summary Uninvoiced</strong></td>
                                    <td class="text-right" id="summary-uninvoiced-stm">-</td>
                                </tr>
                                <tr class="bg-formula">
                                    <td><strong>Summary Sisa Piutang Per SPK</strong></td>
                                    <td class="text-right" id="summary-sisa-piutang-stm">-</td>
                                </tr>
                                <tr class="bg-formula">
                                    <td><strong>Total Piutang</strong></td>
                                    <td class="text-right" id="summary-total-piutang-stm"><strong>-</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-report" id="table-stm">
                            <thead>
                                <tr class="bg-blue">
                                    <th>Customer</th>
                                    <th>No SPK</th>
                                    <th>Nominal Project</th>
                                    <th>TOP</th>
                                    <th>Rincian TOP</th>
                                    <th>Tgl Invoice</th>
                                    <th>No Invoice</th>
                                    <th>Nilai Invoice</th>
                                    <th>Tgl Bayar</th>
                                    <th>Nilai Bayar</th>
                                    <th>Piutang Per Invoice</th>
                                    <th>Uninvoiced</th>
                                    <th>Total Sisa Piutang</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- VUCA Tab -->
            <div class="tab-pane" id="panel-vuca" role="tabpanel" aria-labelledby="tab-vuca">
                <!-- Instruction message (shown when no date selected) -->
                <div class="instruction-message" id="instruction-vuca">
                    <i class="fa fa-calendar"></i>
                    <p>Silakan pilih tanggal filter terlebih dahulu untuk menampilkan data piutang.</p>
                </div>
                <!-- Table container (hidden until date selected) -->
                <div class="table-container" id="table-container-vuca" style="display: none;">
                    <!-- Summary Section VUCA -->
                    <div class="summary-section" id="summary-vuca" style="margin-bottom: 15px;">
                        <table class="table table-bordered">
                            <tbody>
                                <tr class="bg-formula">
                                    <td width="250"><strong>Summary Piutang Per Invoice</strong></td>
                                    <td class="text-right" id="summary-piutang-vuca">-</td>
                                </tr>
                                <tr class="bg-formula">
                                    <td><strong>Summary Uninvoiced</strong></td>
                                    <td class="text-right" id="summary-uninvoiced-vuca">-</td>
                                </tr>
                                <tr class="bg-formula">
                                    <td><strong>Summary Sisa Piutang Per SPK</strong></td>
                                    <td class="text-right" id="summary-sisa-piutang-vuca">-</td>
                                </tr>
                                <tr class="bg-formula">
                                    <td><strong>Total Piutang</strong></td>
                                    <td class="text-right" id="summary-total-piutang-vuca"><strong>-</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-report" id="table-vuca">
                            <thead>
                                <tr class="bg-blue">
                                    <th>Customer</th>
                                    <th>No SPK</th>
                                    <th>Nominal Project</th>
                                    <th>TOP</th>
                                    <th>Rincian TOP</th>
                                    <th>Tgl Invoice</th>
                                    <th>No Invoice</th>
                                    <th>Nilai Invoice</th>
                                    <th>Tgl Bayar</th>
                                    <th>Nilai Bayar</th>
                                    <th>Piutang Per Invoice</th>
                                    <th>Uninvoiced</th>
                                    <th>Total Sisa Piutang</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- SUSTAIN Tab -->
            <div class="tab-pane" id="panel-sustain" role="tabpanel" aria-labelledby="tab-sustain">
                <!-- Instruction message (shown when no date selected) -->
                <div class="instruction-message" id="instruction-sustain">
                    <i class="fa fa-calendar"></i>
                    <p>Silakan pilih tanggal filter terlebih dahulu untuk menampilkan data piutang.</p>
                </div>
                <!-- Table container (hidden until date selected) -->
                <div class="table-container" id="table-container-sustain" style="display: none;">
                    <!-- Summary Section SUSTAIN -->
                    <div class="summary-section" id="summary-sustain" style="margin-bottom: 15px;">
                        <table class="table table-bordered">
                            <tbody>
                                <tr class="bg-formula">
                                    <td width="250"><strong>Summary Piutang Per Invoice</strong></td>
                                    <td class="text-right" id="summary-piutang-sustain">-</td>
                                </tr>
                                <tr class="bg-formula">
                                    <td><strong>Summary Uninvoiced</strong></td>
                                    <td class="text-right" id="summary-uninvoiced-sustain">-</td>
                                </tr>
                                <tr class="bg-formula">
                                    <td><strong>Summary Sisa Piutang Per SPK</strong></td>
                                    <td class="text-right" id="summary-sisa-piutang-sustain">-</td>
                                </tr>
                                <tr class="bg-formula">
                                    <td><strong>Total Piutang</strong></td>
                                    <td class="text-right" id="summary-total-piutang-sustain"><strong>-</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-report" id="table-sustain">
                            <thead>
                                <tr class="bg-blue">
                                    <th>Customer</th>
                                    <th>No SPK</th>
                                    <th>Nominal Project</th>
                                    <th>TOP</th>
                                    <th>Rincian TOP</th>
                                    <th>Tgl Invoice</th>
                                    <th>No Invoice</th>
                                    <th>Nilai Invoice</th>
                                    <th>Tgl Bayar</th>
                                    <th>Nilai Bayar</th>
                                    <th>Piutang Per Invoice</th>
                                    <th>Uninvoiced</th>
                                    <th>Total Sisa Piutang</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>

<!-- Page Script -->
<script type="text/javascript">
    // Store fetched report data per tab for use by rendering functions
    var reportData = {};

    $(document).ready(function() {
        // Initialize datepicker with dd-mm-yyyy format
        $('#filter_date').datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true,
            maxDate: new Date()
        });

        // Tab click handler: auto-trigger search when switching tabs if date is selected
        $('#company-tabs a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            var filterDate = $('#filter_date').val();
            if (filterDate && filterDate.trim() !== '') {
                searchReport();
            }
        });
    });

    /**
     * Convert date from dd-mm-yyyy format to Y-m-d format for server.
     * @param {string} dateStr - Date in dd-mm-yyyy format
     * @returns {string|null} Date in Y-m-d format, or null if invalid
     */
    function convertDateToServerFormat(dateStr) {
        if (!dateStr || dateStr.trim() === '') {
            return null;
        }

        var datePattern = /^(\d{2})-(\d{2})-(\d{4})$/;
        var match = dateStr.match(datePattern);
        if (!match) {
            return null;
        }

        var day = parseInt(match[1], 10);
        var month = parseInt(match[2], 10);
        var year = parseInt(match[3], 10);

        // Validate date components are within valid ranges
        if (month < 1 || month > 12 || day < 1 || day > 31) {
            return null;
        }

        // Validate actual date validity (e.g., Feb 30 is invalid)
        var dateObj = new Date(year, month - 1, day);
        if (dateObj.getFullYear() !== year || dateObj.getMonth() !== (month - 1) || dateObj.getDate() !== day) {
            return null;
        }

        // Format as Y-m-d (zero-padded)
        var ymd = year + '-' + ('0' + month).slice(-2) + '-' + ('0' + day).slice(-2);
        return ymd;
    }

    function searchReport() {
        var filterDate = $('#filter_date').val();

        // Validate: tanggal tidak kosong
        if (!filterDate || filterDate.trim() === '') {
            swal('Perhatian', 'Silakan pilih tanggal filter terlebih dahulu.', 'warning');
            return false;
        }

        // Validate date format dd-mm-yyyy
        var datePattern = /^(\d{2})-(\d{2})-(\d{4})$/;
        if (!datePattern.test(filterDate)) {
            swal('Error', 'Format tanggal tidak valid. Gunakan format dd-mm-yyyy.', 'error');
            return false;
        }

        // Convert dd-mm-yyyy to Y-m-d for server
        var serverDate = convertDateToServerFormat(filterDate);
        if (!serverDate) {
            swal('Error', 'Tanggal tidak valid. Pastikan tanggal yang dimasukkan benar.', 'error');
            return false;
        }

        // Get active tab's company codes
        var activeTab = $('#company-tabs li.active a');
        var companyCodes = activeTab.data('company-codes').toString().split(',');
        var tabKey = activeTab.attr('href').replace('#panel-', '');

        // Hide instruction, show table container for active tab
        $('#instruction-' + tabKey).hide();
        $('#table-container-' + tabKey).show();

        // AJAX call to get report data
        loadReportData(tabKey, companyCodes, serverDate);
    }

    /**
     * Load report data via AJAX.
     * @param {string} tabKey - Tab identifier (stm, vuca, sustain)
     * @param {array} companyCodes - Array of company code strings
     * @param {string} serverDate - Date in Y-m-d format
     */
    function loadReportData(tabKey, companyCodes, serverDate) {
        // Show loading overlay on table container
        var $container = $('#table-container-' + tabKey);
        $container.find('.overlay').remove();
        $container.css('position', 'relative');
        $container.append(
            '<div class="overlay" style="position:absolute;top:0;left:0;width:100%;height:100%;' +
            'background:rgba(255,255,255,0.7);z-index:10;display:flex;align-items:center;justify-content:center;">' +
            '<i class="fa fa-refresh fa-spin" style="font-size:30px;color:#3c8dbc;"></i></div>'
        );

        // Build query parameters
        var params = {
            filter_date: serverDate,
            'company_codes[]': companyCodes
        };

        $.ajax({
            url: siteurl + 'report_piutang_per_invoice/get_report_data',
            type: 'GET',
            data: $.param(params, true),
            dataType: 'json',
            success: function(response) {
                // Hide loading overlay
                $container.find('.overlay').remove();

                if (response.status === 'success') {
                    // Store response data for the active tab
                    reportData[tabKey] = response;

                    renderReportTable(tabKey, response.data);
                    renderSummary(tabKey, response.summary);
                } else {
                    swal('Error', response.message || 'Terjadi kesalahan saat memuat data.', 'error');
                    $('#table-' + tabKey + ' tbody').html('<tr><td colspan="13" class="text-center text-danger">' + (response.message || 'Error') + '</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                // Hide loading overlay
                $container.find('.overlay').remove();

                swal('Error', 'Terjadi kesalahan koneksi ke server.', 'error');
                $('#table-' + tabKey + ' tbody').html('<tr><td colspan="13" class="text-center text-danger">Gagal memuat data.</td></tr>');
            }
        });
    }

    /**
     * Format a numeric value with dot as thousands separator, no decimals, no "Rp" prefix.
     * Examples: 1500000 → "1.500.000", -500000 → "-500.000", 0 → "0"
     * @param {number|string} value - Numeric value to format
     * @returns {string} Formatted number string
     */
    function formatNumber(value) {
        var num = parseFloat(value);
        if (isNaN(num)) {
            return '0';
        }

        var isNegative = num < 0;
        var absNum = Math.abs(Math.round(num));
        var formatted = absNum.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        return isNegative ? '-' + formatted : formatted;
    }

    /**
     * Format a date string from Y-m-d (server format) to dd-mm-yyyy (display format).
     * @param {string} dateStr - Date in Y-m-d format (e.g., "2024-01-15")
     * @returns {string} Formatted date in dd-mm-yyyy format, or "-" if null/empty
     */
    function formatDate(dateStr) {
        if (!dateStr || dateStr.trim() === '') {
            return '-';
        }

        var parts = dateStr.split('-');
        if (parts.length !== 3) {
            return '-';
        }

        // Input: YYYY-MM-DD → Output: DD-MM-YYYY
        return parts[2] + '-' + parts[1] + '-' + parts[0];
    }

    /**
     * Format a formula field value (Piutang Per Invoice, Uninvoiced, Total Sisa Piutang).
     * Zero values display as "-", non-zero values are formatted with thousands separator.
     * @param {number|string} value - Numeric value for formula field
     * @returns {string} Formatted value or "-" for zero
     */
    function formatFormulaField(value) {
        var num = parseFloat(value);
        if (isNaN(num) || num === 0) {
            return '-';
        }

        return formatNumber(num);
    }

    /**
     * Format a data field value (Nilai Invoice, Nilai Bayar).
     * Zero values display as "0", non-zero values are formatted with thousands separator.
     * @param {number|string} value - Numeric value for data field
     * @returns {string} Formatted value or "0" for zero
     */
    function formatDataField(value) {
        var num = parseFloat(value);
        if (isNaN(num) || num === 0) {
            return '0';
        }

        return formatNumber(num);
    }

    /**
     * Render the hierarchical report table from response data.
     * Hierarchy: Customer (level 1) → SPK (level 2) → Invoice/TOP (level 3) → Payment (level 4)
     * @param {string} tabKey - Tab identifier (stm, vuca, sustain)
     * @param {array} data - Hierarchical data from server response
     */
    function renderReportTable(tabKey, data) {
        var tbody = $('#table-' + tabKey + ' tbody');
        tbody.empty();

        if (!data || data.length === 0) {
            tbody.html('<tr><td colspan="13" class="text-center">Tidak ada data piutang untuk ditampilkan.</td></tr>');
            return;
        }

        var html = '';

        // Level 1: Loop through customers
        for (var c = 0; c < data.length; c++) {
            var customer = data[c];

            // Customer header row (level 1) - full width
            html += '<tr style="background-color:#f9f0d2;">';
            html += '<td colspan="13"><strong>' + escapeHtml(customer.customer) + '</strong></td>';
            html += '</tr>';

            // Level 2: Loop through SPK list
            var spkList = customer.spk_list || [];
            for (var s = 0; s < spkList.length; s++) {
                var spk = spkList[s];
                var details = spk.details || [];
                var isFirstSpkRow = true;

                // Calculate total rows for this SPK (for rowspan on Uninvoiced & Total Sisa Piutang)
                var spkRowCount = 0;
                if (details.length === 0) {
                    spkRowCount = 1;
                } else {
                    for (var dr = 0; dr < details.length; dr++) {
                        var detailPayments = (details[dr].invoice && details[dr].invoice.payments) ? details[dr].invoice.payments : [];
                        spkRowCount += 1; // main row for this detail
                        if (detailPayments.length > 1) {
                            spkRowCount += detailPayments.length - 1; // additional payment rows
                        }
                    }
                }

                // If SPK has no details at all, render one row with SPK info and empty columns
                if (details.length === 0) {
                    html += '<tr>';
                    html += '<td class="bg-data"></td>'; // Customer (empty, shown in header)
                    html += '<td class="bg-data">' + escapeHtml(spk.no_spk) + '</td>';
                    html += '<td class="bg-data text-right">' + formatNumber(spk.nominal_project) + '</td>';
                    html += '<td class="bg-data text-center">-</td>'; // TOP
                    html += '<td class="bg-data text-right">-</td>'; // Rincian TOP
                    html += '<td class="bg-data text-center">-</td>'; // Tgl Invoice
                    html += '<td class="bg-data">-</td>'; // No Invoice
                    html += '<td class="bg-data text-right">-</td>'; // Nilai Invoice
                    html += '<td class="bg-data text-center">-</td>'; // Tgl Bayar
                    html += '<td class="bg-data text-right">-</td>'; // Nilai Bayar
                    html += '<td class="bg-formula text-right">' + formatFormulaField(0) + '</td>'; // Piutang Per Invoice
                    html += '<td class="bg-formula text-right" rowspan="1">' + formatFormulaField(spk.uninvoiced) + '</td>'; // Uninvoiced
                    html += '<td class="bg-formula text-right" rowspan="1">' + formatFormulaField(spk.total_sisa_piutang) + '</td>'; // Total Sisa Piutang
                    html += '</tr>';
                    continue;
                }

                // Level 3: Loop through details (TOP/Invoice)
                for (var d = 0; d < details.length; d++) {
                    var detail = details[d];
                    var invoice = detail.invoice;
                    var payments = (invoice && invoice.payments) ? invoice.payments : [];

                    // Main row for this detail/TOP
                    html += '<tr>';

                    // Customer column (empty - shown in header)
                    html += '<td class="bg-data"></td>';

                    // No SPK - only on first row of SPK
                    if (isFirstSpkRow) {
                        html += '<td class="bg-data">' + escapeHtml(spk.no_spk) + '<br><small class="text-muted">Invoice: ' + spk.invoiced_top + '/' + spk.total_top + ' (' + spk.pending_top + ' pending)</small></td>';
                        html += '<td class="bg-data text-right">' + formatNumber(spk.nominal_project) + '</td>';
                    } else {
                        html += '<td class="bg-data"></td>'; // No SPK empty
                        html += '<td class="bg-data"></td>'; // Nominal Project empty
                    }

                    // TOP number
                    html += '<td class="bg-data text-center">' + detail.top_number + '</td>';

                    // Rincian TOP
                    html += '<td class="bg-data text-right">' + formatNumber(detail.rincian_top) + '</td>';

                    // Invoice columns
                    if (invoice) {
                        html += '<td class="bg-data text-center">' + formatDate(invoice.tanggal_invoice) + '</td>';
                        html += '<td class="bg-data">' + escapeHtml(invoice.no_invoice) + '</td>';
                        html += '<td class="bg-data text-right">' + formatDataField(invoice.nilai_invoice) + '</td>';
                    } else {
                        // TOP without invoice - show dash
                        html += '<td class="bg-data text-center">-</td>';
                        html += '<td class="bg-data">-</td>';
                        html += '<td class="bg-data text-right">-</td>';
                    }

                    // Payment columns - show first payment on same row if exists
                    if (payments.length > 0) {
                        html += '<td class="bg-data text-center">' + formatDate(payments[0].tanggal_bayar) + '</td>';
                        html += '<td class="bg-data text-right">' + formatDataField(payments[0].nilai_bayar) + '</td>';
                    } else {
                        html += '<td class="bg-data text-center"></td>';
                        html += '<td class="bg-data text-right"></td>';
                    }

                    // Piutang Per Invoice
                    if (invoice) {
                        html += '<td class="bg-formula text-right">' + formatFormulaField(invoice.piutang_per_invoice) + '</td>';
                    } else {
                        html += '<td class="bg-formula text-right">-</td>';
                    }

                    // Uninvoiced - only on first row of SPK, with rowspan
                    if (isFirstSpkRow) {
                        html += '<td class="bg-formula text-right" rowspan="' + spkRowCount + '" style="vertical-align:middle;">' + formatFormulaField(spk.uninvoiced) + '</td>';
                    }

                    // Total Sisa Piutang - only on first row of SPK, with rowspan
                    if (isFirstSpkRow) {
                        html += '<td class="bg-formula text-right" rowspan="' + spkRowCount + '" style="vertical-align:middle;">' + formatFormulaField(spk.total_sisa_piutang) + '</td>';
                    }

                    html += '</tr>';

                    // Level 4: Additional payment rows (starting from index 1, since first payment is on invoice row)
                    for (var p = 1; p < payments.length; p++) {
                        html += '<tr>';
                        html += '<td class="bg-data"></td>'; // Customer
                        html += '<td class="bg-data"></td>'; // No SPK
                        html += '<td class="bg-data"></td>'; // Nominal Project
                        html += '<td class="bg-data"></td>'; // TOP
                        html += '<td class="bg-data"></td>'; // Rincian TOP
                        html += '<td class="bg-data"></td>'; // Tgl Invoice
                        html += '<td class="bg-data"></td>'; // No Invoice
                        html += '<td class="bg-data"></td>'; // Nilai Invoice
                        html += '<td class="bg-data text-center">' + formatDate(payments[p].tanggal_bayar) + '</td>';
                        html += '<td class="bg-data text-right">' + formatDataField(payments[p].nilai_bayar) + '</td>';
                        html += '<td class="bg-formula"></td>'; // Piutang Per Invoice
                        html += '</tr>';
                    }

                    isFirstSpkRow = false;
                }
            }
        }

        tbody.html(html);
    }

    /**
     * Escape HTML special characters to prevent XSS.
     * @param {string} str
     * @returns {string}
     */
    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    /**
     * Render the summary section from response summary data.
     * Updates summary elements with formatted values per active tab.
     * Uses formatFormulaField() for consistent formatting (shows "-" for zero).
     * @param {string} tabKey - Tab identifier (stm, vuca, sustain)
     * @param {object|null} summary - Summary totals from server response
     */
    function renderSummary(tabKey, summary) {
        // Handle empty/null summary: show "-" for all values
        if (!summary) {
            $('#summary-piutang-' + tabKey).text('-');
            $('#summary-uninvoiced-' + tabKey).text('-');
            $('#summary-sisa-piutang-' + tabKey).text('-');
            $('#summary-total-piutang-' + tabKey).html('<strong>-</strong>');
            return;
        }

        // Format and render each summary value using formatFormulaField
        var piutangPerInvoice = formatFormulaField(summary.total_piutang_per_invoice);
        var uninvoiced = formatFormulaField(summary.total_uninvoiced);
        var sisaPiutangPerSpk = formatFormulaField(summary.total_sisa_piutang_per_spk);
        var totalPiutang = formatFormulaField(summary.grand_total_piutang);

        $('#summary-piutang-' + tabKey).text(piutangPerInvoice);
        $('#summary-uninvoiced-' + tabKey).text(uninvoiced);
        $('#summary-sisa-piutang-' + tabKey).text(sisaPiutangPerSpk);
        $('#summary-total-piutang-' + tabKey).html('<strong>' + totalPiutang + '</strong>');
    }

    /**
     * Download report as Excel file for the active tab.
     */
    function downloadExcel() {
        var filterDate = $('#filter_date').val();

        if (!filterDate || filterDate.trim() === '') {
            swal('Perhatian', 'Silakan pilih tanggal filter terlebih dahulu.', 'warning');
            return false;
        }

        // Convert dd-mm-yyyy to Y-m-d
        var serverDate = convertDateToServerFormat(filterDate);
        if (!serverDate) {
            swal('Error', 'Tanggal tidak valid.', 'error');
            return false;
        }

        // Get active tab key
        var activeTab = $('#company-tabs li.active a');
        var tabKey = activeTab.attr('href').replace('#panel-', '');

        // Open download URL in new window
        var url = siteurl + 'report_piutang_per_invoice/download_excel/' + serverDate + '/' + tabKey;
        window.open(url, '_blank');
    }
</script>