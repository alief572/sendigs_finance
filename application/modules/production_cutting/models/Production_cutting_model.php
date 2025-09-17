<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Production_cutting_model extends BF_Model{

  public function __construct(){
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Production_Cutting.Add');
		$this->ENABLE_MANAGE  = has_permission('Production_Cutting.Manage');
		$this->ENABLE_VIEW    = has_permission('Production_Cutting.View');
		$this->ENABLE_DELETE  = has_permission('Production_Cutting.Delete');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
  }

  public function get_data($table,$where_field='',$where_value=''){
    if($where_field !='' && $where_value!=''){
      $query = $this->db->get_where($table, array($where_field=>$where_value));
    }else{
      $query = $this->db->get($table);
    }

    return $query->result();
  }

  public function get_data_group($table,$where_field='',$where_value='',$where_group=''){
    if($where_field !='' && $where_value!=''){
      $query = $this->db->group_by($where_group)->get_where($table, array($where_field=>$where_value));
    }else{
      $query = $this->db->get($table);
    }

    return $query->result();
  }

  public function data_side_request_produksi(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_request_produksi(
      $requestData['product'],
      $requestData['costcenter'],
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData			= $fetch['totalData'];
    $totalFiltered	= $fetch['totalFiltered'];
    $query					= $fetch['query'];

    $data	= array();
    $urut1  = 1;
    $urut2  = 0;
    $GET_USER = get_list_user();
    foreach($query->result_array() as $row){
      $total_data     = $totalData;
      $start_dari     = $requestData['start'];
      $asc_desc       = $requestData['order'][0]['dir'];
      if($asc_desc == 'asc'){
      $nomor = ($total_data - $start_dari) - $urut2;
      }
      if($asc_desc == 'desc'){
      $nomor = $urut1 + $start_dari;
      }

      $cycletime = $this->db->select('*')->get_where('so_spk_cutting_plan',array('kode_hub'=>$row['kode_hub']))->result_array();
      $QTY_BELUM = 0;
      foreach ($cycletime as $key => $value) { 
        $checkQtySelesai = $this->db->get_where('so_spk_cutting_product',array('id_key_spk'=>$value['id'],'kode_det'=>$row['id_outgoing']))->result_array();
        $qty_selesai = COUNT($checkQtySelesai);
        $qty_sisa = $value['qty'] - $qty_selesai;
        $QTY_BELUM += $qty_sisa;
      }

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nm_product'])."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['so_number'])."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['no_spk'])."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y',strtotime($row['tanggal']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nm_costcenter'])."</div>";
      $nestedData[]	= "<div align='center'>".number_format($row['qty'])."</div>";
      $username = (!empty($GET_USER[$row['created_by']]['username']))?$GET_USER[$row['created_by']]['username']:'-';
      $nestedData[]	= "<div align='left'>".strtolower($username)."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y H:i',strtotime($row['created_date']))."</div>";

      $status = 'Waiting';
      $warna = 'blue';
      if($QTY_BELUM == 0){
        $status = 'Close';
        $warna = 'red';
      }

      $nestedData[]	= "<div align='left'><span class='badge bg-".$warna."'>".$status."</span></div>";

      $edit	= "";
      if($this->ENABLE_MANAGE AND $QTY_BELUM > 0){
        $edit	= "<a href='".site_url($this->uri->segment(1)).'/add/'.$row['id_outgoing']."' class='btn btn-sm btn-primary' title='Confirm' data-role='qtip'><i class='fa fa-check'></i></a>";
      }
      $nestedData[]	= "<div align='center'>".$edit."</div>";
      $data[] = $nestedData;
      $urut1++;
      $urut2++;
    }

    $json_data = array(
      "draw"            	=> intval( $requestData['draw'] ),
      "recordsTotal"    	=> intval( $totalData ),
      "recordsFiltered" 	=> intval( $totalFiltered ),
      "data"            	=> $data
    );

    echo json_encode($json_data);
  }

  public function get_query_request_produksi($product, $costcenter, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $costcenter_where = "";

    $product_where = "";
    if($product != '0'){
    $product_where = " AND b.code_lv1 = '".$product."'";
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              x.id AS id_outgoing,
              y.so_number,
              y.kode_hub,
              b.nama AS nm_product,
              z.nama_costcenter AS nm_costcenter
            FROM
                so_spk_cutting_request_outgoing x
                LEFT JOIN so_spk_cutting_request a ON x.id_spk = a.id
                LEFT JOIN so_spk_cutting y ON a.id_so = y.id
                LEFT JOIN new_inventory_4 b ON y.code_lv4=b.code_lv4
                LEFT JOIN ms_costcenter z ON a.id_costcenter=z.id_costcenter,
                (SELECT @row:=0) r
            WHERE 1=1 ".$costcenter_where." ".$product_where." AND a.outgoing_date IS NOT NULL AND (
              b.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.no_spk LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR y.so_number LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR z.nama_costcenter LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'b.nama',
      2 => 'y.so_number',
      3 => 'no_spk',
      4 => 'tanggal',
      5 => 'z.nama_costcenter',
      6 => 'qty'
    );

    $sql .= " ORDER BY x.id desc,  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }
}