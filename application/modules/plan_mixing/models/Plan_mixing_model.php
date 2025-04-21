<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Plan_mixing_model extends BF_Model{

  public function __construct(){
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Plan_Mixing.Add');
		$this->ENABLE_MANAGE  = has_permission('Plan_Mixing.Manage');
		$this->ENABLE_VIEW    = has_permission('Plan_Mixing.View');
		$this->ENABLE_DELETE  = has_permission('Plan_Mixing.Delete');
  }

  public function data_side_spk_material(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_spk_material(
      $requestData['sales_order'],
      $requestData['code_lv1'],
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
      $nestedData[]	= "<div align='center'>".number_format($row['qty'])."</div>";
      $username = (!empty($GET_USER[$row['created_by']]['username']))?$GET_USER[$row['created_by']]['username']:'-';
      $nestedData[]	= "<div align='left'>".strtolower($username)."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y H:i:s',strtotime($row['created_date']))."</div>";

      $status = 'Waiting Request';
      $warna = 'blue';
      if($row['sts_request'] == 'Y' AND $row['sts_subgudang'] == 'Y'){
        $status = 'Closed';
        $warna = 'green';
      }
      if($row['sts_request'] == 'Y' AND $row['sts_subgudang'] == 'N'){
        $status = 'Waiting Confirm';
        $warna = 'yellow';
      }

      $nestedData[]	= "<div align='center'><span class='badge bg-".$warna."'>".$status."</span></div>";


      $release_again = "";
      $release = "";
      $print = "";
      if($row['sts_request'] == 'N' AND $this->ENABLE_MANAGE){
        // $release	= "<button type='button' class='btn btn-sm btn-primary request' data-id='".$row['id']."' title='Request To Subgudang' data-role='qtip'><i class='fa fa-hand-pointer-o'></i></button>";
        $release	= "<a href='".base_url('plan_mixing/plan_mixing_add/'.$row['id'])."' class='btn btn-sm btn-primary' title='Plan Mixing' data-role='qtip'><i class='fa fa-hand-pointer-o'></i></a>";
      }
      if($row['sts_request'] != 'N'){
        $print	        = "<a href='".base_url('plan_mixing/print_spk/'.$row['kode_det'])."' target='_blank' class='btn btn-sm btn-warning' title='Print SPK' data-role='qtip'><i class='fa fa-print'></i></a>";
        $release_again	= "&nbsp;<a href='".base_url('plan_mixing/plan_mixing_add/'.$row['id'])."' class='btn btn-sm bg-purple' title='Plan Mixing' data-role='qtip'><i class='fa fa-hand-pointer-o'></i></a>";
      }
      $nestedData[]	= "<div align='left'>".$release.$print.$release_again."</div>";
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

  public function get_query_json_spk_material($sales_order, $code_lv1, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $sales_order_where = "";
    if($sales_order != '0'){
        $sales_order_where = " AND a.so_number = '".$sales_order."'";
    }

    $code_lv1_where = "";
    if($code_lv1 != '0'){
        $code_lv1_where = " AND z.code_lv1 = '".$code_lv1."'";
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
              b.kode_det
            FROM
              so_internal_spk b
              LEFT JOIN so_internal a ON a.id=b.id_so AND b.status_id = '1'
              LEFT JOIN new_inventory_4 z ON a.code_lv4 = z.code_lv4
              LEFT JOIN ms_costcenter c ON b.id_costcenter=c.id_costcenter
              LEFT JOIN bom_header d ON a.no_bom=d.no_bom,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND b.status_id = '1' AND z.code_lv1 != 'P123000009' ".$sales_order_where." ".$code_lv1_where." AND (
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
      7 => 'b.created_by',
      8 => 'b.created_date',
    );

    $sql .= " ORDER BY b.id DESC, ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  //Re-Print
  public function data_side_spk_reprint(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_spk_reprint(
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

      $variant_product 	= (!empty($row['variant_product']))?'; Variant '.$row['variant_product']:'';
			$color_product 		= (!empty($row['color_product']))?'; Color '.$row['color_product']:'';
			$surface_product 	= (!empty($row['surface_product']))?'; Surface '.$row['surface_product']:'';

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['so_number'])."</div>";
      $nestedData[]	= "<div align='left'>ORIGA</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nama_level4'].$variant_product.$color_product.$surface_product)."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['no_spk'])."</div>";
      $nestedData[]	= "<div align='center'>".number_format($row['qty'])."</div>";
      $username = (!empty($GET_USER[$row['release_by']]['username']))?$GET_USER[$row['release_by']]['username']:'-';
      $nestedData[]	= "<div align='center'>".strtolower($username)."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y H:i:s', strtotime($row['release_date']))."</div>";
      
      $print	= "<a href='".base_url('plan_mixing/print_spk/'.$row['kode_det'])."' target='_blank' title='Print SPK' data-role='qtip'>Print</a>";
     
      $nestedData[]	= "<div align='center'>".$print."</div>";
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

  public function get_query_json_spk_reprint($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              z.nama AS nama_level4,
              d.variant_product,
              d.color AS color_product,
              d.surface AS surface_product,
              b.kode,
              b.no_spk,
              b.request_by AS release_by,
              b.request_date AS release_date,
              b.qty,
              b.kode_det
            FROM
              so_internal_spk b
              LEFT JOIN so_internal a ON a.id=b.id_so AND b.status_id = '1'
              LEFT JOIN new_inventory_4 z ON a.code_lv4 = z.code_lv4
              LEFT JOIN ms_costcenter c ON b.id_costcenter=c.id_costcenter
              LEFT JOIN bom_header d ON a.no_bom=d.no_bom,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND b.sts_request = 'Y' AND b.status_id = '1' AND (
              a.code_lv4 LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR z.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR d.variant_product LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR d.color LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR d.surface LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.so_number LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.kode LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.no_spk LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'so_number',
      2 => 'so_number',
      3 => 'nama_product',
      4 => 'b.no_spk',
      5 => 'propose'
    );

    $sql .= " ORDER BY b.request_date DESC,  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  //Re-Print NEW
  public function data_side_spk_reprint_new(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_spk_reprint_new(
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

      $variant_product 	= (!empty($row['variant_product']))?'; Variant '.$row['variant_product']:'';
			$color_product 		= (!empty($row['color_product']))?'; Color '.$row['color_product']:'';
			$surface_product 	= (!empty($row['surface_product']))?'; Surface '.$row['surface_product']:'';

      $tipe_mixing = 'PerProduct';
      if($row['tipe_mixing'] == '2'){
        $tipe_mixing = 'PerSPK';
      }

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='left'>".$tipe_mixing."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['so_number'])."</div>";
      $nestedData[]	= "<div align='left'>ORIGA</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nama_level4'].$variant_product.$color_product.$surface_product)."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['no_spk'])."</div>";
      $nestedData[]	= "<div align='center'>".number_format($row['qty'])."</div>";
      $username = (!empty($GET_USER[$row['release_by']]['username']))?$GET_USER[$row['release_by']]['username']:'-';
      $nestedData[]	= "<div align='center'>".strtolower($username)."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y H:i:s', strtotime($row['release_date']))."</div>";
      
      $print	= "<a href='".base_url('plan_mixing/print_spk_new/'.$row['id_mixing'].'/'.$row['tipe_mixing'])."' target='_blank' title='Print SPK' data-role='qtip'>Print</a>";
     
      $nestedData[]	= "<div align='center'>".$print."</div>";
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

  public function get_query_json_spk_reprint_new($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              z.nama AS nama_level4,
              d.variant_product,
              d.color AS color_product,
              d.surface AS surface_product,
              b.kode,
              b.no_spk,
              b.created_by AS release_by,
              b.created_date AS release_date,
              b.qty,
              b.kode_det,
              b.tipe_mixing,
              b.id AS id_mixing
            FROM
              so_internal_spk_mixing b
              LEFT JOIN so_internal a ON a.id=b.id_so AND b.status_id = '1'
              LEFT JOIN new_inventory_4 z ON a.code_lv4 = z.code_lv4
              LEFT JOIN ms_costcenter c ON b.id_costcenter=c.id_costcenter
              LEFT JOIN bom_header d ON a.no_bom=d.no_bom,
              (SELECT @row:=0) r
            WHERE b.status_id = '1' AND (
              a.code_lv4 LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR z.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR d.variant_product LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR d.color LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR d.surface LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.so_number LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.kode LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.no_spk LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'so_number',
      2 => 'so_number',
      3 => 'nama_product',
      4 => 'b.no_spk',
      5 => 'propose'
    );

    $sql .= " ORDER BY b.created_date DESC,  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

}
