<?php
defined('BASEPATH') or exit('No direct script access allowed');
$escape = function($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$availableCents = $allocation['capacity'] - $allocation['used'];
$availableDecimal = $availableCents / 100;
$isDeposit = (($receipt_kind ?? 'invoice') === 'deposit');
?>

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

    .tbl-alokasi th {
        background-color: #f4f6f9;
        font-weight: 700;
        font-size: 12px;
        color: #333;
        vertical-align: middle !important;
        white-space: nowrap;
    }
    .tbl-alokasi td {
        vertical-align: middle !important;
        font-size: 13px;
    }
    .tbl-summary-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 12px 16px;
        margin-top: 15px;
    }
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
        font-size: 13px;
    }
    .summary-item.highlight {
        font-weight: 700;
        border-top: 1px solid #cbd5e1;
        padding-top: 8px;
        margin-top: 4px;
    }
    .kontrol-badge {
        padding: 4px 12px;
        border-radius: 4px;
        font-weight: 700;
        font-size: 14px;
    }
    .kontrol-ok { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    .kontrol-bad { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .journal-section {
        margin-top: 20px;
        border-top: 2px dashed #e2e8f0;
        padding-top: 15px;
    }
</style>

<div id="consulting-receipt-container">
    <input type="hidden" name="id_customer" value="<?= $escape($rows[0]['invoice']['id_customer']) ?>">
    <input type="hidden" name="id_company" value="<?= $escape($rows[0]['owner']['id']) ?>">
    <?php if (!empty($deposit_error)): ?><div class="alert alert-warning"><?= $escape($deposit_error) ?> Penerimaan tanpa deposit tetap dapat diproses.</div><?php endif; ?>
    <input type="hidden" name="no_inv" value="<?= count($rows) ?>">
    <input type="hidden" name="id_alokasi" value="<?= $escape($id_alokasi) ?>">
    <input type="hidden" name="allocation_fingerprint" value="<?= $escape($allocation_fingerprint) ?>">
    <input type="hidden" name="pph23_dipotong" value="<?= $escape($choice) ?>">
    <input type="hidden" name="ppn_dipotong" value="<?= $escape($ppn_choice) ?>">
    <input type="hidden" name="deposit_choice" value="<?= $escape($deposit_choice ?? 'N') ?>">
    <input type="hidden" name="deposit_amount" value="<?= $escape(number_format(($deposit_amount ?? 0) / 100, 2, '.', '')) ?>">
    <input type="hidden" name="kontrol" id="input_kontrol" value="0">
    <input type="hidden" name="total_debit_jurnal" id="input_total_debit_jurnal" value="0">
    <input type="hidden" name="total_kredit_jurnal" id="input_total_kredit_jurnal" value="0">

    <?php if (!$isDeposit && ($deposit_choice ?? 'N') === 'Y' && ($deposit_amount ?? 0) > 0) : ?>
        <div class="alert alert-info" style="margin-bottom: 15px; padding: 10px 15px; border-radius: 4px;">
            <i class="fa fa-info-circle"></i> <strong>Deposit Diterapkan:</strong> Rp <?= number_format($deposit_amount / 100, 2, ',', '.') ?>
            (dari saldo Rp <?= number_format(($customer_deposit ?? 0) / 100, 2, ',', '.') ?> &mdash; estimasi sisa saldo: Rp <?= number_format((($customer_deposit ?? 0) - ($deposit_amount ?? 0)) / 100, 2, ',', '.') ?>).
            <span class="pull-right">
                <a href="javascript:void(0)" id="btn_redistribute_dep" class="text-white" style="text-decoration: underline; font-weight: 700;">
                    <i class="fa fa-refresh"></i> Distribusi Ulang FIFO
                </a>
            </span>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <?php if ($isDeposit) : ?>
            <!-- TABEL KHUSUS KWITANSI DEPOSIT (7 Kolom Ringkas) -->
            <table class="table table-bordered table-hover tbl-alokasi" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 160px;">Nomor Kwitansi</th>
                        <th class="text-center" style="width: 140px;">No SPK</th>
                        <th class="text-center" style="width: 130px;">Company</th>
                        <th class="text-right" style="width: 130px;">Nilai Kwitansi</th>
                        <th class="text-right" style="width: 130px;">Piutang Dagang</th>
                        <th class="text-right" style="width: 140px;">Alokasi Penerimaan</th>
                        <th class="text-right" style="width: 130px;">Biaya Admin<br><small class="text-muted font-normal">(maks 10rb)</small></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($rows as $index => $context) :
                        $n = $index + 1;
                        $inv = $context['invoice'];
                        $saldo = (float)$inv['saldo_piutang'];
                        $snapshot = json_decode($inv['consulting_snapshot'] ?? '', true);
                        $spkNo = $snapshot['spk'] ?? (!empty($context['spk']) ? $context['spk'] : '-');
                        $compName = $snapshot['owner']['nm_company'] ?? $context['owner']['nm_company'] ?? 'STM';
                    ?>
                        <tr data-row="<?= $n ?>">
                            <td class="text-center font-bold" style="font-family: monospace;">
                                <?= $escape($inv['no_invoice']) ?>
                                <input type="hidden" name="id_inv_<?= $n ?>" value="<?= $escape($inv['id']) ?>">
                                <input type="hidden" name="receipt_fingerprint_<?= $n ?>" value="<?= $escape($context['fingerprint']) ?>">
                                <input type="hidden" name="pph_actual_<?= $n ?>" value="0.00">
                                <input type="hidden" name="sisa_piutang_<?= $n ?>" value="0.00">
                                <input type="hidden" name="dep_<?= $n ?>" value="0.00">
                                <input type="hidden" name="lebih_bayar_<?= $n ?>" value="0.00">
                            </td>
                            <td class="text-center"><?= $escape($spkNo) ?></td>
                            <td class="text-center"><span class="label label-info"><?= $escape($compName) ?></span></td>
                            <td class="text-right font-bold"><?= number_format((float)$inv['total_nominal_jurnal'], 2) ?></td>
                            <td class="text-right font-bold bg-gray-light" id="cell_piutang_<?= $n ?>" data-piutang="<?= Penerimaan_calculation::cents($inv['saldo_piutang']) ?>">
                                <?= number_format($saldo, 2) ?>
                            </td>
                            <td class="text-right font-bold">
                                <input type="text" class="form-control input-sm text-right autonum input-alok"
                                    name="penerimaan_<?= $n ?>" id="penerimaan_<?= $n ?>" data-row="<?= $n ?>"
                                    value="<?= number_format($saldo, 2) ?>" readonly style="background-color: #f1f8e9;">
                            </td>
                            <td class="text-right">
                                <input type="text" class="form-control input-sm text-right autonum input-admin"
                                    name="biaya_admin_<?= $n ?>" id="biaya_admin_<?= $n ?>" data-row="<?= $n ?>"
                                    value="0.00">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-light font-bold">
                        <td colspan="4" class="text-right">Total:</td>
                        <td class="text-right" id="foot_tot_piutang">0.00</td>
                        <td class="text-right" id="foot_tot_alok">0.00</td>
                        <td class="text-right" id="foot_tot_admin">0.00</td>
                    </tr>
                </tfoot>
            </table>
        <?php else : ?>
            <!-- TABEL LENGKAP PENERIMAAN INVOICE -->
            <table class="table table-bordered table-hover tbl-alokasi" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 140px;">Nomor Invoice</th>
                        <th class="text-center" style="width: 70px;">TAX Code</th>
                        <th class="text-right" style="width: 110px;">Penjualan</th>
                        <th class="text-right" style="width: 100px;">DPP</th>
                        <th class="text-right" style="width: 90px;">PPN</th>
                        <th class="text-right" style="width: 120px;">
                            PPH 23
                            <?php if ($choice === 'Y') : ?>
                                <br><small class="text-muted font-normal">(bisa diedit)</small>
                            <?php endif; ?>
                        </th>
                        <th class="text-right" style="width: 115px;">Total Tagihan<br><small class="text-muted font-normal">(Penjualan+PPN)</small></th>
                        <th class="text-right" style="width: 115px;">Total Tagihan<br><small class="text-muted font-normal">- PPH</small></th>
                        <th class="text-right" style="width: 115px;">Piutang Dagang</th>
                        <th class="text-right" style="width: 120px;">Alokasi Penerimaan</th>
                        <th class="text-right" style="width: 110px;">Biaya Admin<br><small class="text-muted font-normal">(maks 10rb)</small></th>
                        <th class="text-right" style="width: 115px;">Sisa Piutang</th>
                        <?php if (($deposit_choice ?? 'N') === 'Y') : ?>
                            <th class="text-right" style="width: 115px;">Deposit Dipakai</th>
                        <?php endif; ?>
                        <th class="text-right" style="width: 115px;">Lebih Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $depositRemaining = (($deposit_choice ?? 'N') === 'Y') ? (int)($deposit_amount ?? 0) : 0;
                    $rowDeposits = array();
                    if ($depositRemaining > 0) {
                        $tempRem = $depositRemaining;
                        foreach ($rows as $idx => $ctx) {
                            $saldoCents = Penerimaan_calculation::cents($ctx['invoice']['saldo_piutang']);
                            $defPphCents = $choice === 'Y' ? $ctx['default_pph'] : 0;
                            $netReceivable = max(0, $saldoCents - $defPphCents);
                            $alloc = min($tempRem, $netReceivable);
                            $rowDeposits[$idx] = $alloc;
                            $tempRem -= $alloc;
                        }

                    }

                    foreach ($rows as $index => $context) :
                        $n = $index + 1;
                        $inv = $context['invoice'];
                        $tax = $context['tax_calc'];
                        $taxCode = $context['tax_treatment'] ?? 'PKP';
                        $defPph = $context['default_pph'] / 100;
                        $saldo = (float)$inv['saldo_piutang'];
                        $initDep = isset($rowDeposits[$index]) ? ($rowDeposits[$index] / 100) : 0;
                    ?>
                        <tr data-row="<?= $n ?>">
                            <td class="text-center font-bold">
                                <?= $escape($inv['no_invoice']) ?>
                                <input type="hidden" name="id_inv_<?= $n ?>" value="<?= $escape($inv['id']) ?>">
                                <input type="hidden" name="receipt_fingerprint_<?= $n ?>" value="<?= $escape($context['fingerprint']) ?>">
                                <?php if (!empty($context['spk'])) : ?>
                                    <br><small class="text-muted"><?= $escape($context['spk']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="tax-badge tax-<?= $escape($taxCode) ?>"><?= $escape($taxCode) ?></span>
                            </td>
                            <td class="text-right"><?= number_format($tax['sales'] / 100, 2) ?></td>
                            <td class="text-right"><?= number_format($tax['dpp_lain'] / 100, 2) ?></td>
                            <td class="text-right"><?= number_format($tax['ppn'] / 100, 2) ?></td>
                            <td class="text-right">
                                <?php if ($choice === 'Y') : ?>
                                    <input type="text" class="form-control input-sm text-right autonum input-pph"
                                        name="pph_actual_<?= $n ?>" id="pph_actual_<?= $n ?>" data-row="<?= $n ?>"
                                        value="<?= number_format($defPph, 2) ?>" data-default="<?= $defPph ?>">
                                    <div class="reset-pph-link-<?= $n ?>" style="display:none; font-size: 11px; margin-top: 2px;">
                                        <a href="javascript:void(0)" class="btn-reset-pph text-info" data-row="<?= $n ?>" data-default="<?= $defPph ?>">
                                            ↺ 2% (Rp <?= number_format($defPph, 2) ?>)
                                        </a>
                                    </div>
                                <?php else : ?>
                                    <input type="text" class="form-control input-sm text-right" readonly value="0.00">
                                    <input type="hidden" name="pph_actual_<?= $n ?>" value="0.00">
                                <?php endif; ?>
                            </td>
                            <td class="text-right font-bold"><?= number_format($tax['tagihan'] / 100, 2) ?></td>
                            <td class="text-right text-tagihan-net" id="text_tagihan_net_<?= $n ?>">
                                <?= number_format(($tax['tagihan'] - ($choice === 'Y' ? $context['default_pph'] : 0)) / 100, 2) ?>
                            </td>
                            <td class="text-right font-bold bg-gray-light" id="cell_piutang_<?= $n ?>" data-piutang="<?= Penerimaan_calculation::cents($inv['saldo_piutang']) ?>">
                                <?= number_format($saldo, 2) ?>
                            </td>
                            <td class="text-right">
                                <input type="text" class="form-control input-sm text-right autonum input-alok"
                                    name="penerimaan_<?= $n ?>" id="penerimaan_<?= $n ?>" data-row="<?= $n ?>"
                                    value="<?= number_format($saldo, 2) ?>">
                            </td>
                            <td class="text-right">
                                <input type="text" class="form-control input-sm text-right autonum input-admin"
                                    name="biaya_admin_<?= $n ?>" id="biaya_admin_<?= $n ?>" data-row="<?= $n ?>"
                                    value="0.00">
                            </td>
                            <td class="text-right">
                                <input type="text" class="form-control input-sm text-right autonum input-sisa"
                                    name="sisa_piutang_<?= $n ?>" id="sisa_piutang_<?= $n ?>" data-row="<?= $n ?>"
                                    value="0.00">
                            </td>
                            <?php if (($deposit_choice ?? 'N') === 'Y') : ?>
                                <td class="text-right">
                                    <input type="text" class="form-control input-sm text-right autonum input-dep"
                                        name="dep_<?= $n ?>" id="dep_<?= $n ?>" data-row="<?= $n ?>"
                                        value="<?= number_format($initDep, 2, '.', '') ?>">
                                </td>
                            <?php else : ?>
                                <input type="hidden" name="dep_<?= $n ?>" value="0.00">
                            <?php endif; ?>
                            <td class="text-right">
                                <input type="text" class="form-control input-sm text-right autonum input-lebih"
                                    name="lebih_bayar_<?= $n ?>" id="lebih_bayar_<?= $n ?>" data-row="<?= $n ?>"
                                    value="0.00">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-light font-bold">
                        <td colspan="5" class="text-right">Total:</td>
                        <td class="text-right" id="foot_tot_pph">0.00</td>
                        <td colspan="2"></td>
                        <td class="text-right" id="foot_tot_piutang">0.00</td>
                        <td class="text-right" id="foot_tot_alok">0.00</td>
                        <td class="text-right" id="foot_tot_admin">0.00</td>
                        <td class="text-right" id="foot_tot_sisa">0.00</td>
                        <?php if (($deposit_choice ?? 'N') === 'Y') : ?>
                            <td class="text-right" id="foot_tot_dep">0.00</td>
                        <?php endif; ?>
                        <td class="text-right" id="foot_tot_lebih">0.00</td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>

    <!-- Summary & Kontrol Bar -->
    <div class="row" style="margin-top: 15px;">
        <div class="col-md-7">
            <div id="box-error-message" class="alert alert-danger" style="display: none; padding: 10px 14px; margin-bottom: 0;">
                <i class="fa fa-exclamation-circle"></i> <span id="error-text"></span>
            </div>
            <div id="box-ok-message" class="alert alert-success" style="display: none; padding: 10px 14px; margin-bottom: 0;">
                <i class="fa fa-check-circle"></i> <strong>Kontrol 0 (Sesuai).</strong> Periksa tabel jurnal di bawah sebelum memproses.
            </div>
        </div>
        <div class="col-md-5">
            <div class="tbl-summary-card">
                <?php if ($isDeposit) : ?>
                    <div class="summary-item">
                        <span>Total Nilai Kwitansi:</span>
                        <strong id="card_tot_piutang">Rp. 0,00</strong>
                    </div>
                    <div class="summary-item">
                        <span>Biaya Admin Bank:</span>
                        <strong id="card_tot_admin" class="text-warning">Rp. 0,00</strong>
                    </div>
                    <div class="summary-item">
                        <span>Uang Masuk Bank:</span>
                        <strong id="card_uang_masuk" data-masuk="<?= $availableCents ?>">Rp. <?= number_format($availableDecimal, 2) ?></strong>
                    </div>
                    <div class="summary-item highlight">
                        <span>Kontrol Formula ((Piutang - Admin) - Bank):</span>
                        <span id="badge_kontrol" class="kontrol-badge kontrol-bad">Rp. 0,00</span>
                    </div>
                <?php else : ?>
                    <div class="summary-item">
                        <span>Total Piutang (Penjualan + PPN):</span>
                        <strong id="card_tot_piutang">Rp. 0,00</strong>
                    </div>
                    <div class="summary-item">
                        <span>Uang Masuk (Alokasi Bank):</span>
                        <strong id="card_uang_masuk" data-masuk="<?= $availableCents ?>">Rp. <?= number_format($availableDecimal, 2) ?></strong>
                    </div>
                    <?php if (($deposit_choice ?? 'N') === 'Y') : ?>
                        <div class="summary-item">
                            <span>Deposit Dipakai:</span>
                            <strong id="card_tot_dep" class="text-primary">Rp. 0,00</strong>
                        </div>
                    <?php endif; ?>
                    <div class="summary-item">
                        <span>Lebih Bayar:</span>
                        <strong id="card_tot_lebih" class="text-success">Rp. 0,00</strong>
                    </div>
                    <div class="summary-item highlight">
                        <span>Kontrol Formula ((Piutang-PPh-Admin-Sisa)-Bank-Dep)+Lebih:</span>
                        <span id="badge_kontrol" class="kontrol-badge kontrol-bad">Rp. 0,00</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Preview Jurnal Penerimaan -->
    <div class="journal-section">
        <h4 style="margin-top: 0; font-weight: 700; color: #2c3e50;">
            <i class="fa fa-book"></i> Preview Jurnal Penerimaan Piutang
        </h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" style="font-size: 12px; margin-bottom: 0;">
                <thead>
                    <tr style="background: #eef2f7;">
                        <th style="width: 100px;">Tanggal Jurnal</th>
                        <th style="width: 100px;">COA</th>
                        <th style="width: 150px;">Nama Company</th>
                        <th style="width: 160px;">Nama Account</th>
                        <th>Deskripsi</th>
                        <th class="text-right" style="width: 130px;">Debit</th>
                        <th class="text-right" style="width: 130px;">Credit</th>
                    </tr>
                </thead>
                <tbody id="tbody_preview_jurnal">
                </tbody>
                <tfoot>
                    <tr class="bg-gray font-bold">
                        <td colspan="5" class="text-center">Balancing</td>
                        <td class="text-right" id="tot_jurnal_debit">0.00</td>
                        <td class="text-right" id="tot_jurnal_kredit">0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    var isDepositMode = <?= json_encode($isDeposit) ?>;
    var rowsData = <?= json_encode(array_map(function($r) use ($allocation) {
        return array(
            'invoice' => $r['invoice']['no_invoice'],
            'tax_treatment' => $r['tax_treatment'] ?? 'PKP',
            'tagihan' => $r['tax_calc']['tagihan'],
            'default_pph' => $r['default_pph'],
            'remaining_pph' => $r['remaining_pph'],
            'spk' => $r['spk'] ?? '',
            'owner' => $r['owner']['nm_company'],
            'accounts' => $r['accounts'],
            'transDate' => $allocation['detail']['tanggal_transaksi']
        );
    }, $rows), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    var choice = <?= json_encode($choice) ?>;
    var uangMasuk = parseFloat(document.getElementById('card_uang_masuk').getAttribute('data-masuk')) || 0;
    var container = document.getElementById('consulting-receipt-container');

    function parseVal(el) {
        if (!el) return 0;
        return ConsultingReceiptMoney.cents(el.value);
    }

    function fmtMoney(cents) {
        return (cents / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtIdn(cents) {
        return 'Rp. ' + (cents / 100).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalc() {
        try { recalculate(); }
        catch (error) {
            document.getElementById('error-text').textContent = error.message;
            document.getElementById('box-error-message').style.display = 'block';
            document.getElementById('box-ok-message').style.display = 'none';
            document.getElementById('input_kontrol').value = '1';
            var submit = document.querySelector('#dialog-popup button[type="submit"]');
            if (submit) submit.disabled = true;
        }
    }

    function recalculate() {
        var nRows = rowsData.length;
        var totPiutang = 0, totPph = 0, totAlok = 0, totAdmin = 0, totSisa = 0;
        var totDep = 0, totLebih = 0;
        var errors = [];
        var journals = [];
        var totalBankDebit = 0;
        var depositChoice = <?= json_encode($deposit_choice ?? 'N') ?>;
        var formDepositAmount = <?= (int)($deposit_amount ?? 0) ?>;

        for (var i = 1; i <= nRows; i++) {
            var meta = rowsData[i - 1];
            var piutangCell = document.getElementById('cell_piutang_' + i);
            var piutang = parseFloat(piutangCell.getAttribute('data-piutang')) || 0;
            var inpPph = container.querySelector('[name="pph_actual_' + i + '"]');
            var inpAlok = container.querySelector('[name="penerimaan_' + i + '"]');
            var inpAdmin = container.querySelector('[name="biaya_admin_' + i + '"]');
            var inpSisa = container.querySelector('[name="sisa_piutang_' + i + '"]');
            var inpDep = container.querySelector('[name="dep_' + i + '"]');
            var inpLebih = container.querySelector('[name="lebih_bayar_' + i + '"]');

            var pph = (!isDepositMode && choice === 'Y') ? parseVal(inpPph) : 0;
            var alok = parseVal(inpAlok);
            var admin = parseVal(inpAdmin);
            var sisa = (!isDepositMode && inpSisa) ? parseVal(inpSisa) : 0;
            var dep = (!isDepositMode && depositChoice === 'Y' && inpDep) ? parseVal(inpDep) : 0;
            var lebih = (!isDepositMode && inpLebih) ? parseVal(inpLebih) : 0;

            if (pph < 0 || alok < 0 || admin < 0 || sisa < 0 || dep < 0 || lebih < 0) {
                errors.push('Nilai tidak boleh bernilai minus pada ' + meta.invoice + '.');
            }
            if (dep > 0 && lebih > 0) {
                errors.push('Deposit Dipakai dan Lebih Bayar tidak boleh terisi bersamaan pada ' + meta.invoice + '.');
            }
            if (pph > meta.remaining_pph || pph > alok) {
                errors.push('PPh 23 melebihi sisa batas 2% DPP atau alokasi invoice ' + meta.invoice + '.');
            }
            if (sisa > piutang) {
                errors.push('Sisa piutang melebihi piutang dagang invoice ' + meta.invoice + '.');
            }

            if (isDepositMode) {
                if (alok !== piutang) {
                    errors.push('Alokasi penerimaan kwitansi deposit harus melunasi saldo piutang penuh (' + meta.invoice + ').');
                }
            } else {
                if (alok <= 0 || alok + sisa !== piutang) {
                    errors.push('Alokasi penerimaan + sisa harus sama dengan saldo piutang ' + meta.invoice + '.');
                }
            }

            // Tagihan - PPH column update (invoice mode)
            var textTagihanNet = document.getElementById('text_tagihan_net_' + i);
            if (textTagihanNet) {
                textTagihanNet.textContent = fmtMoney(meta.tagihan - pph);
            }

            // Reset PPh link visibility
            if (!isDepositMode && choice === 'Y' && inpPph) {
                var defPph = meta.default_pph;
                var resetWrapper = container.querySelector('.reset-pph-link-' + i);
                if (resetWrapper) {
                    resetWrapper.style.display = (pph !== defPph) ? 'block' : 'none';
                }
            }

            totPiutang += piutang;
            totPph += pph;
            totAlok += alok;
            totAdmin += admin;
            totSisa += sisa;
            totDep += dep;
            totLebih += lebih;

            // Bank portion for this row:
            var bankCash = isDepositMode ? (piutang - admin) : (alok - pph - admin - dep + lebih);
            if (bankCash < 0) errors.push('Penerimaan bank tidak boleh minus pada ' + meta.invoice + '.');
            totalBankDebit += bankCash;

            // Build journal lines
            var sfx = ' - ' + meta.invoice + (meta.spk ? ' - ' + meta.spk : '') + (meta.tax_treatment === 'KB' ? ' - Kawasan Berikat' : '');

            if (isDepositMode) {
                // Jurnal Kwitansi Deposit:
                // Bank (D) = Piutang - Admin
                if (bankCash > 0) {
                    journals.push({ d: meta.transDate, coa: meta.accounts.bank.code, co: meta.owner, acc: meta.accounts.bank.name, desc: meta.accounts.bank.name + sfx, dr: bankCash, cr: 0 });
                }
                // Biaya Admin (D)
                if (admin > 0) {
                    journals.push({ d: meta.transDate, coa: meta.accounts.admin.code, co: meta.owner, acc: meta.accounts.admin.name, desc: meta.accounts.admin.name + sfx, dr: admin, cr: 0 });
                }
                // Piutang Dagang Usaha (K) = Piutang
                if (piutang > 0) {
                    journals.push({ d: meta.transDate, coa: meta.accounts.receivable.code, co: meta.owner, acc: meta.accounts.receivable.name, desc: meta.accounts.receivable.name + sfx, dr: 0, cr: piutang });
                }
            } else {
                // Jurnal Invoice
                if (bankCash > 0) {
                    journals.push({ d: meta.transDate, coa: meta.accounts.bank.code, co: meta.owner, acc: meta.accounts.bank.name, desc: meta.accounts.bank.name + sfx, dr: bankCash, cr: 0 });
                }
                if (pph > 0) {
                    journals.push({ d: meta.transDate, coa: meta.accounts.pph.code, co: meta.owner, acc: meta.accounts.pph.name, desc: meta.accounts.pph.name + sfx, dr: pph, cr: 0 });
                }
                if (admin > 0) {
                    journals.push({ d: meta.transDate, coa: meta.accounts.admin.code, co: meta.owner, acc: meta.accounts.admin.name, desc: meta.accounts.admin.name + sfx, dr: admin, cr: 0 });
                }
                if (dep > 0) {
                    var depAcc = (meta.accounts && meta.accounts.deposit) ? meta.accounts.deposit : { code: '2102-01-02', name: 'DEPOSIT CUSTOMER' };
                    journals.push({ d: meta.transDate, coa: depAcc.code, co: meta.owner, acc: depAcc.name, desc: depAcc.name + sfx, dr: dep, cr: 0 });
                }
                if (piutang > 0) {
                    journals.push({ d: meta.transDate, coa: meta.accounts.receivable.code, co: meta.owner, acc: meta.accounts.receivable.name, desc: meta.accounts.receivable.name + sfx, dr: 0, cr: piutang });
                }
                if (sisa > 0) {
                    journals.push({ d: meta.transDate, coa: meta.accounts.receivable.code, co: meta.owner, acc: meta.accounts.receivable.name, desc: meta.accounts.receivable.name + sfx, dr: sisa, cr: 0 });
                }
                if (lebih > 0) {
                    var depAcc = (meta.accounts && meta.accounts.deposit) ? meta.accounts.deposit : { code: '2102-01-02', name: 'DEPOSIT CUSTOMER' };
                    journals.push({ d: meta.transDate, coa: depAcc.code, co: meta.owner, acc: depAcc.name, desc: depAcc.name + sfx, dr: 0, cr: lebih });
                }
            }
        }

        // Deposit form amount match
        if (!isDepositMode && depositChoice === 'Y' && totDep !== formDepositAmount) {
            errors.push('Total Deposit Dipakai per invoice (' + fmtMoney(totDep) + ') harus sama dengan Nominal Deposit Dipakai di form (' + fmtMoney(formDepositAmount) + ').');
        }
        if (!isDepositMode && totDep > 0 && totLebih > 0) {
            errors.push('Deposit Dipakai dan Lebih Bayar tidak boleh terisi bersamaan. Kurangi deposit yang dipakai.');
        }

        // Admin max check (Rp 10.000)
        if (totAdmin > 1000000) {
            errors.push('Total biaya admin (' + fmtMoney(totAdmin) + ') melebihi batas maksimal Rp 10.000,00.');
        }

        // Kontrol calculation:
        // Invoice: ((Piutang - PPh - Admin - Sisa) - Uang Masuk - Deposit) + Lebih Bayar
        // Deposit: (Piutang - Admin) - Uang Masuk
        var kontrol = isDepositMode
            ? ((totPiutang - totAdmin) - uangMasuk)
            : (((totPiutang - totPph - totAdmin - totSisa) - uangMasuk - totDep) + totLebih);

        if (![totPiutang, totPph, totAlok, totAdmin, totSisa, totDep, totLebih, totalBankDebit, kontrol].every(Number.isSafeInteger)) {
            throw new Error('Total nominal terlalu besar.');
        }

        if (kontrol !== 0) {
            if (kontrol > 0) {
                errors.push('Masih ada selisih kurang ' + fmtMoney(kontrol) + '. Sesuaikan alokasi atau biaya admin.');
            } else {
                errors.push('Uang masuk lebih besar ' + fmtMoney(-kontrol) + ' dari piutang yang dialokasikan.');
            }
        }

        // Update footer totals
        var footPph = document.getElementById('foot_tot_pph');
        if (footPph) footPph.textContent = fmtMoney(totPph);
        document.getElementById('foot_tot_piutang').textContent = fmtMoney(totPiutang);
        document.getElementById('foot_tot_alok').textContent = fmtMoney(totAlok);
        document.getElementById('foot_tot_admin').textContent = fmtMoney(totAdmin);
        var footSisa = document.getElementById('foot_tot_sisa');
        if (footSisa) footSisa.textContent = fmtMoney(totSisa);
        var elFootDep = document.getElementById('foot_tot_dep');
        if (elFootDep) elFootDep.textContent = fmtMoney(totDep);
        var elFootLebih = document.getElementById('foot_tot_lebih');
        if (elFootLebih) elFootLebih.textContent = fmtMoney(totLebih);

        // Update summary card
        document.getElementById('card_tot_piutang').textContent = fmtIdn(totPiutang);
        var elCardAdmin = document.getElementById('card_tot_admin');
        if (elCardAdmin) elCardAdmin.textContent = fmtIdn(totAdmin);
        var elCardDep = document.getElementById('card_tot_dep');
        if (elCardDep) elCardDep.textContent = fmtIdn(totDep);
        var elCardLebih = document.getElementById('card_tot_lebih');
        if (elCardLebih) elCardLebih.textContent = fmtIdn(totLebih);

        var badgeKontrol = document.getElementById('badge_kontrol');
        badgeKontrol.textContent = fmtMoney(kontrol);
        if (kontrol === 0) {
            badgeKontrol.className = 'kontrol-badge kontrol-ok';
        } else {
            badgeKontrol.className = 'kontrol-badge kontrol-bad';
        }

        // Update hidden kontrol field
        document.getElementById('input_kontrol').value = ConsultingReceiptMoney.decimal(kontrol);

        // Error message display
        var boxErr = document.getElementById('box-error-message');
        var boxOk = document.getElementById('box-ok-message');
        var errText = document.getElementById('error-text');

        if (errors.length > 0) {
            errText.textContent = errors.join(' ');
            boxErr.style.display = 'block';
            boxOk.style.display = 'none';
        } else {
            boxErr.style.display = 'none';
            boxOk.style.display = 'block';
        }

        // Render live preview journal table
        var tbodyJurnal = document.getElementById('tbody_preview_jurnal');
        tbodyJurnal.innerHTML = '';
        var totJurnalDr = 0, totJurnalCr = 0;

        journals.forEach(function(j) {
            var tr = document.createElement('tr');
            [j.d, j.coa, j.co, j.acc, j.desc, j.dr > 0 ? fmtMoney(j.dr) : '-', j.cr > 0 ? fmtMoney(j.cr) : '-'].forEach(function(value, index) {
                var cell = document.createElement('td');
                cell.textContent = value;
                if (index >= 5) cell.className = 'text-right font-bold';
                tr.appendChild(cell);
            });
            tbodyJurnal.appendChild(tr);
            totJurnalDr += j.dr;
            totJurnalCr += j.cr;
        });

        document.getElementById('tot_jurnal_debit').textContent = fmtMoney(totJurnalDr);
        document.getElementById('tot_jurnal_kredit').textContent = fmtMoney(totJurnalCr);

        document.getElementById('input_total_debit_jurnal').value = ConsultingReceiptMoney.decimal(totJurnalDr);
        document.getElementById('input_total_kredit_jurnal').value = ConsultingReceiptMoney.decimal(totJurnalCr);

        // Enable or disable modal save button
        var isOk = (errors.length === 0 && totJurnalDr === totJurnalCr);
        var submitBtn = document.querySelector('#dialog-popup button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = !isOk;
        }
    }

    function updateRow(e) {
        var target = e.target;
        var row = target.getAttribute('data-row');
        if (row && (target.classList.contains('input-sisa') || target.classList.contains('input-alok'))) {
            try {
                var balance = Number(document.getElementById('cell_piutang_' + row).getAttribute('data-piutang'));
                var name = target.classList.contains('input-sisa') ? 'penerimaan_' : 'sisa_piutang_';
                var paired = container.querySelector('[name="' + name + row + '"]');
                $(paired).autoNumeric('set', ConsultingReceiptMoney.decimal(balance - parseVal(target)));
            } catch (error) { /* recalc displays error */ }
        }
        recalc();
    }
    container.addEventListener('change', updateRow);
    container.addEventListener('keyup', updateRow);

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-reset-pph') || e.target.closest('.btn-reset-pph')) {
            var btn = e.target.classList.contains('btn-reset-pph') ? e.target : e.target.closest('.btn-reset-pph');
            var row = btn.getAttribute('data-row');
            var def = btn.getAttribute('data-default');
            var inp = container.querySelector('[name="pph_actual_' + row + '"]');
            if (inp) {
                $(inp).autoNumeric('set', def);
                recalc();
            }
        }
        if (e.target.id === 'btn_redistribute_dep' || e.target.closest('#btn_redistribute_dep')) {
            var left = <?= (int)($deposit_amount ?? 0) ?>;
            var nRows = rowsData.length;
            for (var r = 1; r <= nRows; r++) {
                var piutangVal = parseFloat(document.getElementById('cell_piutang_' + r).getAttribute('data-piutang')) || 0;
                var pphInput = container.querySelector('[name="pph_actual_' + r + '"]');
                var pphVal = choice === 'Y' ? parseVal(pphInput) : 0;
                var adminInput = container.querySelector('[name="biaya_admin_' + r + '"]');
                var net = Math.max(0, piutangVal - pphVal - parseVal(adminInput));
                var take = Math.min(left, net);

                var depInp = container.querySelector('[name="dep_' + r + '"]');
                if (depInp) {
                    $(depInp).autoNumeric('set', ConsultingReceiptMoney.decimal(take));
                }
                left -= take;
            }
            recalc();
        }
    });

    // Initial calculation
    setTimeout(recalc, 100);
})();
</script>
