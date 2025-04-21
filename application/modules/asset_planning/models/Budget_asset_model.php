<?php
class Budget_asset_model extends BF_Model
{

	public function __construct()
	{
		parent::__construct();
		// $this->load->database();
		// $this->db2 = $this->load->database('gl', TRUE);
	}

	public function index()
	{

		$tanda = $this->uri->segment(3);
		$label = (!empty($tanda)) ? 'Approval' : '';
		$data = array(
			'title'			=> 'Indeks Of ' . $label . ' Budget Assets',
			'action'		=> 'index',
			'tanda'			=> $tanda,
			'label' => $label
		);
		history('View Data Budget Assets');
		$this->template->set($data);
		$this->template->render('index');
	}

	// public function detail_rutin(){
	// $controller			= ucfirst(strtolower($this->uri->segment(1)))."/index_asset"; 
	// $Arr_Akses			= getAcccesmenu($controller);
	// if($Arr_Akses['read'] !='1'){
	// $this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
	// redirect(site_url('dashboard'));
	// }

	// $code = $this->uri->segment(3);
	// $header = $this->db->query("SELECT * FROM budget_rutin_header WHERE code_budget='".$code."' ")->result();

	// $data = array(
	// 'title'			=> 'Add Budget Rutin',
	// 'action'		=> 'add',
	// 'akses_menu'	=> $Arr_Akses,
	// 'header'		=> $header,
	// 'code'			=> $code
	// );
	// $this->load->view('Budget_rutin/modal_detail',$data);
	// }

	public function hapus_asset()
	{
		$Arr_Kembali	= array();
		$data			= $this->input->post();
		$data_session	= $this->session->userdata;
		$dateTime		= date('Y-m-d H:i:s');
		// print_r($data); exit;
		$code_plan  	= $this->uri->segment(3);

		$ArrHeader		= array(
			'deleted' 		=> 'Y',
			'deleted_by'	=> $this->auth->user_id(),
			'deleted_date'	=> $dateTime
		);

		$this->db->trans_start();
		$this->db->where(array('code_plan' => $code_plan));
		$this->db->update('asset_planning', $ArrHeader);

		$this->db->trans_complete();


		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Kembali	= array(
				'pesan'		=> 'Process data failed. Please try again later ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Kembali	= array(
				'pesan'		=> 'Process data success. Thanks ...',
				'status'	=> 1
			);
			history('Hapus Pengajuan Budget Asset ' . $code_plan);
		}
		echo json_encode($Arr_Kembali);
	}

	//==========================================================================================================================
	//======================================================SERVER SIDE=========================================================
	//==========================================================================================================================

	public function get_data_json_asset()
	{

		$requestData	= $_REQUEST;
		$fetch			= $this->query_data_json_asset(
			$requestData['tanda'],
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
		foreach ($query->result_array() as $row) {
			$total_data     = $totalData;
			$start_dari     = $requestData['start'];
			$asc_desc       = $requestData['order'][0]['dir'];
			if ($asc_desc == 'asc') {
				$nomor = $urut1 + $start_dari;
			}
			if ($asc_desc == 'desc') {
				$nomor = ($total_data - $start_dari) - $urut2;
			}

			$tanda = $requestData['tanda'];

			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['coa'] . ' | ' . $row['nama']) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['nm_dept']) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['nm_costcenter']) . "</div>";
			$nestedData[]	= "<div align='left'>" . strtoupper($row['nama_asset']) . "</div>";
			$nestedData[]	= "<div align='center'>" . $row['qty'] . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['budget']) . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['budget_pr']) . "</div>";
			$nestedData[]	= "<div align='right'>" . number_format($row['budget_po']) . "</div>";
			$nestedData[]	= "<div align='right'>" . date('F Y', strtotime($row['tahun'] . '-' . $row['bulan'] . '-01')) . "</div>";
			$color = '';
			$status = '';
			if ($row['status'] == 'N') {
				$status = 'WAITING APPROVAL';
				$color = 'blue';
			} else if ($row['status'] == 'Y') {
				$status = 'APPROVED';
				$color = 'green';
			} else if ($row['status'] == 'D') {
				$status = 'REJECTED';
				$color = 'red';
			}
			$nestedData[]	= "<div align='left'><span class='badge bg-" . $color . "'>" . strtoupper($status) . "</span></div>";
			$edit		= "";
			$delete		= "";
			$approve	= "";
			$view		= "";

			if ($row['status'] == 'N') {
				if (empty($tanda)) {
					$edit	= "<a href='" . site_url($this->uri->segment(1)) . '/add_asset/' . $row['code_plan'] . "' class='btn btn-sm btn-primary' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></a>";
					$delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger hapus' data-id='" . $row['code_plan'] . "' title='Delete Data'><i class='fa fa-trash'></i></button>";
				}
				if (!empty($tanda)) {
					$approve = "&nbsp;<a href='" . site_url($this->uri->segment(1)) . '/add_asset/' . $row['code_plan'] . "/approve' class='btn btn-sm btn-info' title='Approve Data' data-role='qtip'><i class='fa fa-check'></i></a>";
				}
			}
			if ($row['status'] != 'N') {
				$view = "<a href='" . site_url($this->uri->segment(1)) . '/add_asset/' . $row['code_plan'] . "/view' class='btn btn-sm btn-warning' title='Detail Data' data-role='qtip'><i class='fa fa-eye'></i></a>";
			}
			$nestedData[]	= "	<div align='left'>
									" . $view . "
                                    " . $edit . "
									" . $delete . "
									" . $approve . "
								</div>";
			$data[] = $nestedData;
			$urut1++;
			$urut2++;
		}

		$json_data = array(
			"draw"            	=> intval($requestData['draw']),
			"recordsTotal"    	=> intval($totalData),
			"recordsFiltered" 	=> intval($totalFiltered),
			"data"            	=> $data
		);

		echo json_encode($json_data);
	}

	public function query_data_json_asset($tanda, $like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{

		$where = '';
		if (!empty($tanda)) {
			$where = " AND a.status = 'N' ";
		}

		$sql = "
			SELECT
				(@row:=@row+1) AS nomor,
				a.*,
				b.nama as nm_dept,
				c.nama_costcenter as nm_costcenter,
				d.nama
			FROM
				asset_planning a
				LEFT JOIN ms_department b ON a.id_dept = b.id
				LEFT JOIN ".DBACC.".coa_master d ON a.coa = d.no_perkiraan
				LEFT JOIN ms_costcenter c ON a.id_costcenter = c.id,
				(SELECT @row:=0) r
		    WHERE  a.deleted='N' " . $where . " AND(
				a.id LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR b.nama LIKE '%" . $this->db->escape_like_str($like_value) . "%'
				OR c.nama_costcenter LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
		";
		// echo $sql; exit;

		$data['totalData'] = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'id',
			2 => 'nm_dept',
			3 => 'nm_costcenter',
			4 => 'nama_asset',
			5 => 'qty',
			6 => 'budget',
			7 => 'budget_pr',
			8 => 'budget_po'
		);

		$sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";

		$data['query'] = $this->db->query($sql);
		return $data;
	}
}
