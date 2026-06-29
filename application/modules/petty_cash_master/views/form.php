<?php
$edit_mode = (isset($mode) && $mode === 'edit');
$header_id = (isset($data->id)) ? $data->id : '';
?>
<!-- Select2 CSS -->
<link rel="stylesheet" href="<?= base_url('assets/plugins/select2/select2.min.css') ?>">

<?= form_open('petty_cash_master/save', array('id' => 'frm_data', 'name' => 'frm_data', 'role' => 'form', 'class' => 'form-horizontal')) ?>
<input type="hidden" id="id" name="id" value="<?= $header_id ?>">

<!-- Header Fields -->
<div class="form-group">
    <label for="nama" class="col-sm-2 control-label">Nama <b class="text-red">*</b></label>
    <div class="col-sm-10">
        <input type="text" class="form-control" id="nama" name="nama" value="<?= isset($data->nama) ? $data->nama : '' ?>" placeholder="Nama">
    </div>
</div>
<div class="form-group">
    <label for="keterangan" class="col-sm-2 control-label">Keterangan <b class="text-red">*</b></label>
    <div class="col-sm-10">
        <input type="text" class="form-control" id="keterangan" name="keterangan" value="<?= isset($data->keterangan) ? $data->keterangan : '' ?>" placeholder="Keterangan">
    </div>
</div>

<hr>

<!-- Detail Table Section -->
<h4><i class="fa fa-list"></i>&nbsp;Detail COA</h4>
<div class="table-responsive">
    <table id="detail-table" class="table table-bordered table-striped">
        <thead>
            <tr class="bg-primary">
                <th width="40" class="text-center">No</th>
                <th width="250">COA</th>
                <th>Jenis Pengeluaran</th>
                <th width="180">Nominal</th>
                <th width="60" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($edit_mode && isset($details) && !empty($details)) : ?>
                <?php foreach ($details as $idx => $detail) : ?>
                    <tr>
                        <td class="text-center row-number"><?= $idx + 1 ?></td>
                        <td>
                            <select name="detail[<?= $idx ?>][coa_code]" class="form-control select2 coa-select" style="width: 100%;">
                                <option value="">-- Pilih COA --</option>
                                <?php foreach ($coa_list as $code => $label) : ?>
                                    <option value="<?= $code ?>" <?= ($detail->coa_code == $code) ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="text" name="detail[<?= $idx ?>][jenis_pengeluaran]" class="form-control" value="<?= $detail->jenis_pengeluaran ?>" placeholder="Jenis Pengeluaran">
                        </td>
                        <td>
                            <input type="text" name="detail[<?= $idx ?>][nominal]" class="form-control nominal-input text-right" value="<?= number_format($detail->nominal, 0, ',', '.') ?>" placeholder="0">
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm btn-remove-row" title="Hapus"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <!-- Default empty row for create mode -->
                <tr>
                    <td class="text-center row-number">1</td>
                    <td>
                        <select name="detail[0][coa_code]" class="form-control select2 coa-select" style="width: 100%;">
                            <option value="">-- Pilih COA --</option>
                            <?php foreach ($coa_list as $code => $label) : ?>
                                <option value="<?= $code ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="detail[0][jenis_pengeluaran]" class="form-control" value="" placeholder="Jenis Pengeluaran">
                    </td>
                    <td>
                        <input type="text" name="detail[0][nominal]" class="form-control nominal-input text-right" value="" placeholder="0">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-row" title="Hapus"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Tambah Baris Button -->
<button type="button" class="btn btn-info btn-sm" id="btn-add-row">
    <i class="fa fa-plus"></i>&nbsp;Tambah Baris
</button>

<!-- Total Budget Display -->
<div class="callout callout-success" style="margin-top: 15px; padding: 15px 20px;">
    <h4 style="margin: 0; font-size: 20px; font-weight: bold;">
        <i class="fa fa-calculator"></i>&nbsp;
        Total Budget: <span id="total-budget-display">Rp 0</span>
    </h4>
</div>

<!-- Footer Buttons -->
<div class="form-group" style="margin-top: 15px;">
    <div class="col-sm-12 text-right">
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-reply"></i>&nbsp;Batal</button>
        <button type="button" class="btn btn-success" id="btn-save"><i class="fa fa-save"></i>&nbsp;Simpan</button>
    </div>
</div>
<?= form_close() ?>

<!-- Hidden COA options template for JS -->
<script type="text/template" id="coa-options-template">
    <option value="">-- Pilih COA --</option>
    <?php foreach ($coa_list as $code => $label) : ?>
        <option value="<?= $code ?>"><?= $label ?></option>
    <?php endforeach; ?>
</script>

<!-- Select2 JS -->
<script src="<?= base_url('assets/plugins/select2/select2.min.js') ?>"></script>

<script type="text/javascript">
    // Initialize Select2 on existing elements
    $('.select2').select2();

    /**
     * Parse formatted nominal string "1.000.000" to integer 1000000
     */
    function parseNominal(str) {
        if (!str || str.trim() === '') return 0;
        var cleaned = str.replace(/\./g, '').replace(/,/g, '');
        var num = parseInt(cleaned, 10);
        return isNaN(num) ? 0 : num;
    }

    /**
     * Format integer to Indonesian number format "1.000.000"
     */
    function formatNominal(num) {
        if (!num || num === 0) return '0';
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    // Auto-format nominal inputs as user types
    $(document).on('keyup', '.nominal-input', function(e) {
        if (e.which >= 37 && e.which <= 40) return;

        var val = $(this).val();
        var num = parseNominal(val);

        if (num === 0 && val !== '0') {
            $(this).val('');
        } else {
            $(this).val(formatNominal(num));
        }
        calculateTotal();
    });

    /**
     * Calculate total budget from all nominal inputs and update display
     */
    function calculateTotal() {
        var total = 0;
        $('.nominal-input').each(function() {
            total += parseNominal($(this).val());
        });
        $('#total-budget-display').text('Rp ' + formatNominal(total));
    }

    /**
     * Renumber all detail rows sequentially
     */
    function renumberRows() {
        $('#detail-table tbody tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
            $(this).find('[name]').each(function() {
                var name = $(this).attr('name');
                name = name.replace(/detail\[\d+\]/, 'detail[' + index + ']');
                $(this).attr('name', name);
            });
        });
    }

    /**
     * Add a new detail row
     */
    function addDetailRow() {
        var rowCount = $('#detail-table tbody tr').length;
        var coaOptions = $('#coa-options-template').html();

        var newRow = '<tr>' +
            '<td class="text-center row-number">' + (rowCount + 1) + '</td>' +
            '<td>' +
            '<select name="detail[' + rowCount + '][coa_code]" class="form-control coa-select" style="width: 100%;">' +
            coaOptions +
            '</select>' +
            '</td>' +
            '<td>' +
            '<input type="text" name="detail[' + rowCount + '][jenis_pengeluaran]" class="form-control" value="" placeholder="Jenis Pengeluaran">' +
            '</td>' +
            '<td>' +
            '<input type="text" name="detail[' + rowCount + '][nominal]" class="form-control nominal-input text-right" value="" placeholder="0">' +
            '</td>' +
            '<td class="text-center">' +
            '<button type="button" class="btn btn-danger btn-sm btn-remove-row" title="Hapus"><i class="fa fa-trash"></i></button>' +
            '</td>' +
            '</tr>';

        $('#detail-table tbody').append(newRow);
        $('#detail-table tbody tr:last').find('.coa-select').select2();
    }

    /**
     * Remove a detail row
     */
    function removeDetailRow(btn) {
        var rowCount = $('#detail-table tbody tr').length;
        if (rowCount <= 1) {
            Swal.fire({
                title: 'Perhatian',
                text: 'Minimal harus ada 1 baris detail.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }
        $(btn).closest('tr').remove();
        renumberRows();
        calculateTotal();
    }

    /**
     * Validate form before submission
     */
    function validateForm() {
        var nama = $('#nama').val().trim();
        var keterangan = $('#keterangan').val().trim();

        if (!nama) {
            return 'Field Nama harus diisi';
        }
        if (!keterangan) {
            return 'Field Keterangan harus diisi';
        }

        var rowCount = $('#detail-table tbody tr').length;
        if (rowCount < 1) {
            return 'Detail COA harus diisi minimal 1 baris';
        }

        var nominalError = '';
        $('#detail-table tbody tr').each(function(index) {
            var nominalVal = $(this).find('.nominal-input').val();
            var parsed = parseNominal(nominalVal);
            if (parsed <= 0) {
                nominalError = 'Nominal pada baris ' + (index + 1) + ' harus lebih besar dari 0';
                return false;
            }
        });

        if (nominalError) {
            return nominalError;
        }

        return '';
    }

    /**
     * Save data with SweetAlert2 confirmation and AJAX POST
     */
    function saveData() {
        Swal.fire({
            title: 'Simpan Data?',
            text: 'Apakah Anda yakin ingin menyimpan data ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });

                var formData = $('#frm_data').serialize();

                $.ajax({
                    url: siteurl + 'petty_cash_master/save',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 1) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.msg,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(function() {
                                $('#modal-crud').modal('hide');
                                if (typeof DataTables === 'function') {
                                    DataTables();
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: response.msg,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Gagal menghubungi server',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    }

    // ============================================================
    // Event Bindings
    // ============================================================

    // Add row button
    $('#btn-add-row').on('click', addDetailRow);

    // Remove row button (delegated for dynamic rows)
    $(document).on('click', '.btn-remove-row', function() {
        removeDetailRow(this);
    });

    // Real-time total calculation on nominal input
    $(document).on('input', '.nominal-input', calculateTotal);

    // Keypress filter on nominal fields - allow only digits and dots
    $(document).on('keypress', '.nominal-input', function(e) {
        var charCode = e.which || e.keyCode;
        if (charCode !== 46 && (charCode < 48 || charCode > 57)) {
            e.preventDefault();
        }
    });

    // Save button click - validate then save
    $('#btn-save').on('click', function() {
        var error = validateForm();
        if (error) {
            Swal.fire({
                title: 'Validasi Gagal',
                text: error,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }
        saveData();
    });

    // Initialize total calculation on page load (for edit mode)
    calculateTotal();
</script>