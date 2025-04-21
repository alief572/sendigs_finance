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

        $edit2	= "";
  			// if(getStatus($row['project_code']) == 'WAITING ESTIMATION PROJECT'){
  			// 	if($Arr_Akses['update']=='1'){
            if($row['id'] <= 65){
    					$edit	= "&nbsp;<a href='".site_url($this->uri->segment(1)).'/add_production_planning/'.$row['no_plan']."' class='btn btn-sm btn-primary' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></a>";
            }
            if($row['id'] > 65){
              $edit2	= "&nbsp;<a href='".site_url($this->uri->segment(1)).'/edit_production_planning/'.$row['no_plan']."' class='btn btn-sm btn-info' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></a>";
            }
        // 	}
  			// 	if($Arr_Akses['approve']=='1'){
  			// 		$approve	= "&nbsp;<button type='button' class='btn btn-sm btn-success approve' title='Request Approval' data-project_code='".$row['project_code']."'><i class='fa fa-check'></i></button>";
  			// 	}
  			// 	if($Arr_Akses['delete']=='1'){
  					$delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete' title='Delete data' data-no_plan='".$row['no_plan']."'><i class='fa fa-trash'></i></button>";
  			// 	}
  			// }
  			// if($Arr_Akses['download']=='1'){
  			$print	= "&nbsp;<a href='".site_url($this->uri->segment(1).'/print_plan/'.$row['no_plan'])."' class='btn btn-sm btn-success' target='_blank' title='Print Project' data-role='qtip'><i class='fa fa-print'></i></a>";
  			// 	$download	= "&nbsp;<a href='".site_url($this->uri->segment(1).'/download_excel/'.$row['project_code'])."' class='btn btn-sm btn-warning' target='_blank' title='Print Project' data-role='qtip'><i class='fa fa-file-excel-o'></i></a>";
  			// }
  			$nestedData[]	= "<div align='left'>
  												<button type='button' class='btn btn-sm btn-warning detail' title='Detail' data-no_plan='".$row['no_plan']."'><i class='fa fa-eye'></i></button>

  												".$edit."
                          ".$edit2."
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

    public function get_data_json_input_produksi(){
  		$controller			= ucfirst(strtolower($this->uri->segment(1)));
  		// $Arr_Akses			= getAcccesmenu($controller);
  		$requestData	= $_REQUEST;
  		$fetch			= $this->query_data_json_input_produksi(
        $requestData['tanggal'],
        $requestData['bulan'],
        $requestData['tahun'],
  			$requestData['range'],
        $requestData['costcenter'],
        $requestData['product'],
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
  		$sumx	= 0;
  		foreach($query->result_array() as $row)
  		{
        $total_data     = $totalData;
        $start_dari     = $requestData['start'];
        $asc_desc       = $requestData['order'][0]['dir'];
        if($asc_desc == 'asc')
        {
            $nomor = ($total_data - $start_dari) - $urut2;
        }
        if($asc_desc == 'desc')
        {
            $nomor = $urut1 + $start_dari;
        }

  			$nestedData 	= array();
  			$nestedData[]	= "<div align='center'>".$nomor."</div>";
  			$nestedData[]	= "<div align='right'>".date('d-F-Y', strtotime($row['tanggal_produksi']))."</div>";

        $get_costcenter		= $this->db->select('nama_costcenter')->limit(1)->get_where('ms_costcenter', array('id_costcenter'=>$row['id_costcenter']))->result();
        $get_product	  	= $this->db->select('nama, id_category1')->limit(1)->get_where('ms_inventory_category2', array('id_category2'=>$row['id_product']))->result();
        $get_project	  	= $this->db->select('nama')->limit(1)->get_where('ms_inventory_category1', array('id_category1'=>$get_product[0]->id_category1))->result();

  			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($get_costcenter[0]->nama_costcenter))."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper(strtolower($get_project[0]->nama))."</div>";
  			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($get_product[0]->nama))."</div>";
        if($row['ket'] == 'not yet'){
          $nestedData[]	= "<div align='left'><a class='code' data-id='".$row['id']."' style='cursor:pointer;'>".strtoupper(strtolower($row['code']))." Edit</a></div>";
        }
        if($row['ket'] <> 'not yet'){
          $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['code']))."</div>";
        }
        $remak = (!empty($row['remarks']))?$row['remarks']:'-';
        $nestedData[]	= "<div align='left'>".$remak."</div>";
        $nestedData[]	= "<div align='center'>".strtoupper(strtolower($row['created_by']))."</div>";
        $nestedData[]	= "<div align='center'>".strtoupper(strtolower($row['created_date']))."</div>";

        $hapus = "";
        if($row['ket'] == 'not yet'){
          $hapus = "<button type='button' class='btn btn-sm btn-danger delete' title='Delete' data-id='".$row['id']."'><i class='fa fa-trash'></i></button>";
        }

        $nestedData[]	= "<div align='center'>".$hapus."</div>";
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

  	public function query_data_json_input_produksi($tanggal, $bulan, $tahun, $range, $costcenter, $product, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){
      $where_tgl = "";
      if($tanggal > 0){
          $where_tgl = "AND DAY(a.tanggal_produksi) = '".$tanggal."' ";
      }

  		$where_bln = "";
      if($bulan > 0){
          $where_bln = "AND MONTH(a.tanggal_produksi) = '".$bulan."' ";
      }

      $where_thn = "";
      if($tahun > 0){
          $where_thn = "AND YEAR(a.tanggal_produksi) = '".$tahun."' ";
      }

  		$where_range = "";
      if($range > 0){
  			$exP = explode(' - ', $range);
  			$date_awal = date('Y-m-d', strtotime($exP[0]));
  			$date_akhir = date('Y-m-d', strtotime($exP[1]));

        $where_range = "AND DATE(a.tanggal_produksi) BETWEEN '".$date_awal."' AND '".$date_akhir."' ";
      }

      $where_cc = "";
      if($costcenter <> '0'){
          $where_cc = "AND b.id_costcenter = '".$costcenter."' ";
      }

      $where_product = "";
      if($product <> '0'){
          $where_product = "AND a.id_product = '".$product."' ";
      }

  		$sql = "
          SELECT
            (@row:=@row+1) AS nomor,
            a.id,
            a.tanggal_produksi,
            a.id_product,
            a.`code`,
            a.ket,
            a.remarks,
            b.id_costcenter,
            b.created_by,
            b.created_date
          FROM
            report_produksi_daily_detail a
            LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h,
            (SELECT @row:=0) r
          WHERE 1=1
            AND a.sts_daycode = 'N'
            ".$where_tgl."
            ".$where_bln."
            ".$where_thn."
            ".$where_range."
            ".$where_cc."
            ".$where_product."
            AND (
              a.code LIKE '%".$this->db->escape_like_str($like_value)."%'
  	        )
  		";
  		// echo $sql; exit;

      $data['totalData'] = $this->db->query($sql)->num_rows();
  		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
  		$columns_order_by = array(
  			0 => 'nomor',
  			1 => 'tanggal_produksi',
  			2 => 'id_costcenter'
  		);

  		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
  		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

  		$data['query'] = $this->db->query($sql);
  		return $data;
  	}


    public function get_data_json_daily_report_produksi(){
  		$controller			= ucfirst(strtolower($this->uri->segment(1)));
  		// $Arr_Akses			= getAcccesmenu($controller);
  		$requestData		= $_REQUEST;
  		$fetch					= $this->get_query_json_daily_report_produksi(
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
  			$nestedData[]	= "<div align='left'>".date('d-F-Y', strtotime(($row['tanggal_produksi'])))."</div>";
  			$nestedData[]	= "<div align='left'>".strtoupper(strtolower(get_name('ms_costcenter', 'nama_costcenter', 'id_costcenter', $row['id_costcenter'])))."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper(strtolower(get_name('ms_inventory_category1', 'nama', 'id_category1', $row['id_category1'])))."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['nama_product']))."</div>";
  			// $nestedData[]	= "<div align='left'>".get_qty_order($row['id_product'])."</div>";
        $nestedData[]	= "<div align='left'>".get_sum_planning_new($row['tanggal_produksi'], $row['id_product'])."</div>";
        $nestedData[]	= "<div align='left'>".get_qty_oke_new($row['tanggal_produksi'], $row['id_product'], $row['id_costcenter'])."</div>";
        // $nestedData[]	= "<div align='left'>".get_qty_rusak($row['tanggal_produksi'], $row['id_product'], $row['id_costcenter'])."</div>";


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

  	public function get_query_json_daily_report_produksi($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

  		$sql = "
  			SELECT
          (@row:=@row+1) AS nomor,
          a.tanggal_produksi,
          b.id_costcenter,
          a.id_product,
          d.id_category1,
          d.nama AS nama_product
  			FROM
          report_produksi_daily_detail a
          LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h = b.id_produksi_h
          LEFT JOIN ms_inventory_category2 d ON a.id_product = d.id_category2,
          (SELECT @row:=0) r
  		   WHERE 1=1 AND a.ket <> 'not yet' AND (
      				b.id_costcenter LIKE '%".$this->db->escape_like_str($like_value)."%'
              OR a.id_product LIKE '%".$this->db->escape_like_str($like_value)."%'
  	        )
        GROUP BY
          a.tanggal_produksi,
          b.id_costcenter,
          a.id_product
  		";
  		// echo $sql; exit;

  		$data['totalData'] = $this->db->query($sql)->num_rows();
  		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
  		$columns_order_by = array(
  			0 => 'nomor',
  			1 => 'tanggal_produksi'
  		);

  		$sql .= " ORDER BY  ".$columns_order_by[$column_order]." ".$column_dir." ";
  		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

  		$data['query'] = $this->db->query($sql);
  		return $data;
  	}

    public function get_data_json_delete_double(){
  		$controller			= ucfirst(strtolower($this->uri->segment(1)));
  		// $Arr_Akses			= getAcccesmenu($controller);
  		$requestData	= $_REQUEST;
  		$fetch			= $this->query_data_json_delete_double(
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
  		$sumx	= 0;
  		foreach($query->result_array() as $row)
  		{
          $total_data     = $totalData;
              $start_dari     = $requestData['start'];
              $asc_desc       = $requestData['order'][0]['dir'];
              if($asc_desc == 'asc')
              {
                  $nomor = ($total_data - $start_dari) - $urut2;
              }
              if($asc_desc == 'desc')
              {
                  $nomor = $urut1 + $start_dari;
              }

  			$nestedData 	= array();
  			$nestedData[]	= "<div align='center'>".$nomor."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['id_produksi']))."</div>";
        $nestedData[]	= "<div align='left'>".strtoupper(strtolower($row['id_produksi_h']))."</div>";
  			$nestedData[]	= "<div align='right'>".date('d-F-Y', strtotime($row['tanggal_produksi']))."</div>";

        $get_costcenter	  = $this->db->select('nama_costcenter')->limit(1)->get_where('ms_costcenter', array('id_costcenter'=>$row['id_costcenter']))->result();

  			$nestedData[]	= "<div align='left'>".strtoupper(strtolower($get_costcenter[0]->nama_costcenter))."</div>";
        $nestedData[]	= "<div align='center'>".strtoupper(strtolower($row['created_by']))."</div>";
        $nestedData[]	= "<div align='center'>".date('d-F-Y H:i:s', strtotime($row['created_date']))."</div>";

        $detail = "<button type='button' class='btn btn-sm btn-warning detail' title='Detail Double' data-id='".$row['id_produksi_h']."'><i class='fa fa-eye'></i></button>";

        $nestedData[]	= "<div align='center'>".$detail."</div>";
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

  	public function query_data_json_delete_double($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){

  		$sql = "
          SELECT
            (@row:=@row+1) AS nomor,
            a.*
          FROM
            report_produksi_daily_header a,
            (SELECT @row:=0) r
          WHERE 1=1 AND (
    				a.tanggal_produksi LIKE '%".$this->db->escape_like_str($like_value)."%'
  	       )
  		";
  		// echo $sql; exit;

      $data['totalData'] = $this->db->query($sql)->num_rows();
  		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
  		$columns_order_by = array(
  			0 => 'nomor',
        1 => 'id_produksi'

  		);

  		$sql .= " ORDER BY a.id DESC,  ".$columns_order_by[$column_order]." ".$column_dir." ";
  		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

  		$data['query'] = $this->db->query($sql);
  		return $data;
  	}

    public function delete_double_modal(){
      $id = $this->uri->segment(3);
      $sql = "SELECT a.*, b.id_costcenter FROM report_produksi_daily_detail a LEFT JOIN report_produksi_daily_header b ON a.id_produksi_h=b.id_produksi_h WHERE a.id_produksi_h='".$id."'";
      // $rest_data = $this->db->get_where('report_produksi_daily_detail', array('id_produksi_h'=>$id))->result_array();
      $rest_data = $this->db->query($sql)->result_array();

      $data = array(
        'detail' => $rest_data
      );
      $this->load->view('delete_double_modal', $data);
    }

    public function delete_check_daycode(){
        $Arr_Kembali			= array();
        $data					    = $this->input->post();
        // print_r($data);
        // exit;
        $check                  = $data['check'];

        $dtImplode	= "('NONE')";

        $dtListArray = array();
        if(!empty($check)){
          foreach($check AS $val => $valx){
            $dtListArray[$val] = $valx;
          }
          $dtImplode	= "('".implode("','", $dtListArray)."')";
        }

        // echo $dtImplode;
        // echo "DELETE FROM report_produksi_daily_detail WHERE id IN ".$dtImplode."";
        // exit;

        $this->db->trans_start();
            if(!empty($check)){
                $this->db->query("DELETE FROM report_produksi_daily_detail WHERE id IN ".$dtImplode."");
            }
        $this->db->trans_complete();

        if($this->db->trans_status() === FALSE){
            $this->db->trans_rollback();
            $Arr_Data	= array(
                'pesan'		=>'Save process failed. Please try again later ...',
                'status'	=> 0
            );
        }
        else{
            $this->db->trans_commit();
            $Arr_Data	= array(
                'pesan'		=>'Save process success. Thanks ',
                'status'	=> 1
            );
        }
        history("Delete report produksi (delete double) ".$dtImplode);
        echo json_encode($Arr_Data);
    }

}
