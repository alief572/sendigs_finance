<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Subgudang_request_list_model extends BF_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Subgudang_Request_List.Add');
    $this->ENABLE_MANAGE  = has_permission('Subgudang_Request_List.Manage');
    $this->ENABLE_VIEW    = has_permission('Subgudang_Request_List.View');
    $this->ENABLE_DELETE  = has_permission('Subgudang_Request_List.Delete');
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
    $GET_SUM_BERAT = getTotalBeratSPKSOInternalMixing();
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

      if ($row['sts_spk'] == 'non-mixing') {
        $color = "text-primary text-bold";
        $nm_gudang = 'gudang produksi';
      } else {
        $color = "text-success text-bold";
        $nm_gudang = $row['nama_costcenter'];
      }

      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['kode_det']) . "-<span class='" . $color . "'>" . strtoupper($row['sts_req']) . "</span></div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['so_number']) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['no_spk']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_gudang']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($nm_gudang) . "</div>";
      $nestedData[]  = "<div align='right'>" . number_format($GET_SUM_BERAT[$row['kode_det']] * $row['qty'], 4) . "</div>";


      $release = "";
      $print = "";
      if ($row['sts_spk'] == 'non-mixing') {
        $username = (!empty($GET_USER[$row['produksi_by']]['username'])) ? $GET_USER[$row['produksi_by']]['username'] : '-';
        $datetime = $row['produksi_date'];

        $status = 'Waiting Confirm';
        $warna = 'yellow';
        if ($row['sts_produksi'] == 'Y') {
          $status = 'Closed';
          $warna = 'green';
          $print  = "&nbsp;<a href='" . base_url('subgudang_request_list/print_spk_confirm/' . $row['kode_det']) . "' target='_blank' class='btn btn-sm btn-warning' title='Print Pengeluaran Material' data-role='qtip'><i class='fa fa-print'></i></a>";
        }

        if ($row['sts_produksi'] == 'P' and $this->ENABLE_MANAGE) {
          $release  = "&nbsp;<a href='" . base_url('subgudang_request_list/add_confirm/' . $row['id']) . "' class='btn btn-sm btn-success' title='Confirm Request' data-role='qtip'><i class='fa fa-check'></i></a>";
        }

        // $print	= "&nbsp;<a href='".base_url('subgudang_request_list/print_spk/'.$row['kode_det'])."' target='_blank' class='btn btn-sm btn-warning' title='Print SPK' data-role='qtip'><i class='fa fa-print'></i></a>";
      }
      if ($row['sts_spk'] != 'non-mixing') {
        $username = (!empty($GET_USER[$row['created_by']]['username'])) ? $GET_USER[$row['created_by']]['username'] : '-';
        $datetime = $row['created_date'];

        $status = 'Waiting Outgoing';
        $warna = 'blue';
        if ($row['sts_subgudang'] == 'P') {
          $status = 'Parsial';
          $warna = 'purple';
        }
        if ($row['sts_subgudang'] == 'Y') {
          $status = 'Closed';
          $warna = 'green';
        }

        if ($row['sts_subgudang'] != 'Y' and $this->ENABLE_MANAGE) {
          $release  = "&nbsp;<a href='" . base_url('subgudang_request_list/add2/' . $row['id']) . "' class='btn btn-sm btn-primary' title='Pengeluaran' data-role='qtip'><i class='fa fa-hand-pointer-o'></i></a>";
        }
        if ($row['sts_subgudang'] == 'Y') {
          $print  = "&nbsp;<a href='" . base_url('subgudang_request_list/print_spk/' . $row['kode_det']) . "' target='_blank' class='btn btn-sm btn-warning' title='Print Pengeluaran Material' data-role='qtip'><i class='fa fa-print'></i></a>";
        }
      }

      $nestedData[]  = "<div align='left'>" . strtolower($username) . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y H:i:s', strtotime($datetime)) . "</div>";
      $nestedData[]  = "<div align='center'><span class='badge bg-" . $warna . "'>" . $status . "</span></div>";

      $nestedData[]  = "<div align='left'>" . $print . $release . "</div>";
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
      $sales_order_where = " AND a.so_number = '" . $sales_order . "'";
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              b.id,
              a.nama_product,
              a.so_number,
              b.no_spk,
              b.tanggal AS plan_date,
              b.id_costcenter,
              b.qty,
              b.request_by AS created_by,
              b.request_date AS created_date,
              b.produksi_by AS produksi_by,
              b.produksi_date AS produksi_date,
              c.nama_costcenter,
              b.sts_subgudang,
              b.sts_produksi,
              b.kode_det,
              b.sts_spk AS sts_req,
              d.nm_gudang AS nm_gudang,
              b.sts_spk
            FROM
              so_internal_spk_view b
              LEFT JOIN so_internal a ON a.id=b.id_so AND b.status_id = '1'
              LEFT JOIN new_inventory_4 z ON a.code_lv4 = z.code_lv4
              LEFT JOIN ms_costcenter c ON b.id_costcenter=c.id_costcenter
              LEFT JOIN warehouse d ON b.id_gudang=d.id,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND b.sts_request='Y' AND b.sts_spk='mixing' AND b.status_id = '1' AND z.code_lv1 != 'P123000009' " . $sales_order_where . " AND (
              a.code_lv4 LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.nama_product LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.so_number LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.no_spk LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nama_costcenter LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.kode_det LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'a.nama_product',
      2 => 'a.so_number',
      3 => 'b.no_spk',
      4 => 'b.tanggal',
      5 => 'c.nama_costcenter',
      6 => 'b.qty',
      7 => 'b.created_by',
      8 => 'b.created_date',
    );

    $sql .= " ORDER BY b.id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  //request material
  public function data_side_request_material()
  {
    $controller      = ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_json_request_material(
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
    $GET_SUM_BERAT = getTotalBeratSPKSOInternal();
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
      $nestedData[]  = "<div align='center'>" . strtoupper($row['kode_trans']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_gudang']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nama_costcenter']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['qty_packing'], 2) . "</div>";
      $username = (!empty($GET_USER[$row['created_by']]['username'])) ? $GET_USER[$row['created_by']]['username'] : '-';
      $nestedData[]  = "<div align='left'>" . strtolower($username) . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y H:i:s', strtotime($row['created_date'])) . "</div>";

      $status = 'Waiting Confirm';
      $warna = 'blue';
      if ($row['sts_confirm'] == 'Y') {
        $status = 'Closed';
        $warna = 'green';
      }
      $nestedData[]  = "<div align='center'><span class='badge bg-" . $warna . "'>" . $status . "</span></div>";


      $release  = "";
      $print    = "";
      $edit    = "";
      $view  = "<button type='button' data-kode_trans='" . $row['kode_trans'] . "' data-tanda='detail' class='btn btn-sm btn-warning detail' title='Detail' data-role='qtip'><i class='fa fa-eye'></i></button>";
      if ($row['sts_confirm'] == 'N' and $this->ENABLE_MANAGE) {
        $edit  = "&nbsp;<button type='button' data-kode_trans='" . $row['kode_trans'] . "' data-tanda='edit' class='btn btn-sm btn-success detail' title='Confirm' data-role='qtip'><i class='fa fa-check'></i></button>";
      }
      if ($row['sts_confirm'] != 'N') {
        $print  = "&nbsp;<a href='" . base_url('subgudang_request_list/print_spk_request/' . $row['kode_trans']) . "' target='_blank' class='btn btn-sm btn-info' title='Print SPK Pengeluaran Material' data-role='qtip'><i class='fa fa-print'></i></a>";
      }
      $nestedData[]  = "<div align='left'>" . $view . $edit . $print . $release . "</div>";
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

  public function get_query_json_request_material($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.kode_trans,
              a.jumlah_mat_packing AS qty_packing,
              a.created_by,
              a.created_date,
              c.nama_costcenter,
              d.nm_gudang,
              a.checked AS sts_confirm
            FROM
              warehouse_adjustment a
              LEFT JOIN ms_costcenter c ON a.kd_gudang_ke=c.id_costcenter
              LEFT JOIN warehouse d ON a.id_gudang_dari=d.id,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND a.category='request produksi' AND d.desc='subgudang' AND (
              a.kode_trans LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.nm_gudang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nama_costcenter LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'kode_trans',
      2 => 'd.nm_gudang',
      3 => 'c.nama_costcenter',
      4 => 'jumlah_mat_packing',
      5 => 'created_by',
      6 => 'created_date',
    );

    $sql .= " ORDER BY a.id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  //request material
  public function data_side_request_material_ftackle()
  {
    $controller      = ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_json_request_material_ftackle(
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
    $GET_SUM_BERAT = getTotalBeratSPKSOInternal();
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
      $nestedData[]  = "<div align='center'>" . strtoupper($row['kode_trans']) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['no_spk']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_product']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_gudang']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nama_costcenter']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['qty_unit'], 4) . "</div>";
      $username = (!empty($GET_USER[$row['created_by']]['username'])) ? $GET_USER[$row['created_by']]['username'] : '-';
      $nestedData[]  = "<div align='left'>" . strtolower($username) . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y H:i:s', strtotime($row['created_date'])) . "</div>";

      $status = 'Waiting Confirm';
      $warna = 'blue';
      if ($row['sts_confirm'] == 'Y') {
        $status = 'Closed';
        $warna = 'green';
      }
      $nestedData[]  = "<div align='center'><span class='badge bg-" . $warna . "'>" . $status . "</span></div>";


      $release  = "";
      $print    = "";
      $edit    = "";
      $view  = "<button type='button' data-kode_trans='" . $row['kode_trans'] . "' data-tanda='detail' class='btn btn-sm btn-warning detail2' title='Detail' data-role='qtip'><i class='fa fa-eye'></i></button>";
      if ($row['sts_confirm'] == 'N' and $this->ENABLE_MANAGE) {
        $edit  = "&nbsp;<button type='button' data-kode_trans='" . $row['kode_trans'] . "' data-tanda='edit' class='btn btn-sm btn-success detail2' title='Confirm' data-role='qtip'><i class='fa fa-check'></i></button>";
      }
      if ($row['sts_confirm'] != 'N') {
        $print  = "&nbsp;<a href='" . base_url('subgudang_request_list/print_spk_request_ftackle/' . $row['kode_trans']) . "' target='_blank' class='btn btn-sm btn-info' title='Print SPK Pengeluaran Material' data-role='qtip'><i class='fa fa-print'></i></a>";
      }
      $nestedData[]  = "<div align='left'>" . $view . $edit . $print . $release . "</div>";
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

  public function get_query_json_request_material_ftackle($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.kode_trans,
              a.jumlah_mat_packing AS qty_packing,
              a.jumlah_mat AS qty_unit,
              a.created_by,
              a.created_date,
              c.nama_costcenter,
              d.nm_gudang,
              a.checked AS sts_confirm,
              e.no_spk,
              g.nama AS nm_product
            FROM
              warehouse_adjustment a
              LEFT JOIN ms_costcenter c ON a.kd_gudang_ke=c.id_costcenter
              LEFT JOIN warehouse d ON a.id_gudang_dari=d.id
              LEFT JOIN so_internal_spk e ON a.no_ipp=e.id
              LEFT JOIN so_internal f ON e.id_so=f.id
              LEFT JOIN new_inventory_4 g ON f.code_lv4=g.code_lv4,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND a.category='request produksi ftackle' AND d.desc='subgudang' AND (
              a.kode_trans LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.nm_gudang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nama_costcenter LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR e.no_spk LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR g.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'kode_trans',
      2 => 'd.nm_gudang',
      3 => 'c.nama_costcenter',
      4 => 'jumlah_mat_packing',
      5 => 'created_by',
      6 => 'created_date',
    );

    $sql .= " ORDER BY a.id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  //request material cutting
  public function data_side_request_material_cutting()
  {
    $controller      = ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_json_request_material_cutting(
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
    $GET_SUM_BERAT = getTotalBeratSPKSOInternal();
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
      $nestedData[]  = "<div align='center'>" . strtoupper($row['kode_trans']) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['no_spk']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_product']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_gudang']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nama_costcenter']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['qty_unit'], 4) . "</div>";
      $username = (!empty($GET_USER[$row['created_by']]['username'])) ? $GET_USER[$row['created_by']]['username'] : '-';
      $nestedData[]  = "<div align='left'>" . strtolower($username) . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y H:i:s', strtotime($row['created_date'])) . "</div>";

      $status = 'Waiting Confirm';
      $warna = 'blue';
      if ($row['sts_confirm'] == 'Y') {
        $status = 'Closed';
        $warna = 'green';
      }
      $nestedData[]  = "<div align='center'><span class='badge bg-" . $warna . "'>" . $status . "</span></div>";


      $release  = "";
      $print    = "";
      $edit    = "";
      $view  = "<button type='button' data-kode_trans='" . $row['kode_trans'] . "' data-tanda='detail' class='btn btn-sm btn-warning detail3' title='Detail' data-role='qtip'><i class='fa fa-eye'></i></button>";
      if ($row['sts_confirm'] == 'N' and $this->ENABLE_MANAGE) {
        $edit  = "&nbsp;<button type='button' data-kode_trans='" . $row['kode_trans'] . "' data-tanda='edit' class='btn btn-sm btn-success detail3' title='Confirm' data-role='qtip'><i class='fa fa-check'></i></button>";
      }
      if ($row['sts_confirm'] != 'N') {
        $print  = "&nbsp;<a href='" . base_url('subgudang_request_list/print_spk_request_cutting/' . $row['kode_trans']) . "' target='_blank' class='btn btn-sm btn-info' title='Print SPK Pengeluaran Material' data-role='qtip'><i class='fa fa-print'></i></a>";
      }
      $nestedData[]  = "<div align='left'>" . $view . $edit . $print . $release . "</div>";
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

  public function get_query_json_request_material_cutting($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.kode_trans,
              a.jumlah_mat_packing AS qty_packing,
              a.jumlah_mat AS qty_unit,
              a.created_by,
              a.created_date,
              c.nama_costcenter,
              d.nm_gudang,
              a.checked AS sts_confirm,
              e.no_spk,
              g.nama AS nm_product
            FROM
              warehouse_adjustment a
              LEFT JOIN ms_costcenter c ON a.kd_gudang_ke=c.id_costcenter
              LEFT JOIN warehouse d ON a.id_gudang_dari=d.id
              LEFT JOIN so_spk_cutting_request e ON a.no_ipp=e.id
              LEFT JOIN so_spk_cutting f ON e.id_so=f.id
              LEFT JOIN new_inventory_4 g ON f.code_lv4=g.code_lv4,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND a.category='request produksi cutting' AND d.desc='subgudang' AND (
              a.kode_trans LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.nm_gudang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nama_costcenter LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR e.no_spk LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR g.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'kode_trans',
      2 => 'd.nm_gudang',
      3 => 'c.nama_costcenter',
      4 => 'jumlah_mat_packing',
      5 => 'created_by',
      6 => 'created_date',
    );

    $sql .= " ORDER BY a.id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  //request material cutting
  public function data_side_request_material_assembly()
  {
    $controller      = ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_json_request_material_assembly(
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
    $GET_SUM_BERAT = getTotalBeratSPKSOInternal();
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
      $nestedData[]  = "<div align='center'>" . strtoupper($row['kode_trans']) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['no_spk']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_product']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_gudang']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nama_costcenter']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['qty_unit'], 4) . "</div>";
      $username = (!empty($GET_USER[$row['created_by']]['username'])) ? $GET_USER[$row['created_by']]['username'] : '-';
      $nestedData[]  = "<div align='left'>" . strtolower($username) . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y H:i:s', strtotime($row['created_date'])) . "</div>";

      $status = 'Waiting Confirm';
      $warna = 'blue';
      if ($row['sts_confirm'] == 'Y') {
        $status = 'Closed';
        $warna = 'green';
      }
      $nestedData[]  = "<div align='center'><span class='badge bg-" . $warna . "'>" . $status . "</span></div>";


      $release  = "";
      $print    = "";
      $edit    = "";
      $view  = "<button type='button' data-kode_trans='" . $row['kode_trans'] . "' data-tanda='detail' class='btn btn-sm btn-warning detail4' title='Detail' data-role='qtip'><i class='fa fa-eye'></i></button>";
      if ($row['sts_confirm'] == 'N' and $this->ENABLE_MANAGE) {
        $edit  = "&nbsp;<button type='button' data-kode_trans='" . $row['kode_trans'] . "' data-tanda='edit' class='btn btn-sm btn-success detail4' title='Confirm' data-role='qtip'><i class='fa fa-check'></i></button>";
      }
      if ($row['sts_confirm'] != 'N') {
        $print  = "&nbsp;<a href='" . base_url('subgudang_request_list/print_spk_request_assembly/' . $row['kode_trans']) . "' target='_blank' class='btn btn-sm btn-info' title='Print SPK Pengeluaran Material' data-role='qtip'><i class='fa fa-print'></i></a>";
      }
      $nestedData[]  = "<div align='left'>" . $view . $edit . $print . $release . "</div>";
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

  public function get_query_json_request_material_assembly($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.kode_trans,
              a.jumlah_mat_packing AS qty_packing,
              a.jumlah_mat AS qty_unit,
              a.created_by,
              a.created_date,
              c.nama_costcenter,
              d.nm_gudang,
              a.checked AS sts_confirm,
              e.no_spk,
              g.nama AS nm_product
            FROM
              warehouse_adjustment a
              LEFT JOIN ms_costcenter c ON a.kd_gudang_ke=c.id_costcenter
              LEFT JOIN warehouse d ON a.id_gudang_dari=d.id
              LEFT JOIN so_spk_assembly e ON a.no_ipp=e.id
              LEFT JOIN bom_header f ON e.no_bom=f.no_bom
              LEFT JOIN new_inventory_4 g ON f.id_product=g.code_lv4,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND a.category='request produksi assembly' AND d.desc='subgudang' AND (
              a.kode_trans LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.nm_gudang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nama_costcenter LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR e.no_spk LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR g.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'kode_trans',
      2 => 'd.nm_gudang',
      3 => 'c.nama_costcenter',
      4 => 'jumlah_mat_packing',
      5 => 'created_by',
      6 => 'created_date',
    );

    $sql .= " ORDER BY a.id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

}
