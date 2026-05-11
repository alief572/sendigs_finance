<?php
$ENABLE_VIEW   = has_permission('Laporan_Kunjungan.View');
$ENABLE_MANAGE = has_permission('Laporan_Kunjungan.Manage');

// Encode id_spk for URLs (base64url)
$encoded_id_spk = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($id_spk));
?>

<style>
    .btn { border-radius: 10px; }
    .info-label { font-weight: bold; color: #555; }
    .mandays-exceeded { color: #d9534f; font-weight: bold; }
    .mandays-normal { color: #5cb85c; font-weight: bold; }
    .badge-progress { background-color: #f0ad4e; }
    .badge-done { background-color: #5cb85c; }
    .btn-status-toggle { cursor: pointer; border: none; padding: 4px 10px; border-radius: 4px; color: #fff; font-size: 12px; }
    .btn-status-toggle.progress { background-color: #f0ad4e; }
    .btn-status-toggle.done { background-color: #5cb85c; }
    .btn-status-toggle:hover { opacity: 0.85; }
    .report-actions { margin-bottom: 15px; }
    .report-actions .btn { margin-right: 5px; }
    .empty-state { text-align: center; padding: 40px 20px; color: #999; }
    .empty-state i { font-size: 48px; margin-bottom: 15px; display: block; }
</style>

<!-- Report Header -->
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Laporan Kunjungan Kumulatif</h3>
    </div>
    <div class="box-body">
        <!-- Project Info -->
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <td class="info-label" width="35%">Perusahaan</td>
                        <td><?php echo htmlspecialchars($spk_detail->nm_customer); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Project</td>
                        <td><?php echo htmlspecialchars($spk_detail->nm_project); ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h4 class="box-title">Informasi Mandays</h4>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr>
                                <td class="info-label" width="50%">Mandays Allocation</td>
                                <td><?php echo number_format($mandays_allocated, 2); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Mandays Terpakai</td>
                                <td><?php echo number_format($mandays_used, 2); ?></td>
                            </tr>
                            <tr>
                                <td class="info-label">Sisa Mandays</td>
                                <td>
                                    <span class="<?php echo ($mandays_remaining < 0) ? 'mandays-exceeded' : 'mandays-normal'; ?>">
                                        <?php echo number_format($mandays_remaining, 2); ?>
                                        <?php if ($mandays_remaining < 0): ?>
                                            <i class="fa fa-exclamation-triangle"></i>
                                            <small>(Budget Exceeded)</small>
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-md-12 report-actions" style="display:none;">
                <a href="<?php echo base_url('laporan_kunjungan/download_pdf/' . urlencode($encoded_id_spk)); ?>" class="btn btn-primary" id="btn-download-pdf">
                    <i class="fa fa-file-pdf-o"></i> Download PDF
                </a>
                <button type="button" class="btn btn-success" id="btn-send-email">
                    <i class="fa fa-envelope"></i> Send Email
                </button>
            </div>
        </div>

        <!-- Report Table -->
        <div class="row">
            <div class="col-md-12">
                <div id="report-table-container">
                    <table id="table_report" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Konsultan</th>
                                <th>Kegiatan</th>
                                <th>Action Plan</th>
                                <th>PIC</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="report-tbody">
                            <!-- Data loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div id="report-empty-state" class="empty-state" style="display: none;">
                    <i class="fa fa-file-text-o"></i>
                    <p>Belum ada laporan kunjungan yang sudah difinalisasi untuk project ini.</p>
                </div>
            </div>
        </div>

        <!-- Back Button -->
        <div class="row" style="margin-top: 15px;">
            <div class="col-md-12">
                <a href="<?php echo base_url('laporan_kunjungan'); ?>" class="btn btn-default">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
    <!-- /.box-body -->
</div>

<script type="text/javascript">
$(document).ready(function() {
    loadReportData();

    // Send Email button click handler
    $('#btn-send-email').on('click', function() {
        var btn = $(this);
        var originalText = btn.html();

        if (!confirm('Apakah Anda yakin ingin mengirim laporan via email ke client?')) {
            return;
        }

        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Mengirim...');

        $.ajax({
            url: '<?php echo base_url("laporan_kunjungan/send_email/" . urlencode($encoded_id_spk)); ?>',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status) {
                    alert(response.message || 'Email berhasil dikirim.');
                } else {
                    alert(response.message || 'Gagal mengirim email.');
                }
            },
            error: function() {
                alert('Terjadi kesalahan saat mengirim email. Silakan coba lagi.');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });
});

/**
 * Load report data via AJAX and populate the table.
 */
function loadReportData() {
    $.ajax({
        url: '<?php echo base_url("laporan_kunjungan/get_report_data/" . urlencode($encoded_id_spk)); ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            var tbody = $('#report-tbody');
            tbody.empty();

            var data = response.data || response;

            if (!data || data.length === 0) {
                $('#report-table-container').hide();
                $('#report-empty-state').show();
                return;
            }

            $('#report-table-container').show();
            $('#report-empty-state').hide();

            $.each(data, function(index, row) {
                var formattedDate = row.date ? formatDate(row.date) : '-';
                var formattedDueDate = row.due_date ? formatDate(row.due_date) : '-';

                // Build status toggle button
                var statusHtml = buildStatusButton(row.action_plan_id, row.status);

                var tr = '<tr>' +
                    '<td>' + escapeHtml(formattedDate) + '</td>' +
                    '<td>' + escapeHtml(row.konsultan || '-') + '</td>' +
                    '<td>' + escapeHtml(row.kegiatan || '-') + '</td>' +
                    '<td>' + escapeHtml(row.action_plan || '-') + '</td>' +
                    '<td>' + escapeHtml(row.pic || '-') + '</td>' +
                    '<td>' + escapeHtml(formattedDueDate) + '</td>' +
                    '<td>' + statusHtml + '</td>' +
                    '</tr>';

                tbody.append(tr);
            });
        },
        error: function() {
            $('#report-table-container').hide();
            $('#report-empty-state').show();
        }
    });
}

/**
 * Build status toggle button HTML.
 * Only users with Manage permission can toggle status.
 */
function buildStatusButton(actionPlanId, status) {
    <?php if ($ENABLE_MANAGE): ?>
    if (status === 'Done') {
        return '<button type="button" class="btn-status-toggle done" data-id="' + actionPlanId + '" data-status="Done" onclick="toggleStatus(this)">' +
               '<i class="fa fa-check"></i> Done</button>';
    } else {
        return '<button type="button" class="btn-status-toggle progress" data-id="' + actionPlanId + '" data-status="Progress" onclick="toggleStatus(this)">' +
               '<i class="fa fa-clock-o"></i> Progress</button>';
    }
    <?php else: ?>
    if (status === 'Done') {
        return '<span class="badge badge-done">Done</span>';
    } else {
        return '<span class="badge badge-progress">Progress</span>';
    }
    <?php endif; ?>
}

/**
 * Toggle action plan status via AJAX (Progress ↔ Done).
 */
function toggleStatus(btn) {
    var $btn = $(btn);
    var id = $btn.data('id');
    var currentStatus = $btn.data('status');
    var newStatus = (currentStatus === 'Progress') ? 'Done' : 'Progress';

    $btn.prop('disabled', true);

    $.ajax({
        url: '<?php echo base_url("laporan_kunjungan/update_action_plan_status"); ?>',
        type: 'POST',
        data: {
            id: id,
            status: newStatus
        },
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                // Update button appearance
                $btn.data('status', newStatus);
                if (newStatus === 'Done') {
                    $btn.removeClass('progress').addClass('done');
                    $btn.html('<i class="fa fa-check"></i> Done');
                } else {
                    $btn.removeClass('done').addClass('progress');
                    $btn.html('<i class="fa fa-clock-o"></i> Progress');
                }
            } else {
                alert(response.message || 'Gagal mengubah status.');
            }
        },
        error: function() {
            alert('Terjadi kesalahan. Silakan coba lagi.');
        },
        complete: function() {
            $btn.prop('disabled', false);
        }
    });
}

/**
 * Format date from YYYY-MM-DD to DD-MM-YYYY.
 */
function formatDate(dateStr) {
    if (!dateStr) return '-';
    var parts = dateStr.split('-');
    if (parts.length === 3) {
        return parts[2] + '-' + parts[1] + '-' + parts[0];
    }
    return dateStr;
}

/**
 * Escape HTML to prevent XSS.
 */
function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
</script>
