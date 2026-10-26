<?php
defined('BASEPATH') or exit('No direct script access allowed');
$e = function($val) {
    $str = (string)$val;
    if (preg_match('/^[\s]*[=+@-]/u', $str)) {
        $str = "'" . $str;
    }
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
};
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<!--[if gte mso 9]>
<xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>
   <x:ExcelWorksheet>
    <x:Name>Report Penerimaan</x:Name>
    <x:WorksheetOptions>
     <x:DisplayGridlines/>
    </x:WorksheetOptions>
   </x:ExcelWorksheet>
  </x:ExcelWorksheets>
 </x:ExcelWorkbook>
</xml>
<![endif]-->
<style>
    th {
        background-color: #3c8dbc;
        color: #ffffff;
        font-weight: bold;
        text-align: center;
        border: 1px solid #000000;
        padding: 6px 10px;
        font-size: 11pt;
    }
    td {
        border: 1px solid #d4d4d4;
        padding: 5px 8px;
        font-size: 10pt;
        vertical-align: middle;
    }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .num { mso-number-format: "\#\,\#\#0\.00"; text-align: right; }
    .text { mso-number-format: "\@"; }
    .date { mso-number-format: "dd\/mm\/yyyy"; text-align: center; }
    .sum-row { background-color: #f2f2f2; font-weight: bold; }
</style>
</head>
<body>
    <h3>REPORT PENERIMAAN PIUTANG</h3>
    <p>Periode: <?= $e($filter_info) ?> | Diunduh: <?= date('d/m/Y H:i') ?></p>
    <table border="1">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Penerimaan</th>
                <th>Bank</th>
                <th>Detail Penerimaan Bank</th>
                <th>Customer</th>
                <th>Nomor Invoice</th>
                <th>Company</th>
                <th>TAX Code</th>
                <th>PPh 23 Dipotong</th>
                <th>Nominal PPh 23</th>
                <th>Penerimaan Bank</th>
                <th>Piutang Dagang</th>
                <th>Alokasi Penerimaan</th>
                <th>Biaya Admin</th>
                <th>Sisa Piutang</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $tUM = 0;
            $tPen = 0;
            $tPph = 0;
            $tAdmin = 0;
            $tSisa = 0;
            $tPiutang = 0;
            $no = 0;

            foreach ($report_data as $group) :
                $no++;
                $nDetails = count($group['details']);
                $first = true;
                $tUM += (float)$group['nominal_penerimaan_bank'];

                foreach ($group['details'] as $idx => $d) :
                    $tPen += (float)$d['penerimaan'];
                    $tPph += (float)$d['pph23'];
                    $tAdmin += (float)$d['biaya_admin'];
                    $tSisa += (float)$d['sisa_piutang'];
                    $tPiutang += (float)$d['total_akhir_jurnal'];
            ?>
                    <tr>
                        <?php if ($first) : ?>
                            <td class="text-center text" rowspan="<?= $nDetails ?>"><?= $no ?></td>
                            <td class="date" rowspan="<?= $nDetails ?>"><?= $e(!empty($group['tanggal_transaksi']) ? date('d/m/Y', strtotime($group['tanggal_transaksi'])) : date('d/m/Y', strtotime($group['created_date']))) ?></td>
                            <td class="text" rowspan="<?= $nDetails ?>"><?= $e($group['bank_name']) ?></td>
                            <td class="text" rowspan="<?= $nDetails ?>"><?= $e($group['keterangan']) ?></td>
                            <td class="text" rowspan="<?= $nDetails ?>"><?= $e($group['nm_customer']) ?></td>
                        <?php endif; ?>

                        <td class="text"><?= $e($d['no_invoice']) ?></td>
                        <td class="text-center text"><?= $e($d['company_name']) ?></td>
                        <td class="text-center text"><?= $e($d['tax_treatment']) ?></td>
                        <td class="text-center text"><?= ($group['pph23_dipotong'] === 'Y' ? 'Potong' : 'Tidak potong') ?></td>
                        <td class="num"><?= (float)$d['pph23'] ?></td>

                        <?php if ($first) : ?>
                            <td class="num" rowspan="<?= $nDetails ?>"><?= (float)$group['nominal_penerimaan_bank'] ?></td>
                        <?php endif; ?>

                        <td class="num"><?= (float)$d['total_akhir_jurnal'] ?></td>
                        <td class="num"><?= (float)$d['penerimaan'] ?></td>
                        <td class="num"><?= (float)$d['biaya_admin'] ?></td>
                        <td class="num"><?= (float)$d['sisa_piutang'] ?></td>
                    </tr>
            <?php
                    $first = false;
                endforeach;
            endforeach;
            ?>
            <tr class="sum-row">
                <td colspan="9" style="text-align: right;"><b>TOTAL</b></td>
                <td class="num"><b><?= $tPph ?></b></td>
                <td class="num"><b><?= $tUM ?></b></td>
                <td class="num"><b><?= $tPiutang ?></b></td>
                <td class="num"><b><?= $tPen ?></b></td>
                <td class="num"><b><?= $tAdmin ?></b></td>
                <td class="num"><b><?= $tSisa ?></b></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
