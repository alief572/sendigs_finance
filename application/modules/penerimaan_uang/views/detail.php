<?php
$total_debit = (!empty($total_debit)) ? $total_debit : 0;
$total_kredit = (!empty($total_kredit)) ? $total_kredit : 0;
?>
<table class="table table-striped">
    <thead>
        <tr>
            <th class="text-center">Tgl invoice</th>
            <th class="text-center">Nomor Invoice</th>
            <th class="text-center">DPP</th>
            <th class="text-center">DPP Lain</th>
            <th class="text-center">PPN</th>
            <th class="text-center">PPH 23</th>
            <th class="text-center">Tagihan + Ppn</th>
            <th class="text-center">Tagihan + Ppn - Pph</th>
            <th class="text-center">Piutang Dagang</th>
            <th class="text-center">Penerimaan</th>
            <th class="text-center">Biaya Admin</th>
            <th class="text-center">Sisa Piutang</th>
        </tr>
    </thead>
    <tbody>
        <?php

        $total_piutang = 0;
        $total_piutang_dagang = 0;
        $total_penerimaan = 0;
        $total_biaya_admin = 0;
        $total_sisa_piutang = 0;

        foreach ($detail as $item) :
            echo '<tr>';

            echo '<td class="text-center">' . date('d-F-Y', strtotime($item['tgl_inv'])) . '</td>';
            echo '<td class="text-center">' . $item['no_invoice'] . '</td>';
            echo '<td class="text-right">' . number_format($item['dpp']) . '</td>';
            echo '<td class="text-right">' . number_format($item['dpp_lain']) . '</td>';
            echo '<td class="text-right">' . number_format($item['ppn']) . '</td>';
            echo '<td class="text-right">' . number_format($item['pph23']) . '</td>';
            echo '<td class="text-right">' . number_format($item['tagihan_ppn']) . '</td>';
            echo '<td class="text-right">' . number_format($item['total_akhir_jurnal']) . '</td>';
            echo '<td class="text-right">' . number_format($item['total']) . '</td>';
            echo '<td class="text-right">' . number_format($item['penerimaan']) . '</td>';
            echo '<td class="text-right">' . number_format($item['biaya_admin']) . '</td>';
            echo '<td class="text-right">' . number_format($item['sisa_piutang']) . '</td>';
            echo '</tr>';

            $total_piutang += $item['total_akhir_jurnal'];
            $total_piutang_dagang += $item['total_akhir_jurnal'];
            $total_penerimaan += $item['penerimaan'];
            $total_biaya_admin += $item['biaya_admin'];
            $total_sisa_piutang += $item['sisa_piutang'];
        endforeach;
        ?>
    </tbody>
    <tbody>
        <tr>
            <td colspan="6" class="text-right">Total</td>
            <td colspan="2">
                <input type="text" name="total_piutang" id="total_piutang" class="form-control form-control-sm autonum text-right" value="<?= $total_piutang ?>" readonly>
            </td>
            <td>
                <input type="text" name="total_piutang_dagang" id="total_piutang_dagang" class="form-control form-control-sm autonum text-right" value="<?= $total_piutang ?>" readonly>
            </td>
            <td>
                <input type="text" name="total_penerimaan" id="total_penerimaan" class="form-control form-control-sm autonum text-right" value="<?= $total_penerimaan ?>" readonly>
            </td>
            <td>
                <input type="text" name="total_biaya_admin" id="total_biaya_admin" class="form-control form-control-sm autonum text-right" value="<?= $total_biaya_admin ?>" readonly>
            </td>
            <td>
                <input type="text" name="total_sisa_piutang" id="total_sisa_piutang" class="form-control form-control-sm autonum text-right" value="<?= $total_sisa_piutang ?>" readonly>
            </td>
        </tr>
        <tr>
            <td colspan="8" class="text-right">Total Alokasi Penerimaan</td>
            <td>
                <input type="text" name="grand_total" class="form-control form-control-sm autonum text-right" id="grand_total" value="<?= $total_penerimaan ?>" readonly>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="8" class="text-right">Uang Masuk</td>
            <td>
                <input type="text" name="uang_masuk" class="form-control form-control-sm autonum text-right" id="uang_masuk" value="<?= $alokasi['nilai_terpakai'] ?>" readonly>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="8" class="text-right">Kontrol</td>
            <td>
                <input type="text" name="kontrol" class="form-control form-control-sm autonum text-right" id="kontrol" value="<?= (0) ?>" readonly>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>

<br><br>

<table class="table table-striped">
    <thead>
        <tr>
            <th class="text-center">Tanggal Jurnal</th>
            <th class="text-center">COA</th>
            <th class="text-center">Nama Company</th>
            <th class="text-center">Nama Account</th>
            <th class="text-center">Debit</th>
            <th class="text-center">Credit</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total_debit = 0;
        $total_kredit = 0;

        foreach ($list_jurnal as $item) {
            echo '<tr>';

            echo '<td class="text-center">' . date('d F Y', strtotime($item['tgl_jurnal'])) . '</td>';
            echo '<td class="text-center"' . $item['coa'] . '></td>';
            echo '<td class="text-center">' . $item['nm_company'] . '</td>';
            echo '<td class="text-center">' . $item['nm_coa'] . '</td>';
            echo '<td class="text-right"><b>' . number_format($item['debit']) . '</b></td>';
            echo '<td class="text-right"><b>' . number_format($item['kredit']) . '</b></td>';

            echo '</tr>';

            $total_debit += $item['debit'];
            $total_kredit += $item['kredit'];
        }
        ?>
    </tbody>
    <tbody>
        <tr>
            <th colspan="4" class="text-center">Grand Total</th>
            <td class="text-right td_total_debit_jurnal">
                <b><?= number_format($total_debit) ?></b>
                <input type="hidden" name="total_debit" value="<?= $total_debit ?>">
            </td>
            <td class="text-right td_total_kredit_jurnal">
                <b><?= number_format($total_kredit) ?></b>
                <input type="hidden" name="total_kredit" value="<?= $total_kredit ?>">
            </td>
        </tr>
    </tbody>
</table>