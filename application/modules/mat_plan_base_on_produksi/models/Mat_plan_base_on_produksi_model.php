<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Mat_plan_base_on_produksi_model extends BF_Model{

  public function __construct(){
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Material_Planning_Base_On_Produksi.Add');
		$this->ENABLE_MANAGE  = has_permission('Material_Planning_Base_On_Produksi.Manage');
		$this->ENABLE_VIEW    = has_permission('Material_Planning_Base_On_Produksi.View');
		$this->ENABLE_DELETE  = has_permission('Material_Planning_Base_On_Produksi.Delete');
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

  public function data_side_material_planning(){
    
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_material_planning(
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
      $nestedData[]	= "<div align='center'>".strtoupper($row['so_number'])."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y',strtotime($row['tgl_so']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nm_customer'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['project'])."</div>";
      $nestedData[]	= "<div class='text-right'>".number_format($row['qty_order'],5)."</div>";
      $nestedData[]	= "<div class='text-right'>".number_format($row['qty_use_stok'],5)."</div>";
      $nestedData[]	= "<div class='text-right'>".number_format($row['qty_propose'],5)."</div>";

      $edit	= "";
      $booking	= "";
      if($this->ENABLE_MANAGE){
        $edit	= "<a href='".site_url($this->uri->segment(1)).'/material_planning/'.$row['so_number']."' class='btn btn-sm btn-primary' title='Set Material Planning' data-role='qtip'><i class='fa fa-hand-pointer-o'></i></a>";
        if(!empty($row['updated_date'])){
          $booking	= "<button type='button' class='btn btn-sm btn-success booking' title='Booking Material & Mengajukan Purchasing' data-so_number='".$row['so_number']."'><i class='fa fa-check'></i></button>";
        }
      }
      $view	= "<button type='button' class='btn btn-sm btn-warning detail' title='Detail' data-so_number='".$row['so_number']."'><i class='fa fa-eye'></i></button>";
      $nestedData[]	= "<div align='center'>".$view." ".$edit." ".$booking."</div>";
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

  public function get_query_material_planning($product, $costcenter, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $costcenter_where = "";
    // if($costcenter != '0'){
    // $costcenter_where = " AND a.costcenter = '".$costcenter."'";
    // }

    $product_where = "";
    // if($product != '0'){
    // $product_where = " AND b.code_lv1 = '".$product."'";
    // }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nm_customer
            FROM
              material_planning_base_on_produksi a
              LEFT JOIN customer b ON a.id_customer=b.id_customer,
              (SELECT @row:=0) r
            WHERE 1=1 AND a.category='base on production' AND a.booking_date IS NULL ".$costcenter_where." ".$product_where." AND (
              b.nm_customer LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.so_number LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.project LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'so_number',
      2 => 'tgl_so',
      3 => 'b.nm_customer',
      4 => 'project',
      5 => 'qty_order'
    );

    $sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

}
