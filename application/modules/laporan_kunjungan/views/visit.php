<?php
$ENABLE_ADD = has_permission('Laporan_Kunjungan.Add');
?>

<style>
    .btn { border-radius: 10px; }
    .info-label { font-weight: bold; color: #555; }
    .mandays-exceeded { color: #d9534f; font-weight: bold; }
    .mandays-normal { color: #5cb85c; font-weight: bold; }
    .time-display { font-size: 18px; font-weight: bold; color: #333; }
    .duration-display { font-size: 16px; color: #337ab7; font-weight: bold; }
    .kegiatan-section { margin-top: 15px; }
    .action-plan-row { background: #f9f9f9; padding: 10px; margin-bottom: 8px; border: 1px solid #ddd; border-radius: 5px; }
    .char-counter { font-size: 12px; color: #999; text-align: right; }
    .char-counter.exceeded { color: #d9534f; font-weight: bold; }
    .previous-ap-table .badge-progress { background-color: #f0ad4e; }
    .previous-ap-table .badge-done { background-color: #5cb85c; }
    .custom-kegiatan-input { margin-top: 10px; }
    .kegiatan-block { border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 15px; background: #fafafa; }
    .kegiatan-block h5 { margin-top: 0; color: #337ab7; }
    .btn-add-ap { margin-top: 5px; }
    .session-controls { text-align: center; padding: 20px; }
    .session-controls .btn { margin: 0 10px; min-width: 120px; }
</style>

<!-- Project Info Section -->
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Kunjungan Baru</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <td class="info-label" width="35%">Perusahaan</td>
                        <td id="info-perusahaan"><?php echo htmlspecialchars($spk_detail->nm_customer); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Project</td>
                        <td id="info-project"><?php echo htmlspecialchars($spk_detail->nm_project); ?></td>
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
    </div>
</div>

<!-- Visit Session Controls -->
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Sesi Kunjungan</h3>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-md-4 text-center">
                <label>Start Time</label>
                <div class="time-display" id="start-time-display">--/--/---- --:--</div>
                <button type="button" class="btn btn-success btn-sm" id="btn-start">
                    <i class="fa fa-play"></i> Start
                </button>
            </div>
            <div class="col-md-4 text-center">
                <label>Finish Time</label>
                <div class="time-display" id="finish-time-display">--/--/---- --:--</div>
                <button type="button" class="btn btn-danger btn-sm" id="btn-finish" disabled>
                    <i class="fa fa-stop"></i> Finish
                </button>
            </div>
            <div class="col-md-4 text-center">
                <label>Durasi</label>
                <div class="duration-display" id="duration-display">-</div>
                <div style="margin-top:5px;">
                    <label>Mandays Terpakai:</label>
                    <span id="mandays-terpakai-display" class="duration-display">-</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kegiatan Section -->
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Kegiatan</h3>
    </div>
    <div class="box-body">
        <?php if ($kegiatan_list && count($kegiatan_list) > 0): ?>
            <div class="form-group">
                <label>Pilih Kegiatan dari SPK:</label>
                <?php foreach ($kegiatan_list as $kegiatan): ?>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" class="kegiatan-checkbox"
                                   data-id="<?php echo htmlspecialchars($kegiatan->id_aktifitas); ?>"
                                   data-nama="<?php echo htmlspecialchars($kegiatan->nm_aktifitas); ?>">
                            <?php echo htmlspecialchars($kegiatan->nm_aktifitas); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> Tidak ada kegiatan tersedia dari SPK. Silakan tambahkan kegiatan custom.
            </div>
        <?php endif; ?>

        <!-- Custom Kegiatan Input -->
        <div class="custom-kegiatan-input">
            <label>Tambah Kegiatan Custom:</label>
            <div class="input-group">
                <input type="text" class="form-control" id="custom-kegiatan-input"
                       maxlength="500" placeholder="Masukkan nama kegiatan (maks 500 karakter)">
                <span class="input-group-btn">
                    <button type="button" class="btn btn-primary" id="btn-add-custom-kegiatan">
                        <i class="fa fa-plus"></i> Tambah
                    </button>
                </span>
            </div>
            <div class="char-counter"><span id="custom-kegiatan-counter">0</span>/500</div>
        </div>

        <!-- Action Plans per Kegiatan -->
        <div id="kegiatan-action-plans-container" class="kegiatan-section">
            <!-- Dynamically populated -->
        </div>
    </div>
</div>

<!-- Previous Action Plans Section -->
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Action Plan Sebelumnya</h3>
    </div>
    <div class="box-body">
        <?php if ($previous_action_plans && count($previous_action_plans) > 0): ?>
            <table class="table table-bordered table-striped previous-ap-table">
                <thead>
                    <tr>
                        <th>Kegiatan</th>
                        <th>Action Plan</th>
                        <th>PIC</th>
                        <th>Due Date</th>
                        <th>Visit Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($previous_action_plans as $ap): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($ap->nama_kegiatan); ?></td>
                            <td><?php echo htmlspecialchars($ap->description); ?></td>
                            <td><?php echo htmlspecialchars($ap->pic); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($ap->due_date)); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($ap->visit_date)); ?></td>
                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-toggle-status <?php echo ($ap->status == 'Done') ? 'btn-success' : 'btn-warning'; ?>"
                                        data-id="<?php echo $ap->id; ?>"
                                        data-status="<?php echo $ap->status; ?>">
                                    <?php echo $ap->status; ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> Tidak ada action plan dari kunjungan sebelumnya.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Potensi Improvement Section -->
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Potensi Improvement</h3>
    </div>
    <div class="box-body">
        <div class="form-group">
            <textarea class="form-control" id="potensi-improvement" rows="5"
                      maxlength="2000" placeholder="Masukkan potensi improvement (opsional, maks 2000 karakter)"></textarea>
            <div class="char-counter"><span id="potensi-counter">0</span>/2000</div>
        </div>
    </div>
</div>

<!-- Hasil Improvement Section -->
<div class="box">
    <div class="box-header">
        <h3 class="box-title">Hasil Improvement</h3>
    </div>
    <div class="box-body">
        <div class="form-group">
            <textarea class="form-control" id="hasil-improvement" rows="5"
                      maxlength="2000" placeholder="Masukkan hasil improvement (opsional, maks 2000 karakter)"></textarea>
            <div class="char-counter"><span id="hasil-counter">0</span>/2000</div>
        </div>
    </div>
</div>

<!-- Save Buttons -->
<div class="box">
    <div class="box-body text-center">
        <a href="<?php echo base_url('laporan_kunjungan'); ?>" class="btn btn-default">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
        <button type="button" class="btn btn-warning" id="btn-save-draft" style="margin-left:10px;">
            <i class="fa fa-save"></i> Save Draft
        </button>
        <button type="button" class="btn btn-primary" id="btn-save-final" style="margin-left:10px;">
            <i class="fa fa-check"></i> Save (Final)
        </button>
    </div>
</div>


<script type="text/javascript">
$(document).ready(function() {
    // ===== State Variables =====
    var startTime = null;
    var finishTime = null;
    var durationMinutes = null;
    var mandaysTerpakai = null;
    var visitDate = '<?php echo date('Y-m-d'); ?>';
    var idSpk = '<?php echo addslashes($id_spk); ?>';
    var selectedKegiatan = []; // Array of {id, nama, is_custom, action_plans: [...]}

    // ===== Helper Functions =====
    /**
     * Format datetime string for display.
     * Input: "YYYY-MM-DD HH:MM" or "YYYY-MM-DD HH:MM:SS"
     * Output: "DD-MM-YYYY HH:MM"
     */
    function formatDatetimeDisplay(datetimeStr) {
        if (!datetimeStr || datetimeStr === '' || datetimeStr === null) return '--/--/---- --:--';

        var parts = datetimeStr.split(' ');
        if (parts.length < 2) return '--/--/---- --:--';

        var dateParts = parts[0].split('-'); // [YYYY, MM, DD]
        var time = parts[1].substring(0, 5); // HH:MM

        return dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0] + ' ' + time;
    }

    /**
     * Format duration in minutes to human-readable string.
     * >= 1440 minutes: "X hari Y jam Z menit"
     * < 1440 minutes: "X jam Y menit"
     */
    function formatDuration(minutes) {
        if (minutes === null || minutes === undefined || minutes === '' || minutes === 0) return '-';

        minutes = parseInt(minutes);

        if (minutes >= 1440) {
            var days = Math.floor(minutes / 1440);
            var remainingAfterDays = minutes % 1440;
            var hours = Math.floor(remainingAfterDays / 60);
            var mins = remainingAfterDays % 60;
            return days + ' hari ' + hours + ' jam ' + mins + ' menit';
        } else {
            var hours = Math.floor(minutes / 60);
            var mins = minutes % 60;
            return hours + ' jam ' + mins + ' menit';
        }
    }

    // ===== Character Counters =====
    $('#custom-kegiatan-input').on('input', function() {
        var len = $(this).val().length;
        $('#custom-kegiatan-counter').text(len);
    });

    $('#potensi-improvement').on('input', function() {
        var len = $(this).val().length;
        $('#potensi-counter').text(len);
        if (len > 2000) {
            $('#potensi-counter').parent().addClass('exceeded');
        } else {
            $('#potensi-counter').parent().removeClass('exceeded');
        }
    });

    $('#hasil-improvement').on('input', function() {
        var len = $(this).val().length;
        $('#hasil-counter').text(len);
        if (len > 2000) {
            $('#hasil-counter').parent().addClass('exceeded');
        } else {
            $('#hasil-counter').parent().removeClass('exceeded');
        }
    });

    // ===== Start Session =====
    $('#btn-start').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: '<?php echo base_url('laporan_kunjungan/start_session'); ?>',
            type: 'POST',
            dataType: 'json',
            data: { id_spk: idSpk },
            success: function(response) {
                if (response.status) {
                    startTime = response.start_time;
                    $('#start-time-display').text(formatDatetimeDisplay(response.start_time));
                    $('#btn-finish').prop('disabled', false);
                } else {
                    alert(response.message || 'Gagal memulai sesi. Silakan coba lagi.');
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                alert('Terjadi kesalahan jaringan. Silakan coba lagi.');
                btn.prop('disabled', false);
            }
        });
    });

    // ===== Finish Session =====
    $('#btn-finish').on('click', function() {
        var btn = $(this);
        btn.prop('disabled', true);

        $.ajax({
            url: '<?php echo base_url('laporan_kunjungan/finish_session'); ?>',
            type: 'POST',
            dataType: 'json',
            data: { id_spk: idSpk, start_time: startTime },
            success: function(response) {
                if (response.status) {
                    finishTime = response.finish_time;
                    durationMinutes = response.duration_minutes;
                    mandaysTerpakai = response.mandays_used;

                    $('#finish-time-display').text(formatDatetimeDisplay(response.finish_time));
                    $('#duration-display').text(formatDuration(response.duration_minutes));
                    $('#mandays-terpakai-display').text(response.mandays_used);
                } else {
                    alert(response.message || 'Gagal mengakhiri sesi. Silakan coba lagi.');
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                alert('Terjadi kesalahan jaringan. Silakan coba lagi.');
                btn.prop('disabled', false);
            }
        });
    });

    // ===== Kegiatan Checkbox Handling =====
    $(document).on('change', '.kegiatan-checkbox', function() {
        var id = $(this).data('id');
        var nama = $(this).data('nama');

        if ($(this).is(':checked')) {
            addKegiatan(id, nama, false);
        } else {
            removeKegiatan(id);
        }
    });

    // ===== Add Custom Kegiatan =====
    $('#btn-add-custom-kegiatan').on('click', function() {
        var input = $('#custom-kegiatan-input');
        var nama = input.val().trim();

        if (!nama || nama.length === 0) {
            alert('Nama kegiatan tidak boleh kosong atau hanya berisi spasi.');
            return;
        }

        if (/^\s+$/.test(input.val())) {
            alert('Nama kegiatan tidak boleh hanya berisi spasi.');
            return;
        }

        if (nama.length > 500) {
            alert('Nama kegiatan maksimal 500 karakter.');
            return;
        }

        var customId = 'custom_' + Date.now();
        addKegiatan(customId, nama, true);
        input.val('');
        $('#custom-kegiatan-counter').text('0');
    });

    // ===== Add Kegiatan Function =====
    function addKegiatan(id, nama, isCustom) {
        // Check if already exists
        for (var i = 0; i < selectedKegiatan.length; i++) {
            if (selectedKegiatan[i].id == id) return;
        }

        var kegiatan = {
            id: id,
            nama: nama,
            is_custom: isCustom,
            action_plans: [createEmptyActionPlan()]
        };
        selectedKegiatan.push(kegiatan);
        renderKegiatanBlocks();
    }

    // ===== Remove Kegiatan Function =====
    function removeKegiatan(id) {
        selectedKegiatan = selectedKegiatan.filter(function(k) {
            return k.id != id;
        });
        renderKegiatanBlocks();
    }

    // ===== Create Empty Action Plan =====
    function createEmptyActionPlan() {
        return {
            description: '',
            pic: '',
            due_date: '',
            status: 'Progress'
        };
    }

    // ===== Render Kegiatan Blocks =====
    function renderKegiatanBlocks() {
        var container = $('#kegiatan-action-plans-container');
        container.empty();

        if (selectedKegiatan.length === 0) {
            return;
        }

        $.each(selectedKegiatan, function(kIdx, kegiatan) {
            var block = $('<div class="kegiatan-block" data-kegiatan-idx="' + kIdx + '"></div>');

            var header = '<h5><i class="fa fa-tasks"></i> ' + escapeHtml(kegiatan.nama);
            if (kegiatan.is_custom) {
                header += ' <span class="label label-info">Custom</span>';
                header += ' <button type="button" class="btn btn-xs btn-danger btn-remove-kegiatan" data-idx="' + kIdx + '" style="margin-left:10px;"><i class="fa fa-times"></i> Hapus</button>';
            }
            header += '</h5>';
            block.append(header);

            block.append('<label>Action Plans:</label>');

            // Render action plan rows
            $.each(kegiatan.action_plans, function(apIdx, ap) {
                var apRow = buildActionPlanRow(kIdx, apIdx, ap, kegiatan.action_plans.length);
                block.append(apRow);
            });

            // Add action plan button (max 50)
            if (kegiatan.action_plans.length < 50) {
                block.append('<button type="button" class="btn btn-xs btn-success btn-add-ap" data-kegiatan-idx="' + kIdx + '"><i class="fa fa-plus"></i> Tambah Action Plan</button>');
            }

            container.append(block);
        });
    }

    // ===== Build Action Plan Row =====
    function buildActionPlanRow(kIdx, apIdx, ap, totalAps) {
        var row = $('<div class="action-plan-row" data-kegiatan-idx="' + kIdx + '" data-ap-idx="' + apIdx + '"></div>');

        var html = '<div class="row">';
        html += '<div class="col-md-4">';
        html += '<div class="form-group"><label>Deskripsi <span class="text-danger">*</span></label>';
        html += '<input type="text" class="form-control ap-description" maxlength="500" value="' + escapeAttr(ap.description) + '" placeholder="Deskripsi action plan (maks 500)">';
        html += '</div></div>';

        html += '<div class="col-md-2">';
        html += '<div class="form-group"><label>PIC <span class="text-danger">*</span></label>';
        html += '<input type="text" class="form-control ap-pic" maxlength="100" value="' + escapeAttr(ap.pic) + '" placeholder="PIC (maks 100)">';
        html += '</div></div>';

        html += '<div class="col-md-2">';
        html += '<div class="form-group"><label>Due Date <span class="text-danger">*</span></label>';
        html += '<input type="date" class="form-control ap-due-date" value="' + escapeAttr(ap.due_date) + '" min="' + visitDate + '">';
        html += '</div></div>';

        html += '<div class="col-md-2">';
        html += '<div class="form-group"><label>Status</label>';
        html += '<select class="form-control ap-status">';
        html += '<option value="Progress"' + (ap.status === 'Progress' ? ' selected' : '') + '>Progress</option>';
        html += '<option value="Done"' + (ap.status === 'Done' ? ' selected' : '') + '>Done</option>';
        html += '</select>';
        html += '</div></div>';

        html += '<div class="col-md-2">';
        html += '<div class="form-group"><label>&nbsp;</label><br>';
        if (totalAps > 1) {
            html += '<button type="button" class="btn btn-xs btn-danger btn-remove-ap" data-kegiatan-idx="' + kIdx + '" data-ap-idx="' + apIdx + '" title="Hapus action plan"><i class="fa fa-trash"></i> Hapus</button>';
        }
        html += '</div></div>';
        html += '</div>';

        row.html(html);
        return row;
    }

    // ===== Add Action Plan =====
    $(document).on('click', '.btn-add-ap', function() {
        var kIdx = $(this).data('kegiatan-idx');
        if (selectedKegiatan[kIdx].action_plans.length >= 50) {
            alert('Maksimal 50 action plan per kegiatan.');
            return;
        }
        syncActionPlanData();
        selectedKegiatan[kIdx].action_plans.push(createEmptyActionPlan());
        renderKegiatanBlocks();
    });

    // ===== Remove Action Plan =====
    $(document).on('click', '.btn-remove-ap', function() {
        var kIdx = parseInt($(this).data('kegiatan-idx'));
        var apIdx = parseInt($(this).data('ap-idx'));

        if (!selectedKegiatan[kIdx] || selectedKegiatan[kIdx].action_plans.length <= 1) {
            alert('Minimal 1 action plan per kegiatan.');
            return;
        }
        syncActionPlanData();
        selectedKegiatan[kIdx].action_plans.splice(apIdx, 1);
        renderKegiatanBlocks();
    });

    // ===== Remove Custom Kegiatan =====
    $(document).on('click', '.btn-remove-kegiatan', function() {
        var idx = $(this).data('idx');
        selectedKegiatan.splice(idx, 1);
        renderKegiatanBlocks();
    });

    // ===== Sync Action Plan Data from DOM =====
    function syncActionPlanData() {
        $('#kegiatan-action-plans-container .kegiatan-block').each(function() {
            var kIdx = $(this).data('kegiatan-idx');
            $(this).find('.action-plan-row').each(function() {
                var apIdx = $(this).data('ap-idx');
                if (selectedKegiatan[kIdx] && selectedKegiatan[kIdx].action_plans[apIdx]) {
                    selectedKegiatan[kIdx].action_plans[apIdx].description = $(this).find('.ap-description').val() || '';
                    selectedKegiatan[kIdx].action_plans[apIdx].pic = $(this).find('.ap-pic').val() || '';
                    selectedKegiatan[kIdx].action_plans[apIdx].due_date = $(this).find('.ap-due-date').val() || '';
                    selectedKegiatan[kIdx].action_plans[apIdx].status = $(this).find('.ap-status').val() || 'Progress';
                }
            });
        });
    }

    // ===== Previous Action Plan Status Toggle =====
    $(document).on('click', '.btn-toggle-status', function() {
        var btn = $(this);
        var apId = btn.data('id');
        var currentStatus = btn.data('status');
        var newStatus = (currentStatus === 'Progress') ? 'Done' : 'Progress';

        $.ajax({
            url: '<?php echo base_url('laporan_kunjungan/update_action_plan_status'); ?>',
            type: 'POST',
            dataType: 'json',
            data: { id: apId, status: newStatus },
            success: function(response) {
                if (response.status) {
                    btn.data('status', newStatus);
                    btn.text(newStatus);
                    if (newStatus === 'Done') {
                        btn.removeClass('btn-warning').addClass('btn-success');
                    } else {
                        btn.removeClass('btn-success').addClass('btn-warning');
                    }
                } else {
                    alert(response.message || 'Gagal mengubah status.');
                }
            },
            error: function() {
                alert('Terjadi kesalahan jaringan.');
            }
        });
    });

    // ===== Client-Side Validation =====
    function validateForm(isFinal) {
        var errors = [];
        syncActionPlanData();

        if (isFinal) {
            // Validate start/finish time
            if (!startTime) {
                errors.push('Start time belum direkam. Silakan klik tombol Start.');
            }
            if (!finishTime) {
                errors.push('Finish time belum direkam. Silakan klik tombol Finish.');
            }
            if (startTime && finishTime && startTime >= finishTime) {
                errors.push('Finish time harus lebih besar dari Start time.');
            }

            // Validate at least one kegiatan
            if (selectedKegiatan.length === 0) {
                errors.push('Minimal satu kegiatan harus dipilih atau ditambahkan.');
            }

            // Validate action plans per kegiatan
            $.each(selectedKegiatan, function(kIdx, kegiatan) {
                if (kegiatan.action_plans.length === 0) {
                    errors.push('Kegiatan "' + kegiatan.nama + '" harus memiliki minimal 1 action plan.');
                }
                $.each(kegiatan.action_plans, function(apIdx, ap) {
                    var apLabel = 'Kegiatan "' + kegiatan.nama + '" - Action Plan #' + (apIdx + 1);
                    if (!ap.description || ap.description.trim().length === 0) {
                        errors.push(apLabel + ': Deskripsi wajib diisi.');
                    }
                    if (!ap.pic || ap.pic.trim().length === 0) {
                        errors.push(apLabel + ': PIC wajib diisi.');
                    }
                    if (!ap.due_date) {
                        errors.push(apLabel + ': Due Date wajib diisi.');
                    } else if (ap.due_date < visitDate) {
                        errors.push(apLabel + ': Due Date harus >= tanggal kunjungan (' + visitDate + ').');
                    }
                });
            });
        }

        // Validate character limits (both draft and final)
        var potensi = $('#potensi-improvement').val() || '';
        var hasil = $('#hasil-improvement').val() || '';
        if (potensi.length > 2000) {
            errors.push('Potensi Improvement melebihi batas 2000 karakter.');
        }
        if (hasil.length > 2000) {
            errors.push('Hasil Improvement melebihi batas 2000 karakter.');
        }

        return errors;
    }

    // ===== Collect Form Data =====
    function collectFormData() {
        syncActionPlanData();

        var data = {
            id_spk: idSpk,
            start_time: startTime,
            finish_time: finishTime,
            duration_minutes: durationMinutes,
            mandays_used: mandaysTerpakai,
            visit_date: visitDate,
            potensi_improvement: $('#potensi-improvement').val() || '',
            hasil_improvement: $('#hasil-improvement').val() || '',
            kegiatan: []
        };

        $.each(selectedKegiatan, function(idx, k) {
            data.kegiatan.push({
                id_aktifitas: k.is_custom ? null : k.id,
                nama_kegiatan: k.nama,
                is_custom: k.is_custom ? 1 : 0,
                action_plans: k.action_plans
            });
        });

        return data;
    }

    // ===== Save Draft =====
    $('#btn-save-draft').on('click', function() {
        var errors = validateForm(false);
        if (errors.length > 0) {
            alert('Validasi gagal:\n\n' + errors.join('\n'));
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true);

        var formData = collectFormData();

        $.ajax({
            url: '<?php echo base_url('laporan_kunjungan/save_draft'); ?>',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                btn.prop('disabled', false);
                if (response.status) {
                    alert('Draft berhasil disimpan.');
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                    }
                } else {
                    alert(response.message || 'Gagal menyimpan draft.');
                }
            },
            error: function() {
                btn.prop('disabled', false);
                alert('Terjadi kesalahan jaringan. Data tetap tersimpan di form.');
            }
        });
    });

    // ===== Save Final =====
    $('#btn-save-final').on('click', function() {
        var errors = validateForm(true);
        if (errors.length > 0) {
            alert('Validasi gagal:\n\n' + errors.join('\n'));
            return;
        }

        if (!confirm('Apakah Anda yakin ingin menyimpan laporan sebagai Final? Laporan yang sudah final tidak dapat diedit.')) {
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true);

        var formData = collectFormData();

        $.ajax({
            url: '<?php echo base_url('laporan_kunjungan/save_final'); ?>',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                btn.prop('disabled', false);
                if (response.status) {
                    alert('Laporan berhasil disimpan sebagai Final.');
                    window.location.href = '<?php echo base_url('laporan_kunjungan'); ?>';
                } else {
                    alert(response.message || 'Gagal menyimpan laporan.');
                }
            },
            error: function() {
                btn.prop('disabled', false);
                alert('Terjadi kesalahan jaringan. Data tetap tersimpan di form.');
            }
        });
    });

    // ===== Utility Functions =====
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    function escapeAttr(text) {
        if (!text) return '';
        return text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
});
</script>
