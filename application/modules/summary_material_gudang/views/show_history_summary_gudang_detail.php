<table class="table table-bordered table-striped table-sm">
    <thead>
        <tr class='bg-blue'>
            <th class="text-center" width='5%'>#</th>
            <th class="text-center" width='16%'>Kode Transaksi</th>
            <th class="text-center no-sort" width='20%'>Material Name</th>
            <th class="text-center no-sort" width='8%'>QTY</th>
            <th class="text-center no-sort">Keterangan</th>
            <th class="text-center no-sort" width='10%'>By</th>
            <th class="text-center no-sort" width='15%'>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php
         $GET_MATERIAL = get_inventory_lv4();
         $GET_USER = get_list_user();
        if(!empty($get_in_trans[$material])){
            $nomor = 0;
            $SUM = 0;
            foreach($get_in_trans[$material] as $key => $value){ 
                if($value['jumlah_material'] > 0){

                    $NM_MATERIAL   = (!empty($GET_MATERIAL[$value['id_material']]['nama']))?$GET_MATERIAL[$value['id_material']]['nama']:'-';
                    $NM_USER   = (!empty($GET_USER[$value['update_by']]['nama']))?$GET_USER[$value['update_by']]['nama']:'-';

                    if($NM_MATERIAL != '-'){ $nomor++;
                        $SUM += $value['jumlah_material'];
                        echo "<tr>";
                            echo "<td align='center'>".$nomor."</td>";
                            echo "<td>".strtoupper($value['kode_trans'])."</td>";
                            echo "<td>".$NM_MATERIAL."</td>";
                            echo "<td class='text-right text-bold text-green'>".number_format($value['jumlah_material'],4)."</td>";
                            echo "<td>".strtoupper($value['ket'])."</td>";
                            echo "<td align='center'>".strtoupper($NM_USER)."</td>";
                            echo "<td align='center'>".date('d-M-Y H:i:s', strtotime($value['update_date']))."</td>";
                        echo "</tr>";
                    }
                }
            }
            echo "<tr>";
                echo "<th class='text-center' colspan='3'>TOTAL MATERIAL</th>";
                echo "<th class='text-right text-bold text-green'>".number_format($SUM,4)."</th>";
            echo "</tr>";
        }
        else{
            echo "<tr>";
                echo "<td colspan='4'>Tidak ada data yang ditampilkan.</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
<script>
    swal.close();
</script>