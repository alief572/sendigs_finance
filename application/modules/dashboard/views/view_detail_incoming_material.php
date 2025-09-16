<style>
    td {
        padding: 0.5rem;
    }
</style>
<table style="width: 100%;" border="0">
    <tr>
        <td width="150">No. PO</td>
        <td class="text-center" width="50">:</td>
        <td>
            <?= $results['no_surat'] ?>
        </td>
    </tr>
    <tr>
        <td width="150">Tanggal Penerimaan</td>
        <td class="text-center" width="50">:</td>
        <td>
            <?= date('d F Y', strtotime($results['tanggal_penerimaan'])) ?>
        </td>
    </tr>
</table>

<br><br>

<table class="table table-bordered">
    <thead class="bg-primary">
        <tr>
            <th class="text-center">No</th>
            <th class="text-center">Code</th>
            <th class="text-center">Product Name</th>
            <th class="text-center">Unit Measurement</th>
            <th class="text-center">Qty</th>
            <th class="text-center">Qty Diterima</th>
            <th class="text-center">Qty Pack</th>
            <th class="text-center">Unit Packing</th>
            <th class="text-center">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        foreach ($results['list_incoming_detail'] as $item_detail) {
            $qty_pack = ($item_detail->qty_diterima / $item_detail->konversi);

            echo '<tr>';
            echo '<td class="text-center">' . $no . '</td>';
            echo '<td class="text-center">' . $item_detail->kode_product . '</td>';
            echo '<td class="text-center">' . $item_detail->nm_material . '</td>';
            echo '<td class="text-center">' . ucfirst($item_detail->unit) . '</td>';
            echo '<td class="text-right">' . number_format($item_detail->qty_order) . '</td>';
            echo '<td class="text-right">' . number_format($item_detail->qty_diterima) . '</td>';
            echo '<td class="text-right">' . number_format($qty_pack) . '</td>';
            echo '<td class="text-center">' . ucfirst($item_detail->unit_packing) . '</td>';
            echo '<td>' . $item_detail->keterangan . '</td>';
            echo '</tr>';

            $no++;
        }
        ?>
    </tbody>
</table>