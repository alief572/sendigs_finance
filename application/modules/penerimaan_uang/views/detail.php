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

<div class="mk">

    <!-- Header Info Card -->
    <div class="box primary" style="margin-bottom: 16px;">
        <div class="kv">
            <div><span>No. Surat Penerimaan</span><?= htmlspecialchars($no_surat) ?></div>
            <div><span>Customer</span><?= htmlspecialchars($customer) ?></div>
            <div><span>Bank Penerima</span><?= htmlspecialchars($bank_name . $rekening) ?></div>
            <div><span>Tgl Transaksi Koran</span><span class="lbl"><?= $tgl_koran ?></span></div>
            <div><span>Uang Masuk Bank</span>Rp <?= number_format($uang_masuk_val, 2, ',', '.') ?></div>
            <div>
                <span>Status</span>
                <?php if ($isDepositReceipt): ?>
                    <span class="lbl used">Pelunasan Kwitansi Deposit</span>
                <?php elseif ($pph_dipotong): ?>
                    <span class="lbl used">PPh 23 Dipotong (2%)</span>
                <?php else: ?>
                    <span class="lbl">PPh 23 Tidak Dipotong</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($isDepositReceipt): ?>
        <!-- RINCIAN KWITANSI DEPOSIT (7 Kolom) -->
        <div class="section-t">Rincian Kwitansi Deposit</div>
        <div class="scroll">
            <table style="margin-bottom: 15px;">
                <thead>
                    <tr>
                        <th class="c">Tgl Kwitansi</th>
                        <th class="c">Nomor Kwitansi</th>
                        <th class="c">No SPK</th>
                        <th class="c">Company</th>
                        <th class="r">Nilai Kwitansi</th>
                        <th class="r">Piutang Dagang</th>
                        <th class="r">Uang Masuk</th>
                        <th class="r">Biaya Admin</th>
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
                        echo '<td class="c">' . (!empty($item['tgl_inv']) ? date('d-M-Y', strtotime($item['tgl_inv'])) : '-') . '</td>';
                        echo '<td class="c" style="font-family: monospace; font-weight: 700;">' . htmlspecialchars($item['no_invoice']) . '</td>';
                        echo '<td class="c">' . htmlspecialchars($spkNo) . '</td>';
                        echo '<td class="c"><span class="lbl">' . htmlspecialchars($compName) . '</span></td>';
                        echo '<td class="r">' . number_format($nilaiKw, 2, ',', '.') . '</td>';
                        echo '<td class="r">' . number_format($piutangVal, 2, ',', '.') . '</td>';
                        echo '<td class="r state-ok">' . number_format($uangVal, 2, ',', '.') . '</td>';
                        echo '<td class="r">' . number_format($adminVal, 2, ',', '.') . '</td>';
                        echo '</tr>';

                        $totPiutangDep += $piutangVal;
                        $totPenerimaanDep += $uangVal;
                        $totAdminDep += $adminVal;
                    endforeach;
                    ?>
                </tbody>
                <tfoot>
                    <tr class="sum">
                        <td colspan="4" class="r">Total:</td>
                        <td class="r"><?= number_format($totPiutangDep, 2, ',', '.') ?></td>
                        <td class="r"><?= number_format($totPiutangDep, 2, ',', '.') ?></td>
                        <td class="r state-ok"><?= number_format($totPenerimaanDep, 2, ',', '.') ?></td>
                        <td class="r"><?= number_format($totAdminDep, 2, ',', '.') ?></td>
                    </tr>
                    <?php
                    $kontrolDep = ($totPiutangDep - $totAdminDep) - (float)$uang_masuk_val;
                    ?>
                    <tr class="sum">
                        <td colspan="5" class="r">Uang Masuk Bank</td>
                        <td colspan="3" class="r state-ok">
                            Rp <?= number_format($uang_masuk_val, 2, ',', '.') ?>
                        </td>
                    </tr>
                    <tr class="sum">
                        <td colspan="5" class="r">Biaya Admin Ditanggung Perusahaan</td>
                        <td colspan="3" class="r">
                            Rp <?= number_format($totAdminDep, 2, ',', '.') ?>
                        </td>
                    </tr>
                    <tr class="sum">
                        <td colspan="5" class="r">Kontrol Formula ((Piutang - Biaya Admin) - Bank)</td>
                        <td colspan="3" class="r">
                            <?php if (abs($kontrolDep) < 0.01): ?>
                                <span class="lbl used">Balanced (0,00)</span>
                            <?php else: ?>
                                <span class="state-bad">Selisih: Rp <?= number_format($kontrolDep, 2, ',', '.') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php else: ?>
        <!-- RINCIAN INVOICE BIASA -->
        <div class="section-t">Rincian Invoice Penerimaan</div>
        <div class="scroll">
            <table style="margin-bottom: 15px;">
                <thead>
                    <tr>
                        <th class="c">Tgl invoice</th>
                        <th class="c">Nomor Invoice</th>
                        <th class="r">DPP</th>
                        <th class="r">DPP Lain</th>
                        <th class="r">PPN</th>
                        <th class="r">PPH 23</th>
                        <th class="r">Tagihan + Ppn</th>
                        <th class="r">Tagihan + Ppn - Pph</th>
                        <th class="r">Piutang Dagang</th>
                        <th class="r">Uang Masuk</th>
                        <th class="r">Deposit Dipakai</th>
                        <th class="r">Lebih Bayar</th>
                        <th class="r">Biaya Admin</th>
                        <th class="r">Sisa Piutang</th>
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
                        if (in_array($taxTreatment, array('WAPU', 'KB', 'PKP', 'UMKM'), true)) {
                            $taxBadge = ' ' . mk_tax_badge($taxTreatment);
                        }

                        $depVal = (float)($item['dep'] ?? 0);
                        $lebihVal = (float)($item['lebih'] ?? 0);

                        echo '<tr>';
                        echo '<td class="c">' . (!empty($item['tgl_inv']) ? date('d-M-Y', strtotime($item['tgl_inv'])) : '-') . '</td>';
                        echo '<td class="c"><b>' . htmlspecialchars($item['no_invoice']) . '</b>' . $taxBadge . '</td>';
                        echo '<td class="r">' . number_format($item['dpp'], $decimals) . '</td>';
                        echo '<td class="r">' . number_format($item['dpp_lain'], $decimals) . '</td>';
                        echo '<td class="r">' . number_format($item['ppn'], $decimals) . '</td>';
                        echo '<td class="r state-bad">' . number_format($item['pph23'], $decimals) . '</td>';
                        echo '<td class="r">' . number_format($item['tagihan_ppn'], $decimals) . '</td>';
                        echo '<td class="r">' . number_format($item['tagihan_net'] ?? $item['total_akhir_jurnal'], $decimals) . '</td>';
                        echo '<td class="r">' . number_format($item['total'], $decimals) . '</td>';
                        echo '<td class="r state-ok"><b>' . number_format($item['penerimaan'], $decimals) . '</b></td>';
                        echo '<td class="r' . ($depVal > 0 ? '' : ' muted') . '">' . ($depVal > 0 ? number_format($depVal, $decimals) : '-') . '</td>';
                        echo '<td class="r' . ($lebihVal > 0 ? '' : ' muted') . '">' . ($lebihVal > 0 ? number_format($lebihVal, $decimals) : '-') . '</td>';
                        echo '<td class="r">' . number_format($item['biaya_admin'], $decimals) . '</td>';
                        echo '<td class="r">' . number_format($item['sisa_piutang'], $decimals) . '</td>';
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
                    <tr class="sum">
                        <td colspan="6" class="r">Total</td>
                        <td colspan="2">
                            <input type="text" name="total_piutang" id="total_piutang" class="num autonum" value="<?= $total_piutang ?>" readonly>
                        </td>
                        <td>
                            <input type="text" name="total_piutang_dagang" id="total_piutang_dagang" class="num autonum" value="<?= $total_piutang ?>" readonly>
                        </td>
                        <td>
                            <input type="text" name="total_penerimaan" id="total_penerimaan" class="num autonum" style="color: #008d4c;" value="<?= $total_penerimaan ?>" readonly>
                        </td>
                        <td>
                            <input type="text" name="total_deposit" id="total_deposit" class="num autonum" style="color: #0284c7;" value="<?= $total_deposit ?>" readonly>
                        </td>
                        <td>
                            <input type="text" name="total_lebih_bayar" id="total_lebih_bayar" class="num autonum" style="color: #d97706;" value="<?= $total_lebih_bayar ?>" readonly>
                        </td>
                        <td>
                            <input type="text" name="total_biaya_admin" id="total_biaya_admin" class="num autonum" value="<?= $total_biaya_admin ?>" readonly>
                        </td>
                        <td>
                            <input type="text" name="total_sisa_piutang" id="total_sisa_piutang" class="num autonum" value="<?= $total_sisa_piutang ?>" readonly>
                        </td>
                    </tr>
                    <?php
                    $uangMasukVal = (float)$uang_masuk_val;
                    $kontrolVal = (($total_piutang_dagang - $total_pph23_kontrol - $total_biaya_admin - $total_sisa_piutang) - $uangMasukVal - $total_deposit) + $total_lebih_bayar;
                    ?>
                    <tr class="sum">
                        <td colspan="9" class="r">Uang Masuk Bank</td>
                        <td colspan="5">
                            <input type="text" name="uang_masuk" class="num autonum" id="uang_masuk" value="<?= $uangMasukVal ?>" readonly style="max-width: 200px;">
                        </td>
                    </tr>
                    <tr class="sum">
                        <td colspan="9" class="r">Total Deposit Digunakan</td>
                        <td colspan="5">
                            <input type="text" name="summary_deposit" class="num autonum" id="summary_deposit" value="<?= $total_deposit ?>" readonly style="max-width: 200px; color: #0284c7;">
                        </td>
                    </tr>
                    <tr class="sum">
                        <td colspan="9" class="r">Total Lebih Bayar (Deposit Baru)</td>
                        <td colspan="5">
                            <input type="text" name="summary_lebih_bayar" class="num autonum" id="summary_lebih_bayar" value="<?= $total_lebih_bayar ?>" readonly style="max-width: 200px; color: #d97706;">
                        </td>
                    </tr>
                    <tr class="sum">
                        <td colspan="9" class="r">Kontrol Formula ((Piutang - PPh - Admin - Sisa) - Bank - Dep) + Lebih</td>
                        <td colspan="5" style="display: flex; align-items: center;">
                            <input type="text" name="kontrol" class="num autonum" id="kontrol" value="<?= round($kontrolVal, 2) ?>" readonly style="max-width: 200px; font-weight: bold; color: <?= abs($kontrolVal) < 0.01 ? '#008d4c' : '#dc2626' ?>;">
                            <?php if (abs($kontrolVal) < 0.01) : ?>
                                <span class="state-ok" style="font-size: 11px; margin-left: 8px;">Balanced (0.00)</span>
                            <?php else : ?>
                                <span class="state-bad" style="font-size: 11px; margin-left: 8px;">Selisih: <?= number_format($kontrolVal, 2) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>

    <?php if (!empty($list_jurnal)) : // Tanpa baris di tr_jurnal, seksi jurnal tidak ditampilkan 
    ?>
        <div class="section-t" style="margin-top: 20px;">Jurnal Akuntansi Penerimaan</div>
        <div class="scroll">
            <table style="margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th class="c">Tanggal Jurnal</th>
                        <th class="c">COA</th>
                        <th class="c">Nama Company</th>
                        <th class="c">Nama Account</th>
                        <th>Keterangan</th>
                        <th class="r">Debit</th>
                        <th class="r">Credit</th>
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
                        echo '<td class="r"><b>' . number_format($item['debit'], $decimals) . '</b></td>';
                        echo '<td class="r"><b>' . number_format($item['kredit'], $decimals) . '</b></td>';
                        echo '</tr>';

                        $total_debit += $item['debit'];
                        $total_kredit += $item['kredit'];
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr class="sum">
                        <th colspan="5" class="c">
                            Grand Total
                            <?php if (round($total_debit, 2) === round($total_kredit, 2)): ?>
                                <span class="lbl used" style="margin-left: 10px;">Balanced</span>
                            <?php else: ?>
                                <span class="lbl" style="margin-left: 10px; background: var(--danger);">Unbalanced</span>
                            <?php endif; ?>
                        </th>
                        <td class="r td_total_debit_jurnal" style="font-weight: bold;">
                            <?= number_format($total_debit, 2) ?>
                            <input type="hidden" name="total_debit" value="<?= $total_debit ?>">
                        </td>
                        <td class="r td_total_kredit_jurnal" style="font-weight: bold;">
                            <?= number_format($total_kredit, 2) ?>
                            <input type="hidden" name="total_kredit" value="<?= $total_kredit ?>">
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>

</div>