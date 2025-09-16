<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Production_assembly_model extends BF_Model{

  public function __construct(){
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Production_Assembly.Add');
		$this->ENABLE_MANAGE  = has_permission('Production_Assembly.Manage');
		$this->ENABLE_VIEW    = has_permission('Production_Assembly.View');
		$this->ENABLE_DELETE  = has_permission('Production_Assembly.Delete');

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

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='left'>".get_name_product_by_bom($row['no_bom'])[$row['no_bom']]."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['so_number'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nm_customer'])."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y',strtotime($row['due_date']))."</div>";
      $nestedData[]	= "<div align='center'>".number_format($row['propose'])."</div>";
      $username = (!empty($GET_USER[$row['created_by']]['username']))?$GET_USER[$row['created_by']]['username']:'-';
      $nestedData[]	= "<div align='left'>".strtolower($username)."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y H:i',strtotime($row['created_date']))."</div>";

      $status = 'Waiting';
      $warna = 'blue';
      if($row['sts_close_material'] == 'Y'){
        $status = 'Close';
        $warna = 'green';
      }

      $nestedData[]	= "<div align='left'><span class='badge bg-".$warna."'>".$status."</span></div>";

      $edit	= "";
      if($this->ENABLE_MANAGE AND $row['sts_close_material'] == 'N'){
        $edit	= "<a href='".site_url($this->uri->segment(1)).'/add/'.$row['id']."' class='btn btn-sm btn-primary' title='Confirm' data-role='qtip'><i class='fa fa-check'></i></a>";
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
              x.*,
              b.nama AS nm_product,
              c.nm_customer
            FROM
                so_spk_assembly x
                LEFT JOIN new_inventory_4 b ON x.code_lv4=b.code_lv4
                LEFT JOIN customer c ON x.id_customer=c.id_customer,
                (SELECT @row:=0) r
            WHERE 1=1 ".$costcenter_where." ".$product_where." AND x.no_bom IS NOT NULL AND (
              b.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR x.so_number LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR c.nm_customer LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'b.nama',
      2 => 'so_number'
    );

    $sql .= " ORDER BY x.id desc,  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }
}