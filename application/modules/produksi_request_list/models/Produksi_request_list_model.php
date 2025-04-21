<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Produksi_request_list_model extends BF_Model{

  public function __construct(){
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Request_List_Produksi.Add');
		$this->ENABLE_MANAGE  = has_permission('Request_List_Produksi.Manage');
		$this->ENABLE_VIEW    = has_permission('Request_List_Produksi.View');
		$this->ENABLE_DELETE  = has_permission('Request_List_Produksi.Delete');
  }

  //non-mixing
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
    $GET_SUM_BERAT = getTotalBeratSPKSOInternal();
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
      $nestedData[]	= "<div align='left'>".strtoupper($row['kode_det'].'-'.$row['sts_req'])."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['so_number'])."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper($row['no_spk'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nm_gudang'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nama_costcenter'])."</div>";
      $nestedData[]	= "<div align='right'>".number_format($GET_SUM_BERAT[$row['kode_det']] * $row['qty'],4)."</div>";
      $username = (!empty($GET_USER[$row['created_by']]['username']))?$GET_USER[$row['created_by']]['username']:'-';
      $nestedData[]	= "<div align='left'>".strtolower($username)."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y H:i:s',strtotime($row['created_date']))."</div>";

      $status = 'Waiting Request';
      $warna = 'blue';
      if($row['sts_produksi'] == 'P'){
        $status = 'Waiting Confirm';
        $warna = 'purple';
      }
      if($row['sts_produksi'] == 'Y'){
        $status = 'Closed';
        $warna = 'green';
      }
      $nestedData[]	= "<div align='center'><span class='badge bg-".$warna."'>".$status."</span></div>";


      $release = "";
      $print = "";
      if($row['sts_produksi'] == 'N' AND $this->ENABLE_MANAGE){
        $release	= "&nbsp;<a href='".base_url('produksi_request_list/add/'.$row['id'])."' class='btn btn-sm btn-primary' title='Pengeluaran' data-role='qtip'><i class='fa fa-hand-pointer-o'></i></a>";
      }
      if($row['sts_produksi'] != 'N'){
        $print	= "&nbsp;<a href='".base_url('produksi_request_list/print_spk/'.$row['kode_det'])."' target='_blank' class='btn btn-sm btn-warning' title='Print SPK Permintaan Material' data-role='qtip'><i class='fa fa-print'></i></a>";
      }
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
              b.created_by,
              b.created_date,
              c.nama_costcenter,
              b.sts_produksi,
              b.kode_det,
              'NON-MIXING' AS sts_req,
              d.nm_gudang AS nm_gudang
            FROM
              so_internal_spk b
              LEFT JOIN so_internal a ON a.id=b.id_so AND b.status_id = '1'
              LEFT JOIN ms_costcenter c ON b.id_costcenter=c.id_costcenter
              LEFT JOIN warehouse d ON b.id_gudang=d.id,
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

  //request material add
  public function server_side_request_produksi(){
		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_request_produksi(
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalData		= $fetch['totalData'];
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];

		$data	= array();
		$urut1  = 1;
        $urut2  = 0;
		foreach($query->result_array() as $row)
		{
			$total_data     = $totalData;
            $start_dari     = $requestData['start'];
            $asc_desc       = $requestData['order'][0]['dir'];
            if($asc_desc == 'asc')
            {
                $nomor = $urut1 + $start_dari;
            }
            if($asc_desc == 'desc')
            {
                $nomor = ($total_data - $start_dari) - $urut2;
            }

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>".$nomor."
                        <input type='hidden' class='id' name='detailx[".$nomor."][id]' value='".$row['code_lv4']."'>
                        <input type='hidden' class='nm_material' name='detailx[".$nomor."][nm_material]' value='".strtoupper($row['nama'])."'>
                        <input type='hidden' class='code_material' name='detailx[".$nomor."][code]' value='".strtoupper($row['code'])."'>
                        <input type='hidden' class='packing' name='detailx[".$nomor."][unit_packing]' value='".strtoupper($row['unit_packing'])."'>
                        <input type='hidden' class='satuan' name='detailx[".$nomor."][unit_satuan]' value='".strtoupper($row['unit_satuan'])."'>
                        <input type='hidden' class='konversi' name='detailx[".$nomor."][konversi]' value='".$row['konversi']."'>
                      </div>";
			$nestedData[]	= "<div align='left'>".strtoupper($row['code'])."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper($row['nama'])."</div>";
			$nestedData[]	= "<div align='center'>".strtoupper($row['unit_packing'])."</div>";
			$nestedData[]	= "<div align='left'><input type='text' style='width:100%' name='detailx[".$nomor."][sudah_request]' data-no='".$nomor."'class='form-control input-sm text-center autoNumeric4 sudah_request'><script type='text/javascript'>$('.autoNumeric4').autoNumeric('init', {mDec: '4', aPad: false});;</script></div>";
			$nestedData[]	= "<div align='left'><input type='text' style='width:100%' name='detailx[".$nomor."][ket_request]' data-no='".$nomor."' class='form-control input-sm text-left ket_request'></div>";
			if($row['konversi'] > 0){
        $nestedData[]	= "<div align='center'><button type='button' class='btn btn-primary btn-sm pindahkan' title='Pindahkan'><i class='fa fa-location-arrow'></i></button></div>";
      }
      else{
        $nestedData[]	= "<div align='center' class='text-red text-bold'>Konversi Nol</div>";
      }
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

	public function query_data_request_produksi($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "SELECT 
            (@row:=@row+1) AS nomor,
              b.*,
              c.code AS unit_packing,
              d.code AS unit_satuan
            FROM
              new_inventory_4 b
              LEFT JOIN ms_satuan c ON b.id_unit_packing=c.id
              LEFT JOIN ms_satuan d ON b.id_unit=d.id,
              (SELECT @row:=0) r
            WHERE b.category = 'material' AND b.deleted_date IS NULL
            AND(
              b.code_lv4 LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR b.code LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
          ";

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'code',
			2 => 'nama'
		);

		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

  //request material
  public function data_side_request_material(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_request_material(
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
    $GET_SUM_BERAT = getTotalBeratSPKSOInternal();
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
      $nestedData[]	= "<div align='center'>".strtoupper($row['kode_trans'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nm_gudang'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['nama_costcenter'])."</div>";
      $nestedData[]	= "<div align='center'>".number_format($row['qty_packing'],2)."</div>";
      $username = (!empty($GET_USER[$row['created_by']]['username']))?$GET_USER[$row['created_by']]['username']:'-';
      $nestedData[]	= "<div align='left'>".strtolower($username)."</div>";
      $nestedData[]	= "<div align='center'>".date('d-M-Y H:i:s',strtotime($row['created_date']))."</div>";

      $status = 'Waiting Confirm';
      $warna = 'blue';
      if($row['sts_confirm'] == 'Y'){
        $status = 'Closed';
        $warna = 'green';
      }
      $nestedData[]	= "<div align='center'><span class='badge bg-".$warna."'>".$status."</span></div>";


      $release  = "";
      $print    = "";
      $edit    = "";
      $view	= "<button type='button' data-kode_trans='".$row['kode_trans']."' data-tanda='detail' class='btn btn-sm btn-warning detail' title='Detail' data-role='qtip'><i class='fa fa-eye'></i></button>";
      if($row['sts_confirm'] == 'N' AND $this->ENABLE_MANAGE){
        $edit	= "&nbsp;<button type='button' data-kode_trans='".$row['kode_trans']."' data-tanda='edit' class='btn btn-sm btn-primary detail' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></button>";
      }
      if($row['sts_confirm'] == 'N'){
        $print	= "&nbsp;<a href='".base_url('produksi_request_list/print_spk_request/'.$row['kode_trans'])."' target='_blank' class='btn btn-sm btn-info' title='Print SPK Permintaan Material' data-role='qtip'><i class='fa fa-print'></i></a>";
      }
      $nestedData[]	= "<div align='left'>".$view.$edit.$print.$release."</div>";
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

  public function get_query_json_request_material($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $sql = "SELECT
              (@row:=@row+1) AS nomor,
              a.kode_trans,
              a.jumlah_mat_packing AS qty_packing,
              a.created_by,
              a.created_date,
              c.nama_costcenter,
              d.nm_gudang,
              a.checked AS sts_confirm
            FROM
              warehouse_adjustment a
              LEFT JOIN ms_costcenter c ON a.kd_gudang_ke=c.id_costcenter
              LEFT JOIN warehouse d ON a.id_gudang_dari=d.id,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND a.category='request produksi' AND (
              a.kode_trans LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR d.nm_gudang LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR c.nama_costcenter LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'kode_trans',
      2 => 'd.nm_gudang',
      3 => 'c.nama_costcenter',
      4 => 'jumlah_mat_packing',
      5 => 'created_by',
      6 => 'created_date',
    );

    $sql .= " ORDER BY a.id DESC, ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

}
