<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Assembly_model extends BF_Model{

  public function __construct(){
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Assembly.Add');
		$this->ENABLE_MANAGE  = has_permission('Assembly.Manage');
		$this->ENABLE_VIEW    = has_permission('Assembly.View');
		$this->ENABLE_DELETE  = has_permission('Assembly.Delete');

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

  public function data_side_request_produksi()
  {
    $controller      = ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_request_produksi(
      $requestData['product'],
      $requestData['costcenter'],
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData      = $fetch['totalData'];
    $totalFiltered  = $fetch['totalFiltered'];
    $query          = $fetch['query'];

    $data  = array();
    $urut1  = 1;
    $urut2  = 0;
    $GET_USER = get_list_user();
    foreach ($query->result_array() as $row) {
      $total_data     = $totalData;
      $start_dari     = $requestData['start'];
      $asc_desc       = $requestData['order'][0]['dir'];
      if ($asc_desc == 'asc') {
        $nomor = ($total_data - $start_dari) - $urut2;
      }
      if ($asc_desc == 'desc') {
        $nomor = $urut1 + $start_dari;
      }

      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['so_customer']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_customer']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_product']) . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y', strtotime($row['due_date'])) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['propose']) . "</div>";
      // $username = (!empty($GET_USER[$row['created_by']]['username'])) ? $GET_USER[$row['created_by']]['username'] : '-';
      // $nestedData[]  = "<div align='left'>" . strtolower($username) . "</div>";

      $status = 'WAITING';
      $warna = 'yellow';
      $color_btn = 'btn-default';
      $icon = 'fa-hand-pointer-o';
      if ($row['sts_ass'] == 'Y') {
        $status = 'CLOSE';
        $warna = 'green';
      }
      if ($row['sts_ass'] == 'P') {
        $status = 'PROCESS';
        $warna = 'blue';
        $color_btn = 'btn-primary';
        $icon = 'fa-edit';
      }
      $nestedData[]  = "<div align='center'><span class='badge bg-" . $warna . "' title='".$row['no_bom_planning']."'>" . $status . "</span></div>";
      $edit  = "";
      $delete  = "";
      $approve  = "";
      if (($row['sts_ass'] == 'N' OR $row['sts_ass'] == 'P') and $this->ENABLE_MANAGE) {
          $edit  = "<a href='" . site_url($this->uri->segment(1)) . '/add/' . $row['code_lv4'] . '/' . $row['id'] . "' class='btn btn-sm ".$color_btn."' title='Assembly' data-role='qtip'><i class='fa ".$icon."'></i></a>";
      }
      if($this->ENABLE_DELETE AND ($row['sts_ass'] == 'P')){
        $delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete' title='Delete' data-id='".$row['id']."'><i class='fa fa-trash'></i></button>";
        $approve	= "&nbsp;<button type='button' class='btn btn-sm btn-success lock' title='Process' data-id='".$row['id']."'><i class='fa fa-check'></i></button>";
      }
      $nestedData[]  = "<div align='left'>" . $edit . " " . $approve . " " . $delete . "</div>";
      $data[] = $nestedData;
      $urut1++;
      $urut2++;
    }

    $json_data = array(
      "draw"              => intval($requestData['draw']),
      "recordsTotal"      => intval($totalData),
      "recordsFiltered"   => intval($totalFiltered),
      "data"              => $data
    );

    echo json_encode($json_data);
  }

  public function get_query_request_produksi($product, $costcenter, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $costcenter_where = "";

    $product_where = "";
    if ($product != '0') {
      $product_where = " AND b.code_lv1 = '" . $product . "'";
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nama AS nm_product,
              c.nm_customer
            FROM
                so_internal_request a
                LEFT JOIN new_inventory_4 b ON a.code_lv4=b.code_lv4
                LEFT JOIN customer c ON a.id_customer=c.id_customer,
                (SELECT @row:=0) r
            WHERE 1=1 " . $costcenter_where . " " . $product_where . " AND a.deleted_date IS NULL AND a.no_bom_planning LIKE 'BOC%' AND (
              a.so_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.code_lv4 LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nm_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'so_customer',
      2 => 'c.nm_customer',
      3 => 'b.nama',
      4 => 'due_date',
      5 => 'propose'
    );

    $sql .= " ORDER BY  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }
}