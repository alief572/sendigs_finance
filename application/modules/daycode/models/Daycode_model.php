<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Daycode_model extends BF_Model{

    public function __construct()
    {
        parent::__construct();
    }

	public function getList($table){
		$queryList = $this->db->where('status','Y')->get($table)->result_array();
		return $queryList;
	}

	public function getWhere($table, $flied, $value){
		$queryList = $this->db->get_where($table, array($flied => $value))->result_array();
		return $queryList;
	}

	public function saveData($table, $dataArr){

		$this->db->trans_start();
			$this->db->insert($table, $dataArr);
		$this->db->trans_complete();

		if($this->db->trans_status() === FALSE){
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=>'Asset gagal disimpan ...',
				'status'	=> 0
			);
		}
		else{
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=>'Asset berhasil disimpan. Thanks ...',
				'status'	=> 1
			);
		}

		return $Arr_Data;
	}

	public function getDataJSON(){
		$controller			= ucfirst(strtolower($this->uri->segment(1)));
		// $Arr_Akses			= getAcccesmenu($controller);
		$requestData	= $_REQUEST;
		$fetch			= $this->queryDataJSON(
      $requestData['tanggal'],
      $requestData['bulan'],
      $requestData['tahun'],
			$requestData['range'],
			$requestData['search']['value'],
			$requestData['order'][0]['column'],
			$requestData['order'][0]['dir'],
			$requestData['start'],
			$requestData['length']
		);
		$totalFiltered	= $fetch['totalFiltered'];
		$query			= $fetch['query'];

		$data	= array();
		$urut1  = 1;
        $urut2  = 0;
		$sumx	= 0;
		foreach($query->result_array() as $row)
		{
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
			$nestedData[]	= "<div align='center'>".strtoupper(strtolower($row['code']))."</div>";
			$nestedData[]	= "<div align='center'>".strtoupper(strtolower($row['tanggal']))."</div>";
			$nestedData[]	= "<div align='center'>".strtoupper(strtolower($row['urut']))."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper(strtolower($row['created_by']))."</div>";
      $nestedData[]	= "<div align='center'>".strtoupper(strtolower($row['created_date']))."</div>";
			$data[] = $nestedData;
            $urut1++;
            $urut2++;
		}

		$json_data = array(
			"draw"            	=> intval( $requestData['draw'] ),
			"recordsFiltered" 	=> intval( $totalFiltered ),
			"data"            	=> $data
		);

		echo json_encode($json_data);
	}

	public function queryDataJSON($tanggal, $bulan, $tahun, $range, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL){
    $where_tgl = "";
    if($tanggal > 0){
        $where_tgl = "AND DAY(a.tanggal) = '".$tanggal."' ";
    }

		$where_bln = "";
    if($bulan > 0){
        $where_bln = "AND MONTH(a.tanggal) = '".$bulan."' ";
    }

    $where_thn = "";
    if($tahun > 0){
        $where_thn = "AND YEAR(a.tanggal) = '".$tahun."' ";
    }

		$where_range = "";
    if($range > 0){
			$exP = explode(' - ', $range);
			$date_awal = date('Y-m-d', strtotime($exP[0]));
			$date_akhir = date('Y-m-d', strtotime($exP[1]));
			// echo $exP[0];exit;
            $where_range = "AND DATE(a.tanggal) BETWEEN '".$date_awal."' AND '".$date_akhir."' ";
    }

		$sql = "
			SELECT
				a.code,
				a.tanggal,
				a.urut,
        a.created_by,
        a.created_date
			FROM
				daycode a
			WHERE 1=1 ".$where_tgl." ".$where_bln." ".$where_thn." ".$where_range."
				AND (
				a.code LIKE '%".$this->db->escape_like_str($like_value)."%'
				OR a.tanggal LIKE '%".$this->db->escape_like_str($like_value)."%'
        OR a.urut LIKE '%".$this->db->escape_like_str($like_value)."%'
	        )
		";
		// echo $sql; exit;

		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'id',
			1 => 'code',
			2 => 'tanggal',
			3 => 'urut'

		);

		$sql .= " ORDER BY ".$columns_order_by[$column_order]." ".$column_dir." ";
		$sql .= " LIMIT ".$limit_start." ,".$limit_length." ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}

}
