<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Warehouse_material_model extends BF_Model{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_data($table,$where_field='',$where_value=''){
  		if($where_field !='' && $where_value!=''){
  			$query = $this->db->get_where($table, array($where_field=>$where_value));
  		}else{
  			$query = $this->db->get($table);
  		}

  		return $query->result();
  	}

  	public function get_data_group($table,$where_field='',$where_value='',$where_group=''){
  		if($where_field !='' && $where_value!=''){
  			$query = $this->db->group_by($where_group)->get_where($table, array($where_field=>$where_value));

  		}else{
  			$query = $this->db->get($table);
  		}

  		return $query->result();
  	}

  public function get_json_stock(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_stock(
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
		foreach($query->result_array() as $row){
			$total_data     = $totalData;
			$start_dari     = $requestData['start'];
			$asc_desc       = $requestData['order'][0]['dir'];
			if($asc_desc == 'asc'){
				$nomor = $urut1 + $start_dari;
			}
			if($asc_desc == 'desc'){
				$nomor = ($total_data - $start_dari) - $urut2;
			}

      $nestedData 	= array();
			$nestedData[]	= "<div align='center'>".$nomor."</div>";
			$nestedData[]	= "<div align='left'>".$row['code_company']."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper($row['nm_material'])."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['konversi'],2)."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['unit'])."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['kelompok'])."</div>";
      $nestedData[]	= "<div align='right'>".number_format(get_stock_material($row['code_material'], '1'), 2)."</div>";
      $nestedData[]	= "<div align='right'>".number_format(get_stock_material_packing($row['code_material'], '1'), 2)."</div>";
      $nestedData[]	= "<div align='center'><button type='button' class='btn btn-sm btn-primary hist' data-gudang='1' data-material='".$row['code_material']."'><i class='fa fa-history'></i></button></div>";
      $nestedData[]	= "<div align='right'>".number_format(get_stock_material($row['code_material'], '2'), 2)."</div>";
      $nestedData[]	= "<div align='right'>".number_format(get_stock_material_packing($row['code_material'], '2'), 2)."</div>";
      $nestedData[]	= "<div align='center'><button type='button' class='btn btn-sm btn-success hist' data-gudang='2' data-material='".$row['code_material']."'><i class='fa fa-history'></i></button></div>";
      $nestedData[]	= "<div align='right'>".number_format(get_stock_material($row['code_material'], '3'), 2)."</div>";
      $nestedData[]	= "<div align='right'>".number_format(get_stock_material_packing($row['code_material'], '3'), 2)."</div>";
      $nestedData[]	= "<div align='center'><button type='button' class='btn btn-sm btn-warning hist' data-gudang='3' data-material='".$row['code_material']."'><i class='fa fa-history'></i></button></div>";
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

	public function get_query_json_stock($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "
			SELECT
        (@row:=@row+1) AS nomor,
				a.*
			FROM
			   ms_material a,
         (SELECT @row:=0) r
		   WHERE 1=1 AND (
				a.code_material LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.code_company LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.nm_material LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
      1 => 'code_material',
			2 => 'code_company',
      3 => 'nm_material',
      4 => 'konversi',
			5 => 'unit',
      6 => 'kelompok'
		);

		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

  public function modal_history(){
    $data     = $this->input->post();
    $gudang   = $data['gudang'];
    $material = $data['material'];

    $sql = "SELECT a.* FROM warehouse_history a WHERE a.id_gudang='".$gudang."' AND a.id_material='".$material."' AND DATE(update_date) >= '2020-12-01' ORDER BY a.id ASC ";
    $data = $this->db->query($sql)->result_array();

    $dataArr = array(
      'gudang' => $gudang,
      'material' => $material,
      'data'  => $data
    );
    $this->load->view('modal_history',$dataArr);

  }

  //Incoming
  public function get_json_incoming(){
		// $controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->get_query_json_incoming(
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
			$nestedData[]	= "<div align='center'>".$nomor."</div>";
			$nestedData[]	= "<div align='center'>".$row['no_ipp']."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['jumlah_mat_packing'],2)."</div>";
			$nestedData[]	= "<div align='right'>".number_format($row['jumlah_mat'],2)."</div>";
			$nestedData[]	= "<div align='center'>".strtolower(get_name('users', 'username', 'id_user', $row['created_by']))."</div>";
			$nestedData[]	= "<div align='center'>".date('d-M-Y H:i:s', strtotime($row['created_date']))."</div>";

				$create	= "";
				$edit	= "";
				$booking	= "";
				$spk_ambil_mat	= "";

			$nestedData[]	= "<div align='center'>
									<button type='button' class='btn btn-sm btn-primary detailAjust' title='Detail Adjustment' data-no_ipp='".$row['no_ipp']."' data-users='".str_replace(' ','sp4si', $row['created_by'])."' data-tanggal='".str_replace(' ','sp4si', $row['created_date'])."'><i class='fa fa-eye'></i></button>
                                    ".$create."
									".$edit."
									".$booking."
									".$spk_ambil_mat."
									</div>";
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

	public function get_query_json_incoming($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "
			SELECT
				a.*
			FROM
				warehouse_adjustment a
		    WHERE kd_gudang_dari='PURCHASE' AND(
				a.no_ipp LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_ipp'
		);

		$sql .= " ORDER BY created_date DESC, ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}


  public function get_json_wip(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_wip(
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
    foreach($query->result_array() as $row){
      $total_data     = $totalData;
      $start_dari     = $requestData['start'];
      $asc_desc       = $requestData['order'][0]['dir'];
      if($asc_desc == 'asc'){
        $nomor = $urut1 + $start_dari;
      }
      if($asc_desc == 'desc'){
        $nomor = ($total_data - $start_dari) - $urut2;
      }

      $nestedData 	= array();
      $nestedData[]	= "<div align='center'>".$nomor."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['no_wip']))."</div>";
      $nestedData[]	= "<div align='left'>".date('d F Y', strtotime($row['date_awal'])).' - '.date('d F Y', strtotime($row['date_akhir']))."</div>";
      $last_create = (!empty($row['updated_by']))?$row['updated_by']:$row['created_by'];
      $nestedData[]	= "<div align='left'>".strtolower(get_name('users', 'username', 'id_user', $last_create))."</div>";

      $last_date = (!empty($row['updated_date']))?$row['updated_date']:$row['created_date'];
      $nestedData[]	= "<div align='center'>".date('d-m-Y H:i:s', strtotime($last_date))."</div>";
      $edit	= "";
      $delete	= "";
      $print	= "";
      $approve = "";
      $download = "";
      // if(getStatus($row['project_code']) == 'WAITING ESTIMATION PROJECT'){
      // 	if($Arr_Akses['update']=='1'){
          // $edit	= "&nbsp;<a href='".site_url($this->uri->segment(1)).'/add_production_planning/'.$row['no_wip']."' class='btn btn-sm btn-primary' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></a>";
      // 	}
      // 	if($Arr_Akses['approve']=='1'){
      // 		$approve	= "&nbsp;<button type='button' class='btn btn-sm btn-success approve' title='Request Approval' data-project_code='".$row['project_code']."'><i class='fa fa-check'></i></button>";
      // 	}
      // 	if($Arr_Akses['delete']=='1'){
          // $delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete' title='Delete work data' data-no_bom='".$row['no_bom']."'><i class='fa fa-trash'></i></button>";
      // 	}
      // }
      // if($Arr_Akses['download']=='1'){
      // 	// $print	= "<a href='".site_url($this->uri->segment(1).'/print_bq/'.$row['project_code'])."' class='btn btn-sm btn-success' target='_blank' title='Print Project' data-role='qtip'><i class='fa fa-print'></i></a>";
      // 	$download	= "&nbsp;<a href='".site_url($this->uri->segment(1).'/download_excel/'.$row['project_code'])."' class='btn btn-sm btn-warning' target='_blank' title='Print Project' data-role='qtip'><i class='fa fa-file-excel-o'></i></a>";
      // }
      $nestedData[]	= "<div align='left'>
                        <button type='button' class='btn btn-sm btn-warning detail' title='Detail' data-no_wip='".$row['no_wip']."'><i class='fa fa-eye'></i></button>

                        ".$edit."
                        ".$print."
                        ".$approve."
                        ".$download."
                        ".$delete."
                        </div>";
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

  public function get_query_json_wip($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $sql = "
      SELECT
      (@row:=@row+1) AS nomor,
        a.*
      FROM
        material_wip a LEFT JOIN ms_costcenter b ON a.costcenter=b.id_costcenter,
        (SELECT @row:=0) r
       WHERE 1=1 AND a.deleted='N' AND (
        a.no_wip LIKE '%".$this->db->escape_like_str($like_value)."%'
        OR b.nama_costcenter LIKE '%".$this->db->escape_like_str($like_value)."%'
          )
    ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'no_wip'
    );

    $sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  public function get_json_stock_pro(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_stock_pro(
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
		foreach($query->result_array() as $row){
			$total_data     = $totalData;
			$start_dari     = $requestData['start'];
			$asc_desc       = $requestData['order'][0]['dir'];
			if($asc_desc == 'asc'){
				$nomor = $urut1 + $start_dari;
			}
			if($asc_desc == 'desc'){
				$nomor = ($total_data - $start_dari) - $urut2;
			}

      $nestedData 	= array();
			$nestedData[]	= "<div align='center'>".$nomor."</div>";
			$nestedData[]	= "<div align='left'>".$row['idmaterial']."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper($row['nm_material'])."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['incoming'],2)." ".ucfirst($row['unit'])."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['outgoing'],2)." ".ucfirst($row['unit'])."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['qty_stock'],2)." ".ucfirst($row['unit'])."</div>";
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

	public function get_query_json_stock_pro($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "
			SELECT
        (@row:=@row+1) AS nomor,
				a.*,
        b.unit,
        b.konversi,
        b.satuan_packing
			FROM
			   warehouse_stock a LEFT JOIN ms_material b ON a.id_material = b.code_material,
         (SELECT @row:=0) r
		   WHERE 1=1 AND kd_gudang='PRO' AND (
				a.id_material LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.idmaterial LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.nm_material LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'id_material',
			2 => 'nm_material'
		);

		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

  public function get_json_adjustment(){
    $controller			= ucfirst(strtolower($this->uri->segment(1)));
    // $Arr_Akses			= getAcccesmenu($controller);
    $requestData		= $_REQUEST;
    $fetch					= $this->get_query_json_adjustment(
      $requestData['material'],
      $requestData['kd_gudang'],
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
      $nestedData[]	= "<div align='left'>".date('d-m-Y H:i:s',strtotime($row['created_date']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(get_name('warehouse', 'nm_gudang', 'id', $row['kd_gudang']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nm_material']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['adjustment']." (".$row['ajust_by'])).")</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['qty'] / get_konversi($row['material']),2)."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['qty'],2)."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['stock_awal'] / get_konversi($row['material']),2)."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['stock_awal'],2)."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['stock_akhir'] / get_konversi($row['material']),2)."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['stock_akhir'],2)."</div>";
      $nestedData[]	= "<div align='left'>".strtolower(get_name('users', 'username', 'id_user', $row['created_by']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['surat_jalan']))."</div>";
      $nestedData[]	= "<div align='left'>".ucfirst(strtolower($row['reason']))."</div>";

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

  public function get_query_json_adjustment($material, $kd_gudang, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $material_where = "";
    if($material != '0'){
    $material_where = " AND a.material = '".$material."'";
    }

    $kd_gudang_where = "";
    if($kd_gudang != '0'){
    $kd_gudang_where = " AND a.kd_gudang = '".$kd_gudang."'";
    }

    $sql = "
            SELECT
              (@row:=@row+1) AS nomor,
              a.*,
              b.nm_gudang,
              c.nm_material
            FROM
              warehouse_material_adjustment a
              LEFT JOIN warehouse b ON a.kd_gudang=b.kd_gudang
              LEFT JOIN ms_material c ON a.material = c.code_material,
              (SELECT @row:=0) r
            WHERE 1=1 ".$material_where." ".$kd_gudang_where." AND (
              b.nm_gudang LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR c.nm_material LIKE '%".$this->db->escape_like_str($like_value)."%'
            )
            ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'created_date',
      2 => 'nm_gudang',
      3 => 'nm_material',
      4 => 'adjustment',
      5 => 'qty',
      6 => 'stock_awal',
      7 => 'stock_akhir',
      8 => 'created_by',
      9 => 'reason'
    );

    $sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
    $sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

    $data['query'] = $this->db->query($sql);
    return $data;
  }

  //History
  public function get_json_history(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_history(
      $requestData['material'],
      $requestData['kd_gudang'],
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
		foreach($query->result_array() as $row){
			$total_data     = $totalData;
			$start_dari     = $requestData['start'];
			$asc_desc       = $requestData['order'][0]['dir'];
			if($asc_desc == 'asc'){
				$nomor = $urut1 + $start_dari;
			}
			if($asc_desc == 'desc'){
				$nomor = ($total_data - $start_dari) - $urut2;
			}

      $gudang_dari = (!empty($row['id_gudang_dari']))?get_name('warehouse','nm_gudang','id',$row['id_gudang_dari']):$row['kd_gudang_dari'];
      $gudang_ke = (!empty($row['id_gudang_ke']))?get_name('warehouse','nm_gudang','id',$row['id_gudang_ke']):$row['kd_gudang_ke'];

      $nestedData 	= array();
			$nestedData[]	= "<div align='center'>".$nomor."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper($row['nm_material'])."</div>";
      $nestedData[]	= "<div align='right'>".date('d-M-Y', strtotime($row['update_date']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(get_name('warehouse','nm_gudang','id',$row['id_gudang']))."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['jumlah_mat'],3)." Kg</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($gudang_dari)."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($gudang_ke)."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['qty_stock_awal'],3)."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['qty_stock_akhir'],3)."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper($row['update_by'])."</div>";
      $nestedData[]	= "<div align='right'>".date('d-M-Y H:i:s', strtotime($row['update_date']))."</div>";
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

	public function get_query_json_history($material, $kd_gudang, $tgl_awal, $tgl_akhir, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $material_where = "";
    if($material != '0'){
    $material_where = " AND a.id_material = '".$material."'";
    }

    $kd_gudang_where = "";
    if($kd_gudang != '0'){
    $kd_gudang_where = " AND a.id_gudang = '".$kd_gudang."'";
    }

    $kd_date_where = "";
    if($tgl_awal != '0'){
    $kd_date_where = " AND a.update_date BETWEEN '".$tgl_awal."' AND '".$tgl_akhir."' ";
    }

		$sql = "
			SELECT
        (@row:=@row+1) AS nomor,
				a.*
			FROM
			   warehouse_history a,
         (SELECT @row:=0) r
		   WHERE DATE(update_date) >= '2020-12-01' ".$material_where." ".$kd_gudang_where." ".$kd_date_where." AND (
				a.id_material LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.nm_material LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
      1 => 'nm_material',
      2 => 'update_date',
      3 => 'id_gudang',
      4 => 'jumlah_mat',
      5 => 'id_gudang_dari',
      6 => 'id_gudang_ke',
      7 => 'qty_stock_awal',
      8 => 'qty_stock_akhir',
      9 => 'update_by',
      10 => 'update_date'
		);

		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}


}
