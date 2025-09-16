<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Gudang_fg_mutasi_model extends BF_Model{

  public function __construct(){
    parent::__construct();
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
      $nestedData[]	= "<div align='left'>".strtoupper($nm_category)."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nm_product'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['variant'])."</div>";

      $countIN  = $this->db->select('SUM(a.qty) AS qty')->like('a.keterangan','penambahan finish good','both')->get_where('stock_product_histroy a',array('a.id_key_price'=>$row['id'],'DATE(a.created_date) >='=>$tgl_awal,'DATE(a.created_date) <='=>$tgl_akhir))->result_array();
      $countOUT = $this->db->select('SUM(a.qty) AS qty')->like('a.keterangan','pengurangan','both')->get_where('stock_product_histroy a',array('a.id_key_price'=>$row['id'],'DATE(a.created_date) >='=>$tgl_awal,'DATE(a.created_date) <='=>$tgl_akhir))->result_array();
      
      $qtyIN  = (!empty($countIN[0]['qty']))?number_format($countIN[0]['qty']):0;
      $qtyOUT = (!empty($countOUT[0]['qty']))?number_format($countOUT[0]['qty']):0;

      $nestedData[]	= "<div align='center'><span class='text-bold text-blue detInOut' data-code_lv4='".$row['id']."' data-tanggal_awal='".$tgl_awal."' data-tanggal_akhir='".$tgl_akhir."' data-tipe='in'>".$qtyIN."</span></div>";
      $nestedData[]	= "<div align='center'><span class='text-bold text-red detInOut' data-code_lv4='".$row['id']."' data-tanggal_awal='".$tgl_awal."' data-tanggal_akhir='".$tgl_akhir."' data-tipe='out'>".$qtyOUT."</span></div>";
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

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              b.id,
              b.code_lv4,
              y.nama AS nm_product,
              x.variant_product AS variant
            FROM
              stock_product b
              LEFT JOIN new_inventory_4 y ON b.code_lv4=y.code_lv4
              LEFT JOIN bom_header x ON b.no_bom=x.no_bom,
              (SELECT @row:=0) r
            WHERE y.deleted_date IS NULL ".$sales_order_where." AND (
              y.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR x.variant_product LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.id LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'y.nama',
      2 => 'y.nama',
      3 => 'x.variant_product'
    );

    $sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

}
