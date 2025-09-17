<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Stock_origa_model extends BF_Model
{

  public function __construct()
  {
    parent::__construct();

    $this->id_user  = $this->auth->user_id();
    $this->datetime = date('Y-m-d H:i:s');

    $this->ENABLE_ADD     = has_permission('Stock_Milik_Origa.Add');
    $this->ENABLE_MANAGE  = has_permission('Stock_Milik_Origa.Manage');
    $this->ENABLE_VIEW    = has_permission('Stock_Milik_Origa.View');
    $this->ENABLE_DELETE  = has_permission('Stock_Milik_Origa.Delete');
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

  public function get_json_stock_milik_origa()
  {
    $controller      = ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData    = $_REQUEST;
    $fetch          = $this->get_query_json_stock_milik_origa(
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
      $nestedData[]  = "<div align='left' title='" . $row['no_bom'] . "'>" . strtoupper($row['category_bom']) . "</div>";
      $nestedData[]  = "<div align='left'>" . strtoupper($row['nama_level4'] . $variant_product . $color_product . $surface_product) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['stock_ng']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['stock_akhir']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['booking_akhir']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['stock_akhir'] - $row['booking_akhir']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['min_stok']) . "</div>";
      $nestedData[]  = "<div align='center'>" . number_format($row['max_stok']) . "</div>";
      $propose = 0;
      if ($row['stock_akhir'] - $row['booking_akhir'] < $row['min_stok']) {
        $propose = $row['max_stok'];
      }
      $nestedData[]  = "<div align='center'>" . number_format($propose) . "</div>";

      $edit  = "";
      $delete  = "";

      $view  = "<a href='" . site_url($this->uri->segment(1)) . '/spk_stok_detail/' . $row['code_lv4'] . '/' . $row['id'] . "' class='btn btn-sm btn-warning' title='Detail' data-role='qtip'><i class='fa fa-eye'></i></a>";
      $barcode  = "<a href='" . site_url($this->uri->segment(1)) . '/qrcode/' . $row['code_lv4'] . '/' . $row['no_bom'] . "' target='_blank' class='btn btn-sm btn-default' title='QR Code' data-role='qtip'><i class='fa fa-qrcode'></i></a>";
      if ($this->ENABLE_ADD) {
        $edit  = "<a href='" . site_url($this->uri->segment(1)) . '/spk_stok/' . $row['code_lv4'] . '/' . $row['id'] . "' class='btn btn-sm btn-primary' title='SPK' data-role='qtip'><i class='fa fa-hand-pointer-o'></i></a>";
      }
      if ($this->ENABLE_DELETE) {
        $delete  = "<button type='button' data-id='" . $row['id'] . "' class='btn btn-sm btn-danger hapus' title='Delete Stock' data-role='qtip'><i class='fa fa-trash'></i></button>";
      }
      $view  = "";
      $nestedData[]  = "<div align='center'>" . $view . " " . $edit . " " . $delete . " " . $barcode . "</div>";
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

  public function get_query_json_stock_milik_origa($product, $costcenter, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
  {

    $costcenter_where = "";
    // if($costcenter != '0'){
    // $costcenter_where = " AND a.costcenter = '".$costcenter."'";
    // }

    $product_where = "";
    if ($product != '0') {
      $product_where = " AND b.code_lv1 = '" . $product . "'";
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              (SELECT ng_stock FROM stock_product WHERE id = MAX(a.id)) AS stock_ng,
              (SELECT actual_stock FROM stock_product WHERE id = MAX(a.id)) AS stock_akhir,
              (SELECT booking_stock FROM stock_product WHERE id = MAX(a.id)) AS booking_akhir,
              b.nama AS nama_level4,
              c.variant_product,
              c.color AS color_product,
              c.surface AS surface_product,
              b.min_stok,
              b.max_stok,
              c.category AS category_bom,
              c.no_bom
            FROM
              stock_product a
              INNER JOIN bom_header c ON a.no_bom=c.no_bom
              LEFT JOIN new_inventory_4 b ON a.code_lv4=b.code_lv4,
              (SELECT @row:=0) r
            WHERE 1=1 AND c.category IN ('grid standard','standard','ftackel') " . $costcenter_where . " " . $product_where . " AND a.deleted_date IS NULL AND (
              a.code_lv4 LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.category LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.variant_product LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.color LIKE '%" . $this->db->escape_like_str($like_value) . "%'
              OR c.surface LIKE '%" . $this->db->escape_like_str($like_value) . "%'
            )
            GROUP BY a.no_bom, a.code_lv4
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'b.nama'
    );

    $sql .= " ORDER BY  " . $columns_order_by[$column_order] . " " . $column_dir . " ";
    $sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  public function approveSO($ArrSODetail, $no_so)
  {
    $dateNow      = date('Y-m-d');
    $due_date_set = date('Y-m-d', strtotime('+14 days', strtotime($dateNow)));
    $getSOHeader  = $this->db->get_where('tr_sales_order', array('no_so' => $no_so))->result_array();
    $due_date     = (!empty($getSOHeader[0]['delivery_date'] and $getSOHeader[0]['delivery_date'] != '0000-00-00')) ? $getSOHeader[0]['delivery_date'] : $due_date_set;
    $id_customer  = (!empty($getSOHeader[0]['id_customer'])) ? $getSOHeader[0]['id_customer'] : NULL;
    $project      = (!empty($getSOHeader[0]['project'])) ? $getSOHeader[0]['project'] : NULL;

    $GET_PRODUCT = get_inventory_lv4();
    $nomor = 0;
    $ArrProduksi = [];
    $ArrUpdateStokNew = [];
    foreach ($ArrSODetail as $value) {
      $nomor++;
      $request_production = 0;
      if ($value->stok_tersedia < $value->qty) {
        // echo 'masuk 1<br>';
        $request_production = ($value->qty);
        $code_lv4 = $value->id_category3;
        if (!empty($GET_PRODUCT[$code_lv4]['nama'])) {
          // echo 'masuk 2<br>';
          $getBOM = $this->db->get_where('stock_product', array('code_lv4' => $code_lv4))->result_array();
          // if(!empty($getBOM[0]['no_bom'])){
          $no_bom = (!empty($value->no_bom)) ? $value->no_bom : 0;
          // echo 'masuk 3<br>';
          $ArrProduksi[$nomor]['code_lv4']      = $code_lv4;
          $ArrProduksi[$nomor]['nama_product']  = $GET_PRODUCT[$code_lv4]['nama'];
          $ArrProduksi[$nomor]['propose']       = $request_production;
          $ArrProduksi[$nomor]['due_date']      = $due_date;
          $ArrProduksi[$nomor]['so_customer']   = $no_so;
          $ArrProduksi[$nomor]['id_customer']   = $id_customer;
          $ArrProduksi[$nomor]['project']       = $project;
          $ArrProduksi[$nomor]['no_bom_planning']        = $no_bom;
          $ArrProduksi[$nomor]['created_by']    = $this->id_user;
          $ArrProduksi[$nomor]['created_date']  = $this->datetime;
          // }
        }
      }

      $qty_booking  = $value->qty_so;
      $code_lv4     = $value->id_category3;
      $no_bom       = $value->no_bom;

      $ArrUpdateStokNew[$nomor]['code_lv4'] = $code_lv4;
      $ArrUpdateStokNew[$nomor]['no_bom'] = $no_bom;
      $ArrUpdateStokNew[$nomor]['stok_aktual'] = 0;
      $ArrUpdateStokNew[$nomor]['stok_booking'] = $qty_booking;
      $ArrUpdateStokNew[$nomor]['stok_downgrade'] = 0;
      $ArrUpdateStokNew[$nomor]['qty'] = $qty_booking;
    }

    // print_r($ArrProduksi);
    // exit;

    if (!empty($ArrProduksi)) {
      // $this->generateSalesOrderProduksi($ArrProduksi);
      $this->db->insert_batch('so_internal_request', $ArrProduksi);
    }

    if (!empty($ArrUpdateStokNew)) {
      history_product($ArrUpdateStokNew, 'plus', $no_so, 'penambahan booking so');
    }
  }

  public function generateSalesOrderProduksi($ArrProduksi)
  {
    foreach ($ArrProduksi as $keyPro => $valuePro) {
      $code_lv4      = $valuePro['code_lv4'];
      $nama_product  = $valuePro['nama_product'];
      $no_bom        = $valuePro['no_bom'];
      $due_date      = $valuePro['due_date'];
      $propose      = $valuePro['propose'];
      $id_customer  = $valuePro['id_customer'];
      $project      = $valuePro['project'];
      $so_customer  = $valuePro['so_customer'];

      $Y = date('y');
      $SQL      = "SELECT MAX(so_number) as maxP FROM so_internal WHERE so_number LIKE 'SOI" . $Y . "%' ";
      $result    = $this->db->query($SQL)->result_array();
      $angkaUrut2    = $result[0]['maxP'];
      $urutan2      = (int)substr($angkaUrut2, 5, 4);
      $urutan2++;
      $urut2        = sprintf('%04s', $urutan2);
      $so_number    = "SOI" . $Y . $urut2;

      $ArrHeader = array(
        'so_number'       => $so_number,
        'so_customer'     => $so_customer,
        'id_customer'     => $id_customer,
        'project'         => $project,
        'code_lv4'        => $code_lv4,
        'no_bom'          => $no_bom,
        'nama_product'    => strtolower($nama_product),
        'due_date'        => $due_date,
        'propose'         => $propose,
        'created_by'      => $this->id_user,
        'created_date'    => $this->datetime
      );

      //BOM material
      $GET_LEVEL4 = get_inventory_lv4();
      $GET_LEVEL1 = get_list_inventory_lv1('material');
      $dataBOM = $this->db->get_where('bom_detail', array('no_bom' => $no_bom))->result_array();
      $ArrBOMDetail = [];
      $ArrPlanningDetail = [];
      $SUM_PLANNING = 0;
      if (!empty($dataBOM)) {
        foreach ($dataBOM as $key => $value) {
          $ArrBOMDetail[$key]['so_number'] = $so_number;
          $ArrBOMDetail[$key]['code_material'] = $value['code_material'];
          $ArrBOMDetail[$key]['weight'] = $value['weight'];

          $code_lv4   = (!empty($GET_LEVEL4[$value['code_material']]['code_lv1'])) ? $GET_LEVEL4[$value['code_material']]['code_lv1'] : 0;
          $type_name  = (!empty($GET_LEVEL1[$code_lv4]['nama'])) ? $GET_LEVEL1[$code_lv4]['nama'] : 0;

          $CHECK_FTACKEL = substr($no_bom, 0, 3);
          if ($CHECK_FTACKEL == 'BFT') {
            $type_name  = $value['spk'];
          }

          $ArrBOMDetail[$key]['code_lv1'] = $code_lv4;
          $ArrBOMDetail[$key]['type_name'] = $type_name;

          //Planning
          $qty_plan = $value['weight'] * $propose;
          $ArrPlanningDetail[$key]['so_number'] = $so_number;
          $ArrPlanningDetail[$key]['id_material'] = $value['code_material'];
          $ArrPlanningDetail[$key]['qty_order'] = $qty_plan;
          $SUM_PLANNING += $qty_plan;
        }
      }

      //SO Produksi
      $ArrPlanning = array(
        'so_number' => $so_number,
        'no_pr'   => generateNoPR(),
        'tgl_so' => date('Y-m-d'),
        'id_customer' => $id_customer,
        'project' => $project,
        'qty_order' => $SUM_PLANNING,
        'created_by' => $this->id_user,
        'created_date' => $this->datetime
      );

      // print_r($ArrBOMDetail);
      // exit;

      $this->db->trans_start();
      $this->db->insert('so_internal', $ArrHeader);
      $this->db->insert('material_planning_base_on_produksi', $ArrPlanning);
      if (!empty($ArrBOMDetail)) {
        $this->db->insert_batch('so_internal_material', $ArrBOMDetail);
      }
      if (!empty($ArrPlanningDetail)) {
        $this->db->insert_batch('material_planning_base_on_produksi_detail', $ArrPlanningDetail);
      }
      $this->db->trans_complete();

      if ($this->db->trans_status() === FALSE) {
        $this->db->trans_rollback();
      } else {
        $this->db->trans_commit();
        history("Create so internal : " . $so_number);
      }
    }
  }
}
