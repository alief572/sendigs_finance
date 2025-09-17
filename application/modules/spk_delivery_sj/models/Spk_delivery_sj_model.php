<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Spk_delivery_sj_model extends BF_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Surat_Jalan_Delivery.Add');
    $this->ENABLE_MANAGE  = has_permission('Surat_Jalan_Delivery.Manage');
    $this->ENABLE_VIEW    = has_permission('Surat_Jalan_Delivery.View');
    $this->ENABLE_DELETE  = has_permission('Surat_Jalan_Delivery.Delete');
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

      $nestedData 	= array();

      $release = "";
      $print = "";
      $create = "";
      $receipt = "";
      $history = "";

      $icon = (!empty($row['no_surat_jalan'])) ? 'fa fa-edit' : 'fa fa-plus';
      $icon_color = (!empty($row['no_surat_jalan'])) ? 'btn-primary' : 'btn-default';

      if ($row['status'] == 'NOT YET DELIVER' and $this->ENABLE_ADD) {
        $create  = "<a href='" . base_url('spk_delivery_sj/add/' . $row['no_delivery']) . "' class='btn btn-sm " . $icon_color . "' title='Create SPK Delivery' data-role='qtip'><i class='" . $icon . "'></i></a>&nbsp;";
      }
      if ($row['status'] == 'NOT YET DELIVER' and !empty($row['no_surat_jalan']) and $this->ENABLE_MANAGE) {
        $release  = "<button type='button' class='btn btn-sm btn-info release' data-id='" . $row['no_delivery'] . "' title='Ready To Deliver' data-role='qtip'><i class='fa fa-paper-plane'></i></button>&nbsp;";
      }
      if (!empty($row['no_surat_jalan'])) {
        $print  = "<a href='" . base_url('spk_delivery_sj/print_spk/' . $row['no_delivery']) . "' target='_blank' class='btn btn-sm btn-warning' title='Print Surat Jalan' data-role='qtip'><i class='fa fa-print'></i></a>&nbsp;";
      }
      if ($row['status'] == 'ON DELIVER' and $this->ENABLE_MANAGE) {
        $receipt  = "<a href='" . base_url('spk_delivery_sj/confirm/' . $row['no_delivery']) . "' class='btn btn-sm btn-success' title='Delivery Receipt' data-role='qtip'><i class='fa fa-check'></i></a>&nbsp;";
      }
      if ($row['status'] == 'DELIVERY CONFIRMED') {
        $history  = "<a href='" . base_url('spk_delivery_sj/confirm/' . $row['no_delivery'] . '/detail') . "' class='btn btn-sm btn-primary' title='Delivery Receipt' data-role='qtip'><i class='fa fa-file'></i></a>&nbsp;";
      }

      $nestedData[]	= "<div align='left'>".$create.$release.$print.$receipt.$history."</div>";

      
      // $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['no_delivery'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['no_surat_jalan'])."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y',strtotime($row['created_date']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nm_customer'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['project'])."</div>";

      $close_by = (!empty($GET_USER[$row['created_by']]['nama']))?$GET_USER[$row['created_by']]['nama']:'';
      $close_date = (!empty($row['created_date']))?date('d-M-Y H:i',strtotime($row['created_date'])):'';
      $nestedData[]	= "<div align='left'>".$close_by."</div>";
      $nestedData[]	= "<div align='center'>".$close_date."</div>";

      $status = ucwords(strtolower($row['status']));
      $warna = 'blue';
      if($status == 'Delivery Confirmed'){
        $warna = 'green';
      }
      if($status == 'On Deliver'){
        $warna = 'yellow';
      }
      if($status == 'Check Qc'){
        $warna = 'purple';
      }

      $nestedData[]	= "<div align='center'><span class='badge bg-".$warna."'>".$status."</span></div>";
      $nestedData[]	= "<div align='left'>".$row['reject_reason']."</div>";


      
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
              z.no_delivery,
              z.no_surat_jalan,
              z.created_date,
              z.id,
              z.status,
              z.reject_reason,
              z.created_by,
              z.created_date
            FROM
              spk_delivery z
              LEFT JOIN tr_sales_order a ON a.no_so = z.no_so
              LEFT JOIN tr_penawaran b ON a.no_penawaran = b.no_penawaran
              LEFT JOIN customer c ON b.id_customer = c.id_customer,
              (SELECT @row:=0) r
            WHERE a.approve = '1' " . $sales_order_where . " AND z.deleted_date IS NULL AND (
              a.no_so LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.no_penawaran LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nm_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.project LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR z.no_surat_jalan LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR z.no_delivery LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'z.no_delivery',
      2 => 'z.no_surat_jalan',
      3 => 'z.created_date',
      4 => 'c.nm_customer',
      4 => 'a.project',
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
