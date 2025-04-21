<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Subgudang_request_material_model extends BF_Model{

  public function __construct(){
    parent::__construct();

    $this->ENABLE_ADD     = has_permission('Request_Material_Subgudang.Add');
		$this->ENABLE_MANAGE  = has_permission('Request_Material_Subgudang.Manage');
		$this->ENABLE_VIEW    = has_permission('Request_Material_Subgudang.View');
		$this->ENABLE_DELETE  = has_permission('Request_Material_Subgudang.Delete');
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
      if($row['sts_confirm'] == 'N'  AND $this->ENABLE_MANAGE){
        $edit	= "&nbsp;<button type='button' data-kode_trans='".$row['kode_trans']."' data-tanda='edit' class='btn btn-sm btn-primary detail' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></button>";
      }
      if($row['sts_confirm'] == 'N' ){
        $print	= "&nbsp;<a href='".base_url('subgudang_request_material/print_spk_request/'.$row['kode_trans'])."' target='_blank' class='btn btn-sm btn-info' title='Print SPK Permintaan Material' data-role='qtip'><i class='fa fa-print'></i></a>";
      }
      else{
        $print	= "&nbsp;<a href='".base_url('subgudang_request_material/print_spk_request/'.$row['kode_trans'])."' target='_blank' class='btn btn-sm btn-info' title='Print SPK Permintaan Material' data-role='qtip'><i class='fa fa-print'></i></a>";
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
              c.nm_gudang AS nama_costcenter,
              d.nm_gudang,
              a.checked AS sts_confirm
            FROM
              warehouse_adjustment a
              LEFT JOIN warehouse c ON a.id_gudang_ke=c.id
              LEFT JOIN warehouse d ON a.id_gudang_dari=d.id,
              (SELECT @row:=0) r
            WHERE a.deleted_date IS NULL AND a.category='request subgudang' AND (
              a.kode_trans LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR d.nm_gudang LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR c.nm_gudang LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'kode_trans',
      2 => 'd.nm_gudang',
      3 => 'c.nm_gudang',
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
