<style>
    td {
        padding: 0.5rem;
    }
</style>
<table style="width: 100%;" border="0">
    <tr>
        <th>No Transaksi</th>
        <th>:</th>
        <th><?= $results['data_incoming']->kode_trans; ?></th>
        <th></th>
        <th></th>
        <th></th>
    </tr>
    <tr>
        <th>Nomor PO</th>
        <th>:</th>
        <th><?= $results['no_surat']; ?></th>
        <th>Tanggal Incoming</th>
        <th>:</th>
        <th>01 Juli 2024</th>
    </tr>
    <tr>
        <th>Gudang</th>
        <th>:</th>
        <th><?= $results['data_incoming']->nm_gudang ?></th>
        <th>PIC</th>
        <th>:</th>
        <th><?= $results['data_incoming']->pic ?></th>
    </tr>
    <tr>
        <th>Note</th>
        <th>:</th>
        <th><?= $results['data_incoming']->note ?></th>
        <th></th>
        <th></th>
        <th></th>
    </tr>
</table>

<br>

<table class="table table-bordered">
    <thead class="bg-primary">
        <tr>
            <th class="text-center">#</th>
            <th class="text-center">Stok Name</th>
            <th class="text-center">Qty Pack</th>
            <th class="text-center">Unit Pack</th>
            <th class="text-center">Qty</th>
            <th class="text-center">Unit Measurement</th>
            <th class="text-center">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        foreach ($results['list_incoming_detail'] as $item_detail) {
            $qty_unit = ($item_detail->qty_diterima * $item_detail->konversi);

            echo '<tr>';
            echo '<td class="text-center">' . $no . '</td>';
            echo '<td class="text-left">' . $item_detail->nm_material . '</td>';
            echo '<td class="text-right">' . number_format($item_detail->qty_diterima) . '</td>';
            echo '<td class="text-center">' . strtoupper($item_detail->unit_packing) . '</td>';
            echo '<td class="text-right">' . number_format($qty_unit) . '</td>';
            echo '<td class="text-center">' . strtoupper($item_detail->unit) . '</td>';
            echo '<td class="text-left">' . $item_detail->keterangan . '</td>';
            echo '</tr>';

            $no++;
        }
        ?>
    </tbody>
</table>