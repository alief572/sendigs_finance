<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Sales_model extends BF_Model{

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

    public function get_json_sales_order(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_sales_order(
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
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['no_so']))."</div>";
      $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['no_so_manual']))."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['name_customer']))."</div>";
      $nestedData[]	= "<div align='left'>".date('d F Y',strtotime($row['delivery_date']))."</div>";
      $nestedData[]	= "<div align='center'>".get_sum_qty_so($row['no_so'])."</div>";
      $nestedData[]	= "<div align='center'>".get_sum_qty_so_propose($row['no_so'])."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['shipping']))."</div>";
			$last_create = (!empty($row['updated_by']))?$row['updated_by']:$row['created_by'];
			$nestedData[]	= "<div align='left'>".strtolower(get_name('users', 'username', 'id_user', $last_create))."</div>";

			$last_date = (!empty($row['updated_date']))?$row['updated_date']:$row['created_date'];
			$nestedData[]	= "<div align='center'>".date('d-m-Y H:i:s', strtotime($last_date))."</div>";
			$edit	= "";
			$delete	= "";
			$print	= "";
			$approve = "";
			$download = "";
      $cekSO = $this->db->query("SELECT * FROM delivery_header WHERE no_so='".$row['no_so']."' ")->result_array();

        if(empty($cekSO)){
					$edit	= "&nbsp;<a href='".site_url($this->uri->segment(1)).'/add_so/'.$row['no_so']."' class='btn btn-sm btn-primary' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></a>";
				}

		    // if(empty($cekSO)){
				// 	$delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete' title='Delete' data-no_so='".$row['no_so']."'><i class='fa fa-trash'></i></button>";
				// }

        $print	= "&nbsp;<a href='".site_url($this->uri->segment(1).'/print_sales_order/'.$row['no_so'])."' class='btn btn-sm btn-info' target='_blank' title='Print Sales Order' data-role='qtip'><i class='fa fa-print'></i></a>";

      $nestedData[]	= "<div align='left'>
												<button type='button' class='btn btn-sm btn-warning detail' title='Detail' data-no_so='".$row['no_so']."'><i class='fa fa-eye'></i></button>

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

	public function get_query_json_sales_order($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "
			SELECT
      (@row:=@row+1) AS nomor,
				a.*,
        b.name_customer
			FROM
				sales_order_header a LEFT JOIN master_customer b ON a.code_cust=b.id_customer,
        (SELECT @row:=0) r
		   WHERE 1=1 AND a.deleted='N' AND (
				no_so LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR name_customer LIKE '%".$this->db->escape_like_str($like_value)."%'
        OR shipping LIKE '%".$this->db->escape_like_str($like_value)."%'
        OR no_so_manual LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_so',
      2 => 'no_so_manual',
			3 => 'name_customer',
      4 => 'delivery_date'
		);

		$sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}



}
