<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Gudang_wip_mutasi_model extends BF_Model{

  public function __construct(){
    parent::__construct();
  }

  public function data_side_gudang_wip(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_gudang_wip(
      $requestData['sales_order'],
      $requestData['date_filter'],
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
    $GET_USER = get_list_user();
    $GET_LEVEL4 = get_inventory_lv4();
    $GET_LEVEL2 = get_inventory_lv2();
    $dateFilter = (!empty($requestData['date_filter']))?$requestData['date_filter']:date('Y-m-d');
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

      $code_lv2     = (!empty($GET_LEVEL4[$row['code_lv4']]['code_lv2']))?$GET_LEVEL4[$row['code_lv4']]['code_lv2']:'';
      $nm_category  = (!empty($GET_LEVEL2[$code_lv2]['nama']))?$GET_LEVEL2[$code_lv2]['nama']:'';

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['no_spk'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nama_product'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($nm_category)."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['so_number'])."</div>";
      $nestedData[]	= "<div align='center'>".number_format($row['qty'])."</div>";

      $countIN = $this->db->get_where('so_internal_product',array('DATE(close_date)'=>$dateFilter))->result_array();
      $countOUT = $this->db->get_where('so_internal_product',array('DATE(qc_date)'=>$dateFilter))->result_array();

      $nestedData[]	= "<div align='center'><span class='text-bold text-blue detInOut' data-tanggal='".$dateFilter."' data-tipe='in'>".COUNT($countIN)."</span></div>";
      $nestedData[]	= "<div align='center'><span class='text-bold text-red detInOut' data-tanggal='".$dateFilter."' data-tipe='out'>".COUNT($countOUT)."</span></div>";
      // $nestedData[]	= "<div align='center'></div>";
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

  public function get_query_json_gudang_wip($sales_order, $date_filter, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $sales_order_where = "";
    if($sales_order != '0'){
        $sales_order_where = " AND a.so_number = '".$sales_order."'";
    }

    $sales_date_filter = "";
    $table = 'so_internal_product';
    $WHERE2 = "AND b.qc_date IS NULL";

    $key1 = "b.id_key_spk";
    $field1 = "COUNT(b.id) AS qty";
    $group1 = " GROUP BY b.id_key_spk";
    if(!empty($date_filter)){
        $sales_date_filter = " AND DATE(b.hist_date) = '".$date_filter."'";
        $table = 'gudang_wip_per_day';
        $WHERE2 = "";

        $key1 = "b.id_uniq";
        $field1 = "b.qty";
        $group1 = "";
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.code_lv4,
              b.no_spk,
              a.nama_product,
              a.so_number,
              ".$field1.",
              a.propose AS qty_propose
            FROM
              ".$table." b
              LEFT JOIN so_internal_spk z ON $key1 = z.id
              LEFT JOIN so_internal a ON a.id=z.id_so AND z.status_id = '1'
              LEFT JOIN new_inventory_4 y ON a.code_lv4=y.code_lv4,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL ".$WHERE2." ".$sales_date_filter." ".$sales_order_where." AND (
              a.code_lv4 LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.nama_product LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.so_number LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.no_spk LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ".$group1;
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'b.no_spk',
      2 => 'a.nama_product',
      3 => 'a.nama_product',
      4 => 'a.so_number',
      5 => 'b.qty',
      6 => 'a.propose'
    );

    $sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  public function data_side_gudang_wip_inout(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_gudang_wip_inout(
      $requestData['sales_order'],
      $requestData['tgl_awal'],
      $requestData['tgl_akhir'],
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
    $GET_USER = get_list_user();
    $GET_LEVEL4 = get_inventory_lv4();
    $GET_LEVEL2 = get_inventory_lv2();
    $tgl_awal   = (!empty($requestData['tgl_awal']))?date('Y-m-d',strtotime($requestData['tgl_awal'])):date('Y-m-d');
    $tgl_akhir  = (!empty($requestData['tgl_akhir']))?date('Y-m-d',strtotime($requestData['tgl_akhir'])):date('Y-m-d');
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

      $code_lv2     = (!empty($GET_LEVEL4[$row['code_lv4']]['code_lv2']))?$GET_LEVEL4[$row['code_lv4']]['code_lv2']:'';
      $nm_category  = (!empty($GET_LEVEL2[$code_lv2]['nama']))?$GET_LEVEL2[$code_lv2]['nama']:'';

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      // $nestedData[]	= "<div align='center'>".strtoupper($row['no_spk'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($nm_category)."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nama_product'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['variant_product'])."</div>";
      // $nestedData[]	= "<div align='center'>".strtoupper($row['so_number'])."</div>";
      // $nestedData[]	= "<div align='center'>".number_format($row['qty'])."</div>";

      $countIN  = $this->db->join('so_internal_spk b','a.id_key_spk=b.id','left')->join('so_internal c','b.id_so=c.id AND b.status_id="1"','left')->get_where('so_internal_product a',array('c.code_lv4'=>$row['code_lv4'],'DATE(a.close_date) >='=>$tgl_awal,'DATE(a.close_date) <='=>$tgl_akhir,'c.deleted_date'=>NULL))->result_array();
      $countOUT = $this->db->join('so_internal_spk b','a.id_key_spk=b.id','left')->join('so_internal c','b.id_so=c.id AND b.status_id="1"','left')->get_where('so_internal_product a',array('c.code_lv4'=>$row['code_lv4'],'DATE(a.qc_date) >='=>$tgl_awal,'DATE(a.qc_date) <='=>$tgl_akhir,'c.deleted_date'=>NULL))->result_array();
            // echo $this->db->last_query();
      $nestedData[]	= "<div align='center'><span class='text-bold text-blue detInOut' data-code_lv4='".$row['code_lv4']."' data-tanggal_awal='".$tgl_awal."' data-tanggal_akhir='".$tgl_akhir."' data-tipe='in'>".COUNT($countIN)."</span></div>";
      $nestedData[]	= "<div align='center'><span class='text-bold text-red detInOut' data-code_lv4='".$row['code_lv4']."' data-tanggal_awal='".$tgl_awal."' data-tanggal_akhir='".$tgl_akhir."' data-tipe='out'>".COUNT($countOUT)."</span></div>";
      // $nestedData[]	= "<div align='center'></div>";
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

  public function get_query_json_gudang_wip_inout($sales_order, $tgl_awal, $tgl_akhir, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){
    $tgl_awal = date('Y-m-d',strtotime($tgl_awal));
    $tgl_akhir = date('Y-m-d',strtotime($tgl_akhir));

    $sales_order_where = "";
    if($sales_order != '0'){
        $sales_order_where = " AND a.so_number = '".$sales_order."'";
    }

    $sales_date_filter = " AND DATE(b.close_date) BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."'";
    $table = 'so_internal_product';
    $WHERE2 = "";
    // $WHERE2 = "AND b.qc_date IS NULL";

    $key1 = "b.id_key_spk";
    $field1 = "COUNT(b.id) AS qty";
    $group1 = " GROUP BY a.code_lv4";
    // if(!empty($tgl_awal)){
    //     $sales_date_filter = " AND DATE(b.hist_date) BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."'";
    //     $table = 'gudang_wip_per_day';
    //     $WHERE2 = "";

    //     $key1 = "b.id_uniq";
    //     $field1 = "b.qty";
    //     $group1 = "";
    // }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.code_lv4,
              b.no_spk,
              a.nama_product,
              a.so_number,
              ".$field1.",
              a.propose AS qty_propose,
              x.variant_product,
              b.id_key_spk
            FROM
              ".$table." b
              LEFT JOIN so_internal_spk z ON $key1 = z.id
              LEFT JOIN so_internal a ON a.id=z.id_so AND z.status_id = '1'
              LEFT JOIN new_inventory_4 y ON a.code_lv4=y.code_lv4
              LEFT JOIN bom_header x ON a.no_bom=x.no_bom,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND y.deleted_date IS NULL ".$WHERE2." ".$sales_date_filter." ".$sales_order_where." AND (
              a.code_lv4 LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.nama_product LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.so_number LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.no_spk LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR x.variant_product LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ".$group1;
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'a.nama_product',
      2 => 'a.nama_product',
      3 => 'x.variant_product'
    );

    $sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

}
