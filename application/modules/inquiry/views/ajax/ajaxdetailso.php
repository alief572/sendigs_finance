<?php   
    $requestData= $_REQUEST;
    $columns = array('no_so','id_barang','nm_barang','satuan','jenis','qty_order','qty_supply','ukuran','harga','diskon','no_so');
    $endfield = array_pop($columns);
    $query = "SELECT ".join(',',$columns)." FROM trans_so_detail WHERE 1=1 ";
    
    $query .= " ORDER BY no_so ASC ";
    $newtott = $this->db->query($query);
    $totalData = $newtott->num_rows();
    $daplun = $this->db->query($query);
    $jafuk  = $daplun->result();
    $data = array();
    $dd = 1+$requestData['start'];
    foreach($jafuk as $vs){
        $cuk = $dd++;
        $btnaction = '';
        $getArraycuk = array();
        $getArraycuk[] = '<center>'.$cuk.'</center>';
        $getArraycuk[] = '<center>'.$vs->no_so.'</center>';
        $getArraycuk[] = '<center>'.$vs->id_barang.'/'.$vs->nm_barang.'</center>';
        $getArraycuk[] = '<center>'.$vs->satuan.'</center>';
        $getArraycuk[] = '<center>'.$vs->qty_supply.'</center>';
        $getArraycuk[] = '<center>'.$vs->qty_order.'</center>';
        $getArraycuk[] = '<center>'.$vs->harga.'</center>';
        $getArraycuk[] = '<center>'.$vs->diskon.'</center>';
        $getArraycuk[] = '<center>'.$vs->qty_order*$vs->harga.'</center>';
        $getArraycuk[] = '<center><div class="btn-group">'.$btnaction.'</div></center>';
        $getArraycuk[] = $vs->qty_order*$vs->harga;
        $data[] = $getArraycuk;
    }
    $json_data = array(
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalData),
            "data"            => $data
            );
    echo json_encode($json_data);
 ?>