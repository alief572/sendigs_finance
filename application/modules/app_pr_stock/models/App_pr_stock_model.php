<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class App_pr_stock_model extends BF_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Approval_PR_Stock.Add');
    $this->ENABLE_MANAGE  = has_permission('Approval_PR_Stock.Manage');
    $this->ENABLE_VIEW    = has_permission('Approval_PR_Stock.View');
    $this->ENABLE_DELETE  = has_permission('Approval_PR_Stock.Delete');
  }

  public function get_data($table, $where_field = '', $where_value = '')
  {
    if ($where_field != '' && $where_value != '') {
      $query = $this->db->get_where($table, array($where_field => $where_value));
    } else {
      $query = $this->db->get($table);
    }

    return $query->result();
  }

  public function get_data_group($table, $where_field = '', $where_value = '', $where_group = '')
  {
    if ($where_field != '' && $where_value != '') {
      $query = $this->db->group_by($where_group)->get_where($table, array($where_field => $where_value));
    } else {
      $query = $this->db->get($table);
    }

    return $query->result();
  }

  public function data_side_approval_pr_material()
  {

    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_approval_pr_material(
      $requestData['tanda'],
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
      $getCheck = $this->db->get_where('material_planning_base_on_produksi_detail', array('so_number' => $row['so_number'], 'status_app' => 'N'))->result();
      // if(COUNT($getCheck) > 0){
      $total_data     = $totalData;
      $start_dari     = $requestData['start'];
      $asc_desc       = $requestData['order'][0]['dir'];
      if ($asc_desc == 'asc') {
        $nomor = ($total_data - $start_dari) - $urut2;
      }
      if ($asc_desc == 'desc') {
        $nomor = $urut1 + $start_dari;
      }

      $tingkat_approval = 1;
      if($requestData['tanda'] == 'cost_control') {
        $tingkat_approval = 2;
      }
      if($requestData['tanda'] == 'management') {
        $tingkat_approval = 3;
      }
      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper('PRODUCTION PLANNING ' . $row['so_number']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['so_number']) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['no_pr']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['project']) . "</div>";
      $nama_user = (!empty($GET_USER[$row['booking_by']]['nama'])) ? $GET_USER[$row['booking_by']]['nama'] : '';
      $nestedData[]  = "<div class='text-left'>" . ucwords(strtolower($nama_user)) . "</div>";
      $nestedData[]  = "<div class='text-left'>" . date('d-M-Y', strtotime($row['booking_date'])) . "</div>";


      $warna = (COUNT($getCheck) > 0) ? 'blue' : 'green';
      $status = (COUNT($getCheck) > 0) ? 'Waiting Approval' : 'Close';

      if (($row['sts_reject1'] !== null || $row['sts_reject2'] !== null || $row['sts_reject3'] !== null) && $row['rejected'] == 1) {
        if ($row['sts_reject1'] == "1") :
          $warna = "red";
          $sts = "Rejected By Head";
        elseif ($row['sts_reject2'] == "1") :
          $warna = "red";
          $sts = "Rejected By Cost Control";
        elseif ($row['sts_reject3'] == "1") :
          $warna = "red";
          $sts = "Rejected By Management";
        endif;
      } else {
        if ($row['app_1'] == null && $row['app_2'] == null && $row['app_3'] == null) :
          $warna = "blue";
          $sts = "Waiting Approval Head";
        elseif ($row['app_1'] !== null && $row['app_2'] == null && $row['app_3'] == null) :
          $warna = "blue";
          $sts = "Waiting Approval Cost Control";
        elseif ($row['app_1'] !== null && $row['app_2'] !== null && $row['app_3'] == null) :
          $warna = "blue";
          $sts = "Waiting Approval Management";
        else :
          if ($row['sts_app'] == "Y") :
            $warna = "green";
            $sts = "Approved";
          else :
            $warna = "blue";
            $sts = "Waiting Approval Head";
          endif;
        endif;
      }

      $nestedData[]    = "<div align='left'><span class='badge' style='background-color: " . $warna . ";'>" . $sts . "</span></div>";
      // $nestedData[]  = "<div align='left'><span class='badge bg-" . $warna . "'>" . $status . "</span></div>";

      $approve  = "";
      $view  = "<a href='" . site_url($this->uri->segment(1)) . '/detail_planning/' . $row['so_number'] . "' class='btn btn-sm btn-warning' title='Detail PR' data-role='qtip'><i class='fa fa-eye'></i></a>";
      if ($this->ENABLE_MANAGE and COUNT($getCheck) > 0) {
        $approve  = "<a href='" . site_url($this->uri->segment(1)) . '/approval_planning/' . $row['so_number'] . "/".$tingkat_approval."' class='btn btn-sm btn-success' title='Approval PR' data-role='qtip'><i class='fa fa-check'></i></a>";
      }
      $nestedData[]  = "<div align='left'>" . $view . " " . $approve . "</div>";
      $data[] = $nestedData;
      $urut1++;
      $urut2++;
      // }
    }

    $json_data = array(
      "draw"              => intval($requestData['draw']),
      "recordsTotal"      => intval($totalData),
      "recordsFiltered"   => intval($totalFiltered),
      "data"              => $data
    );

    echo json_encode($json_data);
  }

  public function get_query_approval_pr_material($tanda, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $costcenter_where = "";
    // if($costcenter != '0'){
    // $costcenter_where = " AND a.costcenter = '".$costcenter."'";
    // }

    $product_where = "";
    // if($product != '0'){
    // $product_where = " AND b.code_lv1 = '".$product."'";
    // }

    $filter_approval = '';
    if ($tanda == 'head') {
      $filter_approval = ' AND a.app_post IS NULL';
    }
    if ($tanda == 'cost_control') {
      $filter_approval = ' AND a.app_post = 2';
    }
    if ($tanda == 'management') {
      $filter_approval = ' AND a.app_post = 3';
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nm_customer
            FROM
              material_planning_base_on_produksi a
              INNER JOIN material_planning_base_on_produksi_detail z ON a.so_number=z.so_number
              LEFT JOIN customer b ON a.id_customer=b.id_customer,
              (SELECT @row:=0) r
            WHERE 1=1 AND a.category='pr stok' AND a.booking_date IS NOT NULL AND z.status_app = 'N' AND a.close_pr IS NULL AND a.rejected IS NULL " . $filter_approval . " AND (
              b.nm_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.so_number LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.project LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            GROUP BY a.so_number
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'so_number',
      2 => 'so_number',
      3 => 'no_pr',
      4 => 'so_number'
    );

    $sql .= " ORDER BY a.booking_date DESC,  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }
}
