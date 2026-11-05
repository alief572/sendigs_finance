<?php
$isDeposit = (($results['receipt_kind'] ?? 'invoice') === 'deposit');
?>
<?= mk_ui_assets() ?>
<style>
    /* Modal alokasi memakai gaya modal mockup: header polos 18px, footer [Proses] [Batal] rata kanan. */
    #dialog-popup .modal-header, #modal_kartu_deposit .modal-header { background: #fff; color: #333; border-bottom: 1px solid #e3e6ea; padding: 12px 16px; }
    #dialog-popup .modal-title, #modal_kartu_deposit .modal-title { font-size: 18px; font-weight: 400; }
    #dialog-popup .modal-footer, #modal_kartu_deposit .modal-footer { border-top: 1px solid #e3e6ea; background: #fff; text-align: right; }
    #dialog-popup .close, #modal_kartu_deposit .close { color: #777; opacity: 1; font-size: 22px; }
    .pu-toast-host { display: none; }
</style>
<div class="mk">
<?php if ($isDeposit): ?>
    <div class="crumb"><a href="<?= base_url('penerimaan_uang') ?>#tab_kwitansi">Penerimaan Piutang Kwitansi Deposit</a> / Proses</div>
<?php endif; ?>

<div class="box primary">
    <form action="" method="post" id="form_data">
        <input type="hidden" name="receipt_kind" value="<?= htmlspecialchars($results['receipt_kind'] ?? 'invoice', ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="id_alokasi" value="<?= $results['id_alokasi'] ?>">
        <div class="grid2">
            <div>
                <div class="fg">
                    <label for="customer">Nama Customer <span class="req">*</span></label>
                    <select name="id_customer" id="customer">
                        <option value="">- Pilih Customer -</option>
                        <?php foreach ($results['list_customer'] as $item) {
                            echo '<option value="' . htmlspecialchars($item['id_customer'], ENT_QUOTES) . '">' . htmlspecialchars($item['nm_customer']) . '</option>';
                        } ?>
                    </select>
                    <?php if ($isDeposit): ?><span class="hint">Hanya customer yang punya Kwitansi Deposit belum lunas.</span><?php endif; ?>
                </div>
                <div id="deposit_context_message" role="status" style="display:none"></div>
                <?php if ($isDeposit): ?>
                    <input type="hidden" name="pph23_dipotong" id="pph23_dipotong" value="N">
                    <input type="hidden" name="deposit_choice" id="deposit_choice" value="N">
                    <input type="hidden" name="deposit_amount" id="deposit_amount" value="0.00">
                <?php else: ?>
                    <div class="fg">
                        <label for="pph23_dipotong">PPH23 Dipotong <span class="req">*</span></label>
                        <select name="pph23_dipotong" id="pph23_dipotong">
                            <option value="" selected>- Pilih -</option>
                            <option value="Y">Dipotong PPh</option>
                            <option value="N">Tidak Dipotong PPh</option>
                        </select>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <?php if ($isDeposit): ?>
                    <div class="fg"><label>Tanggal &amp; Bank</label><input type="text" readonly value="<?= htmlspecialchars($results['tanggal_bank'] ?? '-', ENT_QUOTES, 'UTF-8') ?>"></div>
                <?php else: ?>
                    <div class="fg">
                        <label for="deposit_choice">Deposit <span class="req">*</span></label>
                        <select name="deposit_choice" id="deposit_choice" disabled>
                            <option value="N" selected>Tidak Pakai Deposit</option>
                            <option value="Y">Pakai Deposit</option>
                        </select>
                        <span class="hint" id="customer_deposit_badge_wrap" style="display:none">Saldo deposit: <span id="lbl_cust_deposit">Rp 0,00</span><span id="wrap_kartu_link"> &middot; <button type="button" class="btn-link" id="btn_view_kartu_deposit">Lihat kartu deposit</button></span></span>
                    </div>
                    <div class="fg" id="wrap_deposit_amount" style="display:none">
                        <label for="deposit_amount">Nominal Deposit Dipakai <span class="req">*</span></label>
                        <input type="text" name="deposit_amount" id="deposit_amount" class="num" value="" placeholder="0,00" inputmode="decimal">
                        <span class="hint">Diisi sendiri, maksimal Rp <span id="dep_max_label">0,00</span>. Saat Process, nominal ini dibagi ke invoice yang dicentang.</span>
                    </div>
                <?php endif; ?>
                <div class="fg">
                    <label for="nominal_penerimaan_bank">Nominal Penerimaan Bank <span class="req">*</span></label>
                    <input type="text" name="nominal_penerimaan_bank" id="nominal_penerimaan_bank" class="num" value="<?= $results['nominal_penerimaan_bank'] ?>" readonly>
                </div>
            </div>
        </div>

        <div class="scroll" style="margin-top: 12px;">
            <table>
                <thead>
                    <?php if ($isDeposit): ?>
                        <tr><th>Tgl Kwitansi</th><th>No Kwitansi</th><th>No SPK</th><th>Company</th><th class="r">Nilai Kwitansi</th><th class="r">Saldo Piutang</th><th class="c">Action</th></tr>
                    <?php else: ?>
                        <tr><th>Tgl Invoice</th><th>Nomor Invoice</th><th>TAX Code</th><th class="r">Penjualan</th><th class="r">DPP</th><th class="r">PPN</th><th class="r">PPH 23</th><th class="r">Total Tagihan<br>(Penjualan + PPN)</th><th class="r">Total Tagihan<br>- PPH</th><th class="r">Saldo Piutang</th><th class="c">Action</th></tr>
                    <?php endif; ?>
                </thead>
                <tbody class="tbody_list">
                    <tr><td colspan="<?= $isDeposit ? 7 : 11 ?>" class="empty"><?= $isDeposit ? 'Pilih customer untuk menampilkan kwitansi deposit yang masih terbuka.' : 'No Data Found' ?></td></tr>
                </tbody>
            </table>
        </div>

        <div class="inline" style="margin-top: 12px;">
            <a href="<?= base_url('penerimaan_uang') . ($isDeposit ? '#tab_kwitansi' : '') ?>" class="btn btn-danger">Back</a>
            <button type="submit" class="btn btn-success btn_submit_process">Process</button>
            <span class="hint" id="fmsg"></span>
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

<?php if (!isset($GLOBALS['pu_s2'])) : $GLOBALS['pu_s2'] = 1; ?><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css"><script>window.jQuery && !jQuery.fn.select2 && document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"><\/script>');</script><?php endif; ?>
<script src="<?= base_url('assets/js/autoNumeric.js') ?>"></script>
<script src="<?= base_url('assets/js/penerimaan_uang/consulting.js') ?>"></script>
<script>
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) jQuery(function ($) { $('.mk select').select2({width: '100%'}); });
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
        if (kwitansiHint && $('#customer option[value="' + kwitansiHint.id_customer + '"]').length) {
            $('#customer').val(kwitansiHint.id_customer).trigger('change');
        }

        if (!isDepositKind) {
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
            $('#deposit_choice').val('N').prop('disabled', true);
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
                    $('#deposit_choice').prop('disabled', custDepositBalance <= 0);
                    if (preDepChoice === 'Y' && custDepositBalance > 0) {
                        $('#deposit_choice').val('Y').trigger('change');
                        if (preDepAmount) $('#deposit_amount').val(preDepAmount);
                    }
                    updateDepositHint();
                },
                error:function() { if (version === depositRequestVersion) { $('#lbl_cust_deposit').text('Belum tersedia'); $('#deposit_context_message').text('Saldo gagal dimuat. Muat ulang sebelum menggunakan deposit.'); } }
            });
        }
    }

    // Deposit per customer (mockup): tidak ada pembatasan company; saldo yang dipakai = saldo total customer.
    function updateDepositHint() {
        $('#deposit_context_message').text('');
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
            $('#deposit_choice').prop('disabled', custDepositBalance <= 0);
            updateDepositHint();
        }
    });

    $(document).on('change', '#customer', function() {
        // Ganti customer mereset pilihan PPh dan deposit (mockup newForm).
        if (!isDepositKind) { $('#pph23_dipotong').val(''); }
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
            MockupUI.toast('Nominal deposit dibatasi sebesar saldo ' + $('#lbl_cust_deposit').text() + '.');
        }
    });

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
                MockupUI.toast('Gagal memproses preview alokasi' + detail + '. Coba lagi nanti.');
                $('#dialog-popup').modal('hide');
            }
        });
    });

    $(document).on('submit', '#frm_data', function(e) {
        e.preventDefault();
        // Tombol Proses sudah nonaktif sampai Kontrol 0 dan jurnal seimbang (mockup), jadi tidak ada pre-check tambahan di sini.
        MockupUI.confirmBox('Konfirmasi', isDepositKind ? 'Simpan penerimaan kwitansi deposit dan bentuk jurnal?' : 'Simpan penerimaan piutang dan bentuk jurnal?', function() {
            var postData;
            try {
                postData = document.getElementById('consulting-receipt-container')
                    ? $.param(ConsultingReceiptMoney.normalizeFields($('#frm_data').serializeArray()))
                    : $('#frm_data').serialize();
            } catch (error) {
                MockupUI.toast(error.message);
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
                        MockupUI.toast(result.msg || result.message || 'Terjadi kesalahan saat menyimpan.');
                    }
                },
                error: function() {
                    MockupUI.toast('Gagal menghubungi server. Silakan coba kembali.');
                }
            });
        });
    });
</script>
