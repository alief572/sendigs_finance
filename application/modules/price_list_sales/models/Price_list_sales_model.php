<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Price_list_sales_model extends BF_Model{

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

	public function get_data_where_array($table,$where){
		if(!empty($where)){
			$query = $this->db->get_where($table, $where);
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

  	public function get_json_product_price(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_product_price(
			$requestData['status'],
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

			$detail_ipp_ukuranjadi  = $this->db->get_where('product_price_ukuran_jadi',array('kode' => $row['kode'], 'deleted_date'=> null))->result_array();
			$HTML = "";
			if(!empty($detail_ipp_ukuranjadi)){
				$HTML .= "<table class='table table-bordered'>";
					$HTML .= "<tr>";
						$HTML .= "<td align='right'>Length</td>";
						$HTML .= "<td align='right'>Width</td>";
						$HTML .= "<td align='right'>Qty</td>";
						$HTML .= "<td align='right'>Price Total</td>";
					$HTML .= "</tr>";
					foreach ($detail_ipp_ukuranjadi as $key => $value) {
						$HTML .= "<tr>";
							$HTML .= "<td align='right'>".number_format($value['length'])."</td>";
							$HTML .= "<td align='right'>".number_format($value['width'])."</td>";
							$HTML .= "<td align='right'>".number_format($value['qty'])."</td>";
							$HTML .= "<td align='right'>".number_format($value['app_price'])."</td>";
						$HTML .= "</tr>";
					}
				$HTML .= "</table>";
			}


			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>".$nomor."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama_level1']))."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama_level4']))."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['variant_product']))."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper($row['nm_color'])."</div>";
			$nestedData[]	= "<div align='left'>".$HTML."</div>";
			$nestedData[]	= "<div align='right'>".number_format($row['berat_material'],4)." Kg</div>";
			$nestedData[]	= "<div align='right'>".number_format($row['price_material'],2)."</div>";
			// $nestedData[]	= "<div align='right'>".number_format($row['price_man_power'],2)."</div>";
			$nestedData[]	= "<div align='right'>".number_format($row['price_total'],2)."</div>";
			$nestedData[]	= "<div align='right'>".number_format($row['price_list'],2)."</div>";
			$nestedData[]	= "<div align='right'>".number_format($row['price_list_idr'],2)."</div>";

			$status = 'Waiting Submission';
			$warna = 'blue';
			if($row['status'] == 'WA'){
			  $status = 'Waiting Approval';
			  $warna = 'purple';
			}
			if($row['status'] == 'A'){
			  $status = 'Approved';
			  $warna = 'green';
			}
			if($row['status'] == 'R'){
				$status = 'Rejected';
				$warna = 'red';
			  }

			// $nestedData[]	= "<div align='left'><span class='badge bg-".$warna."'>".$status."</span></div>";

        	
			$view	= "";
			$edit	= "";

			$view	= "<a href='".site_url($this->uri->segment(1)).'/detail_costing/'.$row['no_bom']."' class='btn btn-sm btn-warning' title='Detail' data-role='qtip'><i class='fa fa-eye'></i></a>";
			$delete = "<a href='javascript:void(0)' class='btn btn-sm btn-danger del_product_price' data-id='".$row['id']."'><i class='fa fa-trash'></i></a>";
			// if($row['status'] == 'WA'){
			// 	$edit	= "<a href='".site_url($this->uri->segment(1)).'/pengajuan_costing/'.$row['no_bom']."' class='btn btn-sm btn-success' title='Approval Price List' data-role='qtip'><i class='fa fa-check'></i></a>";
			// }
			$nestedData[]	= "	<div align='center'>
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

	public function get_query_json_product_price($status, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){
		$WHERE = "";
		if($status != '0'){
			$WHERE = "AND a.status = '".$status."'";
		}
		$sql = "SELECT
					(@row:=@row+1) AS nomor,
					a.*,
					b.nama AS nama_level4,
					d.variant_product,
					d.color as nm_color,
					c.nama AS nama_level1
				FROM
					product_price a 
					LEFT JOIN new_inventory_4 b ON a.code_lv4=b.code_lv4
					LEFT JOIN new_inventory_1 c ON b.code_lv1=c.code_lv1
					LEFT JOIN bom_header d ON a.no_bom=d.no_bom,
					(SELECT @row:=0) r
				WHERE 1=1 AND a.deleted_date IS NULL ".$WHERE." AND
					(
						a.no_bom LIKE '%".$this->db->escape_like_str($like_value)."%'
						OR b.nama LIKE '%".$this->db->escape_like_str($like_value)."%'
						OR d.variant_product LIKE '%".$this->db->escape_like_str($like_value)."%'
					)
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'b.nama',
			2 => 'c.nama'
		);

		$sql .= " ORDER BY a.no_bom DESC,  ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

}
