<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Warehouse_product_model extends BF_Model{

  public function __construct(){
    parent::__construct();
  }

  public function get_data($table,$where_field='',$where_value=''){
    if($where_field !='' && $where_value!=''){
      $query = $this->db->get_where($table, array($where_field=>$where_value));
    }else{
      $query = $this->db->get($table);
    }

    return $query->result();
  }

  public function get_data_group($table,$where_field='',$where_value='',$where_group=''){
    if($where_field !='' && $where_value!=''){
      $query = $this->db->group_by($where_group)->get_where($table, array($where_field=>$where_value));
    }else{
      $query = $this->db->get($table);
    }

    return $query->result();
  }

  //WIP Product
  public function get_json_wip(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_wip(
      $requestData['product'],
      $requestData['costcenter'],
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData			= $fetch['totalData'];
    $totalFiltered	= $fetch['totalFiltered'];
    $query					= $fetch['query'];

    $data	= array();
    $urut1  = 1;
    $urut2  = 0;
    foreach($query->result_array() as $row){
      $total_data     = $totalData;
      $start_dari     = $requestData['start'];
      $asc_desc       = $requestData['order'][0]['dir'];
      if($asc_desc == 'asc'){
        $nomor = $urut1 + $start_dari;
      }
      if($asc_desc == 'desc'){
        $nomor = ($total_data - $start_dari) - $urut2;
      }
      //Next Costcenter
      $next_costcenter = get_next_costcenter($row['id_product'], $row['costcenter']);
      $daycode_filter = $this->db->query(" SELECT
                                      a.code
                                    FROM
                                      report_produksi_daily_detail a
                                      LEFT JOIN report_produksi_daily_header b ON a.id_produksi=b.id_produksi
                                    WHERE
                                      b.id_costcenter='".$next_costcenter."'
                                      AND a.id_produksi_h=b.id_produksi_h
                                      AND a.id_product='".$row['id_product']."'
                                      AND a.ket='good'
                                      AND a.sts_daycode='N'
                                    GROUP BY a.code ")->result_array();
      $dtListArray2 = array();
      foreach($daycode_filter AS $val2 => $valx2){
        $dtListArray2[$val2] = $valx2['code'];
      }
      $dtImplode2	= "('".implode("','", $dtListArray2)."')";
      //List Costcenter
      $daycode = $this->db->query(" SELECT
                                      a.code
                                    FROM
                                      report_produksi_daily_detail a
                                      LEFT JOIN report_produksi_daily_header b ON a.id_produksi=b.id_produksi
                                    WHERE
                                      b.id_costcenter='".$row['costcenter']."'
                                      AND a.id_produksi_h=b.id_produksi_h
                                      AND a.id_product='".$row['id_product']."'
                                      AND a.ket='good'
                                      AND a.sts_daycode='N'
                                      AND a.code NOT IN ".$dtImplode2."
                                    GROUP BY a.code ")->result_array();
      $dtListArray = array();
      foreach($daycode AS $val => $valx){
        $dtListArray[$val] = $valx['code'];
      }
      $dtImplode	= implode(", ", $dtListArray);

      //Bef Costcenter
      $next_costcenter2 = get_before_costcenter_warehouse($row['id_product'], $row['costcenter']);
      $daycode_filter2 = $this->db->query(" SELECT
                                      a.code
                                    FROM
                                      report_produksi_daily_detail a
                                      LEFT JOIN report_produksi_daily_header b ON a.id_produksi=b.id_produksi
                                    WHERE
                                      b.id_costcenter='".$next_costcenter2."'
                                      AND a.id_produksi_h=b.id_produksi_h
                                      AND a.id_product='".$row['id_product']."'
                                      AND a.ket='good'
                                      AND a.sts_daycode='N'
                                    GROUP BY a.code ")->result_array();
      $dtListArray22 = array();
      foreach($daycode_filter2 AS $val2 => $valx2){
        $dtListArray22[$val2] = $valx2['code'];
      }
      $dtImplode22	= "('".implode("','", $dtListArray22)."')";
      //List Costcenter
      $daycodeBef = $this->db->query(" SELECT
                                      a.code
                                    FROM
                                      report_produksi_daily_detail a
                                      LEFT JOIN report_produksi_daily_header b ON a.id_produksi=b.id_produksi
                                    WHERE
                                      b.id_costcenter='".$row['costcenter']."'
                                      AND a.id_produksi_h=b.id_produksi_h
                                      AND a.id_product='".$row['id_product']."'
                                      AND a.ket='good'
                                      AND a.sts_daycode='N'
                                      AND a.code NOT IN ".$dtImplode22."
                                    GROUP BY a.code ")->result_array();
      $dtListArrayBef = array();
      foreach($daycodeBef AS $val => $valx){
        $dtListArrayBef[$val] = $valx['code'];
      }
      $dtImplodeBef	= implode(", ", $dtListArrayBef);

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama_costcenter']))."</div>";
      $stock_wip = $row['qty_stock'];
      if(get_last_costcenter_warehouse($row['id_product']) == $row['costcenter']){
        $stock_wip = get_stock($row['id_product']);
      }
      $nestedData[]	= "<div align='center'>".$stock_wip."</div>";
      $nestedData[]	= "<div align='center'>".get_antrian_wip($row['id_product'], $row['costcenter'])."</div>";
      $nestedData[]	= "<div align='center'>".count($dtListArray)."</div>";
      $status = "Oke";
      $warna = "green";
      if(COUNT($dtListArray) <> $stock_wip){
        $status = "Check";
        $warna = "red";
      }
      $nestedData[]	= "<div align='center'><span class='badge bg-".$warna."'>".$status."</span></div>";
      $nestedData[]	= "<div align='left'>".$dtImplode."</div>";

      $daycode_borke = '';
      if($row['costcenter'] <> 'CC2000012'){
        $daycode_borke = $dtImplodeBef;
      }
      $nestedData[]	= "<div align='left'>".$daycode_borke."</div>";

      $data[] = $nestedData;
      $urut1++;
      $urut2++;
    }

    $json_data = array(
      "draw"            	=> intval( $requestData['draw'] ),
      "recordsTotal"    	=> intval( $totalData ),
      "recordsFiltered" 	=> intval( $totalFiltered ),
      "data"            	=> $data
    );

    echo json_encode($json_data);
  }

  public function get_query_json_wip($product, $costcenter, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $costcenter_where = "";
    if($costcenter != '0'){
    $costcenter_where = " AND a.costcenter = '".$costcenter."'";
    }

    $product_where = "";
    if($product != '0'){
    $product_where = " AND a.id_product = '".$product."'";
    }

    $sql = "
      SELECT
        (@row:=@row+1) AS nomor,
        a.*,
        c.nama,
        d.nama_costcenter,
        (SELECT x.urut FROM cycletime_fast x WHERE x.id_product=a.id_product AND x.costcenter=a.costcenter LIMIT 1 ) AS urut
      FROM
        warehouse_product a
        LEFT JOIN ms_inventory_category2 c ON a.id_product = c.id_category2
        LEFT JOIN ms_costcenter d ON a.costcenter = d.id_costcenter,
        (SELECT @row:=0) r
      WHERE a.category='order' AND
        a.id_product <> '0' ".$costcenter_where." ".$product_where."
        AND (
          c.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
          OR d.nama_costcenter LIKE '%".$this->db->escape_like_str($like_value)."%'
        )
    ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'nama',
      2 => 'nama_costcenter'
    );

    $sql .= " ORDER BY a.id_product ASC, urut ASC,  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  //STOCK
  public function get_json_stock(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_stock(
      $requestData['product'],
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData			= $fetch['totalData'];
    $totalFiltered	= $fetch['totalFiltered'];
    $query					= $fetch['query'];

    $data	= array();
    $urut1  = 1;
    $urut2  = 0;
    foreach($query->result_array() as $row){
      // if(get_last_costcenter_warehouse($row['id_product']) == $row['id_costcenter']){
        $total_data     = $totalData;
        $start_dari     = $requestData['start'];
        $asc_desc       = $requestData['order'][0]['dir'];
        if($asc_desc == 'asc'){
          $nomor = $urut1 + $start_dari;
        }
        if($asc_desc == 'desc'){
          $nomor = ($total_data - $start_dari) - $urut2;
        }

        $nestedData 	= array();
        $costcenter_last = get_last_costcenter_warehouse($row['id_product']);
        $daycode = $this->db->query(" SELECT
                                        a.code
                                      FROM
                                        report_produksi_daily_detail a
                                        LEFT JOIN report_produksi_daily_header b ON a.id_produksi=b.id_produksi
                                      WHERE
                                        b.id_costcenter='".$costcenter_last."'
                                        AND a.id_produksi_h=b.id_produksi_h
                                        AND a.id_product='".$row['id_product']."'
                                        AND a.ket='good'
                                        AND a.sts_daycode='N'
                                      GROUP BY a.code ")->result_array();
          $dtListArray = array();
          foreach($daycode AS $val => $valx){
            $dtListArray[$val] = $valx['code'];
          }
          $dtImplode	= implode(", ", $dtListArray);

          $nestedData[]	= "<div align='center'>".$nomor."</div>";
          $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama']))."</div>";
          $nestedData[]	= "<div align='center'>".$row['qty_stock']."</div>";
          $nestedData[]	= "<div align='center'>".COUNT($daycode)."</div>";
          $nestedData[]	= "<div align='left'>".$dtImplode."</div>";
          $status = "Oke";
          $warna = "green";
          if(COUNT($daycode) <> $row['qty_stock']){
            $status = "Check Again";
            $warna = "red";
          }
          $nestedData[]	= "<div align='center'><span class='badge bg-".$warna."'>".$status."</span></div>";

        $data[] = $nestedData;
        $urut1++;
        $urut2++;
      // }
    }

    $json_data = array(
      "draw"            	=> intval( $requestData['draw'] ),
      "recordsTotal"    	=> intval( $totalData ),
      "recordsFiltered" 	=> intval( $totalFiltered ),
      "data"            	=> $data
    );

    echo json_encode($json_data);
  }

  public function get_query_json_stock($product, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $product_where = "";
    if($product != '0'){
    $product_where = " AND a.id_product = '".$product."'";
    }

    $sql = "
      SELECT
        (@row:=@row+1) AS nomor,
        a.id_product,
        c.nama,
        a.qty_stock
      FROM
        warehouse_product a
        LEFT JOIN ms_inventory_category2 c ON a.id_product = c.id_category2,
        (SELECT @row:=0) r
      WHERE 1=1 AND a.category='product'
        ".$product_where."
        AND (
          a.id_product LIKE '%".$this->db->escape_like_str($like_value)."%'
        )
    ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'id_product',
      2 => 'qty_stock'
    );

    $sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  //PRODUCT
  public function get_json_delivery(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_delivery(
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData			= $fetch['totalData'];
    $totalFiltered	= $fetch['totalFiltered'];
    $query					= $fetch['query'];

    $data	= array();
    $urut1  = 1;
    $urut2  = 0;
    foreach($query->result_array() as $row){
      $total_data     = $totalData;
      $start_dari     = $requestData['start'];
      $asc_desc       = $requestData['order'][0]['dir'];
      if($asc_desc == 'asc'){
      $nomor = ($total_data - $start_dari) - $urut2;
      }
      if($asc_desc == 'desc'){
      $nomor = $urut1 + $start_dari;
      }

      $nestedData 	= array();
      $SUM_QTY = $this->db->query("SELECT SUM(qty_delivery) AS qty FROM delivery_detail WHERE no_delivery='".$row['no_delivery']."'")->result();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['no_delivery']))."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['no_so']))." / ".strtoupper(get_name('sales_order_header', 'no_so_manual', 'no_so', $row['no_so']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['name_customer']))."</div>";
      $nestedData[]	= "<div align='left'>".date('d F Y',strtotime($row['delivery_date']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['shipping']))."</div>";
      $nestedData[]	= "<div align='center'>".$SUM_QTY[0]->qty."</div>";
      $last_create = (!empty($row['updated_by']))?$row['updated_by']:$row['created_by'];
      $nestedData[]	= "<div align='left'>".strtolower(get_name('users', 'username', 'id_user', $last_create))."</div>";

      $last_date = (!empty($row['updated_date']))?$row['updated_date']:$row['created_date'];
      $nestedData[]	= "<div align='center'>".date('d-m-Y H:i:s', strtotime($last_date))."</div>";
      $edit	= "";
      $delete	= "";
      $detail	= "";
      $approve = "";

      $detail = "<button type='button' class='btn btn-sm btn-warning detail' title='Detail' data-no_delivery='".$row['no_delivery']."'><i class='fa fa-eye'></i></button>";
      $download	= "&nbsp;<a href='".site_url($this->uri->segment(1)).'/excel_report_delivery/'.$row['no_delivery']."' target='_blank' class='btn btn-sm btn-success' title='Excel Download' data-role='qtip'><i class='fa fa-print'></i></a>";

      $nestedData[]	= "<div align='left'>
                        ".$detail."
                        ".$edit."

                        ".$approve."
                        ".$download."
                        ".$delete."
                        </div>";
      $data[] = $nestedData;
      $urut1++;
      $urut2++;
    }

    $json_data = array(
      "draw"            	=> intval( $requestData['draw'] ),
      "recordsTotal"    	=> intval( $totalData ),
      "recordsFiltered" 	=> intval( $totalFiltered ),
      "data"            	=> $data
    );

    echo json_encode($json_data);
  }

  public function get_query_json_delivery($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $sql = "
            SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.name_customer
            FROM
              delivery_header a LEFT JOIN master_customer b ON a.code_cust=b.id_customer,
              (SELECT @row:=0) r
            WHERE 1=1 AND a.deleted='N' AND (
              a.no_delivery LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.name_customer LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.delivery_date LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.shipping LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'no_delivery',
      2 => 'name_customer',
      3 => 'delivery_date',
      4 => 'shipping'
    );

    $sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  //PRODUCT
  public function get_json_adjustment(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_adjustment(
      $requestData['product'],
      $requestData['costcenter'],
      $requestData['search']['value'],
      $requestData['order'][0]['column'],
      $requestData['order'][0]['dir'],
      $requestData['start'],
      $requestData['length']
    );
    $totalData			= $fetch['totalData'];
    $totalFiltered	= $fetch['totalFiltered'];
    $query					= $fetch['query'];

    $data	= array();
    $urut1  = 1;
    $urut2  = 0;
    foreach($query->result_array() as $row){
      $total_data     = $totalData;
      $start_dari     = $requestData['start'];
      $asc_desc       = $requestData['order'][0]['dir'];
      if($asc_desc == 'asc'){
      $nomor = ($total_data - $start_dari) - $urut2;
      }
      if($asc_desc == 'desc'){
      $nomor = $urut1 + $start_dari;
      }

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='left'>".date('d-m-Y H:i:s',strtotime($row['created_date']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama_costcenter']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['adjustment']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['qty']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['stock_awal']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['stock_akhir']))."</div>";
      $nestedData[]	= "<div align='left'>".strtolower(get_name('users', 'username', 'id_user', $row['created_by']))."</div>";
      $nestedData[]	= "<div align='left'>".ucfirst(strtolower($row['reason']))."</div>";

      $data[] = $nestedData;
      $urut1++;
      $urut2++;
    }

    $json_data = array(
      "draw"            	=> intval( $requestData['draw'] ),
      "recordsTotal"    	=> intval( $totalData ),
      "recordsFiltered" 	=> intval( $totalFiltered ),
      "data"            	=> $data
    );

    echo json_encode($json_data);
  }

  public function get_query_json_adjustment($product, $costcenter, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $costcenter_where = "";
    if($costcenter != '0'){
    $costcenter_where = " AND a.costcenter = '".$costcenter."'";
    }

    $product_where = "";
    if($product != '0'){
    $product_where = " AND a.id_product = '".$product."'";
    }

    $sql = "
            SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nama_costcenter,
              c.nama
            FROM
              warehouse_product_adjustment a
              LEFT JOIN ms_costcenter b ON a.costcenter=b.id_costcenter
              LEFT JOIN ms_inventory_category2 c ON a.id_product = c.id_category2,
              (SELECT @row:=0) r
            WHERE 1=1 ".$costcenter_where." ".$product_where." AND (
              b.nama_costcenter LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR c.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'created_date',
      2 => 'nama_costcenter',
      3 => 'nama',
      4 => 'adjustment',
      5 => 'qty',
      6 => 'stock_awal',
      7 => 'stock_akhir',
      8 => 'created_by',
      9 => 'reason'
    );

    $sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

}
