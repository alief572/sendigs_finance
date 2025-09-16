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

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nama_product'])."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['so_number'])."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['no_spk'])."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y',strtotime($row['plan_date']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nama_costcenter'])."</div>";
      $nestedData[]	= "<div align='center'>".number_format($row['qty'])."</div>";
      $username = (!empty($GET_USER[$row['created_by']]['username']))?$GET_USER[$row['created_by']]['username']:'-';
      // $nestedData[]	= "<div align='left'>".strtolower($username)."</div>";
      // $nestedData[]	= "<div align='center'>".date('d-M-Y H:i:s',strtotime($row['created_date']))."</div>";

      $sts_label = '';
      $release = "";
      $print = "";
      if(empty($row['close_date'])){
        $sts_subgudang = "<span class='badge bg-blue'>Confirm Subgudang</span>";
        $sts_produksi = "<br><span class='badge bg-blue'>Confirm Gd.Produksi</span>";
        if($row['sts_subgudang'] == 'N'){
          $sts_subgudang = "<span class='badge bg-red'>Waiting Subgudang</span>";
        }

        if($row['sts_produksi'] == 'N'){
          $sts_produksi = "<br><span class='badge bg-red'>Waiting Gd.Produksi</span>";
        }
        $sts_label = $sts_subgudang.$sts_produksi;

        if($row['sts_subgudang'] == 'Y' AND $row['sts_produksi'] == 'Y'){
          $release	= "<button type='button' class='btn btn-sm btn-primary request' data-id='".$row['id']."' title='Input Aktual Produksi' data-role='qtip'><i class='fa fa-edit'></i></a>";
        }
      }
      else{
        $sts_subgudang = "<span class='badge bg-purple'>".date('d-M-Y',strtotime($row['close_date']))."</span>";
        $sts_produksi = "<br><span class='badge bg-green'>Close</span>";

        $sts_label = $sts_subgudang.$sts_produksi;

        $release	= "<a href='".base_url('production/history_input_aktual/'.$row['id'])."' class='btn btn-sm btn-success' title='Input Aktual Produksi' data-role='qtip'><i class='fa fa-history'></i></a>";
      }

      $nestedData[]	= "<div align='left'>".$sts_label."</div>";


      
      if(empty($row['close_date'])){
      }
      // else{
      //   $print	= "<a href='".base_url('plan_mixing/print_spk/'.$row['kode_det'])."' target='_blank' class='btn btn-sm btn-warning' title='Print SPK' data-role='qtip'><i class='fa fa-print'></i></a>";
      // }
      $nestedData[]	= "<div align='center'>".$release.$print."</div>";
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
              a.nama_product,
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
              b.close_date
            FROM
              so_internal_spk b
              LEFT JOIN so_internal a ON a.id=b.id_so AND b.status_id = '1'
              LEFT JOIN ms_costcenter c ON b.id_costcenter=c.id_costcenter,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND b.sts_request='Y' AND b.status_id = '1' ".$sales_order_where." AND (
              a.code_lv4 LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.nama_product LIKE '%".$this->db->escape_like_str($like_value)."%'
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

}
