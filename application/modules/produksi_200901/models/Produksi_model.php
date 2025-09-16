<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Produksi_model extends BF_Model{

      public function __construct()
      {
          parent::__construct();
      }


      function generate_id($kode='') {

          $today=date("ymd");
      $year=date("y");
      $month=date("m");
      $day=date("d");

          $cek = date('y').$kode_bln;
          $query = "SELECT MAX(RIGHT(id_cycletime,5)) as max_id from tr_cycletime_hd ";
          $q = $this->db->query($query);
      $r = $q->row();
          $query_cek = $q->num_rows();
      $kode2 = $r->max_id;
      $kd_noreg = "";

          if ($query_cek == 0) {
            $kd_noreg = 1;
            $reg = sprintf("%02d%05s", $year,$kode_noreg);

          }else {

          // jk sudah ada maka
        $kd_new = $kode2+1; // kode sebelumnya ditambah 1.
        $reg = sprintf("%02d%05s", $year,$kd_new);

          }

      $tr ="CT$reg";


            // print_r($tr);
        // exit();

        return $tr;
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


    public function get_data_id_tr_cycletime($id){
      $this->db->select('a.*, b.nama as nama_material, c.nama_costcenter, d.nm_asset as nama_mesin, e.nm_asset as nama_mold');
      $this->db->from('tr_cycletime_header a');
      $this->db->join('ms_material b','b.id_material=a.produk');
      $this->db->join('ms_costcenter c','c.id_costcenter =a.cost_center');
      $this->db->join('asset d','d.kd_asset =a.mesin');
      $this->db->join('asset e','e.kd_asset =a.mold_tools');
      $this->db->where('a.deleted','0');
      $this->db->where('a.id_cycletime',$id);

      $query = $this->db->get();
      return $query->result();
    }

    public function get_name($table, $field, $where, $value)
      {
         $query = "SELECT ".$field." FROM ".$table." WHERE ".$where."='".$value."' LIMIT 1";
       $result = $this->db->query($query)->result();

       return $result->$field;
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
  			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama_costcenter']))."</div>";
        $nestedData[]	= "<div align='left'>".date('d F Y',strtotime($row['date_awal']))."</div>";
        $nestedData[]	= "<div align='left'>".date('d F Y',strtotime($row['date_akhir']))."</div>";
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
  					$edit	= "&nbsp;<a href='".site_url($this->uri->segment(1)).'/add_production_planning/'.$row['no_plan']."' class='btn btn-sm btn-primary' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></a>";
  			// 	}
  			// 	if($Arr_Akses['approve']=='1'){
  			// 		$approve	= "&nbsp;<button type='button' class='btn btn-sm btn-success approve' title='Request Approval' data-project_code='".$row['project_code']."'><i class='fa fa-check'></i></button>";
  			// 	}
  			// 	if($Arr_Akses['delete']=='1'){
  					// $delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete' title='Delete work data' data-no_bom='".$row['no_bom']."'><i class='fa fa-trash'></i></button>";
  			// 	}
  			// }
  			// if($Arr_Akses['download']=='1'){
  			$print	= "&nbsp;<a href='".site_url($this->uri->segment(1).'/print_plan/'.$row['no_plan'])."' class='btn btn-sm btn-success' target='_blank' title='Print Project' data-role='qtip'><i class='fa fa-print'></i></a>";
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
  				a.*,
          b.nama_costcenter
  			FROM
  				produksi_planning a LEFT JOIN ms_costcenter b ON a.costcenter=b.id_costcenter,
          (SELECT @row:=0) r
  		   WHERE 1=1 AND a.deleted='N' AND (
  				a.no_plan LIKE '%".$this->db->escape_like_str($like_value)."%'
          OR b.nama_costcenter LIKE '%".$this->db->escape_like_str($like_value)."%'
  	        )
  		";
  		// echo $sql; exit;

  		$data['totalData'] = $this->db->query($sql)->num_rows();
  		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
  		$columns_order_by = array(
  			0 => 'nomor',
  			1 => 'no_plan',
        2 => 'nama_costcenter',
        3 => 'date_awal',
        4 => 'date_akhir'
  		);

  		$sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
  		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

  		$data['query'] = $this->db->query($sql);
  		return $data;
  	}

}
