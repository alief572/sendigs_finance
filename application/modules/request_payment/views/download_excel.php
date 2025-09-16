<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Request Payment (" . date('d-m-Y') . ").xls");
?>

<table width="100%" border="1">
    <thead>
        <tr>
            <th style="text-align: center">#</th>
            <th style="text-align: center">No. Dokumen</th>
            <th style="text-align: center">Request By</th>
            <th style="text-align: center">Tanggal</th>
            <th style="text-align: center">Keperluan</th>
            <th style="text-align: center">Kategori</th>
            <th style="text-align: center">Nilai Pengajuan</th>
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

            echo '<tr>';

            echo '<td style="text-align: center;">' . $no . '</td>';
            echo '<td style="text-align: center;">' . $item->no_dokumen . '</td>';
            echo '<td style="text-align: center;">' . $nmuser . '</td>';
            echo '<td style="text-align: center;">' . date('d F Y', strtotime($item->tanggal)) . '</td>';
            echo '<td style="text-align: left;">' . $item->keperluan . '</td>';
            echo '<td style="text-align: center;">' . $item->kategori . '</td>';
            echo '<td style="text-align: right;">' . number_format($item->nilai_pengajuan) . '</td>';

            echo '</tr>';
        }
        ?>
    </tbody>
</table>