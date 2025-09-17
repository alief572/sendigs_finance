<table class="table table-bordered table-striped table-sm">
    <thead>
        <tr class='bg-blue'>
            <th class="text-center" width='3%'>#</th>
            <th class="text-center" width='10%'>No.SO</th>
            <th class="text-center" width='10%'>No.SPK</th>
            <th class="text-center no-sort">Product Name</th>
            <th class="text-center no-sort" width='8%'>QTY</th>
            <th class="text-center no-sort" width='10%'>By</th>
            <th class="text-center no-sort" width='15%'>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if(!empty($get_in_trans[$code_lv4])){
            $nomor = 0;
            $SUM = 0;
            foreach($get_in_trans[$code_lv4] as $key => $value){ 
                if($value['qty'] > 0){
                $nomor++;

                $nm_user = (!empty($GET_USER[$value['created_by']]['nama']))?$GET_USER[$value['created_by']]['nama']:'';

                $SUM += $value['qty'];
                 echo "<tr>";
                    echo "<td align='center'>".$nomor."</td>";
                    echo "<td align='center'>".strtoupper($value['so_number'])."</td>";
                    echo "<td align='center'>".strtoupper($value['no_spk'])."</td>";
                    echo "<td>".strtoupper($value['nama_product'])."</td>";
                    echo "<td class='text-center text-bold text-green'>".number_format($value['qty'])."</td>";
                    echo "<td align='center'>".$nm_user."</td>";
                    echo "<td align='center'>".date('d-M-Y H:i:s', strtotime($value['created_date']))."</td>";
                echo "</tr>";
                }
            }
            echo "<tr>";
                echo "<th class='text-center' colspan='4'>TOTAL QTY</th>";
                echo "<th class='text-center text-bold text-green'>".number_format($SUM)."</th>";
                echo "<th class='text-center' colspan='2'></th>";
            echo "</tr>";
        }
        else{
            echo "<tr>";
                echo "<td colspan='7'>Tidak ada data yang ditampilkan.</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
<script>
    swal.close();
</script>