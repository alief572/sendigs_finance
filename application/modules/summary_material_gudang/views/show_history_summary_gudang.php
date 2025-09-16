<table class="table table-bordered table-striped table-sm">
    <thead>
        <tr class='bg-blue'>
            <th class="text-center" width='8%'>#</th>
            <th class="text-center" >NM MATERIAL</th>
            <th class="text-center no-sort" width='20%'>Total IN</th>
            <th class="text-center no-sort" width='20%'>Total OUT</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $nomor = 0;
        if(!empty($result)){
            $GET_IN_MATERIAL = $get_in_material;
            $GET_OUT_MATERIAL = $get_out_material;
            $GET_MATERIAL = get_inventory_lv4();
            foreach($result as $key => $value){
                $IN_MATERIAL    = (!empty($GET_IN_MATERIAL[$value['id_material']]))?number_format($GET_IN_MATERIAL[$value['id_material']],4):'-';
                $OUT_MATERIAL   = (!empty($GET_OUT_MATERIAL[$value['id_material']]))?number_format($GET_OUT_MATERIAL[$value['id_material']],4):'-';
                $NM_MATERIAL   = (!empty($GET_MATERIAL[$value['id_material']]['nama']))?$GET_MATERIAL[$value['id_material']]['nama']:'-';
                if($NM_MATERIAL != '-'){ $nomor++;
                    echo "<tr>";
                        echo "<td align='center'>".$nomor."</td>";
                        echo "<td>".$NM_MATERIAL."</td>";
                        echo "<td class='text-right text-bold text-green' style='padding-right:50px;'><span class='text-green text-bold detail_material' style='cursor:pointer;' data-type='in' data-id_material='".$value['id_material']."'>".$IN_MATERIAL."</span></td>";
                        echo "<td class='text-right text-bold text-red' style='padding-right:50px;'><span class='text-red text-bold detail_material' style='cursor:pointer;' data-type='out' data-id_material='".$value['id_material']."'>".$OUT_MATERIAL."</span></td>";
                    echo "</tr>";
                }
            }
        }
        else{
            echo "<tr>";
                echo "<td colspan='4'>Tidak ada data yang ditampilkan.</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>