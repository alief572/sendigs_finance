<link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" />

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

    .btn-info {
        background-color: #00c0ef;
        border-color: #00acd6;
    }

    .btn-info:hover {
        background-color: #0097bc;
        border-color: #00870f;
    }

    .form-control-sm {
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
    }

    /* Premium Timeline Styling */
    .timeline-container {
        padding: 20px 0;
        position: relative;
        max-height: 480px;
        overflow-y: auto;
    }

    .timeline-container::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 33px;
        width: 3px;
        background: #e9ecef;
        border-radius: 2px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 25px;
        padding-left: 65px;
    }

    .timeline-icon {
        position: absolute;
        left: 17px;
        top: 0;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        text-align: center;
        line-height: 34px;
        color: white;
        font-size: 14px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        z-index: 1;
    }

    .timeline-icon.upload {
        background: #00a65a;
        /* Green */
    }

    .timeline-icon.split {
        background: #605ca8;
        /* Purple */
    }

    .timeline-icon.rollback {
        background: #dd4b39;
        /* Red */
    }

    .timeline-content {
        background: #fdfdfd;
        border-radius: 6px;
        padding: 12px 15px;
        border-left: 4px solid #d2d6de;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        position: relative;
    }

    .timeline-item.upload-item .timeline-content {
        border-left-color: #00a65a;
        background-color: #f6fbf8;
    }

    .timeline-item.split-item .timeline-content {
        border-left-color: #605ca8;
        background-color: #faf9ff;
    }

    .timeline-item.rollback-item .timeline-content {
        border-left-color: #dd4b39;
        background-color: #fffaf9;
    }

    .timeline-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px dashed #e5e5e5;
        padding-bottom: 6px;
        margin-bottom: 8px;
    }

    .timeline-title {
        font-size: 13px;
        font-weight: 700;
        margin: 0;
    }

    .timeline-item.upload-item .timeline-title {
        color: #008d4c;
    }

    .timeline-item.split-item .timeline-title {
        color: #555299;
    }

    .timeline-item.rollback-item .timeline-title {
        color: #d73925;
    }

    .timeline-date {
        font-size: 11px;
        color: #777;
        font-weight: 600;
    }

    .timeline-body {
        font-size: 13px;
        color: #444;
        line-height: 1.5;
        word-wrap: break-word;
    }

    .timeline-user {
        font-size: 11px;
        color: #666;
        margin-top: 8px;
        font-style: italic;
        display: flex;
        align-items: center;
        gap: 4px;
        font-weight: 500;
    }
</style>

<div class="box">
    <div class="box-header">
        <div class="col-md-2">
            <div class="form-inline">
                <div class="form-group">
                    <label for="startDate">Start:</label>
                    <input type="date" class="form-control form-control-sm" id="startDate" name="startDate" style="width: auto !important;">
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-inline">
                <div class="form-group">
                    <label for="endDate">End:</label>
                    <input type="date" class="form-control form-control-sm" id="endDate" name="endDate" style="width: auto !important;">
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <select class="form-control form-control-sm search_bank">
                <option value="">- Bank -</option>
                <?php foreach ($data_bank as $bank) : ?>
                    <option value="<?= $bank['id']; ?>"><?= $bank['nama_bank'] . ' - ' . $bank['rekening'] . ' - ' . $bank['nama']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 text-right">
            <button type="button" class="btn btn-sm btn-primary search_data"><i class="fa fa-search"></i> Search</button>
            <button type="button" class="btn btn-sm btn-default clear_data"><i class="fa fa-refresh"></i> Reset</button>
        </div>
    </div>
    <div class="box-body" style="padding-top: 20px;">
        <table id="table_list" class="table table-bordered table-striped" style="width: 100%;">
            <thead>
                <tr>
                    <th width="30" class="text-center">No</th>
                    <th width="100" class="text-center">Tanggal Transaksi</th>
                    <th>Nama Bank</th>
                    <th>Keterangan</th>
                    <th width="100" class="text-right">Debit</th>
                    <th width="100" class="text-right">Kredit</th>
                    <th width="100" class="text-right">Saldo</th>
                    <th width="150" class="text-center">Status Alokasi</th>
                    <th width="120" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Modal Timeline -->
<div class="modal fade" id="modalHistory" tabindex="-1" role="dialog" aria-labelledby="modalHistoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content" style="border-radius: 8px; overflow: hidden;">
            <div class="modal-header bg-primary" style="color: white; border-radius: 8px 8px 0 0; padding: 12px 15px;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8;"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modalHistoryLabel" style="font-weight: 700;"><i class="fa fa-history"></i> Log Timeline Histori Alokasi</h4>
            </div>
            <div class="modal-body" style="padding: 20px; background: #fafafa;">
                <div id="timeline_detail_info" style="margin-bottom: 15px; padding: 12px 15px; background: #ebf7fd; border-radius: 6px; border-left: 4px solid #00c0ef; font-size: 13px;">
                    <!-- detail of the transaction loaded dynamically -->
                </div>
                <div class="timeline-container" id="timeline_content">
                    <!-- timeline items loaded via ajax -->
                </div>
            </div>
            <div class="modal-footer" style="padding: 10px 15px;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.datatables.net/2.3.8/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $('.search_bank').chosen({
            width: '100%'
        });

        DataTables();
    });

    $(document).on('click', '.search_data', function() {
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();
        var bank = $('.search_bank').val();

        DataTables(startDate, endDate, bank);
    });

    $(document).on('click', '.clear_data', function() {
        $('#startDate').val('');
        $('#endDate').val('');
        $('.search_bank').val('');
        $('.search_bank').trigger('chosen:updated');

        DataTables();
    });

    function DataTables(startDate = null, endDate = null, bank = null) {
        $('#table_list').dataTable({
            serverSide: true,
            processing: true,
            stateSave: true,
            destroy: true,
            paging: true,
            ajax: {
                type: 'post',
                url: siteurl + active_controller + 'get_historical_alokasi',
                dataType: 'json',
                data: function(d) {
                    d.startDate = startDate;
                    d.endDate = endDate;
                    d.bank = bank;
                }
            },
            columns: [{
                    data: 'no',
                    className: 'text-center'
                },
                {
                    data: 'tanggal_transaksi',
                    className: 'text-center'
                },
                {
                    data: 'bank'
                },
                {
                    data: 'keterangan'
                },
                {
                    data: 'debit',
                    className: 'text-right'
                },
                {
                    data: 'kredit',
                    className: 'text-right'
                },
                {
                    data: 'saldo',
                    className: 'text-right'
                },
                {
                    data: 'status_alokasi',
                    className: 'text-center'
                },
                {
                    data: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ]
        });
    }

    // Handle view history timeline click
    $(document).on('click', '.btn_view_history', function() {
        var id = $(this).data('id');
        var tr = $(this).closest('tr');
        var rdata = $('#table_list').DataTable().row(tr).data();

        // Setup transaction details box
        var infoHtml = '<strong><i class="fa fa-info-circle"></i> Transaksi Detail:</strong><br>';
        infoHtml += 'Bank: ' + rdata.bank + '<br>';
        infoHtml += 'Keterangan: ' + rdata.keterangan + '<br>';
        infoHtml += 'Tanggal: ' + rdata.tanggal_transaksi + ' | ';
        if (rdata.debit != '0.00' && rdata.debit != '0') {
            infoHtml += 'Debit: Rp. ' + rdata.debit;
        } else {
            infoHtml += 'Kredit: Rp. ' + rdata.kredit;
        }

        $('#timeline_detail_info').html(infoHtml);
        $('#timeline_content').html('<div class="text-center" style="padding: 20px;"><i class="fa fa-spinner fa-spin fa-2x text-primary"></i> Loading timeline...</div>');
        $('#modalHistory').modal('show');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'get_timeline',
            dataType: 'json',
            data: {
                id: id
            },
            success: function(result) {
                if (result.status == 1 && result.data.length > 0) {
                    var html = '';
                    $.each(result.data, function(i, log) {
                        var iconClass = 'upload';
                        var faIcon = 'fa-upload';
                        var itemClass = 'upload-item';
                        var titleText = 'Upload Rekening Koran';

                        if (log.action === 'SPLIT_ALOKASI') {
                            iconClass = 'split';
                            faIcon = 'fa-code-fork';
                            itemClass = 'split-item';
                            titleText = 'Split Alokasi';
                        } else if (log.action === 'ROLLBACK_UNLOCATED') {
                            iconClass = 'rollback';
                            faIcon = 'fa-undo';
                            itemClass = 'rollback-item';
                            titleText = 'Rollback Alokasi';
                        }

                        // Formatting date
                        var logDate = new Date(log.created_date);
                        var formattedDate = logDate.toLocaleString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit'
                        });

                        html += '<div class="timeline-item ' + itemClass + '">';
                        html += '  <div class="timeline-icon ' + iconClass + '"><i class="fa ' + faIcon + '"></i></div>';
                        html += '  <div class="timeline-content">';
                        html += '    <div class="timeline-header">';
                        html += '      <h5 class="timeline-title">' + titleText + '</h5>';
                        html += '      <span class="timeline-date"><i class="fa fa-clock-o"></i> ' + formattedDate + '</span>';
                        html += '    </div>';
                        html += '    <div class="timeline-body">' + log.deskripsi_log + '</div>';
                        html += '    <div class="timeline-user"><i class="fa fa-user"></i> Oleh: ' + log.nm_lengkap + '</div>';
                        html += '  </div>';
                        html += '</div>';
                    });
                    $('#timeline_content').html(html);
                } else {
                    $('#timeline_content').html('<div class="alert alert-warning text-center"><i class="fa fa-warning"></i> Tidak ada jejak riwayat untuk transaksi ini.</div>');
                }
            },
            error: function() {
                $('#timeline_content').html('<div class="alert alert-danger text-center"><i class="fa fa-exclamation-triangle"></i> Gagal memuat data timeline.</div>');
            }
        });
    });
</script>