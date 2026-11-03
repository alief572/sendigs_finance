<?php
defined('BASEPATH') or exit('No direct script access allowed');
$escape = function ($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
};
$availableCents = $allocation['capacity'] - $allocation['used'];
$availableDecimal = $availableCents / 100;
$isDeposit = (($receipt_kind ?? 'invoice') === 'deposit');
?>

<style>
    /* View-scoped: kelas badge_kontrol ditulis oleh skrip recalculate() yang tidak diubah,
       dan .journal-section dibaca JS untuk show/hide. Sisanya mengandalkan .mk di mockup_ui.css. */
    .mk #badge_kontrol.kontrol-badge {
        display: inline-block;
        font-weight: 700;
    }

    .mk #badge_kontrol.kontrol-ok {
        color: var(--success);
    }

    .mk #badge_kontrol.kontrol-bad {
        color: var(--danger);
    }

    .mk .journal-section {
        margin-top: 20px;
        border-top: 1px solid var(--line);
        padding-top: 15px;
    }
</style>

<div class="mk" id="consulting-receipt-container">
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

    <div class="table-responsive">
        <?php if ($isDeposit) : ?>
            <!-- TABEL KHUSUS KWITANSI DEPOSIT (7 Kolom Ringkas) -->
            <table style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th class="c" style="width: 160px;">Nomor Kwitansi</th>
                        <th class="c" style="width: 140px;">No SPK</th>
                        <th class="c" style="width: 130px;">Company</th>
                        <th class="r" style="width: 130px;">Nilai Kwitansi</th>
                        <th class="r" style="width: 130px;">Piutang Dagang</th>
                        <th class="r" style="width: 140px;">Alokasi Penerimaan</th>
                        <th class="r" style="width: 130px;">Biaya Admin</th>
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
                            <td class="c" style="font-family: monospace;">
                                <?= $escape($inv['no_invoice']) ?>
                                <input type="hidden" name="id_inv_<?= $n ?>" value="<?= $escape($inv['id']) ?>">
                                <input type="hidden" name="receipt_fingerprint_<?= $n ?>" value="<?= $escape($context['fingerprint']) ?>">
                                <input type="hidden" name="pph_actual_<?= $n ?>" value="0.00">
                                <input type="hidden" name="sisa_piutang_<?= $n ?>" value="0.00">
                                <input type="hidden" name="dep_<?= $n ?>" value="0.00">
                                <input type="hidden" name="lebih_bayar_<?= $n ?>" value="0.00">
                            </td>
                            <td class="c"><?= $escape($spkNo) ?></td>
                            <td class="c"><span class="lbl"><?= $escape($compName) ?></span></td>
                            <td class="r"><?= number_format((float)$inv['total_nominal_jurnal'], 2) ?></td>
                            <td class="r" id="cell_piutang_<?= $n ?>" data-piutang="<?= Penerimaan_calculation::cents($inv['saldo_piutang']) ?>">
                                <?= number_format($saldo, 2) ?>
                            </td>
                            <td class="r">
                                <input type="text" class="num cell-in autonum input-alok"
                                    name="penerimaan_<?= $n ?>" id="penerimaan_<?= $n ?>" data-row="<?= $n ?>"
                                    value="<?= number_format($saldo, 2) ?>" readonly style="background-color: #f1f8e9;">
                            </td>
                            <td class="r">
                                <input type="text" class="num cell-in autonum input-admin"
                                    name="biaya_admin_<?= $n ?>" id="biaya_admin_<?= $n ?>" data-row="<?= $n ?>"
                                    value="0.00">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="sum">
                        <td colspan="4" class="k">Total</td>
                        <td class="r" id="foot_tot_piutang">0.00</td>
                        <td class="r" id="foot_tot_alok">0.00</td>
                        <td class="r" id="foot_tot_admin">0.00</td>
                    </tr>
                    <tr class="sum">
                        <td colspan="4" class="k">Uang Masuk</td>
                        <td class="r"><strong id="card_uang_masuk" data-masuk="<?= $availableCents ?>"><?= mk_idn($availableDecimal) ?></strong></td>
                        <td colspan="2"></td>
                    </tr>
                    <tr class="sum">
                        <td colspan="4" class="k">Kontrol</td>
                        <td class="r"><span id="badge_kontrol" class="kontrol-badge kontrol-bad">0,00</span></td>
                        <td colspan="2" class="muted small">(Piutang &minus; biaya admin) &minus; uang masuk</td>
                    </tr>
                </tfoot>
            </table>
            <p class="muted" style="margin-top: 8px; font-size: 12px;">Saldo deposit customer bertambah sebesar nilai kwitansi (<strong id="card_tot_piutang">0,00</strong>). Biaya admin bank menjadi beban perusahaan, tidak mengurangi deposit.</p>
        <?php else : ?>
            <!-- TABEL LENGKAP PENERIMAAN INVOICE -->
            <table style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th class="c" style="width: 140px;">Nomor Invoice</th>
                        <th class="c" style="width: 70px;">TAX Code</th>
                        <th class="r" style="width: 110px;">Penjualan</th>
                        <th class="r" style="width: 100px;">DPP</th>
                        <th class="r" style="width: 90px;">PPN</th>
                        <th class="r" style="width: 120px;">
                            PPH 23
                            <?php if ($choice === 'Y') : ?>
                                <br><small class="muted font-normal">default 2% Penjualan, bisa diedit</small>
                            <?php endif; ?>
                        </th>
                        <th class="r" style="width: 115px;">Total Tagihan<br>(Penjualan + PPN)</th>
                        <th class="r" style="width: 115px;">Total Tagihan<br>- PPH</th>
                        <th class="r" style="width: 115px;">Piutang Dagang<br>(Penjualan + PPN)</th>
                        <th class="r" style="width: 120px;">Alokasi Penerimaan</th>
                        <?php if (($deposit_choice ?? 'N') === 'Y') : ?>
                            <th class="r" style="width: 115px;">Deposit Dipakai</th>
                        <?php endif; ?>
                        <th class="r" style="width: 110px;">Biaya Admin</th>
                        <th class="r" style="width: 115px;">Lebih Bayar</th>
                        <th class="r" style="width: 115px;">Sisa Piutang</th>
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
                            <td class="c">
                                <?= $escape($inv['no_invoice']) ?>
                                <input type="hidden" name="id_inv_<?= $n ?>" value="<?= $escape($inv['id']) ?>">
                                <input type="hidden" name="receipt_fingerprint_<?= $n ?>" value="<?= $escape($context['fingerprint']) ?>">
                            </td>
                            <td class="c">
                                <?= mk_tax_badge($taxCode) ?>
                            </td>
                            <td class="r"><?= mk_en($tax['sales'] / 100) ?></td>
                            <td class="r"><?= mk_en($tax['dpp_lain'] / 100) ?></td>
                            <td class="r"><?= mk_en($tax['ppn'] / 100) ?></td>
                            <td class="r">
                                <?php if ($choice === 'Y') : ?>
                                    <input type="text" class="num cell-in autonum input-pph"
                                        name="pph_actual_<?= $n ?>" id="pph_actual_<?= $n ?>" data-row="<?= $n ?>"
                                        value="<?= number_format($defPph, 2) ?>" data-default="<?= $defPph ?>">
                                    <div class="reset-pph-link-<?= $n ?>" style="display:none; font-size: 12px; margin-top: 2px; white-space: nowrap; color: #777;">
                                        Default <?= mk_en($defPph) ?> &middot;
                                        <a href="javascript:void(0)" class="btn-reset-pph text-info" data-row="<?= $n ?>" data-default="<?= $defPph ?>">Kembalikan</a>
                                    </div>
                                <?php else : ?>
                                    <input type="text" class="num cell-in" readonly value="0.00">
                                    <input type="hidden" name="pph_actual_<?= $n ?>" value="0.00">
                                <?php endif; ?>
                            </td>
                            <td class="r"><?= mk_en($tax['tagihan'] / 100) ?></td>
                            <td class="r text-tagihan-net" id="text_tagihan_net_<?= $n ?>">
                                <?= mk_en(($tax['tagihan'] - ($choice === 'Y' ? $context['default_pph'] : 0)) / 100) ?>
                            </td>
                            <td class="r" id="cell_piutang_<?= $n ?>" data-piutang="<?= Penerimaan_calculation::cents($inv['saldo_piutang']) ?>">
                                <?= mk_en($saldo) ?>
                            </td>
                            <td class="r">
                                <input type="text" class="num cell-in autonum input-alok"
                                    name="penerimaan_<?= $n ?>" id="penerimaan_<?= $n ?>" data-row="<?= $n ?>"
                                    value="<?= number_format($saldo, 2) ?>">
                            </td>
                            <?php if (($deposit_choice ?? 'N') === 'Y') : ?>
                                <td class="r">
                                    <input type="text" class="num cell-in autonum input-dep"
                                        name="dep_<?= $n ?>" id="dep_<?= $n ?>" data-row="<?= $n ?>"
                                        value="<?= number_format($initDep, 2, '.', '') ?>">
                                </td>
                            <?php else : ?>
                                <input type="hidden" name="dep_<?= $n ?>" value="0.00">
                            <?php endif; ?>
                            <td class="r">
                                <input type="text" class="num cell-in autonum input-admin"
                                    name="biaya_admin_<?= $n ?>" id="biaya_admin_<?= $n ?>" data-row="<?= $n ?>"
                                    value="0.00">
                            </td>
                            <td class="r">
                                <input type="text" class="num cell-in autonum input-lebih"
                                    name="lebih_bayar_<?= $n ?>" id="lebih_bayar_<?= $n ?>" data-row="<?= $n ?>"
                                    value="0.00">
                            </td>
                            <td class="r">
                                <input type="text" class="num cell-in autonum input-sisa"
                                    name="sisa_piutang_<?= $n ?>" id="sisa_piutang_<?= $n ?>" data-row="<?= $n ?>"
                                    value="0.00">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="sum">
                        <td colspan="5" class="k">Total</td>
                        <td class="r" id="foot_tot_pph">0.00</td>
                        <td colspan="2"></td>
                        <td class="r" id="foot_tot_piutang">0.00</td>
                        <td class="r" id="foot_tot_alok">0.00</td>
                        <?php if (($deposit_choice ?? 'N') === 'Y') : ?>
                            <td class="r" id="foot_tot_dep">0.00</td>
                        <?php endif; ?>
                        <td class="r" id="foot_tot_admin">0.00</td>
                        <td class="r" id="foot_tot_lebih">0.00</td>
                        <td class="r" id="foot_tot_sisa">0.00</td>
                    </tr>
                    <?php $restCols = (($deposit_choice ?? 'N') === 'Y') ? 5 : 4; ?>
                    <tr class="sum">
                        <td colspan="8" class="k">Total Piutang (Penjualan + PPN)</td>
                        <td class="r"><strong id="card_tot_piutang">0,00</strong></td>
                        <td colspan="<?= $restCols ?>"></td>
                    </tr>
                    <tr class="sum">
                        <td colspan="8" class="k">Uang Masuk</td>
                        <td class="r"><strong id="card_uang_masuk" data-masuk="<?= $availableCents ?>"><?= mk_idn($availableDecimal) ?></strong></td>
                        <td colspan="<?= $restCols ?>"></td>
                    </tr>
                    <?php if (($deposit_choice ?? 'N') === 'Y') : ?>
                        <tr class="sum">
                            <td colspan="8" class="k">Deposit Dipakai</td>
                            <td class="r"><strong id="card_tot_dep" class="text-primary">0,00</strong></td>
                            <td colspan="<?= $restCols ?>" class="muted small">Nominal di form <?= mk_idn(($deposit_amount ?? 0) / 100) ?> &middot; saldo deposit <?= mk_idn(($customer_deposit ?? 0) / 100) ?> &middot; sisa setelah dipakai <?= mk_idn((($customer_deposit ?? 0) - ($deposit_amount ?? 0)) / 100) ?></td>
                        </tr>
                    <?php endif; ?>
                    <tr class="sum">
                        <td colspan="8" class="k">Lebih Bayar</td>
                        <td class="r"><strong id="card_tot_lebih" class="text-success">0,00</strong></td>
                        <td colspan="<?= $restCols ?>" class="muted small" id="lebih_note" style="display:none">Masuk ke saldo deposit customer</td>
                    </tr>
                    <tr class="sum">
                        <td colspan="8" class="k">Kontrol</td>
                        <td class="r"><span id="badge_kontrol" class="kontrol-badge kontrol-bad">0,00</span></td>
                        <td colspan="<?= $restCols ?>" class="muted small">((Piutang &minus; PPH 23 &minus; biaya admin &minus; sisa piutang) &minus; uang masuk &minus; deposit dipakai) + lebih bayar</td>
                    </tr>
                </tfoot>
            </table>
        <?php endif; ?>
    </div>
    <?php if (!$isDeposit && ($deposit_choice ?? 'N') === 'Y' && ($deposit_amount ?? 0) > 0) : ?>
        <div class="text-muted" style="margin-top: 8px; font-size: 12px;">Deposit Dipakai Rp <?= mk_idn($deposit_amount / 100) ?> (dari form) dibagi ke invoice urut tanggal. Pembagian per invoice bisa diedit selama totalnya tetap sama. <a href="javascript:void(0)" id="btn_redistribute_dep">Bagi ulang</a></div>
    <?php endif; ?>

    <!-- Summary & Kontrol Bar -->
    <div class="row" style="margin-top: 15px;">
        <div class="col-md-12">
            <div id="box-error-message" class="alert red" style="display: none; padding: 10px 14px; margin-bottom: 0;">
                <i class="fa fa-exclamation-circle"></i> <span id="error-text"></span>
            </div>
            <div id="box-ok-message" class="alert blue" style="display: none; padding: 10px 14px; margin-bottom: 0;">
                <i class="fa fa-check-circle"></i> <strong>Kontrol 0.</strong> Periksa jurnal di bawah, lalu klik Proses untuk menyimpan.
            </div>
        </div>
    </div>

    <!-- Preview Jurnal Penerimaan -->
    <div class="journal-section">

        <div class="table-responsive">
            <table style="font-size: 12px; margin-bottom: 0;">
                <thead>
                    <tr>
                        <th style="width: 100px;">Tanggal Jurnal</th>
                        <th style="width: 100px;">COA</th>
                        <th style="width: 150px;">Nama Company</th>
                        <th style="width: 160px;">Nama Account</th>
                        <th>Deskripsi</th>
                        <th class="r" style="width: 130px;">Debit</th>
                        <th class="r" style="width: 130px;">Credit</th>
                    </tr>
                </thead>
                <tbody id="tbody_preview_jurnal">
                </tbody>
                <tfoot>
                    <tr class="sum">
                        <td colspan="5" class="c">Balancing</td>
                        <td class="r" id="tot_jurnal_debit">0.00</td>
                        <td class="r" id="tot_jurnal_kredit">0.00</td>
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
        var rowsData = <?= json_encode(array_map(function ($r) use ($allocation) {
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
        // Format seragam dengan input nominal (en-US: 1,234,567.89) agar angka bisa disalin-tempel antar kolom.
        function fmtMoney(cents) {
            return (cents / 100).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function fmtIdn(cents) {
            return fmtMoney(cents);
        }

        function fmtEn(cents) {
            return (cents / 100).toLocaleString('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
        }

        var MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        function fdate(iso) {
            var m = /^(\d{4})-(\d{2})-(\d{2})/.exec(String(iso || ''));
            return m ? m[3] + '-' + MONTH_NAMES[parseInt(m[2], 10) - 1] + '-' + m[1] : String(iso || '');
        }

        function recalc() {
            try {
                recalculate();
            } catch (error) {
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
            var totPiutang = 0,
                totPph = 0,
                totAlok = 0,
                totAdmin = 0,
                totSisa = 0;
            var totDep = 0,
                totLebih = 0;
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
                    errors.push('Nilai minus tidak diperbolehkan di ' + meta.invoice + '.');
                }
                if (dep > 0 && lebih > 0) {
                    errors.push('Deposit Dipakai dan Lebih Bayar tidak boleh terisi bersamaan pada ' + meta.invoice + '.');
                }
                if (pph > meta.remaining_pph) {
                    errors.push('PPh 23 melebihi sisa batas 2% Penjualan invoice ' + meta.invoice + '.');
                }
                if (pph > piutang) {
                    errors.push('PPh 23 ' + meta.invoice + ' melebihi piutang dagang invoice.');
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
                        journals.push({
                            d: meta.transDate,
                            coa: meta.accounts.bank.code,
                            co: meta.owner,
                            acc: meta.accounts.bank.name,
                            desc: meta.accounts.bank.name + sfx,
                            dr: bankCash,
                            cr: 0
                        });
                    }
                    // Biaya Admin (D)
                    if (admin > 0) {
                        journals.push({
                            d: meta.transDate,
                            coa: meta.accounts.admin.code,
                            co: meta.owner,
                            acc: meta.accounts.admin.name,
                            desc: meta.accounts.admin.name + sfx,
                            dr: admin,
                            cr: 0
                        });
                    }
                    // Piutang Dagang Usaha (K) = Piutang
                    if (piutang > 0) {
                        journals.push({
                            d: meta.transDate,
                            coa: meta.accounts.receivable.code,
                            co: meta.owner,
                            acc: meta.accounts.receivable.name,
                            desc: meta.accounts.receivable.name + sfx,
                            dr: 0,
                            cr: piutang
                        });
                    }
                } else {
                    // Jurnal Invoice
                    if (bankCash > 0) {
                        journals.push({
                            d: meta.transDate,
                            coa: meta.accounts.bank.code,
                            co: meta.owner,
                            acc: meta.accounts.bank.name,
                            desc: meta.accounts.bank.name + sfx,
                            dr: bankCash,
                            cr: 0
                        });
                    }
                    if (pph > 0) {
                        journals.push({
                            d: meta.transDate,
                            coa: meta.accounts.pph.code,
                            co: meta.owner,
                            acc: meta.accounts.pph.name,
                            desc: meta.accounts.pph.name + sfx,
                            dr: pph,
                            cr: 0
                        });
                    }
                    if (admin > 0) {
                        journals.push({
                            d: meta.transDate,
                            coa: meta.accounts.admin.code,
                            co: meta.owner,
                            acc: meta.accounts.admin.name,
                            desc: meta.accounts.admin.name + sfx,
                            dr: admin,
                            cr: 0
                        });
                    }
                    if (dep > 0) {
                        var depAcc = (meta.accounts && meta.accounts.deposit) ? meta.accounts.deposit : {
                            code: '2102-01-02',
                            name: 'DEPOSIT CUSTOMER'
                        };
                        journals.push({
                            d: meta.transDate,
                            coa: depAcc.code,
                            co: meta.owner,
                            acc: depAcc.name,
                            desc: depAcc.name + sfx,
                            dr: dep,
                            cr: 0
                        });
                    }
                    if (piutang > 0) {
                        journals.push({
                            d: meta.transDate,
                            coa: meta.accounts.receivable.code,
                            co: meta.owner,
                            acc: meta.accounts.receivable.name,
                            desc: meta.accounts.receivable.name + sfx,
                            dr: 0,
                            cr: piutang
                        });
                    }
                    if (sisa > 0) {
                        journals.push({
                            d: meta.transDate,
                            coa: meta.accounts.receivable.code,
                            co: meta.owner,
                            acc: meta.accounts.receivable.name,
                            desc: meta.accounts.receivable.name + sfx,
                            dr: sisa,
                            cr: 0
                        });
                    }
                    if (lebih > 0) {
                        var depAcc = (meta.accounts && meta.accounts.deposit) ? meta.accounts.deposit : {
                            code: '2102-01-02',
                            name: 'DEPOSIT CUSTOMER'
                        };
                        journals.push({
                            d: meta.transDate,
                            coa: depAcc.code,
                            co: meta.owner,
                            acc: depAcc.name,
                            desc: depAcc.name + sfx,
                            dr: 0,
                            cr: lebih
                        });
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
                errors.push('Total biaya admin melebihi 10,000.');
            }

            // Kontrol calculation:
            // Invoice: ((Piutang - PPh - Admin - Sisa) - Uang Masuk - Deposit) + Lebih Bayar
            // Deposit: (Piutang - Admin) - Uang Masuk
            var kontrol = isDepositMode ?
                ((totPiutang - totAdmin) - uangMasuk) :
                (((totPiutang - totPph - totAdmin - totSisa) - uangMasuk - totDep) + totLebih);

            if (![totPiutang, totPph, totAlok, totAdmin, totSisa, totDep, totLebih, totalBankDebit, kontrol].every(Number.isSafeInteger)) {
                throw new Error('Total nominal terlalu besar.');
            }

            if (kontrol !== 0 && isDepositMode) {
                // Pesan khusus Kwitansi Deposit (mockup 961-962): tidak ada Lebih Bayar.
                errors.push(kontrol > 0 ?
                    'Masih ada selisih kurang ' + fmtMoney(kontrol) + '. Isi Biaya Admin sesuai potongan bank.' :
                    'Uang masuk melebihi nilai kwitansi sebesar ' + fmtMoney(-kontrol) + '. Kwitansi deposit tidak punya Lebih Bayar: periksa kwitansi yang dicentang, atau pecah transfernya di Alokasi.');
            } else if (kontrol !== 0) {
                if (kontrol > 0) {
                    errors.push('Masih ada selisih kurang ' + fmtMoney(kontrol) + '. Alokasikan ke PPh 23, Biaya Admin, atau Sisa Piutang pada invoice yang sesuai' + (depositChoice === 'Y' ? ', atau ubah Nominal Deposit Dipakai di form (klik Batal)' : '') + '.');
                } else {
                    errors.push('Ada kelebihan ' + fmtMoney(-kontrol) + '. Alokasikan ke Lebih Bayar (masuk ke saldo deposit customer).');
                }
            }

            var lebihNote = document.getElementById('lebih_note');
            if (lebihNote) lebihNote.style.display = totLebih > 0 ? '' : 'none';
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
                // Judul + daftar berpoin seperti mockup (844).
                errText.innerHTML = '';
                var title = document.createElement('b');
                title.textContent = kontrol !== 0 ? 'Catatan: kontrol belum 0, belum bisa disimpan.' : 'Catatan: data belum valid.';
                var list = document.createElement('ul');
                list.style.margin = '6px 0 0 18px';
                list.style.padding = '0';
                errors.forEach(function(text) {
                    var li = document.createElement('li');
                    li.textContent = text;
                    list.appendChild(li);
                });
                errText.appendChild(title);
                errText.appendChild(list);
                boxErr.style.display = 'block';
                boxOk.style.display = 'none';
            } else {
                boxErr.style.display = 'none';
                boxOk.style.display = 'block';
            }

            // Render live preview journal table
            var tbodyJurnal = document.getElementById('tbody_preview_jurnal');
            tbodyJurnal.innerHTML = '';
            var totJurnalDr = 0,
                totJurnalCr = 0;

            journals.forEach(function(j) {
                var tr = document.createElement('tr');
                [fdate(j.d), j.coa, j.co, j.acc, j.desc, fmtEn(j.dr), fmtEn(j.cr)].forEach(function(value, index) {
                    var cell = document.createElement('td');
                    cell.textContent = value;
                    if (index >= 5) cell.className = 'text-right';
                    tr.appendChild(cell);
                });
                tbodyJurnal.appendChild(tr);
                totJurnalDr += j.dr;
                totJurnalCr += j.cr;
            });

            document.getElementById('tot_jurnal_debit').textContent = fmtEn(totJurnalDr);
            document.getElementById('tot_jurnal_kredit').textContent = fmtEn(totJurnalCr);

            document.getElementById('input_total_debit_jurnal').value = ConsultingReceiptMoney.decimal(totJurnalDr);
            document.getElementById('input_total_kredit_jurnal').value = ConsultingReceiptMoney.decimal(totJurnalCr);

            // Enable or disable modal save button
            var isOk = (errors.length === 0 && totJurnalDr === totJurnalCr);
            var submitBtn = document.querySelector('#dialog-popup button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = !isOk;
            }
            // Seperti mockup: jurnal hanya ditampilkan setelah Kontrol 0 (isian tetap terhitung di belakang layar).
            var journalSection = document.querySelector('.journal-section');
            if (journalSection && journalSection.style) journalSection.style.display = isOk ? '' : 'none';
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
                } catch (error) {
                    /* recalc displays error */
                }
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