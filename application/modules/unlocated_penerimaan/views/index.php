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
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
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
            width: '100%'
        });
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
                url: siteurl + active_controller + 'get_unlocated_penerimaan',
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
                }
            ]
        });
    }
</script>