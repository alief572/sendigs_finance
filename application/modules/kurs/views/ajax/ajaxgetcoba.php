<?php 
  $requestData= $_REQUEST;
    $columns = array('id','coba','id');
  
  $endfield = array_pop($columns);
  $query = "SELECT ".join(',',$columns)." FROM tb_coba_ss WHERE 1=1 ";

  if(!empty($requestData['search']['value'])){
    $query .=" AND coba like '%".$requestData['search']['value']."%'";
  }

  $newtott = $this->db->query($query);
  $totalData = $newtott->num_rows();
  $query .=" ORDER BY ". $columns[$requestData['order'][0]['column']]."  ".$requestData['order'][0]['dir']."   LIMIT ".$requestData['start']." ,".$requestData['length']."   "; 
  $daplun = $this->db->query($query);
  $jafuk  = $daplun->result(); // OK

  $data = array();

  $dd = 1+$requestData['start'];
  foreach($jafuk as $vs){
    $cuk = $dd++;
    $getArraycuk = array();
    $getArraycuk[] = '<center>'.$cuk.'</center>';
    $getArraycuk[] = $vs->coba;
    $getArraycuk[] = '<center><div class="btn-group">
              <button class="btn btn-sm btn-info" onclick="edit(\''.$vs->id.'\')">Coba</button>
              </div></center>';
    $data[] = $getArraycuk;
  }
  //OK

  $json_data = array(
            "draw"            => intval($requestData['draw']),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalData),
            "data"            => $data
            );
 
  echo json_encode($json_data);
 ?>