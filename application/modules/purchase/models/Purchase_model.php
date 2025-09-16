<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
 * @author Yunas Handra
 * @copyright Copyright (c) 2018, Yunas Handra
 *
 * This is model class for table "Customer"
 */

class Purchase_model extends BF_Model{

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

 public function get_json_purchase(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData		= $_REQUEST;
		$fetch					= $this->get_query_json_purchase(
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
			$nestedData[]	= "<div align='center'>".$row['no_po']."</div>";
			$nestedData[]	= "<div align='left'>".strtoupper($row['nm_supplier'])."</div>";
      $last_create = (!empty($row['updated_by']))?$row['updated_by']:$row['created_by'];
      $nestedData[]	= "<div align='left'>".strtolower(get_name('users', 'username', 'id_user', $last_create))."</div>";

      $last_date = (!empty($row['updated_date']))?$row['updated_date']:$row['created_date'];
      $nestedData[]	= "<div align='center'>".date('d-m-Y H:i:s', strtotime($last_date))."</div>";
			if($row['sts_ajuan']=='OPN' AND $row['sts_process']=='N'){
				$status = "WAITING PROCESS";
				$badge = "bg-orange";
			}
			if($row['sts_ajuan']=='OPN' AND $row['sts_process']=='Y'){
				$status = "IN PROCESS";
				$badge = "bg-blue";
			}
			if($row['sts_ajuan']=='CLS' AND $row['sts_process']=='Y'){
				$status = "CLOSE";
				$badge = "bg-green";
			}
			if($row['sts_ajuan']=='CNC'){
				$status = "CANCEL";
				$badge = "bg-red";
			}
			$nestedData[]	= "<div align='center'><span class='badge ".$badge."'>".$status."</span></div>";
				$create	= "";
				$edit	= "";
				$booking	= "";
				$print	= "";
        // $print	= "&nbsp;<a href='".base_url('purchase/print/'.$row['no_po'])."' target='_blank' class='btn btn-sm btn-info' title='Print' data-role='qtip'><i class='fa fa-print'></i></a>";

				$cancel	= "";
				if($row['sts_ajuan']=='OPN' AND $row['sts_process']=='N'){
					  // if($Arr_Akses['update']=='1'){
						$edit			= "&nbsp;<button type='button' class='btn btn-sm btn-success editMat' title='Edit Material Purchase' data-no_po='".$row['no_po']."'><i class='fa fa-edit'></i></button>";
					// }
					// if($Arr_Akses['delete']=='1'){
						$cancel			= "&nbsp;<button type='button' class='btn btn-sm btn-danger cancelPO' title='Cancel Material Purchase' data-no_po='".$row['no_po']."'><i class='fa fa-close'></i></button>";
					// }
				}
			$nestedData[]	= "<div align='left'>
                        <button type='button' class='btn btn-sm btn-primary detailMat' title='Total Material Purchase' data-no_po='".$row['no_po']."' data-status='".$row['sts_ajuan']."'><i class='fa fa-eye'></i></button>
                        ".$create."
          							".$edit."
          							".$booking."
          							".$print."
          							".$cancel."
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

	public function get_query_json_purchase($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

    $sql = "
			SELECT
        (@row:=@row+1) AS nomor,
				a.*
			FROM
				tran_material_purchase_header a,
        (SELECT @row:=0) r
		    WHERE  (
				a.no_po LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.nm_supplier LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'no_po',
			2 => 'nm_supplier'
		);

		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

  public function get_json_request(){
		// $controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->get_query_json_request(
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
			$nestedData[]	= "<div align='center' style='vertical-align:middle;'><center><input type='checkbox' name='check[$nomor]' class='chk_personal' data-nomor='".$nomor."' value='".$row['id']."'></center></div>";
      $nestedData[]	= "<div align='left' style='vertical-align:middle;'>".$row['no_req']."</div>";
      $nestedData[]	= "<div align='left' style='vertical-align:middle;'>".$row['code_company']."</div>";
		  $nestedData[]	= "<div align='left' style='vertical-align:middle;'>".strtoupper($row['nm_material'])."</div>";
      $nestedData[]	= "<div align='right' style='vertical-align:middle;'>".number_format($row['weight']/$row['konversi'],2)." ".ucfirst($row['satuan_packing'])."</div>";
			$nestedData[]	= "<div align='right' style='vertical-align:middle;'>".number_format($row['weight'],2)." ".ucfirst($row['unit'])."</div>";
			$nestedData[]	= "<div align='right'><input type='text' id='packing_".$nomor."' name='packing_".$row['id']."' data-no='".$nomor."' data-konversi='".$row['konversi']."' style='text-align:right;' class='form-control input-md numberOnly maskM qty' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></div>";
      $nestedData[]	= "<div align='right'><input type='text' id='qty_".$nomor."' name='qty_".$row['id']."' style='text-align:right;' readonly class='form-control input-md numberOnly maskM' data-decimal='.' data-thousand='' data-precision='0' data-allow-zero=''></div>";

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

	public function get_query_json_request($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "
			SELECT
      (@row:=@row+1) AS nomor,
				a.*,
        b.code_company,
        b.nm_material,
        b.satuan_packing,
        b.konversi,
        b.unit
			FROM
				material_request_detail a LEFT JOIN ms_material b ON a.material=b.code_material,
        (SELECT @row:=0) r
		    WHERE
          a.weight > 0
          AND (a.no_po is null OR a.no_po = '')
          AND a.sts_purchase='N'
          AND (
				        a.material LIKE '%".$this->db->escape_like_str($like_value)."%'
	            )

		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
      1 => 'no_req',
      2 => 'code_company',
      3 => 'nm_material'
		);

		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

  //MATERIAL PURCHASE SDO_List
  public function get_json_matplan(){
		// $controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);

		$requestData	= $_REQUEST;
		$fetch			= $this->get_query_json_matplan(
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
			$nestedData[]	= "<div align='center' style='vertical-align:middle;'>".$nomor."</div>";
      $nestedData[]	= "<div align='left' style='vertical-align:middle;'>".$row['no_req']."</div>";
      $nestedData[]	= "<div align='right' style='vertical-align:middle;'>".number_format($row['sum_mat'],2)."</div>";
      $last_create = (!empty($row['updated_by']))?$row['updated_by']:$row['created_by'];
      $nestedData[]	= "<div align='left'>".strtolower($last_create)."</div>";

      $last_date = (!empty($row['updated_date']))?$row['updated_date']:$row['created_date'];
      $nestedData[]	= "<div align='center'>".date('d-m-Y H:i:s', strtotime($last_date))."</div>";

      $view	  = "&nbsp;<a href='".base_url('purchase/add_material_planning/'.$row['no_req'].'/view')."' class='btn btn-sm btn-warning' title='View' data-role='qtip'><i class='fa fa-eye'></i></a>";
      $edit	  = "&nbsp;<a href='".base_url('purchase/add_material_planning/'.$row['no_req'].'/edit')."' class='btn btn-sm btn-primary' title='Edit' data-role='qtip'><i class='fa fa-edit'></i></a>";
      $print	= "&nbsp;<a href='".base_url('purchase/print_material_planning/'.$row['no_req'])."' target='_blank' class='btn btn-sm btn-info' title='Print' data-role='qtip'><i class='fa fa-print'></i></a>";
    $nestedData[]	= " <div align='left'>
                      ".$view."
                      ".$edit."
                      ".$print."
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

	public function get_query_json_matplan($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

		$sql = "
			SELECT
      (@row:=@row+1) AS nomor,
				a.*
			FROM
				material_request a,
        (SELECT @row:=0) r
		    WHERE
          1=1
          AND a.status='N'
          AND (
				        a.no_req LIKE '%".$this->db->escape_like_str($like_value)."%'
	            )

		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
      1 => 'no_req'
		);

		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}



}
