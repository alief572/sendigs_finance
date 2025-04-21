<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Spk_material_model extends BF_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('SPK_Material.Add');
    $this->ENABLE_MANAGE  = has_permission('SPK_Material.Manage');
    $this->ENABLE_VIEW    = has_permission('SPK_Material.View');
    $this->ENABLE_DELETE  = has_permission('SPK_Material.Delete');
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

  public function data_side_spk_material()
  {
    $controller      = ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_json_spk_material(
      $requestData['sales_order'],
      $requestData['code_lv1'],
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

      $variant_product   = (!empty($row['variant_product'])) ? '; Variant ' . $row['variant_product'] : '';
      $color_product     = (!empty($row['color_product'])) ? '; Color ' . $row['color_product'] : '';
      $surface_product   = (!empty($row['surface_product'])) ? '; Surface ' . $row['surface_product'] : '';

      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['so_number']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_customer']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nama_level4'] . $variant_product . $color_product . $surface_product) . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y', strtotime($row['due_date'])) . "</div>";
      $username = (!empty($GET_USER[$row['created_by']]['username'])) ? $GET_USER[$row['created_by']]['username'] : '-';
      $nestedData[]  = "<div align='left'>" . strtolower($username) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['propose']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['qty_spk']) . "</div>";
      $qty_sisa = $row['propose'] - $row['qty_spk'];
      $nestedData[]  = "<div align='center' id='sisa_" . $row['id'] . "'>" . number_format($qty_sisa) . "</div>";
      $nestedData[]  = "<div align='center'>
                        <input type='text' id='qty_" . $row['id'] . "' data-id='" . $row['id'] . "' class='form-control text-center input-sm autoNumeric0 changeQty' style='width:80px;'><script>$('.autoNumeric0').autoNumeric('init', {mDec: '0', aPad: false})</script>
                      </div>";
      $release = "";
      if ($qty_sisa > 0 and $this->ENABLE_MANAGE) {
        $release  = "<button type='button' class='btn btn-sm btn-primary release' data-id='" . $row['id'] . "' title='SPK' data-role='qtip'><i class='fa fa-hand-pointer-o'></i></a>";
      }
      $nestedData[]  = "<div align='center'>" . $release . "</div>";
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

  public function get_query_json_spk_material($sales_order, $code_lv1, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $sales_order_where = "";
    if ($sales_order != '0') {
      $sales_order_where = " AND a.so_number = '" . $sales_order . "'";
    }

    $code_lv1_where = "";
    if ($code_lv1 != '0') {
      $code_lv1_where = " AND z.code_lv1 = '" . $code_lv1 . "'";
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              SUM(b.qty) AS qty_spk,
              z.nama AS nama_level4,
              d.variant_product,
              d.color AS color_product,
              d.surface AS surface_product,
              c.nm_customer
            FROM
              so_internal a
              LEFT JOIN so_internal_spk b ON a.id=b.id_so AND b.status_id = '1'
              LEFT JOIN new_inventory_4 z ON a.code_lv4 = z.code_lv4
              LEFT JOIN customer c ON a.id_customer=c.id_customer
              LEFT JOIN bom_header d ON a.no_bom=d.no_bom,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND z.code_lv1 != 'P123000009' " . $sales_order_where . " " . $code_lv1_where . " AND (
              a.code_lv4 LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR z.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.variant_product LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.color LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.surface LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.so_number LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nm_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            GROUP BY a.id
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'so_number',
      2 => 'c.nm_customer',
      3 => 'nama_product',
      4 => 'due_date',
      5 => 'propose'
    );

    $sql .= " ORDER BY  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
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

      $variant_product   = (!empty($row['variant_product'])) ? '; Variant ' . $row['variant_product'] : '';
      $color_product     = (!empty($row['color_product'])) ? '; Color ' . $row['color_product'] : '';
      $surface_product   = (!empty($row['surface_product'])) ? '; Surface ' . $row['surface_product'] : '';

      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['so_number']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_customer']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nama_level4'] . $variant_product . $color_product . $surface_product) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['no_spk']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['qty_spk']) . "</div>";
      $username = (!empty($GET_USER[$row['release_by']]['username'])) ? $GET_USER[$row['release_by']]['username'] : '-';
      $nestedData[]  = "<div align='center'>" . strtolower($username) . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y H:i:s', strtotime($row['release_date'])) . "</div>";

      $print  = "<a href='" . base_url('spk_material/print_spk/' . $row['kode']) . "' target='_blank' title='Print SPK' data-role='qtip'>Print</a>";

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
              b.created_by AS release_by,
              b.created_date AS release_date,
              SUM(b.qty) AS qty_spk,
              z.nama AS nama_level4,
              d.variant_product,
              d.color AS color_product,
              d.surface AS surface_product,
              c.nm_customer
            FROM
              so_internal_spk b
              LEFT JOIN so_internal a ON a.id=b.id_so AND b.status_id = '1'
              LEFT JOIN new_inventory_4 z ON a.code_lv4 = z.code_lv4
              LEFT JOIN customer c ON a.id_customer=c.id_customer
              LEFT JOIN bom_header d ON a.no_bom=d.no_bom,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND b.status_id = '1' AND (
              a.code_lv4 LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR z.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.variant_product LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.color LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.surface LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.so_number LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.kode LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.no_spk LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nm_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            GROUP BY b.kode
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'so_number',
      2 => 'c.nm_customer',
      3 => 'nama_product',
      4 => 'b.no_spk',
      5 => 'propose'
    );

    $sql .= " ORDER BY b.id DESC,  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }
}
