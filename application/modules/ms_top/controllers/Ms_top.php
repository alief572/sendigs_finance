<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ms_top extends Admin_Controller
{

	protected $viewPermission   = "TOP.View";
	protected $addPermission    = "TOP.Add";
	protected $managePermission = "TOP.Manage";
	protected $deletePermission = "TOP.Delete";


	public function __construct()
	{
		parent::__construct();
		$this->load->model('ms_top/master_model');
	}
	public function index()
	{


		$data_Group			= $this->db->get('groups');
		$data = array(
			'title'			=> 'Index Of TOP',
			'action'		=> 'ms_top',
			'row_group'		=> $data_Group
		);
		history('View Data TOP');
		$this->template->set($data);
		$this->template->render('index');
	}
	public function data_side()
	{

		$requestData	= $_REQUEST;
		$fetch			= $this->get_query_json(
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
			if ($asc_desc == 'asc') $nomor = $urut1 + $start_dari;
			if ($asc_desc == 'desc') $nomor = ($total_data - $start_dari) - $urut2;
			$nestedData 	= array();
			$nestedData[]	= "<div align='center'>" . $nomor . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['name'] . "</div>";
			$nestedData[]	= "<div align='left'>" . $row['data1'] . "</div>";
			$detail		= "";
			$edit		= "";
			$delete		= "";
			// if ($Arr_Akses['delete'] == '1') {
			$delete	= "&nbsp;<button type='button' class='btn btn-sm btn-danger delete' title='Delete data' data-code='" . $row['id'] . "'><i class='fa fa-trash'></i></button>";
			// }
			// if ($Arr_Akses['update'] == '1') {
			$edit	= "&nbsp;<button type='button' class='btn btn-sm btn-primary edit' data-code='" . $row['id'] . "' title='Edit Data' data-role='qtip'><i class='fa fa-edit'></i></button>";
			// }
			$nestedData[]	= "<div align='left'> " . $edit . " " . $delete . " </div>";
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
	public function get_query_json($like_value = NULL, $column_order = NULL, $column_dir = NULL, $limit_start = NULL, $limit_length = NULL)
	{
		$sql = "
            SELECT
                (@row:=@row+1) AS nomor,
				a.*
			FROM
                list_help a,
                (SELECT @row:=0) r
            WHERE
                a.`group_by`='top invoice' AND (
                a.`name` LIKE '%" . $this->db->escape_like_str($like_value) . "%'
                or a.data1 LIKE '%" . $this->db->escape_like_str($like_value) . "%'
	        )
		";
		$data['totalData']  = $this->db->query($sql)->num_rows();
		$data['totalFiltered'] = $this->db->query($sql)->num_rows();
		$columns_order_by = array(
			0 => 'nomor',
			1 => 'name',
			2 => 'data1',
		);
		$sql .= " ORDER BY " . $columns_order_by[$column_order] . " " . $column_dir . " ";
		$sql .= " LIMIT " . $limit_start . " ," . $limit_length . " ";
		$data['query'] = $this->db->query($sql);
		return $data;
	}
	public function add_data()
	{
		if ($this->input->post()) {
			$Arr_Kembali	= array();
			$data			= $this->input->post();
			$data_session	= $this->session->userdata;
			$dateTime		= date('Y-m-d H:i:s');
			$id 		    = $data['id'];
			$name			=  ($data['name']);
			$data1		= $data['data1'];
			if (empty($id)) {
				$ArrHeader = array(
					'name'			=> $name,
					'data1' 		=> $data1,
					'sts' 			=> 'Y',
					'group_by' 		=> 'top invoice',
				);
				$TandaI = "Insert";
			}
			if (!empty($id)) {
				$ArrHeader = array(
					'name'			=> $name,
					'data1' 		=> $data1,
				);
				$TandaI = "Update";
			}
			$this->db->trans_start();
			if (empty($id)) $this->db->insert('list_help', $ArrHeader);
			if (!empty($id)) {
				$this->db->where('id', $id);
				$this->db->update('list_help', $ArrHeader);
			}
			$this->db->trans_complete();
			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$Arr_Kembali	= array(
					'pesan'		=> $TandaI . ' data failed. Please try again later ...',
					'status'	=> 0
				);
			} else {
				$this->db->trans_commit();
				$Arr_Kembali	= array(
					'pesan'		=> $TandaI . ' data success.',
					'status'	=> 1
				);
				history($TandaI . ' TOP  ' . $id . ' / ' . $name);
			}
			echo json_encode($Arr_Kembali);
		} else {

			// if ($Arr_Akses['create'] != '1') {
			// 	$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
			// 	redirect(site_url('users'));
			// }
			$id = $this->uri->segment(3);
			$query = "SELECT * FROM list_help WHERE id ='" . $id . "' LIMIT 1 ";
			$result = $this->db->query($query)->result();
			$data = array(
				'title'		=> 'Data TOP',
				'action'	=> 'add',
				'data'      => $result
			);
			$this->load->view('form', $data);
		}
	}
	public function hapus_data()
	{
		// if ($Arr_Akses['create'] != '1') {
		// 	$this->session->set_flashdata("alert_data", "<div class=\"alert alert-warning\" id=\"flash-message\">You Don't Have Right To Access This Page, Please Contact Your Administrator....</div>");
		// 	redirect(site_url('users'));
		// }
		$id = $this->uri->segment(3);
		$data_session	= $this->session->userdata;
		$this->db->trans_start();
		$this->db->where('id', $id);
		$this->db->delete('list_help');
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			$Arr_Data	= array(
				'pesan'		=> 'Delete data failed. Please try again later ...',
				'status'	=> 0
			);
		} else {
			$this->db->trans_commit();
			$Arr_Data	= array(
				'pesan'		=> 'Delete data success. Thanks ...',
				'status'	=> 1
			);
			history('Delete TOP Data : ' . $id);
		}
		echo json_encode($Arr_Data);
	}
}
