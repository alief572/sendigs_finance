<table style="width: 150px;">
    <tr>
        <td><span style="font-weight: bold;">No. PR</span></td>
        <td class="text-left"><b>:</b></td>
        <td class="text-center"><b><?= $no_pr ?></b></td>
    </tr>
</table>
<div class="col-12">
    <table class="table table-bordered" border="0">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th class="text-center">Material</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Qty Packing</th>
                <th class="text-center">Unit</th>
                <th class="text-center">Unit Packing</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($list_barang as $item) {
                $konversi = ($item->nilai_konversi <= 0) ? 1 : $item->nilai_konversi;
                $qty = $item->qty;
                $qty_packing = ($item->qty / $konversi);
                if ($item->kategori_pr == 'PR Stok') {
                    $qty_packing = $qty_packing;
                }

                echo '<tr>';
                echo '<td class="text-center">' . $no . '</td>';
                echo '<td class="text-center">' . $item->nm_barang . '</td>';
                echo '<td class="text-right">' . number_format($qty, 2) . '</td>';
                echo '<td class="text-right">' . number_format($qty_packing, 2) . '</td>';
                echo '<td class="text-center">' . ucfirst($item->unit) . '</td>';
                echo '<td class="text-center">' . ucfirst($item->unit_packing) . '</td>';
                echo '</tr>';
                $no++;
            }
            ?>
        </tbody>
    </table>
</div>