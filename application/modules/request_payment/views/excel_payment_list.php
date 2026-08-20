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
                <th style="text-align: center;">No Pengajuan</th>
                <th style="text-align: center;">No Transaksi Payment</th>
                <th style="text-align: center;">Pengajuan Oleh</th>
                <th style="text-align: center;">Tanggal Pengajuan</th>
                <th style="text-align: center;">Keperluan</th>
                <th style="text-align: center;">Tipe</th>
                <th style="text-align: center;">Nilai Pengajuan</th>
                <th style="text-align: center;">Request Payment Oleh</th>
                <th style="text-align: center;">Tanggal Request Payment</th>
                <th style="text-align: center;">Dibayar Oleh</th>
                <th style="text-align: center;">Tanggal Pembayaran</th>
                <th style="text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($data_payment_list as $row) {
                $display_no_doc = $row->no_doc;
                $display_nama   = $row->nama;

                if ($row->tipe == 'kasbon') {
                    if (!empty($row->no_kasbon_consultant)) {
                        $display_no_doc = $row->no_kasbon_consultant;
                    }

                    if (!empty($row->id_pr)) {
                        if ($row->tipe_pr == 'pr departemen') {
                            $pr_creator = $this->db->select('b.nm_lengkap')
                                ->from('rutin_non_planning_header a')
                                ->join('users b', 'b.id_user = a.created_by')
                                ->where('a.no_pr', $row->id_pr)
                                ->get()->row();
                            if (!empty($pr_creator)) $display_nama = $pr_creator->nm_lengkap;
                        } elseif ($row->tipe_pr == 'pr stok') {
                            $pr_creator = $this->db->select('b.nm_lengkap')
                                ->from('material_planning_base_on_produksi a')
                                ->join('users b', 'b.id_user = a.created_by')
                                ->where('a.no_pr', $row->id_pr)
                                ->get()->row();
                            if (!empty($pr_creator)) $display_nama = $pr_creator->nm_lengkap;
                        } elseif ($row->tipe_pr == 'pr asset') {
                            $pr_creator = $this->db->select('b.nm_lengkap')
                                ->from('tran_pr_header a')
                                ->join('users b', 'b.id_user = a.created_by')
                                ->where('a.no_pr', $row->id_pr)
                                ->get()->row();
                            if (!empty($pr_creator)) $display_nama = $pr_creator->nm_lengkap;
                        }
                    }
                }

                $nilai_pengajuan = $row->jumlah;
                if ($row->tipe == 'expense' && !empty($row->id_kasbon) && $row->kurang_bayar > 0) {
                    $nilai_pengajuan = $row->kurang_bayar;
                }

                $no_payment        = !empty($row->pa_id) ? $row->pa_id : (!empty($row->pa_id_payment) ? $row->pa_id_payment : '-');
                $tgl_pengajuan_fmt = !empty($row->pa_tgl_pengajuan) ? date('d F Y', strtotime($row->pa_tgl_pengajuan)) : '-';
                $tgl_bayar_fmt     = !empty($row->pa_tgl_bayar) ? date('d F Y', strtotime($row->pa_tgl_bayar)) : (!empty($row->tgl_pembayaran_paid) ? date('d F Y', strtotime($row->tgl_pembayaran_paid)) : '-');
                $diajukan_oleh     = !empty($row->pa_diajukan_oleh) ? $row->pa_diajukan_oleh : '-';
                $dibayar_oleh      = !empty($row->dibayar_oleh_nama) ? $row->dibayar_oleh_nama : '-';
                $status            = 'Paid';

                echo '<tr>';
                echo '<td style="text-align: center;">' . $no . '</td>';
                echo '<td style="text-align: center;">' . $display_no_doc . '</td>';
                echo '<td style="text-align: center;">' . $no_payment . '</td>';
                echo '<td style="text-align: left;">' . $display_nama . '</td>';
                echo '<td style="text-align: center;">' . (!empty($row->tgl_doc) ? date('d F Y', strtotime($row->tgl_doc)) : '-') . '</td>';
                echo '<td style="text-align: left;">' . $row->keperluan . '</td>';
                echo '<td style="text-align: center;">' . ucfirst($row->tipe) . '</td>';
                echo '<td style="text-align: right;">' . number_format($nilai_pengajuan, 2) . '</td>';
                echo '<td style="text-align: center;">' . $diajukan_oleh . '</td>';
                echo '<td style="text-align: center;">' . $tgl_pengajuan_fmt . '</td>';
                echo '<td style="text-align: center;">' . $dibayar_oleh . '</td>';
                echo '<td style="text-align: center;">' . $tgl_bayar_fmt . '</td>';
                echo '<td style="text-align: center;">' . $status . '</td>';
                echo '</tr>';

                $no++;
            }
            ?>
        </tbody>
    </table>

</body>

</html>