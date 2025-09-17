<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Spk_delivery_qc_model extends BF_Model{

  public function __construct(){
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('QC_Delivery.Add');
		$this->ENABLE_MANAGE  = has_permission('QC_Delivery.Manage');
		$this->ENABLE_VIEW    = has_permission('QC_Delivery.View');
		$this->ENABLE_DELETE  = has_permission('QC_Delivery.Delete');
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

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['no_delivery'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['no_surat_jalan'])."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y',strtotime($row['created_date']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nm_customer'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['project'])."</div>";
      $check	= "";
      if($this->ENABLE_MANAGE){
      $check	= "<a href='".base_url('spk_delivery_qc/add/'.$row['no_delivery'])."' class='btn btn-sm btn-success' title='QC Check' data-role='qtip'><i class='fa fa-check'></i></a>&nbsp;";
      }
      $nestedData[]	= "<div align='center'>".$check."</div>";
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
        $sales_order_where = " AND a.no_so = '".$sales_order."'";
    }

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.no_so,
              a.no_penawaran,
              c.nm_customer,
              a.project,
              z.no_delivery,
              z.no_surat_jalan,
              z.created_date,
              z.id,
              z.status
            FROM
              spk_delivery z
              LEFT JOIN tr_sales_order a ON a.no_so = z.no_so
              LEFT JOIN tr_penawaran b ON a.no_penawaran = b.no_penawaran
              LEFT JOIN customer c ON b.id_customer = c.id_customer,
              (SELECT @row:=0) r
            WHERE a.approve = '1' AND z.status = 'CHECK QC' ".$sales_order_where." AND (
              a.no_so LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.no_penawaran LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR c.nm_customer LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.project LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR z.no_surat_jalan LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR z.no_delivery LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'z.no_delivery',
      2 => 'z.no_surat_jalan',
      3 => 'z.created_date',
      4 => 'c.nm_customer',
      4 => 'a.project',
    );

    $sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

}
