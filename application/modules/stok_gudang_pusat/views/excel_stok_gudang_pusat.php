<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Stock Gudang Pusat.xls");
?>
<div style="width: 100%; text-align: center;">
    <h2>Stock Gudang Pusat - <?= date('d F Y', strtotime($results['tanggal'])) ?></h2>
</div>
<table width="100%" border="1">
    <thead>
        <tr>
            <th class="text-center">#</th>
            <th class="text-center">ID PROGRAM</th>
            <th class="text-center">CODE</th>
            <th class="text-center">NM BARANG</th>
            <th class="text-center">UNIT PACKING</th>
            <th class="text-center">CONVERTION</th>
            <th class="text-center">STOK</th>
            <th class="text-center">UNIT</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        foreach ($results['list_material'] as $item_material) {
            $unit_packing = '';
            if (!empty($results['list_packing'][$item_material['id_unit_packing']])) {
                $unit_packing = $results['list_packing'][$item_material['id_unit_packing']];
            }
            $unit = '';
            if (!empty($results['list_unit'][$item_material['id_unit']])) {
                $unit = $results['list_unit'][$item_material['id_unit']];
            }

            $stok = 0;
            if (!empty($results['list_stok'][$item_material['code_lv4']])) {
                $stok = $results['list_stok'][$item_material['code_lv4']];
            }

            $konversi = $item_material['konversi'];
            if($konversi <= 0) {
                $konversi = 1;
            }

            echo '<tr>';
            echo '<td class="text-center">' . $no . '</td>';
            echo '<td>' . $item_material['code_lv4'] . '</td>';
            echo '<td>' . $item_material['code'] . '</td>';
            echo '<td>' . $item_material['nama'] . '</td>';
            echo '<td align="center">' . strtoupper($unit_packing) . '</td>';
            echo '<td align="right">' . number_format($item_material['konversi'], 2) . '</td>';
            echo '<td align="right">' . number_format($stok / $konversi, 2) . '</td>';
            echo '<td align="center">' . strtoupper($unit) . '</td>';
            echo '</tr>';

            $no++;
        }
        ?>
    </tbody>
</table>