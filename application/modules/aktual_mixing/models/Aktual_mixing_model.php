<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Aktual_mixing_model extends BF_Model{

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
    $GET_SUM_BERAT = getTotalBeratSPKSOInternalMixing();
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

      if($row['sts_spk'] == 'non-mixing'){
        $color = "text-primary text-bold";
        $nm_gudang = 'gudang produksi';
      }
      else{
        $color = "text-success text-bold";
        $nm_gudang = $row['nama_costcenter'];
      }

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['kode_det'])."-<span class='".$color."'>".strtoupper($row['sts_req'])."</span></div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['so_number'])."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['no_spk'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nm_gudang'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($nm_gudang)."</div>";
      $nestedData[]	= "<div align='right'>".number_format($GET_SUM_BERAT[$row['kode_det']] * $row['qty'],4)."</div>";


      $release = "";
      $print = "";

      $username = (!empty($GET_USER[$row['created_by']]['username']))?$GET_USER[$row['created_by']]['username']:'-';
      $datetime = $row['created_date'];

      $status = 'Waiting Input';
      $warna = 'blue';
      if($row['sts_subgudang'] == 'Y' AND $row['sts_mixing'] == 'P'){
        $status = 'Parsial';
        $warna = 'purple';
      }
      if($row['sts_mixing'] == 'Y'){
        $status = 'Closed';
        $warna = 'green';
      }
      if($row['sts_subgudang'] == 'N'){
        $status = 'Waiting Subgudang';
        $warna = 'yellow';
      }

      if($row['sts_subgudang'] == 'Y' AND $row['sts_mixing'] != 'Y'){
        $release	= "&nbsp;<a href='".base_url('aktual_mixing/add_new/'.$row['id'])."' class='btn btn-sm btn-primary' title='Input Aktual Produksi' data-role='qtip'><i class='fa fa-hand-pointer-o'></i></a>";
      }
      else{
        if($row['sts_mixing'] == 'Y'){
          $print	= "&nbsp;<a href='".base_url('aktual_mixing/print_spk/'.$row['kode_det'])."' target='_blank' class='btn btn-sm btn-warning' title='Print Pengeluaran Material' data-role='qtip'><i class='fa fa-print'></i></a>";
        }
      }

      if($row['sts_close'] == 'Y' AND !empty($row['close_date'])){
        $print	  = "&nbsp;<a href='".base_url('aktual_mixing/print_spk/'.$row['kode_det'])."' target='_blank' class='btn btn-sm btn-warning' title='Print Pengeluaran Material' data-role='qtip'><i class='fa fa-print'></i></a>";
        $release	= "";
        $status   = 'Closed';
        $warna    = 'green';
      }

      $nestedData[]	= "<div align='left'>".strtolower($username)."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y H:i:s',strtotime($datetime))."</div>";
      $nestedData[]	= "<div align='center'><span class='badge bg-".$warna."'>".$status."</span></div>";
      
      $nestedData[]	= "<div align='left'>".$print.$release."</div>";
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
              b.request_by AS created_by,
              b.request_date AS created_date,
              b.produksi_by AS produksi_by,
              b.produksi_date AS produksi_date,
              c.nama_costcenter,
              b.sts_subgudang,
              b.sts_mixing,
              b.sts_produksi,
              b.kode_det,
              b.sts_spk AS sts_req,
              d.nm_gudang AS nm_gudang,
              b.sts_spk,
              b.sts_close,
              b.close_date
            FROM
              so_internal_spk_view b
              LEFT JOIN so_internal a ON a.id=b.id_so AND b.status_id = '1'
              LEFT JOIN new_inventory_4 z ON a.code_lv4 = z.code_lv4
              LEFT JOIN ms_costcenter c ON b.id_costcenter=c.id_costcenter
              LEFT JOIN warehouse d ON b.id_gudang=d.id,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND b.sts_request='Y' AND b.sts_spk='mixing' AND z.code_lv1 != 'P123000009' AND b.status_id = '1' ".$sales_order_where." AND (
              a.code_lv4 LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.nama_product LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.so_number LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.no_spk LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR c.nama_costcenter LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.kode_det LIKE '%".$this->db->escape_like_str($like_value)."%'
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
