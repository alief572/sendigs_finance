<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Production_report_model extends BF_Model{

  public function __construct(){
    parent::__construct();
  }

  public function data_side_spk_material(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_spk_material(
      $requestData['sales_order'],
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

      $tgl_close = (!empty($row['tanggal_close_new']))?$row['tanggal_close_new']:$row['tanggal_close'];

      $variant_product 	= (!empty($row['variant_product']))?'; Variant '.$row['variant_product']:'';
			$color_product 		= (!empty($row['color_product']))?'; Color '.$row['color_product']:'';
			$surface_product 	= (!empty($row['surface_product']))?'; Surface '.$row['surface_product']:'';

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y',strtotime($row['est_finish_date']))."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y',strtotime($tgl_close))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nama_level4'].$variant_product.$color_product.$surface_product)."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['so_number'])."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['no_spk'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nama_costcenter'])."</div>";
      $nestedData[]	= "<div align='center'>".number_format($row['qty_close'])."/".number_format($row['qty'])."</div>";

      $tgl1 = date_create($row['est_finish_date']);
      $tgl2 = date_create($tgl_close);
      $jarak = date_diff( $tgl1, $tgl2 );

      $selisih = $jarak->days;

      $labelSts = 'Lebih '.$selisih.' hari';
      $color    = 'purple';
      $labelSts2 = 'Not Achieve';
      $color2    = 'red';
      if($tgl_close  < $row['est_finish_date']){
        $labelSts = 'Kurang '.$selisih.' hari';
        $color    = 'blue';
        $labelSts2 = 'Achieve';
        $color2    = 'green';
      }
      if($selisih == 0){
        $labelSts = 'Ontime';
        $color    = 'green';
        $labelSts2 = 'Achieve';
        $color2    = 'green';
      }

      $nestedData[]	= "<div align='left'><span class='badge bg-".$color."'>".$labelSts."</span></div>";
      $nestedData[]	= "<div align='left'><span class='badge bg-".$color2."'>".$labelSts2."</span></div>";
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

  public function get_query_json_spk_material($sales_order, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $sales_order_where = "";
    if($sales_order != '0'){
        $sales_order_where = " AND a.so_number = '".$sales_order."'";
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              b.id,
              e.nama AS nama_level4,
              x.variant_product,
              x.color AS color_product,
              x.surface AS surface_product,
              a.so_number,
              b.no_spk,
              b.tanggal_est_finish AS est_finish_date,
              b.id_costcenter,
              b.qty,
              b.created_by,
              b.created_date,
              c.nama_costcenter,
              b.sts_request,
              b.sts_subgudang,
              b.sts_produksi,
              b.kode_det,
              b.tanggal_close,
              MAX(d.close_produksi) AS tanggal_close_new,
              COUNT(d.id) AS qty_close,
              b.subgudang_date
            FROM
              so_internal_spk b
              LEFT JOIN so_internal a ON a.id=b.id_so AND b.status_id = '1'
              LEFT JOIN ms_costcenter c ON b.id_costcenter=c.id_costcenter
              LEFT JOIN so_internal_product d ON b.id=d.id_key_spk
              LEFT JOIN new_inventory_4 e ON a.code_lv4=e.code_lv4
              LEFT JOIN bom_header x ON a.no_bom=x.no_bom,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND b.status_id = '1' AND b.sts_close IN ('Y','P') ".$sales_order_where." AND e.code_lv1 != 'P123000009' AND (
              a.code_lv4 LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR e.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR x.variant_product LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR x.color LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR x.surface LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.so_number LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.no_spk LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR c.nama_costcenter LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            GROUP BY b.id
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'b.tanggal_close',
      2 => 'b.close_date',
      3 => 'a.nama_product',
      4 => 'a.so_number',
      5 => 'b.no_spk',
      6 => 'c.nama_costcenter',
      7 => 'b.qty'
    );

    $sql .= " ORDER BY b.close_date DESC, ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

}
