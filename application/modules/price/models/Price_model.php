<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Price_model extends BF_Model{

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

  public function get_json_material(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_material(
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
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nm_material']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['type_material']))."</div>";
	   $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['kurs']))."</div>";
      $nestedData[]	= "<div align='right'>".number_format($row['rate'],2)."</div>";

			$last_create = (!empty($row['updated_by']))?$row['updated_by']:$row['created_by'];
			$nestedData[]	= "<div align='left'>".strtolower(get_name('users', 'username', 'id_user', $last_create))."</div>";

      $last_date = (!empty($row['updated_date']))?$row['updated_date']:$row['created_date'];
      $nestedData[]	= "<div align='center'>".date('d-M-Y H:i:s', strtotime($last_date))."</div>";

      $date_now       = date('Y-m-d');
      $date_expired   = date('Y-m-d', strtotime('+3 month', strtotime($last_date)));
      $date_minus     = date('Y-m-d', strtotime('-14 days', strtotime($date_expired)));


      if($date_now > $date_expired){
        $warna = 'red';
        $status = 'More than 3 Months';
      }
      if($date_now >= $date_minus AND $date_now < $date_expired){
        $warna = 'blue';
        $status = 'Soon 3 Months, Please Update';
      }
      if($date_now < $date_minus){
        $warna = 'green';
        $status = 'Safe Price';
      }

      $nestedData[]	= "<div align='left'><span class='badge bg-".$warna."'>".$status."</span></div>";

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

	public function get_query_json_material($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "
			SELECT
      (@row:=@row+1) AS nomor,
				a.*,
				b.nm_material
			FROM
				ms_material b LEFT JOIN price_ref a ON a.code=b.code_material,
        (SELECT @row:=0) r
		   WHERE 1=1 AND a.deleted='N' AND a.category='material' AND (
				a.id LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR b.nm_material LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR b.kelompok LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR b.code_material LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'id'
		);

		$sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

  public function get_json_product(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_product(
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
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama']))."</div>";
      $nestedData[]	= "<div align='right' style='padding-right:50px;'>".number_format($row['rate'],2)."</div>";
      $nestedData[]	= "<div align='right' style='padding-right:50px;'>".number_format($row['rate_fitting'],2)."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['kurs']))."</div>";
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

	public function get_query_json_product($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $sql = "
      SELECT
      (@row:=@row+1) AS nomor,
        a.*,
        b.nama
      FROM
        price_ref a LEFT JOIN ms_inventory_category2 b ON a.code=b.id_category2,
        (SELECT @row:=0) r
       WHERE 1=1 AND a.deleted='N' AND a.category='product' AND (
        a.id LIKE '%".$this->db->escape_like_str($like_value)."%'
          )
    ";
    // echo $sql; exit;

    $data['totalData'] = $this->db->query($sql)->num_rows();
    $data['totalFiltered'] = $this->db->query($sql)->num_rows();
    $columns_order_by = array(
      0 => 'nomor',
      1 => 'id'
    );

		$sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}



}
