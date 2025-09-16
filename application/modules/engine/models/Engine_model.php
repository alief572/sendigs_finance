<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Engine_model extends BF_Model{

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

  public function get_json_bom(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_bom(
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
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['no_bom']))."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama']))."</div>";
        $berat = $this->db->query("SELECT SUM(weight) AS weight FROM bom_detail WHERE no_bom='".$row['no_bom']."'")->result();
			$nestedData[]	= "<div align='right'>".number_format($berat[0]->weight,2)." Kg</div>";
			
			$waste_product = (!empty($row['waste_product']))?number_format($row['waste_product'],2).' %':'-';
			$waste_setting = (!empty($row['waste_setting']))?number_format($row['waste_setting'],2).' %':'-';
			
			$nestedData[]	= "<div align='center'>".$waste_product."</div>";
			$nestedData[]	= "<div align='center'>".$waste_setting."</div>";
			
			$last_create = (!empty($row['updated_by']))?$row['updated_by']:$row['created_by'];
			$nestedData[]	= "<div align='left'>".strtolower(get_name('users', 'username', 'id_user', $last_create))."</div>";

			$last_date = (!empty($row['updated_date']))?$row['updated_date']:$row['created_date'];
			$nestedData[]	= "<div align='center'>".date('d-M-Y H:i', strtotime($last_date))."</div>";
			$edit	= "";
			$delete	= "";
			$print	= "";
			$approve = "";
			$download = "";
			// if(getStatus($row['project_code']) == 'WAITING ESTIMATION PROJECT'){
			// 	if($Arr_Akses['update']=='1'){
					$edit	= "&nbsp;<a href='".site_url($this->uri->segment(1)).'/add_bom/'.$row['no_bom']."' class='btn btn-sm btn-primary' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></a>";
			// 	}
			// 	if($Arr_Akses['approve']=='1'){
			// 		$approve	= "&nbsp;<button type='button' class='btn btn-sm btn-success approve' title='Request Approval' data-project_code='".$row['project_code']."'><i class='fa fa-check'></i></button>";
			// 	}
			// 	if($Arr_Akses['delete']=='1'){
					$delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete' title='Delete work data' data-no_bom='".$row['no_bom']."'><i class='fa fa-trash'></i></button>";
			// 	}
			// }
			// if($Arr_Akses['download']=='1'){
			// 	// $print	= "<a href='".site_url($this->uri->segment(1).'/print_bq/'.$row['project_code'])."' class='btn btn-sm btn-success' target='_blank' title='Print Project' data-role='qtip'><i class='fa fa-print'></i></a>";
			// 	$download	= "&nbsp;<a href='".site_url($this->uri->segment(1).'/download_excel/'.$row['project_code'])."' class='btn btn-sm btn-warning' target='_blank' title='Print Project' data-role='qtip'><i class='fa fa-file-excel-o'></i></a>";
			// }
      $excel	= "&nbsp;<a href='".site_url($this->uri->segment(1).'/excel_report_all_bom_detail/'.$row['no_bom'])."' class='btn btn-sm btn-success' target='_blank' title='Excel' data-role='qtip'><i class='fa fa-file-excel-o'></i></a>";

			$nestedData[]	= "<div align='left'>
												<button type='button' class='btn btn-sm btn-warning detail' title='Detail' data-no_bom='".$row['no_bom']."'><i class='fa fa-eye'></i></button>

												".$edit."
												".$print."
												".$excel."
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

	public function get_query_json_bom($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "
			SELECT
      (@row:=@row+1) AS nomor,
				a.*,
        b.nama
			FROM
				bom_header a LEFT JOIN ms_inventory_category2 b ON a.id_product=b.id_category2,
        (SELECT @row:=0) r
		   WHERE 1=1 AND a.deleted='N' AND (
				no_bom LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR b.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_bom',
			2 => 'nama'
		);

		$sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

  public function get_json_plan(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_plan(
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
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['no_plan']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(get_name('ms_inventory_category1', 'nama', 'id_category1', $row['project']))."</div>";
			$nestedData[]	= "<div align='left'>".date('F Y',strtotime($row['tahun'].'-'.$row['bulan'].'-01'))."</div>";
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
					$edit	= "&nbsp;<a href='".site_url($this->uri->segment(1)).'/add_material_planning/'.$row['no_plan']."' class='btn btn-sm btn-primary' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></a>";
			// 	}
			// 	if($Arr_Akses['approve']=='1'){
			// 		$approve	= "&nbsp;<button type='button' class='btn btn-sm btn-success approve' title='Request Approval' data-project_code='".$row['project_code']."'><i class='fa fa-check'></i></button>";
			// 	}
			// 	if($Arr_Akses['delete']=='1'){
					// $delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete' title='Delete work data' data-no_bom='".$row['no_bom']."'><i class='fa fa-trash'></i></button>";
			// 	}
			// }
			// if($Arr_Akses['download']=='1'){
			$print	= "&nbsp;<a href='".site_url($this->uri->segment(1).'/print_material_planning/'.$row['no_plan'])."' class='btn btn-sm btn-info' target='_blank' title='Print' data-role='qtip'><i class='fa fa-print'></i></a>";
			// 	$download	= "&nbsp;<a href='".site_url($this->uri->segment(1).'/download_excel/'.$row['project_code'])."' class='btn btn-sm btn-warning' target='_blank' title='Print Project' data-role='qtip'><i class='fa fa-file-excel-o'></i></a>";
			// }
			$nestedData[]	= "<div align='left'>
												<button type='button' class='btn btn-sm btn-warning detail' title='Detail' data-no_plan='".$row['no_plan']."'><i class='fa fa-eye'></i></button>

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

	public function get_query_json_plan($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "
			SELECT
      (@row:=@row+1) AS nomor,
				a.*
			FROM
				material_planning a,
        (SELECT @row:=0) r
		   WHERE 1=1 AND a.deleted='N' AND (
				no_plan LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_plan'
		);

		$sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

  public function get_json_bom_hth(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_bom_hth(
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
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['kode_bom_hth']))."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama']))."</div>";
			$nestedData[]	= "<div align='right'>".number_format($row['total_price'],5)."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['total_price2'],5)."</div>";
			$last_create = (!empty($row['updated_by']))?$row['updated_by']:$row['created_by'];
			$nestedData[]	= "<div align='left'>".strtolower(get_name('users', 'username', 'id_user', $last_create))."</div>";

			$last_date = (!empty($row['updated_date']))?$row['updated_date']:$row['created_date'];
			$nestedData[]	= "<div align='center'>".date('d-m-Y H:i:s', strtotime($last_date))."</div>";
			$edit	= "";
			$delete	= "";
			$print	= "";
			$approve = "";
			$download = "";
          $excel	= "&nbsp;<a href='".site_url($this->uri->segment(1).'/excel_report_satuan_bom_hth/'.$row['kode_bom_hth'])."' class='btn btn-sm btn-success' target='_blank' title='Excel' data-role='qtip'><i class='fa fa-file-excel-o'></i></a>";
          $print	= "&nbsp;<a href='".site_url($this->uri->segment(1).'/print_bom_hth/'.$row['kode_bom_hth'])."' class='btn btn-sm btn-info' target='_blank' title='Print' data-role='qtip'><i class='fa fa-print'></i></a>";
          $edit	= "&nbsp;<a href='".site_url($this->uri->segment(1)).'/add_bom_head_to_head/'.$row['kode_bom_hth']."' class='btn btn-sm btn-primary' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></a>";
			    $delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete' title='Delete work data' data-no_bom='".$row['kode_bom_hth']."'><i class='fa fa-trash'></i></button>";
		$nestedData[]	= "<div align='left'>
												<button type='button' class='btn btn-sm btn-warning detail' title='Detail' data-no_bom='".$row['kode_bom_hth']."'><i class='fa fa-eye'></i></button>

												".$edit."
												".$print."
                        ".$excel."
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

	public function get_query_json_bom_hth($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "
			SELECT
      (@row:=@row+1) AS nomor,
				a.*,
        b.nama
			FROM
				bom_hth_header a LEFT JOIN ms_inventory_category2 b ON a.id_product=b.id_category2,
        (SELECT @row:=0) r
		   WHERE 1=1 AND a.deleted='N' AND (
				kode_bom_hth LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR b.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'kode_bom_hth',
			2 => 'nama'
		);

		$sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}



}
