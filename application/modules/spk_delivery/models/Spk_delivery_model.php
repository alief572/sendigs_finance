<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Spk_delivery_model extends BF_Model
{

  public function __construct()
  {
    parent::__construct();
    $this->ENABLE_ADD     = has_permission('SPK_Delivery.Add');
    $this->ENABLE_MANAGE  = has_permission('SPK_Delivery.Manage');
    $this->ENABLE_VIEW    = has_permission('SPK_Delivery.View');
    $this->ENABLE_DELETE  = has_permission('SPK_Delivery.Delete');
  }

  public function data_side_spk_material()
  {
    $controller      = ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_json_spk_material(
      $requestData['sales_order'],
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
      $nestedData[]  = "<div align='center'>" . strtoupper($row['no_so']) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['no_penawaran']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_customer']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['project']) . "</div>";

      $close_by = (!empty($GET_USER[$row['created_by']]['nama'])) ? $GET_USER[$row['created_by']]['nama'] : '';
      $close_date = (!empty($row['created_date'])) ? date('d-M-Y H:i', strtotime($row['created_date'])) : '';
      $nestedData[]  = "<div align='left'>" . $close_by . "</div>";
      $nestedData[]  = "<div align='center'>" . $close_date . "</div>";

      $getQTYSO = $this->db->select('SUM(qty_so) AS qty_so')->get_where('tr_sales_order_detail', array('no_so' => $row['no_so']))->result_array();
      $qty_so = (!empty($getQTYSO[0]['qty_so'])) ? $getQTYSO[0]['qty_so'] : 0;

      $status = 'Belum Ada SPK';
      $warna = 'blue';
      if ($qty_so == $row['total_delivery']) {
        $status = 'Closed';
        $warna = 'green';
      }
      if ($qty_so > $row['total_delivery'] and $row['total_delivery'] > 0) {
        $status = 'Partial SPK';
        $warna = 'yellow';
      }

      $nestedData[]  = "<div align='center'><span class='badge bg-" . $warna . "'>" . $status . "</span></div>";


      $release = "";
      $print = "";
      $create = "";
      $ButtonPrint = "";

      $getSPKDelivery = $this->db->get_where('spk_delivery', array('no_so' => $row['no_so'], 'deleted_date' => NULL))->result_array();
      $LI_A = "";
      foreach ($getSPKDelivery as $key => $value) {
        $LI_A .= "<li><a href='" . base_url('spk_delivery/print_spk/' . $value['no_delivery']) . "' target='_blank'>" . $value['no_delivery'] . "</a></li>";
      }

      if ($row['total_delivery'] > 0) {
        $ButtonPrint = '<div class="dropdown">
                          <button class="btn btn-default btn-sm dropdown-toggle" type="button" data-toggle="dropdown">Print
                          <span class="caret"></span></button>
                          <ul class="dropdown-menu">' . $LI_A . '</ul>
                        </div>';
      }

      if ($qty_so != $row['total_delivery'] and $this->ENABLE_ADD) {
        $create  = "<a href='" . base_url('spk_delivery/add/' . $row['no_so']) . "' class='btn btn-sm btn-primary' title='Create SPK Delivery' data-role='qtip'><i class='fa fa-plus'></i></a>";
      }
      // if($row['sts_request'] == 'N'){
      //   $release	= "<button type='button' class='btn btn-sm btn-primary request' data-id='".$row['id']."' title='Request To Subgudang' data-role='qtip'><i class='fa fa-hand-pointer-o'></i></button>";
      // }
      // else{
      //   $print	= "<a href='".base_url('plan_mixing/print_spk/'.$row['kode_det'])."' target='_blank' class='btn btn-sm btn-warning' title='Print SPK' data-role='qtip'><i class='fa fa-print'></i></a>";
      // }
      $nestedData[]  = "<div align='left'>" . $create . $release . $print . $ButtonPrint . "</div>";
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

  public function get_query_json_spk_material($sales_order, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $sales_order_where = "";
    if ($sales_order != '0') {
      $sales_order_where = " AND a.no_so = '" . $sales_order . "'";
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.no_so,
              a.no_penawaran,
              c.nm_customer,
              a.project,
              SUM(y.qty_delivery) AS total_delivery,
              a.created_by,
              a.created_on AS created_date
            FROM
              tr_sales_order a
              LEFT JOIN spk_delivery_detail y ON a.no_so = y.no_so
              LEFT JOIN spk_delivery z ON y.no_delivery = z.no_delivery
              LEFT JOIN tr_penawaran b ON a.no_penawaran = b.no_penawaran
              LEFT JOIN customer c ON b.id_customer = c.id_customer,
              (SELECT @row:=0) r
            WHERE a.approve = '1' " . $sales_order_where . " AND z.deleted_date IS NULL AND (
              a.no_so LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.no_penawaran LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nm_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.project LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            GROUP BY a.no_so
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'a.no_so',
      2 => 'a.no_penawaran',
      3 => 'c.nm_customer',
      4 => 'a.project'
    );

    $sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  //Re-Print
  public function data_side_spk_reprint()
  {
    $controller      = ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_json_spk_reprint(
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
      $nestedData[]  = "<div align='center'>" . strtoupper($row['so_number']) . "</div>";
      $nestedData[]  = "<div align='left'>ORIGA</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nama_product']) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['no_spk']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['qty']) . "</div>";
      $username = (!empty($GET_USER[$row['release_by']]['username'])) ? $GET_USER[$row['release_by']]['username'] : '-';
      $nestedData[]  = "<div align='center'>" . strtolower($username) . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y H:i:s', strtotime($row['release_date'])) . "</div>";

      $print  = "<a href='" . base_url('plan_mixing/print_spk/' . $row['kode_det']) . "' target='_blank' title='Print SPK' data-role='qtip'>Print</a>";

      $nestedData[]  = "<div align='center'>" . $print . "</div>";
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

  public function get_query_json_spk_reprint($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.kode,
              b.no_spk,
              b.request_by AS release_by,
              b.request_date AS release_date,
              b.qty,
              b.kode_det
            FROM
              so_internal_spk b
              LEFT JOIN so_internal a ON a.id=b.id_so AND b.status_id = '1',
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND b.sts_request = 'Y' AND b.status_id = '1' AND (
              a.code_lv4 LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.nama_product LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.so_number LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.kode LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.no_spk LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'so_number',
      2 => 'so_number',
      3 => 'nama_product',
      4 => 'b.no_spk',
      5 => 'propose'
    );

    $sql .= " ORDER BY b.request_date DESC,  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }
}
