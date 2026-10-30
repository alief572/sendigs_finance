<link rel="stylesheet" href="<?= base_url('assets/dist/sweetalert2.min.css') ?>">
<style>
    .form-group label {
        font-weight: 600;
        color: #334155;
        font-size: 13px;
    }
    .preview-journal {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 16px;
        margin-top: 15px;
    }
    .preview-journal th {
        background: #e2e8f0;
        font-size: 12px;
        color: #1e293b;
    }
    .preview-journal td {
        font-size: 13px;
        font-variant-numeric: tabular-nums;
    }
    .badge-balanced {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
        padding: 3px 8px;
        border-radius: 4px;
        font-weight: 700;
    }
</style>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title" style="font-weight: 700; color: #1e293b;">
            <i class="fa fa-plus-circle"></i> Buat Tagihan Kwitansi Deposit Customer
        </h3>
    </div>
    <div class="box-body">
        <form id="form_kwitansi" method="post" action="<?= site_url('kwitansi_deposit/save') ?>">
            <input type="hidden" name="id_company" id="id_company" value="">
            <input type="hidden" name="nm_company" id="nm_company" value="">
            <input type="hidden" name="id_customer" id="id_customer" value="">
            <input type="hidden" name="nm_customer" id="nm_customer" value="">

            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_kwitansi">Tanggal Kwitansi <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_kwitansi" id="tanggal_kwitansi" class="form-control input-sm" value="<?= $results['today'] ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="preview_no_kwitansi">No. Kwitansi (Auto Preview)</label>
                                <input type="text" id="preview_no_kwitansi" class="form-control input-sm" style="font-family: monospace; font-weight: 700; background: #f1f5f9; color: #1e293b;" value="<?= $results['no_kwitansi_preview'] ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="select_customer">Customer <span class="text-danger">*</span></label>
                        <select id="select_customer" class="form-control input-sm select2" required style="width: 100%;">
                            <option value="">-- Pilih Customer --</option>
                            <?php foreach ($results['customers'] as $c): ?>
                                <option value="<?= htmlspecialchars($c['id_customer']) ?>" data-name="<?= htmlspecialchars($c['nm_customer']) ?>">
                                    <?= htmlspecialchars($c['nm_customer']) ?> (<?= htmlspecialchars($c['id_customer']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="po_customer">SPK Customer <span class="text-danger">*</span></label>
                        <select name="id_spk_penawaran" id="po_customer" class="form-control input-sm select2" required style="width: 100%;">
                            <option value="" disabled selected>Pilih customer terlebih dahulu</option>
                        </select>
                        <small class="text-muted">Pilih satu SPK. Company dan penomoran otomatis diselaraskan dari data SPK.</small>
                    </div>

                    <div class="form-group">
                        <label for="display_company">Company Penerbit</label>
                        <input type="text" id="display_company" class="form-control input-sm" style="font-weight: 600; background: #f8fafc; color: #0369a1;" placeholder="Otomatis terisi setelah SPK dipilih..." readonly>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nilai_kwitansi">Nilai Kwitansi Deposit (Rp) <span class="text-danger">*</span></label>
                        <input type="text" name="nilai_kwitansi" id="nilai_kwitansi" inputmode="decimal" class="form-control text-right" style="font-size: 18px; font-weight: 700; color: #0284c7;" placeholder="0,00" required>
                        <small class="text-muted">Nominal tagihan deposit yang akan diterbitkan ke customer.</small>
                    </div>

                    <div class="form-group">
                        <label for="keterangan">Keterangan / Catatan</label>
                        <textarea name="keterangan" id="keterangan" rows="5" class="form-control input-sm" placeholder="Catatan tambahan kwitansi deposit..."></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="preview-journal">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <h4 style="margin: 0; font-size: 14px; font-weight: 700; color: #1e293b;">
                                <i class="fa fa-book"></i> Preview Draft Jurnal Akrual Kwitansi Deposit
                            </h4>
                            <span class="badge-balanced"><i class="fa fa-check"></i> Balanced (Debit = Kredit)</span>
                        </div>
                        <table class="table table-bordered table-condensed" style="margin-bottom: 0; background: #fff;">
                            <thead>
                                <tr>
                                    <th width="120">No. Akun (COA)</th>
                                    <th width="180">Nama Akun</th>
                                    <th width="180">Company</th>
                                    <th>Keterangan</th>
                                    <th width="150" class="text-right">Debit (Rp)</th>
                                    <th width="150" class="text-right">Kredit (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>1102-01-01</code></td>
                                    <td><strong>Piutang Dagang Usaha</strong></td>
                                    <td id="jurnal_comp_1" class="text-muted">-</td>
                                    <td id="jurnal_ket_1" class="text-muted">Piutang Dagang Usaha</td>
                                    <td class="text-right" id="jurnal_debit_val" style="font-weight: 700; color: #047857;">0,00</td>
                                    <td class="text-right" style="color: #94a3b8;">0,00</td>
                                </tr>
                                <tr>
                                    <td><code>2102-01-02</code></td>
                                    <td><strong>Deposit Customer</strong></td>
                                    <td id="jurnal_comp_2" class="text-muted">-</td>
                                    <td id="jurnal_ket_2" class="text-muted">Deposit Customer</td>
                                    <td class="text-right" style="color: #94a3b8;">0,00</td>
                                    <td class="text-right" id="jurnal_kredit_val" style="font-weight: 700; color: #047857;">0,00</td>
                                </tr>
                            </tbody>
                        </table>
                        <small class="text-muted" style="display: block; margin-top: 8px;">
                            * Jurnal akrual ini otomatis dicatat saat kwitansi diterbitkan. Pelunasan uang masuk rekening koran diproses di modul <strong>Penerimaan Piutang</strong>.
                        </small>
                    </div>
                </div>
            </div>

            <div class="box-footer" style="margin-top: 20px; padding: 15px 0 0 0; background: transparent; border-top: 1px solid #e2e8f0;">
                <button type="submit" id="btn_submit" class="btn btn-primary btn-sm">
                    <i class="fa fa-save"></i> Simpan Kwitansi
                </button>
                <a href="<?= site_url('kwitansi_deposit') ?>" class="btn btn-danger btn-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/dist/sweetalert2.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        $('#select_customer').select2({
            width: '100%',
            minimumResultsForSearch: 0,
            placeholder: 'Cari nama atau kode customer',
            allowClear: true
        });

        function formatNumber(n) {
            return parseFloat(n).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function parseCurrency(str) {
            if (!str) return 0;
            var clean = str.toString().replace(/[^0-9,-]/g, '').replace(',', '.');
            var val = parseFloat(clean);
            return isNaN(val) ? 0 : val;
        }

        function updateJournalPreview() {
            var raw = $('#nilai_kwitansi').val();
            var val = parseCurrency(raw);
            var formatted = val > 0 ? formatNumber(val) : '0,00';
            $('#jurnal_debit_val').text(formatted);
            $('#jurnal_kredit_val').text(formatted);

            var noKw = $('#preview_no_kwitansi').val() || 'KD/...';
            var spkOpt = $('#po_customer').find(':selected');
            var spkNo = spkOpt.data('spk-no') || spkOpt.val() || '';
            var compName = $('#display_company').val() || '-';

            $('#jurnal_comp_1').text(compName);
            $('#jurnal_comp_2').text(compName);

            if (spkNo) {
                $('#jurnal_ket_1').text('Piutang Dagang Usaha - ' + noKw + ' - ' + spkNo);
                $('#jurnal_ket_2').text('Deposit Customer - ' + noKw + ' - ' + spkNo);
            } else {
                $('#jurnal_ket_1').text('Piutang Dagang Usaha - ' + noKw);
                $('#jurnal_ket_2').text('Deposit Customer - ' + noKw);
            }
        }

        function formatCurrencyInput(input) {
            var cursor = input.selectionStart;
            var tokens = input.value.slice(0, cursor).replace(/\./g, '').length;
            var sign = input.value.charAt(0) === '-' ? '-' : '';
            var parts = input.value.replace(/[^0-9,]/g, '').split(',');
            var integer = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            input.value = sign + integer + (parts.length > 1 ? ',' + parts.slice(1).join('') : '');
            var position = 0;
            while (position < input.value.length && tokens > 0) {
                if (input.value.charAt(position) !== '.') tokens--;
                position++;
            }
            input.setSelectionRange(position, position);
        }

        $('#nilai_kwitansi').on('input', function() {
            formatCurrencyInput(this);
            updateJournalPreview();
        }).on('blur', updateJournalPreview);

        $('#tanggal_kwitansi').on('change', function() {
            var tgl = $(this).val();
            if (!tgl) return;
            $.ajax({
                url: '<?= site_url("kwitansi_deposit/preview_no_kwitansi") ?>',
                type: 'POST',
                data: { tanggal_kwitansi: tgl },
                dataType: 'json',
                success: function(resp) {
                    if (resp.status === 1 && resp.no_kwitansi) {
                        $('#preview_no_kwitansi').val(resp.no_kwitansi);
                        updateJournalPreview();
                    }
                }
            });
        });

        $('#select_customer').on('change', function() {
            var custId = $(this).val();
            var custName = $(this).find(':selected').data('name') || '';
            $('#id_customer').val(custId);
            $('#nm_customer').val(custName);
            $('#id_company').val('');
            $('#nm_company').val('');
            $('#display_company').val('');

            $('#po_customer').empty().append('<option value="" disabled selected>Memuat daftar SPK...</option>');

            if (!custId) {
                $('#po_customer').empty().append('<option value="" disabled selected>Pilih customer terlebih dahulu</option>');
                updateJournalPreview();
                return;
            }

            $.ajax({
                url: '<?= site_url("kwitansi_deposit/get_spk_by_customer") ?>',
                type: 'POST',
                data: { id_customer: custId, nm_customer: custName },
                dataType: 'json',
                success: function(resp) {
                    $('#po_customer').empty().append($('<option>', {value: '', text: '-- Pilih SPK --', disabled: true, selected: true}));
                    if (resp.status === 1 && resp.data && resp.data.length > 0) {
                        $.each(resp.data, function(idx, item) {
                            var spkNo = item.no_spk ? item.no_spk : item.id_spk_penawaran;
                            var label = spkNo;
                            if (item.no_po) label += ' (PO: ' + item.no_po + ')';
                            label += ' [' + (item.nm_company || item.company_error || 'Company penawaran belum ditentukan') + ']';
                            if (item.nm_project) label += ' - ' + item.nm_project;
                            var opt = $('<option>', {
                                value: item.id_spk_penawaran,
                                text: label,
                                disabled: !item.id_company || !item.nm_company
                            });
                            opt.attr('data-company-id', item.id_company || '');
                            opt.attr('data-company-name', item.nm_company || '');
                            opt.attr('data-spk-no', spkNo);
                            $('#po_customer').append(opt);
                        });
                    } else {
                        $('#po_customer').append('<option value="" disabled selected>Tidak ada SPK untuk customer ini</option>');
                    }
                    updateJournalPreview();
                },
                error: function() {
                    $('#po_customer').empty().append('<option value="" disabled selected>Gagal memuat data SPK</option>');
                    updateJournalPreview();
                }
            });
        });

        $('#po_customer').on('change', function() {
            var selected = $(this).find(':selected');
            var compId = selected.attr('data-company-id') || '';
            var compName = selected.attr('data-company-name') || '';

            $('#id_company').val(compId);
            $('#nm_company').val(compName);
            $('#display_company').val(compName);
            updateJournalPreview();
        });

        var savingKwitansi = false;
        function resetSaveButton() {
            savingKwitansi = false;
            $('#btn_submit').prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Kwitansi');
        }

        $('#form_kwitansi').on('submit', async function(e) {
            e.preventDefault();
            if (savingKwitansi) return;

            var cust = $('#id_customer').val();
            var spk = $('#po_customer').val();
            var val = parseCurrency($('#nilai_kwitansi').val());

            if (!cust) {
                Swal.fire({icon: 'warning', title: 'Customer belum dipilih', text: 'Silakan pilih customer terlebih dahulu.'});
                return false;
            }
            if (!spk) {
                Swal.fire({icon: 'warning', title: 'SPK belum dipilih', text: 'Silakan pilih SPK customer terlebih dahulu.'});
                return false;
            }
            if (val <= 0) {
                Swal.fire({icon: 'warning', title: 'Nominal belum valid', text: 'Nilai kwitansi deposit harus lebih besar dari 0.'});
                return false;
            }

            savingKwitansi = true;
            $('#btn_submit').prop('disabled', true);
            try {
                var confirmation = await Swal.fire({
                    icon: 'question',
                    title: 'Simpan kwitansi deposit?',
                    text: 'Pastikan customer, SPK, dan nominal kwitansi sudah sesuai.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, simpan',
                    cancelButtonText: 'Batal',
                    focusCancel: true,
                    reverseButtons: true
                });
                if (!confirmation.isConfirmed) {
                    resetSaveButton();
                    return;
                }
            } catch (error) {
                resetSaveButton();
                return;
            }
            $('#btn_submit').html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

            var payload = $(this).serializeArray();
            $.each(payload, function(index, field) {
                if (field.name === 'nilai_kwitansi') {
                    field.value = field.value.replace(/\./g, '').replace(',', '.');
                }
            });

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: payload,
                dataType: 'json',
                success: function(resp) {
                    if (resp.status === 1) {
                        Swal.fire({icon: 'success', title: 'Kwitansi tersimpan', text: resp.msg || 'Kwitansi deposit berhasil disimpan.'}).then(function() {
                            window.location.href = '<?= site_url("kwitansi_deposit") ?>';
                        });
                    } else {
                        resetSaveButton();
                        Swal.fire({icon: 'error', title: 'Gagal menyimpan', text: resp.msg || 'Terjadi kesalahan sistem.'});
                    }
                },
                error: function(xhr) {
                    resetSaveButton();
                    Swal.fire({icon: 'error', title: 'Gagal menyimpan', text: 'Terjadi kesalahan jaringan atau server.'});
                }
            });
        });
    });
</script>
