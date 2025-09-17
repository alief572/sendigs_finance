<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Accessories_model extends BF_Model{

    public function __construct()
    {
        parent::__construct();

		$this->ENABLE_ADD     = has_permission('Master_Indirect.Add');
		$this->ENABLE_MANAGE  = has_permission('Master_Indirect.Manage');
		$this->ENABLE_VIEW    = has_permission('Master_Indirect.View');
		$this->ENABLE_DELETE  = has_permission('Master_Indirect.Delete');
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

  public function get_json_accessories(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_accessories(
			$requestData['id_category'],
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
			$nestedData[]	= "<div align='left'>".$row['id_stock']."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['stock_name']))."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nm_category']))."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['trade_name']))."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['brand']))."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['spec']))."</div>";

			$status = ($row['status'] == '1')?'Active':'Non-Active';
			$warna = ($row['status'] == '1')?'green':'red';

			$nestedData[]	= "<div align='left'><span class='badge bg-".$warna."'>".$status."</span></div>";

			$last_create = (!empty($row['updated_by']))?$row['updated_by']:$row['created_by'];
			$nestedData[]	= "<div align='left'>".strtolower(get_name('users', 'username', 'id_user', $last_create))."</div>";

			$last_date = (!empty($row['updated_date']))?$row['updated_date']:$row['created_date'];
			$nestedData[]	= "<div align='center'>".date('d-M-Y H:i', strtotime($last_date))."</div>";

			$view	= "";
			$edit	= "";
			$delete	= "";

			$view	= "&nbsp;<a href='".site_url($this->uri->segment(1)).'/add/'.$row['id']."/view' class='btn btn-sm btn-warning' title='Detail Data' data-role='qtip'><i class='fa fa-eye'></i></a>";
			if($this->ENABLE_MANAGE){
				$edit	= "&nbsp;<a href='".site_url($this->uri->segment(1)).'/add/'.$row['id']."' class='btn btn-sm btn-primary' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></a>";
			}
			if($this->ENABLE_DELETE){
					$delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete' title='Delete data' data-id='".$row['id']."'><i class='fa fa-trash'></i></button>";
			}
			$nestedData[]	= "<div align='left'>
								".$view."
								".$edit."
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

	public function get_query_json_accessories($id_category, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){
		$WHERE = '';
		if($id_category > 0){
			$WHERE = "AND a.id_category='".$id_category."'";
		}
		$sql = "SELECT
					(@row:=@row+1) AS nomor,
					a.*,
					b.code AS sat_packing,
					c.code AS sat_unit,
					d.nm_category AS nm_category
				FROM
					accessories a
					LEFT JOIN ms_satuan b ON a.id_unit_gudang=b.id
					LEFT JOIN ms_satuan c ON a.id_unit=c.id
					LEFT JOIN accessories_category d ON a.id_category = d.id,
					(SELECT @row:=0) r
				WHERE 1=1 AND a.deleted_date IS NULL ".$WHERE." AND (
					a.stock_name LIKE '%".$this->db->escape_like_str($like_value)."%'
					OR a.id_stock LIKE '%".$this->db->escape_like_str($like_value)."%'
					OR a.trade_name LIKE '%".$this->db->escape_like_str($like_value)."%'
					OR a.brand LIKE '%".$this->db->escape_like_str($like_value)."%'
					OR a.spec LIKE '%".$this->db->escape_like_str($like_value)."%'
					OR b.code LIKE '%".$this->db->escape_like_str($like_value)."%'
					OR c.code LIKE '%".$this->db->escape_like_str($like_value)."%'
					OR d.nm_category LIKE '%".$this->db->escape_like_str($like_value)."%'
				)
			";
			// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'id_stock',
			2 => 'd.nm_category',
      		3 => 'stock_name',
      		4 => 'trade_name',
      		5 => 'brand',
			6 => 'spec',
			7 => 'status'
		);

		$sql .= " ORDER BY a.id DESC, ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

  public function get_json_unit(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_unit(
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
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['code']))."</div>";
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
					$edit	= "&nbsp;<button data-id='".$row['id']."' class='btn btn-sm btn-primary edit' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></button>";
			// 	}
			// 	if($Arr_Akses['approve']=='1'){
			// 		$approve	= "&nbsp;<button type='button' class='btn btn-sm btn-success approve' title='Request Approval' data-project_code='".$row['project_code']."'><i class='fa fa-check'></i></button>";
			// 	}
			// 	if($Arr_Akses['delete']=='1'){
					$delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete' title='Delete data' data-id='".$row['id']."'><i class='fa fa-trash'></i></button>";
			// 	}
			// }
			// if($Arr_Akses['download']=='1'){
			// 	// $print	= "<a href='".site_url($this->uri->segment(1).'/print_bq/'.$row['project_code'])."' class='btn btn-sm btn-success' target='_blank' title='Print Project' data-role='qtip'><i class='fa fa-print'></i></a>";
			// 	$download	= "&nbsp;<a href='".site_url($this->uri->segment(1).'/download_excel/'.$row['project_code'])."' class='btn btn-sm btn-warning' target='_blank' title='Print Project' data-role='qtip'><i class='fa fa-file-excel-o'></i></a>";
			// }
			$nestedData[]	= "<div align='center'>
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

	public function get_query_json_unit($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "
			SELECT
      (@row:=@row+1) AS nomor,
				a.*
			FROM
				ms_satuan a,
        (SELECT @row:=0) r
		   WHERE 1=1 AND a.deleted='N' AND (
				a.code LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'code'
		);

		$sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}



}
