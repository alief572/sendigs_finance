<?php
defined('BASEPATH') or exit('No direct script access allowed');
$escape = function($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
$availableCents = $allocation['capacity'] - $allocation['used'];
$availableDecimal = $availableCents / 100;
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
    <input type="hidden" name="no_inv" value="<?= count($rows) ?>">
    <input type="hidden" name="id_alokasi" value="<?= $escape($id_alokasi) ?>">
    <input type="hidden" name="allocation_fingerprint" value="<?= $escape($allocation_fingerprint) ?>">
    <input type="hidden" name="pph23_dipotong" value="<?= $escape($choice) ?>">
    <input type="hidden" name="ppn_dipotong" value="<?= $escape($ppn_choice) ?>">
    <input type="hidden" name="kontrol" id="input_kontrol" value="0">
    <input type="hidden" name="total_debit_jurnal" id="input_total_debit_jurnal" value="0">
    <input type="hidden" name="total_kredit_jurnal" id="input_total_kredit_jurnal" value="0">

    <div class="table-responsive">
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
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $index => $context) :
                    $n = $index + 1;
                    $inv = $context['invoice'];
                    $tax = $context['tax_calc'];
                    $taxCode = $context['tax_treatment'] ?? 'PKP';
                    $defPph = $context['default_pph'] / 100;
                    $saldo = (float)$inv['saldo_piutang'];
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
                                    <a href="javascript:void(0)" class="btn-reset-pph text-info" data-row="<?= $n ?>" data-default="<?= $defPph ?>">Kembalikan</a>
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
                        <td class="text-right font-bold bg-gray-light" id="cell_piutang_<?= $n ?>" data-piutang="<?= $saldo ?>">
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
                </tr>
            </tfoot>
        </table>
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
                <div class="summary-item">
                    <span>Total Piutang (Penjualan + PPN):</span>
                    <strong id="card_tot_piutang">Rp. 0,00</strong>
                </div>
                <div class="summary-item">
                    <span>Uang Masuk (Alokasi Bank):</span>
                    <strong id="card_uang_masuk" data-masuk="<?= $availableDecimal ?>">Rp. <?= number_format($availableDecimal, 2) ?></strong>
                </div>
                <div class="summary-item highlight">
                    <span>Kontrol ((Piutang - PPh - Admin - Sisa) - Uang Masuk):</span>
                    <span id="badge_kontrol" class="kontrol-badge kontrol-bad">Rp. 0,00</span>
                </div>
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

    var rowsData = <?= json_encode(array_map(function($r) {
        return array(
            'invoice' => $r['invoice']['no_invoice'],
            'tax_treatment' => $r['tax_treatment'] ?? 'PKP',
            'tagihan' => $r['tax_calc']['tagihan'] / 100,
            'default_pph' => $r['default_pph'] / 100,
            'spk' => $r['spk'] ?? '',
            'owner' => $r['owner']['nm_company'],
            'accounts' => $r['accounts'],
            'transDate' => $r['invoice']['tanggal_invoice'] ?? date('Y-m-d')
        );
    }, $rows), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    var choice = <?= json_encode($choice) ?>;
    var uangMasuk = parseFloat(document.getElementById('card_uang_masuk').getAttribute('data-masuk')) || 0;
    var container = document.getElementById('consulting-receipt-container');

    function parseVal(el) {
        if (!el) return 0;
        var val = el.value || '';
        val = val.split(',').join('');
        var n = parseFloat(val);
        return isNaN(n) ? 0 : Math.round(n * 100) / 100;
    }

    function fmtMoney(num) {
        return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fmtIdn(num) {
        return 'Rp. ' + num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalc() {
        var nRows = rowsData.length;
        var totPiutang = 0, totPph = 0, totAlok = 0, totAdmin = 0, totSisa = 0;
        var errors = [];
        var journals = [];
        var totalBankDebit = 0;

        for (var i = 1; i <= nRows; i++) {
            var meta = rowsData[i - 1];
            var piutangCell = document.getElementById('cell_piutang_' + i);
            var piutang = parseFloat(piutangCell.getAttribute('data-piutang')) || 0;
            var inpPph = container.querySelector('[name="pph_actual_' + i + '"]');
            var inpAlok = container.querySelector('[name="penerimaan_' + i + '"]');
            var inpAdmin = container.querySelector('[name="biaya_admin_' + i + '"]');
            var inpSisa = container.querySelector('[name="sisa_piutang_' + i + '"]');

            var pph = choice === 'Y' ? parseVal(inpPph) : 0;
            var alok = parseVal(inpAlok);
            var admin = parseVal(inpAdmin);
            var sisa = parseVal(inpSisa);

            if (pph < 0 || alok < 0 || admin < 0 || sisa < 0) {
                errors.push('Nilai tidak boleh bernilai minus pada ' + meta.invoice + '.');
            }
            if (pph > piutang) {
                errors.push('PPh 23 melebihi piutang dagang invoice ' + meta.invoice + '.');
            }
            if (sisa > piutang) {
                errors.push('Sisa piutang melebihi piutang dagang invoice ' + meta.invoice + '.');
            }

            // Tagihan - PPH column update
            var textTagihanNet = document.getElementById('text_tagihan_net_' + i);
            if (textTagihanNet) {
                textTagihanNet.textContent = fmtMoney(meta.tagihan - pph);
            }

            // Reset PPh link visibility
            if (choice === 'Y' && inpPph) {
                var defPph = parseFloat(inpPph.getAttribute('data-default')) || 0;
                var resetWrapper = container.querySelector('.reset-pph-link-' + i);
                if (resetWrapper) {
                    resetWrapper.style.display = (Math.abs(pph - defPph) > 0.009) ? 'block' : 'none';
                }
            }

            totPiutang += piutang;
            totPph += pph;
            totAlok += alok;
            totAdmin += admin;
            totSisa += sisa;

            // Bank portion for this invoice
            var bankCash = Math.round((piutang - pph - admin - sisa) * 100) / 100;
            totalBankDebit += bankCash;

            // Build journal lines per invoice
            var sfx = ' - ' + meta.invoice + (meta.spk ? ' - ' + meta.spk : '') + (meta.tax_treatment === 'KB' ? ' - Kawasan Berikat' : '');

            // 1. Bank Debit
            if (bankCash > 0) {
                journals.push({ d: meta.transDate, coa: meta.accounts.bank.code, co: meta.owner, acc: meta.accounts.bank.name, desc: meta.accounts.bank.name + sfx, dr: bankCash, cr: 0 });
            }
            // 2. PPh 23 Disetor Debit
            if (pph > 0) {
                journals.push({ d: meta.transDate, coa: meta.accounts.pph.code, co: meta.owner, acc: meta.accounts.pph.name, desc: meta.accounts.pph.name + sfx, dr: pph, cr: 0 });
            }
            // 3. Biaya Admin Debit
            if (admin > 0) {
                journals.push({ d: meta.transDate, coa: meta.accounts.admin.code, co: meta.owner, acc: meta.accounts.admin.name, desc: meta.accounts.admin.name + sfx, dr: admin, cr: 0 });
            }
            // 4. Piutang Dagang Usaha Kredit (Gross initial receivable)
            if (piutang > 0) {
                journals.push({ d: meta.transDate, coa: meta.accounts.receivable.code, co: meta.owner, acc: meta.accounts.receivable.name, desc: meta.accounts.receivable.name + sfx, dr: 0, cr: piutang });
            }
            // 5. Piutang Dagang Usaha Debit (Kurang Bayar / Sisa Piutang)
            if (sisa > 0) {
                journals.push({ d: meta.transDate, coa: meta.accounts.receivable.code, co: meta.owner, acc: meta.accounts.receivable.name, desc: meta.accounts.receivable.name + sfx, dr: sisa, cr: 0 });
            }
        }

        // Admin max check
        if (totAdmin > 10000) {
            errors.push('Total biaya admin (' + fmtMoney(totAdmin) + ') melebihi batas maksimal Rp 10.000,00.');
        }

        // Kontrol calculation
        var kontrol = Math.round(((totPiutang - totPph - totAdmin - totSisa) - uangMasuk) * 100) / 100;
        if (Math.abs(kontrol) > 0.009) {
            if (kontrol > 0) {
                errors.push('Masih ada selisih kurang ' + fmtMoney(kontrol) + '. Alokasikan ke PPh 23, Biaya Admin, atau Sisa Piutang pada invoice yang sesuai.');
            } else {
                errors.push('Uang masuk lebih besar ' + fmtMoney(-kontrol) + ' dari piutang yang dialokasikan. Tambahkan invoice atau sesuaikan alokasi.');
            }
        }

        // Update footer totals
        document.getElementById('foot_tot_pph').textContent = fmtMoney(totPph);
        document.getElementById('foot_tot_piutang').textContent = fmtMoney(totPiutang);
        document.getElementById('foot_tot_alok').textContent = fmtMoney(totAlok);
        document.getElementById('foot_tot_admin').textContent = fmtMoney(totAdmin);
        document.getElementById('foot_tot_sisa').textContent = fmtMoney(totSisa);

        // Update summary card
        document.getElementById('card_tot_piutang').textContent = fmtIdn(totPiutang);

        var badgeKontrol = document.getElementById('badge_kontrol');
        badgeKontrol.textContent = fmtMoney(kontrol);
        if (Math.abs(kontrol) < 0.01) {
            badgeKontrol.className = 'kontrol-badge kontrol-ok';
        } else {
            badgeKontrol.className = 'kontrol-badge kontrol-bad';
        }

        // Update hidden kontrol field
        document.getElementById('input_kontrol').value = Math.abs(kontrol) < 0.01 ? '0' : kontrol.toString();

        // Error message display
        var boxErr = document.getElementById('box-error-message');
        var boxOk = document.getElementById('box-ok-message');
        var errText = document.getElementById('error-text');

        if (errors.length > 0) {
            errText.innerHTML = errors.join('<br>');
            boxErr.style.display = 'block';
            boxOk.style.display = 'none';
        } else {
            boxErr.style.display = 'none';
            boxOk.style.display = 'block';
        }

        // Render preview journal table
        var tbodyJurnal = document.getElementById('tbody_preview_jurnal');
        tbodyJurnal.innerHTML = '';
        var totJurnalDr = 0, totJurnalCr = 0;

        journals.forEach(function(j) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td>' + j.d + '</td>' +
                '<td>' + j.coa + '</td>' +
                '<td>' + j.co + '</td>' +
                '<td>' + j.acc + '</td>' +
                '<td>' + j.desc + '</td>' +
                '<td class="text-right">' + (j.dr > 0 ? fmtMoney(j.dr) : '-') + '</td>' +
                '<td class="text-right">' + (j.cr > 0 ? fmtMoney(j.cr) : '-') + '</td>';
            tbodyJurnal.appendChild(tr);
            totJurnalDr += j.dr;
            totJurnalCr += j.cr;
        });

        totJurnalDr = Math.round(totJurnalDr * 100) / 100;
        totJurnalCr = Math.round(totJurnalCr * 100) / 100;

        document.getElementById('tot_jurnal_debit').textContent = fmtMoney(totJurnalDr);
        document.getElementById('tot_jurnal_kredit').textContent = fmtMoney(totJurnalCr);

        document.getElementById('input_total_debit_jurnal').value = totJurnalDr.toString();
        document.getElementById('input_total_kredit_jurnal').value = totJurnalCr.toString();

        // Enable or disable modal save button
        var isOk = (errors.length === 0 && Math.abs(totJurnalDr - totJurnalCr) < 0.01);
        var submitBtn = document.querySelector('#dialog-popup button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = !isOk;
        }
    }

    // Event listeners
    container.addEventListener('change', function(e) {
        var t = e.target;
        var row = t.getAttribute('data-row');
        if (row && t.classList.contains('input-sisa')) {
            // When user edits sisa piutang, automatically adjust alokasi penerimaan
            var piutang = parseFloat(document.getElementById('cell_piutang_' + row).getAttribute('data-piutang')) || 0;
            var sisa = parseVal(t);
            var alokInput = container.querySelector('[name="penerimaan_' + row + '"]');
            if (alokInput) {
                var newAlok = Math.max(0, piutang - sisa);
                $(alokInput).autoNumeric('set', newAlok);
            }
        }
        recalc();
    });

    container.addEventListener('keyup', function(e) {
        if (e.target.classList.contains('autonum')) {
            recalc();
        }
    });

    container.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-reset-pph')) {
            var row = e.target.getAttribute('data-row');
            var def = e.target.getAttribute('data-default');
            var inp = container.querySelector('[name="pph_actual_' + row + '"]');
            if (inp) {
                $(inp).autoNumeric('set', def);
                recalc();
            }
        }
    });

    // Initial calculation
    setTimeout(recalc, 100);
})();
</script>
