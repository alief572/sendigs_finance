<?php
$total_debit = (!empty($total_debit)) ? $total_debit : 0;
$total_kredit = (!empty($total_kredit)) ? $total_kredit : 0;

$no_surat = !empty($header['no_surat']) ? $header['no_surat'] : '-';
$customer = !empty($header['nm_customer']) ? $header['nm_customer'] : (!empty($detail[0]['nm_customer']) ? $detail[0]['nm_customer'] : '-');
$bank_name = !empty($alokasi['nm_bank']) ? $alokasi['nm_bank'] : (!empty($alokasi['nama_bank_acc']) ? $alokasi['nama_bank_acc'] : 'Bank');
$rekening = !empty($alokasi['rekening']) ? ' (' . $alokasi['rekening'] . ')' : '';
$tgl_koran = !empty($alokasi['tanggal_transaksi']) ? date('d F Y', strtotime($alokasi['tanggal_transaksi'])) : (!empty($header['created_date']) ? date('d F Y', strtotime($header['created_date'])) : '-');
$uang_masuk_val = !empty($header['nominal_penerimaan_bank']) ? (float)$header['nominal_penerimaan_bank'] : (!empty($alokasi['nilai_terpakai']) ? (float)$alokasi['nilai_terpakai'] : 0);
$pph_dipotong = (!empty($header['pph23_dipotong']) && $header['pph23_dipotong'] === 'Y');
$receiptSnapshot = json_decode($detail[0]['consulting_snapshot'] ?? '', true);
if ((int)($receiptSnapshot['calculation_version'] ?? 0) === 4) $tgl_koran = date('d F Y', strtotime($receiptSnapshot['date']));

$isDepositReceipt = false;
if (!empty($header['jenis_penerimaan']) && $header['jenis_penerimaan'] === 'deposit') {
    $isDepositReceipt = true;
} else {
    foreach ($detail as $d) {
        $tt = $d['tax_treatment'] ?? '';
        if (empty($tt) && !empty($d['consulting_snapshot'])) {
            $s = json_decode($d['consulting_snapshot'], true);
            $tt = $s['tax_treatment'] ?? $s['type'] ?? '';
        }
        if (strtoupper($tt) === 'DEP') {
            $isDepositReceipt = true;
            break;
        }
    }
}
?>

<!-- Header Info Card -->
<div class="panel panel-default" style="border-left: 4px solid #00c0ef; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">
    <div class="panel-body" style="padding: 14px 18px; background-color: #fafbfc;">
        <div class="row">
            <div class="col-md-4 col-sm-6" style="border-right: 1px solid #eee;">
                <div style="font-size: 11px; text-transform: uppercase; color: #888; font-weight: 600; letter-spacing: 0.5px;">No. Surat Penerimaan</div>
                <div style="font-size: 16px; font-weight: bold; color: #2c3e50; margin-top: 3px;">
                    <i class="fa fa-file-text-o text-primary"></i> <?= htmlspecialchars($no_surat) ?>
                </div>
                <div style="margin-top: 8px; font-size: 12px; color: #555;">
                    <span style="color: #888;">Customer:</span> <strong><?= htmlspecialchars($customer) ?></strong>
                </div>
            </div>
            <div class="col-md-4 col-sm-6" style="border-right: 1px solid #eee;">
                <div style="font-size: 11px; text-transform: uppercase; color: #888; font-weight: 600; letter-spacing: 0.5px;">Bank Penerima & Tanggal</div>
                <div style="font-size: 14px; font-weight: bold; color: #2c3e50; margin-top: 3px;">
                    <i class="fa fa-university text-info"></i> <?= htmlspecialchars($bank_name . $rekening) ?>
                </div>
                <div style="margin-top: 8px; font-size: 12px; color: #555;">
                    <span style="color: #888;">Tgl Transaksi Koran:</span> <span class="label label-info"><?= $tgl_koran ?></span>
                </div>
            </div>
            <div class="col-md-4 col-sm-12">
                <div style="font-size: 11px; text-transform: uppercase; color: #888; font-weight: 600; letter-spacing: 0.5px;">Uang Masuk Bank</div>
                <div style="font-size: 18px; font-weight: bold; color: #008d4c; margin-top: 3px;">
                    Rp <?= number_format($uang_masuk_val, 2, ',', '.') ?>
                </div>
                <div style="margin-top: 8px; font-size: 12px; color: #555;">
                    <?php if ($isDepositReceipt): ?>
                        <span class="label label-primary"><i class="fa fa-ticket"></i> Pelunasan Kwitansi Deposit</span>
                    <?php else: ?>
                        <span style="color: #888;">Status PPh 23:</span>
                        <?php if ($pph_dipotong): ?>
                            <span class="label label-success"><i class="fa fa-check"></i> Dipotong (2%)</span>
                        <?php else: ?>
                            <span class="label label-default">Tidak Dipotong</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($isDepositReceipt): ?>
    <!-- RINCIAN KWITANSI DEPOSIT (7 Kolom) -->
    <h5 style="font-weight: bold; margin-bottom: 8px; color: #444;"><i class="fa fa-list"></i> Rincian Kwitansi Deposit</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover" style="font-size: 12px; margin-bottom: 15px;">
            <thead style="background-color: #f4f6f9;">
                <tr>
                    <th class="text-center" style="width: 100px;">Tgl Kwitansi</th>
                    <th class="text-center" style="width: 160px;">Nomor Kwitansi</th>
                    <th class="text-center">No SPK</th>
                    <th class="text-center" style="width: 130px;">Company</th>
                    <th class="text-right" style="width: 140px;">Nilai Kwitansi</th>
                    <th class="text-right" style="width: 140px;">Piutang Dagang</th>
                    <th class="text-right" style="width: 140px; background-color: #e8f5e9;">Uang Masuk</th>
                    <th class="text-right" style="width: 120px;">Biaya Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totPiutangDep = 0;
                $totPenerimaanDep = 0;
                $totAdminDep = 0;

                foreach ($detail as $item) :
                    $item = Penerimaan_calculation::receipt_detail($item);
                    $snap = json_decode($item['consulting_snapshot'] ?? '', true);
                    $spkNo = $snap['spk'] ?? (!empty($item['id_spk_penawaran']) ? $item['id_spk_penawaran'] : '-');
                    $compName = $snap['owner']['nm_company'] ?? 'STM';
                    $nilaiKw = (float)$item['total_nominal_jurnal'];
                    $piutangVal = (float)$item['total_akhir_jurnal'];
                    $uangVal = (float)$item['penerimaan'];
                    $adminVal = (float)$item['biaya_admin'];

                    echo '<tr>';
                    echo '<td class="text-center">' . (!empty($item['tgl_inv']) ? date('d-M-Y', strtotime($item['tgl_inv'])) : '-') . '</td>';
                    echo '<td class="text-center font-bold" style="font-family: monospace;">' . htmlspecialchars($item['no_invoice']) . '</td>';
                    echo '<td class="text-center">' . htmlspecialchars($spkNo) . '</td>';
                    echo '<td class="text-center"><span class="label label-info">' . htmlspecialchars($compName) . '</span></td>';
                    echo '<td class="text-right font-bold">' . number_format($nilaiKw, 2, ',', '.') . '</td>';
                    echo '<td class="text-right font-bold">' . number_format($piutangVal, 2, ',', '.') . '</td>';
                    echo '<td class="text-right font-bold" style="background-color: #f1f8e9; color: #008d4c;">' . number_format($uangVal, 2, ',', '.') . '</td>';
                    echo '<td class="text-right">' . number_format($adminVal, 2, ',', '.') . '</td>';
                    echo '</tr>';

                    $totPiutangDep += $piutangVal;
                    $totPenerimaanDep += $uangVal;
                    $totAdminDep += $adminVal;
                endforeach;
                ?>
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f9f9f9;">
                    <td colspan="4" class="text-right">Total:</td>
                    <td class="text-right"><?= number_format($totPiutangDep, 2, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($totPiutangDep, 2, ',', '.') ?></td>
                    <td class="text-right" style="color: #008d4c;"><?= number_format($totPenerimaanDep, 2, ',', '.') ?></td>
                    <td class="text-right"><?= number_format($totAdminDep, 2, ',', '.') ?></td>
                </tr>
                <?php
                $kontrolDep = ($totPiutangDep - $totAdminDep) - (float)$uang_masuk_val;
                ?>
                <tr>
                    <td colspan="5" class="text-right font-weight-bold">Uang Masuk Bank</td>
                    <td colspan="3" class="text-right font-weight-bold" style="color: #008d4c;">
                        Rp <?= number_format($uang_masuk_val, 2, ',', '.') ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" class="text-right font-weight-bold">Biaya Admin Ditanggung Perusahaan</td>
                    <td colspan="3" class="text-right font-weight-bold text-warning">
                        Rp <?= number_format($totAdminDep, 2, ',', '.') ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" class="text-right font-weight-bold">Kontrol Formula ((Piutang - Biaya Admin) - Bank)</td>
                    <td colspan="3" class="text-right font-weight-bold">
                        <?php if (abs($kontrolDep) < 0.01): ?>
                            <span class="label label-success" style="font-size: 12px;"><i class="fa fa-check-circle"></i> Balanced (0,00)</span>
                        <?php else: ?>
                            <span class="label label-danger" style="font-size: 12px;">Selisih: Rp <?= number_format($kontrolDep, 2, ',', '.') ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
<?php else: ?>
    <!-- RINCIAN INVOICE BIASA -->
    <h5 style="font-weight: bold; margin-bottom: 8px; color: #444;"><i class="fa fa-list"></i> Rincian Invoice Penerimaan</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover" style="font-size: 12px; margin-bottom: 15px;">
            <thead style="background-color: #f4f6f9;">
                <tr>
                    <th class="text-center" style="width: 85px;">Tgl invoice</th>
                    <th class="text-center">Nomor Invoice</th>
                    <th class="text-center">DPP</th>
                    <th class="text-center">DPP Lain</th>
                    <th class="text-center">PPN</th>
                    <th class="text-center">PPH 23</th>
                    <th class="text-center">Tagihan + Ppn</th>
                    <th class="text-center">Tagihan + Ppn - Pph</th>
                    <th class="text-center">Piutang Dagang</th>
                    <th class="text-center" style="background-color: #e8f5e9;">Uang Masuk</th>
                    <th class="text-center" style="background-color: #e0f2fe;">Deposit Dipakai</th>
                    <th class="text-center" style="background-color: #fef3c7;">Lebih Bayar</th>
                    <th class="text-center">Biaya Admin</th>
                    <th class="text-center">Sisa Piutang</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_piutang = 0;
                $total_piutang_dagang = 0;
                $total_pph23 = 0;
                $total_pph23_kontrol = 0; // PPh yang ikut rumus Kontrol: hanya baris To-Be (v4) yang Piutang Dagang-nya bruto
                $total_penerimaan = 0;
                $total_deposit = 0;
                $total_lebih_bayar = 0;
                $total_biaya_admin = 0;
                $total_sisa_piutang = 0;

                foreach ($detail as $item) :
                    $rowSnapshot = json_decode($item['consulting_snapshot'] ?? '', true);
                    $isCalcV4 = (int)($rowSnapshot['calculation_version'] ?? 0) === 4;
                    $item = Penerimaan_calculation::receipt_detail($item);
                    $isNew = (int)($item['consulting_version'] ?? 0) === 3;
                    $decimals = $isNew ? 2 : 0;
                    $taxTreatment = !empty($item['tax_treatment']) ? strtoupper($item['tax_treatment']) : '';
                    if (empty($taxTreatment) && !empty($item['consulting_snapshot'])) {
                        $snap = json_decode($item['consulting_snapshot'], true);
                        $taxTreatment = $snap['tax_treatment'] ?? '';
                    }

                    $taxBadge = '';
                    if ($taxTreatment === 'WAPU') {
                        $taxBadge = ' <span class="label label-warning" style="font-size:9px;">WAPU</span>';
                    } elseif ($taxTreatment === 'KB') {
                        $taxBadge = ' <span class="label label-info" style="font-size:9px;">KB</span>';
                    } elseif ($taxTreatment === 'PKP') {
                        $taxBadge = ' <span class="label label-primary" style="font-size:9px;">PKP</span>';
                    }

                    $depVal = (float)($item['dep'] ?? 0);
                    $lebihVal = (float)($item['lebih'] ?? 0);

                    echo '<tr>';
                    echo '<td class="text-center">' . (!empty($item['tgl_inv']) ? date('d-M-Y', strtotime($item['tgl_inv'])) : '-') . '</td>';
                    echo '<td class="text-center"><b>' . htmlspecialchars($item['no_invoice']) . '</b>' . $taxBadge . '</td>';
                    echo '<td class="text-right">' . number_format($item['dpp'], $decimals) . '</td>';
                    echo '<td class="text-right">' . number_format($item['dpp_lain'], $decimals) . '</td>';
                    echo '<td class="text-right">' . number_format($item['ppn'], $decimals) . '</td>';
                    echo '<td class="text-right text-danger">' . number_format($item['pph23'], $decimals) . '</td>';
                    echo '<td class="text-right">' . number_format($item['tagihan_ppn'], $decimals) . '</td>';
                    echo '<td class="text-right">' . number_format($item['tagihan_net'] ?? $item['total_akhir_jurnal'], $decimals) . '</td>';
                    echo '<td class="text-right">' . number_format($item['total'], $decimals) . '</td>';
                    echo '<td class="text-right font-weight-bold" style="background-color: #f1f8e9;"><b>' . number_format($item['penerimaan'], $decimals) . '</b></td>';
                    echo '<td class="text-right' . ($depVal > 0 ? ' font-weight-bold text-primary' : ' text-muted') . '" style="background-color: #f0f9ff;">' . ($depVal > 0 ? number_format($depVal, $decimals) : '-') . '</td>';
                    echo '<td class="text-right' . ($lebihVal > 0 ? ' font-weight-bold text-warning' : ' text-muted') . '" style="background-color: #fffbeb;">' . ($lebihVal > 0 ? number_format($lebihVal, $decimals) : '-') . '</td>';
                    echo '<td class="text-right">' . number_format($item['biaya_admin'], $decimals) . '</td>';
                    echo '<td class="text-right' . ($item['sisa_piutang'] > 0 ? ' text-warning' : '') . '">' . number_format($item['sisa_piutang'], $decimals) . '</td>';
                    echo '</tr>';

                    $total_piutang += (float)$item['total_akhir_jurnal'];
                    $total_piutang_dagang += (float)$item['total_akhir_jurnal'];
                    $total_pph23 += (float)$item['pph23'];
                    // Invoice lama: total_akhir_jurnal sudah neto setelah PPh 23, jadi PPh tidak dikurangkan lagi di Kontrol.
                    if ($isCalcV4) $total_pph23_kontrol += (float)$item['pph23'];
                    $total_penerimaan += (float)$item['penerimaan'];
                    $total_deposit += $depVal;
                    $total_lebih_bayar += $lebihVal;
                    $total_biaya_admin += (float)$item['biaya_admin'];
                    $total_sisa_piutang += (float)$item['sisa_piutang'];
                endforeach;
                ?>
            </tbody>
            <tfoot>
                <tr style="font-weight: bold; background-color: #f9f9f9;">
                    <td colspan="6" class="text-right">Total</td>
                    <td colspan="2">
                        <input type="text" name="total_piutang" id="total_piutang" class="form-control form-control-sm autonum text-right font-weight-bold" value="<?= $total_piutang ?>" readonly>
                    </td>
                    <td>
                        <input type="text" name="total_piutang_dagang" id="total_piutang_dagang" class="form-control form-control-sm autonum text-right font-weight-bold" value="<?= $total_piutang ?>" readonly>
                    </td>
                    <td>
                        <input type="text" name="total_penerimaan" id="total_penerimaan" class="form-control form-control-sm autonum text-right font-weight-bold" style="color: #008d4c;" value="<?= $total_penerimaan ?>" readonly>
                    </td>
                    <td>
                        <input type="text" name="total_deposit" id="total_deposit" class="form-control form-control-sm autonum text-right font-weight-bold" style="color: #0284c7;" value="<?= $total_deposit ?>" readonly>
                    </td>
                    <td>
                        <input type="text" name="total_lebih_bayar" id="total_lebih_bayar" class="form-control form-control-sm autonum text-right font-weight-bold" style="color: #d97706;" value="<?= $total_lebih_bayar ?>" readonly>
                    </td>
                    <td>
                        <input type="text" name="total_biaya_admin" id="total_biaya_admin" class="form-control form-control-sm autonum text-right" value="<?= $total_biaya_admin ?>" readonly>
                    </td>
                    <td>
                        <input type="text" name="total_sisa_piutang" id="total_sisa_piutang" class="form-control form-control-sm autonum text-right" value="<?= $total_sisa_piutang ?>" readonly>
                    </td>
                </tr>
                <?php
                $uangMasukVal = (float)$uang_masuk_val;
                $kontrolVal = (($total_piutang_dagang - $total_pph23_kontrol - $total_biaya_admin - $total_sisa_piutang) - $uangMasukVal - $total_deposit) + $total_lebih_bayar;
                ?>
                <tr>
                    <td colspan="9" class="text-right font-weight-bold">Uang Masuk Bank</td>
                    <td colspan="5">
                        <input type="text" name="uang_masuk" class="form-control form-control-sm autonum text-right" id="uang_masuk" value="<?= $uangMasukVal ?>" readonly style="max-width: 200px;">
                    </td>
                </tr>
                <tr>
                    <td colspan="9" class="text-right font-weight-bold">Total Deposit Digunakan</td>
                    <td colspan="5">
                        <input type="text" name="summary_deposit" class="form-control form-control-sm autonum text-right" id="summary_deposit" value="<?= $total_deposit ?>" readonly style="max-width: 200px; color: #0284c7;">
                    </td>
                </tr>
                <tr>
                    <td colspan="9" class="text-right font-weight-bold">Total Lebih Bayar (Deposit Baru)</td>
                    <td colspan="5">
                        <input type="text" name="summary_lebih_bayar" class="form-control form-control-sm autonum text-right" id="summary_lebih_bayar" value="<?= $total_lebih_bayar ?>" readonly style="max-width: 200px; color: #d97706;">
                    </td>
                </tr>
                <tr>
                    <td colspan="9" class="text-right font-weight-bold">Kontrol Formula ((Piutang - PPh - Admin - Sisa) - Bank - Dep) + Lebih</td>
                    <td colspan="5" style="display: flex; align-items: center;">
                        <input type="text" name="kontrol" class="form-control form-control-sm autonum text-right" id="kontrol" value="<?= round($kontrolVal, 2) ?>" readonly style="max-width: 200px; font-weight: bold; color: <?= abs($kontrolVal) < 0.01 ? '#008d4c' : '#dc2626' ?>;">
                        <?php if (abs($kontrolVal) < 0.01) : ?>
                            <span class="text-success" style="font-weight: 700; font-size: 11px; margin-left: 8px;"><i class="fa fa-check-circle"></i> Balanced (0.00)</span>
                        <?php else : ?>
                            <span class="text-danger" style="font-weight: 700; font-size: 11px; margin-left: 8px;"><i class="fa fa-exclamation-circle"></i> Selisih: <?= number_format($kontrolVal, 2) ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
<?php endif; ?>

<?php if (!empty($list_jurnal)) : // Tanpa baris di tr_jurnal, seksi jurnal tidak ditampilkan ?>
<h5 style="font-weight: bold; margin-top: 25px; margin-bottom: 8px; color: #444;"><i class="fa fa-book"></i> Jurnal Akuntansi Penerimaan</h5>
<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" style="font-size: 12px; margin-bottom: 10px;">
        <thead style="background-color: #f4f6f9;">
            <tr>
                <th class="text-center" style="width: 100px;">Tanggal Jurnal</th>
                <th class="text-center" style="width: 110px;">COA</th>
                <th class="text-center">Nama Company</th>
                <th class="text-center">Nama Account</th>
                <th class="text-center">Keterangan</th>
                <th class="text-center" style="width: 120px;">Debit</th>
                <th class="text-center" style="width: 120px;">Credit</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_debit = 0;
            $total_kredit = 0;

            foreach ($list_jurnal as $item) {
                $isNew = (int)($header['consulting_batch'] ?? 0) === 3;
                $decimals = $isNew ? 2 : 0;

                echo '<tr>';
                echo '<td class="text-center">' . date('d F Y', strtotime($item['tgl_jurnal'])) . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($item['coa'], ENT_QUOTES, 'UTF-8') . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($item['nm_company']) . '</td>';
                echo '<td class="text-center">' . htmlspecialchars($item['nm_coa']) . '</td>';
                echo '<td>' . htmlspecialchars($item['keterangan'] ?? '-') . '</td>';
                echo '<td class="text-right font-weight-bold">' . number_format($item['debit'], $decimals) . '</td>';
                echo '<td class="text-right font-weight-bold">' . number_format($item['kredit'], $decimals) . '</td>';
                echo '</tr>';

                $total_debit += $item['debit'];
                $total_kredit += $item['kredit'];
            }
            ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f9f9f9;">
                <th colspan="5" class="text-center">
                    Grand Total
                    <?php if (round($total_debit, 2) === round($total_kredit, 2)): ?>
                        <span class="label label-success" style="margin-left: 10px;"><i class="fa fa-check"></i> Balanced</span>
                    <?php else: ?>
                        <span class="label label-danger" style="margin-left: 10px;"><i class="fa fa-times"></i> Unbalanced</span>
                    <?php endif; ?>
                </th>
                <td class="text-right td_total_debit_jurnal" style="font-weight: bold;">
                    <?= number_format($total_debit, 2) ?>
                    <input type="hidden" name="total_debit" value="<?= $total_debit ?>">
                </td>
                <td class="text-right td_total_kredit_jurnal" style="font-weight: bold;">
                    <?= number_format($total_kredit, 2) ?>
                    <input type="hidden" name="total_kredit" value="<?= $total_kredit ?>">
                </td>
            </tr>
        </tfoot>
    </table>
</div>
<?php endif; ?>
