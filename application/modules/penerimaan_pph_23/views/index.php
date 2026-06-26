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
        <div class="row">
            <div class="col-md-3">
                <select id="filter_company" class="form-control form-control-sm select2">
                    <option value="">- Semua Company -</option>
                    <?php foreach ($list_company as $item): ?>
                        <option value="<?= $item->id ?>"><?= $item->nm_company ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select id="filter_year" class="form-control form-control-sm select2">
                    <option value="">- Semua Tahun -</option>
                    <?php 
                    $current_year = date('Y');
                    for ($i = $current_year; $i >= $current_year - 5; $i--): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filter_status" class="form-control form-control-sm select2">
                    <option value="">- Semua Status -</option>
                    <option value="1">Lunas</option>
                    <option value="0">Belum Lunas</option>
                </select>
            </div>
        </div>
    </div>
    <div class="box-body">
        <table class="table table-striped" id="table_list">
            <thead>
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center">No. Invoice</th>
                    <th class="text-center">Customer</th>
                    <th class="text-center">Company</th>
                    <th class="text-center">Project</th>
                    <th class="text-center">Keterangan Invoice</th>
                    <th class="text-center">Nilai PPh</th>
                    <th class="text-center">Status</th>
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
                <h4 class="modal-title" id="myModalLabel"><span class="fa fa-money"></span>&nbsp;Penerimaan Piutang</h4>
            </div>
            <form action="" id="frm_data" enctype="multipart/form-data">
                <div class="modal-body" id="MyModalBody">

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

<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function() {
        DataTables();

        $('.bank').chosen({
            width: '100%'
        });
        $('.search_bank').chosen({
            width: '450px'
        });
        $('.select2').chosen({
            width: '100%'
        });

        $(document).on('change', '#filter_company, #filter_year, #filter_status', function() {
            $('#table_list').DataTable().ajax.reload();
        });
    });

    function DataTables() {
        if ($.fn.DataTable.isDataTable('#table_list')) {
            $('#table_list').DataTable().destroy();
        }

        $('#table_list').DataTable({
            serverSide: true,
            processing: true,
            stateSave: true,
            paging: true,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            ajax: {
                type: 'POST',
                url: siteurl + active_controller + 'get_alokasi_penerimaan_pph23',
                data: function(d) {
                    d.filter_company = $('#filter_company').val();
                    d.filter_year = $('#filter_year').val();
                    d.filter_status = $('#filter_status').val();
                },
                dataType: 'json',
                error: function(xhr, error, thrown) {
                    console.error('DataTables AJAX error:', error, thrown);
                    swal('Gagal memuat data. Silakan coba lagi.');
                }
            },
            columns: [{
                    data: 'no',
                    className: 'text-center',
                    width: '50px',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'no_invoice',
                    className: 'text-center'
                },
                {
                    data: 'nm_customer'
                },
                {
                    data: 'nm_company'
                },
                {
                    data: 'nm_project'
                },
                {
                    data: 'keterangan_invoice'
                },
                {
                    data: 'nilai_pph',
                    className: 'text-right'
                },
                {
                    data: 'status',
                    className: 'text-center',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    className: 'text-center',
                    width: '100px',
                    orderable: false,
                    searchable: false
                }
            ],
            language: {
                processing: '<i class="fa fa-spinner fa-spin"></i> Memuat data...',
                emptyTable: 'Tidak ada data yang tersedia',
                info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 - 0 dari 0 data',
                infoFiltered: '(disaring dari _MAX_ total data)',
                lengthMenu: 'Tampilkan _MENU_ data',
                search: 'Cari:',
                zeroRecords: 'Tidak ditemukan data yang cocok',
                paginate: {
                    first: '<i class="fa fa-angle-double-left"></i>',
                    last: '<i class="fa fa-angle-double-right"></i>',
                    next: '<i class="fa fa-angle-right"></i>',
                    previous: '<i class="fa fa-angle-left"></i>'
                }
            },
            order: [],
            responsive: true
        });
    }
</script>