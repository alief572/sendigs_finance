<table id="example1" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-center">#</th>
            <th class="text-center">Code</th>
            <th class="text-center">Material</th>
            <th class="text-center">Warehouse</th>
            <th class="text-center">Stok</th>
            <th class="text-center">Costbook</th>
            <th class="text-center">Value Neraca</th>
            <th class="text-center">Last Update</th>
            <th class="text-center">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        foreach ($results['list_data'] as $item) {

            $nilai_costbook = 0;
            $value_neraca = 0;
            $last_update = '';
            $get_nilai_costbook = $this->db->query("SELECT a.costbook as nilai_costbook, a.created_on, a.value_neraca FROM tr_cost_book a WHERE a.id_material = '" . $item->id_material . "' AND (a.id_gudang_dari = '" . $item->id_gudang . "' OR a.id_gudang_ke = '" . $item->id_gudang . "') ORDER BY a.created_on DESC LIMIT 1")->row();
            if (!empty($get_nilai_costbook)) {
                $nilai_costbook = $get_nilai_costbook->nilai_costbook;
                $value_neraca = $get_nilai_costbook->value_neraca;
                $last_update = $get_nilai_costbook->created_on;
            }

            $terakhir_update = '';
            if($last_update !== '') {
                $terakhir_update = date('d F Y H:i:s', strtotime($last_update));
            }

            
            echo '<tr>';
            echo '<td class="text-center">' . $no . '</td>';
            echo '<td class="text-center">' . $item->kode_produk . '</td>';
            echo '<td class="text-center">' . $item->nm_material . '</td>';
            echo '<td class="text-center">' . strtoupper($item->nm_gudang) . '</td>';
            echo '<td class="text-right">' . number_format($item->qty_stock, 2) . '</td>';
            echo '<td class="text-right">' . number_format($nilai_costbook, 2) . '</td>';
            echo '<td class="text-right">' . number_format($value_neraca, 2) . '</td>';
            echo '<td class="text-center">' . $terakhir_update . '</td>';
            echo '<td class="text-center">
                                <button type="button" class="btn btn-sm btn-info check_costbook" data-id_material="' . $item->id_material . '" data-id_gudang="' . $item->id_gudang . '" title="Check Costbook"><i class="fa fa-eye"></i></button>
                            </td>';
            echo '</tr>';
            

            

            $no++;
        }
        ?>
    </tbody>
</table>