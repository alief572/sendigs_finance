<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Production_model extends BF_Model
{

  public function __construct()
  {
    parent::__construct();
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

      $checkCLose = (!empty(checkInputProduksiQty($row['id'])[$row['id']])) ? checkInputProduksiQty($row['id'])[$row['id']] : 0;

      $variant_product   = (!empty($row['variant_product'])) ? '; Variant ' . $row['variant_product'] : '';
      $color_product     = (!empty($row['color_product'])) ? '; Color ' . $row['color_product'] : '';
      $surface_product   = (!empty($row['surface_product'])) ? '; Surface ' . $row['surface_product'] : '';

      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nama_level4'] . $variant_product . $color_product . $surface_product) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['so_number']) . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['no_spk']) . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y', strtotime($row['plan_date'])) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nama_costcenter']) . "</div>";
      $nestedData[]  = "<div align='center' title='" . $checkCLose . "'>" . number_format($row['qty']) . "</div>";

      $close_by = (!empty($GET_USER[$row['close_by']]['nama'])) ? $GET_USER[$row['close_by']]['nama'] : '';
      $close_date = (!empty($row['close_date'])) ? date('d-M-Y H:i', strtotime($row['close_date'])) : '';
      $nestedData[]  = "<div align='left'>" . $close_by . "</div>";
      $nestedData[]  = "<div align='center'>" . $close_date . "</div>";

      $sts_label = '';
      $release = "";
      $print = "";
      $excel = "";

      $LinkPembeda = ($row['code_lv1'] == 'P123000009') ? 'requestFtackle' : 'request';


      if ($checkCLose == (int)$row['qty']) {
        $sts_subgudang = "<span class='badge bg-purple'>" . date('d-M-Y', strtotime($row['close_date'])) . "</span>";
        $sts_produksi = "<br><span class='badge bg-green'>Close</span>";

        $sts_label = $sts_subgudang . $sts_produksi;

        $release  = "<a href='" . base_url('production/history_input_aktual/' . $row['id']) . "' class='btn btn-sm btn-success' title='History Aktual Produksi' data-role='qtip'><i class='fa fa-history'></i></a>";
        $excel    = "&nbsp;<a href='" . base_url('production/history_input_aktual_excel/' . $row['id']) . "' target='_blank' class='btn btn-sm btn-info' title='Download History' data-role='qtip'><i class='fa fa-file-excel-o'></i></a>";
      } else {
        $sts_subgudang = "<span class='badge bg-blue'>Waiting Input</span>";
        if ($row['sts_subgudang'] == 'N') {
          $sts_subgudang = "<span class='badge bg-red'>Waiting Subgudang</span>";
        }
        if ($checkCLose > 0) {
          $sts_subgudang = "<span class='badge bg-purple'>Parsial</span>";
        }
        if (empty(checkInputMixingQty($row['id'])) and $row['code_lv1'] != 'P123000009') {
          $sts_subgudang = "<span class='badge bg-red'>Waiting Input Mixing</span>";
        }

        $sts_label = $sts_subgudang;

        if ($row['code_lv1'] == 'P123000009' and $row['sts_close'] == 'Y') {
          $sts_subgudang = "<span class='badge bg-purple'>" . date('d-M-Y', strtotime($row['close_date'])) . "</span>";
          $sts_produksi = "<br><span class='badge bg-green'>Close</span>";

          $release  = "<a href='" . base_url('production/history_input_aktual_ftackle/' . $row['id']) . "' class='btn btn-sm btn-success' title='History Aktual Produksi' data-role='qtip'><i class='fa fa-history'></i></a>";
          $excel    = "&nbsp;<a href='" . base_url('production/history_input_aktual_excel_ftackle/' . $row['id']) . "' target='_blank' class='btn btn-sm btn-info' title='Download History' data-role='qtip'><i class='fa fa-file-excel-o'></i></a>";
          $excel    = "";

          $sts_label = $sts_subgudang . $sts_produksi;
        } else {
          if ($row['sts_subgudang'] == 'Y' or $row['sts_subgudang'] == 'P') {
            if (!empty(checkInputMixingQty($row['id']))) {
              $release  = "<button type='button' class='btn btn-sm btn-primary " . $LinkPembeda . "' data-id='" . $row['id'] . "' title='Input Aktual Produksi' data-role='qtip'><i class='fa fa-edit'></i></button>";
            }
            if ($row['code_lv1'] == 'P123000009') {
              $release  = "<button type='button' class='btn btn-sm btn-primary " . $LinkPembeda . "' data-id='" . $row['id'] . "' title='Input Aktual Produksi' data-role='qtip'><i class='fa fa-edit'></i></button>";
            }
          }
        }

        if ($row['sts_close'] == 'Y' and !empty($row['close_date']) and $row['code_lv1'] != 'P123000009') {
          $sts_subgudang = "<span class='badge bg-purple'>" . date('d-M-Y', strtotime($row['close_date'])) . "</span>";
          $sts_produksi = "<br><span class='badge bg-green'>Close</span>";

          $sts_label = $sts_subgudang . $sts_produksi;

          $release  = "<a href='" . base_url('production/history_input_aktual/' . $row['id']) . "' class='btn btn-sm btn-success' title='History Aktual Produksi' data-role='qtip'><i class='fa fa-history'></i></a>";
          $excel    = "&nbsp;<a href='" . base_url('production/history_input_aktual_excel/' . $row['id']) . "' target='_blank' class='btn btn-sm btn-info' title='Download History' data-role='qtip'><i class='fa fa-file-excel-o'></i></a>";
        }
      }

      $nestedData[]  = "<div align='left'>" . $sts_label . "</div>";



      if (empty($row['close_date'])) {
      }
      // else{
      $print  = "&nbsp;<a href='" . base_url('production/print_record_production/' . $row['id']) . "' target='_blank' class='btn btn-sm btn-warning' title='Record Production' data-role='qtip'><i class='fa fa-print'></i></a>";
      // }
      $nestedData[]  = "<div align='left'>" . $release . $print . $excel . "</div>";
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
    if ($sales_order == '1') {
      $sales_order_where = " AND b.sts_close = 'N' AND b.close_date IS NULL";
    }
    if ($sales_order == '2') {
      $sales_order_where = " AND b.sts_close = 'N' AND b.close_date IS NULL";
    }
    if ($sales_order == '3') {
      $sales_order_where = " AND b.sts_close = 'P' AND b.close_date IS NULL AND b.close_by IS NOT NULL";
    }
    if ($sales_order == '4') {
      $sales_order_where = " AND b.sts_close = 'Y' AND b.close_date IS NOT NULL";
    }

    $code_lv1_where = "";
    if ($code_lv1 != '0') {
      $code_lv1_where = " AND z.code_lv1 = '" . $code_lv1 . "'";
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              b.id,
              z.nama AS nama_level4,
              d.variant_product,
              d.color AS color_product,
              d.surface AS surface_product,
              a.so_number,
              b.no_spk,
              b.tanggal AS plan_date,
              b.id_costcenter,
              b.qty,
              b.created_by,
              b.created_date,
              c.nama_costcenter,
              b.sts_request,
              b.sts_subgudang,
              b.sts_produksi,
              b.kode_det,
              b.close_date,
              b.close_by,
              b.sts_close,
              z.code_lv1
            FROM
              so_internal_spk b
              LEFT JOIN so_internal a ON a.id=b.id_so AND b.status_id = '1'
              LEFT JOIN new_inventory_4 z ON a.code_lv4 = z.code_lv4
              LEFT JOIN ms_costcenter c ON b.id_costcenter=c.id_costcenter
              LEFT JOIN bom_header d ON a.no_bom=d.no_bom,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND b.sts_request='Y' AND b.status_id = '1' " . $sales_order_where . " " . $code_lv1_where . " AND (
              a.code_lv4 LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR z.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.variant_product LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.color LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.surface LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.so_number LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.no_spk LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nama_costcenter LIKE '%" . $this->db->escape_like_str($like_value) . "%'
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
      7 => 'b.close_by',
      8 => 'b.close_date',
    );

    $sql .= " ORDER BY b.id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }
}
