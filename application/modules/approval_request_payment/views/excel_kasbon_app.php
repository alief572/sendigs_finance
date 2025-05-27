<?php
// fungsi header dengan mengirimkan raw data excel
$nama_tingkat = 'Finance';
if ($tingkat !== '1') {
    $nama_tingkat = 'Direktur';
}
header("Content-type: application/vnd-ms-excel");

// membuat nama file ekspor "export-to-excel.xls"
header("Content-Disposition: attachment; filename=Approval Request Payment Kasbon (" . $nama_tingkat . ").xls");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Excel Kasbon Finance</title>
</head>

<body>
    <table class="table table-bordered" border="1">
        <thead>
            <tr>
                <th align="center">No. Kasbon</th>
                <th align="center">Request By</th>
                <th align="center">Tanggal Pengajuan</th>
                <th align="center">Deskripsi Pengajuan</th>
                <th align="center">Kategori</th>
                <th align="center">Keperluan</th>
                <th align="center">Nilai Pengajuan</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $ttl_kasbon = 0;
            foreach ($data as $item_kasbon) :

                $get_kasbon_header = $this->db->get_where('kons_tr_kasbon_project_header', array('id' => $item_kasbon->no_doc))->row();

                if (!empty($get_kasbon_header)) {
                    $get_spk_penawaran = $this->db->get_where('kons_tr_spk_penawaran', array('id_spk_penawaran' => $get_kasbon_header->id_spk_penawaran))->row();

                    $tipe = '';
                    if (!empty($get_kasbon_header)) {
                        if ($get_kasbon_header->tipe == '1') {
                            $tipe = 'Kasbon Subcont';
                        }
                        if ($get_kasbon_header->tipe == '2') {
                            $tipe = 'Kasbon Akomodasi';
                        }
                        if ($get_kasbon_header->tipe == '3') {
                            $tipe = 'Kasbon Others';
                        }
                    }

                    echo '<tr>';
                    echo '<td>' . $item_kasbon->no_doc . '</td>';
                    echo '<td>' . $item_kasbon->nama . '</td>';
                    echo '<td>' . $item_kasbon->tgl_doc . '</td>';
                    echo '<td>' . $item_kasbon->keperluan . '</td>';
                    echo '<td>' . $tipe . '</td>';
                    echo '<td>' . $get_spk_penawaran->nm_customer . ', ' . $get_kasbon_header->id_spk_penawaran . ', ' . $tipe . '</td>';
                    echo '<td align="right">' . number_format($item_kasbon->jumlah) . '</td>';
                    echo '</tr>';

                    $ttl_kasbon += $item_kasbon->jumlah;
                }
            endforeach;
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" align="right">Grand Total</th>
                <th align="right"><?= number_format($ttl_kasbon) ?></th>
            </tr>
        </tfoot>
    </table>
</body>

</html>