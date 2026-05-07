<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Request Payment (" . date('d-m-Y') . ").xls");

$bulan_indonesia = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];
?>

<style>
    table {
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #000000;
        padding: 5px;
    }

    th {
        background-color: #D9E1F2;
        font-weight: bold;
    }
</style>

<table width="100%" border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th style="text-align: center; border: 1px solid #000;">#</th>
            <th style="text-align: center; border: 1px solid #000;">No. Dokumen</th>
            <th style="text-align: center; border: 1px solid #000;">Request By</th>
            <th style="text-align: center; border: 1px solid #000;">Tanggal</th>
            <th style="text-align: center; border: 1px solid #000;">Keperluan</th>
            <th style="text-align: center; border: 1px solid #000;">Kategori</th>
            <th style="text-align: center; border: 1px solid #000;">Nilai Pengajuan</th>
            <th style="text-align: center; border: 1px solid #000;">Tanggal di Approve</th>
            <th style="text-align: center; border: 1px solid #000;">Bulan di Approve</th>
            <th style="text-align: center; border: 1px solid #000;">Tanggal Dibayar</th>
            <th style="text-align: center; border: 1px solid #000;">Bulan Dibayar</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 0;
        foreach ($list_all_request_payment as $item) {
            $no++;

            $nmuser = $item->request_by;
            if ($item->kategori == 'Kasbon') {
                $get_kasbon = $this->db->get_where('tr_kasbon', array('no_doc' => $item->no_dokumen))->row();
                $check_detail = $this->db->get_where('tr_pr_detail_kasbon', ['id_kasbon' => $item->no_dokumen])->result();
                if (count($check_detail)) {
                    if ($get_kasbon->tipe_pr == 'pr departemen') {
                        $this->db->select('b.nm_lengkap');
                        $this->db->from('rutin_non_planning_header a');
                        $this->db->join('users b', 'b.id_user = a.created_by');
                        $this->db->where('a.no_pr', $get_kasbon->id_pr);
                        $get_single_detail = $this->db->get()->row();

                        $nmuser = $get_single_detail->nm_lengkap;
                    }

                    if ($get_kasbon->tipe_pr == 'pr stok') {
                        $this->db->select('b.nm_lengkap');
                        $this->db->from('material_planning_base_on_produksi a');
                        $this->db->join('users b', 'b.id_user = a.created_by');
                        $this->db->where('a.no_pr', $get_kasbon->id_pr);
                        $get_single_detail = $this->db->get()->row();

                        $nmuser = $get_single_detail->nm_lengkap;
                    }
                }
            }

            // Lookup payment_approve data for this document
            $tgl_approve = '';
            $bulan_approve = '';
            $tgl_dibayar = '';
            $bulan_dibayar = '';

            if (isset($payment_approve_lookup[$item->no_dokumen])) {
                $pa = $payment_approve_lookup[$item->no_dokumen];

                // Format Tanggal di Approve & Bulan di Approve
                if (!empty($pa['approved_on']) && strtotime($pa['approved_on']) !== false) {
                    $ts_approve = strtotime($pa['approved_on']);
                    $day_approve = date('d', $ts_approve);
                    $month_approve = (int) date('m', $ts_approve);
                    $year_approve = date('Y', $ts_approve);
                    $tgl_approve = $day_approve . ' ' . $bulan_indonesia[$month_approve] . ' ' . $year_approve;
                    $bulan_approve = $bulan_indonesia[$month_approve];
                }

                // Format Tanggal Dibayar & Bulan Dibayar
                if (!empty($pa['tgl_bayar']) && strtotime($pa['tgl_bayar']) !== false) {
                    $ts_bayar = strtotime($pa['tgl_bayar']);
                    $day_bayar = date('d', $ts_bayar);
                    $month_bayar = (int) date('m', $ts_bayar);
                    $year_bayar = date('Y', $ts_bayar);
                    $tgl_dibayar = $day_bayar . ' ' . $bulan_indonesia[$month_bayar] . ' ' . $year_bayar;
                    $bulan_dibayar = $bulan_indonesia[$month_bayar];
                }
            }

            echo '<tr>';

            echo '<td style="text-align: center; border: 1px solid #000;">' . $no . '</td>';
            echo '<td style="text-align: center; border: 1px solid #000;">' . $item->no_dokumen . '</td>';
            echo '<td style="text-align: center; border: 1px solid #000;">' . $nmuser . '</td>';
            echo '<td style="text-align: center; border: 1px solid #000;">' . date('d F Y', strtotime($item->tanggal)) . '</td>';
            echo '<td style="text-align: left; border: 1px solid #000;">' . $item->keperluan . '</td>';
            echo '<td style="text-align: center; border: 1px solid #000;">' . $item->kategori . '</td>';
            echo '<td style="text-align: right; border: 1px solid #000;">' . number_format($item->nilai_pengajuan) . '</td>';
            echo '<td style="text-align: center; border: 1px solid #000;">' . $tgl_approve . '</td>';
            echo '<td style="text-align: center; border: 1px solid #000;">' . $bulan_approve . '</td>';
            echo '<td style="text-align: center; border: 1px solid #000;">' . $tgl_dibayar . '</td>';
            echo '<td style="text-align: center; border: 1px solid #000;">' . $bulan_dibayar . '</td>';

            echo '</tr>';
        }
        ?>
    </tbody>
</table>