<table id="example1" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center">Transaksi</th>
            <th class="text-center">No. Transaksi</th>
            <th class="text-center">Tgl Transaksi</th>
            <th class="text-center">Jenis Transaksi</th>
            <th class="text-center">Dari Gudang</th>
            <th class="text-center">Ke Gudang</th>
            <th class="text-center">Value In</th>
            <th class="text-center">Value Out</th>
            <th class="text-center">Saldo</th>
        </tr>
    </thead>
    <tbody class="list_data">
        <?php 
            $saldo_awal = 0;
            foreach($results['list_saldo_awal'] as $item_saldo_awal) {
                $saldo_awal += $item_saldo_awal->value_neraca;
            }

            echo '<tr>'; 
            echo '<td class="text-right" colspan="6">Saldo Awal -></td>';
            echo '<td class="text-right" colspan="3">'.number_format($saldo_awal, 2).'</td>';
            echo '</tr>';

            $ttl_in = 0;
            $ttl_out = 0;
            
            foreach($results['list_data'] as $item) {

                $value_in = 0;
                $value_out = 0;

                if($item->tipe_transaksi == 'In') {
                    $value_in = $item->value_transaksi;
                }else{
                    $value_out = $item->value_transaksi;
                }

                echo '<tr>'; 
                echo '<td class="text-center">'.$item->jenis_transaksi.'</td>';
                echo '<td class="text-center">'.$item->no_transaksi.'</td>';
                echo '<td class="text-center">'.date('d F Y', strtotime($item->tgl)).'</td>';
                echo '<td class="text-center">'.$item->tipe_transaksi.'</td>';
                echo '<td class="text-center">'.$item->nm_gudang_dari.'</td>';
                echo '<td class="text-center">'.$item->nm_gudang_ke.'</td>';
                echo '<td class="text-right">'.number_format($value_in, 2).'</td>';
                echo '<td class="text-right">'.number_format($value_out, 2).'</td>';
                echo '<td class="text-right">'.number_format(($saldo_awal + $value_in - $value_out), 2).'</td>';
                echo '</tr>';

                $ttl_in += $value_in;
                $ttl_out += $value_out;

                $saldo_awal += $value_in;
                $saldo_awal -= $value_out;
            }

            echo '<tr>'; 
            echo '<td class="text-right" colspan="6">Saldo Akhir -></td>';
            echo '<td class="text-right">'.number_format($ttl_in, 2).'</td>';
            echo '<td class="text-right">'.number_format($ttl_out, 2).'</td>';
            echo '<td class="text-right">'.number_format($saldo_awal, 2).'</td>';
            echo '</tr>';
        ?>
    </tbody>
</table>