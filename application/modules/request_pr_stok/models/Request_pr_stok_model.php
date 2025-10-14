<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Request_pr_stok_model extends BF_Model
{

  protected $ENABLE_ADD;
  protected $ENABLE_MANAGE;
  protected $ENABLE_VIEW;
  protected $ENABLE_DELETE;

  public function __construct()
  {
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('PR_Stok.Add');
    $this->ENABLE_MANAGE  = has_permission('PR_Stok.Manage');
    $this->ENABLE_VIEW    = has_permission('PR_Stok.View');
    $this->ENABLE_DELETE  = has_permission('PR_Stok.Delete');
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

      $get_detail_pr = $this->db->get_where('material_planning_base_on_produksi_detail', ['so_number' => $row['so_number']])->result();

      $nm_detail = '';
      $qty_detail = '';
      foreach ($get_detail_pr as $item) {
        // $get_stok_data = $this->db->get_where('accessories', ['id' => $row['id_material']])->row();
        $this->db->select('a.stock_name, b.code');
        $this->db->from('accessories a');
        $this->db->join('ms_satuan b', 'b.id = a.id_unit_gudang', 'left');
        $this->db->where('a.id', $item->id_material);
        $get_stok_data = $this->db->get()->row();

        if (!empty($get_stok_data)) {
          $nm_detail = $nm_detail . $get_stok_data->stock_name . '<br>';
          $qty_detail = $qty_detail . number_format($item->propose_purchase, 2) . ' ' . ucfirst($get_stok_data->code) . '<br>';
        }
      }

      $kategori_pr = [];
      $this->db->select('c.nm_category as kategori');
      $this->db->from('material_planning_base_on_produksi_detail a');
      $this->db->join('accessories b', 'b.id = a.id_material', 'left');
      $this->db->join('accessories_category c', 'c.id = b.id_category', 'left');
      $this->db->where('a.so_number', $row['so_number']);
      $this->db->group_by('c.id');
      $get_kategori_pr = $this->db->get()->result();
      foreach ($get_kategori_pr as $item_kategori_pr) {
        $kategori_pr[] = $item_kategori_pr->kategori;
      }

      if (!empty($kategori_pr)) {
        $kategori_pr = implode(', ', $kategori_pr);
      } else {
        $kategori_pr = '';
      }

      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['no_pr']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($kategori_pr) . "</div>";
      $nestedData[] = "<div  align='left'>" . $nm_detail . "</div>";
      $nestedData[] = "<div  align='right'>" . $qty_detail . "</div>";
      $nestedData[] = "<div  align='left'>" . date('d F Y', strtotime($row['tgl_dibutuhkan'])) . "</div>";

      $getCheck = $this->db->get_where('material_planning_base_on_produksi_detail', array('so_number' => $row['so_number'], 'status_app' => 'N'))->result();

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
      if (COUNT($getCheck) <= 0) {
        $sts = 'Approved';
        $warna = 'green';
      }

      $nestedData[]    = "<div align='left'><span class='badge' style='background-color: " . $warna . ";'>" . $sts . "</span></div>";
      $nestedData[]    = "<div align='center'>" . $row['request_by'] . "</div>";
      $nestedData[]    = "<div align='center'>" . $row['request_date'] . "</div>";

      $approve  = "";
      $view  = "<a href='" . site_url($this->uri->segment(1)) . '/detail_planning/' . $row['so_number'] . "' class='btn btn-sm btn-warning' title='Detail PR' data-role='qtip'><i class='fa fa-eye'></i></a>";
      $edit   = "";
      if ($this->ENABLE_MANAGE and COUNT($getCheck) > 0) {
        $edit   = "<a href='" . site_url($this->uri->segment(1)) . '/edit_planning/' . $row['so_number'] . "' class='btn btn-sm btn-info' title='Edit PR' data-role='qtip'><i class='fa fa-edit'></i></a>";
      }

      $print = '<a href="' . site_url($this->uri->segment(1)) . '/PrintH2/' . $row['so_number'] . '" class="btn btn-sm btn-info" title="Print PR" target="_blank"><i class="fa fa-download"></i></a>';

      $nestedData[]  = "<div align='left'>" . $view . " " . $edit . " " . $approve . " " . $print . "</div>";
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

  public function get_query_approval_pr_material($product, $costcenter, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $costcenter_where = "";
    // if($costcenter != '0'){
    // $costcenter_where = " AND a.costcenter = '".$costcenter."'";
    // }

    $product_where = "";
    // if($product != '0'){
    // $product_where = " AND b.code_lv1 = '".$product."'";
    // }

    $sql = "SELECT
              a.*,
              b.nm_customer,
              e.nm_lengkap as request_by,
              DATE_FORMAT(a.created_date, '%d %M %Y') as request_date
            FROM
              material_planning_base_on_produksi a
              LEFT JOIN customer b ON a.id_customer = b.id_customer
              LEFT JOIN material_planning_base_on_produksi_detail c ON c.so_number = a.so_number
              LEFT JOIN accessories d ON d.id = c.id_material
              LEFT JOIN users e ON e.id_user = a.created_by
            WHERE 1=1 AND a.category='pr stok' AND a.booking_date IS NOT NULL AND (
              b.nm_customer LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.so_number LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.project LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.no_pr LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR d.stock_name LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR e.nm_lengkap LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            GROUP BY a.so_number
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'so_number',
      1 => 'so_number',
      2 => 'so_number',
      3 => 'no_pr',
      4 => 'so_number'
    );

    $sql .= " ORDER BY a.created_date DESC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  public function get_data_json_reorder_point()
  {
    // $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);

    $requestData  = $_REQUEST;
    $fetch      = $this->query_data_json_reorder_point(
      $requestData['category'],
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData    = $fetch['totalData'];
    $totalFiltered  = $fetch['totalFiltered'];
    $query      = $fetch['query'];

    $GET_SATUAN = get_list_satuan();

    $data  = array();
    $urut1  = 1;
    $urut2  = 0;
    foreach ($query->result_array() as $row) {
      $total_data     = $totalData;
      $start_dari     = $requestData['start'];
      $asc_desc       = $requestData['order'][0]['dir'];
      if ($asc_desc == 'asc') {
        $nomor = $urut1 + $start_dari;
      }
      if ($asc_desc == 'desc') {
        $nomor = ($total_data - $start_dari) - $urut2;
      }

      $tgl_now = date('Y-m-d');
      $tgl_next_month = date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));

      $qty_stock    = 0;
      $qty_booking  = 0;

      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='left'>" . $row['id_stock'] . "</div>";
      $nestedData[]  = "<div align='left'>" . $row['stock_name'] . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nama_level1']) . "</div>";

      $konversi = $row['konversi'];
      $qty_pack = 0;
      if ($konversi > 0 and $qty_stock > 0) {
        $qty_pack = $qty_stock / $konversi;
      }

      $satuan = (!empty($GET_SATUAN[$row['id_unit_gudang']]['code'])) ? $GET_SATUAN[$row['id_unit_gudang']]['code'] : '';

      $nestedData[]  = "<div align='right'>" . number_format($qty_pack, 2) . "</div>";
      $nestedData[]  = "<div align='center'>" . $satuan . "</div>";
      $nestedData[]  = "<div align='center' class='konversi'>" . number_format($konversi, 2) . "</div>";
      $nestedData[]  = "<div align='right'>" . number_format($qty_stock, 2) . "</div>";

      $nestedData[]  = "<div align='right'>" . number_format($row['min_stok'], 2) . "</div>";
      $nestedData[]  = "<div align='right'>" . number_format($row['max_stok'], 2) . "</div>";

      $outanding_pr   = 0;
      $nestedData[]  = "<div align='right'>" . number_format($outanding_pr, 2) . "</div>";

      // $kg_per_bulan 	= 0;
      // $reorder 		= ($row['min_stok']/30) * $kg_per_bulan;
      // $max_stock2 = ($row['max_stok']/30) * $kg_per_bulan;
      // $qtypr = 0; //semnetara NOL
      // $QTY_PR = $max_stock2 - ($qty_stock - $qty_booking) - $qtypr;
      // if($QTY_PR < 0){
      // 	$QTY_PR = '';
      // }

      $QTY_PR = '';
      if ($qty_stock < $row['min_stok']) {
        $QTY_PR = ($row['max_stok'] - ($qty_stock + $outanding_pr));
      }

      $purchase2 = (!empty($row['request'])) ? $row['request'] : $QTY_PR;

      $purchase_packing = 0;
      if ($konversi > 0 and $purchase2 > 0) {
        $purchase_packing = $purchase2 / $konversi;
      }

      $nestedData[]  = " <div align='left'>
									        <input type='text' name='purchase' id='purchase_" . $nomor . "' data-id_material='" . $row['id'] . "' data-no='" . $nomor . "' class='form-control input-sm text-right maskM changeSave' style='width:100%;' value='" . $purchase2 . "'>
								        </div>";
      $nestedData[]  = " <div align='center' class='propose_packing'>" . number_format($purchase_packing, 2) . "</div>
                          <script type='text/javascript'>$('.maskM').autoNumeric('init', {mDec: '2', aPad: false});</script>";
      $nestedData[]  = "<div align='center'>" . $satuan . "</div>";

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

  public function query_data_json_reorder_point($category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {
    $tanggal  = date('Y-m-d', strtotime('-1 month', strtotime(date('Y-m-d'))));
    $bulan    = ltrim(date('m', strtotime($tanggal)), '0');
    $tahun    = date('Y', strtotime($tanggal));

    $product_where = "";
    if ($category != '0') {
      $product_where = " AND a.id_category = '" . $category . "'";
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              z.nm_category AS nama_level1
            FROM
              accessories a
              LEFT JOIN accessories_category z ON a.id_category=z.id,
              (SELECT @row:=0) r
            WHERE 1=1 
              $product_where
              AND a.deleted_date IS NULL
              AND a.status = '1'
              AND (
                a.id_stock LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                OR a.stock_name LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              )
          ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'id_stock',
      2 => 'stock_name'
    );

    $sql .= " ORDER BY a.id, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  //NEW
  public function server_side_reorder_point_new()
  {

    $requestData  = $_REQUEST;
    $fetch      = $this->query_data_json_reorder_point_new(
      $requestData['category'],
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData    = $fetch['totalData'];
    $totalFiltered  = $fetch['totalFiltered'];
    $query      = $fetch['query'];

    $data  = array();
    $urut1  = 1;
    $urut2  = 0;
    $GET_KEBUTUHAN_PER_MONTH = get_kebutuhanPerMonth();
    $GET_WAREHOUSE_STOCK = getStokBarangAll();



    $this->db->select('SUM(a.request * a.price_ref) as total_price');
    $this->db->from('accessories a');
    if (!empty($requestData['category'])) {
      $this->db->where('a.id_category', $requestData['category']);
    }
    $get_total_price = $this->db->get()->row();

    $total_price = (!empty($get_total_price)) ? $get_total_price->total_price : 0;

    foreach ($query->result_array() as $row) {
      $total_data     = $totalData;
      $start_dari     = $requestData['start'];
      $asc_desc       = $requestData['order'][0]['dir'];
      if ($asc_desc == 'asc') {
        $nomor = $urut1 + $start_dari;
      }
      if ($asc_desc == 'desc') {
        $nomor = ($total_data - $start_dari) - $urut2;
      }

      $tgl_now = date('Y-m-d');
      // $tgl_next_month = date('Y-m-'.'20', strtotime('+1 month', strtotime($tgl_now)));

      $tgl_next_month = (!empty($row['tgl_dibutuhkan'])) ? $row['tgl_dibutuhkan'] : date('Y-m-' . '20', strtotime('+1 month', strtotime($tgl_now)));
      $spec_pr = (!empty($row['spec_pr'])) ? $row['spec_pr'] : '';
      $info_pr = (!empty($row['info_pr'])) ? $row['info_pr'] : '';

      $nestedData   = array();
      $nestedData[]  = "<div align='center'>" . $nomor . "</div>";
      $nestedData[]  = "<div align='left'>" . $row['stock_name'] . "</div>";

      $STOCK_WRH    = (!empty($GET_WAREHOUSE_STOCK[$row['id']]['stok'])) ? $GET_WAREHOUSE_STOCK[$row['id']]['stok'] : 0;
      $stock_oke     = (!empty($STOCK_WRH)) ? $STOCK_WRH : 0;
      $stock_oke2   = (!empty($STOCK_WRH)) ? $STOCK_WRH : 0;

      $konversi = ($row['konversi'] > 0) ? $row['konversi'] : 1;

      // $get_price_ref = $this->db->select('price_reference')->get_where('budget_rutin_detail', ['id_barang' => $row['id']])->row();
      $price_ref = $row['price_ref'];

      $kebutuhnMonth   = (!empty($GET_KEBUTUHAN_PER_MONTH[$row['id']]['kebutuhan'])) ? $GET_KEBUTUHAN_PER_MONTH[$row['id']]['kebutuhan'] : 0;
      $nestedData[]  = "<div align='right'>" . number_format($kebutuhnMonth) . "</div>";
      $nestedData[]  = "<div align='right'>" . number_format($stock_oke) . "</div>";
      $nestedData[]  = "<div align='right'>" . number_format(($kebutuhnMonth * 1.5)) . "</div>";
      $purchase = ($kebutuhnMonth) - $stock_oke2;
      $purchase2x = ($purchase < 0) ? 0 : $purchase;
      $purchase2 = (!empty($row['request'])) ? $row['request'] : $purchase2x;

      $purchase_value = ($purchase2 > 0) ? number_format($purchase2, 2) : '';

      $grand_total_val = ($purchase_value !== '') ? number_format($price_ref * $purchase2) : '';
      $grand_total_val2 = ($purchase_value !== '') ? ($price_ref * $purchase2) : 0;

      $purchase_value_pack = ($row['request_pack'] > 0) ? number_format($row['request_pack'], 2) : '';

      $unit_satuan   = get_name('ms_satuan', 'code', 'id', $row['id_unit']);
      $unit_sat = ($unit_satuan != '0') ? $unit_satuan : '';

      $nestedData[]  = "<div align='right'>
									<input type='text' name='purchase_" . $nomor . "' id='purchase_" . $nomor . "' value='" . $purchase_value . "' data-id='" . $row['id'] . "' data-no='" . $nomor . "' data-konversi='" . $row['konversi'] . "' class='form-control input-md text-right input_qty_satuan maskM changeSave purchase_" . $row['id'] . "' style='width:100%;' data-max_propose='" . ceil($kebutuhnMonth * 1.5) . "'>
								  </div><script type='text/javascript'>$('.maskM').autoNumeric('init', {mDec: '2', aPad: false});</script>";

      $nestedData[]  = "<div align='left'>
									<select id='satuan_" . $nomor . "' class='chosen_select form-control input-md'><option value='" . $row['id_unit'] . "'>" . strtoupper($unit_sat) . "</option></select>	
									<input type='hidden' name='tanggal_" . $nomor . "' id='tanggal_" . $nomor . "' data-id='" . $row['id'] . "' data-no='" . $nomor . "' class='form-control input-md tgl changeSave' style='width:100%;' readonly value='" . $tgl_next_month . "'></div>";

      $unit_packing   = get_name('ms_satuan', 'code', 'id', $row['id_unit_gudang']);
      $unit_pack = ($unit_packing != '0') ? $unit_packing : '';

      $nestedData[]  = "<div align='left'>
									<input type='text' name='info_" . $nomor . "' id='info_" . $nomor . "' data-id='" . $row['id'] . "' data-no='" . $nomor . "' class='form-control input-md changeSave' style='width:100%;' placeholder='- Keterangan -' value='" . $info_pr . "'></div>
									<style>.tgl{cursor:pointer;}</style>
									<script type='text/javascript'>
									$('.chosen_select').select2({width: '100%'});
									$('.tgl').datepicker({
										dateFormat : 'yy-mm-dd',
										changeMonth: true, 
										changeYear: true,
										minDate : 0
									});
									</script>";

      $nestedData[] = number_format($price_ref);
      $nestedData[] = '<div align="right">' . $grand_total_val . '</div>';
      $data[] = $nestedData;
      $urut1++;
      $urut2++;

      // $total_price += $grand_total_val2;
    }

    $json_data = array(
      "draw"              => intval($requestData['draw']),
      "recordsTotal"      => intval($totalData),
      "recordsFiltered"   => intval($totalFiltered),
      "data"              => $data,
      "total_price" => $total_price
    );

    echo json_encode($json_data);
  }

  public function query_data_json_reorder_point_new($category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $where_inventory = "";
    if (!empty($category)) {
      $where_inventory = " AND a.id_category = '" . $category . "' ";
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nm_category AS category_type
            FROM
              accessories a  
              LEFT JOIN accessories_category b ON a.id_category=b.id,
              (SELECT @row:=0) r
              WHERE 1=1 AND a.status = '1'  AND a.deleted_date IS NULL
              " . $where_inventory . "
            AND (
              a.id_stock LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR a.stock_name LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.nm_category LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                )
		      ";

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'id_stock',
      2 => 'stock_name'
    );

    $sql .= " ORDER BY a.id ASC, " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  public function getPRStockHeader($id_pr)
  {
    $get_header = $this->db->get_where('material_planning_base_on_produksi', ['so_number' => $id_pr])->row();

    return $get_header;
  }

  public function getPRStockDetail($id_pr)
  {
    $this->db->select('a.*, b.stock_name, b.konversi, b.spec, c.qty_stock, d.nm_category, e.code as satuan, f.kebutuhan_month as qty_kebutuhan');
    $this->db->from('material_planning_base_on_produksi_detail a');
    $this->db->join('accessories b', 'b.id = a.id_material', 'left');
    $this->db->join('warehouse_stock c', 'c.id_material = a.id_material', 'left');
    $this->db->join('accessories_category d', 'd.id = b.id_category', 'left');
    $this->db->join('ms_satuan e', 'e.id = b.id_unit_gudang', 'left');
    $this->db->join('budget_rutin_detail f', 'f.id_barang = b.id', 'left');
    $this->db->where('a.so_number', $id_pr);
    $this->db->group_by('b.id');
    $get_detail = $this->db->get()->result();

    return $get_detail;
  }
}
