<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Outstanding_qc_ftackle_model extends BF_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Outstanding_QC_Ftackle.Add');
    $this->ENABLE_MANAGE  = has_permission('Outstanding_QC_Ftackle.Manage');
    $this->ENABLE_VIEW    = has_permission('Outstanding_QC_Ftackle.View');
    $this->ENABLE_DELETE  = has_permission('Outstanding_QC_Ftackle.Delete');

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');
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

  public function data_side_outstanding_qc()
  {
    $controller      = ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_outstanding_qc(
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
    $jumlahQC = 0;
    foreach ($query->result_array() as $row) {
      $QTY = $row['qty'] - $row['qty_qc'];
      $QC_SISA = $QTY;
      $QC_ALL = $row['qty'];
      if ($row['subgudang_date'] >= '2024-02-25 00:00:00' || $row['subgudang_date'] == null) {
        $getQC = $this->db->get_where('so_internal_product', array('id_key_spk' => $row['id'], 'status' => 'N'))->result();

        $QC_SISA = COUNT($getQC);
        $QC_ALL = $row['qty_qc'];
      }
      if ($QC_SISA > 0) {

        $total_data     = $totalData;
        $start_dari     = $requestData['start'];
        $asc_desc       = $requestData['order'][0]['dir'];
        if ($asc_desc == 'asc') {
          $nomor = ($total_data - $start_dari) - $urut2;
        }
        if ($asc_desc == 'desc') {
          $nomor = $urut1 + $start_dari;
        }

        $variant_product   = (!empty($row['variant_product'])) ? '; Variant ' . $row['variant_product'] : '';
        $color_product     = (!empty($row['color_product'])) ? '; Color ' . $row['color_product'] : '';
        $surface_product   = (!empty($row['surface_product'])) ? '; Surface ' . $row['surface_product'] : '';

        $nestedData   = array();
        $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
        $nestedData[]  = "<div align='center'>" . strtoupper($row['no_spk']) . "</div>";
        $nestedData[]  = "<div align='left'>" . strtoupper($row['nama_level4'] . $variant_product . $color_product . $surface_product) . "</div>";
        $nestedData[]  = "<div align='center'>" . strtoupper($row['so_number']) . "</div>";
        $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_customer']) . "</div>";
        $nestedData[]  = "<div align='left'>" . strtoupper($row['project']) . "</div>";
        $nestedData[]  = "<div align='center'>" . $QC_SISA . "/" . number_format($QC_ALL) . "</div>";
        $nestedData[]  = "<div align='center'>" . date('d-M-Y', strtotime($row['close_produksi'])) . "</div>";

        $sts_subgudang = ($row['subgudang_date'] >= '2024-02-25 00:00:00') ? 'new' : 'old';

        $approve  = "";
        if ($this->ENABLE_MANAGE) {
          $approve  = "<button type='button' data-id='" . $row['id'] . "' data-status='" . $sts_subgudang . "' class='btn btn-sm btn-success detail' title='QC' data-role='qtip'><i class='fa fa-check'></i></a>";
        }
        $nestedData[]  = "<div align='center'>" . $approve . "</div>";
        $data[] = $nestedData;
        $urut1++;
        $urut2++;
      } else {
        $jumlahQC++;
      }
    }

    $json_data = array(
      "draw"              => intval($requestData['draw']),
      "recordsTotal"      => intval($totalData - $jumlahQC),
      "recordsFiltered"   => intval($totalFiltered - $jumlahQC),
      "data"              => $data
    );

    echo json_encode($json_data);
  }

  public function get_query_outstanding_qc($product, $costcenter, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $costcenter_where = "";

    $product_where = "";
    if ($product != '0') {
      $product_where = " AND b.code_lv1 = '" . $product . "'";
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              z.id,
              z.no_spk,
              b.nama AS nama_level4,
              d.variant_product,
              d.color AS color_product,
              d.surface AS surface_product,
              a.so_number,
              c.nm_customer,
              a.project,
              d.variant_product,
              z.qty,
              MAX(y.close_produksi) AS close_produksi,
              COUNT(y.id) AS qty_qc,
              z.subgudang_date
            FROM
                so_internal_product y
                LEFT JOIN so_internal_spk z ON z.id = y.id_key_spk
                LEFT JOIN so_internal a ON z.id_so = a.id AND a.deleted_date IS NULL
                LEFT JOIN new_inventory_4 b ON a.code_lv4=b.code_lv4
                LEFT JOIN customer c ON a.id_customer=c.id_customer
                LEFT JOIN bom_header d ON a.no_bom = d.no_bom,
                (SELECT @row:=0) r
            WHERE z.sts_close IN ('Y','P') " . $costcenter_where . " " . $product_where . " AND b.code_lv1 = 'P123000009' AND (
              z.no_spk LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.variant_product LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.color LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.surface LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.so_number LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nm_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.project LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            GROUP BY z.id
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'z.no_spk',
      2 => 'a.so_number',
      3 => 'b.nama',
      4 => 'c.nm_customer',
      5 => 'a.project',
      6 => 'z.qty',
      7 => 'z.tanggal_close'
    );

    $sql .= " ORDER BY  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }
}
