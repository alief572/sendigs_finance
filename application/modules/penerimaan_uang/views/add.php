<?php
$isDeposit = (($results['receipt_kind'] ?? 'invoice') === 'deposit');
?>
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
</style>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title" style="font-weight: 700; color: #2c3e50;">
            <i class="fa fa-pencil-square-o"></i> <?= $isDeposit ? 'Proses Penerimaan Piutang Kwitansi Deposit' : 'Proses Penerimaan Piutang Invoice' ?>
        </h3>
        <div class="box-tools pull-right">
            <span class="label <?= $isDeposit ? 'label-info' : 'label-primary' ?>" style="font-size: 12px;">
                <?= $isDeposit ? 'Jalur Khusus Kwitansi Deposit' : 'Penerimaan Invoice Konsultasi' ?>
            </span>
        </div>
    </div>
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
                    </div>

                    <div class="form-group">
                        <label for="receipt_company">Company <span class="text-red">*</span></label>
                        <select class="form-control" id="receipt_company" name="id_company" required><option value="">Pilih customer terlebih dahulu</option></select>
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
                                <option value="Y" selected>Dipotong PPh</option>
                                <option value="N">Tidak Dipotong PPh</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deposit_choice">Deposit Customer</label>
                                    <select class="form-control form-control-sm" name="deposit_choice" id="deposit_choice">
                                        <option value="N" selected>Tidak Pakai Deposit</option>
                                        <option value="Y">Pakai Deposit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" id="wrap_deposit_amount" style="display:none;">
                                <div class="form-group">
                                    <label for="deposit_amount">Nominal Deposit Dipakai</label>
                                    <input type="text" name="deposit_amount" id="deposit_amount" class="form-control form-control-sm text-right" value="0.00">
                                </div>
                            </div>
                        </div>
                        <div id="customer_deposit_badge_wrap" style="display:none; margin-bottom: 12px;">
                            <span class="label label-info" style="font-size: 12px; padding: 4px 8px; display: inline-block;">
                                <i class="fa fa-money"></i> Saldo aktif: <strong id="lbl_cust_deposit">Rp 0,00</strong>
                            </span>
                            <button type="button" class="btn btn-xs btn-default" id="btn_view_kartu_deposit" style="margin-left: 8px; font-weight: 600;">
                                <i class="fa fa-credit-card text-primary"></i> Lihat kartu deposit
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nominal_penerimaan_bank">Nominal Penerimaan Bank <span class="text-red">*</span></label>
                        <input type="text" name="nominal_penerimaan_bank" id="nominal_penerimaan_bank" class="form-control form-control-sm text-right" value="<?= $results['nominal_penerimaan_bank'] ?>" readonly style="font-size: 16px; font-weight: 700; background-color: #f8fafc;">
                    </div>
                    <?php if ($isDeposit): ?>
                        <div class="alert alert-info" style="margin-top: 10px; padding: 10px 14px; font-size: 12px; border-radius: 4px;">
                            <i class="fa fa-info-circle"></i> <strong>Pelunasan Kwitansi Deposit:</strong> Pembayaran bank ini akan mengaktifkan saldo deposit customer sebesar nilai kwitansi utuh. Biaya admin (jika ada) ditanggung perusahaan.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info" style="margin-top: 10px; padding: 10px 14px; font-size: 12px; border-radius: 4px;">
                            <i class="fa fa-info-circle"></i> Jenis perlakuan PPN (PKP, WAPU, Kawasan Berikat) terkunci otomatis dari data invoice asal.
                        </div>
                    <?php endif; ?>
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
                                        <th class="text-right" style="width: 120px;">Total Tagihan<br><small class="text-muted font-normal">(Penjualan+PPN)</small></th>
                                        <th class="text-right" style="width: 120px;">Total Tagihan<br><small class="text-muted font-normal">- PPH</small></th>
                                        <th class="text-right" style="width: 120px;">Saldo Piutang</th>
                                        <th class="text-center" style="width: 60px;">Action</th>
                                    </tr>
                                <?php endif; ?>
                            </thead>
                            <tbody class="tbody_list">
                                <tr>
                                    <td colspan="<?= $isDeposit ? 7 : 11 ?>" class="text-center text-muted" style="padding: 20px;">
                                        Pilih Customer terlebih dahulu untuk memunculkan daftar tagihan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top: 20px;">
                <div class="col-md-12">
                    <a href="<?= base_url('penerimaan_uang') ?>" class="btn btn-default" style="margin-right: 10px;">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-success btn_submit_process">
                        <i class="fa fa-check"></i> Proses Alokasi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Kartu Deposit Customer -->
<div class="modal fade" id="modal_kartu_deposit" tabindex="-1" role="dialog" aria-labelledby="modalKartuDepTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 90% !important; max-width: 1100px;">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #0284c7; color: #fff;">
                <button type="button" class="close" data-dismiss="modal" style="color: #fff; opacity: 0.9;">&times;</button>
                <h4 class="modal-title" id="modalKartuDepTitle"><i class="fa fa-credit-card"></i> Kartu Deposit Customer: <span id="kartu_dep_cust_name">-</span></h4>
            </div>
            <div class="modal-body" style="padding: 18px; max-height: 75vh; overflow-y: auto;">
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-4">
                        <div class="well well-sm text-center" style="background: #f0fdf4; border-color: #bbf7d0; margin-bottom: 0;">
                            <div style="font-size: 11px; text-transform: uppercase; color: #166534; font-weight: 700;">Total Deposit Masuk</div>
                            <div id="kartu_total_masuk" style="font-size: 18px; font-weight: 700; color: #15803d; margin-top: 4px;">Rp 0,00</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="well well-sm text-center" style="background: #fef2f2; border-color: #fecaca; margin-bottom: 0;">
                            <div style="font-size: 11px; text-transform: uppercase; color: #991b1b; font-weight: 700;">Total Terpakai</div>
                            <div id="kartu_total_terpakai" style="font-size: 18px; font-weight: 700; color: #b91c1c; margin-top: 4px;">Rp 0,00</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="well well-sm text-center" style="background: #eff6ff; border-color: #bfdbfe; margin-bottom: 0;">
                            <div style="font-size: 11px; text-transform: uppercase; color: #1e40af; font-weight: 700;">Sisa Saldo Aktif</div>
                            <div id="kartu_saldo_aktif" style="font-size: 18px; font-weight: 700; color: #1d4ed8; margin-top: 4px;">Rp 0,00</div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <div id="kartu_dep_warning" class="alert alert-warning" role="status" style="display:none;"></div>
                    <table class="table table-bordered table-striped table-hover" id="table_kartu_dep" style="font-size: 12px; margin-bottom: 0;">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th class="text-center" width="40">No</th>
                                <th class="text-center" width="95">Tanggal</th>
                                <th class="text-center" width="70">Tipe</th>
                                <th>Sumber / Referensi</th>
                                <th>No. Dokumen</th>
                                <th>No. Transaksi</th>
                                <th>Keterangan</th>
                                <th class="text-right" width="120">Masuk (+)</th>
                                <th class="text-right" width="120">Keluar (-)</th>
                                <th class="text-right" width="130">Saldo</th>
                            </tr>
                        </thead>
                        <tbody id="tbody_kartu_dep">
                            <tr><td colspan="10" class="text-center text-muted">Memuat data kartu deposit...</td></tr>
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
                <h4 class="modal-title" id="myModalLabel"><i class="fa fa-money"></i>&nbsp; <?= $isDeposit ? 'Konfirmasi & Jurnal Penerimaan Piutang Kwitansi Deposit' : 'Penerimaan Piutang & Jurnal Otomatis' ?></h4>
            </div>
            <form action="" id="frm_data" enctype="multipart/form-data">
                <div class="modal-body" id="MyModalBody" style="max-height: 80vh; overflow-y: auto; padding: 15px;">
                    <!-- Konten alokasi dimuat via AJAX process_alokasi / process_kwitansi_deposit -->
                </div>
                <div class="modal-footer" style="background-color: #f8fafc;">
                    <button type="button" class="btn btn-default" data-dismiss="modal">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-success btn_proses" disabled>
                        <i class="fa fa-save"></i> Simpan Penerimaan & Bentuk Jurnal
                    </button>
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
            $('#deposit_amount').autoNumeric('set', '0.00');
        }
    }

    function loadInvoices() {
        resetReceiptContext();
        var customer = $('#customer').val(), company = $('#receipt_company').val();
        var version = depositRequestVersion;
        if (!customer || !company) return;
        var context = {id_customer:customer, id_company:company, receipt_kind:isDepositKind ? 'deposit' : 'invoice', pph23_dipotong:$('#pph23_dipotong').val() || 'N'};
        $.ajax({type:'post', url:siteurl+active_controller+'get_inv_by_cust', data:context,
            success:function(html) { if (version === depositRequestVersion) { $('.tbody_list').html(html); applyKwitansiHint(); } },
            error:function() { if (version === depositRequestVersion) $('#deposit_context_message').text('Daftar dokumen gagal dimuat.'); }
        });
        if (!isDepositKind && company !== 'legacy') {
            $('#customer_deposit_badge_wrap').show();
            $('#lbl_cust_deposit').text('Memuat saldo...');
            $.ajax({type:'post', url:siteurl+active_controller+'get_customer_deposit', data:context, dataType:'json',
                success:function(res) {
                    if (version !== depositRequestVersion) return;
                    if (res.status !== 1) {
                        $('#lbl_cust_deposit').text('Belum dapat digunakan');
                        $('#deposit_context_message').text(res.error || res.message || 'Saldo perlu rekonsiliasi.');
                        return;
                    }
                    custDepositBalance = Number(res.saldo) || 0;
                    $('#lbl_cust_deposit').text(res.formatted);
                    $('#deposit_choice').prop('disabled', custDepositBalance <= 0).trigger('chosen:updated');
                },
                error:function() { if (version === depositRequestVersion) { $('#lbl_cust_deposit').text('Belum tersedia'); $('#deposit_context_message').text('Saldo gagal dimuat. Muat ulang sebelum menggunakan deposit.'); } }
            });
        }
    }

    $(document).on('change', '#customer', function() {
        resetReceiptContext();
        $('#receipt_company').empty().append($('<option>').val('').text('Pilih company'));
        var customer = $('#customer').val(), version = depositRequestVersion;
        if (!customer) return;
        $.ajax({type:'post', url:siteurl+active_controller+'get_receipt_companies', data:{id_customer:customer, receipt_kind:isDepositKind ? 'deposit' : 'invoice'}, dataType:'json',
            success:function(res) {
                if (version !== depositRequestVersion) return;
                if (res.status !== 1) { $('#deposit_context_message').text(res.message); return; }
                res.companies.forEach(function(company) { $('#receipt_company').append($('<option>').val(company.id).text(company.name)); });
                if (res.companies.length === 1) { $('#receipt_company').val(res.companies[0].id); loadInvoices(); }
            },
            error:function() { if (version === depositRequestVersion) $('#deposit_context_message').text('Company gagal dimuat.'); }
        });
    });
    $(document).on('change', '#receipt_company', loadInvoices);

    $(document).on('change', '#pph23_dipotong', function() {
        loadInvoices();
    });

    $(document).on('change', '#deposit_choice', function() {
        if ($(this).val() === 'Y') {
            $('#wrap_deposit_amount').show();
            if (custDepositBalance > 0) {
                $('#deposit_amount').autoNumeric('update', { vMax: custDepositBalance.toString() });
            }
        } else {
            $('#wrap_deposit_amount').hide();
            $('#deposit_amount').autoNumeric('set', '0.00');
        }
    });

    function ledgerText(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function(ch) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]; });
    }

    // View Kartu Deposit Modal
    $(document).on('click', '#btn_view_kartu_deposit', function() {
        var cust = $('#customer').val();
        if (!cust) return;
        $('#kartu_dep_warning').hide().text('');
        $('#kartu_dep_cust_name').text($('#customer option:selected').text() + ' / ' + $('#receipt_company option:selected').text());
        var ledgerVersion = depositRequestVersion;
        $('#tbody_kartu_dep').html('<tr><td colspan="10" class="text-center"><i class="fa fa-spinner fa-spin"></i> Memuat riwayat mutasi deposit...</td></tr>');
        $('#modal_kartu_deposit').modal('show');

        $.ajax({
            type: 'POST',
            url: siteurl + active_controller + 'get_customer_deposit_ledger',
            data: { id_customer: cust, id_company: $('#receipt_company').val() },
            dataType: 'json',
            success: function(resp) {
                if (ledgerVersion !== depositRequestVersion) return;
                if (resp.error) { $('#deposit_context_message').text(resp.error); $('#kartu_dep_warning').text(resp.error).show(); }
                if (resp.status === 1) {
                    $('#kartu_total_masuk').text(resp.total_masuk);
                    $('#kartu_total_terpakai').text(resp.total_keluar);
                    $('#kartu_saldo_aktif').text(resp.saldo_aktif);

                    if (resp.ledger && resp.ledger.length > 0) {
                        var html = '';
                        var no = 1;
                        $.each(resp.ledger, function(idx, item) {
                            var badgeTipe = item.tipe === 'IN'
                                ? '<span class="label label-success">MASUK</span>'
                                : '<span class="label label-danger">KELUAR</span>';
                            html += '<tr>';
                            html += '<td class="text-center">' + (no++) + '</td>';
                            html += '<td class="text-center nowrap">' + ledgerText(item.tanggal) + '</td>';
                            html += '<td class="text-center">' + badgeTipe + '</td>';
                            html += '<td>' + ledgerText(item.sumber) + '</td>';
                            html += '<td><code>' + ledgerText(item.no_dokumen) + '</code></td>';
                            html += '<td>' + ledgerText(item.no_transaksi) + '</td>';
                            html += '<td>' + ledgerText(item.keterangan) + '</td>';
                            html += '<td class="text-right text-success font-bold">' + (item.masuk !== '0,00' ? ledgerText(item.masuk) : '-') + '</td>';
                            html += '<td class="text-right text-danger font-bold">' + (item.keluar !== '0,00' ? ledgerText(item.keluar) : '-') + '</td>';
                            html += '<td class="text-right font-bold">' + ledgerText(item.saldo) + '</td>';
                            html += '</tr>';
                        });
                        $('#tbody_kartu_dep').html(html);
                    } else {
                        $('#tbody_kartu_dep').html('<tr><td colspan="10" class="text-center text-muted" style="padding: 20px;">Belum ada riwayat mutasi deposit untuk customer ini.</td></tr>');
                    }
                } else {
                    $('#tbody_kartu_dep').html('<tr><td colspan="10" class="text-center text-danger">Gagal memuat kartu deposit.</td></tr>');
                }
            },
            error: function() {
                if (ledgerVersion !== depositRequestVersion) return;
                $('#tbody_kartu_dep').html('<tr><td colspan="10" class="text-center text-danger">Terjadi kesalahan server saat memuat kartu deposit.</td></tr>');
            }
        });
    });

    $(document).on('submit', '#form_data', function(e) {
        e.preventDefault();

        var customer = $('#customer').val();
        var pph23_dipotong = $('#pph23_dipotong').val();
        var depositChoice = $('#deposit_choice').val();
        var check_data = $('input[name="choose_inv\\[\\]"]:checked').length;

        if (customer === '' || !$('#receipt_company').val()) {
            swal({
                type: 'warning',
                title: 'Peringatan !',
                text: 'Customer dan company wajib dipilih !',
                timer: 3000
            });
            return false;
        }

        if (!isDepositKind && pph23_dipotong === '') {
            swal({
                type: 'warning',
                title: 'Peringatan !',
                text: 'Pilihan PPH23 Dipotong wajib dipilih !',
                timer: 3000
            });
            return false;
        }

        if (!isDepositKind && depositChoice === 'Y') {
            var depVal = parseFloat($('#deposit_amount').autoNumeric('get')) || 0;
            if (depVal <= 0) {
                swal({
                    type: 'warning',
                    title: 'Peringatan !',
                    text: 'Nominal deposit yang dipakai harus lebih besar dari 0 !',
                    timer: 3000
                });
                return false;
            }
            if (depVal > custDepositBalance) {
                swal({
                    type: 'warning',
                    title: 'Peringatan !',
                    text: 'Nominal deposit melebihi saldo deposit aktif customer (' + $('#lbl_cust_deposit').text() + ') !',
                    timer: 3000
                });
                return false;
            }
        }

        if (check_data < 1) {
            swal({
                type: 'warning',
                title: 'Peringatan !',
                text: isDepositKind ? 'Centang minimal satu Kwitansi Deposit yang akan dilunasi !' : 'Centang minimal satu invoice yang akan dialokasikan !',
                timer: 3000
            });
            return false;
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
            title: 'Konfirmasi Simpan',
            text: isDepositKind ? 'Simpan pelunasan kwitansi deposit dan aktifkan saldo deposit customer?' : 'Simpan penerimaan piutang dan bentuk jurnal otomatis?',
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
                            swal({
                                type: 'success',
                                title: 'Berhasil !',
                                text: result.msg || result.message || 'Penerimaan piutang berhasil disimpan.',
                                timer: 2500
                            }, function() {
                                window.location.href = siteurl + active_controller;
                            });
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
