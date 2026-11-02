<?php
$isDeposit = (($results['receipt_kind'] ?? 'invoice') === 'deposit');
?>
<?= mk_tax_badge_css() ?>
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css" integrity="sha512-yVvxUQV0QESBt1SyZbNJMAwyKvFTLMyXSyBHDO4BG5t7k/Lw34tyqlSDlKIrIENIzCl+RVUNjmCPG+V/GMesRw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
    .tax-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .tax-PKP { background: #e8f4fd; color: #1976d2; border: 1px solid #bbdefb; }
    .tax-WAPU { background: #fef8e7; color: #b78103; border: 1px solid #ffe082; }
    .tax-KB { background: #f3e5f5; color: #7b1fa2; border: 1px solid #e1bee7; }
    .tax-DEP { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }

    .table_list th {
        background-color: #f4f6f9;
        font-weight: 700;
        font-size: 12px;
        color: #333;
        vertical-align: middle !important;
        white-space: nowrap;
    }
    .table_list td {
        vertical-align: middle !important;
        font-size: 13px;
    }
    .nowrap {
        white-space: nowrap;
    }
    .font-bold {
        font-weight: 700;
    }
    .form-control-sm {
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
    }
    .pu-hint { font-size: 12px; color: #777; }
    .state-bad { color: #dd4b39; font-weight: 700; }
    .pu-toast { position: fixed; right: 16px; bottom: 16px; background: #333; color: #fff; padding: 10px 14px; border-radius: 3px; z-index: 2000; font-size: 13px; max-width: 340px; }
    .pu-hint .btn-link { padding: 0; font-size: 12px; }
</style>

<h1 style="font-size: 24px; margin: 0 0 14px;">Penerimaan Piutang</h1>
<?php if ($isDeposit): ?>
    <div style="font-size: 12px; color: #777; margin: -8px 0 14px;"><a href="<?= base_url('penerimaan_uang') ?>#tab_kwitansi">Penerimaan Piutang Kwitansi Deposit</a> / Proses</div>
<?php endif; ?>

<div class="box box-primary">
    <div class="box-body">
        <form action="" method="post" id="form_data">
            <input type="hidden" name="receipt_kind" value="<?= htmlspecialchars($results['receipt_kind'] ?? 'invoice', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="id_alokasi" value="<?= $results['id_alokasi'] ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="customer">Nama Customer <span class="text-red">*</span></label>
                        <select class="form-control form-control-sm" name="id_customer" id="customer">
                            <option value="">- Pilih Customer -</option>
                            <?php
                            foreach ($results['list_customer'] as $item) {
                                echo '<option value="' . htmlspecialchars($item['id_customer'], ENT_QUOTES) . '">' . htmlspecialchars($item['nm_customer']) . '</option>';
                            }
                            ?>
                        </select>
                        <?php if ($isDeposit): ?>
                            <span class="pu-hint">Hanya customer yang punya Kwitansi Deposit belum lunas.</span>
                        <?php endif; ?>
                    </div>

                    <div id="deposit_context_message" class="text-warning" role="status"></div>
                    <?php if ($isDeposit): ?>
                        <input type="hidden" name="pph23_dipotong" id="pph23_dipotong" value="N">
                        <input type="hidden" name="deposit_choice" id="deposit_choice" value="N">
                        <input type="hidden" name="deposit_amount" id="deposit_amount" value="0.00">
                    <?php else: ?>
                        <div class="form-group">
                            <label for="pph23_dipotong">PPH23 Dipotong <span class="text-red">*</span></label>
                            <select class="form-control form-control-sm" name="pph23_dipotong" id="pph23_dipotong">
                                <option value="" selected>- Pilih -</option>
                                <option value="Y">Dipotong PPh</option>
                                <option value="N">Tidak Dipotong PPh</option>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <?php if ($isDeposit): ?>
                        <div class="form-group">
                            <label>Tanggal &amp; Bank</label>
                            <input type="text" class="form-control form-control-sm" readonly value="<?= htmlspecialchars($results['tanggal_bank'] ?? '-', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    <?php endif; ?>
                    <?php if (!$isDeposit): ?>
                        <div class="form-group">
                            <label for="deposit_choice">Deposit <span class="text-red">*</span></label>
                            <select class="form-control form-control-sm" name="deposit_choice" id="deposit_choice" disabled>
                                <option value="N" selected>Tidak Pakai Deposit</option>
                                <option value="Y">Pakai Deposit</option>
                            </select>
                            <div id="customer_deposit_badge_wrap" class="pu-hint" style="display:none; margin-top: 4px;">
                                Saldo deposit: <span id="lbl_cust_deposit">Rp 0,00</span><span id="wrap_kartu_link"> &middot; <button type="button" class="btn btn-link" id="btn_view_kartu_deposit">Lihat kartu deposit</button></span>
                            </div>
                        </div>
                        <div class="form-group" id="wrap_deposit_amount" style="display:none;">
                            <label for="deposit_amount">Nominal Deposit Dipakai <span class="text-red">*</span></label>
                            <input type="text" name="deposit_amount" id="deposit_amount" class="form-control form-control-sm text-right" value="" placeholder="0,00" inputmode="decimal">
                            <span class="pu-hint">Diisi sendiri, maksimal Rp <span id="dep_max_label">0,00</span>. Saat Process, nominal ini dibagi ke invoice yang dicentang.</span>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="nominal_penerimaan_bank">Nominal Penerimaan Bank <span class="text-red">*</span></label>
                        <input type="text" name="nominal_penerimaan_bank" id="nominal_penerimaan_bank" class="form-control form-control-sm text-right" value="<?= $results['nominal_penerimaan_bank'] ?>" readonly style="font-size: 16px; font-weight: 700; background-color: #f8fafc;">
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top: 15px;">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table_list">
                            <thead>
                                <?php if ($isDeposit): ?>
                                    <tr>
                                        <th class="text-center" style="width: 100px;">Tgl Kwitansi</th>
                                        <th class="text-center" style="width: 160px;">No Kwitansi</th>
                                        <th class="text-center" style="width: 140px;">No SPK</th>
                                        <th class="text-center" style="width: 120px;">Company</th>
                                        <th class="text-right" style="width: 130px;">Nilai Kwitansi</th>
                                        <th class="text-right" style="width: 130px;">Saldo Piutang</th>
                                        <th class="text-center" style="width: 60px;">Action</th>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <th class="text-center" style="width: 100px;">Tgl Invoice</th>
                                        <th class="text-center" style="width: 140px;">Nomor Invoice</th>
                                        <th class="text-center" style="width: 80px;">TAX Code</th>
                                        <th class="text-right" style="width: 110px;">Penjualan</th>
                                        <th class="text-right" style="width: 100px;">DPP</th>
                                        <th class="text-right" style="width: 90px;">PPN</th>
                                        <th class="text-right" style="width: 90px;">PPH 23</th>
                                        <th class="text-right" style="width: 120px;">Total Tagihan<br>(Penjualan + PPN)</th>
                                        <th class="text-right" style="width: 120px;">Total Tagihan<br>- PPH</th>
                                        <th class="text-right" style="width: 120px;">Saldo Piutang</th>
                                        <th class="text-center" style="width: 60px;">Action</th>
                                    </tr>
                                <?php endif; ?>
                            </thead>
                            <tbody class="tbody_list">
                                <tr>
                                    <td colspan="<?= $isDeposit ? 7 : 11 ?>" class="text-center text-muted" style="padding: 20px;">
                                        <?= $isDeposit ? 'Pilih customer untuk menampilkan kwitansi deposit yang masih terbuka.' : 'No Data Found' ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top: 20px;">
                <div class="col-md-12">
                    <a href="<?= base_url('penerimaan_uang') . ($isDeposit ? '#tab_kwitansi' : '') ?>" class="btn btn-danger" style="margin-right: 8px;">Back</a>
                    <button type="submit" class="btn btn-success btn_submit_process">Process</button>
                    <span class="pu-hint" id="fmsg" style="margin-left: 8px;"></span>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Kartu Deposit Customer (mockup depCardModal) -->
<div class="modal fade" id="modal_kartu_deposit" tabindex="-1" role="dialog" aria-labelledby="modalKartuDepTitle" aria-hidden="true">
    <div class="modal-dialog" style="width: min(640px, 100%);">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">&times;</button>
                <h4 class="modal-title" id="modalKartuDepTitle">Kartu Deposit Customer</h4>
            </div>
            <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                <div style="margin-bottom: 8px;"><b id="kartu_dep_cust_name">-</b> &middot; saldo <span id="kartu_saldo_aktif">Rp 0,00</span></div>
                <div id="kartu_dep_warning" class="alert alert-warning" role="status" style="display:none;"></div>
                <div class="table-responsive">
                    <table class="table" id="table_kartu_dep" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Referensi</th>
                                <th>Keterangan</th>
                                <th class="text-right">Masuk</th>
                                <th class="text-right">Keluar</th>
                                <th class="text-right">Saldo</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_kartu_dep">
                            <tr><td colspan="6" class="text-center text-muted">Memuat data kartu deposit...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dialog Popup Alokasi -->
<div class="modal fade" id="dialog-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg" style="width: 98% !important; max-width: 1440px;">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #3c8dbc; color: #fff;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.8;"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel"><?= $isDeposit ? 'Penerimaan Piutang Kwitansi Deposit' : 'Penerimaan Piutang' ?></h4>
            </div>
            <form action="" id="frm_data" enctype="multipart/form-data">
                <div class="modal-body" id="MyModalBody" style="max-height: 80vh; overflow-y: auto; padding: 15px;">
                    <!-- Konten alokasi dimuat via AJAX process_alokasi / process_kwitansi_deposit -->
                </div>
                <div class="modal-footer" style="background-color: #f8fafc;">
                    <button type="submit" class="btn btn-success btn_proses" disabled>Proses</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="<?= base_url('assets/js/penerimaan_uang/consulting.js') ?>"></script>
<script src="https://cdn.datatables.net/2.3.2/js/dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js" integrity="sha512-rMGGF4wg1R73ehtnxXBt5mbUfN9JUJwbk21KMlnLZDJh7BkPmeovBuddZCENJddHYYMkCh9hPFnPmS9sspki8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    var custDepositBalance = 0;
    var depositByCompany = [];
    var depositRequestVersion = 0;
    var isDepositKind = <?= json_encode($isDeposit) ?>;
    var kwitansiHint = <?= json_encode($results['kwitansi_hint'] ?? null) ?>;

    // Kwitansi yang dipilih di Alokasi dicentang otomatis; hanya saran, pengguna tetap bisa mengubahnya.
    function applyKwitansiHint() {
        if (!kwitansiHint) return;
        $('.tbody_list input[name="choose_inv[]"]').filter(function() { return $(this).val() === kwitansiHint.invoice_id; })
            .not(':disabled').first().prop('checked', true).trigger('change');
    }

    $(document).ready(function() {
        $('#customer').chosen({
            width: '100%',
            search_contains: true
        });
        if (kwitansiHint && $('#customer option[value="' + kwitansiHint.id_customer + '"]').length) {
            $('#customer').val(kwitansiHint.id_customer).trigger('chosen:updated').trigger('change');
        }

        if (!isDepositKind) {
            $('#pph23_dipotong').chosen({ width: '100%' });
            $('#deposit_choice').chosen({ width: '100%' });
            $('#deposit_amount').autoNumeric('init', {
                vMin: '0.00',
                vMax: '9999999999999.99',
                aSep: '.',
                aDec: ','
            });
            $('#deposit_amount').val('');
        }

        $('#nominal_penerimaan_bank').autoNumeric('init', {
            vMin: '-9999999999999.99',
            vMax: '9999999999999.99',
            aSep: '.',
            aDec: ','
        });
    });

    function resetReceiptContext() {
        depositRequestVersion++;
        custDepositBalance = 0;
        depositByCompany = [];
        $('#MyModalBody').empty();
        $('#dialog-popup').modal('hide');
        $('#tbody_kartu_dep').empty();
        $('#modal_kartu_deposit').modal('hide');
        $('#customer_deposit_badge_wrap').hide();
        $('#deposit_context_message').text('');
        $('.tbody_list').empty();
        if (!isDepositKind) {
            $('#deposit_choice').val('N').prop('disabled', true).trigger('chosen:updated');
            $('#wrap_deposit_amount').hide();
            $('#deposit_amount').val('');
        }
    }

    function loadInvoices(keepChecked) {
        var preChecked = keepChecked === true ? $('.tbody_list input[name="choose_inv[]"]:checked').map(function() { return $(this).val(); }).get() : [];
        var preDepChoice = keepChecked === true && !isDepositKind ? $('#deposit_choice').val() : 'N';
        var preDepAmount = keepChecked === true && !isDepositKind ? $('#deposit_amount').val() : '';
        resetReceiptContext();
        var customer = $('#customer').val();
        var version = depositRequestVersion;
        if (!customer) return;
        // Company tidak dipilih di form: ditentukan dari invoice yang dicentang (satu penerimaan = satu company).
        var context = {id_customer:customer, receipt_kind:isDepositKind ? 'deposit' : 'invoice', pph23_dipotong:$('#pph23_dipotong').val() || 'N'};
        $.ajax({type:'post', url:siteurl+active_controller+'get_inv_by_cust', data:context,
            success:function(html) {
                if (version !== depositRequestVersion) return;
                $('.tbody_list').html(html);
                applyKwitansiHint();
                // Ganti PPh tidak membatalkan pilihan invoice (mockup): centang yang sama dipulihkan.
                $.each(preChecked, function(i, val) { $('.tbody_list input[name="choose_inv[]"]').filter(function() { return $(this).val() === val; }).not(':disabled').prop('checked', true).trigger('change'); });
            },
            error:function() { if (version === depositRequestVersion) $('#deposit_context_message').text('Daftar dokumen gagal dimuat.'); }
        });
        if (!isDepositKind) {
            $('#customer_deposit_badge_wrap').show();
            $('#lbl_cust_deposit').text('Memuat saldo...');
            $.ajax({type:'post', url:siteurl+active_controller+'get_customer_deposit', data:{id_customer:customer}, dataType:'json',
                success:function(res) {
                    if (version !== depositRequestVersion) return;
                    if (res.status !== 1) {
                        $('#lbl_cust_deposit').text('Belum dapat digunakan');
                        $('#deposit_context_message').text(res.error || res.message || 'Saldo perlu rekonsiliasi.');
                        return;
                    }
                    custDepositBalance = Number(res.saldo) || 0;
                    depositByCompany = res.by_company || [];
                    $('#lbl_cust_deposit').text(res.formatted);
                    // Link kartu deposit hanya bila saldo > 0 (mockup 753); nominal maksimal ikut saldo.
                    $('#wrap_kartu_link').toggle(custDepositBalance > 0);
                    $('#dep_max_label').text(String(res.formatted || '').replace(/^Rp\s*/, ''));
                    $('#deposit_choice').prop('disabled', custDepositBalance <= 0).trigger('chosen:updated');
                    if (preDepChoice === 'Y' && custDepositBalance > 0) {
                        $('#deposit_choice').val('Y').trigger('chosen:updated').trigger('change');
                        if (preDepAmount) $('#deposit_amount').val(preDepAmount);
                    }
                    updateDepositHint();
                },
                error:function() { if (version === depositRequestVersion) { $('#lbl_cust_deposit').text('Belum tersedia'); $('#deposit_context_message').text('Saldo gagal dimuat. Muat ulang sebelum menggunakan deposit.'); } }
            });
        }
    }

    // Deposit hanya boleh menutup invoice dari company yang sama dengan voucher; tampilkan angka yang bisa dipakai.
    function updateDepositHint() {
        var group = selectedGroup();
        var hint = '';
        if (group && group.id !== 'legacy' && depositByCompany.length) {
            var match = depositByCompany.filter(function(row) { return String(row.id) === String(group.id); })[0];
            hint = 'Bisa dipakai untuk ' + group.name + ': ' + (match ? match.formatted : 'Rp 0,00');
        } else if (depositByCompany.length > 1) {
            hint = 'Saldo per company: ' + depositByCompany.map(function(row) { return row.name + ' ' + row.formatted; }).join(', ');
        }
        $('#deposit_context_message').text(hint);
    }

    function selectedGroup() {
        var first = $('.tbody_list input[name="choose_inv[]"]:checked').first();
        return first.length ? {id: String(first.data('group')), name: String(first.data('group-name'))} : null;
    }

    // Satu penerimaan hanya untuk satu company: setelah satu dokumen dicentang, dokumen company lain dikunci.
    $(document).on('change', '.tbody_list input[name="choose_inv[]"]', function() {
        var group = selectedGroup();
        $('.tbody_list input[name="choose_inv[]"]').each(function() {
            var other = group && String($(this).data('group')) !== group.id;
            if (other) $(this).prop('checked', false);
            $(this).prop('disabled', !!other).attr('title', other ? 'Satu penerimaan hanya untuk satu company (' + group.name + ')' : '');
        });
        if (!isDepositKind) {
            var legacy = !!group && group.id === 'legacy';
            if (legacy) { $('#deposit_choice').val('N'); $('#wrap_deposit_amount').hide(); $('#deposit_amount').val(''); }
            $('#deposit_choice').prop('disabled', legacy || custDepositBalance <= 0).trigger('chosen:updated');
            updateDepositHint();
        }
    });

    $(document).on('change', '#customer', function() {
        // Ganti customer mereset pilihan PPh dan deposit (mockup newForm).
        if (!isDepositKind) { $('#pph23_dipotong').val('').trigger('chosen:updated'); }
        formMsg('');
        loadInvoices(false);
    });

    $(document).on('change', '#pph23_dipotong', function() {
        loadInvoices(true);
    });

    $(document).on('change', '#deposit_choice', function() {
        if ($(this).val() === 'Y') {
            $('#wrap_deposit_amount').show();
        } else {
            $('#wrap_deposit_amount').hide();
            $('#deposit_amount').val('');
        }
    });

    // Nominal deposit dibatasi sebesar saldo (mockup 766), disertai pesan.
    $(document).on('change', '#deposit_amount', function() {
        var v = parseFloat($(this).autoNumeric('get')) || 0;
        if (v > custDepositBalance) {
            $(this).autoNumeric('set', custDepositBalance);
            puToast('Nominal deposit dibatasi sebesar saldo ' + $('#lbl_cust_deposit').text() + '.');
        }
    });

    function puToast(msg) {
        $('.pu-toast').remove();
        var t = $('<div class="pu-toast"></div>').text(msg).appendTo('body');
        setTimeout(function() { t.remove(); }, 2600);
    }

    function formMsg(text) {
        $('#fmsg').html(text ? '<span class="state-bad">' + ledgerText(text) + '</span>' : '');
    }

    function ledgerText(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function(ch) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]; });
    }

    // View Kartu Deposit Modal
    $(document).on('click', '#btn_view_kartu_deposit', function() {
        var cust = $('#customer').val();
        if (!cust) return;
        $('#kartu_dep_warning').hide().text('');
        $('#kartu_dep_cust_name').text($('#customer option:selected').text());
        var ledgerVersion = depositRequestVersion;
        $('#tbody_kartu_dep').html('<tr><td colspan="6" class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat riwayat mutasi deposit...</td></tr>');
        $('#modal_kartu_deposit').modal('show');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'get_customer_deposit_ledger',
            data: { id_customer: cust },
            dataType: 'json',
            success: function(resp) {
                if (ledgerVersion !== depositRequestVersion) return;
                if (resp.error) { $('#deposit_context_message').text(resp.error); $('#kartu_dep_warning').text(resp.error).show(); }
                if (resp.status === 1) {
                    $('#kartu_saldo_aktif').text(resp.saldo_aktif);

                    if (resp.ledger && resp.ledger.length > 0) {
                        var html = '';
                        $.each(resp.ledger, function(idx, item) {
                            html += '<tr>';
                            html += '<td class="nowrap">' + ledgerText(item.tanggal) + '</td>';
                            html += '<td class="nowrap">' + ledgerText(item.referensi) + '</td>';
                            html += '<td>' + ledgerText(item.keterangan) + '</td>';
                            html += '<td class="text-right">' + ledgerText(item.masuk) + '</td>';
                            html += '<td class="text-right">' + ledgerText(item.keluar) + '</td>';
                            html += '<td class="text-right">' + ledgerText(item.saldo) + '</td>';
                            html += '</tr>';
                        });
                        $('#tbody_kartu_dep').html(html);
                    } else {
                        $('#tbody_kartu_dep').html('<tr><td colspan="6" class="text-muted">Belum ada mutasi deposit untuk customer ini.</td></tr>');
                    }
                } else {
                    $('#tbody_kartu_dep').html('<tr><td colspan="6" class="text-center text-danger">Gagal memuat kartu deposit.</td></tr>');
                }
            },
            error: function() {
                if (ledgerVersion !== depositRequestVersion) return;
                $('#tbody_kartu_dep').html('<tr><td colspan="6" class="text-center text-danger">Terjadi kesalahan server saat memuat kartu deposit.</td></tr>');
            }
        });
    });

    $(document).on('submit', '#form_data', function(e) {
        e.preventDefault();

        var customer = $('#customer').val();
        var pph23_dipotong = $('#pph23_dipotong').val();
        var depositChoice = $('#deposit_choice').val();
        var check_data = $('input[name="choose_inv\\[\\]"]:checked').length;

        // Urutan & teks validasi mockup (771-774): customer, invoice, PPh, nominal deposit.
        formMsg('');
        if (customer === '') { formMsg('Pilih customer terlebih dahulu.'); return false; }
        if (check_data < 1) { formMsg(isDepositKind ? 'Centang minimal satu kwitansi deposit.' : 'Centang minimal satu invoice.'); return false; }
        if (!isDepositKind && pph23_dipotong === '') { formMsg('Pilih PPH23 Dipotong atau Tidak Dipotong.'); return false; }
        if (!isDepositKind && depositChoice === 'Y') {
            var depVal = parseFloat($('#deposit_amount').autoNumeric('get')) || 0;
            if (depVal <= 0) { formMsg('Isi Nominal Deposit Dipakai.'); return false; }
            if (depVal > custDepositBalance) { formMsg('Nominal deposit melebihi saldo deposit aktif customer (' + $('#lbl_cust_deposit').text() + ').'); return false; }
        }

        var previewVersion = depositRequestVersion;
        var formData = $(this).serialize();
        $('#MyModalBody').html('<div class="text-center" style="padding: 40px;"><i class="fa fa-spinner fa-spin fa-3x text-primary"></i><p style="margin-top: 10px;">Menyiapkan preview alokasi & jurnal...</p></div>');
        $('#dialog-popup').modal('show');

        $.ajax({
            type: 'post',
            url: siteurl + active_controller + <?= json_encode($isDeposit ? 'process_kwitansi_deposit' : 'process_alokasi') ?>,
            data: formData,
            cache: false,
            success: function(result) {
                if (previewVersion !== depositRequestVersion) return;
                $('#MyModalBody').html(result);
                $('.autonum').autoNumeric('init', {
                    vMin: '-9999999999999.99',
                    vMax: '9999999999999.99',
                    aSep: ',',
                    aDec: '.'
                });
                // Modal invoice lama mengisi Penerimaan default = Piutang Dagang; hitung total/kontrol/jurnal sejak awal.
                if (typeof hitungAll === 'function') hitungAll();
            },
            error: function(xhr) {
                if (previewVersion !== depositRequestVersion) return;
                // Tampilkan kode HTTP agar kegagalan server (500) bisa dibedakan dari jaringan; detail ada di application/logs.
                var detail = xhr && xhr.status ? ' (HTTP ' + xhr.status + ', detail di log server)' : ' (tidak ada respons dari server)';
                swal({
                    type: 'error',
                    title: 'Error !',
                    text: 'Gagal memproses preview alokasi' + detail + '. Coba lagi nanti.',
                    timer: 5000
                });
                $('#dialog-popup').modal('hide');
            }
        });
    });

    $(document).on('submit', '#frm_data', function(e) {
        e.preventDefault();

        var kontrolVal = parseFloat($('input[name="kontrol"]').val()) || 0;
        if (Math.abs(kontrolVal) > 0.009) {
            swal({
                type: 'warning',
                title: 'Kontrol Belum 0 !',
                text: 'Kontrol harus 0 sebelum penerimaan dapat disimpan.',
                timer: 3000
            });
            return false;
        }

        var debitJurnal = parseFloat($('input[name="total_debit_jurnal"]').val()) || 0;
        var kreditJurnal = parseFloat($('input[name="total_kredit_jurnal"]').val()) || 0;

        if (Math.abs(debitJurnal - kreditJurnal) > 0.009) {
            swal({
                type: 'error',
                title: 'Jurnal Tidak Balance !',
                text: 'Total Debit tidak sama dengan Total Kredit. Harap periksa kembali alokasi.',
                timer: 3000
            });
            return false;
        }

        swal({
            type: 'warning',
            title: 'Konfirmasi',
            text: isDepositKind ? 'Simpan penerimaan kwitansi deposit dan bentuk jurnal?' : 'Simpan penerimaan piutang dan bentuk jurnal?',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#00a65a'
        }, function(isConfirm) {
            if (isConfirm) {
                var postData;
                try {
                    postData = document.getElementById('consulting-receipt-container')
                        ? $.param(ConsultingReceiptMoney.normalizeFields($('#frm_data').serializeArray()))
                        : $('#frm_data').serialize();
                } catch (error) {
                    swal({type: 'warning', title: 'Nominal Tidak Valid', text: error.message});
                    return;
                }
                $.ajax({
                    type: 'post',
                    url: siteurl + active_controller + <?= json_encode($isDeposit ? 'save_kwitansi_deposit' : 'save_penerimaan_piutang') ?>,
                    data: postData,
                    cache: false,
                    dataType: 'json',
                    success: function(result) {
                        if (result.status == '1' || result.save == '1') {
                            // Mockup (869-870): buka halaman hasil penerimaan + pesan singkat.
                            var noSurat = result.no_surat || '';
                            var custName = $('#customer option:selected').text();
                            var doneMsg = isDepositKind
                                ? 'Penerimaan ' + noSurat + ' tersimpan. Saldo deposit ' + custName + ' bertambah.'
                                : 'Penerimaan piutang tersimpan dan jurnal terbentuk.';
                            try { sessionStorage.setItem('pu_flash', doneMsg); } catch (err) {}
                            window.location.href = noSurat
                                ? siteurl + active_controller + 'view_surat?no=' + encodeURIComponent(noSurat)
                                : siteurl + active_controller;
                        } else {
                            swal({
                                type: 'warning',
                                title: 'Gagal Menyimpan',
                                text: result.msg || result.message || 'Terjadi kesalahan saat menyimpan.',
                                timer: 4000
                            });
                        }
                    },
                    error: function() {
                        swal({
                            type: 'error',
                            title: 'Error !',
                            text: 'Gagal menghubungi server. Silakan coba kembali.',
                            timer: 3000
                        });
                    }
                });
            }
        });
    });
</script>
