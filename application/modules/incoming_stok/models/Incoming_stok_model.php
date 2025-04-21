<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Incoming_stok_model extends BF_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Incoming_Stok.Add');
    $this->ENABLE_MANAGE  = has_permission('Incoming_Stok.Manage');
    $this->ENABLE_VIEW    = has_permission('Incoming_Stok.View');
    $this->ENABLE_DELETE  = has_permission('Incoming_Stok.Delete');
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

      $no_po = [];
      $get_no_po =  $this->db->query("SELECT a.no_surat FROM tr_purchase_order a WHERE a.no_po IN ('" . str_replace(",", "','", $row['no_ipp']) . "')")->result();
      foreach ($get_no_po as $item) {
        $no_po[] = $item->no_surat;
      }
      $no_po = implode(', ', $no_po);

      $no_pr = [];
      $get_no_pr = $this->db->query("
          SELECT
            c.no_pr
          FROM
            dt_trans_po a
            JOIN tr_purchase_order d ON d.no_po = a.no_po
            JOIN material_planning_base_on_produksi_detail b ON b.id = a.idpr
            JOIN material_planning_base_on_produksi c ON c.so_number = b.so_number
          WHERE
            d.no_surat IN ('".str_replace(",", "','", str_replace(', ', ',', $no_po))."')
          GROUP BY c.no_pr
      ")->result();
      foreach ($get_no_pr as $item_no_pr) {
        $no_pr[] = $item_no_pr->no_pr;
      }

      if (!empty($no_pr)) {
        $no_pr = implode(', ', $no_pr);
      } else {
        $no_pr = '';
      }

      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='center'>" . strtoupper($row['kode_trans']) . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y', strtotime($row['tanggal'])) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($no_po) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($no_pr) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nm_gudang']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['qty_unit'], 2) . "</div>";
      $nestedData[]  = "<div align='left'>" . $row['pic'] . "</div>";
      $username = (!empty($GET_USER[$row['created_by']]['nama'])) ? $GET_USER[$row['created_by']]['nama'] : '-';
      $nestedData[]  = "<div align='left'>" . $username . "</div>";
      $nestedData[]  = "<div align='center'>" . date('d-M-Y H:i:s', strtotime($row['created_date'])) . "</div>";

      $status = 'Waiting Confirm';
      $warna = 'blue';
      if ($row['sts_confirm'] == 'Y') {
        $status = 'Closed';
        $warna = 'green';
      }
      // $nestedData[]	= "<div align='center'><span class='badge bg-".$warna."'>".$status."</span></div>";


      $release  = "";
      $print    = "";
      $edit    = "";
      $view  = "<button type='button' data-kode_trans='" . $row['kode_trans'] . "' data-tanda='detail' class='btn btn-sm btn-warning detail' title='Detail' data-role='qtip'><i class='fa fa-eye'></i></button>";
      // if($row['sts_confirm'] == 'N'  AND $this->ENABLE_MANAGE){
      //   $edit	= "&nbsp;<button type='button' data-kode_trans='".$row['kode_trans']."' data-tanda='edit' class='btn btn-sm btn-primary detail' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></button>";
      // }
      if ($row['sts_confirm'] == 'N') {
        $print  = "&nbsp;<a href='" . base_url('incoming_stok/print_incoming_stok/' . $row['kode_trans']) . "' target='_blank' class='btn btn-sm btn-info' title='Print SPK Permintaan Material' data-role='qtip'><i class='fa fa-print'></i></a>";
      }
      $nestedData[]  = "<div align='center'>" . $view . $edit . $print . $release . "</div>";
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
              a.no_ipp,
              e.no_surat AS no_surat,
              a.pic,
              a.id_dept,
              a.tanggal,
              a.jumlah_mat AS qty_unit,
              a.created_by,
              a.created_date,
              c.nm_gudang AS nm_gudang,
              a.checked AS sts_confirm
            FROM
              warehouse_adjustment a
              LEFT JOIN warehouse c ON a.id_gudang_ke=c.id
              LEFT JOIN tr_purchase_order e ON a.no_ipp=e.no_po,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND a.category='incoming stok' AND (
              a.kode_trans LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.nm_gudang LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR e.no_surat LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'kode_trans',
      2 => 'tanggal',
      3 => 'e.no_surat',
      4 => 'c.nm_gudang',
      5 => 'a.jumlah_mat',
      6 => 'pic',
      7 => 'created_by',
      8 => 'created_date',
    );

    $sql .= " ORDER BY a.id DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }
}
