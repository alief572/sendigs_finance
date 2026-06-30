<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.7.1/css/buttons.bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* Modern Premium Typography */
    .report-container {
        font-family: 'Inter', sans-serif;
    }

    /* Premium Card Design */
    .modern-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: none;
        overflow: hidden;
        margin-bottom: 25px;
    }

    .modern-card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        padding: 18px 25px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .modern-card-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #2c3e50;
    }

    .modern-card-body {
        padding: 25px;
    }

    /* Filter Section Styling */
    .filter-wrapper {
        background: #f8fafd;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid #e1e8f0;
    }

    .modern-input {
        border-radius: 8px;
        border: 1px solid #ced4da;
        padding: 8px 12px;
        height: auto;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
    }

    .modern-input:focus {
        border-color: #4a90e2;
        box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.15);
    }

    .btn-modern {
        border-radius: 8px;
        padding: 8px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
        letter-spacing: 0.3px;
    }
    
    .btn-primary-modern {
        background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
        border: none;
        box-shadow: 0 4px 10px rgba(74, 144, 226, 0.3);
        color: white;
    }

    .btn-primary-modern:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(74, 144, 226, 0.4);
        color: white;
    }

    .btn-reset-modern {
        background: white;
        border: 1px solid #ced4da;
        color: #495057;
    }

    .btn-reset-modern:hover {
        background: #f1f3f5;
        color: #212529;
    }

    /* Info Alert */
    .modern-alert {
        background: #f0f7ff;
        border-left: 4px solid #4a90e2;
        border-radius: 8px;
        padding: 15px 20px;
        margin-bottom: 25px;
    }
    
    .modern-alert p {
        color: #2c3e50;
        margin-bottom: 8px;
    }
    
    .modern-alert ol {
        margin-bottom: 0;
        color: #4a5568;
    }

    .modern-alert li {
        margin-bottom: 4px;
    }

    /* Table Styling */
    .modern-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        table-layout: auto;
    }

    .modern-table thead th {
        background: #f8f9fa;
        color: #4a5568;
        font-weight: 600;
        padding: 15px 10px;
        border-top: 1px solid #e2e8f0;
        border-bottom: 2px solid #e2e8f0 !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        white-space: normal;
    }

    .modern-table tbody td {
        padding: 15px 10px;
        border-bottom: 1px solid #edf2f7;
        vertical-align: middle;
        color: #2d3748;
        font-size: 0.9rem;
        word-wrap: break-word;
        white-space: normal !important;
    }

    .modern-table tbody tr {
        transition: all 0.2s ease;
    }

    .modern-table tbody tr:hover {
        background-color: #f8fafc;
        transform: scale(1.001);
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
    }

    /* Badges */
    .badge-modern {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
    }

    .badge-transaksi {
        background-color: #fff5f5;
        color: #e53e3e;
        border: 1px solid #fed7d7;
    }

    .badge-refill {
        background-color: #f0fff4;
        color: #38a169;
        border: 1px solid #c6f6d5;
    }

    .table-jurnal-transaksi td {
        /* color: #e53e3e; */
    }

    .table-jurnal-refill td {
        /* color: #38a169; */
    }
    
    /* Text Colors for amounts */
    .text-amount-out {
        color: #e53e3e;
        font-weight: 500;
    }
    
    .text-amount-in {
        color: #38a169;
        font-weight: 500;
    }

    /* Export button */
    .btn-export {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: white;
        border-radius: 8px;
        padding: 8px 16px;
        font-weight: 500;
        box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);
        transition: all 0.3s ease;
    }
    
    .btn-export:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(16, 185, 129, 0.35);
        color: white;
    }
    
    /* DataTables Overrides */
    div.dataTables_wrapper div.dataTables_filter input {
        border-radius: 20px;
        border: 1px solid #ced4da;
        padding: 6px 15px;
        outline: none;
        transition: border-color 0.3s;
    }
    
    div.dataTables_wrapper div.dataTables_filter input:focus {
        border-color: #4a90e2;
    }
</style>

<div class="report-container">
    <div class="modern-card">
        <div class="modern-card-header">
            <h3 class="modern-card-title"><i class="fa fa-book text-primary" style="margin-right: 8px;"></i>Buku Kas Kecil – Rekap Pergerakan Saldo</h3>
        </div>
        <div class="modern-card-body">
            
            <div class="filter-wrapper">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <div class="row">
                            <label class="col-sm-3 control-label text-right" style="padding-top: 8px; font-weight: 600; color: #4a5568;">Filter Periode</label>
                            <div class="col-sm-4">
                                <input type="date" name="start_date" id="start_date" class="form-control modern-input">
                            </div>
                            <div class="col-sm-1 text-center" style="padding-top: 8px; font-weight: bold; color: #a0aec0;">&mdash;</div>
                            <div class="col-sm-4">
                                <input type="date" name="end_date" id="end_date" class="form-control modern-input">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5" style="display: flex; align-items: center;">
                        <button type="button" class="btn btn-modern btn-primary-modern" id="btn-filter">
                            <i class="fa fa-filter"></i> Filter
                        </button>
                        <button type="button" class="btn btn-modern btn-reset-modern" id="btn-reset" style="margin-left: 10px;">
                            <i class="fa fa-refresh"></i> Reset
                        </button>
                        <div id="export-container" style="margin-left: 10px;"></div>
                    </div>
                </div>
            </div>

            <div class="modern-alert">
                <p><b><i class="fa fa-info-circle"></i> Petunjuk Membaca Laporan:</b></p>
                <ol>
                    <li><b>Debet</b> : Kas kecil BERTAMBAH &rarr; sumber: Jurnal Refill (RPC)</li>
                    <li><b>Kredit</b> : Kas kecil BERKURANG &rarr; sumber: Jurnal Pencatatan (PCP)</li>
                    <li><b>Saldo</b> : Running balance = Saldo sebelumnya + Debet - Kredit</li>
                    <li><b>Jenis Jurnal ditandai warna</b>: <span class="badge-modern badge-transaksi">Transaksi (Merah)</span> | <span class="badge-modern badge-refill">Refill (Hijau)</span></li>
                </ol>
            </div>

            <div class="table-responsive">
                <table id="table_report" class="table modern-table" width="100%">
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
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.7.1/js/buttons.html5.min.js"></script>

<script>
    var BASE_URL = '<?= site_url('report_petty_cash/') ?>';
</script>

<script>
    $(document).ready(function() {
        var table = $('#table_report').DataTable({
            "processing": true,
            "serverSide": false, // We return all data at once to allow excel export of everything
            "ajax": {
                "url": BASE_URL + "get_data",
                "type": "POST",
                "data": function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            "columns": [
                { "data": "no", "className": "text-center" },
                { "data": "no_transaksi", "className": "font-weight-bold text-primary" },
                { "data": "tanggal", "className": "text-center" },
                { "data": "coa", "className": "text-center text-muted" },
                { "data": "company", "className": "text-center" },
                { "data": "pengeluaran" },
                { "data": "jenis_jurnal", "className": "text-center", 
                  "render": function(data, type, row) {
                      if(data == 'Transaksi') return '<span class="badge-modern badge-transaksi">' + data + '</span>';
                      if(data == 'Refill') return '<span class="badge-modern badge-refill">' + data + '</span>';
                      return data;
                  }
                },
                { "data": "debit", "className": "text-right text-amount-in" },
                { "data": "kredit", "className": "text-right text-amount-out" },
                { "data": "saldo", "className": "text-right font-weight-bold", "style": "font-size: 1.05em;" },
                { "data": "keterangan", "className": "text-muted" }
            ],
            "createdRow": function(row, data, dataIndex) {
                // Bold the first row
                if(data.debit === 'saldo awal >>') {
                    $(row).css('background-color', '#f8fafc');
                    $('td', row).css('font-weight', 'bold').css('color', '#2d3748');
                    $('td:eq(7)', row).removeClass('text-amount-in'); // remove green from text
                }
            },
            "dom": "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                   "<'row'<'col-sm-12'tr>>" +
                   "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            "buttons": [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fa fa-file-excel-o"></i> Download Excel',
                    className: 'btn-export',
                    title: 'Report Petty Cash - Buku Kas Kecil',
                    exportOptions: {
                        columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10 ]
                    }
                }
            ],
            "pageLength": 50,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "ordering": false, // Disable ordering to keep running balance correct
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search records..."
            }
        });
        
        // Append generated buttons to our custom container
        table.buttons().container().appendTo('#export-container');

        $('#btn-filter').click(function() {
            table.ajax.reload();
        });

        $('#btn-reset').click(function() {
            $('#start_date').val('');
            $('#end_date').val('');
            table.ajax.reload();
        });
    });
</script>
