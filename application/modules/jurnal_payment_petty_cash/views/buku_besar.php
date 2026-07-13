<style>
    .btn {
        border-radius: 10px;
    }

    .filter-actions {
        margin-top: 25px;
    }

    #table_buku_besar tbody tr.row-transaksi {
        background-color: #f8d7da;
    }

    #table_buku_besar tbody tr.row-refill {
        background-color: #d4edda;
    }

    #table_buku_besar tbody tr.row-saldo-awal {
        background-color: #fff3cd;
        font-weight: bold;
    }

    .no-data-message {
        text-align: center;
        padding: 20px;
        color: #777;
        font-style: italic;
    }
</style>

<div class="box">
    <div class="box-header">
        <h3 class="box-title">Laporan Buku Besar Kas Kecil</h3>
    </div>
    <div class="box-body">
        <!-- Filter Form -->
        <form id="frm-filter" class="form-inline">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tgl_from">Tanggal Dari</label>
                        <input type="date" name="tgl_from" id="tgl_from" class="form-control" style="width: 100%;">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tgl_to">Tanggal Sampai</label>
                        <input type="date" name="tgl_to" id="tgl_to" class="form-control" style="width: 100%;">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group filter-actions">
                        <button type="button" class="btn btn-sm btn-primary" id="btn-search" onclick="loadBukuBesar()">
                            <i class="fa fa-search"></i> Search
                        </button>
                        <a href="#" class="btn btn-sm btn-success" id="btn-export" onclick="exportExcel()" style="display:none;">
                            <i class="fa fa-file-excel-o"></i> Export Excel
                        </a>
                    </div>
                </div>
            </div>
        </form>

        <hr>

        <!-- Table Buku Besar -->
        <div class="table-responsive">
            <table id="table_buku_besar" class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">No Transaksi</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">COA</th>
                        <th class="text-center">Company</th>
                        <th class="text-center">Pengeluaran</th>
                        <th class="text-center">Jenis Jurnal</th>
                        <th class="text-center">Debit</th>
                        <th class="text-center">Kredit</th>
                        <th class="text-center">Saldo</th>
                        <th class="text-center">Keterangan</th>
                    </tr>
                </thead>
                <tbody id="tbody_buku_besar">
                    <tr>
                        <td colspan="11" class="text-center text-muted">Silakan pilih periode tanggal dan klik Search</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- No Data Message -->
        <div id="no-data-message" class="no-data-message" style="display:none;">
            Tidak ada transaksi dalam periode ini
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
    function formatNumber(num) {
        if (num === null || num === undefined || num === '' || num == 0) return '0';
        var n = Math.round(parseFloat(num));
        if (isNaN(n)) return '0';
        var isNegative = n < 0;
        n = Math.abs(n);
        var formatted = n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return isNegative ? '-' + formatted : formatted;
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        var parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        var day = parseInt(parts[2], 10);
        var month = months[parseInt(parts[1], 10) - 1] || '';
        var year = parts[0];
        return day + ' ' + month + ' ' + year;
    }

    function loadBukuBesar() {
        var tgl_from = $('#tgl_from').val();
        var tgl_to = $('#tgl_to').val();

        if (!tgl_from || !tgl_to) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Silakan pilih tanggal dari dan tanggal sampai',
                timer: 3000
            });
            return;
        }

        if (tgl_from > tgl_to) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Tanggal dari tidak boleh lebih besar dari tanggal sampai',
                timer: 3000
            });
            return;
        }

        $('#btn-search').html('<i class="fa fa-spinner fa-spin"></i> Loading...').prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '<?= site_url("jurnal_payment_petty_cash/get_data_buku_besar") ?>',
            data: {
                tgl_from: tgl_from,
                tgl_to: tgl_to
            },
            dataType: 'json',
            success: function(response) {
                $('#btn-search').html('<i class="fa fa-search"></i> Search').prop('disabled', false);
                renderBukuBesar(response);
            },
            error: function() {
                $('#btn-search').html('<i class="fa fa-search"></i> Search').prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal memuat data buku besar. Silakan coba lagi.',
                    timer: 3000
                });
            }
        });
    }

    function renderBukuBesar(response) {
        var tbody = $('#tbody_buku_besar');
        tbody.empty();
        $('#no-data-message').hide();

        var saldo_awal = parseFloat(response.saldo_awal) || 0;
        var transactions = response.transactions || [];

        // Render Saldo Awal row
        var saldoAwalRow = '<tr class="row-saldo-awal">' +
            '<td class="text-center">-</td>' +
            '<td class="text-center">-</td>' +
            '<td class="text-center">-</td>' +
            '<td class="text-center">-</td>' +
            '<td class="text-center">-</td>' +
            '<td class="text-center">-</td>' +
            '<td class="text-center">-</td>' +
            '<td class="text-right">-</td>' +
            '<td class="text-right">-</td>' +
            '<td class="text-right">' + formatNumber(saldo_awal) + '</td>' +
            '<td>Saldo Awal</td>' +
            '</tr>';
        tbody.append(saldoAwalRow);

        // Check if no transactions
        if (transactions.length === 0) {
            $('#no-data-message').show();
            $('#btn-export').show();
            return;
        }

        // Render transaction rows with running balance
        var running_saldo = saldo_awal;
        for (var i = 0; i < transactions.length; i++) {
            var trx = transactions[i];
            var debit = parseFloat(trx.debit) || 0;
            var kredit = parseFloat(trx.kredit) || 0;

            running_saldo = running_saldo + debit - kredit;

            // Determine row color based on keterangan/jenis
            var rowClass = 'row-transaksi';
            var jenisJurnal = 'Transaksi';
            if (trx.keterangan && trx.keterangan.toLowerCase().indexOf('refill') !== -1) {
                rowClass = 'row-refill';
                jenisJurnal = 'Refill';
            }

            var row = '<tr class="' + rowClass + '">' +
                '<td class="text-center">' + (i + 1) + '</td>' +
                '<td class="text-center">' + (trx.no_transaksi || '-') + '</td>' +
                '<td class="text-center">' + formatDate(trx.tgl_jurnal) + '</td>' +
                '<td class="text-center">' + (trx.coa || '-') + '</td>' +
                '<td class="text-center">' + (trx.nm_company || '-') + '</td>' +
                '<td class="text-center">' + (trx.nm_coa || '-') + '</td>' +
                '<td class="text-center">' + jenisJurnal + '</td>' +
                '<td class="text-right">' + formatNumber(debit) + '</td>' +
                '<td class="text-right">' + formatNumber(kredit) + '</td>' +
                '<td class="text-right">' + formatNumber(running_saldo) + '</td>' +
                '<td>' + (trx.keterangan || '-') + '</td>' +
                '</tr>';
            tbody.append(row);
        }

        // Show export button
        $('#btn-export').show();
    }

    function exportExcel() {
        var tgl_from = $('#tgl_from').val();
        var tgl_to = $('#tgl_to').val();

        if (!tgl_from || !tgl_to) {
            Swal.fire({
                icon: 'warning',
                title: 'Perhatian',
                text: 'Silakan pilih periode tanggal terlebih dahulu',
                timer: 3000
            });
            return;
        }

        var url = '<?= site_url("jurnal_payment_petty_cash/export_buku_besar") ?>?tgl_from=' + tgl_from + '&tgl_to=' + tgl_to;
        window.location.href = url;
    }
</script>