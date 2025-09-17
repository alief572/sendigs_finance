<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report_checksheet_model extends BF_Model{

  public function __construct(){
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Report_Checksheet.Add');
    $this->ENABLE_MANAGE  = has_permission('Report_Checksheet.Manage');
    $this->ENABLE_VIEW    = has_permission('Report_Checksheet.View');
    $this->ENABLE_DELETE  = has_permission('Report_Checksheet.Delete');
  }

  public function data_side_report_checksheet(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_report_checksheet(
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
    $GET_CHECKSHEET = get_checksheet();
    $GET_CHECKSHEET_INPUT = get_checksheet_input();
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

      $checkCLose = (!empty(checkInputProduksiQty($row['id'])[$row['id']]))?checkInputProduksiQty($row['id'])[$row['id']]:0;

      $variant_product 	= (!empty($row['variant_product']))?'; Variant '.$row['variant_product']:'';
			$color_product 		= (!empty($row['color_product']))?'; Color '.$row['color_product']:'';
			$surface_product 	= (!empty($row['surface_product']))?'; Surface '.$row['surface_product']:'';

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nama_level4'].$variant_product.$color_product.$surface_product)."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['so_number'])."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['no_spk'])."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y',strtotime($row['plan_date']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nama_costcenter'])."</div>";
      $nestedData[]	= "<div align='center' title='".$checkCLose."'>".number_format($row['qty'])."</div>";

      // $close_by = (!empty($GET_USER[$row['close_by']]['nama']))?$GET_USER[$row['close_by']]['nama']:'';
      // $close_date = (!empty($row['close_date']))?date('d-M-Y H:i',strtotime($row['close_date'])):'';
      // $nestedData[]	= "<div align='left'>".$close_by."</div>";
      // $nestedData[]	= "<div align='center'>".$close_date."</div>";

      $release  = "";
      $print    = "";
      $excel    = "";
      $view    = "";
      $print_check    = "";

      $check = (!empty($GET_CHECKSHEET[$row['code_lv4']]))?$GET_CHECKSHEET[$row['code_lv4']]:'0';

      $check_input = (!empty($GET_CHECKSHEET_INPUT[$row['id']]))?TRUE:FALSE;

      if($this->ENABLE_MANAGE){
        if($check != '0'){
          // if($row['frequency_check'] == 'hourly'){
          //   $release	= "<a href='".base_url('report_checksheet/add_hourly/'.$row['id'].'/1')."' class='btn btn-sm btn-success' title='Input Checksheet' data-role='qtip'><i class='fa fa-plus'></i></a>";
          // }
          // else{
          //   $release	= "<a href='".base_url('report_checksheet/add/'.$row['id'].'/1')."' class='btn btn-sm btn-success' title='Input Checksheet' data-role='qtip'><i class='fa fa-plus'></i></a>";
          // }
          $print_check	= "<a href='".base_url('report_checksheet/print_checksheet/'.$row['id'].'/'.$row['code_lv4'])."' target='_blank' class='btn btn-sm btn-default' title='Print Checksheet' data-role='qtip'><i class='fa fa-print'></i></a>";
          $release	    = "&nbsp;<a href='".base_url('report_checksheet/add2/'.$row['id'].'/1')."' class='btn btn-sm btn-success' title='Input Checksheet' data-role='qtip'><i class='fa fa-plus'></i></a>";

        }
        else{
          $release	= "<span class='text-danger text-bold'>Checksheet belum dibuat !</span>";
        }

        if($check_input === TRUE){
          // if($row['frequency_check'] == 'hourly'){
          //   $view	    = "<a href='".base_url('report_checksheet/add_hourly/'.$row['id'].'/1/view')."' class='btn btn-sm btn-warning' title='Detail Checksheet' data-role='qtip'><i class='fa fa-eye'></i></a>";
          //   $release	= "&nbsp;<a href='".base_url('report_checksheet/add_hourly/'.$row['id'].'/1')."' class='btn btn-sm btn-primary' title='Input Checksheet' data-role='qtip'><i class='fa fa-edit'></i></a>";
          // }
          // else{
          //   $view	    = "<a href='".base_url('report_checksheet/add/'.$row['id'].'/1/view')."' class='btn btn-sm btn-warning' title='Detail Checksheet' data-role='qtip'><i class='fa fa-eye'></i></a>";
          //   $release	= "&nbsp;<a href='".base_url('report_checksheet/add/'.$row['id'].'/1')."' class='btn btn-sm btn-primary' title='Input Checksheet' data-role='qtip'><i class='fa fa-edit'></i></a>";
          // }
          $view	        = "&nbsp;<a href='".base_url('report_checksheet/add2/'.$row['id'].'/1/view')."' class='btn btn-sm btn-warning' title='Detail Checksheet' data-role='qtip'><i class='fa fa-eye'></i></a>";
          $print_check	= "<a href='".base_url('report_checksheet/print_checksheet/'.$row['id'].'/'.$row['code_lv4'])."' target='_blank' class='btn btn-sm btn-default' title='Print Checksheet' data-role='qtip'><i class='fa fa-print'></i></a>";
          $release	    = "&nbsp;<a href='".base_url('report_checksheet/add2/'.$row['id'].'/1')."' class='btn btn-sm btn-success' title='Input Checksheet' data-role='qtip'><i class='fa fa-plus'></i></a>";

          // $print	  = "&nbsp;<a href='".base_url('report_checksheet/print/'.$row['id'])."' target='_blank' class='btn btn-sm btn-default' title='Print' data-role='qtip'><i class='fa fa-print'></i></a>";
        }

      }
      
      $nestedData[]	= "<div align='left'>".$print_check.$view.$release.$print.$excel."</div>";
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

  public function get_query_json_report_checksheet($sales_order, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $sales_order_where = "";
    if($sales_order != '0'){
        $sales_order_where = " AND a.so_number = '".$sales_order."'";
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
              z.code_lv1,
              a.code_lv4,
              y.frequency_check
            FROM
              so_internal_spk b
              LEFT JOIN so_internal a ON a.id=b.id_so AND b.status_id = '1'
              LEFT JOIN new_inventory_4 z ON a.code_lv4 = z.code_lv4
              LEFT JOIN checksheet_header y ON z.code_lv4 = y.code_lv4
              LEFT JOIN ms_costcenter c ON b.id_costcenter=c.id_costcenter
              LEFT JOIN bom_header d ON a.no_bom=d.no_bom,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND b.sts_request='Y'  AND b.status_id = '1' ".$sales_order_where." AND (
              a.code_lv4 LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR z.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR d.variant_product LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR d.color LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR d.surface LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.so_number LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.no_spk LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR c.nama_costcenter LIKE '%".$this->db->escape_like_str($like_value)."%'
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

    $sql .= " ORDER BY b.id DESC, ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }
  
}
