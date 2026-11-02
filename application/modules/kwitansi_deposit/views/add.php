<link rel="stylesheet" href="<?= base_url('assets/dist/sweetalert2.min.css') ?>">
<style>
    .kd-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px 28px; }
    .kd-fg { display: flex; flex-direction: column; gap: 4px; margin-bottom: 6px; }
    .kd-fg > label { font-weight: 700; font-size: 13px; margin: 0; }
    .kd-hint { font-size: 12px; color: #777; }
    .kd-req { color: #dd4b39; }
    .kd-inline { display: flex; gap: 8px; align-items: center; }
    .kd-journal th { font-weight: 700; white-space: nowrap; }
    .kd-journal .r { text-align: right; }
    @media (max-width: 900px) { .kd-grid2 { grid-template-columns: 1fr; } }
</style>

<h1 style="font-size: 24px; margin: 0 0 14px;">Kwitansi Deposit</h1>
<div style="font-size: 12px; color: #777; margin: -8px 0 14px;"><a href="<?= site_url('kwitansi_deposit') ?>">Kwitansi Deposit</a> / Tambah</div>

<div class="box box-primary">
    <div class="box-body">
        <form id="form_kwitansi" method="post" action="<?= site_url('kwitansi_deposit/save') ?>">
            <input type="hidden" name="id_company" id="id_company" value="">
            <input type="hidden" name="nm_company" id="nm_company" value="">
            <input type="hidden" name="id_customer" id="id_customer" value="">
            <input type="hidden" name="nm_customer" id="nm_customer" value="">

            <div class="kd-grid2">
                <div>
                    <div class="kd-fg">
                        <label for="preview_no_kwitansi">No Kwitansi</label>
                        <input type="text" id="preview_no_kwitansi" class="form-control" value="<?= htmlspecialchars($results['no_kwitansi_preview']) ?>" readonly>
                        <span class="kd-hint">Otomatis sistem</span>
                    </div>
                    <div class="kd-fg">
                        <label for="tanggal_kwitansi">Tanggal Kwitansi <span class="kd-req">*</span></label>
                        <input type="date" name="tanggal_kwitansi" id="tanggal_kwitansi" class="form-control" value="<?= htmlspecialchars($results['today']) ?>" required>
                    </div>
                    <div class="kd-fg">
                        <label for="select_customer">Nama Customer <span class="kd-req">*</span></label>
                        <select id="select_customer" class="form-control select2" required style="width: 100%;">
                            <option value="">- Pilih Customer -</option>
                            <?php foreach ($results['customers'] as $c): ?>
                                <option value="<?= htmlspecialchars($c['id_customer']) ?>" data-name="<?= htmlspecialchars($c['nm_customer']) ?>">
                                    <?= htmlspecialchars($c['nm_customer']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="kd-fg">
                        <label for="po_customer">No SPK <span class="kd-req">*</span></label>
                        <select name="id_spk_penawaran" id="po_customer" class="form-control select2" required disabled style="width: 100%;">
                            <option value="" selected>- Pilih SPK -</option>
                        </select>
                        <span class="kd-hint" id="spk_hint">Pilih customer dulu untuk lihat SPK</span>
                    </div>
                    <div class="kd-fg">
                        <label for="display_company">Company</label>
                        <input type="text" id="display_company" class="form-control" placeholder="Otomatis dari SPK" readonly>
                        <span class="kd-hint">Mengikuti company SPK (masuk ke jurnal).</span>
                    </div>
                    <div class="kd-fg">
                        <label for="nilai_kwitansi">Nilai Deposit <span class="kd-req">*</span></label>
                        <input type="text" name="nilai_kwitansi" id="nilai_kwitansi" inputmode="decimal" class="form-control text-right" placeholder="0" required>
                    </div>
                    <div class="kd-fg">
                        <label for="keterangan">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="2" class="form-control" placeholder="Catatan tambahan kwitansi deposit..."></textarea>
                    </div>
                </div>
            </div>

            <div style="font-weight: 700; margin: 12px 0 8px;">Jurnal Kwitansi Deposit</div>
            <div class="table-responsive">
                <table class="table kd-journal">
                    <thead>
                        <tr>
                            <th>Tanggal Jurnal</th>
                            <th>COA</th>
                            <th>Nama Company</th>
                            <th>Nama Account</th>
                            <th>Deskripsi</th>
                            <th class="r">Debit</th>
                            <th class="r">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="jurnal_tgl" style="white-space: nowrap;">-</td>
                            <td>1102-01-01</td>
                            <td class="jurnal_comp">-</td>
                            <td>Piutang Dagang Usaha</td>
                            <td id="jurnal_ket_1">Piutang Dagang Usaha</td>
                            <td class="r jurnal_val">0</td>
                            <td class="r">0</td>
                        </tr>
                        <tr>
                            <td class="jurnal_tgl" style="white-space: nowrap;">-</td>
                            <td>2102-01-02</td>
                            <td class="jurnal_comp">-</td>
                            <td>Deposit Customer</td>
                            <td id="jurnal_ket_2">Deposit Customer</td>
                            <td class="r">0</td>
                            <td class="r jurnal_val">0</td>
                        </tr>
                        <tr>
                            <td colspan="5" style="text-align: center;"><b>Balancing</b></td>
                            <td class="r"><b id="jurnal_tot_d">0</b></td>
                            <td class="r"><b id="jurnal_tot_k">0</b></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="kd-inline" style="margin-top: 12px;">
                <a href="<?= site_url('kwitansi_deposit') ?>" class="btn btn-danger">Batal</a>
                <button type="submit" id="btn_submit" class="btn btn-success" disabled>Simpan Kwitansi</button>
                <span class="kd-hint" id="save_hint">Lengkapi tanggal, customer, SPK, dan nilai deposit.</span>
            </div>
        </form>
    </div>
</div>

<script src="<?= base_url('assets/dist/sweetalert2.min.js') ?>"></script>
<script>
    $(document).ready(function() {
        var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        $('#select_customer').select2({
            width: '100%',
            minimumResultsForSearch: 0,
            placeholder: '- Pilih Customer -',
            allowClear: true
        });

        // en() mockup: bulat, pemisah koma, tanpa Rp.
        function en(n) {
            return Number(n || 0).toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0});
        }

        function parseCurrency(str) {
            if (!str) return 0;
            var clean = str.toString().replace(/[^0-9,-]/g, '').replace(',', '.');
            var val = parseFloat(clean);
            return isNaN(val) ? 0 : val;
        }

        function isComplete() {
            return !!($('#tanggal_kwitansi').val() && $('#id_customer').val() && $('#po_customer').val() && parseCurrency($('#nilai_kwitansi').val()) > 0);
        }

        function updateJournalPreview() {
            var val = parseCurrency($('#nilai_kwitansi').val());
            $('.jurnal_val').text(en(val));
            $('#jurnal_tot_d, #jurnal_tot_k').text(en(val));

            var noKw = $('#preview_no_kwitansi').val() || 'KD/...';
            var spkOpt = $('#po_customer').find(':selected');
            var spkNo = spkOpt.data('spk-no') || spkOpt.val() || '';
            var compName = $('#display_company').val() || '';

            var tglKw = $('#tanggal_kwitansi').val();
            var d = tglKw ? new Date(tglKw + 'T00:00:00') : null;
            var tglText = d && !isNaN(d.getTime()) ? ('0' + d.getDate()).slice(-2) + '-' + MONTHS[d.getMonth()] + '-' + d.getFullYear() : '-';
            $('.jurnal_tgl').text(tglText);
            $('.jurnal_comp').text(compName || '-');

            var sfx = ' - ' + noKw + (spkNo ? ' - ' + spkNo : '');
            $('#jurnal_ket_1').text('Piutang Dagang Usaha' + sfx);
            $('#jurnal_ket_2').text('Deposit Customer' + sfx);

            var ok = isComplete();
            $('#btn_submit').prop('disabled', !ok);
            $('#save_hint').text(ok ? 'Jurnal seimbang.' : 'Lengkapi tanggal, customer, SPK, dan nilai deposit.');
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
            updateJournalPreview();
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

        function resetSpk(hint) {
            $('#po_customer').empty().append('<option value="" selected>- Pilih SPK -</option>').prop('disabled', true);
            $('#spk_hint').text(hint);
        }

        $('#select_customer').on('change', function() {
            var custId = $(this).val();
            var custName = $(this).find(':selected').data('name') || '';
            $('#id_customer').val(custId);
            $('#nm_customer').val(custName);
            $('#id_company').val('');
            $('#nm_company').val('');
            $('#display_company').val('');

            if (!custId) {
                resetSpk('Pilih customer dulu untuk lihat SPK');
                updateJournalPreview();
                return;
            }
            resetSpk('Memuat daftar SPK...');

            $.ajax({
                url: '<?= site_url("kwitansi_deposit/get_spk_by_customer") ?>',
                type: 'POST',
                data: { id_customer: custId, nm_customer: custName },
                dataType: 'json',
                success: function(resp) {
                    $('#po_customer').empty().append($('<option>', {value: '', text: '- Pilih SPK -', selected: true}));
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
                        $('#po_customer').prop('disabled', false);
                        $('#spk_hint').text('SPK diambil dari data SPK customer');
                    } else {
                        $('#spk_hint').text('Customer ini belum punya SPK terdaftar.');
                    }
                    updateJournalPreview();
                },
                error: function() {
                    $('#spk_hint').text('Gagal memuat data SPK');
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
            $('#btn_submit').prop('disabled', !isComplete()).text('Simpan Kwitansi');
        }

        $('#form_kwitansi').on('submit', async function(e) {
            e.preventDefault();
            if (savingKwitansi || !isComplete()) return;

            savingKwitansi = true;
            $('#btn_submit').prop('disabled', true);
            var noKw = $('#preview_no_kwitansi').val();
            try {
                // confirmBox mockup: judul "Konfirmasi", tombol Batal / Ya, Simpan.
                var confirmation = await Swal.fire({
                    icon: 'warning',
                    title: 'Konfirmasi',
                    text: 'Simpan Kwitansi Deposit ' + noKw + '?',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan',
                    cancelButtonText: 'Batal',
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
            $('#btn_submit').text('Menyimpan...');

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
                        var done = resp.no_kwitansi || noKw;
                        Swal.fire({icon: 'success', title: 'Kwitansi Deposit ' + done + ' tersimpan.', timer: 1800, showConfirmButton: false}).then(function() {
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

        updateJournalPreview();
    });
</script>
