<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Payment_List_" . date('YmdHis') . ".xls");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment List</title>
</head>

<body>

    <table border="1" width="100%">
        <thead>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <th style="text-align: center;">#</th>
                <th style="text-align: center;">No Dokumen</th>
                <th style="text-align: center;">No Transaksi Payment</th>
                <th style="text-align: center;">Request By</th>
                <th style="text-align: center;">Tanggal Dokumen</th>
                <th style="text-align: center;">Keperluan</th>
                <th style="text-align: center;">Tipe</th>
                <th style="text-align: center;">Nilai Pengajuan</th>
                <th style="text-align: center;">Diajukan Oleh</th>
                <th style="text-align: center;">Tanggal Pengajuan</th>
                <th style="text-align: center;">Dibayar Oleh</th>
                <th style="text-align: center;">Tanggal Pembayaran</th>
                <th style="text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($data_payment_list as $item) {
                $tgl_pengajuan = (isset($list_tgl_pengajuan_pembayaran[$item->no_doc])) ? $list_tgl_pengajuan_pembayaran[$item->no_doc]['tgl_pengajuan'] : '-';
                $tgl_pembayaran = (isset($list_tgl_pengajuan_pembayaran[$item->no_doc])) ? $list_tgl_pengajuan_pembayaran[$item->no_doc]['tgl_pembayaran'] : '-';
                $diajukan_oleh = (isset($list_tgl_pengajuan_pembayaran[$item->no_doc])) ? $list_tgl_pengajuan_pembayaran[$item->no_doc]['diajukan_oleh'] : '-';
                $dibayar_oleh = (isset($list_tgl_pengajuan_pembayaran[$item->no_doc])) ? $list_tgl_pengajuan_pembayaran[$item->no_doc]['dibayar_oleh'] : '-';
                $no_payment = (isset($list_tgl_pengajuan_pembayaran[$item->no_doc])) ? $list_tgl_pengajuan_pembayaran[$item->no_doc]['no_payment'] : '-';

                $get_payment = $this->db->get_where('payment_approve', ['no_doc' => $item->no_doc, 'tgl_bayar <>' => null])->row();
                $status = (!empty($get_payment)) ? 'Paid' : 'Open';

                $nilai_pengajuan = $item->jumlah;
                if ($item->tipe == 'expense' && !empty($item->id_kasbon) && $item->kurang_bayar > 0) {
                    $nilai_pengajuan = $item->kurang_bayar;
                }

                echo '<tr>';
                echo '<td style="text-align: center;">' . $no . '</td>';
                echo '<td style="text-align: center;">' . $item->no_doc . '</td>';
                echo '<td style="text-align: center;">' . $no_payment . '</td>';
                echo '<td style="text-align: left;">' . $item->nama . '</td>';
                echo '<td style="text-align: center;">' . (!empty($item->tgl_doc) ? date('d F Y', strtotime($item->tgl_doc)) : '-') . '</td>';
                echo '<td style="text-align: left;">' . $item->keperluan . '</td>';
                echo '<td style="text-align: center;">' . ucfirst($item->tipe) . '</td>';
                echo '<td style="text-align: right;">' . number_format($nilai_pengajuan, 2) . '</td>';
                echo '<td style="text-align: center;">' . $diajukan_oleh . '</td>';
                echo '<td style="text-align: center;">' . $tgl_pengajuan . '</td>';
                echo '<td style="text-align: center;">' . $dibayar_oleh . '</td>';
                echo '<td style="text-align: center;">' . $tgl_pembayaran . '</td>';
                echo '<td style="text-align: center;">' . $status . '</td>';
                echo '</tr>';

                $no++;
            }
            ?>
        </tbody>
    </table>

</body>

</html>